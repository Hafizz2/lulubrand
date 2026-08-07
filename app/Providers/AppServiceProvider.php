<?php

namespace App\Providers;

use App\Events\OrderPlaced;
use App\Events\OrderStatusChanged;
use App\Listeners\AwardLoyaltyPoints;
use App\Listeners\SendLowStockAlert;
use App\Listeners\SendOrderPlacedNotification;
use App\Listeners\SendStatusChangedNotification;
use App\Services\TelegramService;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Bind TelegramService as a singleton so it reads config once
        $this->app->singleton(TelegramService::class, fn() => new TelegramService());
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Telegram Notification Listeners are auto-discovered by Laravel 11
    }
}
