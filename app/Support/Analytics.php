<?php

namespace App\Support;

use App\Models\AnalyticsEvent;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * Lightweight first-party funnel tracking: visit -> add to cart ->
 * checkout started -> order placed. Never throws; analytics must not
 * be able to break a sale.
 */
class Analytics
{
    public const VISIT = 'visit';
    public const ADD_TO_CART = 'add_to_cart';
    public const CHECKOUT_STARTED = 'checkout_started';
    public const ORDER_PLACED = 'order_placed';

    public static function visitorId(): string
    {
        $id = session('analytics_vid');
        if (!$id) {
            $id = Str::random(32);
            session(['analytics_vid' => $id]);
        }
        return $id;
    }

    public static function record(string $type, ?int $productId = null, ?int $orderId = null, ?int $quantity = null): void
    {
        try {
            AnalyticsEvent::create([
                'visitor_id' => self::visitorId(),
                'type' => $type,
                'product_id' => $productId,
                'order_id' => $orderId,
                'quantity' => $quantity,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::warning('Analytics event failed: ' . $e->getMessage());
        }
    }

    /** Record once per visitor session (e.g. checkout_started). */
    public static function recordOnce(string $type): void
    {
        $key = 'analytics_once_' . $type;
        if (session($key)) {
            return;
        }
        session([$key => true]);
        self::record($type);
    }
}
