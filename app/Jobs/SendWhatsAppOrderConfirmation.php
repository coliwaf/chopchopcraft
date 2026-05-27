<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class SendWhatsAppOrderConfirmation implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 30; // seconds between retries

    public function __construct(public readonly Order $order) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        // Don't re-send if already sent
        if ($this->order->whatsapp_confirmation_sent_at) {
            Log::info("WA confirmation already sent for order {$this->order->order_number}, skipping.");
            return;
        }

        $this->order->load(['items', 'customer']);

        $sent = $whatsapp->sendOrderConfirmation($this->order);

        if ($sent) {
            $this->order->update(['whatsapp_confirmation_sent_at' => now()]);
            Log::info("WA confirmation sent for order {$this->order->order_number}");
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("WA confirmation job failed for order {$this->order->order_number}: {$exception->getMessage()}");
    }
}
