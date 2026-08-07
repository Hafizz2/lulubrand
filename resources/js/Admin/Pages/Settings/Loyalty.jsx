import React, { useState } from 'react';
import AdminLayout from '@/Admin/Layouts/AdminLayout';
import { router } from '@inertiajs/react';

export default function LoyaltySettings({ settings }) {
    const [form, setForm] = useState({
        loyalty_enabled: settings?.enabled ?? false,
        loyalty_birr_per_point: settings?.birr_per_point ?? 100,
        loyalty_point_value_cents: settings?.point_value_cents ?? 100,
        loyalty_min_redeem: settings?.min_redeem ?? 50,
    });
    
    const [processing, setProcessing] = useState(false);

    const handleChange = (e) => {
        const value = e.target.type === 'checkbox' ? e.target.checked : e.target.value;
        setForm({
            ...form,
            [e.target.name]: value
        });
    };
    
    const handleSave = (e) => {
        e.preventDefault();
        setProcessing(true);
        router.post('/admin/settings/loyalty', form, {
            onFinish: () => setProcessing(false)
        });
    };
    
    return (
        <AdminLayout title="Loyalty Settings">
            <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                
                {/* Form Section */}
                <div className="md:col-span-2">
                    <form onSubmit={handleSave} className="bg-white border border-stone-200 rounded-xl shadow-sm p-6 space-y-6">
                        
                        {/* Enable/Disable Toggle */}
                        <div className="flex items-center justify-between pb-6 border-b border-stone-100">
                            <div>
                                <h3 className="text-sm font-bold text-[#221F1F]">Enable Loyalty Program</h3>
                                <p className="text-xs text-stone-500 mt-1">Turn on to allow customers to earn and redeem points.</p>
                            </div>
                            <label className="relative inline-flex items-center cursor-pointer">
                                <input 
                                    type="checkbox" 
                                    className="sr-only peer" 
                                    name="loyalty_enabled"
                                    checked={form.loyalty_enabled}
                                    onChange={handleChange}
                                />
                                <div className="w-11 h-6 bg-stone-200 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-stone-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-[#8C6554]"></div>
                            </label>
                        </div>

                        {/* Settings Inputs */}
                        <div className={`space-y-6 ${!form.loyalty_enabled ? 'opacity-50 pointer-events-none' : ''}`}>
                            
                            <div>
                                <label className="block text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">
                                    Birr Per Point
                                </label>
                                <div className="flex items-center">
                                    <span className="bg-[#FAF8F5] border border-r-0 border-stone-200 px-3 py-2 text-sm text-stone-500 rounded-l-lg">
                                        ETB
                                    </span>
                                    <input 
                                        type="number"
                                        min="1"
                                        name="loyalty_birr_per_point"
                                        value={form.loyalty_birr_per_point}
                                        onChange={handleChange}
                                        className="w-full bg-white border border-stone-200 rounded-r-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#8C6554] focus:border-transparent"
                                    />
                                </div>
                                <p className="text-[10px] text-stone-400 mt-1">Amount of Birr spent to earn 1 point.</p>
                            </div>

                            <div>
                                <label className="block text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">
                                    Point Value (in cents)
                                </label>
                                <div className="flex items-center">
                                    <span className="bg-[#FAF8F5] border border-r-0 border-stone-200 px-3 py-2 text-sm text-stone-500 rounded-l-lg">
                                        Cents
                                    </span>
                                    <input 
                                        type="number"
                                        min="1"
                                        name="loyalty_point_value_cents"
                                        value={form.loyalty_point_value_cents}
                                        onChange={handleChange}
                                        className="w-full bg-white border border-stone-200 rounded-r-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#8C6554] focus:border-transparent"
                                    />
                                </div>
                                <p className="text-[10px] text-stone-400 mt-1">The discount value of 1 point in cents (100 cents = 1 Birr).</p>
                            </div>

                            <div>
                                <label className="block text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">
                                    Minimum Points to Redeem
                                </label>
                                <input 
                                    type="number"
                                    min="1"
                                    name="loyalty_min_redeem"
                                    value={form.loyalty_min_redeem}
                                    onChange={handleChange}
                                    className="w-full bg-white border border-stone-200 rounded-lg px-3 py-2 text-sm focus:ring-2 focus:ring-[#8C6554] focus:border-transparent"
                                />
                                <p className="text-[10px] text-stone-400 mt-1">Customers cannot redeem points until they reach this balance.</p>
                            </div>

                        </div>

                        <div className="pt-4 flex justify-end">
                            <button
                                type="submit"
                                disabled={processing}
                                className="bg-[#8C6554] hover:bg-[#7A5747] text-white text-xs font-bold uppercase tracking-wider px-6 py-2.5 rounded-lg transition-colors disabled:opacity-50"
                            >
                                {processing ? 'Saving...' : 'Save Settings'}
                            </button>
                        </div>
                    </form>
                </div>

                {/* Explanation / Preview Panel */}
                <div className="md:col-span-1">
                    <div className="bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl p-6 sticky top-6">
                        <h3 className="text-sm font-bold text-[#221F1F] mb-4 flex items-center">
                            <svg className="w-4 h-4 mr-2 text-[#8C6554]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            How it works
                        </h3>
                        
                        <div className="space-y-4 text-sm text-stone-600">
                            <p>
                                <strong>Earning:</strong><br/>
                                For every <span className="font-bold text-[#8C6554]">{form.loyalty_birr_per_point || 0} Birr</span> spent, the customer earns 1 point.
                            </p>
                            
                            <p>
                                <strong>Redeeming:</strong><br/>
                                1 point gives a discount of <span className="font-bold text-[#8C6554]">{((form.loyalty_point_value_cents || 0) / 100).toFixed(2)} Birr</span>.
                            </p>

                            <div className="bg-white p-4 rounded-lg border border-stone-200 mt-4">
                                <h4 className="text-xs font-bold uppercase tracking-wider text-stone-500 mb-2">Example Scenario</h4>
                                <p className="text-xs">
                                    If a customer buys items worth <strong>{form.loyalty_birr_per_point * 10 || 0} Birr</strong>:
                                </p>
                                <ul className="list-disc list-inside text-xs mt-2 space-y-1 text-stone-500">
                                    <li>They earn <strong>10 points</strong>.</li>
                                    <li>Those 10 points can be used for a <strong>{((form.loyalty_point_value_cents * 10) / 100).toFixed(2)} Birr</strong> discount on their next purchase.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </AdminLayout>
    );
}
