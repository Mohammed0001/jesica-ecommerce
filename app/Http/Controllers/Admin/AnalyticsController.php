<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\Collection;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class AnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $total_orders = Order::count();
        $total_revenue = Order::sum('total_amount');
        $total_products = Product::count();
        $total_customers = User::where('role_id', 2)->count();

        // Top products by quantity ordered
        $top_products = OrderItem::select('product_id', DB::raw('SUM(quantity) as orders_count'), DB::raw('SUM(subtotal) as total_revenue'))
            ->groupBy('product_id')
            ->orderByDesc('orders_count')
            ->with('product')
            ->take(10)
            ->get();

        // Top collections by number of orders
        $top_collections = OrderItem::select('products.collection_id', DB::raw('SUM(order_items.quantity) as orders_count'))
            ->join('products', 'order_items.product_id', '=', 'products.id')
            ->groupBy('products.collection_id')
            ->orderByDesc('orders_count')
            ->take(10)
            ->get()
            ->map(function ($row) {
                $collection = Collection::find($row->collection_id);
                return (object) [
                    'collection' => $collection,
                    'orders_count' => (int) $row->orders_count,
                    'products_count' => $collection ? $collection->products()->count() : 0,
                ];
            });

        $recent_orders = Order::with('user')->latest()->take(10)->get();

        $avg_order_value = $total_orders > 0 ? round($total_revenue / $total_orders, 2) : 0;
        $completion_rate = $total_orders > 0 ? (Order::where('status', 'completed')->count() / $total_orders) * 100 : 0;

        $analytics = [
            'total_orders' => $total_orders,
            'total_revenue' => $total_revenue,
            'total_products' => $total_products,
            'total_customers' => $total_customers,
            'top_products' => $top_products,
            'top_collections' => $top_collections,
            'recent_orders' => $recent_orders,
            'avg_order_value' => $avg_order_value,
            'completion_rate' => $completion_rate,
            'popular_product' => optional($top_products->first())->product->title ?? 'N/A',
            'retention_rate' => 0,
            'avg_response_time' => 'N/A',
        ];

        return view('admin.analytics.index', compact('analytics'));
    }
}
