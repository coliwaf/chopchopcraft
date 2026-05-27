<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MpesaService
{
    private string $consumerKey;
    private string $consumerSecret;
    private string $shortcode;
    private string $passkey;
    private string $callbackUrl;
    private string $baseUrl;

    public function __construct()
    {
        $this->consumerKey    = config('services.mpesa.consumer_key');
        $this->consumerSecret = config('services.mpesa.consumer_secret');
        $this->shortcode      = config('services.mpesa.shortcode');
        $this->passkey        = config('services.mpesa.passkey');
        $this->callbackUrl    = config('services.mpesa.callback_url');
        $this->baseUrl        = config('services.mpesa.env') === 'production'
            ? 'https://api.safaricom.co.ke'
            : 'https://sandbox.safaricom.co.ke';
    }

    // ─── Access Token (cached for 55 min) ────────────────────────────────────
    public function getAccessToken(): string
    {
        return Cache::remember('mpesa_access_token', 3300, function () {
            $response = Http::withBasicAuth($this->consumerKey, $this->consumerSecret)
                ->get("{$this->baseUrl}/oauth/v1/generate?grant_type=client_credentials");

            if (! $response->successful()) {
                throw new \RuntimeException('Failed to obtain M-Pesa access token.');
            }

            return $response->json('access_token');
        });
    }

    // ─── STK Push ─────────────────────────────────────────────────────────────
    /**
     * Initiates an STK Push to the customer's phone.
     * Returns the CheckoutRequestID for polling/callback matching.
     */
    public function stkPush(Order $order, string $phone): array
    {
        $phone     = $this->formatPhone($phone);
        $timestamp = now()->format('YmdHis');
        $password  = base64_encode($this->shortcode . $this->passkey . $timestamp);

        $response = Http::withToken($this->getAccessToken())
            ->post("{$this->baseUrl}/mpesa/stkpush/v1/processrequest", [
                'BusinessShortCode' => $this->shortcode,
                'Password'          => $password,
                'Timestamp'         => $timestamp,
                'TransactionType'   => 'CustomerPayBillOnline',
                'Amount'            => (int) ceil($order->total), // M-Pesa requires integer
                'PartyA'            => $phone,
                'PartyB'            => $this->shortcode,
                'PhoneNumber'       => $phone,
                'CallBackURL'       => $this->callbackUrl,
                'AccountReference'  => $order->order_number,
                'TransactionDesc'   => "Payment for {$order->order_number}",
            ]);

        $data = $response->json();

        Log::info('M-Pesa STK Push', ['order' => $order->order_number, 'response' => $data]);

        if (($data['ResponseCode'] ?? '') !== '0') {
            throw new \RuntimeException($data['errorMessage'] ?? $data['ResponseDescription'] ?? 'STK Push failed.');
        }

        // Store the CheckoutRequestID on the payment record so we can match the callback
        Payment::where('order_id', $order->id)
               ->where('gateway', 'mpesa')
               ->update(['gateway_checkout_id' => $data['CheckoutRequestID']]);

        return $data;
    }

    // ─── STK Query (optional polling) ────────────────────────────────────────
    public function stkQuery(string $checkoutRequestId): array
    {
        $timestamp = now()->format('YmdHis');
        $password  = base64_encode($this->shortcode . $this->passkey . $timestamp);

        return Http::withToken($this->getAccessToken())
            ->post("{$this->baseUrl}/mpesa/stkpushquery/v1/query", [
                'BusinessShortCode'  => $this->shortcode,
                'Password'           => $password,
                'Timestamp'          => $timestamp,
                'CheckoutRequestID'  => $checkoutRequestId,
            ])->json();
    }

    // ─── Process Callback ─────────────────────────────────────────────────────
    /**
     * Handle the Daraja callback payload.
     * Called from MpesaWebhookController.
     */
    public function processCallback(array $payload): void
    {
        $stk = $payload['Body']['stkCallback'] ?? [];

        $checkoutRequestId = $stk['CheckoutRequestID'] ?? null;
        $resultCode        = $stk['ResultCode'] ?? -1;

        $payment = Payment::where('gateway_checkout_id', $checkoutRequestId)->first();

        if (! $payment) {
            Log::warning('M-Pesa callback: no payment found', compact('checkoutRequestId'));
            return;
        }

        $payment->update(['raw_response' => $payload]);

        if ($resultCode === 0) {
            // Extract M-Pesa receipt number from metadata items
            $items  = collect($stk['CallbackMetadata']['Item'] ?? []);
            $mpesaRef = $items->firstWhere('Name', 'MpesaReceiptNumber')['Value'] ?? null;
            $payment->markPaid($mpesaRef);

            Log::info("M-Pesa payment confirmed for order {$payment->order->order_number}", compact('mpesaRef'));
        } else {
            $payment->update(['status' => 'failed']);
            Log::warning("M-Pesa payment failed for order {$payment->order_id}", ['code' => $resultCode, 'desc' => $stk['ResultDesc'] ?? '']);
        }
    }

    // ─── Helpers ──────────────────────────────────────────────────────────────
    /** Normalise Kenyan numbers to 2547XXXXXXXX */
    private function formatPhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0'))     return '254' . substr($phone, 1);
        if (str_starts_with($phone, '+254'))  return ltrim($phone, '+');
        if (str_starts_with($phone, '254'))   return $phone;

        return '254' . $phone; // fallback
    }
}
