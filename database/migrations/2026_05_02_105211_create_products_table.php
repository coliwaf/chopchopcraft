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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique();
            $table->text('description')->nullable();
            $table->longText('long_description')->nullable();
            $table->decimal('base_price', 10, 2);
            $table->string('wood_type')->nullable();         // e.g. Walnut, Teak, Acacia
            $table->string('finish')->nullable();            // e.g. Oiled, Natural, Waxed
            $table->boolean('is_active')->default(true);
            $table->boolean('is_featured')->default(false);
            $table->integer('sort_order')->default(0);
            $table->json('care_instructions')->nullable();   // array of strings
            $table->json('dimensions')->nullable();          // { length, width, thickness, unit }
            $table->timestamps();
            $table->softDeletes();
 
            $table->index(['is_active', 'is_featured']);
            $table->index('sort_order');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
