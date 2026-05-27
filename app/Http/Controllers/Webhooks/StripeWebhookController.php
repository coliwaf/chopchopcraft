<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppOrderConfirmation;
use App\Models\Payment;
use App\Services\Payment\StripeService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class StripeWebhookController extends Controller
{
    public function __construct(private StripeService $stripe) {}

    public function handle(Request $request): Response
    {
        $payload   = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature', '');

        Log::info('Stripe webhook received', ['sig' => substr($sigHeader, 0, 20) . '...']);

        try {
            $this->stripe->processWebhook($payload, $sigHeader);

            // Dispatch WhatsApp confirmation for successful payments
            if (str_contains($payload, 'payment_intent.succeeded')) {
                $data      = json_decode($payload, true);
                $intentId  = $data['data']['object']['id'] ?? null;

                if ($intentId) {
                    $payment = Payment::where('gateway_checkout_id', $intentId)
                                      ->where('status', 'paid')
                                      ->with('order')
                                      ->first();

                    if ($payment?->order) {
                        SendWhatsAppOrderConfirmation::dispatch($payment->order);
                    }
                }
            }
        } catch (\RuntimeException $e) {
            // Signature mismatch — return 400 so Stripe retries
            Log::error('Stripe webhook failed: ' . $e->getMessage());
            return response('Webhook signature verification failed.', 400);
        } catch (\Throwable $e) {
            Log::error('Stripe webhook error: ' . $e->getMessage());
        }

        return response('Webhook handled.', 200);
    }
}
