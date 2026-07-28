import React, { useState } from 'react';
import { useForm, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

export default function Index({ products, categories, filters }) {
    const [selectedIds, setSelectedIds] = useState([]);
    const [search, setSearch] = useState(filters?.search || '');
    const [quickEditId, setQuickEditId] = useState(null);
    const [quickData, setQuickData] = useState({ base_price: '', stock_quantity: '' });

    const handleSearchSubmit = (e) => {
        e.preventDefault();
        router.get('/admin/products', { search }, { preserveState: true });
    };

    const handleSelectAll = (e) => {
        if (e.target.checked) {
            setSelectedIds(products.data.map(p => p.id));
        } else {
            setSelectedIds([]);
        }
    };

    const handleSelectOne = (id) => {
        if (selectedIds.includes(id)) {
            setSelectedIds(selectedIds.filter(i => i !== id));
        } else {
            setSelectedIds([...selectedIds, id]);
        }
    };

    const handleBulkAction = (action) => {
        if (selectedIds.length === 0) return;
        router.post('/admin/products/bulk', { ids: selectedIds, action }, {
            onSuccess: () => setSelectedIds([])
        });
    };

    const startQuickEdit = (product) => {
        setQuickEditId(product.id);
        const firstVariant = product.variants?.[0];
        setQuickData({
            base_price: (product.base_price / 100).toFixed(2),
            stock_quantity: firstVariant ? firstVariant.stock_quantity : 0,
        });
    };

    const saveQuickEdit = (productId) => {
        router.post(`/admin/products/${productId}/quick-edit`, quickData, {
            preserveScroll: true,
            onSuccess: () => setQuickEditId(null)
        });
    };

    return (
        <AdminLayout title="Products Catalog">
            {/* Action Bar & Search */}
            <div className="bg-white border border-[#E6DFD5] rounded-xl p-4 mb-6 flex flex-col md:flex-row md:items-center justify-between gap-4 shadow-sm">
                <form onSubmit={handleSearchSubmit} className="flex-1 flex items-center space-x-2">
                    <input
                        type="text"
                        value={search}
                        onChange={(e) => setSearch(e.target.value)}
                        placeholder="Search product title, custom product code, slug, or SKU..."
                        className="w-full px-4 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 placeholder-stone-400 focus:outline-none focus:border-[#8C6554]"
                    />
                    <button type="submit" className="px-5 py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase rounded-lg shadow-sm transition-all">
                        Search
                    </button>
                </form>

                <div className="flex items-center space-x-3">
                    <a
                        href="/admin/products/create"
                        className="px-5 py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm flex items-center space-x-2 transition-all"
                    >
                        <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4"/></svg>
                        <span>Add Product</span>
                    </a>
                </div>
            </div>

            {/* Bulk Actions Bar */}
            {selectedIds.length > 0 && (
                <div className="bg-[#F3EEE8] border border-[#E6DFD5] p-3 rounded-xl mb-4 flex items-center justify-between">
                    <span className="text-xs font-semibold text-[#8C6554]">
                        {selectedIds.length} item(s) selected
                    </span>
                    <div className="flex items-center space-x-2">
                        <button onClick={() => handleBulkAction('publish')} className="px-3 py-1 bg-emerald-700 hover:bg-emerald-800 text-white text-[11px] font-bold uppercase rounded-full">
                            Publish
                        </button>
                        <button onClick={() => handleBulkAction('unpublish')} className="px-3 py-1 bg-amber-700 hover:bg-amber-800 text-white text-[11px] font-bold uppercase rounded-full">
                            Unpublish
                        </button>
                        <button onClick={() => handleBulkAction('delete')} className="px-3 py-1 bg-rose-700 hover:bg-rose-800 text-white text-[11px] font-bold uppercase rounded-full">
                            Delete
                        </button>
                    </div>
                </div>
            )}

            {/* Table */}
            <div className="bg-white border border-[#E6DFD5] rounded-xl overflow-hidden shadow-sm">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs text-stone-700">
                        <thead className="bg-[#F9F6F0] text-stone-500 uppercase border-b border-[#E6DFD5] font-bold">
                            <tr>
                                <th className="p-4 w-10">
                                    <input
                                        type="checkbox"
                                        onChange={handleSelectAll}
                                        checked={selectedIds.length === products.data.length && products.data.length > 0}
                                        className="rounded border-[#E6DFD5] text-[#8C6554]"
                                    />
                                </th>
                                <th className="p-4">Product</th>
                                <th className="p-4">Category</th>
                                <th className="p-4">Base Price</th>
                                <th className="p-4">Variants / Stock</th>
                                <th className="p-4">Status</th>
                                <th className="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-[#E6DFD5]">
                            {products.data.map((product) => {
                                const isEditing = quickEditId === product.id;
                                const totalStock = product.variants ? product.variants.reduce((acc, v) => acc + v.stock_quantity, 0) : 0;
                                const primaryImg = product.primary_image ? product.primary_image.url : 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=800&q=80';

                                return (
                                    <tr key={product.id} className="hover:bg-[#F9F6F0]/60 transition-colors">
                                        <td className="p-4">
                                            <input
                                                type="checkbox"
                                                checked={selectedIds.includes(product.id)}
                                                onChange={() => handleSelectOne(product.id)}
                                                className="rounded border-[#E6DFD5] text-[#8C6554]"
                                            />
                                        </td>
                                        <td className="p-4">
                                            <div className="flex items-center space-x-3">
                                                <img src={primaryImg} alt={product.title} className="w-10 h-12 object-cover rounded border border-[#E6DFD5] bg-[#F9F6F0]" />
                                                <div>
                                                    <div className="flex flex-wrap items-center gap-1.5">
                                                        <span className="font-bold text-[#221F1F]">{product.title}</span>
                                                        <span className="text-[10px] bg-stone-100 text-stone-600 px-1.5 py-0.5 rounded font-mono border border-stone-200">ID: #{product.id}</span>
                                                        {product.product_code && (
                                                            <span className="text-[10px] bg-[#8C6554] text-white px-1.5 py-0.5 rounded font-bold font-mono">
                                                                Code: {product.product_code}
                                                            </span>
                                                        )}
                                                    </div>
                                                    <span className="text-[10px] text-stone-500 font-mono">/{product.slug}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td className="p-4 text-stone-600 font-medium">
                                            {product.category ? product.category.name : '—'}
                                        </td>
                                        <td className="p-4">
                                            {isEditing ? (
                                                <input
                                                    type="number"
                                                    step="0.01"
                                                    value={quickData.base_price}
                                                    onChange={(e) => setQuickData({ ...quickData, base_price: e.target.value })}
                                                    className="w-20 px-2 py-1 bg-[#F9F6F0] border border-[#E6DFD5] text-xs text-stone-900 rounded"
                                                />
                                            ) : (
                                                <span className="font-bold text-[#8C6554]">${(product.base_price / 100).toFixed(2)}</span>
                                            )}
                                        </td>
                                        <td className="p-4">
                                            {isEditing ? (
                                                <input
                                                    type="number"
                                                    value={quickData.stock_quantity}
                                                    onChange={(e) => setQuickData({ ...quickData, stock_quantity: e.target.value })}
                                                    className="w-20 px-2 py-1 bg-[#F9F6F0] border border-[#E6DFD5] text-xs text-stone-900 rounded"
                                                />
                                            ) : (
                                                <div>
                                                    <span className="font-semibold text-stone-900">{totalStock} in stock</span>
                                                    <span className="text-[10px] text-stone-500 block">({product.variants_count || 0} variants)</span>
                                                </div>
                                            )}
                                        </td>
                                        <td className="p-4">
                                            <span className={`text-[10px] font-bold uppercase px-2.5 py-1 rounded-full border ${
                                                product.status === 'published' ? 'bg-emerald-100 text-emerald-800 border-emerald-200' : 'bg-amber-100 text-amber-800 border-amber-200'
                                            }`}>
                                                {product.status}
                                            </span>
                                        </td>
                                        <td className="p-4 text-right">
                                            {isEditing ? (
                                                <button onClick={() => saveQuickEdit(product.id)} className="px-3 py-1 bg-emerald-700 text-white text-[11px] font-bold uppercase rounded-full">
                                                    Save
                                                </button>
                                            ) : (
                                                <div className="flex items-center justify-end space-x-2">
                                                    <button onClick={() => startQuickEdit(product)} className="text-xs text-stone-500 hover:text-stone-900 font-semibold">
                                                        Quick
                                                    </button>
                                                    <a href={`/admin/products/${product.id}/edit`} className="text-xs text-[#8C6554] hover:text-[#221F1F] font-bold uppercase tracking-wider">
                                                        Edit →
                                                    </a>
                                                </div>
                                            )}
                                        </td>
                                    </tr>
                                );
                            })}
                        </tbody>
                    </table>
                </div>
            </div>
        </AdminLayout>
    );
}
