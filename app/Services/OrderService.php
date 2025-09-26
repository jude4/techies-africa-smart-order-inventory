<?php

namespace App\Services;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;
use App\Helpers\ResponseHelper as RH;

class OrderService
{
    /**
     * Create a new class instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Create an order with items. Returns ResponseHelper::success/error style array.
     */
    public function createOrder($user, array $items)
    {
        return DB::transaction(function () use ($user, $items) {
            $total = 0;
            $orderItems = [];

            foreach ($items as $item) {
                $product = Product::lockForUpdate()->find($item['product_id']);
                if (!$product) {
                    return ResponseHelper::error('Product not found: ' . $item['product_id'], [], 404);
                }

                if ($product->quantity < $item['quantity']) {
                    return ResponseHelper::error('Insufficient stock for product: ' . $product->sku, [], 400);
                }

                $lineTotal = $product->price * $item['quantity'];
                $total += $lineTotal;

                $orderItems[] = [
                    'product' => $product,
                    'quantity' => $item['quantity'],
                    'unit_price' => $product->price,
                ];
            }

            $discount = 0;
            if ($total > 500) {
                $discount = round($total * 0.1, 2);
                $total = round($total - $discount, 2);
            }

            do {
                $orderNumber = 'ORD-' . Str::upper(Str::random(8));
            } while (Order::where('order_number', $orderNumber)->exists());

            $order = Order::create([
                'order_number' => $orderNumber,
                'user_id' => $user->id,
                'status' => 'completed',
                'total_amount' => $total,
                'discount_amount' => $discount,
            ]);

            foreach ($orderItems as $oi) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $oi['product']->id,
                    'quantity' => $oi['quantity'],
                    'unit_price' => $oi['unit_price'],
                ]);

                $oi['product']->decrement('quantity', $oi['quantity']);

                StockTransaction::create([
                    'product_id' => $oi['product']->id,
                    'user_id' => $user->id,
                    'change_type' => 'out',
                    'quantity_changed' => $oi['quantity'],
                    'reason' => 'order:' . $order->id,
                ]);
            }

            return ResponseHelper::success($order->load('items.product'), 'Order created', 201);
        });
    }

    /**
     * List orders for a given user (paginated)
     */
    public function listForUser($user, $perPage = 15)
    {
        $orders = Order::with('items.product')->where('user_id', $user->id)->paginate($perPage);
        return RH::paginated($orders->items(), [
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
        ]);
    }

    /**
     * Find an order by id
     */
    public function find(string $id)
    {
        return Order::with('items.product')->find($id);
    }
}
