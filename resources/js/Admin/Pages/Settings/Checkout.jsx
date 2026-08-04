import React from 'react';
import { useForm } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

function Section({ title, children }) {
    return (
        <div className="bg-white border border-[#E6DFD5] rounded-xl p-6 space-y-4 shadow-sm">
            <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-3">
                {title}
            </h2>
            {children}
        </div>
    );
}

export default function CheckoutSettings({ settings }) {
    const form = useForm({
        currency_symbol: settings.currency_symbol || 'Birr',
        schedule_enabled: settings.schedule_enabled || '0',
        logistics_pickup: settings.logistics_pickup || '1',
        pickup_location_name: settings.pickup_location_name || '',
        pickup_location_link: settings.pickup_location_link || '',
        logistics_delivery_fixed: settings.logistics_delivery_fixed || '1',
        delivery_fixed_fee: settings.delivery_fixed_fee || '15.00',
        logistics_delivery_rider: settings.logistics_delivery_rider || '1',
        rider_disclaimer: settings.rider_disclaimer || '',
        payment_cod: settings.payment_cod || '1',
        payment_transfer: settings.payment_transfer || '1',
        payment_paypal: settings.payment_paypal || '1',
        paypal_instructions: settings.paypal_instructions || '',
        payment_card: settings.payment_card || '1',
        card_instructions: settings.card_instructions || '',
        deposit_required: settings.deposit_required || '0',
        deposit_percentage: settings.deposit_percentage || '50',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        form.post('/admin/settings/checkout');
    };

    return (
        <AdminLayout title="Checkout & Delivery Settings">
            <form onSubmit={handleSubmit} className="space-y-6 max-w-4xl">
                {/* General Settings */}
                <Section title="⚙️ General & Scheduling Settings">
                    <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <div>
                            <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Currency Symbol</label>
                            <input
                                type="text"
                                value={form.data.currency_symbol}
                                onChange={e => form.setData('currency_symbol', e.target.value)}
                                className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                required
                            />
                        </div>
                        <div>
                            <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Enable Delivery/Pickup Scheduling</label>
                            <select
                                value={form.data.schedule_enabled}
                                onChange={e => form.setData('schedule_enabled', e.target.value)}
                                className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554] font-bold"
                            >
                                <option value="1">ON (Require date & time slots selection)</option>
                                <option value="0">OFF (Hide date & time selection completely)</option>
                            </select>
                        </div>
                    </div>
                </Section>

                {/* Logistics Modes */}
                <Section title="🚚 Logistics & Fulfillment Options">
                    {/* Pickup Option */}
                    <div className="p-4 bg-[#F9F6F0] rounded-xl border border-[#E6DFD5] space-y-3">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-bold text-[#221F1F] uppercase tracking-wider">Storefront Pickup</span>
                            <select
                                value={form.data.logistics_pickup}
                                onChange={e => form.setData('logistics_pickup', e.target.value)}
                                className="px-3 py-1.5 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 font-bold"
                            >
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>
                        {form.data.logistics_pickup === '1' && (
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3 pt-2">
                                <div>
                                    <label className="block text-[10px] font-semibold uppercase text-stone-600 mb-1">Pickup Location Name</label>
                                    <input
                                        type="text"
                                        value={form.data.pickup_location_name}
                                        onChange={e => form.setData('pickup_location_name', e.target.value)}
                                        placeholder="e.g. LULU Boutique - Suite 402"
                                        className="w-full px-3.5 py-2.5 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                    />
                                </div>
                                <div>
                                    <label className="block text-[10px] font-semibold uppercase text-stone-600 mb-1">Google Maps Link</label>
                                    <input
                                        type="url"
                                        value={form.data.pickup_location_link}
                                        onChange={e => form.setData('pickup_location_link', e.target.value)}
                                        placeholder="https://maps.google.com/..."
                                        className="w-full px-3.5 py-2.5 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                    />
                                </div>
                            </div>
                        )}
                    </div>

                    {/* Courier / Shipping Delivery Option */}
                    <div className="p-4 bg-[#F9F6F0] rounded-xl border border-[#E6DFD5] space-y-3">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-bold text-[#221F1F] uppercase tracking-wider">Delivery (Courier / Shipping)</span>
                            <select
                                value={form.data.logistics_delivery_fixed}
                                onChange={e => form.setData('logistics_delivery_fixed', e.target.value)}
                                className="px-3 py-1.5 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 font-bold"
                            >
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>
                    </div>

                    {/* Rider Delivery Option */}
                    <div className="p-4 bg-[#F9F6F0] rounded-xl border border-[#E6DFD5] space-y-3">
                        <div className="flex items-center justify-between">
                            <span className="text-xs font-bold text-[#221F1F] uppercase tracking-wider">Rider / On-Demand Delivery</span>
                            <select
                                value={form.data.logistics_delivery_rider}
                                onChange={e => form.setData('logistics_delivery_rider', e.target.value)}
                                className="px-3 py-1.5 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 font-bold"
                            >
                                <option value="1">Enabled</option>
                                <option value="0">Disabled</option>
                            </select>
                        </div>
                        {form.data.logistics_delivery_rider === '1' && (
                            <div>
                                <label className="block text-[10px] font-semibold uppercase text-stone-600 mb-1">Rider Disclaimer / Note</label>
                                <textarea
                                    value={form.data.rider_disclaimer}
                                    onChange={e => form.setData('rider_disclaimer', e.target.value)}
                                    placeholder="Customer pays exact rider fee upon arrival..."
                                    rows="2"
                                    className="w-full px-3.5 py-2.5 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                />
                            </div>
                        )}
                    </div>
                </Section>

                {/* Payment Methods */}
                <Section title="💳 Payment Methods & Deposit Policy">
                    <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                        <div className="p-4 bg-[#F9F6F0] rounded-xl border border-[#E6DFD5] space-y-2">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-bold text-[#221F1F] uppercase">Cash on Delivery</span>
                                <select
                                    value={form.data.payment_cod}
                                    onChange={e => form.setData('payment_cod', e.target.value)}
                                    className="px-3 py-1.5 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 font-bold"
                                >
                                    <option value="1">Enabled</option>
                                    <option value="0">Disabled</option>
                                </select>
                            </div>
                        </div>

                        <div className="p-4 bg-[#F9F6F0] rounded-xl border border-[#E6DFD5] space-y-2">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-bold text-[#221F1F] uppercase">Bank Transfer / Proof Upload</span>
                                <select
                                    value={form.data.payment_transfer}
                                    onChange={e => form.setData('payment_transfer', e.target.value)}
                                    className="px-3 py-1.5 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 font-bold"
                                >
                                    <option value="1">Enabled</option>
                                    <option value="0">Disabled</option>
                                </select>
                            </div>
                        </div>

                        {/* PayPal settings */}
                        <div className="p-4 bg-[#F9F6F0] rounded-xl border border-[#E6DFD5] space-y-3">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-bold text-[#221F1F] uppercase">PayPal (International)</span>
                                <select
                                    value={form.data.payment_paypal}
                                    onChange={e => form.setData('payment_paypal', e.target.value)}
                                    className="px-3 py-1.5 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 font-bold"
                                >
                                    <option value="1">Enabled</option>
                                    <option value="0">Disabled</option>
                                </select>
                            </div>
                            {form.data.payment_paypal === '1' && (
                                <div className="pt-2">
                                    <label className="block text-[10px] font-semibold uppercase text-stone-600 mb-1">PayPal Instructions</label>
                                    <textarea
                                        value={form.data.paypal_instructions}
                                        onChange={e => form.setData('paypal_instructions', e.target.value)}
                                        placeholder="Click 'Place Order' below to proceed..."
                                        rows="2"
                                        className="w-full px-3.5 py-2.5 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                    />
                                </div>
                            )}
                        </div>

                        {/* Credit card settings */}
                        <div className="p-4 bg-[#F9F6F0] rounded-xl border border-[#E6DFD5] space-y-3">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-bold text-[#221F1F] uppercase">Credit / Debit Card</span>
                                <select
                                    value={form.data.payment_card}
                                    onChange={e => form.setData('payment_card', e.target.value)}
                                    className="px-3 py-1.5 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 font-bold"
                                >
                                    <option value="1">Enabled</option>
                                    <option value="0">Disabled</option>
                                </select>
                            </div>
                            {form.data.payment_card === '1' && (
                                <div className="pt-2">
                                    <label className="block text-[10px] font-semibold uppercase text-stone-600 mb-1">Card Instructions</label>
                                    <textarea
                                        value={form.data.card_instructions}
                                        onChange={e => form.setData('card_instructions', e.target.value)}
                                        placeholder="Fill in card details securely..."
                                        rows="2"
                                        className="w-full px-3.5 py-2.5 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                    />
                                </div>
                            )}
                        </div>
                    </div>

                    {form.data.payment_transfer === '1' && (
                        <div className="p-4 bg-[#F9F6F0] rounded-xl border border-[#E6DFD5] space-y-3 pt-3">
                            <div className="flex items-center justify-between">
                                <span className="text-xs font-bold text-[#221F1F] uppercase">Require Deposit upfront?</span>
                                <select
                                    value={form.data.deposit_required}
                                    onChange={e => form.setData('deposit_required', e.target.value)}
                                    className="px-3 py-1.5 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 font-bold"
                                >
                                    <option value="0">Full Amount Required</option>
                                    <option value="1">Require Percentage Deposit</option>
                                </select>
                            </div>
                            {form.data.deposit_required === '1' && (
                                <div>
                                    <label className="block text-[10px] font-semibold uppercase text-stone-600 mb-1">Required Deposit Percentage (%)</label>
                                    <input
                                        type="number"
                                        min="1"
                                        max="100"
                                        value={form.data.deposit_percentage}
                                        onChange={e => form.setData('deposit_percentage', e.target.value)}
                                        className="w-full sm:w-48 px-3.5 py-2.5 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900"
                                    />
                                </div>
                            )}
                        </div>
                    )}
                </Section>

                <div className="pt-2">
                    <button
                        type="submit"
                        disabled={form.processing}
                        className="px-8 py-3 bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm transition-all"
                    >
                        Save Settings
                    </button>
                </div>
            </form>
        </AdminLayout>
    );
}
