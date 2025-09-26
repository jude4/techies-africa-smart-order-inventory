<?php

namespace Database\Seeders;

use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Example: fetch first user as stock manager
        $adminUser = User::first();

        $products = [
            [
                'name' => 'Nintendo Switch OLED',
                'sku' => 'NSW-OLED-64',
                'price' => 349.99,
                'quantity' => 18,
            ],
            [
                'name' => 'PlayStation 5 Slim',
                'sku' => 'PS5-SLIM-1TB',
                'price' => 499.99,
                'quantity' => 10,
            ],
            [
                'name' => 'Xbox Series X',
                'sku' => 'XBOX-SX-1TB',
                'price' => 479.99,
                'quantity' => 8,
            ],
        ];

        foreach ($products as $product) {
            $newProduct = Product::create($product);

            // Log initial stock transaction if quantity > 0
            if ($newProduct->quantity > 0 && $adminUser) {
                $newProduct->stockTransactions()->create([
                    'user_id'           => $adminUser->id,
                    'change_type'       => 'in',
                    'quantity_changed'  => $newProduct->quantity,
                    'reason'            => 'Initial stock seeding',
                ]);
            }
        }
    }
}
