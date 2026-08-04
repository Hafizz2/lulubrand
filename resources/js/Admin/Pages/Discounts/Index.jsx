import React, { useState } from 'react';
import { useForm, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Index({ discounts }) {
    const [showForm, setShowForm] = useState(false);

    const { data, setData, post, processing, errors, reset } = useForm({
        code: '',
        type: 'percentage',
        value: '',
        min_spend: '',
        max_uses: '',
        expires_at: '',
        is_active: true,
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/discounts', {
            onSuccess: () => { reset(); setShowForm(false); }
        });
    };

    const toggleDiscount = (id) => {
        router.post(`/admin/discounts/${id}/toggle`, {}, { preserveScroll: true });
    };

    const deleteDiscount = (id, code) => {
        if (!confirm(`Delete discount code "${code}"?`)) return;
        router.delete(`/admin/discounts/${id}`);
    };

    return (
        <AdminLayout title="Discount Codes">
            {/* Create Toggle */}
            <div className="mb-5">
                <button
                    onClick={() => setShowForm(!showForm)}
                    className="px-5 py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm"
                >
                    {showForm ? '— Cancel' : '+ New Discount Code'}
                </button>
            </div>

            {/* Create Form */}
            {showForm && (
                <div className="bg-white border border-[#E6DFD5] rounded-xl p-6 mb-6 shadow-sm">
                    <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-3 mb-4">Create Discount Code</h2>
                    <form onSubmit={handleSubmit} className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                        <div>
                            <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Code *</label>
                            <input
                                type="text"
                                value={data.code}
                                onChange={e => setData('code', e.target.value.toUpperCase())}
                                required
                                placeholder="e.g. LULU20"
                                className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 uppercase font-mono focus:outline-none focus:border-[#8C6554]"
                            />
                            {errors.code && <p className="text-xs text-rose-600 mt-1">{errors.code}</p>}
                        </div>
                        <div>
                            <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Type</label>
                            <select
                                value={data.type}
                                onChange={e => setData('type', e.target.value)}
                                className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            >
                                <option value="percentage">Percentage (%)</option>
                                <option value="fixed">Fixed Amount (Birr)</option>
                            </select>
                        </div>
                        <div>
                             <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">
                                Value ({data.type === 'percentage' ? '%' : 'Birr'}) *
                            </label>
                            <input
                                type="number"
                                step="0.01"
                                value={data.value}
                                onChange={e => setData('value', e.target.value)}
                                required
                                placeholder={data.type === 'percentage' ? '20' : '10.00'}
                                className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                        </div>
                        <div>
                             <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Min Spend (Birr)</label>
                            <input
                                type="number"
                                step="0.01"
                                value={data.min_spend}
                                onChange={e => setData('min_spend', e.target.value)}
                                placeholder="0 = no minimum"
                                className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                        </div>
                        <div>
                            <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Usage Limit</label>
                            <input
                                type="number"
                                value={data.max_uses}
                                onChange={e => setData('max_uses', e.target.value)}
                                placeholder="Unlimited if blank"
                                className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                        </div>
                        <div>
                            <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Expiry Date</label>
                            <input
                                type="date"
                                value={data.expires_at}
                                onChange={e => setData('expires_at', e.target.value)}
                                className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                        </div>
                        <div className="sm:col-span-2 lg:col-span-3 pt-2">
                            <button
                                type="submit"
                                disabled={processing}
                                className="px-6 py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase rounded-lg shadow-sm"
                            >
                                Save Discount Code
                            </button>
                        </div>
                    </form>
                </div>
            )}

            {/* List */}
            <div className="bg-white border border-[#E6DFD5] rounded-xl overflow-hidden shadow-sm">
                <div className="overflow-x-auto">
                    <table className="w-full text-xs text-stone-700">
                        <thead className="bg-[#F9F6F0] text-stone-500 uppercase border-b border-[#E6DFD5] font-bold">
                            <tr>
                                <th className="p-4">Code</th>
                                <th className="p-4">Discount</th>
                                <th className="p-4">Min Spend</th>
                                <th className="p-4">Uses</th>
                                <th className="p-4">Status</th>
                                <th className="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-[#E6DFD5]">
                            {discounts.data.map(d => (
                                <tr key={d.id} className="hover:bg-[#F9F6F0]/60 transition-colors">
                                    <td className="p-4 font-mono font-bold text-[#221F1F]">{d.code}</td>
                                    <td className="p-4 font-bold text-[#8C6554]">
                                        {d.type === 'percentage' ? `${d.value}% OFF` : `${(d.value / 100).toFixed(2)} Birr OFF`}
                                    </td>
                                    <td className="p-4 text-stone-600">
                                        {d.min_spend ? `${(d.min_spend / 100).toFixed(2)} Birr` : 'None'}
                                    </td>
                                    <td className="p-4 text-stone-600">
                                        {d.uses_count} {d.max_uses ? `/ ${d.max_uses}` : ''}
                                    </td>
                                    <td className="p-4">
                                        <button
                                            onClick={() => toggleDiscount(d.id)}
                                            className={`text-[10px] font-bold uppercase px-2.5 py-1 rounded-full border ${
                                                d.is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-stone-100 text-stone-600 border-stone-200'
                                            }`}
                                        >
                                            {d.is_active ? 'Active' : 'Disabled'}
                                        </button>
                                    </td>
                                    <td className="p-4 text-right">
                                        <button
                                            onClick={() => deleteDiscount(d.id, d.code)}
                                            className="text-xs text-rose-700 hover:underline font-bold uppercase tracking-wider"
                                        >
                                            Delete
                                        </button>
                                    </td>
                                </tr>
                            ))}
                            {discounts.data.length === 0 && (
                                <tr><td colSpan="6" className="p-8 text-center text-stone-500">No discount codes created yet.</td></tr>
                            )}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
