<?php

namespace Database\Seeders;

use App\Models\SystemSetting;
use Illuminate\Database\Seeder;

class LoyaltySettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SystemSetting::set('loyalty_enabled', '1');
        SystemSetting::set('loyalty_birr_per_point', '100'); // 100 birr = 1 point
        SystemSetting::set('loyalty_point_value_cents', '100'); // 1 point = 1 birr = 100 cents
        SystemSetting::set('loyalty_min_redeem', '50'); // minimum 50 points to redeem
    }
}
