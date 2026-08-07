<?php

use App\Http\Controllers\Admin\Auth\LoginController as AdminLoginController;
use App\Http\Controllers\Admin\CategoryController as AdminCategoryController;
use App\Http\Controllers\Admin\CustomerController as AdminCustomerController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\DiscountController as AdminDiscountController;
use App\Http\Controllers\Admin\OrderController as AdminOrderController;
use App\Http\Controllers\Admin\ProductController as AdminProductController;
use App\Http\Controllers\Admin\StockController as AdminStockController;
use App\Http\Controllers\Admin\UserController as AdminUserController;
use App\Http\Controllers\Admin\BankSettingController;
use App\Http\Controllers\Admin\CheckoutSettingController;
use App\Http\Controllers\Admin\ScheduleSettingController;
use App\Http\Controllers\Admin\SizeGuideSettingController;
use App\Http\Controllers\Admin\ContentSettingController;
use App\Http\Controllers\Admin\TelegramSettingsController;
use App\Http\Controllers\Admin\DeliverySettingController;
use App\Http\Controllers\Admin\OutfitController as AdminOutfitController;
use App\Http\Controllers\SitemapController;
use App\Http\Controllers\TelegramWebhookController;
use App\Http\Controllers\Storefront\Auth\CustomerAuthController;
use App\Http\Controllers\Storefront\CartController;
use App\Http\Controllers\Storefront\CatalogController;
use App\Http\Controllers\Storefront\CheckoutController;
use App\Http\Controllers\Storefront\OrderTrackingController;
use App\Http\Controllers\Storefront\PushSubscriptionController;
use App\Http\Controllers\Storefront\ProfileController;
use App\Http\Controllers\Storefront\WishlistController;
use App\Http\Controllers\Admin\LoyaltyController;
use App\Http\Controllers\Admin\LoyaltySettingController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Storefront Routes (Blade + Alpine.js)
|--------------------------------------------------------------------------
*/
Route::get('/sitemap.xml', [SitemapController::class, 'index'])->name('sitemap');
Route::get('/', [CatalogController::class, 'home'])->name('storefront.home');
Route::get('/catalog', [CatalogController::class, 'index'])->name('catalog.index');
Route::get('/categories', [CatalogController::class, 'categoriesIndex'])->name('categories.index');
Route::get('/category/{slug}', [CatalogController::class, 'index'])->name('catalog.category');
// Product detail — routes by product_code (not slug) for clean SEO-friendly URLs
Route::get('/product/{product_code}', [CatalogController::class, 'show'])->name('catalog.show');
Route::get('/outfit/{slug}', [CatalogController::class, 'showOutfit'])->name('catalog.outfit');
Route::get('/outfits', [CatalogController::class, 'outfits'])->name('catalog.outfits');

// Language toggle
Route::post('/language/{lang}', function (string $lang) {
    $allowed = ['en', 'am'];
    if (in_array($lang, $allowed, true)) {
        session(['locale' => $lang]);
        app()->setLocale($lang);
    }
    return back();
})->name('language.switch');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::get('/cart/summary', [CartController::class, 'summary'])->name('cart.summary');
Route::post('/cart/add', [CartController::class, 'add'])->middleware('throttle:30,1')->name('cart.add');
Route::post('/cart/update/{id}', [CartController::class, 'update'])->name('cart.update');
Route::post('/cart/remove/{id}', [CartController::class, 'remove'])->name('cart.remove');
Route::post('/cart/discount', [CartController::class, 'applyDiscount'])->middleware('throttle:20,1')->name('cart.discount');

Route::get('/checkout', [CheckoutController::class, 'index'])->name('checkout.index');
Route::get('/checkout/slot-availability', [CheckoutController::class, 'checkSlotAvailability'])->name('checkout.slot-availability');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:5,1')->name('checkout.store');
Route::get('/order/{order_number}', [OrderTrackingController::class, 'show'])->name('order.confirmation');
Route::get('/track', [OrderTrackingController::class, 'showLookupForm'])->name('order.track');
Route::post('/track', [OrderTrackingController::class, 'lookup'])->middleware('throttle:10,1')->name('order.lookup');

