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
        Schema::create('payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->enum('gateway', ['mpesa', 'stripe', 'paypal']);
            $table->string('gateway_ref')->nullable()->unique(); // M-Pesa transaction ID etc.
            $table->string('gateway_checkout_id')->nullable();   // STK checkout request ID
            $table->decimal('amount', 10, 2);
            $table->string('currency', 3)->default('KES');
            $table->enum('status', ['pending', 'paid', 'failed', 'refunded'])->default('pending');
            $table->json('raw_response')->nullable();            // full gateway response for debugging
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();
 
            $table->index(['order_id', 'status']);
            $table->index('gateway_ref');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payments');
    }
};
