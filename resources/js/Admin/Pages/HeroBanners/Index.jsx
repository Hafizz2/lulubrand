import React, { useState } from 'react';
import { useForm, router } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import InteractiveHeroCropModal from '../../Components/InteractiveHeroCropModal';

export default function Index({ banners }) {
    const [showAddForm, setShowAddForm] = useState(false);
    const [editTarget, setEditTarget] = useState(null);
    const [cropModal, setCropModal] = useState({
        isOpen: false,
        imageUrl: '',
        mode: 'desktop',
        initialFocal: '50% 50%',
        targetForm: 'new' // 'new' or 'edit'
    });

    const { data: newData, setData: setNewData, post: postNew, processing: processingNew, reset: resetNew } = useForm({
        title: '',
        subtitle: '',
        button_text: 'SHOP COLLECTION',
        button_url: '/categories',
        image_url: '',
        image_file: null,
        desktop_focal_position: '50% 50%',
        mobile_focal_position: '50% 30%',
        is_active: true,
        sort_order: (banners?.length || 0) + 1,
    });

    const { data: editData, setData: setEditData, post: postEdit, processing: processingEdit } = useForm({
        title: '',
        subtitle: '',
        button_text: '',
        button_url: '',
        image_url: '',
        image_file: null,
        desktop_focal_position: '50% 50%',
        mobile_focal_position: '50% 30%',
        is_active: true,
        sort_order: 0,
    });

    const handleEdit = (banner) => {
        setEditTarget(banner);
        setEditData({
            title: banner.title || '',
            subtitle: banner.subtitle || '',
            button_text: banner.button_text || 'SHOP COLLECTION',
            button_url: banner.button_url || '/categories',
            image_url: banner.image_url || '',
            image_file: null,
            desktop_focal_position: banner.desktop_focal_position || '50% 50%',
            mobile_focal_position: banner.mobile_focal_position || '50% 30%',
            is_active: banner.is_active ?? true,
            sort_order: banner.sort_order || 0,
        });
    };

    const handleDelete = (banner) => {
        if (!confirm(`Delete Hero Slide "${banner.title}"?`)) return;
        router.delete(`/admin/hero-banners/${banner.id}`);
    };

    const submitNew = (e) => {
        e.preventDefault();
        postNew('/admin/hero-banners', {
            forceFormData: true,
            onSuccess: () => { resetNew(); setShowAddForm(false); }
        });
    };

    const submitEdit = (e) => {
        e.preventDefault();
        postEdit(`/admin/hero-banners/${editTarget.id}/update`, {
            forceFormData: true,
            onSuccess: () => setEditTarget(null)
        });
    };

    const openCropModal = (mode, targetForm) => {
        const sourceData = targetForm === 'new' ? newData : editData;
        
        let url = sourceData.image_url;
        if (sourceData.image_file) {
            url = URL.createObjectURL(sourceData.image_file);
        }

        if (!url) {
            alert('Please select or upload a banner image first to adjust its crop frame!');
            return;
        }

        const initialFocal = mode === 'desktop' ? sourceData.desktop_focal_position : sourceData.mobile_focal_position;

        setCropModal({
            isOpen: true,
            imageUrl: url,
            mode: mode,
            initialFocal: initialFocal || '50% 50%',
            targetForm: targetForm
        });
    };

    const handleCropSave = (focalString) => {
        if (cropModal.targetForm === 'new') {
            if (cropModal.mode === 'desktop') {
                setNewData('desktop_focal_position', focalString);
            } else {
                setNewData('mobile_focal_position', focalString);
            }
        } else {
            if (cropModal.mode === 'desktop') {
                setEditData('desktop_focal_position', focalString);
            } else {
                setEditData('mobile_focal_position', focalString);
            }
        }
    };

    return (
        <AdminLayout title="Hero Banners & Visual Drag Crop Frame">
            <div className="space-y-6">
                
                {/* Action Header */}
                <div className="bg-white border border-[#E6DFD5] rounded-xl p-6 flex flex-col sm:flex-row sm:items-center justify-between gap-4 shadow-sm">
                    <div>
                        <h2 className="text-sm font-serif font-bold uppercase tracking-wider text-[#221F1F]">
                            Homepage Editorial Hero Banners
                        </h2>
                        <p className="text-xs text-stone-500 mt-1">
                            Upload a photo, then drag locked Desktop (16:9) & Mobile (9:16) crop frames directly on the image to set perfect focal framing.
                        </p>
                    </div>
                    <button
                        type="button"
                        onClick={() => setShowAddForm(!showAddForm)}
                        className="bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase tracking-widest px-5 py-3 rounded-full transition-all shadow-sm flex-shrink-0"
                    >
                        + Add Hero Banner
                    </button>
                </div>

                {/* Add New Slide Form */}
                {showAddForm && (
                    <form onSubmit={submitNew} className="bg-white border border-[#E6DFD5] rounded-xl p-6 shadow-sm space-y-5">
                        <h3 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-3">
                            New Hero Banner
                        </h3>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Headline Title *</label>
                                <input
                                    type="text"
                                    value={newData.title}
                                    onChange={e => setNewData('title', e.target.value)}
                                    required
                                    placeholder="e.g. New Season High-Fashion drops"
                                    className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                />
                            </div>
                            <div>
                                <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Subtitle / Tagline</label>
                                <input
                                    type="text"
                                    value={newData.subtitle}
                                    onChange={e => setNewData('subtitle', e.target.value)}
                                    placeholder="e.g. Elegance & Couture Redefined"
                                    className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                />
                            </div>
                        </div>

                        <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Button Text</label>
                                <input
                                    type="text"
                                    value={newData.button_text}
                                    onChange={e => setNewData('button_text', e.target.value)}
                                    placeholder="SHOP COLLECTION"
                                    className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                />
                            </div>
                            <div>
                                <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Button Link URL</label>
                                <input
                                    type="text"
                                    value={newData.button_url}
                                    onChange={e => setNewData('button_url', e.target.value)}
                                    placeholder="/categories or /catalog"
                                    className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                />
                            </div>
                        </div>

                        {/* Image Upload Input */}
                        <div className="bg-[#FAF8F5] p-4 rounded-xl border border-[#E6DFD5] space-y-3">
                            <label className="block text-xs font-bold uppercase tracking-wider text-[#8C6554]">
                                📸 Hero Banner Photo *
                            </label>
                            <input
                                type="file"
                                accept="image/*"
                                onChange={e => setNewData('image_file', e.target.files[0])}
                                className="w-full text-xs text-stone-700 file:mr-3 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#8C6554] file:text-white hover:file:bg-[#755243] cursor-pointer"
                            />
                            <span className="text-[10px] text-stone-400 block">Or paste Image URL fallback:</span>
                            <input
                                type="url"
                                value={newData.image_url}
                                onChange={e => setNewData('image_url', e.target.value)}
                                placeholder="https://images.unsplash.com/..."
                                className="w-full px-3 py-2 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 focus:outline-none"
                            />
                        </div>

                        {/* Interactive Drag-and-Drop Crop Buttons */}
                        <div className="bg-stone-900 text-white p-5 rounded-2xl border border-stone-800 space-y-3">
                            <span className="text-xs font-bold uppercase tracking-wider text-[#C49A9A] block">
                                ✂️ Drag & Position Locked Crop Frame per Device
                            </span>
                            <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                <button
                                    type="button"
                                    onClick={() => openCropModal('desktop', 'new')}
                                    className="px-4 py-3 bg-stone-800 hover:bg-[#8C6554] border border-stone-700 hover:border-[#8C6554] text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all flex items-center justify-between shadow-sm"
                                >
                                    <span>🖥️ Drag Desktop Crop Frame (16:9)</span>
                                    <span className="font-mono bg-white/20 px-2 py-0.5 rounded text-[10px]">{newData.desktop_focal_position}</span>
                                </button>

                                <button
                                    type="button"
                                    onClick={() => openCropModal('mobile', 'new')}
                                    className="px-4 py-3 bg-stone-800 hover:bg-[#8C6554] border border-stone-700 hover:border-[#8C6554] text-white rounded-xl text-xs font-bold uppercase tracking-wider transition-all flex items-center justify-between shadow-sm"
                                >
                                    <span>📱 Drag Mobile Crop Frame (9:16)</span>
                                    <span className="font-mono bg-white/20 px-2 py-0.5 rounded text-[10px]">{newData.mobile_focal_position}</span>
                                </button>
                            </div>
                        </div>

                        <div className="flex items-center space-x-4 pt-2">
                            <button
                                type="submit"
                                disabled={processingNew}
                                className="bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase tracking-widest px-6 py-2.5 rounded-full transition-all shadow-sm"
                            >
                                Save Banner Slide
                            </button>
                            <button
                                type="button"
                                onClick={() => setShowAddForm(false)}
                                className="bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-bold uppercase tracking-widest px-6 py-2.5 rounded-full transition-all"
                            >
                                Cancel
                            </button>
                        </div>
                    </form>
                )}

                {/* Hero Banners List Grid */}
                <div className="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {banners && banners.map((banner) => (
                        <div key={banner.id} className="bg-white border border-[#E6DFD5] rounded-xl overflow-hidden shadow-sm flex flex-col justify-between group">
                            
                            {/* Live Device Focal Previews */}
                            <div className="grid grid-cols-3 bg-stone-950 p-2.5 gap-2.5 relative">
                                {/* Desktop Preview Frame */}
                                <div className="col-span-2 relative aspect-[16/9] rounded-lg overflow-hidden bg-stone-900 border border-stone-800">
                                    <img 
                                        src={banner.image_url} 
                                        alt={banner.title} 
                                        style={{ objectPosition: banner.desktop_focal_position || '50% 50%' }}
                                        className="w-full h-full object-cover opacity-85" 
                                    />
                                    <div className="absolute top-2 left-2 bg-black/70 text-white text-[8px] font-bold uppercase px-2 py-0.5 rounded backdrop-blur-xs">
                                        🖥️ Desktop ({banner.desktop_focal_position || '50% 50%'})
                                    </div>
                                    <div className="absolute inset-0 bg-gradient-to-t from-black/80 via-transparent to-transparent p-4 flex flex-col justify-end text-white">
                                        <span className="text-[9px] font-bold uppercase tracking-widest text-[#C49A9A]">{banner.subtitle || 'COUTURE'}</span>
                                        <h3 className="text-sm font-serif font-bold uppercase truncate">{banner.title}</h3>
                                    </div>
                                </div>

                                {/* Mobile Preview Frame */}
                                <div className="col-span-1 relative aspect-[9/16] rounded-lg overflow-hidden bg-stone-900 border border-stone-800">
                                    <img 
                                        src={banner.image_url} 
                                        alt={banner.title} 
                                        style={{ objectPosition: banner.mobile_focal_position || '50% 30%' }}
                                        className="w-full h-full object-cover opacity-85" 
                                    />
                                    <div className="absolute top-2 left-2 bg-black/70 text-white text-[8px] font-bold uppercase px-1.5 py-0.5 rounded backdrop-blur-xs truncate max-w-[90%]">
                                        📱 Mobile ({banner.mobile_focal_position || '50% 30%'})
                                    </div>
                                </div>
                            </div>

                            <div className="p-4 bg-[#F9F6F0] border-t border-[#E6DFD5] flex items-center justify-between">
                                <div>
                                    <span className="text-[10px] text-stone-500 font-mono block">Desktop: {banner.desktop_focal_position || '50% 50%'} • Mobile: {banner.mobile_focal_position || '50% 30%'}</span>
                                    <span className="text-[10px] text-stone-500 font-mono block truncate">URL: {banner.button_url}</span>
                                </div>
                                <div className="flex items-center space-x-2">
                                    <button
                                        type="button"
                                        onClick={() => handleEdit(banner)}
                                        className="text-xs text-[#8C6554] font-bold uppercase tracking-wider hover:underline"
                                    >
                                        Edit
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => handleDelete(banner)}
                                        className="text-xs text-rose-700 font-bold uppercase tracking-wider hover:underline ml-2"
                                    >
                                        Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    ))}
                </div>

                {/* Edit Modal / Drawer */}
                {editTarget && (
                    <div className="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm overflow-y-auto">
                        <form onSubmit={submitEdit} className="bg-white border border-[#E6DFD5] rounded-xl p-6 max-w-xl w-full shadow-2xl space-y-4 max-h-[92vh] overflow-y-auto">
                            <div className="flex items-center justify-between border-b border-[#E6DFD5] pb-3">
                                <h3 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F]">Edit Banner & Crop Framing</h3>
                                <button type="button" onClick={() => setEditTarget(null)} className="text-stone-400 hover:text-stone-900 text-xl">&times;</button>
                            </div>

                            <div>
                                <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Headline Title *</label>
                                <input
                                    type="text"
                                    value={editData.title}
                                    onChange={e => setEditData('title', e.target.value)}
                                    required
                                    className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                />
                            </div>

                            <div>
                                <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Subtitle / Tagline</label>
                                <input
                                    type="text"
                                    value={editData.subtitle}
                                    onChange={e => setEditData('subtitle', e.target.value)}
                                    className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                />
                            </div>

                            <div className="grid grid-cols-2 gap-3">
                                <div>
                                    <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Button Text</label>
                                    <input
                                        type="text"
                                        value={editData.button_text}
                                        onChange={e => setEditData('button_text', e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                    />
                                </div>
                                <div>
                                    <label className="block text-[11px] font-semibold uppercase text-stone-600 mb-1">Button URL</label>
                                    <input
                                        type="text"
                                        value={editData.button_url}
                                        onChange={e => setEditData('button_url', e.target.value)}
                                        className="w-full px-3.5 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-lg text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                                    />
                                </div>
                            </div>

                            {/* Single Image File Input */}
                            <div className="bg-[#FAF8F5] p-3.5 rounded-xl border border-[#E6DFD5] space-y-2">
                                <label className="block text-xs font-bold uppercase tracking-wider text-[#8C6554]">
                                    Upload New Banner Image
                                </label>
                                <input
                                    type="file"
                                    accept="image/*"
                                    onChange={e => setEditData('image_file', e.target.files[0])}
                                    className="w-full text-xs text-stone-700 file:mr-3 file:py-1.5 file:px-3 file:rounded-full file:border-0 file:text-xs file:font-bold file:bg-[#8C6554] file:text-white cursor-pointer"
                                />
                            </div>

                            {/* Interactive Crop Frame Buttons */}
                            <div className="bg-stone-900 text-white p-4 rounded-xl border border-stone-800 space-y-2">
                                <span className="text-xs font-bold uppercase tracking-wider text-[#C49A9A] block">
                                    ✂️ Drag & Position Crop Frame
                                </span>
                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-3">
                                    <button
                                        type="button"
                                        onClick={() => openCropModal('desktop', 'edit')}
                                        className="px-3.5 py-2.5 bg-stone-800 hover:bg-[#8C6554] border border-stone-700 text-white rounded-lg text-xs font-bold uppercase transition-all flex items-center justify-between"
                                    >
                                        <span>🖥️ Desktop (16:9)</span>
                                        <span className="font-mono text-[10px] bg-white/20 px-1.5 py-0.5 rounded">{editData.desktop_focal_position}</span>
                                    </button>

                                    <button
                                        type="button"
                                        onClick={() => openCropModal('mobile', 'edit')}
                                        className="px-3.5 py-2.5 bg-stone-800 hover:bg-[#8C6554] border border-stone-700 text-white rounded-lg text-xs font-bold uppercase transition-all flex items-center justify-between"
                                    >
                                        <span>📱 Mobile (9:16)</span>
                                        <span className="font-mono text-[10px] bg-white/20 px-1.5 py-0.5 rounded">{editData.mobile_focal_position}</span>
                                    </button>
                                </div>
                            </div>

                            <div className="flex items-center space-x-3 pt-2">
                                <button
                                    type="submit"
                                    disabled={processingEdit}
                                    className="bg-[#8C6554] hover:bg-[#755243] text-white text-xs font-bold uppercase tracking-widest px-6 py-2.5 rounded-full transition-all shadow-sm"
                                >
                                    Update Slide
                                </button>
                                <button
                                    type="button"
                                    onClick={() => setEditTarget(null)}
                                    className="bg-stone-100 text-stone-700 text-xs font-bold uppercase tracking-widest px-6 py-2.5 rounded-full"
                                >
                                    Cancel
                                </button>
                            </div>
                        </form>
                    </div>
                )}
            </div>

            {/* Interactive Hero Drag Crop Modal */}
            <InteractiveHeroCropModal
                isOpen={cropModal.isOpen}
                imageUrl={cropModal.imageUrl}
                mode={cropModal.mode}
                initialFocal={cropModal.initialFocal}
                onClose={() => setCropModal(prev => ({ ...prev, isOpen: false }))}
                onSave={handleCropSave}
            />
        </AdminLayout>
    );
}
