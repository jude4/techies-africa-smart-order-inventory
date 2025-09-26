<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\Product;
use App\Models\StockTransaction;
use App\Helpers\ResponseHelper;
use App\Http\Requests\Order\StoreRequest;
use App\Services\OrderService;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected OrderService $service;

    public function __construct(OrderService $service)
    {
        $this->service = $service;
    }

    public function index(Request $request)
    {
        $orders = Order::with('items.product')->paginate(15);
        return ResponseHelper::paginated($orders->items(), [
            'current_page' => $orders->currentPage(),
            'last_page' => $orders->lastPage(),
            'per_page' => $orders->perPage(),
            'total' => $orders->total(),
        ]);
    }

    public function show(string $id)
    {
        $order = Order::with('items.product')->find($id);
        if (!$order) return ResponseHelper::error('Order not found', [], 404);
        return ResponseHelper::success($order, 'Order fetched');
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();

        return $this->service->createOrder($user, $data['items']);
    }

    public function destroy(string $id)
    {
        $order = Order::find($id);
        if (!$order) return ResponseHelper::error('Order not found', [], 404);

        if ($order->status === 'cancelled') return ResponseHelper::error('Order already cancelled', [], 400);

        DB::transaction(function () use ($order) {
            foreach ($order->items as $item) {
                $product = Product::find($item->product_id);
                if ($product) {
                    $product->increment('quantity', $item->quantity);
                    StockTransaction::create([
                        'product_id' => $product->id,
                        'user_id' => $order->user_id,
                        'change_type' => 'in',
                        'quantity_changed' => $item->quantity,
                        'reason' => 'order_cancel:' . $order->id,
                    ]);
                }
            }

            $order->status = 'cancelled';
            $order->save();
        });

        return ResponseHelper::success($order->refresh(), 'Order cancelled');
    }
}
