import React from 'react';
import { useForm } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function PushBroadcast({ stats }) {
    const { data, setData, post, processing, errors } = useForm({
        target: 'all',
        title: '',
        body: '',
        url: '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/push-broadcast');
    };

    return (
        <AdminLayout title="Web Push Notification Broadcast">
            <div className="space-y-8 max-w-4xl">

                {/* Audience Stats Cards */}
                <div className="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div className="bg-white border border-[#E6DFD5] rounded-2xl p-5 shadow-sm space-y-1">
                        <div className="flex items-center justify-between text-[#8C6554]">
                            <span className="text-[10px] font-bold uppercase tracking-wider">Total Active Subscribers</span>
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9" />
                            </svg>
                        </div>
                        <p className="text-2xl font-serif font-bold text-[#221F1F]">{stats.total}</p>
                        <p className="text-[10px] text-stone-500">Browsers opted into push alerts</p>
                    </div>

                    <div className="bg-white border border-[#E6DFD5] rounded-2xl p-5 shadow-sm space-y-1">
                        <div className="flex items-center justify-between text-emerald-700">
                            <span className="text-[10px] font-bold uppercase tracking-wider">Registered Accounts</span>
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                        </div>
                        <p className="text-2xl font-serif font-bold text-[#221F1F]">{stats.registered}</p>
                        <p className="text-[10px] text-stone-500">Subscribers linked to customer accounts</p>
                    </div>

                    <div className="bg-white border border-[#E6DFD5] rounded-2xl p-5 shadow-sm space-y-1">
                        <div className="flex items-center justify-between text-amber-700">
                            <span className="text-[10px] font-bold uppercase tracking-wider">Guest Browsers</span>
                            <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z" />
                            </svg>
                        </div>
                        <p className="text-2xl font-serif font-bold text-[#221F1F]">{stats.anonymous}</p>
                        <p className="text-[10px] text-stone-500">Store visitors who enabled web push</p>
                    </div>
                </div>

                {/* Broadcast Form */}
                <form onSubmit={handleSubmit} className="bg-white border border-[#E6DFD5] rounded-2xl p-6 space-y-6 shadow-sm">
                    <div className="border-b border-[#E6DFD5] pb-4 flex items-center justify-between">
                        <div>
                            <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F]">
                                Send Web Push Broadcast
                            </h2>
                            <p className="text-xs text-stone-500 mt-0.5">
                                Send real-time browser push notifications to your shoppers & visitors.
                            </p>
                        </div>
                        <span className="bg-[#8C6554]/10 text-[#8C6554] text-[10px] font-bold uppercase px-3 py-1 rounded-full border border-[#8C6554]/20">
                            Instant Delivery
                        </span>
                    </div>

                    <div className="space-y-4">
                        <div>
                            <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Target Audience *</label>
                            <select
                                value={data.target}
                                onChange={(e) => setData('target', e.target.value)}
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            >
                                <option value="all">All Push Subscribers ({stats.total} Subscribers)</option>
                                <option value="customers">Registered Customers Only ({stats.registered} Accounts)</option>
                            </select>
                            {errors.target && <p className="text-xs text-rose-600 mt-1">{errors.target}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Notification Title *</label>
                            <input
                                type="text"
                                value={data.title}
                                onChange={(e) => setData('title', e.target.value)}
                                required
                                placeholder="e.g. 🌸 New Spring Collection Drop at LULU!"
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                            {errors.title && <p className="text-xs text-rose-600 mt-1">{errors.title}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Message Body *</label>
                            <textarea
                                value={data.body}
                                onChange={(e) => setData('body', e.target.value)}
                                required
                                rows="3"
                                placeholder="e.g. Discover our latest corset dresses and silk outfits. Shop online now with instant delivery!"
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                            {errors.body && <p className="text-xs text-rose-600 mt-1">{errors.body}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Action URL (Optional)</label>
                            <input
                                type="url"
                                value={data.url}
                                onChange={(e) => setData('url', e.target.value)}
                                placeholder="e.g. http://localhost/catalog or leave empty for homepage"
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                            <p className="text-[10px] text-stone-500 mt-1">Users who click on the notification will be directed to this link.</p>
                            {errors.url && <p className="text-xs text-rose-600 mt-1">{errors.url}</p>}
                        </div>
                    </div>

                    <button
                        type="submit"
                        disabled={processing || stats.total === 0}
                        className="w-full py-4 bg-[#8C6554] hover:bg-[#755243] text-white font-bold text-xs uppercase tracking-widest rounded-full transition-all shadow-md disabled:opacity-50 min-h-[48px] flex items-center justify-center space-x-2"
                    >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8" />
                        </svg>
                        <span>{processing ? 'Sending Broadcast...' : 'Broadcast Web Push Notification'}</span>
                    </button>
                </form>

            </div>
        </AdminLayout>
    );
}
