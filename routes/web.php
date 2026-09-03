<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\DeliveryAreasController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\SpecialOrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\SearchController;
use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\AdminProductController;
use App\Http\Controllers\Admin\AdminCollectionController;
use App\Http\Controllers\Admin\AdminOrderController;
use App\Http\Controllers\Admin\AdminClientController;
use App\Http\Controllers\Admin\AnalyticsController;
use App\Http\Controllers\Admin\AdminPromoCodeController;
use App\Http\Controllers\Admin\AdminSizeChartController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\NewsletterController;
use App\Http\Controllers\ShipmentController;
use App\Http\Controllers\GuestCheckoutController;

// PUBLIC ROUTES - No authentication required
Route::get('/', [HomeController::class, 'index'])->name('home');

// Static pages - Public access
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
// Additional static pages
Route::get('/customer-services', [PageController::class, 'customerServices'])->name('pages.customer-services');
Route::get('/faqs', [PageController::class, 'faqs'])->name('pages.faqs');
Route::get('/track-order', [PageController::class, 'trackOrder'])->name('pages.track-order');
Route::get('/request-return', [PageController::class, 'requestReturn'])->name('pages.request-return');
Route::get('/the-legacy', [PageController::class, 'legacy'])->name('pages.legacy');
Route::get('/legal', [PageController::class, 'legal'])->name('pages.legal');
Route::get('/privacy', [PageController::class, 'privacy'])->name('pages.privacy');
Route::post('/contact', [PageController::class, 'contactSubmit'])->name('contact.submit');

// Search - Public access
Route::get('/search', [SearchController::class, 'index'])->name('search');
Route::get('/search/suggestions', [SearchController::class, 'suggestions'])->name('search.suggestions');

// Collections - Public access
Route::get('/collections', [CollectionController::class, 'index'])->name('collections.index');
Route::get('/collections/{collection:slug}', [CollectionController::class, 'show'])->name('collections.show');

// Products - Public access
Route::get('/products', [ProductController::class, 'index'])->name('products.index');
Route::get('/products/{product:slug}', [ProductController::class, 'show'])->name('products.show');

// Guest Checkout - No login required
Route::prefix('checkout/guest')->name('guest.checkout.')->group(function () {
    Route::get('/', [GuestCheckoutController::class, 'show'])->name('show');
    Route::post('/process', [GuestCheckoutController::class, 'process'])->name('process');
    Route::get('/confirmation/{order}', [GuestCheckoutController::class, 'confirmation'])->name('confirmation');
    Route::get('/delivery-fee', [GuestCheckoutController::class, 'getDeliveryFee'])->name('delivery-fee');
});

// Cart routes - Public access (session-based)
Route::prefix('cart')->name('cart.')->group(function () {
    Route::get('/', [CartController::class, 'index'])->name('index');
    Route::post('/add', [CartController::class, 'add'])->name('add');
    Route::post('/add/{product}', [CartController::class, 'addFromProductPage'])->name('add.product');
    Route::post('/update', [CartController::class, 'update'])->name('update');
    Route::post('/remove', [CartController::class, 'remove'])->name('remove');
    Route::post('/clear', [CartController::class, 'clear'])->name('clear');
    Route::get('/count', [CartController::class, 'count'])->name('count');
    Route::post('/apply-promo', [CartController::class, 'applyPromo'])->name('apply-promo');
    Route::post('/remove-promo', [CartController::class, 'removePromo'])->name('remove-promo');
});

// Admin settings
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
    Route::post('/settings/hero-image', [SettingsController::class, 'uploadHero'])->name('settings.hero.upload');
    Route::post('/settings/hero-image/reset', [SettingsController::class, 'resetHero'])->name('settings.hero.reset');
    // newsletter send form (admin-only routes are registered in the admin group below)
});

