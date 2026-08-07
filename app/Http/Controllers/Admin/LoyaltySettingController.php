<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\LoyaltyService;
use Illuminate\Http\Request;
use Inertia\Inertia;

class LoyaltySettingController extends Controller
{
    public function index()
    {
        $settings = app(LoyaltyService::class)->getSettings();
        return Inertia::render('Settings/Loyalty', ['settings' => $settings]);
    }
    
    public function update(Request $request)
    {
        $request->validate([
            'loyalty_enabled' => 'required|boolean',
            'loyalty_birr_per_point' => 'required|integer|min:1',
            'loyalty_point_value_cents' => 'required|integer|min:1',
            'loyalty_min_redeem' => 'required|integer|min:1',
        ]);
        
        SystemSetting::set('loyalty_enabled', $request->loyalty_enabled ? '1' : '0');
        SystemSetting::set('loyalty_birr_per_point', (string) $request->loyalty_birr_per_point);
        SystemSetting::set('loyalty_point_value_cents', (string) $request->loyalty_point_value_cents);
        SystemSetting::set('loyalty_min_redeem', (string) $request->loyalty_min_redeem);
        
        return back()->with('success', 'Loyalty settings updated.');
    }
}
