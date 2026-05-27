<?php

namespace App\Http\Controllers\Webhooks;

use App\Http\Controllers\Controller;
use App\Jobs\SendWhatsAppOrderConfirmation;
use App\Models\Payment;
use App\Services\Payment\MpesaService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class MpesaWebhookController extends Controller
{
    public function __construct(private MpesaService $mpesa) {}

    public function handle(Request $request): Response
    {
        $payload = $request->all();

        Log::channel('stack')->info('M-Pesa webhook received', $payload);

        try {
            $this->mpesa->processCallback($payload);

            // Dispatch WhatsApp confirmation if payment succeeded
            $checkoutId = $payload['Body']['stkCallback']['CheckoutRequestID'] ?? null;
            if ($checkoutId) {
                $payment = Payment::where('gateway_checkout_id', $checkoutId)
                                  ->where('status', 'paid')
                                  ->with('order')
                                  ->first();

                if ($payment?->order) {
                    SendWhatsAppOrderConfirmation::dispatch($payment->order);
                }
            }
        } catch (\Throwable $e) {
            Log::error('M-Pesa webhook error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
        }

        // Daraja expects a 200 with this exact structure, regardless of outcome
        return response(['ResultCode' => 0, 'ResultDesc' => 'Accepted'], 200);
    }
}
