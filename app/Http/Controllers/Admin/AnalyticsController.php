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
            ->with('product.images')
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

        // ── Storefront funnel (from analytics_events) ──
        $days = in_array((int) $request->query('days'), [1, 7, 30, 90, 365]) ? (int) $request->query('days') : 30;
        $since = now()->subDays($days);
        $events = \App\Models\AnalyticsEvent::where('created_at', '>=', $since);

        $countVisitors = fn (string $type) => (clone $events)->where('type', $type)->distinct()->count('visitor_id');

        $visitors = $countVisitors('visit');
        $cartVisitors = $countVisitors('add_to_cart');
        $checkoutVisitors = $countVisitors('checkout_started');
        $orderVisitors = $countVisitors('order_placed');
        $totalAddsToCart = (int) (clone $events)->where('type', 'add_to_cart')->sum('quantity');

        // Visitors who added to cart but never placed an order
        $buyers = (clone $events)->where('type', 'order_placed')->select('visitor_id');
        $abandonedVisitors = (clone $events)->where('type', 'add_to_cart')
            ->whereNotIn('visitor_id', $buyers)
            ->distinct()->count('visitor_id');

        $mostAdded = \App\Models\AnalyticsEvent::where('analytics_events.created_at', '>=', $since)
            ->where('type', 'add_to_cart')
            ->whereNotNull('product_id')
            ->select('product_id', DB::raw('SUM(quantity) as units'), DB::raw('COUNT(DISTINCT visitor_id) as visitors'))
            ->groupBy('product_id')
            ->orderByDesc('units')
            ->with('product')
            ->take(10)
            ->get();

        $funnel = [
            'days' => $days,
            'visitors' => $visitors,
            'cart_visitors' => $cartVisitors,
            'total_adds' => $totalAddsToCart,
            'checkout_visitors' => $checkoutVisitors,
            'completed' => $orderVisitors,
            'abandoned' => $abandonedVisitors,
            'cart_rate' => $visitors > 0 ? round($cartVisitors / $visitors * 100, 1) : 0,
            'completion_rate' => $cartVisitors > 0 ? round($orderVisitors / $cartVisitors * 100, 1) : 0,
            'abandon_rate' => $cartVisitors > 0 ? round($abandonedVisitors / $cartVisitors * 100, 1) : 0,
            'most_added' => $mostAdded,
        ];

        // ── Revenue trend: one point per day across the selected range ──
        $revenueByDay = Order::where('created_at', '>=', $since->copy()->startOfDay())
            ->selectRaw('DATE(created_at) as day, SUM(total_amount) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $revenueLabels = [];
        $revenueData = [];
        // Cap points so a 365-day range stays readable
        $step = $days > 90 ? 7 : 1;
        for ($i = $days; $i >= 0; $i -= $step) {
            $date = now()->subDays($i);
            $revenueLabels[] = $date->format('M j');
            $sum = 0;
            for ($j = 0; $j < $step; $j++) {
                $sum += (float) ($revenueByDay[$date->copy()->addDays($j)->toDateString()] ?? 0);
            }
            $revenueData[] = round($sum, 2);
        }

        // ── Order status distribution ──
        $statusCounts = Order::selectRaw('status, COUNT(*) as total')
            ->groupBy('status')
            ->pluck('total', 'status');

        $analytics = [
            'revenue_chart' => ['labels' => $revenueLabels, 'data' => $revenueData],
            'status_chart' => [
                'labels' => $statusCounts->keys()->map(fn ($s) => ucfirst((string) $s))->values()->all(),
                'data' => $statusCounts->values()->map(fn ($c) => (int) $c)->all(),
            ],
            'funnel' => $funnel,
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
