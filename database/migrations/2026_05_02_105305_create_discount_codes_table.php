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
        Schema::create('discount_codes', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();                 // e.g. WELCOME10
            $table->string('description')->nullable();        // internal label
            $table->enum('type', ['percent', 'fixed']);       // percent off or fixed KES amount
            $table->decimal('value', 10, 2);                  // 10 = 10% or KES 10
            $table->decimal('minimum_order_amount', 10, 2)->default(0);
            $table->integer('uses_limit')->nullable();         // null = unlimited
            $table->integer('uses_count')->default(0);
            $table->integer('per_customer_limit')->default(1);
            $table->boolean('is_active')->default(true);
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
 
            $table->index(['code', 'is_active']);
            $table->index('expires_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('discount_codes');
    }
};
