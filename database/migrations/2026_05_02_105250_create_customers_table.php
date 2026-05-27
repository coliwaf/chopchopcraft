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
        Schema::create('customers', function (Blueprint $table) {
            $table->id();
                        $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('email')->unique();
            $table->string('phone')->nullable();              // in E.164 format, e.g. +254712345678
            $table->string('whatsapp_number')->nullable();    // if different from phone
            $table->text('notes')->nullable();                // internal CRM notes
 
            // Shipping address (primary)
            $table->string('address_line1')->nullable();
            $table->string('address_line2')->nullable();
            $table->string('city')->nullable();
            $table->string('county')->nullable();             // Kenya county
            $table->string('postal_code')->nullable();
            $table->string('country')->default('KE');
 
            $table->enum('source', [
                'website', 'whatsapp', 'referral', 'instagram', 'other'
            ])->default('website');
 
            $table->boolean('marketing_opt_in')->default(false);
            $table->timestamp('last_ordered_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
 
            $table->index('email');
            $table->index('phone');
            $table->index('last_ordered_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customers');
    }
};
