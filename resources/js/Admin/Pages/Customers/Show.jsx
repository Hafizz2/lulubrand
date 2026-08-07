import React from 'react';
import { router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Show({ customer }) {
    // Loyalty Balance and details
    const balance = customer.loyalty_points?.balance || 0;
    const lifetimeEarned = customer.loyalty_points?.lifetime_earned || 0;
    const lifetimeRedeemed = customer.loyalty_points?.lifetime_redeemed || 0;

    return (
        <AdminLayout title={`Customer Detail: ${customer.name}`}>
            {/* Back Button & Action Bar */}
            <div className="mb-6 flex items-center justify-between">
                <a
                    href="/admin/customers"
                    className="inline-flex items-center space-x-2 text-xs font-bold uppercase tracking-wider text-stone-600 hover:text-[#221F1F] transition-all"
                >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" strokeWidth="2.5" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span>Back to Customers</span>
                </a>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 items-start">
                
                {/* Left: Customer Info Card */}
                <div className="lg:col-span-1 space-y-6">
                    <div className="bg-white border border-[#E6DFD5] rounded-xl p-6 shadow-sm space-y-6">
                        <div>
                            <div className="w-16 h-16 rounded-full bg-[#8C6554] text-white flex items-center justify-center text-2xl font-bold uppercase shadow-sm mb-4">
                                {customer.name?.charAt(0)}
                            </div>
                            <h2 className="text-lg font-serif font-bold text-[#221F1F]">{customer.name}</h2>
                            <p className="text-[10px] text-stone-400 font-mono uppercase mt-0.5">Customer ID #{customer.id}</p>
                        </div>

                        <div className="border-t border-[#E6DFD5] pt-4 space-y-3 text-xs">
                            <div>
                                <span className="block text-[10px] font-bold uppercase text-[#A38B7E] tracking-wider">Email</span>
                                <span className="text-[#221F1F] font-semibold">{customer.email}</span>
                            </div>
                            <div>
                                <span className="block text-[10px] font-bold uppercase text-[#A38B7E] tracking-wider">Phone</span>
                                <span className="text-[#221F1F] font-semibold">{customer.phone || '—'}</span>
                            </div>
                            <div>
                                <span className="block text-[10px] font-bold uppercase text-[#A38B7E] tracking-wider">Member Since</span>
                                <span className="text-stone-500 font-medium">{new Date(customer.created_at).toLocaleDateString()}</span>
                            </div>
                        </div>

                        {/* Loyalty points overview if enabled */}
                        <div className="border-t border-[#E6DFD5] pt-4 space-y-3">
                            <h3 className="text-xs font-bold uppercase tracking-wider text-[#221F1F]">LULU Points</h3>
                            <div className="grid grid-cols-3 gap-2 text-center">
                                <div className="bg-[#FAF8F5] p-3 border border-[#E6DFD5] rounded-lg">
                                    <span className="block text-[18px] font-extrabold text-[#8C6554]">{balance}</span>
                                    <span className="text-[8px] font-bold uppercase tracking-wider text-stone-500">Balance</span>
                                </div>
                                <div className="bg-[#FAF8F5] p-3 border border-[#E6DFD5] rounded-lg">
                                    <span className="block text-[18px] font-extrabold text-emerald-700">{lifetimeEarned}</span>
                                    <span className="text-[8px] font-bold uppercase tracking-wider text-stone-500">Earned</span>
                                </div>
                                <div className="bg-[#FAF8F5] p-3 border border-[#E6DFD5] rounded-lg">
                                    <span className="block text-[18px] font-extrabold text-rose-700">{lifetimeRedeemed}</span>
                                    <span className="text-[8px] font-bold uppercase tracking-wider text-stone-500">Redeemed</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                {/* Right: Order History & Transactions */}
                <div className="lg:col-span-2 space-y-6">
                    {/* Orders List */}
                    <div className="bg-white border border-[#E6DFD5] rounded-xl overflow-hidden shadow-sm">
                        <div className="bg-[#F9F6F0] px-6 py-4 border-b border-[#E6DFD5]">
                            <h3 className="text-xs font-bold uppercase tracking-wider text-[#221F1F]">Purchase History</h3>
                        </div>
                        <div className="p-6">
                            {customer.orders && customer.orders.length > 0 ? (
                                <div className="space-y-6">
                                    {customer.orders.map(order => (
                                        <div key={order.id} className="border border-[#E6DFD5] rounded-xl p-4 space-y-4">
                                            <div className="flex flex-col sm:flex-row sm:items-center justify-between border-b border-[#E6DFD5] pb-3 gap-2">
                                                <div>
                                                    <span className="text-xs font-bold text-[#221F1F]">Order #{order.order_number}</span>
                                                    <span className="text-[10px] text-stone-400 font-medium block mt-0.5">{new Date(order.created_at).toLocaleDateString()}</span>
                                                </div>
                                                <div className="flex items-center space-x-3">
                                                    <span className="text-xs font-extrabold text-[#8C6554]">
                                                        {parseFloat((order.total || 0) / 100).toFixed(2)} Birr
                                                    </span>
                                                    <span className={`px-2.5 py-0.5 rounded-full text-[9px] font-bold uppercase tracking-wider ${
                                                        order.status === 'delivered' ? 'bg-emerald-100 text-emerald-800' :
                                                        order.status === 'cancelled' ? 'bg-rose-100 text-rose-800' :
                                                        'bg-amber-100 text-amber-800'
                                                    }`}>
                                                        {order.status}
                                                    </span>
                                                </div>
                                            </div>

                                            {/* Order Items */}
                                            <div className="space-y-2">
                                                {order.items?.map(item => (
                                                    <div key={item.id} className="flex justify-between items-center text-xs text-stone-700">
                                                        <div className="flex items-center space-x-2">
                                                            <span className="font-semibold text-stone-900">{item.product?.title || 'Unknown Product'}</span>
                                                            <span className="text-stone-400">x{item.quantity}</span>
                                                        </div>
                                                        <span className="font-medium text-stone-800">
                                                            {parseFloat(((item.price || 0) * (item.quantity || 1)) / 100).toFixed(2)} Birr
                                                        </span>
                                                    </div>
                                                ))}
                                            </div>
                                        </div>
                                    ))}
                                </div>
                            ) : (
                                <p className="text-xs text-stone-500 text-center py-6">No purchases recorded for this customer yet.</p>
                            )}
                        </div>
                    </div>

                    {/* Loyalty Transactions Audit Log */}
                    <div className="bg-white border border-[#E6DFD5] rounded-xl overflow-hidden shadow-sm">
                        <div className="bg-[#F9F6F0] px-6 py-4 border-b border-[#E6DFD5]">
                            <h3 className="text-xs font-bold uppercase tracking-wider text-[#221F1F]">Loyalty Points Statement</h3>
                        </div>
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-xs text-stone-700">
                                <thead className="bg-[#F9F6F0]/50 text-stone-500 uppercase border-b border-[#E6DFD5] font-bold text-[10px] tracking-wider">
                                    <tr>
                                        <th className="p-4">Date</th>
                                        <th className="p-4">Transaction Type</th>
                                        <th className="p-4">Source</th>
                                        <th className="p-4">Description</th>
                                        <th className="p-4 text-right">Points</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-[#E6DFD5]">
                                    {customer.loyalty_transactions && customer.loyalty_transactions.length > 0 ? (
                                        customer.loyalty_transactions.map(tx => (
                                            <tr key={tx.id} className="hover:bg-[#F9F6F0]/40 transition-colors">
                                                <td className="p-4 text-stone-500">{new Date(tx.created_at).toLocaleDateString()}</td>
                                                <td className="p-4">
                                                    <span className={`px-2 py-0.5 rounded text-[9px] font-bold uppercase tracking-wider ${
                                                        tx.type === 'earn' ? 'bg-emerald-100 text-emerald-800' :
                                                        tx.type === 'redeem' ? 'bg-rose-100 text-rose-800' :
                                                        'bg-stone-100 text-stone-800'
                                                    }`}>
                                                        {tx.type}
                                                    </span>
                                                </td>
                                                <td className="p-4 text-stone-600 font-mono text-[10px] uppercase">{tx.source}</td>
                                                <td className="p-4 text-stone-600 font-medium">{tx.description}</td>
                                                <td className={`p-4 text-right font-extrabold ${
                                                    tx.points > 0 ? 'text-emerald-700' : 'text-rose-700'
                                                }`}>
                                                    {tx.points > 0 ? `+${tx.points}` : tx.points}
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan="5" className="p-8 text-center text-stone-500">No loyalty point transactions on record.</td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

            </div>
        </AdminLayout>
    );
}
