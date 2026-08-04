import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function ContentSettings({ settings }) {
    const { data, setData, post, processing, errors, recentlySuccessful } = useForm({
        terms_and_conditions: settings.terms_and_conditions || '',
        privacy_policy: settings.privacy_policy || '',
        announcement_message: settings.announcement_message || '',
        footer_address_line1: settings.footer_address_line1 || '',
        footer_address_line2: settings.footer_address_line2 || '',
        footer_address_line3: settings.footer_address_line3 || '',
        footer_maps_link: settings.footer_maps_link || '',
        footer_phone: settings.footer_phone || '',
        footer_instagram: settings.footer_instagram || '',
        footer_facebook: settings.footer_facebook || '',
        footer_tiktok: settings.footer_tiktok || '',
    });

    const [activeTab, setActiveTab] = useState('announcement');

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/settings/content');
    };

    const tabs = [
        { key: 'announcement', label: '📢 Announcement Bar' },
        { key: 'footer', label: '📞 Footer & Socials' },
        { key: 'terms', label: '📜 Terms & Conditions' },
        { key: 'privacy', label: '🔒 Privacy Policy' },
    ];

    return (
        <AdminLayout title="Content & Policy Settings">
            <div className="max-w-4xl mx-auto p-4 sm:p-6">
                {/* Header */}
                <div className="mb-6">
                    <h1 className="text-xl font-bold text-gray-900">Content & Policy Settings</h1>
                    <p className="text-sm text-gray-500 mt-1">
                        Manage announcement bar message, Terms & Conditions, and Privacy Policy — these appear live on the storefront.
                    </p>
                </div>

                {recentlySuccessful && (
                    <div className="mb-4 px-4 py-3 bg-green-50 border border-green-200 rounded-lg text-green-800 text-sm font-medium flex items-center gap-2">
                        ✓ Content settings saved successfully.
                    </div>
                )}

                <form onSubmit={handleSubmit} className="space-y-6">
                    {/* Tab Navigation */}
                    <div className="border-b border-gray-200">
                        <nav className="flex gap-1 -mb-px overflow-x-auto">
                            {tabs.map(tab => (
                                <button
                                    key={tab.key}
                                    type="button"
                                    onClick={() => setActiveTab(tab.key)}
                                    className={`px-4 py-3 text-sm font-medium whitespace-nowrap transition-colors border-b-2 ${
                                        activeTab === tab.key
                                            ? 'border-[#82203E] text-[#82203E]'
                                            : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300'
                                    }`}
                                >
                                    {tab.label}
                                </button>
                            ))}
                        </nav>
                    </div>

                    {/* Announcement Tab */}
                    {activeTab === 'announcement' && (
                        <div className="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
                            <div>
                                <h2 className="text-base font-semibold text-gray-900 mb-1">Announcement Bar</h2>
                                <p className="text-sm text-gray-500">
                                    This message appears at the very top of every page. Leave blank to auto-show an active discount code, 
                                    or the default shipping message if no discounts are active.
                                </p>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1.5">
                                    Custom Announcement Message
                                </label>
                                <input
                                    type="text"
                                    value={data.announcement_message}
                                    onChange={e => setData('announcement_message', e.target.value)}
                                    placeholder="e.g. ✦ FREE SHIPPING ON ALL ORDERS THIS WEEK ✦"
                                    maxLength={500}
                                    className="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#82203E] focus:border-transparent"
                                />
                                <p className="text-xs text-gray-400 mt-1">
                                    {data.announcement_message.length}/500 characters. Leave empty to show active discount code automatically.
                                </p>
                                {errors.announcement_message && (
                                    <p className="text-xs text-red-600 mt-1">{errors.announcement_message}</p>
                                )}
                            </div>

                            {/* Preview */}
                            {data.announcement_message && (
                                <div className="mt-4">
                                    <p className="text-xs font-semibold text-gray-500 uppercase tracking-wider mb-2">Preview:</p>
                                    <div className="h-9 bg-[#F6DADF] text-[#82203E] flex items-center justify-center text-xs font-medium rounded">
                                        {data.announcement_message}
                                    </div>
                                </div>
                            )}
                        </div>
                    )}

                    {/* Footer Tab */}
                    {activeTab === 'footer' && (
                        <div className="bg-white border border-gray-200 rounded-xl p-6 space-y-6">
                            <div>
                                <h2 className="text-base font-semibold text-gray-900 mb-1">Footer & Boutique Details</h2>
                                <p className="text-sm text-gray-500">
                                    Configure boutique details and social media links that appear live on the storefront footer.
                                </p>
                            </div>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div className="space-y-4">
                                    <h3 className="text-xs font-bold uppercase tracking-wider text-[#82203E]">📍 Boutique Address</h3>
                                    
                                    <div>
                                        <label className="block text-xs font-medium text-gray-700 mb-1">Address Line 1</label>
                                        <input
                                            type="text"
                                            value={data.footer_address_line1}
                                            onChange={e => setData('footer_address_line1', e.target.value)}
                                            placeholder="e.g. Bole Medhanialem"
                                            className="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#82203E]"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-medium text-gray-700 mb-1">Address Line 2</label>
                                        <input
                                            type="text"
                                            value={data.footer_address_line2}
                                            onChange={e => setData('footer_address_line2', e.target.value)}
                                            placeholder="e.g. Edna Mall Area"
                                            className="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#82203E]"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-medium text-gray-700 mb-1">Address Line 3</label>
                                        <input
                                            type="text"
                                            value={data.footer_address_line3}
                                            onChange={e => setData('footer_address_line3', e.target.value)}
                                            placeholder="e.g. Addis Ababa, Ethiopia"
                                            className="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#82203E]"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-medium text-gray-700 mb-1">Google Maps Pin link</label>
                                        <input
                                            type="url"
                                            value={data.footer_maps_link}
                                            onChange={e => setData('footer_maps_link', e.target.value)}
                                            placeholder="https://maps.google.com/..."
                                            className="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#82203E]"
                                        />
                                    </div>
                                </div>

                                <div className="space-y-4">
                                    <h3 className="text-xs font-bold uppercase tracking-wider text-[#82203E]">📞 Contacts & Social links</h3>

                                    <div>
                                        <label className="block text-xs font-medium text-gray-700 mb-1">Support Phone Number</label>
                                        <input
                                            type="text"
                                            value={data.footer_phone}
                                            onChange={e => setData('footer_phone', e.target.value)}
                                            placeholder="e.g. +251 911 223 344"
                                            className="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#82203E]"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-medium text-gray-700 mb-1">Instagram Link</label>
                                        <input
                                            type="text"
                                            value={data.footer_instagram}
                                            onChange={e => setData('footer_instagram', e.target.value)}
                                            placeholder="https://instagram.com/..."
                                            className="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#82203E]"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-medium text-gray-700 mb-1">Facebook Link</label>
                                        <input
                                            type="text"
                                            value={data.footer_facebook}
                                            onChange={e => setData('footer_facebook', e.target.value)}
                                            placeholder="https://facebook.com/..."
                                            className="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#82203E]"
                                        />
                                    </div>

                                    <div>
                                        <label className="block text-xs font-medium text-gray-700 mb-1">TikTok Link</label>
                                        <input
                                            type="text"
                                            value={data.footer_tiktok}
                                            onChange={e => setData('footer_tiktok', e.target.value)}
                                            placeholder="https://tiktok.com/..."
                                            className="w-full px-4 py-2.5 border border-gray-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-[#82203E]"
                                        />
                                    </div>
                                </div>
                            </div>
                        </div>
                    )}

                    {/* Terms Tab */}
                    {activeTab === 'terms' && (
                        <div className="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
                            <div>
                                <h2 className="text-base font-semibold text-gray-900 mb-1">Terms & Conditions</h2>
                                <p className="text-sm text-gray-500">
                                    This text appears in the Terms & Conditions modal on the storefront (accessible from the footer).
                                    Supports basic formatting with **bold** text and line breaks.
                                </p>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1.5">
                                    Terms & Conditions Content
                                </label>
                                <textarea
                                    value={data.terms_and_conditions}
                                    onChange={e => setData('terms_and_conditions', e.target.value)}
                                    rows={18}
                                    placeholder="Enter your terms and conditions here..."
                                    className="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#82203E] focus:border-transparent resize-y"
                                />
                                <p className="text-xs text-gray-400 mt-1">
                                    {data.terms_and_conditions.length} characters. 
                                    Use **text** for bold, and blank lines between sections.
                                </p>
                                {errors.terms_and_conditions && (
                                    <p className="text-xs text-red-600 mt-1">{errors.terms_and_conditions}</p>
                                )}
                            </div>
                        </div>
                    )}

                    {/* Privacy Tab */}
                    {activeTab === 'privacy' && (
                        <div className="bg-white border border-gray-200 rounded-xl p-6 space-y-4">
                            <div>
                                <h2 className="text-base font-semibold text-gray-900 mb-1">Privacy Policy</h2>
                                <p className="text-sm text-gray-500">
                                    This text appears in the Privacy Policy modal on the storefront (accessible from the footer).
                                    Supports basic formatting with **bold** text and line breaks.
                                </p>
                            </div>
                            <div>
                                <label className="block text-sm font-medium text-gray-700 mb-1.5">
                                    Privacy Policy Content
                                </label>
                                <textarea
                                    value={data.privacy_policy}
                                    onChange={e => setData('privacy_policy', e.target.value)}
                                    rows={18}
                                    placeholder="Enter your privacy policy here..."
                                    className="w-full px-4 py-3 border border-gray-300 rounded-lg text-sm font-mono focus:outline-none focus:ring-2 focus:ring-[#82203E] focus:border-transparent resize-y"
                                />
                                <p className="text-xs text-gray-400 mt-1">
                                    {data.privacy_policy.length} characters.
                                    Use **text** for bold, and blank lines between sections.
                                </p>
                                {errors.privacy_policy && (
                                    <p className="text-xs text-red-600 mt-1">{errors.privacy_policy}</p>
                                )}
                            </div>
                        </div>
                    )}

                    {/* Save Button */}
                    <div className="flex justify-end gap-3 pt-2">
                        <button
                            type="submit"
                            disabled={processing}
                            className="px-6 py-3 bg-[#82203E] text-white text-sm font-semibold rounded-lg hover:bg-[#6a1932] transition-colors disabled:opacity-60 flex items-center gap-2"
                        >
                            {processing ? (
                                <>
                                    <svg className="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                        <circle className="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" strokeWidth="4"/>
                                        <path className="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                    </svg>
                                    Saving...
                                </>
                            ) : '💾 Save Content Settings'}
                        </button>
                    </div>
                </form>
            </div>
        </AdminLayout>
    );
}
