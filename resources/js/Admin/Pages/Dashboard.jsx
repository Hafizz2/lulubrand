import React from 'react';
import AdminLayout from '../Layouts/AdminLayout';

function StatCard({ label, value, sub, color = 'mocha' }) {
    const colorMap = {
        mocha: 'bg-[#8C6554]/15 text-[#8C6554] border-[#8C6554]/30',
        emerald: 'bg-emerald-100 text-emerald-800 border-emerald-200',
        amber: 'bg-amber-100 text-amber-800 border-amber-200',
        blue: 'bg-sky-100 text-sky-800 border-sky-200',
    };

    return (
        <div className="bg-white border border-[#E6DFD5] rounded-xl p-5 flex flex-col gap-1 shadow-sm">
            <span className="text-[11px] font-bold uppercase tracking-widest text-stone-500">{label}</span>
            <span className="text-3xl font-serif font-bold text-[#221F1F]">{value}</span>
            {sub && (
                <span className={`text-[11px] font-semibold px-2.5 py-0.5 rounded-full border w-fit mt-1 ${colorMap[color]}`}>
                    {sub}
                </span>
            )}
        </div>
    );
}

export default function Dashboard({ stats }) {
    const s = stats || {};

    return (
        <AdminLayout title="Dashboard Overview">
            {/* Stats Grid */}
            <div className="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
                <StatCard label="Total Orders" value={s.total_orders ?? '—'} sub="All time" color="blue" />
                <StatCard label="Pending Orders" value={s.pending_orders ?? '—'} sub="Awaiting action" color="amber" />
                <StatCard label="Total Revenue" value={s.total_revenue ? `${s.total_revenue} Birr` : '—'} sub="From paid orders" color="emerald" />
                <StatCard label="Low Stock SKUs" value={s.low_stock ?? '—'} sub="≤ 5 units" color="mocha" />
            </div>

            {/* Quick Links */}
            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
                <a href="/admin/products/create"
                    className="bg-[#8C6554] hover:bg-[#755243] rounded-xl p-5 flex items-center space-x-4 transition-all shadow-sm">
                    <div className="w-10 h-10 bg-white/20 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg className="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                        </svg>
                    </div>
                    <div>
                        <p className="text-white font-bold text-sm">Add New Product</p>
                        <p className="text-[#F3EEE8] text-xs">Take photo & build variant matrix</p>
                    </div>
                </a>

                <a href="/admin/orders"
                    className="bg-white border border-[#E6DFD5] hover:border-[#8C6554] rounded-xl p-5 flex items-center space-x-4 transition-all shadow-sm">
                    <div className="w-10 h-10 bg-amber-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg className="w-5 h-5 text-amber-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                                d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2" />
                        </svg>
                    </div>
                    <div>
                        <p className="text-[#221F1F] font-bold text-sm">Manage Orders</p>
                        <p className="text-stone-500 text-xs">Update status & track deliveries</p>
                    </div>
                </a>

                <a href="/admin/stock"
                    className="bg-white border border-[#E6DFD5] hover:border-[#8C6554] rounded-xl p-5 flex items-center space-x-4 transition-all shadow-sm">
                    <div className="w-10 h-10 bg-emerald-100 rounded-lg flex items-center justify-center flex-shrink-0">
                        <svg className="w-5 h-5 text-emerald-800" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2"
                                d="M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4" />
                        </svg>
                    </div>
                    <div>
                        <p className="text-[#221F1F] font-bold text-sm">Stock Management</p>
                        <p className="text-stone-500 text-xs">Adjust quantities & view log</p>
                    </div>
                </a>
            </div>

            {/* Recent Orders Table */}
            {s.recent_orders && s.recent_orders.length > 0 && (
                <div className="bg-white border border-[#E6DFD5] rounded-xl overflow-hidden shadow-sm">
                    <div className="p-4 border-b border-[#E6DFD5] flex items-center justify-between bg-[#F9F6F0]">
                        <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F]">Recent Orders</h2>
                        <a href="/admin/orders" className="text-xs text-[#8C6554] hover:underline font-bold uppercase tracking-wider">View All →</a>
                    </div>
                    <table className="w-full text-xs text-left text-stone-700">
                        <thead className="bg-[#F9F6F0] text-stone-500 uppercase border-b border-[#E6DFD5] font-bold">
                            <tr>
                                <th className="p-3">Order #</th>
                                <th className="p-3">Customer</th>
                                <th className="p-3">Total</th>
                                <th className="p-3">Status</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-[#E6DFD5]">
                            {s.recent_orders.map(order => (
                                <tr key={order.id} className="hover:bg-[#F9F6F0]/60 transition-colors">
                                    <td className="p-3 font-mono font-bold text-[#221F1F]">{order.order_number}</td>
                                    <td className="p-3 font-medium">{order.customer_name}</td>
                                    <td className="p-3 font-bold text-[#8C6554]">{(order.total / 100).toFixed(2)} Birr</td>
                                    <td className="p-3">
                                        <span className="px-2.5 py-0.5 text-[10px] font-bold uppercase rounded-full bg-amber-100 text-amber-800 border border-amber-200">
                                            {order.status}
                                        </span>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
            )}
        </AdminLayout>
    );
}
