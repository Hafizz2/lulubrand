import React, { useState } from 'react';
import { useForm, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function SizeGuideSettings({ sizeGuides = [], settings = {} }) {
    const [editingSize, setEditingSize] = useState(null);
    const [isAddModalOpen, setIsAddModalOpen] = useState(false);
    const [isPreviewOpen, setIsPreviewOpen] = useState(false);

    // Form for Title, Unit, and How-to-Measure Description
    const settingsForm = useForm({
        size_guide_title: settings.size_guide_title || 'LULU Couture Size Guide',
        size_guide_unit: settings.size_guide_unit || 'Inches (in)',
        size_guide_description: settings.size_guide_description || '',
    });

    // Form for Adding / Editing a Size row
    const sizeForm = useForm({
        name: '',
        bust: '',
        waist: '',
        hips: '',
        length: '',
        sort_order: sizeGuides.length + 1,
        is_active: true,
    });

    const handleSaveSettings = (e) => {
        e.preventDefault();
        settingsForm.post('/admin/settings/size-guide/settings', {
            preserveScroll: true,
        });
    };

    const handleOpenAddModal = () => {
        setEditingSize(null);
        sizeForm.setData({
            name: '',
            bust: '',
            waist: '',
            hips: '',
            length: '',
            sort_order: sizeGuides.length + 1,
            is_active: true,
        });
        setIsAddModalOpen(true);
    };

    const handleOpenEditModal = (size) => {
        setEditingSize(size);
        sizeForm.setData({
            name: size.name || '',
            bust: size.bust || '',
            waist: size.waist || '',
            hips: size.hips || '',
            length: size.length || '',
            sort_order: size.sort_order ?? 0,
            is_active: size.is_active ?? true,
        });
        setIsAddModalOpen(true);
    };

    const handleSaveSize = (e) => {
        e.preventDefault();
        if (editingSize) {
            sizeForm.post(`/admin/settings/size-guide/${editingSize.id}/update`, {
                preserveScroll: true,
                onSuccess: () => {
                    setIsAddModalOpen(false);
                    setEditingSize(null);
                    sizeForm.reset();
                },
            });
        } else {
            sizeForm.post('/admin/settings/size-guide', {
                preserveScroll: true,
                onSuccess: () => {
                    setIsAddModalOpen(false);
                    sizeForm.reset();
                },
            });
        }
    };

    const handleToggleStatus = (sizeId) => {
        router.post(`/admin/settings/size-guide/${sizeId}/toggle`, {}, {
            preserveScroll: true,
        });
    };

    const handleDeleteSize = (sizeId, name) => {
        if (confirm(`Are you sure you want to delete size '${name}'?`)) {
            router.delete(`/admin/settings/size-guide/${sizeId}`, {
                preserveScroll: true,
            });
        }
    };

    return (
        <AdminLayout title="Size Guide Settings">
            <div className="space-y-8">

                {/* Info Header Banner */}
                <div className="bg-[#FAF8F5] border border-[#E6DFD5] rounded-3xl p-6 shadow-sm flex flex-col md:flex-row items-start md:items-center justify-between gap-4">
                    <div>
                        <span className="text-[10px] font-bold uppercase tracking-[0.2em] text-[#8C6554] block mb-1">
                            Storefront Integration
                        </span>
                        <h2 className="text-xl font-serif font-bold text-[#221F1F]">
                            Dynamic Size Guide & Measurement Instructions
                        </h2>
                        <p className="text-xs text-stone-500 mt-1 max-w-2xl">
                            Configure the size chart rows and detailed instructions explaining to customers how to calculate and measure their size. Changes update live across product pages.
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={() => setIsPreviewOpen(true)}
                        className="bg-[#8C6554] hover:bg-[#755243] text-white px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all flex items-center space-x-2 shadow-sm"
                    >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                        </svg>
                        <span>Preview Modal</span>
                    </button>
                </div>

                {/* Section 1: Title, Unit & Measurement Instructions */}
                <form onSubmit={handleSaveSettings} className="bg-white border border-[#E6DFD5] rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
                    <div className="border-b border-[#E6DFD5] pb-4 flex items-center justify-between">
                        <div>
                            <h3 className="text-base font-serif font-bold text-[#221F1F] uppercase tracking-wide">
                                📐 Modal Title & How-to-Measure Description
                            </h3>
                            <p className="text-xs text-stone-500 mt-0.5">
                                Provide instructions to guide customers on how to accurately calculate their bust, waist, and hips.
                            </p>
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-6">
                        <div className="md:col-span-2">
                            <label className="block text-xs font-bold uppercase tracking-wider text-[#221F1F] mb-2">
                                Size Guide Title *
                            </label>
                            <input
                                type="text"
                                value={settingsForm.data.size_guide_title}
                                onChange={(e) => settingsForm.setData('size_guide_title', e.target.value)}
                                className="w-full px-4 py-2.5 bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl text-xs font-medium focus:outline-none focus:border-[#8C6554] focus:ring-1 focus:ring-[#8C6554]"
                                placeholder="e.g. LULU Couture Size Guide"
                                required
                            />
                            {settingsForm.errors.size_guide_title && (
                                <p className="text-xs text-rose-600 mt-1">{settingsForm.errors.size_guide_title}</p>
                            )}
                        </div>

                        <div>
                            <label className="block text-xs font-bold uppercase tracking-wider text-[#221F1F] mb-2">
                                Measurement Unit *
                            </label>
                            <input
                                type="text"
                                value={settingsForm.data.size_guide_unit}
                                onChange={(e) => settingsForm.setData('size_guide_unit', e.target.value)}
                                className="w-full px-4 py-2.5 bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl text-xs font-medium focus:outline-none focus:border-[#8C6554] focus:ring-1 focus:ring-[#8C6554]"
                                placeholder="e.g. Inches (in) or cm"
                                required
                            />
                            {settingsForm.errors.size_guide_unit && (
                                <p className="text-xs text-rose-600 mt-1">{settingsForm.errors.size_guide_unit}</p>
                            )}
                        </div>
                    </div>

                    <div>
                        <label className="block text-xs font-bold uppercase tracking-wider text-[#221F1F] mb-2">
                            How to Calculate / Measure Your Size (Description & Guide)
                        </label>
                        <textarea
                            rows="5"
                            value={settingsForm.data.size_guide_description}
                            onChange={(e) => settingsForm.setData('size_guide_description', e.target.value)}
                            className="w-full p-4 bg-[#FAF8F5] border border-[#E6DFD5] rounded-2xl text-xs font-medium leading-relaxed focus:outline-none focus:border-[#8C6554] focus:ring-1 focus:ring-[#8C6554]"
                            placeholder="Explain how customers can measure their Bust, Waist, Hips, and Length..."
                        />
                        <p className="text-[11px] text-stone-400 mt-1.5">
                            Tip: You can list bullet points for Bust, Waist, Hips, and Length. Line breaks will be preserved.
                        </p>
                        {settingsForm.errors.size_guide_description && (
                            <p className="text-xs text-rose-600 mt-1">{settingsForm.errors.size_guide_description}</p>
                        )}
                    </div>

                    <div className="flex justify-end pt-2">
                        <button
                            type="submit"
                            disabled={settingsForm.processing}
                            className="bg-[#8C6554] hover:bg-[#755243] text-white px-6 py-3 rounded-full text-xs font-bold uppercase tracking-widest transition-all shadow-md disabled:opacity-50"
                        >
                            {settingsForm.processing ? 'Saving...' : 'Save Description & Settings'}
                        </button>
                    </div>
                </form>

                {/* Section 2: Size Chart Items Table */}
                <div className="bg-white border border-[#E6DFD5] rounded-3xl p-6 sm:p-8 shadow-sm space-y-6">
                    <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 border-b border-[#E6DFD5] pb-4">
                        <div>
                            <h3 className="text-base font-serif font-bold text-[#221F1F] uppercase tracking-wide">
                                📊 Size Measurement Chart Entries
                            </h3>
                            <p className="text-xs text-stone-500 mt-0.5">
                                Manage exact measurements for XS, S, M, L, XL, or custom size definitions.
                            </p>
                        </div>
                        <button
                            type="button"
                            onClick={handleOpenAddModal}
                            className="bg-[#221F1F] hover:bg-stone-800 text-white px-5 py-2.5 rounded-full text-xs font-bold uppercase tracking-wider transition-all flex items-center space-x-2 shadow-sm"
                        >
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                            </svg>
                            <span>Add New Size</span>
                        </button>
                    </div>

                    {sizeGuides.length === 0 ? (
                        <div className="text-center py-12 bg-[#FAF8F5] rounded-2xl border border-dashed border-[#E6DFD5]">
                            <p className="text-xs text-stone-500">No size definitions found in database.</p>
                            <button
                                type="button"
                                onClick={handleOpenAddModal}
                                className="mt-3 text-xs font-bold text-[#8C6554] underline uppercase tracking-wider"
                            >
                                Click here to add your first size
                            </button>
                        </div>
                    ) : (
                        <div className="overflow-x-auto">
                            <table className="w-full text-left text-xs border-collapse">
                                <thead>
                                    <tr className="bg-[#FAF8F5] text-[#221F1F] uppercase tracking-wider font-bold border-b border-[#E6DFD5]">
                                        <th className="p-3.5">Size</th>
                                        <th className="p-3.5">Bust ({settings.size_guide_unit || 'in'})</th>
                                        <th className="p-3.5">Waist ({settings.size_guide_unit || 'in'})</th>
                                        <th className="p-3.5">Hips ({settings.size_guide_unit || 'in'})</th>
                                        <th className="p-3.5">Length ({settings.size_guide_unit || 'in'})</th>
                                        <th className="p-3.5 text-center">Order</th>
                                        <th className="p-3.5 text-center">Status</th>
                                        <th className="p-3.5 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody className="divide-y divide-[#E6DFD5] text-stone-700">
                                    {sizeGuides.map((size) => (
                                        <tr key={size.id} className="hover:bg-[#FAF8F5]/50 transition-colors">
                                            <td className="p-3.5 font-bold text-[#221F1F] text-sm">
                                                {size.name}
                                            </td>
                                            <td className="p-3.5">{size.bust || '—'}</td>
                                            <td className="p-3.5">{size.waist || '—'}</td>
                                            <td className="p-3.5">{size.hips || '—'}</td>
                                            <td className="p-3.5">{size.length || '—'}</td>
                                            <td className="p-3.5 text-center font-mono">{size.sort_order}</td>
                                            <td className="p-3.5 text-center">
                                                <button
                                                    type="button"
                                                    onClick={() => handleToggleStatus(size.id)}
                                                    className={`px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider transition-all ${
                                                        size.is_active
                                                            ? 'bg-emerald-100 text-emerald-800 border border-emerald-300'
                                                            : 'bg-stone-100 text-stone-500 border border-stone-300'
                                                    }`}
                                                >
                                                    {size.is_active ? 'Active' : 'Hidden'}
                                                </button>
                                            </td>
                                            <td className="p-3.5 text-right space-x-2">
                                                <button
                                                    type="button"
                                                    onClick={() => handleOpenEditModal(size)}
                                                    className="px-3 py-1.5 bg-[#FAF8F5] border border-[#E6DFD5] hover:border-[#8C6554] rounded-lg text-[11px] font-bold uppercase text-[#221F1F] transition-all"
                                                >
                                                    Edit
                                                </button>
                                                <button
                                                    type="button"
                                                    onClick={() => handleDeleteSize(size.id, size.name)}
                                                    className="px-3 py-1.5 bg-rose-50 border border-rose-200 hover:bg-rose-100 rounded-lg text-[11px] font-bold uppercase text-rose-700 transition-all"
                                                >
                                                    Delete
                                                </button>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    )}
                </div>

                {/* Add / Edit Size Modal */}
                {isAddModalOpen && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-950/60 backdrop-blur-sm">
                        <div className="bg-white max-w-lg w-full p-6 border border-[#E6DFD5] rounded-3xl shadow-2xl space-y-6 animate-in fade-in zoom-in duration-150">
                            <div className="flex justify-between items-center pb-3 border-b border-[#E6DFD5]">
                                <h3 className="text-base font-serif font-bold uppercase tracking-wide text-[#221F1F]">
                                    {editingSize ? `Edit Size (${editingSize.name})` : 'Add New Size Entry'}
                                </h3>
                                <button
                                    type="button"
                                    onClick={() => setIsAddModalOpen(false)}
                                    className="text-stone-400 hover:text-[#221F1F] text-xl font-bold"
                                >
                                    &times;
                                </button>
                            </div>

                            <form onSubmit={handleSaveSize} className="space-y-4">
                                <div>
                                    <label className="block text-xs font-bold uppercase tracking-wider text-[#221F1F] mb-1">
                                        Size Label / Name *
                                    </label>
                                    <input
                                        type="text"
                                        value={sizeForm.data.name}
                                        onChange={(e) => sizeForm.setData('name', e.target.value)}
                                        className="w-full px-4 py-2.5 bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl text-xs font-bold uppercase focus:outline-none focus:border-[#8C6554]"
                                        placeholder="e.g. XS, S, M, L, XL, 2XL"
                                        required
                                    />
                                    {sizeForm.errors.name && (
                                        <p className="text-xs text-rose-600 mt-1">{sizeForm.errors.name}</p>
                                    )}
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-[#221F1F] mb-1">
                                            Bust Measurement
                                        </label>
                                        <input
                                            type="text"
                                            value={sizeForm.data.bust}
                                            onChange={(e) => sizeForm.setData('bust', e.target.value)}
                                            className="w-full px-4 py-2 bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl text-xs focus:outline-none focus:border-[#8C6554]"
                                            placeholder="e.g. 33 - 34"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-[#221F1F] mb-1">
                                            Waist Measurement
                                        </label>
                                        <input
                                            type="text"
                                            value={sizeForm.data.waist}
                                            onChange={(e) => sizeForm.setData('waist', e.target.value)}
                                            className="w-full px-4 py-2 bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl text-xs focus:outline-none focus:border-[#8C6554]"
                                            placeholder="e.g. 26 - 27"
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4">
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-[#221F1F] mb-1">
                                            Hips Measurement
                                        </label>
                                        <input
                                            type="text"
                                            value={sizeForm.data.hips}
                                            onChange={(e) => sizeForm.setData('hips', e.target.value)}
                                            className="w-full px-4 py-2 bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl text-xs focus:outline-none focus:border-[#8C6554]"
                                            placeholder="e.g. 36 - 37"
                                        />
                                    </div>
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-[#221F1F] mb-1">
                                            Length (Optional)
                                        </label>
                                        <input
                                            type="text"
                                            value={sizeForm.data.length}
                                            onChange={(e) => sizeForm.setData('length', e.target.value)}
                                            className="w-full px-4 py-2 bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl text-xs focus:outline-none focus:border-[#8C6554]"
                                            placeholder="e.g. 35"
                                        />
                                    </div>
                                </div>

                                <div className="grid grid-cols-2 gap-4 items-center pt-2">
                                    <div>
                                        <label className="block text-xs font-bold uppercase tracking-wider text-[#221F1F] mb-1">
                                            Sort Order
                                        </label>
                                        <input
                                            type="number"
                                            value={sizeForm.data.sort_order}
                                            onChange={(e) => sizeForm.setData('sort_order', parseInt(e.target.value) || 0)}
                                            className="w-full px-4 py-2 bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl text-xs font-mono focus:outline-none focus:border-[#8C6554]"
                                        />
                                    </div>

                                    <div className="flex items-center space-x-2 pt-5">
                                        <input
                                            type="checkbox"
                                            id="is_active"
                                            checked={sizeForm.data.is_active}
                                            onChange={(e) => sizeForm.setData('is_active', e.target.checked)}
                                            className="w-4 h-4 text-[#8C6554] border-[#E6DFD5] rounded focus:ring-[#8C6554]"
                                        />
                                        <label htmlFor="is_active" className="text-xs font-bold uppercase tracking-wider text-[#221F1F]">
                                            Visible on Storefront
                                        </label>
                                    </div>
                                </div>

                                <div className="flex justify-end space-x-3 pt-4 border-t border-[#E6DFD5]">
                                    <button
                                        type="button"
                                        onClick={() => setIsAddModalOpen(false)}
                                        className="px-5 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 rounded-full text-xs font-bold uppercase tracking-wider"
                                    >
                                        Cancel
                                    </button>
                                    <button
                                        type="submit"
                                        disabled={sizeForm.processing}
                                        className="px-6 py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white rounded-full text-xs font-bold uppercase tracking-wider shadow-md disabled:opacity-50"
                                    >
                                        {sizeForm.processing ? 'Saving...' : (editingSize ? 'Update Size' : 'Add Size')}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                )}

                {/* Live Preview Modal */}
                {isPreviewOpen && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-950/60 backdrop-blur-sm">
                        <div className="bg-white max-w-xl w-full p-6 border border-[#E6DFD5] rounded-3xl shadow-2xl space-y-5 animate-in fade-in zoom-in duration-150">
                            <div className="flex justify-between items-center pb-3 border-b border-[#E6DFD5]">
                                <div className="flex items-center space-x-2">
                                    <span className="bg-[#8C6554]/10 text-[#8C6554] text-[9px] font-bold uppercase px-2 py-0.5 rounded-full border border-[#8C6554]/20">Storefront Preview</span>
                                    <h3 className="text-sm font-serif font-bold uppercase tracking-widest text-[#221F1F]">
                                        {settingsForm.data.size_guide_title}
                                    </h3>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => setIsPreviewOpen(false)}
                                    className="text-stone-400 hover:text-[#221F1F] text-xl font-bold"
                                >
                                    &times;
                                </button>
                            </div>

                            {/* How to Measure Description Box */}
                            {settingsForm.data.size_guide_description && (
                                <div className="bg-[#FAF8F5] border border-[#E6DFD5] rounded-2xl p-4 space-y-2">
                                    <h4 className="text-[11px] font-bold uppercase tracking-wider text-[#8C6554] flex items-center space-x-1">
                                        <span>📏 How to Calculate Your Size</span>
                                    </h4>
                                    <div className="text-xs text-stone-600 font-light leading-relaxed whitespace-pre-line">
                                        {settingsForm.data.size_guide_description}
                                    </div>
                                </div>
                            )}

                            {/* Table */}
                            <div className="overflow-x-auto">
                                <table className="w-full text-xs text-left text-stone-700 border-collapse">
                                    <thead>
                                        <tr className="bg-[#F3EEE8] text-[#221F1F] uppercase tracking-wider font-bold">
                                            <th className="p-2.5 border border-[#E6DFD5]">Size</th>
                                            <th className="p-2.5 border border-[#E6DFD5]">Bust ({settingsForm.data.size_guide_unit})</th>
                                            <th className="p-2.5 border border-[#E6DFD5]">Waist ({settingsForm.data.size_guide_unit})</th>
                                            <th className="p-2.5 border border-[#E6DFD5]">Hips ({settingsForm.data.size_guide_unit})</th>
                                            <th className="p-2.5 border border-[#E6DFD5]">Length ({settingsForm.data.size_guide_unit})</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {sizeGuides.filter(s => s.is_active).map((size, idx) => (
                                            <tr key={size.id} className={idx % 2 === 1 ? 'bg-[#F9F6F0]' : 'bg-white'}>
                                                <td className="p-2.5 border border-[#E6DFD5] font-bold text-[#221F1F]">{size.name}</td>
                                                <td className="p-2.5 border border-[#E6DFD5]">{size.bust || '—'}</td>
                                                <td className="p-2.5 border border-[#E6DFD5]">{size.waist || '—'}</td>
                                                <td className="p-2.5 border border-[#E6DFD5]">{size.hips || '—'}</td>
                                                <td className="p-2.5 border border-[#E6DFD5]">{size.length || '—'}</td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            <div className="pt-2 flex justify-end">
                                <button
                                    type="button"
                                    onClick={() => setIsPreviewOpen(false)}
                                    className="px-6 py-2 bg-[#221F1F] text-white rounded-full text-xs font-bold uppercase tracking-wider"
                                >
                                    Close Preview
                                </button>
                            </div>
                        </div>
                    </div>
                )}

            </div>
        </AdminLayout>
    );
}
