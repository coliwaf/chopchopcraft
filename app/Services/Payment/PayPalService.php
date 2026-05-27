<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PayPalService
{
    private string $clientId;
    private string $clientSecret;
    private string $baseUrl;
    private string $webhookId;

    public function __construct()
    {
        $this->clientId     = config('services.paypal.client_id');
        $this->clientSecret = config('services.paypal.client_secret');
        $this->webhookId    = config('services.paypal.webhook_id');
        $this->baseUrl      = config('services.paypal.mode') === 'live'
            ? 'https://api-m.paypal.com'
            : 'https://api-m.sandbox.paypal.com';
    }

    // ─── Access Token (cached 8 hours) ────────────────────────────────────────
    public function getAccessToken(): string
    {
        return Cache::remember('paypal_access_token', 28000, function () {
            $response = Http::asForm()
                ->withBasicAuth($this->clientId, $this->clientSecret)
                ->post("{$this->baseUrl}/v1/oauth2/token", ['grant_type' => 'client_credentials']);

            if (! $response->successful()) {
                throw new \RuntimeException('Failed to obtain PayPal access token.');
            }

            return $response->json('access_token');
        });
    }

    // ─── Create Order ─────────────────────────────────────────────────────────
    /**
     * Creates a PayPal Order (equivalent to PaymentIntent in Stripe).
     * Returns the approval URL to redirect the customer.
     */
    public function createOrder(Order $order): array
    {
        $response = Http::withToken($this->getAccessToken())
            ->post("{$this->baseUrl}/v2/checkout/orders", [
                'intent'          => 'CAPTURE',
                'purchase_units'  => [[
                    'reference_id' => $order->order_number,
                    'description'  => "Chopping board order {$order->order_number}",
                    'amount'       => [
                        'currency_code' => config('services.paypal.currency', 'USD'),
                        'value'         => number_format($order->total / $this->getUsdRate(), 2, '.', ''),
                    ],
                ]],
                'application_context' => [
                    'return_url' => route('checkout.paypal.capture', $order),
                    'cancel_url' => route('checkout.paypal.cancel', $order),
                    'brand_name' => config('app.name'),
                    'user_action' => 'PAY_NOW',
                ],
            ]);

        $data = $response->json();

        Log::info('PayPal order created', ['order' => $order->order_number, 'paypal_id' => $data['id'] ?? null]);

        if (! isset($data['id'])) {
            throw new \RuntimeException('PayPal order creation failed: ' . ($data['message'] ?? 'Unknown error'));
        }

        // Store PayPal order ID on payment record
        Payment::where('order_id', $order->id)
               ->where('gateway', 'paypal')
               ->update(['gateway_checkout_id' => $data['id']]);

        $approveLink = collect($data['links'])->firstWhere('rel', 'approve');

        return [
            'paypal_order_id' => $data['id'],
            'approve_url'     => $approveLink['href'],
        ];
    }

    // ─── Capture ──────────────────────────────────────────────────────────────
    /**
     * Called when the customer returns from PayPal after approving.
     */
    public function captureOrder(string $paypalOrderId): array
    {
        $response = Http::withToken($this->getAccessToken())
            ->post("{$this->baseUrl}/v2/checkout/orders/{$paypalOrderId}/capture");

        $data = $response->json();

        Log::info('PayPal capture', ['paypal_order_id' => $paypalOrderId, 'status' => $data['status'] ?? null]);

        if (($data['status'] ?? '') === 'COMPLETED') {
            $payment = Payment::where('gateway_checkout_id', $paypalOrderId)->first();
            if ($payment) {
                $captureId = $data['purchase_units'][0]['payments']['captures'][0]['id'] ?? $paypalOrderId;
                $payment->update(['raw_response' => $data]);
                $payment->markPaid($captureId);
            }
        }

        return $data;
    }

    // ─── Webhook ──────────────────────────────────────────────────────────────
    /**
     * Verifies and processes PayPal webhook events.
     */
    public function processWebhook(array $headers, string $rawBody): void
    {
        // Verify webhook signature via PayPal API
        $verified = $this->verifyWebhookSignature($headers, $rawBody);
        if (! $verified) {
            throw new \RuntimeException('PayPal webhook verification failed.');
        }

        $event = json_decode($rawBody, true);
        Log::info('PayPal webhook', ['event_type' => $event['event_type'] ?? null]);

        match ($event['event_type'] ?? '') {
            'PAYMENT.CAPTURE.COMPLETED'  => $this->handleCaptureCompleted($event),
            'PAYMENT.CAPTURE.DENIED'     => $this->handleCaptureDenied($event),
            'PAYMENT.CAPTURE.REFUNDED'   => $this->handleCaptureRefunded($event),
            default                      => null,
        };
    }

    private function handleCaptureCompleted(array $event): void
    {
        $orderId = $event['resource']['supplementary_data']['related_ids']['order_id'] ?? null;
        if (! $orderId) return;

        $payment = Payment::where('gateway_checkout_id', $orderId)->first();
        if ($payment) {
            $payment->update(['raw_response' => $event]);
            $payment->markPaid($event['resource']['id']);
        }
    }

    private function handleCaptureDenied(array $event): void
    {
        $orderId = $event['resource']['supplementary_data']['related_ids']['order_id'] ?? null;
        $payment = Payment::where('gateway_checkout_id', $orderId)->first();
        $payment?->update(['status' => 'failed', 'raw_response' => $event]);
    }

    private function handleCaptureRefunded(array $event): void
    {
        $captureId = $event['resource']['links'][0]['href'] ?? null; // heuristic
        $payment   = Payment::where('gateway_ref', $event['resource']['id'])->first();
        if ($payment) {
            $payment->update(['status' => 'refunded']);
            $payment->order->update(['payment_status' => 'refunded', 'status' => 'refunded']);
        }
    }

    private function verifyWebhookSignature(array $headers, string $rawBody): bool
    {
        $response = Http::withToken($this->getAccessToken())
            ->post("{$this->baseUrl}/v1/notifications/verify-webhook-signature", [
                'auth_algo'         => $headers['paypal-auth-algo'] ?? '',
                'cert_url'          => $headers['paypal-cert-url'] ?? '',
                'transmission_id'   => $headers['paypal-transmission-id'] ?? '',
                'transmission_sig'  => $headers['paypal-transmission-sig'] ?? '',
                'transmission_time' => $headers['paypal-transmission-time'] ?? '',
                'webhook_id'        => $this->webhookId,
                'webhook_event'     => json_decode($rawBody, true),
            ]);

        return ($response->json('verification_status') ?? '') === 'SUCCESS';
    }

    /** KES → USD conversion rate. In production use a live rate API. */
    private function getUsdRate(): float
    {
        return Cache::remember('kes_usd_rate', 3600, fn() => 130.0); // fallback rate
    }
}
