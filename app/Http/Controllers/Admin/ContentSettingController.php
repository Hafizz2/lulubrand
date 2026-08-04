<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ContentSettingController extends Controller
{
    /**
     * Show the content/policy admin settings page.
     */
    public function index(): Response
    {
        return Inertia::render('Settings/Content', [
            'settings' => [
                'terms_and_conditions' => SystemSetting::get('terms_and_conditions', $this->defaultTerms()),
                'privacy_policy'       => SystemSetting::get('privacy_policy', $this->defaultPrivacy()),
                'announcement_message' => SystemSetting::get('announcement_message', ''),
                'footer_address_line1' => SystemSetting::get('footer_address_line1', 'Bole Medhanialem'),
                'footer_address_line2' => SystemSetting::get('footer_address_line2', 'Edna Mall Area'),
                'footer_address_line3' => SystemSetting::get('footer_address_line3', 'Addis Ababa, Ethiopia'),
                'footer_maps_link'     => SystemSetting::get('footer_maps_link', 'https://maps.google.com'),
                'footer_phone'         => SystemSetting::get('footer_phone', '+251 911 223 344'),
                'footer_instagram'     => SystemSetting::get('footer_instagram', 'https://instagram.com/lulu__addis'),
                'footer_facebook'      => SystemSetting::get('footer_facebook', '#'),
                'footer_tiktok'        => SystemSetting::get('footer_tiktok', '#'),
            ],
        ]);
    }

    /**
     * Save policy content and announcement message.
     */
    public function update(Request $request)
    {
        $validated = $request->validate([
            'terms_and_conditions' => 'nullable|string|max:50000',
            'privacy_policy'       => 'nullable|string|max:50000',
            'announcement_message' => 'nullable|string|max:500',
            'footer_address_line1' => 'nullable|string|max:255',
            'footer_address_line2' => 'nullable|string|max:255',
            'footer_address_line3' => 'nullable|string|max:255',
            'footer_maps_link'     => 'nullable|string|max:1000',
            'footer_phone'         => 'nullable|string|max:50',
            'footer_instagram'     => 'nullable|string|max:500',
            'footer_facebook'      => 'nullable|string|max:500',
            'footer_tiktok'        => 'nullable|string|max:500',
        ]);

        SystemSetting::set('terms_and_conditions', $validated['terms_and_conditions'] ?? '');
        SystemSetting::set('privacy_policy', $validated['privacy_policy'] ?? '');
        SystemSetting::set('announcement_message', $validated['announcement_message'] ?? '');
        
        SystemSetting::set('footer_address_line1', $validated['footer_address_line1'] ?? '');
        SystemSetting::set('footer_address_line2', $validated['footer_address_line2'] ?? '');
        SystemSetting::set('footer_address_line3', $validated['footer_address_line3'] ?? '');
        SystemSetting::set('footer_maps_link', $validated['footer_maps_link'] ?? '');
        SystemSetting::set('footer_phone', $validated['footer_phone'] ?? '');
        SystemSetting::set('footer_instagram', $validated['footer_instagram'] ?? '');
        SystemSetting::set('footer_facebook', $validated['footer_facebook'] ?? '');
        SystemSetting::set('footer_tiktok', $validated['footer_tiktok'] ?? '');

        return back()->with('success', 'Content settings saved successfully.');
    }

    private function defaultTerms(): string
    {
        return "Welcome to LULU Couture. By browsing our store or placing an order, you agree to the following terms and conditions:\n\n**Order Confirmation**\nAll orders are subject to item availability and payment verification. We reserve the right to cancel orders that cannot be fulfilled.\n\n**Pricing**\nPrices are displayed in Ethiopian Birr (ETB) and are inclusive of standard handling. Express shipping charges are applied at checkout.\n\n**Exchanges & Returns**\nUnworn items with original tags attached can be exchanged within 14 days of delivery. Items must be in their original condition, unused, and with all tags attached.\n\n**Payment**\nWe accept Cash on Delivery and bank/mobile-money transfer. Payment must be confirmed before order dispatch.\n\n**Intellectual Property**\nAll content on this site, including images, logos, and text, is the property of LULU Couture and may not be reproduced without permission.\n\n**Changes to Terms**\nWe reserve the right to update these terms at any time. Continued use of the site constitutes acceptance of updated terms.";
    }

    private function defaultPrivacy(): string
    {
        return "At LULU Couture, your privacy is our highest priority.\n\n**Data We Collect**\nWe collect only the information required to process your orders and deliver exceptional customer service. This includes your name, phone number, delivery address, and order details.\n\n**How We Use Your Data**\nYour information is used solely for order processing, delivery coordination, and customer support. We may send you order status updates via Telegram if you opt in.\n\n**Data Security**\nAll customer data is encrypted and securely stored. We implement industry-standard security measures to protect your personal information.\n\n**Third Parties**\nWe never share, sell, or rent your personal information to third parties. Delivery partners only receive the minimum necessary information to complete your order.\n\n**Cookies**\nWe use cookies to maintain your shopping session and cart. These are essential for the site to function and do not track you across other websites.\n\n**Your Rights**\nYou may request access to, correction, or deletion of your personal data at any time by contacting our support team.\n\n**Contact**\nFor privacy concerns, please reach out to us via Instagram @lulu__addis or by phone.";
    }
}
