<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\CollectionController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\Admin\SettingsController;
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
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\PaymentWebhookController;
use App\Http\Controllers\NewsletterController;

// PUBLIC ROUTES - No authentication required
Route::get('/', [HomeController::class, 'index'])->name('home');

// Static pages - Public access
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/contact', [PageController::class, 'contact'])->name('contact');
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
});

// Admin settings
Route::prefix('admin')->name('admin.')->middleware(['auth'])->group(function () {
    Route::get('/settings', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::post('/settings', [SettingsController::class, 'update'])->name('settings.update');
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
    Route::post('special-orders/{specialOrder}/update-status', [AdminOrderController::class, 'updateSpecialOrderStatus'])->name('special-orders.update-status');

    // Client management
    Route::resource('clients', AdminClientController::class)->only(['index', 'show', 'update']);

    // Promo codes management
    Route::resource('promo-codes', Admin\AdminPromoCodeController::class)->except(['show']);

    // Analytics
    Route::get('analytics', [AnalyticsController::class, 'index'])->name('analytics.index');
    Route::get('analytics/sales', [AnalyticsController::class, 'sales'])->name('analytics.sales');
    Route::get('analytics/products', [AnalyticsController::class, 'products'])->name('analytics.products');
    Route::get('analytics/clients', [AnalyticsController::class, 'clients'])->name('analytics.clients');
    Route::get('analytics/ordering-frequency', [AnalyticsController::class, 'orderingFrequency'])->name('analytics.ordering-frequency');

    // Admin newsletter send
    Route::get('newsletter/send', [NewsletterController::class, 'showSendForm'])->name('newsletter.send.form');
    Route::post('newsletter/send', [NewsletterController::class, 'send'])->name('newsletter.send');
});

// Laravel Breeze Dashboard (keep for compatibility)
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
