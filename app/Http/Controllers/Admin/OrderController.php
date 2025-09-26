<?php

namespace App\Http\Controllers\Admin;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $orderService) {}

    public function index(Request $request)
    {
        return $this->orderService->listForUser($request->user());
    }

    public function show(string $id)
    {
        $order = $this->orderService->find($id);
        if (!$order) return ResponseHelper::error('Order not found', [], 404);
        return ResponseHelper::success($order, 'Order fetched');
    }
}
