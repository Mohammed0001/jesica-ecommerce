<?php

namespace App\Http\Middleware;

use App\Support\Analytics;
use Closure;
use Illuminate\Http\Request;

/** Counts one "visit" per browser session on storefront page loads. */
class TrackVisit
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        try {
            if (
                $request->isMethod('GET')
                && !$request->ajax()
                && !$request->expectsJson()
                && !$request->is('admin', 'admin/*', 'up')
                && $response->isSuccessful()
                && !$this->isBot($request)
                && !session('analytics_visit_logged')
            ) {
                session(['analytics_visit_logged' => true]);
                Analytics::record(Analytics::VISIT);
            }
        } catch (\Throwable $e) {
            // never break the page for analytics
        }

        return $response;
    }

    private function isBot(Request $request): bool
    {
        $ua = strtolower((string) $request->userAgent());
        return $ua === '' || (bool) preg_match('/bot|crawl|spider|slurp|facebookexternalhit|preview|monitor|curl|wget/', $ua);
    }
}
