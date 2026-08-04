import React from 'react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Invoice({ order }) {
    return (
        <div className="min-h-screen bg-white text-stone-900 p-8 print:p-0">
            <div className="max-w-2xl mx-auto">
                {/* Print Button */}
                <div className="flex justify-between items-center mb-8 print:hidden">
                    <h1 className="text-sm font-bold uppercase tracking-widest text-stone-500">LULU Couture — Invoice</h1>
                    <button onClick={() => window.print()} className="px-4 py-2 bg-stone-900 text-white text-xs font-bold uppercase rounded-lg">
                        Print Invoice
                    </button>
                </div>

                {/* Invoice Header */}
                <div className="flex justify-between items-start border-b border-stone-200 pb-6 mb-6">
                    <div>
                        <h2 className="text-3xl font-black uppercase tracking-widest text-stone-900">LULU</h2>
                        <p className="text-xs text-stone-400 uppercase tracking-widest">Couture Fashion</p>
                    </div>
                    <div className="text-right">
                        <p className="text-xs text-stone-500 uppercase tracking-widest">Invoice</p>
                        <p className="text-base font-black font-mono text-stone-900">{order.order_number}</p>
                        <p className="text-xs text-stone-400 mt-1">{new Date(order.created_at).toLocaleDateString()}</p>
                    </div>
                </div>

                {/* Customer Details */}
                <div className="mb-6">
                    <p className="text-[10px] font-bold uppercase tracking-widest text-stone-400 mb-1">Bill To</p>
                    <p className="font-bold text-stone-900">{order.customer_name}</p>
                    <p className="text-sm text-stone-600">{order.customer_address}, {order.customer_city}</p>
                    <p className="text-sm text-stone-600">{order.customer_phone}</p>
                </div>

                {/* Items Table */}
                <table className="w-full text-sm border-collapse mb-6">
                    <thead>
                        <tr className="bg-stone-100 text-stone-900 uppercase text-[10px] tracking-widest">
                            <th className="p-3 text-left">Item</th>
                            <th className="p-3 text-left">SKU</th>
                            <th className="p-3 text-center">Qty</th>
                            <th className="p-3 text-right">Unit</th>
                            <th className="p-3 text-right">Total</th>
                        </tr>
                    </thead>
                    <tbody>
                        {order.items?.map(item => (
                            <tr key={item.id} className="border-b border-stone-100">
                                <td className="p-3 font-semibold text-stone-900">{item.product_title}</td>
                                <td className="p-3 font-mono text-stone-500 text-xs">{item.variant_sku}</td>
                                <td className="p-3 text-center">{item.quantity}</td>
                                <td className="p-3 text-right">{(item.unit_price / 100).toFixed(2)} Birr</td>
                                <td className="p-3 text-right font-bold">{(item.total_price / 100).toFixed(2)} Birr</td>
                            </tr>
                        ))}
                    </tbody>
                    <tfoot>
                        {order.discount_amount > 0 && (
                            <tr>
                                <td colSpan="4" className="p-3 text-right text-sm text-stone-500">Discount</td>
                                <td className="p-3 text-right text-sm text-emerald-700">-{(order.discount_amount / 100).toFixed(2)} Birr</td>
                            </tr>
                        )}
                        <tr className="bg-stone-900 text-white">
                            <td colSpan="4" className="p-3 text-right text-sm font-black uppercase tracking-wider">Total Payable</td>
                            <td className="p-3 text-right text-base font-black">{(order.total / 100).toFixed(2)} Birr</td>
                        </tr>
                    </tfoot>
                </table>

                {/* Footer */}
                <div className="text-center text-xs text-stone-400 border-t border-stone-200 pt-6">
                    <p className="font-bold uppercase tracking-widest text-stone-900 mb-1">LULU Couture</p>
                    <p>Thank you for your order. For any enquiries, contact us via our website.</p>
                </div>
            </div>
        </div>
    );
}