// AUTHENTICATED USER ROUTES
Route::middleware('auth')->group(function () {
    // Checkout routes - Require authentication
    Route::prefix('checkout')->name('checkout.')->group(function () {
        Route::get('/', [CheckoutController::class, 'index'])->name('index');
        Route::post('/process', [CheckoutController::class, 'process'])->name('process');
        Route::get('/success', [CheckoutController::class, 'success'])->name('success');
        Route::get('/cancel', [CheckoutController::class, 'cancel'])->name('cancel');
    });
    // AJAX: per-area delivery fee lookup (no CSRF needed, GET only)
    Route::get('/checkout/delivery-fee', [CheckoutController::class, 'getDeliveryFee'])->name('checkout.delivery-fee');

    // Order routes - User's own orders
    Route::prefix('orders')->name('orders.')->group(function () {
        Route::get('/', [OrderController::class, 'index'])->name('index');
        Route::get('/{order}', [OrderController::class, 'show'])->name('show');
        Route::post('/{order}/cancel', [OrderController::class, 'cancel'])->name('cancel');
        Route::get('/{order}/reorder', [OrderController::class, 'reorder'])->name('reorder');
        Route::get('/{order}/invoice', [OrderController::class, 'invoice'])->name('invoice');
    });

    // Special orders - Authenticated users only
    Route::prefix('special-orders')->name('special-orders.')->group(function () {
        Route::get('/', [SpecialOrderController::class, 'index'])->name('index');
        Route::get('/create', [SpecialOrderController::class, 'create'])->name('create');
        Route::post('/', [SpecialOrderController::class, 'store'])->name('store');
        Route::get('/{specialOrder}', [SpecialOrderController::class, 'show'])->name('show');
    });

    // User profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    // Address management on profile
    Route::post('/profile/address', [ProfileController::class, 'storeAddress'])->name('profile.address.store');
    Route::delete('/profile/address/{address}', [ProfileController::class, 'destroyAddress'])->name('profile.address.destroy');
});

// Public newsletter routes
Route::post('/newsletter/subscribe', [NewsletterController::class, 'subscribe'])->name('newsletter.subscribe');
Route::get('/newsletter/unsubscribe/{id}/{token}', [NewsletterController::class, 'unsubscribe'])->name('newsletter.unsubscribe');

// Public tracking routes
Route::get('/track-shipment', [ShipmentController::class, 'track'])->name('tracking.search');
Route::get('/track-shipment/{trackingNumber}', [ShipmentController::class, 'track'])->name('tracking.show');

// ADMIN ROUTES - Require admin authentication
Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [AdminDashboardController::class, 'index'])->name('dashboard');

    // Product management
    Route::resource('products', AdminProductController::class)->parameters([
        'products' => 'product:slug'
    ]);
    Route::post('products/{product:slug}/toggle-visibility', [AdminProductController::class, 'toggleVisibility'])->name('products.toggle-visibility');

    // Collection management
    Route::resource('collections', AdminCollectionController::class)->parameters([
        'collections' => 'collection:slug'
    ]);
    Route::post('collections/{collection:slug}/toggle-visibility', [AdminCollectionController::class, 'toggleVisibility'])->name('collections.toggle-visibility');

    // Order management
    Route::resource('orders', AdminOrderController::class)->only(['index', 'show', 'update']);
    // Printable order invoice
    Route::get('orders/{order}/print', [\App\Http\Controllers\Admin\AdminOrderController::class, 'print'])->name('orders.print');
    Route::post('orders/{order}/update-status', [AdminOrderController::class, 'updateStatus'])->name('orders.update-status');

    // Special order management
    Route::get('special-orders', [AdminOrderController::class, 'specialOrders'])->name('special-orders.index');
    Route::get('special-orders/{specialOrder}', [AdminOrderController::class, 'showSpecialOrder'])->name('special-orders.show');
    // Accept both POST and PATCH for status updates (some views use method spoofing)
    Route::match(['post', 'patch'], 'special-orders/{specialOrder}/update-status', [AdminOrderController::class, 'updateSpecialOrderStatus'])->name('special-orders.update-status');
    // Backwards-compatible route name used by some admin views (camelCase)
    Route::patch('special-orders/{specialOrder}/update-notes', [AdminOrderController::class, 'updateSpecialOrderStatus'])->name('special-orders.updateNotes');
    // Allow admins to delete special orders
    Route::delete('special-orders/{specialOrder}', [AdminOrderController::class, 'destroySpecialOrder'])->name('special-orders.destroy');

    // Client management
    Route::resource('clients', AdminClientController::class)->only(['index', 'show', 'update']);

    // Promo codes management
    Route::resource('promo-codes', AdminPromoCodeController::class)->except(['show']);

    // Size charts management
    Route::resource('size-charts', AdminSizeChartController::class)->except(['show', 'edit', 'update']);

    // Analytics
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/sales', [AnalyticsController::class, 'sales'])->name('analytics.sales');

    // Delivery areas (per-city fees)
    Route::resource('delivery-areas', DeliveryAreasController::class)->parameters([
        'delivery-areas' => 'deliveryArea',
    ]);
    // Admin newsletter send
    Route::get('newsletter/send', [NewsletterController::class, 'showSendForm'])->name('newsletter.send.form');
    Route::post('newsletter/send', [NewsletterController::class, 'send'])->name('newsletter.send');
    // Admin newsletter logs viewer
    Route::get('newsletter/logs', [\App\Http\Controllers\Admin\AdminNewsletterController::class, 'showLogs'])->name('newsletter.logs');

    // Shipment management
    Route::prefix('shipments')->name('shipments.')->group(function () {
        Route::get('/', [ShipmentController::class, 'index'])->name('index');
        Route::get('/{shipment}', [ShipmentController::class, 'show'])->name('show');
        Route::post('/create/{order}', [ShipmentController::class, 'create'])->name('create');
        Route::post('/{shipment}/cancel', [ShipmentController::class, 'cancel'])->name('cancel');
        Route::get('/{shipment}/print-label', [ShipmentController::class, 'printLabel'])->name('print-label');
        Route::post('/{shipment}/update-tracking', [ShipmentController::class, 'updateTracking'])->name('update-tracking');
        Route::post('/request-pickup', [ShipmentController::class, 'requestPickup'])->name('request-pickup');
    });
});

