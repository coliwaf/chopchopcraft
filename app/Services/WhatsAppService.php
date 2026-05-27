<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class WhatsAppService
{
    private string $token;
    private string $phoneNumberId;
    private string $apiVersion = 'v19.0';

    public function __construct()
    {
        $this->token         = config('services.whatsapp.token', '');
        $this->phoneNumberId = config('services.whatsapp.phone_number_id', '');
    }

    // ─── Send order confirmation ──────────────────────────────────────────────
    /**
     * Sends a structured order confirmation message.
     * Uses a free-form text message (works without approved templates for testing).
     * For production, switch to sendTemplate() below.
     */
    public function sendOrderConfirmation(Order $order): bool
    {
        $customer = $order->customer;
        $phone    = $customer->whatsapp_contact;

        if (! $phone) {
            Log::warning("WhatsApp: no phone for customer {$customer->id}, order {$order->order_number}");
            return false;
        }

        $itemLines = $order->items->map(fn($i) =>
            "  • {$i->product_name} ({$i->variant_name}) x{$i->qty} — KES " . number_format($i->line_total, 2)
        )->implode("\n");

        $message = implode("\n", [
            "🪵 *Chopping Board Shop*",
            "━━━━━━━━━━━━━━━━━━",
            "Hi {$customer->first_name}! Your order is confirmed ✅",
            "",
            "*Order:* {$order->order_number}",
            "*Status:* " . ucfirst($order->status->value),
            "",
            "*Items:*",
            $itemLines,
            "",
            "*Subtotal:* KES " . number_format($order->subtotal, 2),
            $order->discount_amount > 0
                ? "*Discount:* -KES " . number_format($order->discount_amount, 2) . "\n"
                : "",
            "*Shipping:* KES " . number_format($order->shipping_amount, 2),
            "*Total:* KES " . number_format($order->total, 2),
            "",
            "*Delivering to:* {$order->shipping_city}" . ($order->shipping_county ? ", {$order->shipping_county}" : ""),
            "",
            "We'll notify you when your order ships. Thank you! 🙏",
        ]);

        return $this->sendText($phone, $message);
    }

    // ─── Send shipping/status update ─────────────────────────────────────────
    public function sendStatusUpdate(Order $order): bool
    {
        $customer = $order->customer;
        $phone    = $customer->whatsapp_contact;

        if (! $phone) return false;

        $statusMessages = [
            'processing' => "🔨 Your order *{$order->order_number}* is being prepared.",
            'shipped'    => "🚚 Your order *{$order->order_number}* is on its way!" .
                           ($order->tracking_number ? "\nTracking: {$order->tracking_number}" : ""),
            'delivered'  => "📦 Your order *{$order->order_number}* has been delivered. Enjoy your board! 🪵",
            'cancelled'  => "❌ Your order *{$order->order_number}* has been cancelled. Contact us if this was a mistake.",
        ];

        $text = $statusMessages[$order->status->value] ?? null;

        if (! $text) return false;

        return $this->sendText($phone, $text);
    }

    // ─── Low-level: send text message ────────────────────────────────────────
    public function sendText(string $phone, string $message): bool
    {
        $phone = $this->normalisePhone($phone);

        $response = Http::withToken($this->token)
            ->post("https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'recipient_type'    => 'individual',
                'to'                => $phone,
                'type'              => 'text',
                'text'              => ['body' => $message, 'preview_url' => false],
            ]);

        if (! $response->successful()) {
            Log::error('WhatsApp send failed', [
                'phone'    => $phone,
                'status'   => $response->status(),
                'response' => $response->json(),
            ]);
            return false;
        }

        Log::info('WhatsApp message sent', ['to' => $phone, 'wa_id' => $response->json('messages.0.id')]);
        return true;
    }

    // ─── Template message (for production — requires Meta approval) ───────────
    /**
     * Example: sendTemplate($phone, 'order_confirmation', 'en', [
     *     ['type' => 'text', 'text' => $order->order_number],
     *     ['type' => 'text', 'text' => 'KES ' . $order->total],
     * ]);
     */
    public function sendTemplate(string $phone, string $templateName, string $langCode, array $bodyParams): bool
    {
        $phone = $this->normalisePhone($phone);

        $response = Http::withToken($this->token)
            ->post("https://graph.facebook.com/{$this->apiVersion}/{$this->phoneNumberId}/messages", [
                'messaging_product' => 'whatsapp',
                'to'                => $phone,
                'type'              => 'template',
                'template'          => [
                    'name'       => $templateName,
                    'language'   => ['code' => $langCode],
                    'components' => [[
                        'type'       => 'body',
                        'parameters' => $bodyParams,
                    ]],
                ],
            ]);

        return $response->successful();
    }

    // ─── Helpers ─────────────────────────────────────────────────────────────
    private function normalisePhone(string $phone): string
    {
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '0'))    return '254' . substr($phone, 1);
        if (str_starts_with($phone, '+'))    return ltrim($phone, '+');

        return $phone;
    }
}
