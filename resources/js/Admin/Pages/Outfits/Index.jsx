import React, { useState, useEffect } from 'react';
import { useForm, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import ProductSelector from '../../Components/ProductSelector';

export default function Index({ outfits, products }) {
    const [isEditing, setIsEditing] = useState(false);
    const [editingOutfitId, setEditingOutfitId] = useState(null);
    const [showFormModal, setShowFormModal] = useState(false);
    const [imagePreview, setImagePreview] = useState(null);
    const [galleryPreviews, setGalleryPreviews] = useState([]);
    const [galleryUrls, setGalleryUrls] = useState([]);

    const { data, setData, post, delete: destroy, processing, errors, reset } = useForm({
        name: '',
        description: '',
        status: 'published',
        product_ids: [],
        image_file: null,
        image_url: '',
        images_urls: [],
    });

    useEffect(() => {
        setData('images_urls', galleryUrls);
    }, [galleryUrls]);

    const handleFileChange = (e) => {
        const file = e.target.files[0];
        if (!file) return;

        setData('image_file', file);
        setData('image_url', '');

        // Read and show preview, compress on client side if possible
        const reader = new FileReader();
        reader.onload = (event) => {
            // Standard preview
            setImagePreview(event.target.result);

            // Optional client-side image compression using canvas
            const img = new Image();
            img.src = event.target.result;
            img.onload = () => {
                const canvas = document.createElement('canvas');
                const MAX_WIDTH = 1200;
                const MAX_HEIGHT = 1200;
                let width = img.width;
                let height = img.height;

                if (width > height) {
                    if (width > MAX_WIDTH) {
                        height *= MAX_WIDTH / width;
                        width = MAX_WIDTH;
                    }
                } else {
                    if (height > MAX_HEIGHT) {
                        width *= MAX_HEIGHT / height;
                        height = MAX_HEIGHT;
                    }
                }

                canvas.width = width;
                canvas.height = height;
                const ctx = canvas.getContext('2d');
                ctx.drawImage(img, 0, 0, width, height);
                
                // Get compressed base64 data URL
                const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                setData('image_url', dataUrl);
            };
        };
        reader.readAsDataURL(file);
    };

    const handleGalleryFilesChange = (e) => {
        const files = Array.from(e.target.files);
        if (files.length === 0) return;

        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.src = event.target.result;
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const MAX_WIDTH = 1200;
                    const MAX_HEIGHT = 1200;
                    let width = img.width;
                    let height = img.height;

                    if (width > height) {
                        if (width > MAX_WIDTH) { height *= MAX_WIDTH / width; width = MAX_WIDTH; }
                    } else {
                        if (height > MAX_HEIGHT) { width *= MAX_HEIGHT / height; height = MAX_HEIGHT; }
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    const dataUrl = canvas.toDataURL('image/jpeg', 0.8);
                    setGalleryUrls(prev => [...prev, dataUrl]);
                    setGalleryPreviews(prev => [...prev, dataUrl]);
                };
            };
            reader.readAsDataURL(file);
        });
    };

    const handleRemoveGalleryImage = (index) => {
        setGalleryUrls(prev => prev.filter((_, idx) => idx !== index));
        setGalleryPreviews(prev => prev.filter((_, idx) => idx !== index));
    };

    const handleOpenCreate = () => {
        reset();
        setImagePreview(null);
        setGalleryPreviews([]);
        setGalleryUrls([]);
        setIsEditing(false);
        setEditingOutfitId(null);
        setShowFormModal(true);
    };

    const handleOpenEdit = (outfit) => {
        reset();
        setData({
            name: outfit.name || '',
            description: outfit.description || '',
            status: outfit.status || 'published',
            product_ids: outfit.product_ids || [],
            image_file: null,
            image_url: outfit.image_url || '',
            images_urls: outfit.images_urls || [],
        });
        setImagePreview(outfit.image_url || null);
        setGalleryPreviews(outfit.images_urls || []);
        setGalleryUrls(outfit.images_urls || []);
        setIsEditing(true);
        setEditingOutfitId(outfit.id);
        setShowFormModal(true);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        
        // We use standard FormData since we are submitting files
        if (isEditing) {
            post(`/admin/outfits/${editingOutfitId}/update`, {
                onSuccess: () => {
                    setShowFormModal(false);
                    reset();
                }
            });
        } else {
            post('/admin/outfits', {
                onSuccess: () => {
                    setShowFormModal(false);
                    reset();
                }
            });
        }
    };

    const handleDelete = (outfitId) => {
        if (confirm('Are you sure you want to delete this outfit look?')) {
            destroy(`/admin/outfits/${outfitId}`);
        }
    };

    return (
        <AdminLayout title="Outfits & Looks">
            {/* Action Bar */}
            <div className="bg-white border border-[#E6DFD5] rounded-xl p-4 mb-6 flex items-center justify-between shadow-sm">
                <div>
                    <h1 className="text-sm font-serif font-bold uppercase tracking-widest text-[#221F1F]">Curated Outfit Looks</h1>
                    <p className="text-[11px] text-stone-500 mt-1">Design head-to-toe editorial looks that shoppers can add to bag in one tap.</p>
                </div>
                <button
                    onClick={handleOpenCreate}
                    className="px-5 py-2.5 bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase tracking-wider rounded-lg shadow-sm flex items-center space-x-2 transition-all min-h-[44px] cursor-pointer"
                >
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    <span>Add Outfit Look</span>
                </button>
            </div>

            {/* Outfits List Table */}
            <div className="bg-white border border-[#E6DFD5] rounded-xl overflow-hidden shadow-sm">
                <div className="overflow-x-auto">
                    <table className="w-full text-left text-xs text-stone-700">
                        <thead className="bg-[#F9F6F0] text-stone-500 uppercase border-b border-[#E6DFD5] font-bold">
                            <tr>
                                <th className="p-4 w-24">Look Image</th>
                                <th className="p-4">Look Name</th>
                                <th className="p-4">Components</th>
                                <th className="p-4">Status</th>
                                <th className="p-4 text-right">Actions</th>
                            </tr>
                        </thead>
                        <tbody className="divide-y divide-[#E6DFD5]">
                            {outfits.data.length > 0 ? (
                                outfits.data.map((outfit) => {
                                    const componentCount = outfit.product_ids ? outfit.product_ids.length : 0;
                                    return (
                                        <tr key={outfit.id} className="hover:bg-[#F9F6F0]/60 transition-colors">
                                            <td className="p-4">
                                                <img 
                                                    src={outfit.image_url} 
                                                    alt={outfit.name} 
                                                    className="w-12 h-16 object-cover rounded border border-[#E6DFD5] bg-[#F9F6F0]" 
                                                />
                                            </td>
                                            <td className="p-4">
                                                <div>
                                                    <span className="font-bold text-[#221F1F] text-sm">{outfit.name}</span>
                                                    <span className="block text-[10px] text-stone-400 font-mono mt-0.5">/outfit/{outfit.slug}</span>
                                                    {outfit.description && (
                                                        <p className="text-[11px] text-stone-500 mt-1 line-clamp-1 max-w-sm">{outfit.description}</p>
                                                    )}
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                <div className="flex items-center space-x-2">
                                                    <span className="bg-[#8C6554]/10 text-[#8C6554] text-[10px] font-bold uppercase tracking-wider px-2 py-0.5 rounded-full border border-[#8C6554]/20">
                                                        {componentCount} Items
                                                    </span>
                                                </div>
                                            </td>
                                            <td className="p-4">
                                                {outfit.status === 'published' ? (
                                                    <span className="bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full border border-emerald-200">
                                                        Published
                                                    </span>
                                                ) : (
                                                    <span className="bg-stone-100 text-stone-600 text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full border border-stone-200">
                                                        Draft
                                                    </span>
                                                )}
                                            </td>
                                            <td className="p-4 text-right">
                                                <div className="flex items-center justify-end space-x-2.5">
                                                    <button
                                                        onClick={() => handleOpenEdit(outfit)}
                                                        className="px-3.5 py-1.5 bg-[#FAF8F5] border border-[#E6DFD5] hover:border-[#8C6554] text-stone-700 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all"
                                                    >
                                                        Edit
                                                    </button>
                                                    <button
                                                        onClick={() => handleDelete(outfit.id)}
                                                        className="px-3.5 py-1.5 bg-rose-50 border border-rose-200 hover:bg-rose-100 text-rose-700 text-[10px] font-bold uppercase tracking-wider rounded-lg transition-all"
                                                    >
                                                        Delete
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    );
                                })
                            ) : (
                                <tr>
                                    <td colSpan="5" className="p-8 text-center text-stone-400 font-medium">
                                        No curated outfits found. Click "Add Outfit Look" to design one.
                                    </td>
                                </tr>
                            )}
                        </tbody>
                    </table>
                </div>

                {/* Pagination */}
                {outfits.links && outfits.links.length > 3 && (
                    <div className="bg-[#F9F6F0] px-4 py-3 border-t border-[#E6DFD5] flex items-center justify-between">
                        <div className="flex-1 flex justify-between sm:hidden">
                            {outfits.prev_page_url ? (
                                <a href={outfits.prev_page_url} className="relative inline-flex items-center px-4 py-2 border border-[#E6DFD5] text-xs font-semibold rounded-md text-stone-700 bg-white hover:bg-stone-50">
                                    Previous
                                </a>
                            ) : (
                                <span className="opacity-50 inline-flex items-center px-4 py-2 border border-[#E6DFD5] text-xs font-semibold rounded-md text-stone-700 bg-white cursor-not-allowed">
                                    Previous
                                </span>
                            )}
                            {outfits.next_page_url ? (
                                <a href={outfits.next_page_url} className="ml-3 relative inline-flex items-center px-4 py-2 border border-[#E6DFD5] text-xs font-semibold rounded-md text-stone-700 bg-white hover:bg-stone-50">
                                    Next
                                </a>
                            ) : (
                                <span className="opacity-50 ml-3 inline-flex items-center px-4 py-2 border border-[#E6DFD5] text-xs font-semibold rounded-md text-stone-700 bg-white cursor-not-allowed">
                                    Next
                                </span>
                            )}
                        </div>
                        <div className="hidden sm:flex-1 sm:flex sm:items-center sm:justify-between">
                            <div>
                                <p className="text-xs text-stone-500 font-medium">
                                    Showing <span className="font-bold text-stone-700">{outfits.from || 0}</span> to <span className="font-bold text-stone-700">{outfits.to || 0}</span> of <span className="font-bold text-stone-700">{outfits.total}</span> results
                                </p>
                            </div>
                            <div>
                                <nav className="relative z-0 inline-flex rounded-md shadow-xs -space-x-px">
                                    {outfits.links.map((link, idx) => {
                                        return (
                                            <a
                                                key={idx}
                                                href={link.url || '#'}
                                                dangerouslySetInnerHTML={{ __html: link.label }}
                                                className={`relative inline-flex items-center px-3 py-2 border border-[#E6DFD5] text-xs font-semibold ${
                                                    link.active
                                                        ? 'z-10 bg-[#8C6554] border-[#8C6554] text-white'
                                                        : 'bg-white border-[#E6DFD5] text-stone-500 hover:bg-stone-50'
                                                } ${idx === 0 ? 'rounded-l-md' : ''} ${
                                                    idx === outfits.links.length - 1 ? 'rounded-r-md' : ''
                                                }`}
                                            />
                                        );
                                    })}
                                </nav>
                            </div>
                        </div>
                    </div>
                )}
            </div>

            {/* sliding modal / form overlay */}
            {showFormModal && (
                <div className="fixed inset-0 z-50 flex justify-end overflow-hidden bg-stone-900/60 backdrop-blur-xs">
                    <div className="w-full max-w-xl bg-white h-full flex flex-col shadow-2xl relative">
                        {/* Header */}
                        <div className="px-6 py-5 border-b border-[#E6DFD5] bg-[#FAF8F5] flex items-center justify-between">
                            <div>
                                <h3 className="text-sm font-serif font-bold uppercase tracking-widest text-[#221F1F]">
                                    {isEditing ? 'Edit Outfit Look' : 'Design Outfit Look'}
                                </h3>
                                <p className="text-[10px] text-stone-500 mt-1 uppercase font-semibold">Step-by-Step Curation</p>
                            </div>
                            <button
                                onClick={() => setShowFormModal(false)}
                                className="text-stone-400 hover:text-stone-700 transition-colors p-2"
                            >
                                <svg className="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>

                        {/* Body */}
                        <form onSubmit={handleSubmit} className="flex-1 overflow-y-auto p-6 space-y-6">
                            
                            {/* Look Name */}
                            <div>
                                <label className="block text-xs font-semibold uppercase text-stone-600 mb-1 tracking-wider">Look Name *</label>
                                <input
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    required
                                    placeholder="e.g. Silk Corset & Wide-Leg Pants Set"
                                    className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                />
                                {errors.name && <p className="text-xs text-rose-600 mt-1 font-semibold">{errors.name}</p>}
                            </div>

                            {/* Description */}
                            <div>
                                <label className="block text-xs font-semibold uppercase text-stone-600 mb-1 tracking-wider">Description</label>
                                <textarea
                                    value={data.description}
                                    onChange={(e) => setData('description', e.target.value)}
                                    rows="3"
                                    placeholder="Brief styling notes or editorial context..."
                                    className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                />
                                {errors.description && <p className="text-xs text-rose-600 mt-1 font-semibold">{errors.description}</p>}
                            </div>

                            {/* Image Uploader */}
                            <div>
                                <label className="block text-xs font-semibold uppercase text-stone-600 mb-2 tracking-wider">Outfit Look Banner Image</label>
                                <div className="flex flex-col sm:flex-row items-center gap-4">
                                    {/* Preview Frame */}
                                    <div className="w-28 h-36 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl overflow-hidden flex items-center justify-center flex-shrink-0 shadow-sm">
                                        {imagePreview ? (
                                            <img src={imagePreview} alt="Preview" className="w-full h-full object-cover" />
                                        ) : (
                                            <span className="text-[10px] text-stone-400 font-bold uppercase tracking-wider text-center p-2">No Image</span>
                                        )}
                                    </div>

                                    {/* Upload Button wrapper */}
                                    <div className="flex-1 w-full text-center sm:text-left">
                                        <label className="cursor-pointer inline-flex items-center space-x-2 px-5 py-3 bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm transition-all min-h-[44px]">
                                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                            <span>Upload Image</span>
                                            <input
                                                type="file"
                                                accept="image/*"
                                                onChange={handleFileChange}
                                                className="hidden"
                                            />
                                        </label>
                                        <p className="text-[9px] text-[#A38B7E] mt-2">Supports JPG, PNG up to 10MB.</p>
                                    </div>
                                </div>
                                {errors.image_file && <p className="text-xs text-rose-600 mt-1 font-semibold">{errors.image_file}</p>}
                                {errors.image_url && <p className="text-xs text-rose-600 mt-1 font-semibold">{errors.image_url}</p>}
                            </div>

                            {/* Gallery Images Uploader */}
                            <div className="border-t border-[#E6DFD5] pt-5">
                                <label className="block text-xs font-semibold uppercase text-[#A38B7E] mb-2 tracking-wider">Outfit Gallery Images (Multiple)</label>
                                
                                {/* Grid of existing/new gallery previews */}
                                <div className="grid grid-cols-3 sm:grid-cols-4 gap-4 mb-4">
                                    {galleryPreviews.map((url, idx) => (
                                        <div key={idx} className="relative aspect-[3/4] bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl overflow-hidden shadow-xs group">
                                            <img src={url} alt={`Gallery ${idx + 1}`} className="w-full h-full object-cover" />
                                            <button
                                                type="button"
                                                onClick={() => handleRemoveGalleryImage(idx)}
                                                className="absolute top-1 right-1 bg-rose-600 hover:bg-rose-700 text-white w-5 h-5 rounded-full flex items-center justify-center text-xs shadow-md transition-colors cursor-pointer"
                                                title="Remove Image"
                                            >
                                                &times;
                                            </button>
                                        </div>
                                    ))}
                                    
                                    {/* Add Button Box */}
                                    <label className="aspect-[3/4] border-2 border-dashed border-[#E6DFD5] hover:border-[#8C6554] rounded-xl flex flex-col items-center justify-center cursor-pointer transition-colors p-2 text-center bg-[#FAF8F5]">
                                        <svg className="w-6 h-6 text-stone-400 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 4v16m8-8H4" />
                                        </svg>
                                        <span className="text-[9px] font-bold uppercase tracking-wider text-stone-500">Add Image</span>
                                        <input
                                            type="file"
                                            accept="image/*"
                                            multiple
                                            onChange={handleGalleryFilesChange}
                                            className="hidden"
                                        />
                                    </label>
                                </div>
                                <p className="text-[9px] text-[#A38B7E]">Select multiple images to show under the outfit page gallery. Images are compressed automatically.</p>
                            </div>

                            {/* Status */}
                            <div>
                                <label className="block text-xs font-semibold uppercase text-stone-600 mb-1 tracking-wider">Status</label>
                                <select
                                    value={data.status}
                                    onChange={(e) => setData('status', e.target.value)}
                                    className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                >
                                    <option value="published">Published</option>
                                    <option value="draft">Draft</option>
                                </select>
                                {errors.status && <p className="text-xs text-rose-600 mt-1 font-semibold">{errors.status}</p>}
                            </div>

                            {/* Look Component Products Selector */}
                            <div className="border-t border-[#E6DFD5] pt-5">
                                <ProductSelector
                                    products={products}
                                    selectedIds={data.product_ids}
                                    onChange={(nextIds) => setData('product_ids', nextIds)}
                                    label="Look Components"
                                    placeholder="Search and select products to bundle in this look..."
                                />
                                {errors.product_ids && <p className="text-xs text-rose-600 mt-1 font-semibold">{errors.product_ids}</p>}
                            </div>

                            {/* Buttons */}
                            <div className="pt-4 border-t border-[#E6DFD5] flex items-center justify-end space-x-3 bg-white sticky bottom-0">
                                <button
                                    type="button"
                                    onClick={() => setShowFormModal(false)}
                                    className="px-5 py-3 border border-[#E6DFD5] hover:bg-stone-50 text-stone-700 text-xs font-bold uppercase tracking-wider rounded-xl transition-all min-h-[44px]"
                                >
                                    Cancel
                                </button>
                                <button
                                    type="submit"
                                    disabled={processing}
                                    className="px-6 py-3 bg-[#8C6554] hover:bg-[#755243] disabled:opacity-50 text-white text-xs font-bold uppercase tracking-wider rounded-xl shadow-sm transition-all min-h-[44px]"
                                >
                                    {processing ? 'Saving...' : 'Save Look'}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            )}
        </AdminLayout>
    );
}