// NOTE: a stray copy of the admin newsletter routes used to sit here, outside
// the auth+admin group. Because it was registered later it also took over the
// `newsletter.send*` route names, so admin links pointed at an unauthenticated
// /newsletter/send URL. The canonical definitions live in the admin group above.

// BOSTA webhooks
Route::post('/webhooks/bosta', [ShipmentController::class, 'webhook'])
    ->name('webhooks.bosta')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);
Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Auth routes
require __DIR__.'/auth.php';

// Payment webhooks (Paymob)
Route::post('/payments/webhook/paymob', [PaymentWebhookController::class, 'handlePaymob'])
    ->name('payments.webhook.paymob')
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class]);

// Payment status polling (authenticated) - used by iframe/polling UI
Route::get('/payments/status/{order}', [PaymentWebhookController::class, 'checkStatus'])
    ->name('payments.status')
    ->middleware(['auth']);

// --- Artisan Utility Routes (For Hosting Environments without SSH) ---
Route::prefix('dev/cmd')->middleware(['auth', 'admin'])->group(function () {
    Route::get('/optimize', function () {
        \Illuminate\Support\Facades\Artisan::call('optimize:clear');
        \Illuminate\Support\Facades\Artisan::call('optimize');
        return 'Optimized successfully. <br> <a href="/">Go Home</a>';
    });
    
    // Reports what actually happened. The previous version always claimed
    // success, which hid both failures and a no-op "Nothing to migrate".
    Route::get('/migrate', function () {
        try {
            \Illuminate\Support\Facades\Artisan::call('migrate', ['--force' => true]);
            $output = \Illuminate\Support\Facades\Artisan::output();
        } catch (\Throwable $e) {
            return response('<h3>Migration failed</h3><pre>' . e($e->getMessage()) . '</pre>', 500);
        }

        return 'Migrate finished. <br><pre>' . e($output) . '</pre><a href="/">Go Home</a>';
    });

    // Shows which migrations have run and which are still pending, so a
    // schema that is behind the deployed code is visible before it bites.
    Route::get('/migrate-status', function () {
        \Illuminate\Support\Facades\Artisan::call('migrate:status');

        return '<pre>' . e(\Illuminate\Support\Facades\Artisan::output()) . '</pre><a href="/">Go Home</a>';
    });
    
    // Reports what this server actually looks like: PHP limits, the extension
    // image handling needs, pending migrations, and every table and column
    // product creation touches -- ending in a dry run of the create itself
    // that is always rolled back. Read-only; nothing is committed.
    Route::get('/diagnose', function () {
        $out = [];

        $out['PHP'] = [
            'version'             => PHP_VERSION,
            'max_execution_time'  => ini_get('max_execution_time'),
            'max_input_time'      => ini_get('max_input_time'),
            'memory_limit'        => ini_get('memory_limit'),
            'post_max_size'       => ini_get('post_max_size'),
            'upload_max_filesize' => ini_get('upload_max_filesize'),
            'gd_loaded'           => extension_loaded('gd') ? 'yes' : 'NO',
        ];

        try {
            $out['DB'] = [
                'connected' => 'yes',
                'driver'    => \Illuminate\Support\Facades\DB::connection()->getDriverName(),
                'database'  => \Illuminate\Support\Facades\DB::connection()->getDatabaseName(),
            ];
        } catch (\Throwable $e) {
            $out['DB'] = ['connected' => 'NO -- ' . $e->getMessage()];
        }

        foreach (['products', 'product_colors', 'product_images', 'size_charts',
                  'collections', 'sessions', 'cache'] as $table) {
            $out['Tables'][$table] = \Illuminate\Support\Facades\Schema::hasTable($table) ? 'ok' : 'MISSING';
        }

        foreach (['sale_price', 'sale_starts_at', 'sale_ends_at', 'is_sold_out',
                  'deleted_at', 'size_chart_id', 'story', 'currency', 'quantity'] as $column) {
            $out['products columns'][$column] =
                \Illuminate\Support\Facades\Schema::hasColumn('products', $column) ? 'ok' : 'MISSING';
        }

        \Illuminate\Support\Facades\Artisan::call('migrate:status');
        $out['Migrations'] = \Illuminate\Support\Facades\Artisan::output();

        $collectionId = \App\Models\Collection::value('id');
        $out['Collections exist'] = $collectionId ? 'yes' : 'NO -- create a collection first';

        // Exactly what store() does, then forced to roll back.
        try {
            \Illuminate\Support\Facades\DB::transaction(function () use ($collectionId) {
                $product = \App\Models\Product::create([
                    'collection_id'     => $collectionId,
                    'title'             => '__diagnose__ ' . uniqid(),
                    'description'       => 'diagnostic dry run',
                    'price'             => 1,
                    'sale_price'        => null,
                    'sale_starts_at'    => null,
                    'sale_ends_at'      => null,
                    'currency'          => array_key_first((array) config('currencies.rates')),
                    'quantity'          => 1,
                    'visible'           => false,
                    'is_one_of_a_kind'  => false,
                    'is_sold_out'       => false,
                ]);

                \App\Models\ProductColor::where('product_id', $product->id)
                    ->whereNotIn('id', [0])
                    ->delete();
                $product->load('colors');

                throw new \RuntimeException('__ROLLBACK__');
            });
        } catch (\Throwable $e) {
            $out['Dry-run create'] = $e->getMessage() === '__ROLLBACK__'
                ? 'PASSED -- rolled back cleanly'
                : 'FAILED -- ' . get_class($e) . ': ' . $e->getMessage();
        }

        $out['storage/logs writable'] = is_writable(storage_path('logs')) ? 'yes' : 'NO';

        return '<pre>' . e(print_r($out, true)) . '</pre>';
    });

    Route::get('/storage-link', function () {
        \Illuminate\Support\Facades\Artisan::call('storage:link');
        return 'Storage linked successfully. <br> <a href="/">Go Home</a>';
    });
    
    Route::get('/clear', function () {
        \Illuminate\Support\Facades\Artisan::call('cache:clear');
        \Illuminate\Support\Facades\Artisan::call('config:clear');
        \Illuminate\Support\Facades\Artisan::call('route:clear');
        \Illuminate\Support\Facades\Artisan::call('view:clear');
        return 'Caches cleared successfully. <br> <a href="/">Go Home</a>';
    });

    Route::get('/seed/cairo-areas', function () {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'CairoAreasSeeder', '--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        return 'Cairo areas seeded. <br><pre>' . e($output) . '</pre><a href="/">Go Home</a>';
    });

    Route::get('/seed/regions', function () {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'RegionSeeder', '--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        return 'Regions seeded. <br><pre>' . e($output) . '</pre><a href="/">Go Home</a>';
    });

    Route::get('/seed/all', function () {
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'RegionSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'CairoAreasSeeder', '--force' => true]);
        \Illuminate\Support\Facades\Artisan::call('db:seed', ['--class' => 'BostaCitySeeder', '--force' => true]);
        $output = \Illuminate\Support\Facades\Artisan::output();
        return 'All seeders ran. <br><pre>' . e($output) . '</pre><a href="/">Go Home</a>';
    });
});
