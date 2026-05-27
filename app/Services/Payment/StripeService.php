<?php

namespace App\Services\Payment;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\SignatureVerificationException;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Stripe\Webhook;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('services.stripe.secret'));
    }

    // ─── Create PaymentIntent ─────────────────────────────────────────────────
    /**
     * Creates a Stripe PaymentIntent and returns its client_secret for the
     * React frontend (Stripe.js / Elements).
     */
    public function createPaymentIntent(Order $order): array
    {
        $intent = PaymentIntent::create([
            'amount'               => (int) ($order->total * 100), // cents
            'currency'             => strtolower(config('services.stripe.currency', 'kes')),
            'automatic_payment_methods' => ['enabled' => true],
            'metadata'             => [
                'order_id'     => $order->id,
                'order_number' => $order->order_number,
            ],
            'description'          => "Chopping board order {$order->order_number}",
            'receipt_email'        => $order->customer->email,
        ]);

        // Store intent ID on payment record
        Payment::where('order_id', $order->id)
               ->where('gateway', 'stripe')
               ->update(['gateway_checkout_id' => $intent->id]);

        return [
            'client_secret'    => $intent->client_secret,
            'payment_intent_id' => $intent->id,
        ];
    }

    // ─── Retrieve & confirm ───────────────────────────────────────────────────
    public function retrieveIntent(string $intentId): PaymentIntent
    {
        return PaymentIntent::retrieve($intentId);
    }

    // ─── Webhook ──────────────────────────────────────────────────────────────
    /**
     * Verify Stripe webhook signature and process the event.
     * Called from StripeWebhookController.
     */
    public function processWebhook(string $payload, string $sigHeader): void
    {
        try {
            $event = Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
        } catch (SignatureVerificationException $e) {
            throw new \RuntimeException('Stripe webhook signature verification failed.');
        }

        Log::info('Stripe webhook event', ['type' => $event->type]);

        match ($event->type) {
            'payment_intent.succeeded'          => $this->handleIntentSucceeded($event->data->object),
            'payment_intent.payment_failed'     => $this->handleIntentFailed($event->data->object),
            'charge.refunded'                   => $this->handleRefund($event->data->object),
            default                             => null,
        };
    }

    private function handleIntentSucceeded(object $intent): void
    {
        $payment = Payment::where('gateway_checkout_id', $intent->id)->first();
        if (! $payment) {
            Log::warning('Stripe: no payment for intent', ['intent_id' => $intent->id]);
            return;
        }

        $payment->update(['raw_response' => (array) $intent]);
        $payment->markPaid($intent->id);
    }

    private function handleIntentFailed(object $intent): void
    {
        $payment = Payment::where('gateway_checkout_id', $intent->id)->first();
        $payment?->update(['status' => 'failed', 'raw_response' => (array) $intent]);
    }

    private function handleRefund(object $charge): void
    {
        // Match on charge's payment_intent
        $payment = Payment::where('gateway_checkout_id', $charge->payment_intent)->first();
        $payment?->update(['status' => 'refunded']);
        $payment?->order->update(['payment_status' => 'refunded', 'status' => 'refunded']);
    }
}
