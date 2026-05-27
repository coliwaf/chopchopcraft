<?php

namespace Database\Seeders;

use App\Models\DiscountCode;
use Illuminate\Database\Seeder;

class DiscountCodeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DiscountCode::insert([
            [
                'code'                  => 'WELCOME10',
                'description'           => '10% off for new customers',
                'type'                  => 'percent',
                'value'                 => 10,
                'minimum_order_amount'  => 0,
                'uses_limit'            => null,
                'uses_count'            => 0,
                'per_customer_limit'    => 1,
                'is_active'             => true,
                'starts_at'             => null,
                'expires_at'            => null,
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
            [
                'code'                  => 'SAVE500',
                'description'           => 'KES 500 off orders over KES 5000',
                'type'                  => 'fixed',
                'value'                 => 500,
                'minimum_order_amount'  => 5000,
                'uses_limit'            => 50,
                'uses_count'            => 0,
                'per_customer_limit'    => 1,
                'is_active'             => true,
                'starts_at'             => null,
                'expires_at'            => now()->addMonths(3),
                'created_at'            => now(),
                'updated_at'            => now(),
            ],
        ]);
    }
}
