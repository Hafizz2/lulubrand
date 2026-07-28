<?php

namespace Database\Seeders;

use App\Models\BankAccount;
use App\Models\PickupTime;
use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class CheckoutSystemSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Initial System Settings
        $defaultSettings = [
            'currency_symbol' => '$',
            'logistics_pickup' => '1',
            'pickup_location_name' => 'LULU Flagship Boutique — Suite 402, Fashion District',
            'pickup_location_link' => 'https://maps.google.com/?q=LULU+Boutique',
            'logistics_delivery_fixed' => '1',
            'delivery_fixed_fee' => '15.00',
            'logistics_delivery_rider' => '1',
            'rider_disclaimer' => 'Delivery fee is computed based on distance and paid directly to the courier rider upon delivery.',
            'payment_cod' => '1',
            'payment_transfer' => '1',
            'deposit_required' => '1',
            'deposit_percentage' => '50',
            'blocked_dates' => json_encode(['2026-12-25', '2026-01-01']),
            'blocked_days_of_week' => json_encode([0]), // Sunday blocked
        ];

        foreach ($defaultSettings as $k => $v) {
            SystemSetting::set($k, $v);
        }

        // 2. Initial Bank Accounts
        if (BankAccount::count() === 0) {
            BankAccount::create([
                'bank_name' => 'Commercial Bank of Ethiopia (CBE)',
                'account_number' => '1000123456789',
                'account_name' => 'LULU COUTURE PLC',
                'is_active' => true,
                'sort_order' => 1,
            ]);

            BankAccount::create([
                'bank_name' => 'Telebirr SuperApp',
                'account_number' => '0911223344',
                'account_name' => 'LULU COUTURE',
                'is_active' => true,
                'sort_order' => 2,
            ]);

            BankAccount::create([
                'bank_name' => 'Bank of Abyssinia (BOA)',
                'account_number' => '7788990011',
                'account_name' => 'LULU COUTURE PLC',
                'is_active' => true,
                'sort_order' => 3,
            ]);
        }

        // 3. Initial Pickup/Delivery Time Slots
        if (PickupTime::count() === 0) {
            $slots = [
                '09:00 AM - 12:00 PM',
                '12:00 PM - 03:00 PM',
                '03:00 PM - 06:00 PM',
                '06:00 PM - 08:00 PM',
            ];

            foreach ($slots as $idx => $label) {
                PickupTime::create([
                    'time_label' => $label,
                    'sort_order' => $idx + 1,
                    'is_active' => true,
                ]);
            }
        }
    }
}
