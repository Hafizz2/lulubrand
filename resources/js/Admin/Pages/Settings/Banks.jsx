import React, { useState } from 'react';
import { useForm, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Banks({ banks }) {
    const [editingBank, setEditingBank] = useState(null);

    const form = useForm({
        bank_name: '',
        account_number: '',
        account_name: '',
        is_active: true,
        sort_order: 1,
    });

    const handleCreate = (e) => {
        e.preventDefault();
        form.post('/admin/settings/banks', {
            onSuccess: () => form.reset(),
        });
    };

    const handleToggle = (bankId) => {
        router.post(`/admin/settings/banks/${bankId}/toggle`);
    };

    const handleDelete = (bankId) => {
        if (!confirm('Are you sure you want to delete this bank account?')) return;
        router.delete(`/admin/settings/banks/${bankId}`);
    };

    return (
        <AdminLayout title="Manage Bank Accounts">
            <div className="grid grid-cols-1 lg:grid-cols-3 gap-6 max-w-6xl">
                {/* Form column */}
                <div className="bg-white border border-[#E6DFD5] rounded-xl p-6 h-fit space-y-4 shadow-sm">
                    <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-3">
                        ➕ Add New Bank Account
                    </h2>
                    <form onSubmit={handleCreate} className="space-y-4">
                        <div>
                            <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Bank / Wallet Name *</label>
                            <input
                                type="text"
                                placeholder="e.g. Commercial Bank of Ethiopia"
                                value={form.data.bank_name}
                                onChange={e => form.setData('bank_name', e.target.value)}
                                className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                required
                            />
                        </div>
                        <div>
                            <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Account / Phone Number *</label>
                            <input
                                type="text"
                                placeholder="e.g. 1000123456789"
                                value={form.data.account_number}
                                onChange={e => form.setData('account_number', e.target.value)}
                                className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-xs text-stone-900 font-mono focus:outline-none focus:border-[#8C6554]"
                                required
                            />
                        </div>
                        <div>
                            <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Account Holder Name *</label>
                            <input
                                type="text"
                                placeholder="e.g. LULU COUTURE PLC"
                                value={form.data.account_name}
                                onChange={e => form.setData('account_name', e.target.value)}
                                className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                required
                            />
                        </div>
                        <button
                            type="submit"
                            disabled={form.processing}
                            className="w-full py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white font-bold text-xs uppercase tracking-wider rounded-lg shadow-sm transition-all"
                        >
                            {form.processing ? 'Saving...' : 'Add Bank Account'}
                        </button>
                    </form>
                </div>

                {/* List column */}
                <div className="lg:col-span-2 space-y-4">
                    <div className="bg-white border border-[#E6DFD5] rounded-xl p-6 shadow-sm">
                        <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-4 mb-4">
                            🏦 Active Bank Accounts ({banks.length})
                        </h2>

                        {banks.length === 0 ? (
                            <p className="text-xs text-stone-500 py-6 text-center">No bank accounts configured yet.</p>
                        ) : (
                            <div className="space-y-3">
                                {banks.map(bank => (
                                    <div
                                        key={bank.id}
                                        className="bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl p-4 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4"
                                    >
                                        <div>
                                            <span className="text-sm font-bold text-[#221F1F] uppercase tracking-wide">{bank.bank_name}</span>
                                            <p className="text-xs font-mono text-[#8C6554] font-bold mt-0.5">{bank.account_number}</p>
                                            <p className="text-[11px] text-stone-500 font-medium">{bank.account_name}</p>
                                        </div>
                                        <div className="flex items-center space-x-3">
                                            <button
                                                onClick={() => handleToggle(bank.id)}
                                                className={`text-[10px] font-bold uppercase px-3 py-1 rounded-full border ${
                                                    bank.is_active ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-stone-200 text-stone-600 border-stone-300'
                                                }`}
                                            >
                                                {bank.is_active ? 'Active' : 'Disabled'}
                                            </button>
                                            <button
                                                onClick={() => handleDelete(bank.id)}
                                                className="text-xs text-rose-700 hover:underline font-bold uppercase tracking-wider"
                                            >
                                                Delete
                                            </button>
                                        </div>
                                    </div>
                                ))}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </AdminLayout>
    );
}
