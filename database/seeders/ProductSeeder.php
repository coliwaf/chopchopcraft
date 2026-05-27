<?php

namespace Database\Seeders;

use App\Models\Product;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name'        => 'Acacia Classic Board',
                'slug'        => 'acacia-classic-board',
                'base_price'  => 2500,
                'wood_type'   => 'Acacia',
                'finish'      => 'Oiled',
                'description' => 'Our signature Acacia board — durable, beautiful grain, perfect for everyday use.',
                'is_featured' => true,
                'variants'    => [
                    ['name' => 'Small – Acacia',  'sku' => 'ACA-S', 'size' => 'S', 'price' => 2500, 'stock' => 30],
                    ['name' => 'Medium – Acacia', 'sku' => 'ACA-M', 'size' => 'M', 'price' => 3000, 'stock' => 25],
                    ['name' => 'Large – Acacia',  'sku' => 'ACA-L', 'size' => 'L', 'price' => 3800, 'stock' => 15],
                ],
            ],
            [
                'name'        => 'Walnut Premium Board',
                'slug'        => 'walnut-premium-board',
                'base_price'  => 4500,
                'wood_type'   => 'Walnut',
                'finish'      => 'Waxed',
                'description' => 'Rich dark walnut with a deep, luxurious grain. A kitchen centrepiece.',
                'is_featured' => true,
                'variants'    => [
                    ['name' => 'Medium – Walnut', 'sku' => 'WAL-M', 'size' => 'M', 'price' => 4500, 'stock' => 12],
                    ['name' => 'Large – Walnut',  'sku' => 'WAL-L', 'size' => 'L', 'price' => 5500, 'stock' => 8],
                    ['name' => 'XL – Walnut',     'sku' => 'WAL-XL','size' => 'XL','price' => 7000, 'stock' => 4],
                ],
            ],
            [
                'name'        => 'Bamboo Eco Board',
                'slug'        => 'bamboo-eco-board',
                'base_price'  => 1800,
                'wood_type'   => 'Bamboo',
                'finish'      => 'Natural',
                'description' => 'Sustainable bamboo, gentle on knives, and naturally antimicrobial.',
                'is_featured' => false,
                'variants'    => [
                    ['name' => 'Small – Bamboo',  'sku' => 'BAM-S', 'size' => 'S', 'price' => 1800, 'stock' => 40],
                    ['name' => 'Medium – Bamboo', 'sku' => 'BAM-M', 'size' => 'M', 'price' => 2200, 'stock' => 35],
                    ['name' => 'Large – Bamboo',  'sku' => 'BAM-L', 'size' => 'L', 'price' => 2800, 'stock' => 20],
                ],
            ],
            [
                'name'        => 'Olive Wood Statement Board',
                'slug'        => 'olive-wood-statement-board',
                'base_price'  => 6000,
                'wood_type'   => 'Olive',
                'finish'      => 'Oiled',
                'description' => 'No two boards are alike. Unique swirling grain — a gift-worthy statement piece.',
                'is_featured' => true,
                'variants'    => [
                    ['name' => 'Medium – Olive', 'sku' => 'OLV-M', 'size' => 'M', 'price' => 6000, 'stock' => 6],
                    ['name' => 'Large – Olive',  'sku' => 'OLV-L', 'size' => 'L', 'price' => 7500, 'stock' => 4],
                ],
            ],
            [
                'name'        => 'Teak End-Grain Board',
                'slug'        => 'teak-end-grain-board',
                'base_price'  => 5200,
                'wood_type'   => 'Teak',
                'finish'      => 'Oiled',
                'description' => 'End-grain construction self-heals after every cut. Built to last a lifetime.',
                'is_featured' => false,
                'variants'    => [
                    ['name' => 'Medium – Teak', 'sku' => 'TEA-M', 'size' => 'M', 'price' => 5200, 'stock' => 10],
                    ['name' => 'Large – Teak',  'sku' => 'TEA-L', 'size' => 'L', 'price' => 6500, 'stock' => 7],
                ],
            ],
        ];

        foreach ($products as $sort => $data) {
            $product = Product::create([
                'name'             => $data['name'],
                'slug'             => $data['slug'],
                'base_price'       => $data['base_price'],
                'wood_type'        => $data['wood_type'],
                'finish'           => $data['finish'],
                'description'      => $data['description'],
                'long_description' => "<p>{$data['description']}</p><p>Handcrafted in Kenya by local artisans using sustainably sourced wood. Each board is finished with food-safe oil and inspected before shipping.</p>",
                'is_active'        => true,
                'is_featured'      => $data['is_featured'],
                'sort_order'       => $sort,
                'care_instructions'=> [
                    'Hand wash only — never put in a dishwasher.',
                    'Dry immediately after washing.',
                    'Apply food-safe mineral oil monthly to maintain the finish.',
                    'Store flat or upright, away from direct heat.',
                ],
            ]);

            foreach ($data['variants'] as $i => $v) {
                $product->variants()->create([
                    'name'                => $v['name'],
                    'sku'                 => $v['sku'],
                    'size'                => $v['size'],
                    'price_override'      => $v['price'],
                    'stock_qty'           => $v['stock'],
                    'low_stock_threshold' => 5,
                    'is_active'           => true,
                    'sort_order'          => $i,
                ]);
            }
        }
    }
}
