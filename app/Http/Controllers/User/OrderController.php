<?php

namespace App\Http\Controllers\User;

use App\Helpers\ResponseHelper;
use App\Http\Controllers\Controller;
use App\Http\Requests\Order\StoreRequest;
use App\Services\OrderService;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function __construct(private OrderService $service) {}

    public function index(Request $request)
    {
        return $this->service->listForUser($request->user());
    }

    public function show(string $id)
    {
        $order = $this->service->find($id);
        if (!$order) return ResponseHelper::error('Order not found', [], 404);
        return ResponseHelper::success($order, 'Order fetched');
    }

    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $user = $request->user();
        return $this->service->createOrder($user, $data['items']);
    }
}
