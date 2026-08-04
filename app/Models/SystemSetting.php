<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    use HasFactory;

    protected $fillable = [
        'key',
        'value',
    ];

    /**
     * Get setting value by key with caching.
     */
    public static function get(string $key, mixed $default = null): mixed
    {
        return Cache::remember("system_setting_{$key}", 3600, function () use ($key, $default) {
            $setting = static::where('key', $key)->first();
            return $setting ? $setting->value : $default;
        });
    }

    /**
     * Set/update setting value by key.
     */
    public static function set(string $key, mixed $value): void
    {
        static::updateOrCreate(['key' => $key], ['value' => (string) $value]);
        Cache::forget("system_setting_{$key}");
    }

    /**
     * Get all checkout settings array.
     */
    public static function getAllCheckoutSettings(): array
    {
        $keys = [
            'currency_symbol' => 'Birr',
            'schedule_enabled' => '0',
            'logistics_pickup' => '1',
            'pickup_location_name' => 'LULU Main Boutique Store',
            'pickup_location_link' => 'https://maps.google.com',
            'logistics_delivery_fixed' => '1',
            'delivery_fixed_fee' => '15.00',
            'logistics_delivery_rider' => '1',
            'rider_disclaimer' => 'Delivery fee will be paid directly to the courier rider upon delivery.',
            'payment_cod' => '1',
            'payment_transfer' => '1',
            'payment_paypal' => '1',
            'paypal_instructions' => 'Click "Place Order" below to proceed. You will be redirected to the secure PayPal portal to authorize payment.',
            'payment_card' => '1',
            'card_instructions' => 'Fill in your credit or debit card details below. All transaction data is securely processed.',
            'deposit_required' => '0',
            'deposit_percentage' => '50',
            'blocked_dates' => '[]',
            'blocked_days_of_week' => '[]',
        ];

        $settings = [];
        foreach ($keys as $k => $default) {
            $settings[$k] = static::get($k, $default);
        }

        return $settings;
    }
}
