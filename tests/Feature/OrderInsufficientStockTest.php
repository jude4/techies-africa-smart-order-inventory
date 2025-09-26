<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderInsufficientStockTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_with_insufficient_stock_is_rejected()
    {
        $user = User::factory()->create();

        $product = Product::create([
            'name' => 'Test Product',
            'sku' => 'TP-001',
            'price' => 100,
            'quantity' => 2,
        ]);

        Sanctum::actingAs($user, [], 'sanctum');

        $response = $this->postJson('/api/v1/admin/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 5],
            ],
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['status' => 'error']);
    }
}
