import React, { useState } from 'react';
import { Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

const STATUS_COLORS = {
    pending:   'bg-amber-100 text-amber-800 border-amber-200',
    confirmed: 'bg-blue-100 text-blue-800 border-blue-200',
    packed:    'bg-purple-100 text-purple-800 border-purple-200',
    shipped:   'bg-cyan-100 text-cyan-800 border-cyan-200',
    delivered: 'bg-emerald-100 text-emerald-800 border-emerald-200',
    cancelled: 'bg-rose-100 text-rose-800 border-rose-200',
    refunded:  'bg-stone-100 text-stone-700 border-stone-200',
};

function OrderDrawer({ order, onClose, statusOptions }) {
    const [updating, setUpdating] = useState(false);

    const changeStatus = (newStatus) => {
        setUpdating(true);
        router.post(`/admin/orders/${order.id}/status`, { status: newStatus }, {
            preserveScroll: true,
            onSuccess: () => { setUpdating(false); onClose(); },
            onError: () => setUpdating(false),
        });
    };

    const markPaid = () => {
        router.post(`/admin/orders/${order.id}/mark-paid`, {}, { preserveScroll: true, onSuccess: onClose });
    };

    return (
        <div className="fixed inset-0 z-50 flex">
            <div className="absolute inset-0 bg-black/60 backdrop-blur-sm" onClick={onClose} />
            <div className="ml-auto w-full max-w-lg bg-white border-l border-[#E6DFD5] flex flex-col h-full overflow-y-auto shadow-2xl relative">
                {/* Header */}
                <div className="p-5 border-b border-[#E6DFD5] flex items-center justify-between flex-shrink-0 bg-[#F9F6F0]">
                    <div>
                        <p className="text-[10px] text-stone-500 font-mono uppercase">Order Reference</p>
                        <h2 className="text-base font-serif font-bold text-[#221F1F] tracking-wider">{order.order_number}</h2>
                    </div>
                    <button onClick={onClose} className="text-stone-400 hover:text-stone-900 text-2xl leading-none">&times;</button>
                </div>

                {/* Content */}
                <div className="flex-1 p-5 space-y-5 overflow-y-auto">
                    {/* Customer */}
                    <div className="bg-[#F9F6F0] rounded-xl p-4 space-y-1.5 border border-[#E6DFD5]">
                        <h3 className="text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">Customer Details</h3>
                        <p className="text-sm font-bold text-stone-900">{order.customer_name}</p>
                        <p className="text-xs text-stone-600 font-mono">{order.customer_phone}</p>
                        <p className="text-xs text-stone-600">{order.customer_address}, {order.customer_city}</p>
                    </div>

                    {/* Status Control */}
                    <div className="bg-[#F9F6F0] rounded-xl p-4 border border-[#E6DFD5]">
                        <h3 className="text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-3">Update Status</h3>
                        <div className="flex flex-wrap gap-2">
                            {statusOptions.map(s => (
                                <button
                                    key={s}
                                    onClick={() => changeStatus(s)}
                                    disabled={updating || order.status === s}
                                    className={`text-[10px] font-bold uppercase px-3 py-1.5 rounded-full border transition-all ${
                                        order.status === s
                                            ? 'bg-[#221F1F] text-white ring-2 ring-[#221F1F]/20 shadow'
                                            : 'bg-white text-stone-700 border-[#E6DFD5] hover:bg-[#8C6554] hover:text-white'
                                    }`}
                                >
                                    {s}
                                </button>
                            ))}
                        </div>
                    </div>

                    {/* Payment */}
                    <div className="bg-[#F9F6F0] rounded-xl p-4 flex items-center justify-between border border-[#E6DFD5]">
                        <div>
                            <h3 className="text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-1">Payment Status</h3>
                            <span className={`text-xs font-bold uppercase ${order.payment_status === 'paid' ? 'text-emerald-700' : 'text-amber-700'}`}>
                                {order.payment_status} — {order.payment_method?.replace('_', ' ')}
                            </span>
                        </div>
                        {order.payment_status !== 'paid' && (
                            <button onClick={markPaid} className="text-xs font-bold uppercase px-4 py-1.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-full transition-all">
                                Mark Paid
                            </button>
                        )}
                    </div>

                    {/* Items */}
                    <div className="bg-[#F9F6F0] rounded-xl p-4 border border-[#E6DFD5]">
                        <h3 className="text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-3">Ordered Items</h3>
                        <div className="space-y-3">
                            {order.items?.map(item => (
                                <div key={item.id} className="flex items-center justify-between border-b border-[#E6DFD5] pb-2 text-xs">
                                    <div>
                                        <p className="font-bold text-stone-900">{item.product_title}</p>
                                        <p className="text-stone-500 font-mono">{item.variant_sku} &times; {item.quantity}</p>
                                    </div>
                                    <span className="font-semibold text-[#8C6554]">{(item.total_price / 100).toFixed(2)} Birr</span>
                                </div>
                            ))}
                        </div>
                    </div>

                    <div className="pt-2">
                        <Link href={`/admin/orders/${order.id}`} className="block w-full text-center bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase tracking-widest py-3 rounded-full transition-all shadow-sm">
                            Open Full Page Details &rarr;
                        </Link>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default function Index({ orders, statusOptions, filters }) {
    const [search, setSearch] = useState(filters?.search || '');
    const [statusFilter, setStatusFilter] = useState(filters?.status || '');
    const [drawerOrder, setDrawerOrder] = useState(null);

    const applyFilters = () => {
        router.get('/admin/orders', { search, status: statusFilter }, { preserveState: true });
    };

    const openDrawer = async (order) => {
        const res = await fetch(`/admin/orders/${order.id}`, { headers: { 'Accept': 'application/json' } });
        const full = await res.json();
        setDrawerOrder(full);
    };

    return (
        <AdminLayout title="Orders">
            {/* Filters */}
            <div className="bg-white border border-[#E6DFD5] rounded-xl p-4 mb-6 flex flex-col sm:flex-row gap-3 shadow-sm">
                <input
                    type="text"
                    value={search}
                    onChange={e => setSearch(e.target.value)}
                    onKeyDown={e => e.key === 'Enter' && applyFilters()}
                    placeholder="Order #, customer name or phone..."
                    className="flex-1 px-4 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#8C6554]"
                />
                <select
                    value={statusFilter}
                    onChange={e => { setStatusFilter(e.target.value); }}
                    className="px-4 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                >
                    <option value="">All Statuses</option>
                    {statusOptions?.map(s => <option key={s} value={s}>{s}</option>)}
                </select>
                <button onClick={applyFilters} className="px-5 py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase rounded-lg transition-all shadow-sm">
                    Filter
                </button>
            </div>

            {/* Table */}
            <div className="bg-white border border-[#E6DFD5] rounded-xl overflow-hidden shadow-sm">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs text-stone-700">
                        <thead className="bg-[#F9F6F0] text-stone-500 uppercase border-b border-[#E6DFD5] font-bold">
                            <tr>
                                <th className="p-4">Order #</th>
                                <th className="p-4">Customer</th>
                                <th className="p-4">Total</th>
                                <th className="p-4">Payment</th>
                                <th className="p-4">Status</th>
                                <th className="p-4">Date</th>
                                <th className="p-4 text-right">Action</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-[#E6DFD5]">
                            {orders.data.map(order => (
                                <tr key={order.id} className="hover:bg-[#F9F6F0]/60 transition-colors">
                                    <td className="p-4 font-mono text-[#8C6554] font-bold">
                                        <Link href={`/admin/orders/${order.id}`} className="hover:underline">
                                            {order.order_number}
                                        </Link>
                                    </td>
                                    <td className="p-4">
                                        <p className="font-bold text-stone-900">{order.customer_name}</p>
                                        <p className="text-stone-500 font-mono text-[11px]">{order.customer_phone}</p>
                                    </td>
                                    <td className="p-4 font-bold text-stone-900">{(order.total / 100).toFixed(2)} Birr</td>
                                    <td className="p-4">
                                        <span className={`text-[10px] font-bold uppercase px-2.5 py-1 rounded-full border ${order.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-200'}`}>
                                            {order.payment_status}
                                        </span>
                                    </td>
                                    <td className="p-4">
                                        <span className={`text-[10px] font-bold uppercase px-2.5 py-1 rounded-full border ${STATUS_COLORS[order.status] || 'bg-stone-100 text-stone-700 border-stone-200'}`}>
                                            {order.status}
                                        </span>
                                    </td>
                                    <td className="p-4 text-stone-500 font-mono">{new Date(order.created_at).toLocaleDateString()}</td>
                                    <td className="p-4 text-right flex justify-end items-center space-x-3">
                                        <button onClick={() => openDrawer(order)} className="text-xs text-stone-500 hover:text-stone-900 font-semibold">
                                            Quick Drawer
                                        </button>
                                        <Link href={`/admin/orders/${order.id}`} className="text-xs text-[#8C6554] hover:text-[#221F1F] font-bold uppercase tracking-wider">
                                            Full Page &rarr;
                                        </Link>
                                    </td>
                                </tr>
                            ))}
                        </tbody>
                    </table>
                </div>
                {/* Pagination */}
                {orders.last_page > 1 && (
                    <div className="p-4 border-t border-[#E6DFD5] flex justify-between items-center">
                        <span className="text-xs text-stone-500">Page {orders.current_page} of {orders.last_page}</span>
                        <div className="flex space-x-2">
                            {orders.prev_page_url && (
                                <a href={orders.prev_page_url} className="px-4 py-2 bg-white border border-[#E6DFD5] hover:border-[#8C6554] text-xs font-bold text-[#221F1F] rounded-lg">← Prev</a>
                            )}
                            {orders.next_page_url && (
                                <a href={orders.next_page_url} className="px-4 py-2 bg-white border border-[#E6DFD5] hover:border-[#8C6554] text-xs font-bold text-[#221F1F] rounded-lg">Next →</a>
                            )}
                        </div>
                    </div>
                )}
            </div>

            {/* Slide-over Drawer */}
            {drawerOrder && (
                <OrderDrawer
                    order={drawerOrder}
                    statusOptions={statusOptions}
                    onClose={() => setDrawerOrder(null)}
                />
            )}
        </AdminLayout>
    );
}
