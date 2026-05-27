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

class SendWhatsAppStatusUpdate implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries   = 3;
    public int $backoff = 15;

    public function __construct(public readonly Order $order) {}

    public function handle(WhatsAppService $whatsapp): void
    {
        $this->order->load('customer');
        $sent = $whatsapp->sendStatusUpdate($this->order);

        if ($sent) {
            Log::info("WA status update sent for order {$this->order->order_number}, status: {$this->order->status->value}");
        }
    }

    public function failed(\Throwable $exception): void
    {
        Log::error("WA status update failed for order {$this->order->order_number}: {$exception->getMessage()}");
    }
}
