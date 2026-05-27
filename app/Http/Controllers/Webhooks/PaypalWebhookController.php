<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppOrderConfirmation;
use App\Models\Payment;
use App\Services\Payment\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class PayPalWebhookController extends Controller
{
    public function __construct(private PayPalService $paypal) {}

    public function handle(Request $request): Response
    {
        $rawBody = $request->getContent();
        $headers = array_map(
            fn($h) => is_array($h) ? $h[0] : $h,
            $request->headers->all()
        );

        Log::info('PayPal webhook received', ['event_type' => $request->input('event_type')]);

        try {
            $this->paypal->processWebhook($headers, $rawBody);

            // Dispatch WhatsApp confirmation for capture events
            if ($request->input('event_type') === 'PAYMENT.CAPTURE.COMPLETED') {
                $orderId = $request->input('resource.supplementary_data.related_ids.order_id');

                if ($orderId) {
                    $payment = Payment::where('gateway_checkout_id', $orderId)
                                      ->where('status', 'paid')
                                      ->with('order')
                                      ->first();

                    if ($payment?->order) {
                        SendWhatsAppOrderConfirmation::dispatch($payment->order);
                    }
                }
            }
        } catch (\RuntimeException $e) {
            Log::error('PayPal webhook verification failed: ' . $e->getMessage());
            return response('Webhook verification failed.', 400);
        } catch (\Throwable $e) {
            Log::error('PayPal webhook error: ' . $e->getMessage());
        }

        return response('Webhook handled.', 200);
    }
}
