<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateCheckoutSettingsRequest;
use App\Models\SystemSetting;
use Inertia\Inertia;
use Inertia\Response;

class CheckoutSettingController extends Controller
{
    public function index(): Response
    {
        $settings = SystemSetting::getAllCheckoutSettings();
        $settings['blocked_dates'] = json_decode($settings['blocked_dates'] ?? '[]', true);
        $settings['blocked_days_of_week'] = json_decode($settings['blocked_days_of_week'] ?? '[]', true);

        return Inertia::render('Settings/Checkout', [
            'settings' => $settings,
        ]);
    }

    public function update(UpdateCheckoutSettingsRequest $request)
    {
        $validated = $request->validated();

        foreach ($validated as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }
            SystemSetting::set($key, $value);
        }

        return back()->with('success', 'Checkout system settings updated successfully!');
    }
}
