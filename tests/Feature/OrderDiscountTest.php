<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Product;
use Laravel\Sanctum\Sanctum;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderDiscountTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_total_has_discount_applied_when_over_500()
    {
        $user = User::factory()->create();

        // create expensive product
        $product = Product::create([
            'name' => 'Expensive Item',
            'sku' => 'EXP-001',
            'price' => 300,
            'quantity' => 10,
        ]);

        Sanctum::actingAs($user, [], 'sanctum');

        // order 2 units -> 600 total -> discount 10% -> 60 -> final 540
        $response = $this->postJson('/api/v1/admin/orders', [
            'items' => [
                ['product_id' => $product->id, 'quantity' => 2],
            ],
        ]);

        $response->assertStatus(201);
        $response->assertJsonFragment(['status' => 'success']);

        $order = $response->json('data');

        $this->assertEquals(540.00, (float)$order['total_amount']);
        $this->assertEquals(60.00, (float)$order['discount_amount']);
    }
}
