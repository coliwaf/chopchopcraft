<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('order_number')->unique();         // e.g. CB-2024-00042
            $table->foreignId('customer_id')->constrained()->restrictOnDelete();
            $table->foreignId('discount_code_id')->nullable()->constrained()->nullOnDelete();
 
            // Financials
            $table->decimal('subtotal', 10, 2);
            $table->decimal('discount_amount', 10, 2)->default(0);
            $table->decimal('shipping_amount', 10, 2)->default(0);
            $table->decimal('total', 10, 2);
 
            // Status
            $table->enum('status', [
                'pending',       // just placed
                'confirmed',     // payment received
                'processing',    // being packed
                'shipped',       // in transit
                'delivered',     // confirmed delivery
                'cancelled',
                'refunded',
            ])->default('pending');
 
            $table->enum('payment_method', [
                'mpesa', 'stripe', 'paypal', 'whatsapp', 'unpaid'
            ])->default('unpaid');
 
            $table->enum('payment_status', [
                'pending', 'paid', 'failed', 'refunded', 'partial'
            ])->default('pending');
 
            // Shipping snapshot (denormalised, customer address may change)
            $table->string('shipping_name');
            $table->string('shipping_phone')->nullable();
            $table->string('shipping_address_line1');
            $table->string('shipping_address_line2')->nullable();
            $table->string('shipping_city');
            $table->string('shipping_county')->nullable();
            $table->string('shipping_postal_code')->nullable();
            $table->string('shipping_country')->default('KE');
 
            // Comms
            $table->timestamp('whatsapp_confirmation_sent_at')->nullable();
            $table->timestamp('whatsapp_order_sent_at')->nullable(); // for WA-initiated orders
            $table->text('internal_notes')->nullable();
            $table->string('tracking_number')->nullable();
 
            $table->timestamps();
            $table->softDeletes();
 
            $table->index(['status', 'payment_status']);
            $table->index('customer_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
