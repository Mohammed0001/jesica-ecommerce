<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Support\Facades\Auth;

class AffiliateController extends Controller
{
    public function __construct()
    {
        $this->middleware(['auth', 'affiliate']);
    }

    /**
     * Affiliate dashboard: their promo codes, summary stats, and the orders
     * that used them.
     */
    public function index()
    {
        $affiliate = Auth::user();

        $promoCodes = $affiliate->promoCodes()->withCount('orders')->latest()->get();
        $promoCodeIds = $promoCodes->pluck('id');

        // Cancelled/draft orders never became real sales, so they are excluded
        // from the revenue and discount totals shown to the affiliate.
        $countedStatuses = collect(Order::STATUSES)->reject(fn ($status) => in_array($status, ['draft', 'cancelled']))->values();

        $ordersQuery = Order::whereIn('promo_code_id', $promoCodeIds);

        $totalOrders = (clone $ordersQuery)->count();
        $countedOrders = (clone $ordersQuery)->whereIn('status', $countedStatuses);
        $totalRevenue = (clone $countedOrders)->sum('total_amount');
        $totalDiscountGiven = (clone $countedOrders)->sum('discount_amount');

        $orders = Order::with(['promoCode', 'user'])
            ->whereIn('promo_code_id', $promoCodeIds)
            ->latest()
            ->paginate(20);

        return view('affiliate.dashboard', compact(
            'promoCodes',
            'orders',
            'totalOrders',
            'totalRevenue',
            'totalDiscountGiven'
        ));
    }
}
