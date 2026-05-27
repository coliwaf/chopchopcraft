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
        Schema::create('product_variants', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name');                          // e.g. "Small – Acacia", "Large – Walnut"
            $table->string('sku')->unique();
            $table->decimal('price_override', 10, 2)->nullable(); // null = use product base_price
            $table->integer('stock_qty')->default(0);
            $table->integer('low_stock_threshold')->default(5);
            $table->string('size')->nullable();              // e.g. "Small", "Medium", "Large", "XL"
            $table->decimal('weight_kg', 6, 3)->nullable();
            $table->json('dimensions')->nullable();          // override per-variant
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
 
            $table->index(['product_id', 'is_active']);
            $table->index('stock_qty');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('product_variants');
    }
};
