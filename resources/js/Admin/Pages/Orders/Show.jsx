import React, { useState } from 'react';
import { Head, Link, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Show({ order }) {
    const [updatingStatus, setUpdatingStatus] = useState(false);
    const [updatingPayment, setUpdatingPayment] = useState(false);
    const [copiedOrder, setCopiedOrder] = useState(false);
    const [copiedPhone, setCopiedPhone] = useState(false);

    const statusOptions = ['pending', 'confirmed', 'packed', 'shipped', 'delivered', 'cancelled', 'refunded'];

    const handleStatusChange = (newStatus) => {
        setUpdatingStatus(true);
        router.post(`/admin/orders/${order.id}/status`, {
            status: newStatus,
        }, {
            onFinish: () => setUpdatingStatus(false),
        });
    };

    const handleMarkPaid = () => {
        setUpdatingPayment(true);
        router.post(`/admin/orders/${order.id}/mark-paid`, {}, {
            onFinish: () => setUpdatingPayment(false),
        });
    };

    const copyToClipboard = (text, type) => {
        if (!text) return;
        navigator.clipboard.writeText(text);
        if (type === 'order') {
            setCopiedOrder(true);
            setTimeout(() => setCopiedOrder(false), 2000);
        } else {
            setCopiedPhone(true);
            setTimeout(() => setCopiedPhone(false), 2000);
        }
    };

    return (
        <AdminLayout>
            <Head title={`Order #${order?.order_number || ''} — Admin`} />

            <div className="space-y-8 max-w-full">
                {/* Page Breadcrumbs & Title Bar */}
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 border-b border-[#E6DFD5] pb-6">
                    <div>
                        <div className="flex items-center space-x-2 text-xs text-stone-500 mb-1">
                            <Link href="/admin/orders" className="hover:text-[#8C6554] transition-colors">Orders</Link>
                            <span>/</span>
                            <span className="text-stone-900 font-bold">#{order?.order_number}</span>
                        </div>
                        <div className="flex items-center space-x-3">
                            <h1 className="text-2xl sm:text-3xl font-serif font-bold text-[#221F1F] uppercase tracking-wider">
                                Order #{order?.order_number}
                            </h1>
                            <button
                                type="button"
                                onClick={() => copyToClipboard(order?.order_number, 'order')}
                                className="bg-[#8C6554] hover:bg-[#755243] text-white text-[10px] font-bold uppercase tracking-widest px-3 py-1 rounded-full transition-all"
                            >
                                {copiedOrder ? '✓ Copied' : 'Copy Code'}
                            </button>
                        </div>
                    </div>

                    <div className="flex flex-wrap items-center gap-3">
                        <Link
                            href={`/admin/orders/${order?.id}/invoice`}
                            target="_blank"
                            className="bg-white border border-[#E6DFD5] hover:border-[#221F1F] text-[#221F1F] text-xs font-bold uppercase tracking-widest px-5 py-2.5 rounded-full transition-all shadow-sm"
                        >
                            🖨️ Print Invoice
                        </Link>
                    </div>
                </div>

                {/* Status & Payment Action Strip */}
                <div className="grid grid-cols-1 md:grid-cols-2 gap-6 bg-white p-6 rounded-xl border border-[#E6DFD5] shadow-sm">
                    <div>
                        <label className="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">
                            Update Order Fulfillment Status:
                        </label>
                        <div className="flex flex-wrap items-center gap-2">
                            {statusOptions.map((st) => (
                                <button
                                    key={st}
                                    type="button"
                                    disabled={updatingStatus || order.status === st}
                                    onClick={() => handleStatusChange(st)}
                                    className={`px-3 py-1.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all ${
                                        order.status === st
                                            ? 'bg-[#221F1F] text-white ring-2 ring-[#221F1F]/20 shadow'
                                            : 'bg-[#F3EEE8] text-stone-700 hover:bg-[#8C6554] hover:text-white disabled:opacity-50'
                                    }`}
                                >
                                    {st}
                                </button>
                            ))}
                        </div>
                    </div>

                    <div className="flex flex-col justify-center items-start md:items-end">
                        <label className="block text-[10px] font-bold uppercase tracking-widest text-stone-500 mb-2">
                            Payment Status:
                        </label>
                        <div className="flex items-center space-x-3">
                            <span className={`px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider ${
                                order.payment_status === 'paid' ? 'bg-emerald-100 text-emerald-800' : 'bg-amber-100 text-amber-800'
                            }`}>
                                {order.payment_status}
                            </span>
                            {order.payment_status !== 'paid' && (
                                <button
                                    type="button"
                                    disabled={updatingPayment}
                                    onClick={handleMarkPaid}
                                    className="bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold uppercase tracking-wider px-4 py-1.5 rounded-full transition-all shadow-sm"
                                >
                                    {updatingPayment ? 'Updating...' : '✓ Mark as Paid'}
                                </button>
                            )}
                        </div>
                    </div>
                </div>

                {/* 2-Column Full-Page Detail Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-12 gap-8 items-start">
                    
                    {/* Left Side: Order Items List (lg:col-span-8) */}
                    <div className="lg:col-span-8 space-y-6">
                        <div className="bg-white border border-[#E6DFD5] rounded-xl p-6 sm:p-8 shadow-sm">
                            <h2 className="text-sm font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-4 mb-6">
                                Ordered Products ({order.items ? order.items.length : 0})
                            </h2>

                            <div className="space-y-6">
                                {order.items && order.items.map((item) => {
                                    const imgUrl = item.variant?.image?.url || item.product?.primary_image?.url || 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=400&q=80';
                                    const productCode = item.product?.product_code || 'N/A';
                                    const productId = item.product_id || item.product?.id;

                                    return (
                                        <div key={item.id} className="flex flex-col sm:flex-row sm:items-center justify-between p-4 bg-[#F9F6F0] rounded-xl border border-[#E6DFD5] gap-4 hover:border-[#8C6554] transition-colors group">
                                            <div className="flex items-center space-x-4">
                                                <img
                                                    src={imgUrl}
                                                    alt={item.product_title}
                                                    className="w-16 h-20 object-cover bg-white rounded-lg border border-[#E6DFD5] flex-shrink-0"
                                                />
                                                <div className="space-y-1">
                                                    {/* Clickable Product Link */}
                                                    <Link
                                                        href={`/admin/products?search=${encodeURIComponent(item.product_title)}`}
                                                        className="text-sm font-bold uppercase tracking-wider text-[#221F1F] group-hover:text-[#8C6554] hover:underline transition-colors block"
                                                        title="Click to view/edit product in admin"
                                                    >
                                                        {item.product_title} ↗
                                                    </Link>

                                                    {/* Product ID, Custom Product Code, & SKU */}
                                                    <div className="flex flex-wrap items-center gap-2 text-[11px] font-mono text-stone-600">
                                                        {productId && <span className="bg-white px-2 py-0.5 rounded border border-stone-200">ID: #{productId}</span>}
                                                        <span className="bg-[#8C6554] text-white px-2 py-0.5 rounded font-bold uppercase">Code: {productCode}</span>
                                                        <span className="bg-stone-200 text-stone-800 px-2 py-0.5 rounded">SKU: {item.variant_sku}</span>
                                                    </div>

                                                    <div className="text-xs text-stone-500 font-semibold">
                                                        Quantity: <strong className="text-stone-900">{item.quantity}</strong>
                                                    </div>
                                                </div>
                                            </div>

                                            <div className="text-right sm:text-right font-mono">
                                                <div className="text-xs text-stone-500">Unit: {(item.unit_price / 100).toFixed(2)} Birr</div>
                                                <div className="text-base font-bold text-[#8C6554]">{(item.total_price / 100).toFixed(2)} Birr</div>
                                            </div>
                                        </div>
                                    );
                                })}
                            </div>

                            {/* Financial Summary Table */}
                            <div className="mt-8 pt-6 border-t border-[#E6DFD5] space-y-3 text-xs font-semibold">
                                <div className="flex justify-between text-stone-600">
                                    <span>Subtotal</span>
                                    <span className="font-mono text-stone-900">{(order.subtotal / 100).toFixed(2)} Birr</span>
                                </div>
                                {order.discount_amount > 0 && (
                                    <div className="flex justify-between text-emerald-700">
                                        <span>Discount</span>
                                        <span className="font-mono">-{(order.discount_amount / 100).toFixed(2)} Birr</span>
                                    </div>
                                )}
                                <div className="flex justify-between text-stone-600">
                                    <span>Delivery Fee ({order.logistics_mode})</span>
                                    <span className="font-mono text-stone-900">{(order.delivery_fee / 100).toFixed(2)} Birr</span>
                                </div>
                                <div className="flex justify-between text-base font-bold text-[#221F1F] pt-3 border-t border-[#E6DFD5]">
                                    <span>Total Amount</span>
                                    <span className="font-mono text-[#8C6554]">{(order.total / 100).toFixed(2)} Birr</span>
                                </div>

                                {order.deposit_amount > 0 && (
                                    <div className="mt-4 p-4 bg-[#F3EEE8] rounded-xl border border-[#E6DFD5] space-y-2 text-xs">
                                        <div className="flex justify-between font-bold text-[#8C6554]">
                                            <span>Deposit Paid / Due Now</span>
                                            <span className="font-mono">{(order.deposit_amount / 100).toFixed(2)} Birr</span>
                                        </div>
                                        <div className="flex justify-between text-stone-600">
                                            <span>Balance Remaining at Handover</span>
                                            <span className="font-mono">{(order.balance_due / 100).toFixed(2)} Birr</span>
                                        </div>
                                    </div>
                                )}
                            </div>
                        </div>
                    </div>

                    {/* Right Side: Customer, Logistics & Payment Info (lg:col-span-4) */}
                    <div className="lg:col-span-4 space-y-6">
                        
                        {/* Customer Info Card */}
                        <div className="bg-white border border-[#E6DFD5] rounded-xl p-6 shadow-sm space-y-4">
                            <h3 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-3">
                                Customer Details
                            </h3>
                            <div className="space-y-2 text-xs">
                                <div>
                                    <span className="text-[10px] font-bold uppercase text-stone-400 block">Name</span>
                                    <span className="font-bold text-stone-900 text-sm">{order.customer_name}</span>
                                </div>
                                <div>
                                    <span className="text-[10px] font-bold uppercase text-stone-400 block">Phone</span>
                                    <div className="flex items-center space-x-2">
                                        <a href={`tel:${order.customer_phone}`} className="font-mono font-bold text-[#8C6554] underline">
                                            {order.customer_phone}
                                        </a>
                                        <button
                                            type="button"
                                            onClick={() => copyToClipboard(order.customer_phone, 'phone')}
                                            className="text-[9px] font-bold uppercase bg-stone-100 hover:bg-stone-200 text-stone-700 px-2 py-0.5 rounded"
                                        >
                                            {copiedPhone ? 'Copied' : 'Copy'}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Logistics Card */}
                        <div className="bg-white border border-[#E6DFD5] rounded-xl p-6 shadow-sm space-y-4">
                            <h3 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-3">
                                Fulfillment & Schedule
                            </h3>
                            <div className="space-y-3 text-xs">
                                <div>
                                    <span className="text-[10px] font-bold uppercase text-stone-400 block">Fulfillment Mode</span>
                                    <span className="font-bold uppercase text-[#8C6554] bg-[#F3EEE8] px-2.5 py-1 rounded inline-block mt-0.5">
                                        {order.logistics_mode ? order.logistics_mode.replace('_', ' ') : 'N/A'}
                                    </span>
                                </div>
                                <div>
                                    <span className="text-[10px] font-bold uppercase text-stone-400 block">Delivery Address</span>
                                    <p className="text-stone-800 font-medium leading-relaxed">
                                        {order.customer_address}, {order.customer_district ? order.customer_district + ', ' : ''}{order.customer_city}, {order.customer_country || 'Ethiopia'}
                                    </p>
                                    {order.google_maps_link && (
                                        <a href={order.google_maps_link} target="_blank" rel="noreferrer" className="text-[#8C6554] font-bold underline text-[11px] block mt-1">
                                            📍 View Google Maps Pin Link ↗
                                        </a>
                                    )}
                                </div>
                                <div className="pt-2 border-t border-stone-100">
                                    <span className="text-[10px] font-bold uppercase text-stone-400 block">Preferred Schedule</span>
                                    <p className="font-semibold text-stone-900 mt-0.5">
                                        📅 {order.preferred_date || 'N/A'} ({order.preferred_time || 'N/A'})
                                    </p>
                                </div>
                            </div>
                        </div>

                        {/* Payment & Bank Proof Card */}
                        <div className="bg-white border border-[#E6DFD5] rounded-xl p-6 shadow-sm space-y-4">
                            <h3 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-3">
                                Payment Verification
                            </h3>
                            <div className="space-y-3 text-xs">
                                <div>
                                    <span className="text-[10px] font-bold uppercase text-stone-400 block">Method</span>
                                    <span className="font-bold uppercase text-stone-900">{order.payment_method}</span>
                                </div>

                                {order.bank_account && (
                                    <div className="p-3 bg-[#F9F6F0] rounded border border-[#E6DFD5]">
                                        <span className="text-[10px] font-bold uppercase text-stone-500 block">Target Bank Account</span>
                                        <span className="font-bold text-stone-900 block">{order.bank_account.bank_name}</span>
                                        <span className="font-mono text-xs text-[#8C6554] block font-bold">{order.bank_account.account_number}</span>
                                    </div>
                                )}

                                {order.confirmed_transaction_id && (
                                    <div>
                                        <span className="text-[10px] font-bold uppercase text-stone-400 block">Transaction Reference / Link</span>
                                        <span className="font-mono text-xs text-stone-900 font-bold break-all">{order.confirmed_transaction_id}</span>
                                    </div>
                                )}

                                {order.payment_proof && (
                                    <div className="pt-2 border-t border-stone-100">
                                        <span className="text-[10px] font-bold uppercase text-stone-400 block mb-2">Uploaded Receipt Proof Screenshot</span>
                                        <a href={order.payment_proof} target="_blank" rel="noreferrer" className="block border border-[#E6DFD5] rounded-lg overflow-hidden group">
                                            <img src={order.payment_proof} alt="Payment Proof" className="w-full max-h-48 object-contain bg-stone-50 group-hover:scale-105 transition-transform" />
                                        </a>
                                        <a href={order.payment_proof} target="_blank" rel="noreferrer" className="text-[11px] font-bold text-[#8C6554] underline block text-center mt-2">
                                            🔍 Open High-Res Receipt Screenshot ↗
                                        </a>
                                    </div>
                                )}
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
