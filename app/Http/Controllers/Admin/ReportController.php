<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;
use App\Helpers\ResponseHelper;
use Illuminate\Support\Facades\DB;

class ReportController extends Controller
{
    public function lowStock()
    {
        $products = Product::where('quantity', '<', 5)->get();
        return ResponseHelper::success($products, 'Low stock products');
    }

    public function salesSummary(Request $request)
    {
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');
        $numberOfOrders = Order::where('status', 'completed')->count();

        $topProducts = OrderItem::select('product_id', DB::raw('SUM(quantity) as total_sold'))
            ->groupBy('product_id')
            ->orderByDesc('total_sold')
            ->limit(3)
            ->with('product')
            ->get();

        return ResponseHelper::success([
            'total_revenue' => (float)$totalRevenue,
            'number_of_orders' => $numberOfOrders,
            'top_products' => $topProducts,
        ], 'Sales summary');
    }
}