// PWA Push Notification Subscriptions
Route::post('/push/subscribe', [PushSubscriptionController::class, 'subscribe'])
    ->middleware('throttle:10,1')
    ->name('push.subscribe');
Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'unsubscribe'])
    ->middleware('throttle:10,1')
    ->name('push.unsubscribe');

Route::middleware('guest')->group(function () {
    Route::get('/login', [CustomerAuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [CustomerAuthController::class, 'login'])->middleware('throttle:10,1');
    Route::get('/register', [CustomerAuthController::class, 'showRegisterForm'])->name('register');
    Route::post('/register', [CustomerAuthController::class, 'register'])->middleware('throttle:10,1');
});
Route::post('/logout', [CustomerAuthController::class, 'logout'])->middleware('auth')->name('logout');

// Customer Account Routes
Route::middleware('auth')->prefix('account')->name('account.')->group(function () {
    Route::get('/', [ProfileController::class, 'points'])->name('points');
    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::post('/update', [ProfileController::class, 'update'])->name('profile.update');
    Route::get('/orders', [ProfileController::class, 'orders'])->name('orders');
    Route::get('/wishlist', [ProfileController::class, 'wishlist'])->name('wishlist');
});

// Wishlist AJAX Routes
Route::post('/wishlist/toggle/{product}', [WishlistController::class, 'toggle'])
    ->middleware('auth')
    ->name('wishlist.toggle');
Route::get('/wishlist/status', [WishlistController::class, 'status'])
    ->name('wishlist.status');

/*
|--------------------------------------------------------------------------
| Telegram Webhook (public, CSRF-exempt, rate-limited)
|--------------------------------------------------------------------------
*/
Route::post('/telegram/webhook', [TelegramWebhookController::class, 'handle'])
    ->withoutMiddleware(\Illuminate\Foundation\Http\Middleware\VerifyCsrfToken::class)
    ->middleware(['throttle:60,1'])
    ->name('telegram.webhook');


/*
|--------------------------------------------------------------------------
| Admin Console Routes (Inertia.js + React)
|--------------------------------------------------------------------------
*/
Route::prefix('admin')->name('admin.')->group(function () {

    // Public Admin Auth
    Route::get('/login', [AdminLoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AdminLoginController::class, 'login'])->middleware('throttle:10,1');
    Route::post('/logout', [AdminLoginController::class, 'logout'])->middleware('auth')->name('logout');

    // Staff-Protected
    Route::middleware(['role.staff'])->group(function () {

        // Dashboard
        Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

        // Loyalty - Cashier Interface (accessible by staff + cashier + owner)
        Route::get('/loyalty', [LoyaltyController::class, 'index'])->name('loyalty.index');
        Route::get('/loyalty/search', [LoyaltyController::class, 'search'])->name('loyalty.search');
        Route::get('/loyalty/customer/{user}', [LoyaltyController::class, 'show'])->name('loyalty.show');
        Route::post('/loyalty/award', [LoyaltyController::class, 'award'])->name('loyalty.award');
        Route::post('/loyalty/redeem', [LoyaltyController::class, 'redeem'])->name('loyalty.redeem');

        // Products
        Route::get('/products', [AdminProductController::class, 'index'])->name('products.index');
        Route::get('/products/create', [AdminProductController::class, 'create'])->name('products.create');
        Route::post('/products', [AdminProductController::class, 'store'])->name('products.store');
        Route::get('/products/{product}/edit', [AdminProductController::class, 'edit'])->name('products.edit');
        Route::post('/products/{product}/update', [AdminProductController::class, 'update'])->name('products.update');
        Route::post('/products/{product}/quick-edit', [AdminProductController::class, 'quickUpdate'])->name('products.quick-update');
        Route::post('/products/bulk', [AdminProductController::class, 'bulk'])->name('products.bulk');
        Route::delete('/products/{product}', [AdminProductController::class, 'destroy'])->name('products.destroy');

        // Categories
        Route::get('/categories', [AdminCategoryController::class, 'index'])->name('categories.index');
        Route::post('/categories', [AdminCategoryController::class, 'store'])->name('categories.store');
        Route::post('/categories/{category}/update', [AdminCategoryController::class, 'update'])->name('categories.update');
        Route::post('/categories/reorder', [AdminCategoryController::class, 'reorder'])->name('categories.reorder');
        Route::delete('/categories/{category}', [AdminCategoryController::class, 'destroy'])->name('categories.destroy');

        // Outfits
        Route::get('/outfits', [AdminOutfitController::class, 'index'])->name('outfits.index');
        Route::post('/outfits', [AdminOutfitController::class, 'store'])->name('outfits.store');
        Route::post('/outfits/{outfit}/update', [AdminOutfitController::class, 'update'])->name('outfits.update');
        Route::delete('/outfits/{outfit}', [AdminOutfitController::class, 'destroy'])->name('outfits.destroy');

        // Hero Banners
        Route::get('/hero-banners', [\App\Http\Controllers\Admin\HeroBannerController::class, 'index'])->name('hero-banners.index');
        Route::post('/hero-banners', [\App\Http\Controllers\Admin\HeroBannerController::class, 'store'])->name('hero-banners.store');
        Route::post('/hero-banners/{banner}/update', [\App\Http\Controllers\Admin\HeroBannerController::class, 'update'])->name('hero-banners.update');
        Route::delete('/hero-banners/{banner}', [\App\Http\Controllers\Admin\HeroBannerController::class, 'destroy'])->name('hero-banners.destroy');

        // Orders
        Route::get('/orders', [AdminOrderController::class, 'index'])->name('orders.index');
        Route::get('/orders/{order}', [AdminOrderController::class, 'show'])->name('orders.show');
        Route::post('/orders/{order}/status', [AdminOrderController::class, 'updateStatus'])->name('orders.status');
        Route::post('/orders/{order}/mark-paid', [AdminOrderController::class, 'markPaid'])->name('orders.mark-paid');
        Route::get('/orders/{order}/invoice', [AdminOrderController::class, 'invoice'])->name('orders.invoice');

        // Stock
        Route::get('/stock', [AdminStockController::class, 'index'])->name('stock.index');
        Route::post('/stock/{variant}/adjust', [AdminStockController::class, 'adjust'])->name('stock.adjust');

        // Customers
        Route::get('/customers', [AdminCustomerController::class, 'index'])->name('customers.index');
        Route::get('/customers/{user}', [AdminCustomerController::class, 'show'])->name('customers.show');

        // Discounts
        Route::get('/discounts', [AdminDiscountController::class, 'index'])->name('discounts.index');
        Route::post('/discounts', [AdminDiscountController::class, 'store'])->name('discounts.store');
        Route::post('/discounts/{discount}/toggle', [AdminDiscountController::class, 'toggle'])->name('discounts.toggle');
        Route::delete('/discounts/{discount}', [AdminDiscountController::class, 'destroy'])->name('discounts.destroy');

        // Owner-only
        Route::middleware(['role.owner'])->group(function () {
            Route::get('/settings/telegram', [TelegramSettingsController::class, 'index'])->name('settings.telegram');
            Route::post('/settings/telegram/webhook', [TelegramSettingsController::class, 'setWebhook'])->name('settings.telegram.webhook');
            Route::delete('/settings/telegram/webhook', [TelegramSettingsController::class, 'deleteWebhook'])->name('settings.telegram.webhook.delete');
            Route::post('/settings/telegram/preview', [TelegramSettingsController::class, 'previewBroadcast'])->name('settings.telegram.preview');
            Route::post('/settings/telegram/broadcast', [TelegramSettingsController::class, 'broadcast'])->name('settings.telegram.broadcast');

            // Web Push Broadcast Panel
            Route::get('/push-broadcast', [\App\Http\Controllers\Admin\PushBroadcastController::class, 'index'])->name('push-broadcast.index');
            Route::post('/push-broadcast', [\App\Http\Controllers\Admin\PushBroadcastController::class, 'broadcast'])->name('push-broadcast.broadcast');

            Route::get('/settings/loyalty', [LoyaltySettingController::class, 'index'])->name('settings.loyalty');
            Route::post('/settings/loyalty', [LoyaltySettingController::class, 'update'])->name('settings.loyalty.update');

            // User & Staff Management
            Route::get('/users', [AdminUserController::class, 'index'])->name('users.index');
            Route::post('/users', [AdminUserController::class, 'store'])->name('users.store');
            Route::post('/users/{user}/role', [AdminUserController::class, 'updateRole'])->name('users.role');
            Route::delete('/users/{user}', [AdminUserController::class, 'destroy'])->name('users.destroy');

            // Dynamic Checkout, Banks & Schedule Settings
            Route::get('/settings/checkout', [CheckoutSettingController::class, 'index'])->name('settings.checkout');
            Route::post('/settings/checkout', [CheckoutSettingController::class, 'update'])->name('settings.checkout.update');

            Route::get('/settings/banks', [BankSettingController::class, 'index'])->name('settings.banks');
            Route::post('/settings/banks', [BankSettingController::class, 'store'])->name('settings.banks.store');
            Route::post('/settings/banks/{bank}/update', [BankSettingController::class, 'update'])->name('settings.banks.update');
            Route::post('/settings/banks/{bank}/toggle', [BankSettingController::class, 'toggle'])->name('settings.banks.toggle');
            Route::delete('/settings/banks/{bank}', [BankSettingController::class, 'destroy'])->name('settings.banks.destroy');

            Route::get('/settings/schedule', [ScheduleSettingController::class, 'index'])->name('settings.schedule');
            Route::post('/settings/schedule/slots', [ScheduleSettingController::class, 'storeSlot'])->name('settings.schedule.slots.store');
            Route::post('/settings/schedule/slots/{slot}/toggle', [ScheduleSettingController::class, 'toggleSlot'])->name('settings.schedule.slots.toggle');
            Route::delete('/settings/schedule/slots/{slot}', [ScheduleSettingController::class, 'destroySlot'])->name('settings.schedule.slots.destroy');
            Route::post('/settings/schedule/overrides', [ScheduleSettingController::class, 'storeOverride'])->name('settings.schedule.overrides.store');
            Route::delete('/settings/schedule/overrides/{override}', [ScheduleSettingController::class, 'destroyOverride'])->name('settings.schedule.overrides.destroy');

            // Size Guide Settings
            Route::get('/settings/size-guide', [SizeGuideSettingController::class, 'index'])->name('settings.size-guide');
            Route::post('/settings/size-guide/settings', [SizeGuideSettingController::class, 'updateSettings'])->name('settings.size-guide.settings');
            Route::post('/settings/size-guide', [SizeGuideSettingController::class, 'store'])->name('settings.size-guide.store');
            Route::post('/settings/size-guide/{sizeGuide}/update', [SizeGuideSettingController::class, 'update'])->name('settings.size-guide.update');
            Route::post('/settings/size-guide/{sizeGuide}/toggle', [SizeGuideSettingController::class, 'toggle'])->name('settings.size-guide.toggle');
            Route::delete('/settings/size-guide/{sizeGuide}', [SizeGuideSettingController::class, 'destroy'])->name('settings.size-guide.destroy');

            // Content & Policy Settings
            Route::get('/settings/content', [ContentSettingController::class, 'index'])->name('settings.content');
            Route::post('/settings/content', [ContentSettingController::class, 'update'])->name('settings.content.update');

            // Delivery & Shipping Rates Settings
            Route::get('/settings/delivery', [DeliverySettingController::class, 'index'])->name('settings.delivery');
            Route::post('/settings/delivery', [DeliverySettingController::class, 'store'])->name('settings.delivery.store');
            Route::post('/settings/delivery/{shippingRate}/update', [DeliverySettingController::class, 'update'])->name('settings.delivery.update');
            Route::post('/settings/delivery/{shippingRate}/toggle', [DeliverySettingController::class, 'toggle'])->name('settings.delivery.toggle');
            Route::delete('/settings/delivery/{shippingRate}', [DeliverySettingController::class, 'destroy'])->name('settings.delivery.destroy');
        });
    });
});
