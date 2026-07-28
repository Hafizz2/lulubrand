import React, { useState } from 'react';
import { useForm, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Index({ variants, movements, filters }) {
    const [adjustTarget, setAdjustTarget] = useState(null);
    const [search, setSearch] = useState(filters?.search || '');
    const [lowOnly, setLowOnly] = useState(filters?.low_only === '1');

    const { data, setData, post, processing, reset } = useForm({
        delta: '',
        reason: 'manual_adjustment',
    });

    const applyFilters = () => {
        router.get('/admin/stock', { search, low_only: lowOnly ? '1' : '' }, { preserveState: true });
    };

    const submitAdjust = (e) => {
        e.preventDefault();
        post(`/admin/stock/${adjustTarget.id}/adjust`, {
            onSuccess: () => { setAdjustTarget(null); reset(); }
        });
    };

    return (
        <AdminLayout title="Stock Management">
            {/* Filters */}
            <div className="bg-white border border-[#E6DFD5] rounded-xl p-4 mb-5 flex flex-col sm:flex-row gap-3 items-end shadow-sm">
                <div className="flex-1">
                    <input
                        type="text"
                        value={search}
                        onChange={e => setSearch(e.target.value)}
                        onKeyDown={e => e.key === 'Enter' && applyFilters()}
                        placeholder="Search SKU or product title..."
                        className="w-full px-4 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#8C6554]"
                    />
                </div>
                <label className="flex items-center space-x-2 cursor-pointer pb-2">
                    <input
                        type="checkbox"
                        checked={lowOnly}
                        onChange={e => setLowOnly(e.target.checked)}
                        className="rounded border-[#E6DFD5] text-[#8C6554]"
                    />
                    <span className="text-xs font-bold uppercase text-stone-600">Low Stock Only (≤5)</span>
                </label>
                <button onClick={applyFilters} className="px-5 py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase rounded-lg shadow-sm">
                    Filter
                </button>
            </div>

            <div className="grid grid-cols-1 lg:grid-cols-5 gap-6">
                {/* Variants Table (3 cols) */}
                <div className="lg:col-span-3 bg-white border border-[#E6DFD5] rounded-xl overflow-hidden shadow-sm">
                    <div className="p-4 border-b border-[#E6DFD5] bg-[#F9F6F0]">
                        <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F]">Variant Inventory</h2>
                    </div>
                    <div className="overflow-x-auto">
                        <table className="w-full text-xs text-stone-700">
                            <thead className="bg-[#F9F6F0] text-stone-500 uppercase border-b border-[#E6DFD5] font-bold">
                                <tr>
                                    <th className="p-3">SKU</th>
                                    <th className="p-3">Product</th>
                                    <th className="p-3 text-center">Stock</th>
                                    <th className="p-3 text-right">Adjust</th>
                                </tr>
                            </thead>
                            <tbody className="divide-y divide-[#E6DFD5]">
                                {variants.data.map(v => (
                                    <tr key={v.id} className="hover:bg-[#F9F6F0]/60 transition-colors">
                                        <td className="p-3 font-mono text-[#221F1F] font-bold">{v.sku}</td>
                                        <td className="p-3 font-medium text-stone-700">{v.product?.title}</td>
                                        <td className="p-3 text-center">
                                            <span className={`font-black text-sm ${v.stock_quantity === 0 ? 'text-rose-700' : v.stock_quantity <= 5 ? 'text-amber-700' : 'text-emerald-700'}`}>
                                                {v.stock_quantity}
                                            </span>
                                        </td>
                                        <td className="p-3 text-right">
                                            <button
                                                onClick={() => { setAdjustTarget(v); reset(); setData({ delta: '', reason: 'manual_adjustment' }); }}
                                                className="text-xs text-[#8C6554] hover:text-[#221F1F] font-bold uppercase tracking-wider"
                                            >
                                                Adjust →
                                            </button>
                                        </td>
                                    </tr>
                                ))}
                            </tbody>
                        </table>
                    </div>
                </div>

                {/* Sidebar: Adjust Form + Movement Log (2 cols) */}
                <div className="lg:col-span-2 space-y-4">
                    {/* Adjustment Form */}
                    {adjustTarget && (
                        <div className="bg-white border border-[#8C6554]/40 rounded-xl p-5 shadow-sm">
                            <div className="flex items-center justify-between mb-3 border-b border-[#E6DFD5] pb-2">
                                <h3 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F]">
                                    Adjust Stock: {adjustTarget.sku}
                                </h3>
                                <button onClick={() => setAdjustTarget(null)} className="text-stone-400 hover:text-stone-900 text-lg">&times;</button>
                            </div>
                            <form onSubmit={submitAdjust} className="space-y-3">
                                <div>
                                    <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Delta (+N to add, -N to reduce) *</label>
                                    <input
                                        type="number"
                                        value={data.delta}
                                        onChange={e => setData('delta', e.target.value)}
                                        required
                                        placeholder="+10 or -2"
                                        className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                    />
                                </div>
                                <div>
                                    <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Reason</label>
                                    <select
                                        value={data.reason}
                                        onChange={e => setData('reason', e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                    >
                                        <option value="manual_adjustment">Manual Adjustment</option>
                                        <option value="restock">Restock / New Shipment</option>
                                        <option value="damage">Damaged / Written Off</option>
                                        <option value="return">Customer Return</option>
                                    </select>
                                </div>
                                <button type="submit" disabled={processing} className="w-full py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase rounded-lg shadow-sm">
                                    Apply Adjustment
                                </button>
                            </form>
                        </div>
                    )}

                    {/* Stock Movements Log */}
                    <div className="bg-white border border-[#E6DFD5] rounded-xl overflow-hidden shadow-sm">
                        <div className="p-4 border-b border-[#E6DFD5] bg-[#F9F6F0]">
                            <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F]">Recent Movement Log</h2>
                        </div>
                        <div className="divide-y divide-[#E6DFD5] max-h-96 overflow-y-auto">
                            {movements.map(m => (
                                <div key={m.id} className="p-3 text-xs flex items-center justify-between hover:bg-[#F9F6F0]/60">
                                    <div>
                                        <span className="font-mono text-[#221F1F] font-bold block">{m.variant?.sku}</span>
                                        <span className="text-[10px] text-stone-500 uppercase">{m.reason}</span>
                                    </div>
                                    <div className="text-right">
                                        <span className={`font-black text-sm block ${m.delta > 0 ? 'text-emerald-700' : 'text-rose-700'}`}>
                                            {m.delta > 0 ? `+${m.delta}` : m.delta}
                                        </span>
                                        <span className="text-[10px] text-stone-400">{new Date(m.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' })}</span>
                                    </div>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
