import React, { useState } from 'react';
import { useForm, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function DeliverySettings({ shippingRates = [] }) {
    const [editingRate, setEditingRate] = useState(null);
    const [isModalOpen, setIsModalOpen] = useState(false);
    const [showCustomCountryInput, setShowCustomCountryInput] = useState(false);
    const PREDEFINED_COUNTRIES = ['Ethiopia', 'United States', 'United Kingdom', 'Germany', 'France', 'Canada', 'UAE', 'Kenya'];

    const form = useForm({
        country: 'Ethiopia',
        city: '',
        district: '',
        cost: '',
        is_active: true,
    });

    const handleOpenAddModal = () => {
        setEditingRate(null);
        setShowCustomCountryInput(false);
        form.setData({
            country: 'Ethiopia',
            city: '',
            district: '',
            cost: '',
            is_active: true,
        });
        setIsModalOpen(true);
    };

    const handleOpenEditModal = (rate) => {
        setEditingRate(rate);
        setShowCustomCountryInput(!PREDEFINED_COUNTRIES.includes(rate.country));
        form.setData({
            country: rate.country || 'Ethiopia',
            city: rate.city || '',
            district: rate.district || '',
            cost: (rate.cost_cents / 100).toFixed(2),
            is_active: rate.is_active ?? true,
        });
        setIsModalOpen(true);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        if (editingRate) {
            form.post(`/admin/settings/delivery/${editingRate.id}/update`, {
                preserveScroll: true,
                onSuccess: () => {
                    setIsModalOpen(false);
                    setEditingRate(null);
                    form.reset();
                },
            });
        } else {
            form.post('/admin/settings/delivery', {
                preserveScroll: true,
                onSuccess: () => {
                    setIsModalOpen(false);
                    form.reset();
                },
            });
        }
    };

    const handleToggleActive = (id) => {
        router.post(`/admin/settings/delivery/${id}/toggle`, {}, {
            preserveScroll: true,
        });
    };

    const handleDelete = (id, label) => {
        if (confirm(`Are you sure you want to delete the shipping rate for "${label}"?`)) {
            router.delete(`/admin/settings/delivery/${id}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <AdminLayout title="Delivery & Shipping Rates">
            <div className="space-y-6">
                <div className="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h1 className="text-xl font-bold text-gray-900">Delivery & Shipping Rates</h1>
                        <p className="text-sm text-gray-500 mt-0.5">
                            Configure shipping fees by Country, City, and District for checkout logistics.
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={handleOpenAddModal}
                        className="px-5 py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase tracking-widest rounded-xl transition-all shadow-sm flex items-center gap-1.5"
                    >
                        <span>+ Add Shipping Rate</span>
                    </button>
                </div>

                <div className="bg-white border border-[#E6DFD5] rounded-2xl overflow-hidden shadow-xs">
                    {shippingRates.length === 0 ? (
                        <div className="p-12 text-center text-stone-400 space-y-2">
                            <span className="text-3xl">🌍</span>
                            <p className="text-sm font-semibold uppercase tracking-wider">No delivery rates defined.</p>
                            <p className="text-xs">Click the button above to define your first shipping rate.</p>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left border-collapse">
                                <thead>
                                    <tr className="bg-stone-50 border-b border-[#E6DFD5] text-[10px] font-bold uppercase tracking-wider text-stone-500">
                                        <th className="px-6 py-4">Country</th>
                                        <th className="px-6 py-4">City / Region</th>
                                        <th className="px-6 py-4">District / Area</th>
                                        <th className="px-6 py-4 text-right">Shipping Cost</th>
                                        <th className="px-6 py-4 text-center">Status</th>
                                        <th className="px-6 py-4 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-stone-100 text-xs font-semibold text-stone-700">
                                    {shippingRates.map(rate => {
                                        const label = `${rate.country} - ${rate.city}${rate.district ? ' (' + rate.district + ')' : ''}`;
                                        return (
                                            <tr key={rate.id} className="hover:bg-stone-50/50 transition-colors">
                                                <td className="px-6 py-4 font-bold text-[#221F1F] uppercase">{rate.country}</td>
                                                <td className="px-6 py-4">{rate.city}</td>
                                                <td className="px-6 py-4 text-stone-500">{rate.district || '—'}</td>
                                                <td className="px-6 py-4 text-right font-mono font-bold text-[#8C6554]">
                                                    {(rate.cost_cents / 100).toFixed(2)} Birr
                                                </td>
                                                <td className="px-6 py-4 text-center">
                                                    <button
                                                        type="button"
                                                        onClick={() => handleToggleActive(rate.id)}
                                                        className={`px-2.5 py-1 rounded-full text-[9px] font-bold uppercase tracking-wider transition-colors ${
                                                            rate.is_active
                                                                ? 'bg-emerald-50 text-emerald-700 border border-emerald-200'
                                                                : 'bg-stone-100 text-stone-400 border border-stone-200'
                                                        }`}
                                                    >
                                                        {rate.is_active ? 'Active' : 'Disabled'}
                                                    </button>
                                                </td>
                                                <td className="px-6 py-4 text-right space-x-2">
                                                    <button
                                                        type="button"
                                                        onClick={() => handleOpenEditModal(rate)}
                                                        className="px-3 py-1.5 bg-stone-100 hover:bg-stone-200 rounded-lg text-[10px] font-bold uppercase text-stone-700 transition-all"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        type="button"
                                                        onClick={() => handleDelete(rate.id, label)}
                                                        className="px-3 py-1.5 bg-rose-50 border border-rose-100 hover:bg-rose-100 rounded-lg text-[10px] font-bold uppercase text-rose-700 transition-all"
                                                    >
                                                        Delete
                                                    </button>
                                                </td>
                                            </tr>
                                        );
                                    })}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>
            </div>

            {/* Add / Edit Rate Modal */}
            {isModalOpen && (
                <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-950/60 backdrop-blur-xs">
                    <div className="bg-white max-w-md w-full p-6 border border-[#E6DFD5] rounded-3xl shadow-2xl space-y-6 animate-in fade-in zoom-in duration-150">
                        <div class="flex justify-between items-center pb-3 border-b border-[#E6DFD5]">
                            <h3 className="text-sm font-bold uppercase tracking-wider text-[#221F1F]">
                                {editingRate ? 'Edit Shipping Rate' : 'Add Shipping Rate'}
                            </h3>
                            <button
                                type="button"
                                onClick={() => setIsModalOpen(false)}
                                className="text-stone-400 hover:text-[#221F1F] text-xl font-bold"
                            >
                                &times;
                            </button>
                        </div>

                        <form onSubmit={handleSubmit} className="space-y-4">
                            <div>
                                <label className="block text-[10px] font-bold uppercase tracking-wider text-stone-500 mb-1">
                                    Country *
                                </label>
                                <select
                                    value={showCustomCountryInput ? 'Other' : form.data.country}
                                    onChange={e => {
                                        const val = e.target.value;
                                        if (val === 'Other') {
                                            setShowCustomCountryInput(true);
                                            form.setData('country', '');
                                        } else {
                                            setShowCustomCountryInput(false);
                                            form.setData('country', val);
                                        }
                                    }}
                                    className="w-full px-4 py-2.5 bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl text-xs font-bold uppercase focus:outline-none focus:border-[#8C6554]"
                                    required
                                >
                                    <option value="Ethiopia">Ethiopia</option>
                                    <option value="United States">United States</option>
                                    <option value="United Kingdom">United Kingdom</option>
                                    <option value="Germany">Germany</option>
                                    <option value="France">France</option>
                                    <option value="Canada">Canada</option>
                                    <option value="UAE">UAE</option>
                                    <option value="Kenya">Kenya</option>
                                    <option value="Other">Other (Enter custom name...)</option>
                                </select>
                            </div>

                            {showCustomCountryInput && (
                                <div>
                                    <label className="block text-[10px] font-bold uppercase tracking-wider text-stone-500 mb-1">
                                        Custom Country Name *
                                    </label>
                                    <input
                                        type="text"
                                        value={form.data.country}
                                        onChange={e => form.setData('country', e.target.value)}
                                        className="w-full px-4 py-2.5 bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl text-xs focus:outline-none focus:border-[#8C6554]"
                                        placeholder="e.g. Italy, Australia"
                                        required
                                    />
                                </div>
                            )}
                            <div>
                                <label className="block text-[10px] font-bold uppercase tracking-wider text-stone-500 mb-1">
                                    City / Region Name {form.data.country === 'Ethiopia' ? '*' : '(Optional)'}
                                </label>
                                <input
                                    type="text"
                                    value={form.data.city}
                                    onChange={e => form.setData('city', e.target.value)}
                                    className="w-full px-4 py-2.5 bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl text-xs focus:outline-none focus:border-[#8C6554]"
                                    placeholder={form.data.country === 'Ethiopia' ? 'e.g. Addis Ababa, Hawassa, Adama' : 'e.g. London, New York (Optional)'}
                                    required={form.data.country === 'Ethiopia'}
                                />
                            </div>

                            {form.data.city === 'Addis Ababa' && (
                                <div>
                                    <label className="block text-[10px] font-bold uppercase tracking-wider text-stone-500 mb-1">
                                        District / Area (for Addis Ababa) *
                                    </label>
                                    <input
                                        type="text"
                                        value={form.data.district}
                                        onChange={e => form.setData('district', e.target.value)}
                                        className="w-full px-4 py-2.5 bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl text-xs focus:outline-none focus:border-[#8C6554]"
                                        placeholder="e.g. Bole, Mexico, Megenagna, Kazanchis"
                                        required={form.data.city === 'Addis Ababa'}
                                    />
                                </div>
                            )}

                            <div>
                                <label className="block text-[10px] font-bold uppercase tracking-wider text-stone-500 mb-1">
                                    Shipping Cost (Birr) (Optional)
                                </label>
                                <input
                                    type="number"
                                    step="0.01"
                                    value={form.data.cost}
                                    onChange={e => form.setData('cost', e.target.value)}
                                    className="w-full px-4 py-2.5 bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl text-xs focus:outline-none focus:border-[#8C6554] font-mono font-bold text-[#8C6554]"
                                    placeholder="0.00"
                                />
                            </div>

                            <div className="flex items-center space-x-2 pt-2">
                                <input
                                    type="checkbox"
                                    id="is_active"
                                    checked={form.data.is_active}
                                    onChange={e => form.setData('is_active', e.target.checked)}
                                    className="rounded border-[#E6DFD5] text-[#8C6554] focus:ring-[#8C6554]"
                                />
                                <label htmlFor="is_active" className="text-[10px] font-bold uppercase tracking-wider text-stone-600">
                                    Active (Enable rate on checkout)
                                </label>
                            </div>

                            <div class="flex justify-end gap-2 pt-4 border-t border-[#E6DFD5]">
                                <button
                                    type="button"
                                    onClick={() => setIsModalOpen(false)}
                                    className="px-4 py-2 bg-stone-100 hover:bg-stone-200 rounded-xl text-[10px] font-bold uppercase text-stone-700"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={form.processing}
                                    className="px-5 py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase tracking-widest rounded-xl disabled:opacity-50"
                                >
                                    {form.processing ? 'Saving...' : 'Save Rate'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
