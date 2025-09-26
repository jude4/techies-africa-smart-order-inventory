<?php

namespace Database\Seeders;

use App\Models\Order;
use App\Models\OrderItem;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class OrderSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create 10 orders, each with 1–5 order items
        Order::factory(10)
            ->create()
            ->each(function ($order) {
                OrderItem::factory(rand(1, 5))->create([
                    'order_id' => $order->id,
                ]);
            });
    }
}
