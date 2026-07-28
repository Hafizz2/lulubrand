import React, { useState } from 'react';
import { useForm, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';

function CategoryNode({ category, depth = 0, onEdit, onDelete }) {
    const [expanded, setExpanded] = useState(true);
    const hasChildren = category.children && category.children.length > 0;

    return (
        <div>
            <div
                className={`flex items-center justify-between py-2.5 px-3 rounded-lg hover:bg-neutral-800/50 transition-colors group ${depth > 0 ? 'ml-6 border-l border-neutral-800 pl-4' : ''}`}
            >
                <div className="flex items-center space-x-3 flex-1 min-w-0">
                    {hasChildren ? (
                        <button onClick={() => setExpanded(!expanded)} className="text-neutral-500 hover:text-white flex-shrink-0">
                            <svg className={`w-4 h-4 transition-transform ${expanded ? 'rotate-90' : ''}`} fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M9 5l7 7-7 7" />
                            </svg>
                        </button>
                    ) : (
                        <span className="w-4 flex-shrink-0" />
                    )}

                    {/* Circular Thumbnail */}
                    <div className="w-9 h-9 rounded-full overflow-hidden bg-neutral-800 border border-neutral-700 flex-shrink-0">
                        {category.image_url ? (
                            <img src={category.image_url} alt={category.name} className="w-full h-full object-cover" />
                        ) : (
                            <div className="w-full h-full flex items-center justify-center text-[10px] text-neutral-500 font-bold uppercase">
                                {category.name.substring(0, 2)}
                            </div>
                        )}
                    </div>

                    <div>
                        <span className="text-sm font-semibold text-white">{category.name}</span>
                        <span className="text-[10px] text-neutral-500 font-mono ml-2">/{category.slug}</span>
                        {category.description && (
                            <p className="text-[11px] text-neutral-500 mt-0.5">{category.description}</p>
                        )}
                    </div>
                </div>
                <div className="flex items-center space-x-2 opacity-0 group-hover:opacity-100 transition-opacity">
                    <button onClick={() => onEdit(category)} className="text-xs text-rose-400 hover:text-rose-300 font-semibold px-2 py-1 rounded">
                        Edit
                    </button>
                    <button onClick={() => onDelete(category)} className="text-xs text-neutral-500 hover:text-rose-400 font-semibold px-2 py-1 rounded">
                        Delete
                    </button>
                </div>
            </div>
            {hasChildren && expanded && (
                <div>
                    {category.children.map(child => (
                        <CategoryNode key={child.id} category={child} depth={depth + 1} onEdit={onEdit} onDelete={onDelete} />
                    ))}
                </div>
            )}
        </div>
    );
}

export default function Index({ categories }) {
    const [editTarget, setEditTarget] = useState(null);
    const [showAddForm, setShowAddForm] = useState(false);

    const { data: newData, setData: setNewData, post: postNew, processing: processingNew, reset: resetNew } = useForm({
        name: '', parent_id: '', description: '', image_url: '', image_file: null
    });

    const { data: editData, setData: setEditData, post: postEdit, processing: processingEdit } = useForm({
        name: '', description: '', image_url: '', image_file: null
    });

    const handleEdit = (category) => {
        setEditTarget(category);
        setEditData({ name: category.name, description: category.description || '', image_url: category.image_url || '', image_file: null });
    };

    const handleDelete = (category) => {
        if (!confirm(`Delete "${category.name}"? Its subcategories will be moved to root.`)) return;
        router.delete(`/admin/categories/${category.id}`);
    };

    const submitNew = (e) => {
        e.preventDefault();
        postNew('/admin/categories', {
            forceFormData: true,
            onSuccess: () => { resetNew(); setShowAddForm(false); }
        });
    };

    const submitEdit = (e) => {
        e.preventDefault();
        postEdit(`/admin/categories/${editTarget.id}/update`, {
            forceFormData: true,
            onSuccess: () => setEditTarget(null)
        });
    };

    const flatList = [];
    const flatten = (cats, depth = 0) => {
        cats.forEach(c => {
            flatList.push({ ...c, depth });
            if (c.children) flatten(c.children, depth + 1);
        });
    };
    flatten(categories);

    return (
        <AdminLayout title="Categories & Images">
            <div className="flex flex-col lg:flex-row gap-6">
                {/* Tree Panel */}
                <div className="flex-1 bg-white border border-[#E6DFD5] rounded-xl overflow-hidden shadow-sm">
                    <div className="p-4 border-b border-[#E6DFD5] flex items-center justify-between bg-[#F9F6F0]">
                        <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F]">Categories Tree</h2>
                        <button
                            onClick={() => setShowAddForm(!showAddForm)}
                            className="px-4 py-2 bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase rounded-lg shadow-sm transition-all"
                        >
                            + Add Category
                        </button>
                    </div>

                    {/* Add Form */}
                    {showAddForm && (
                        <form onSubmit={submitNew} className="p-5 border-b border-[#E6DFD5] bg-[#F9F6F0] space-y-3">
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Category Name *</label>
                                    <input
                                        type="text"
                                        value={newData.name}
                                        onChange={e => setNewData('name', e.target.value)}
                                        required
                                        placeholder="e.g. Elegant Evening Dresses"
                                        className="w-full px-3.5 py-2.5 bg-white border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                    />
                                </div>
                                <div>
                                    <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Parent (optional)</label>
                                    <select
                                        value={newData.parent_id}
                                        onChange={e => setNewData('parent_id', e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-white border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                    >
                                        <option value="">Root (top-level)</option>
                                        {flatList.map(c => (
                                            <option key={c.id} value={c.id}>
                                                {'— '.repeat(c.depth)}{c.name}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>
                            <div>
                                <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Upload Category Image</label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={e => setNewData('image_file', e.target.files[0])}
                                    className="w-full text-xs text-stone-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#8C6554] file:text-white hover:file:bg-[#755243] cursor-pointer"
                                />
                                <span className="text-[10px] text-stone-500 mt-1 block">Or enter Image URL below:</span>
                                <input
                                    type="url"
                                    value={newData.image_url}
                                    onChange={e => setNewData('image_url', e.target.value)}
                                    placeholder="https://..."
                                    className="w-full px-3.5 py-2 mt-1 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                />
                            </div>
                            <input
                                type="text"
                                value={newData.description}
                                onChange={e => setNewData('description', e.target.value)}
                                placeholder="Short description (optional)"
                                className="w-full px-3.5 py-2.5 bg-white border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                            <div className="flex space-x-2 pt-1">
                                <button type="submit" disabled={processingNew} className="px-5 py-2 bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase rounded-lg shadow-sm">
                                    Create Category
                                </button>
                                <button type="button" onClick={() => setShowAddForm(false)} className="px-5 py-2 bg-stone-200 text-stone-700 text-xs font-bold uppercase rounded-lg">
                                    Cancel
                                </button>
                            </div>
                        </form>
                    )}

                    <div className="p-4 space-y-1">
                        {categories.length === 0 ? (
                            <p className="text-sm text-stone-500 text-center py-8">No categories yet. Add one above.</p>
                        ) : (
                            categories.map(cat => (
                                <CategoryNode key={cat.id} category={cat} onEdit={handleEdit} onDelete={handleDelete} />
                            ))
                        )}
                    </div>
                </div>

                {/* Edit Panel */}
                {editTarget && (
                    <div className="w-full lg:w-80 bg-white border border-[#E6DFD5] rounded-xl p-5 h-fit space-y-4 shadow-sm">
                        <div className="flex items-center justify-between border-b border-[#E6DFD5] pb-3">
                            <h3 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F]">Edit Category</h3>
                            <button onClick={() => setEditTarget(null)} className="text-stone-400 hover:text-stone-900 text-lg">&times;</button>
                        </div>
                        <form onSubmit={submitEdit} className="space-y-3">
                            <div>
                                <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Name</label>
                                <input
                                    type="text"
                                    value={editData.name}
                                    onChange={e => setEditData('name', e.target.value)}
                                    required
                                    className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                />
                            </div>
                            <div>
                                <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Upload New Category Image</label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={e => setEditData('image_file', e.target.files[0])}
                                    className="w-full text-xs text-stone-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-[#8C6554] file:text-white hover:file:bg-[#755243] cursor-pointer"
                                />
                                <span className="text-[10px] text-stone-500 mt-1 block">Or edit Image URL below:</span>
                                <input
                                    type="url"
                                    value={editData.image_url}
                                    onChange={e => setEditData('image_url', e.target.value)}
                                    placeholder="https://..."
                                    className="w-full px-3.5 py-2 mt-1 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                />
                            </div>
                            <div>
                                <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Description</label>
                                <textarea
                                    value={editData.description}
                                    onChange={e => setEditData('description', e.target.value)}
                                    rows="2"
                                    className="w-full px-3 py-2 bg-neutral-950 border border-neutral-800 rounded-lg text-sm text-white focus:outline-none focus:border-rose-500"
                                />
                            </div>
                            <button type="submit" disabled={processingEdit} className="w-full py-2.5 bg-rose-600 hover:bg-rose-500 text-white text-xs font-bold uppercase rounded-lg">
                                Save Changes
                            </button>
                        </form>
                    </div>
                )}
            </div>
        </AdminLayout>
    );
}
