import React, { useState, useEffect } from 'react';
import { useForm } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import ImageColorPickerModal from '../../Components/ImageColorPickerModal';

export default function Create({ categories, attributes }) {
    const { data, setData, post, processing, errors } = useForm({
        title: '',
        product_code: '',
        category_id: categories?.[0]?.id || '',
        base_price: '',
        material: '',
        status: 'published',
        is_new: true,
        description: '',
        images: [],
        variants: [],
    });

    const [selectedSizes, setSelectedSizes] = useState([]);
    const [selectedColors, setSelectedColors] = useState([]);
    const [pickerModal, setPickerModal] = useState({ isOpen: false, imageUrl: '', imageIndex: null });

    const sizeAttr = attributes?.find(a => a.slug === 'size');
    const colorAttr = attributes?.find(a => a.slug === 'colour');

    // Handle Client-Side Mobile Camera Capture & Image Compression
    const handleImageUpload = (e) => {
        const files = Array.from(e.target.files);
        if (!files.length) return;

        files.forEach(file => {
            const reader = new FileReader();
            reader.onload = (event) => {
                const img = new Image();
                img.onload = () => {
                    const canvas = document.createElement('canvas');
                    const MAX_WIDTH = 1200;
                    const scaleSize = MAX_WIDTH / img.width;
                    canvas.width = (img.width > MAX_WIDTH) ? MAX_WIDTH : img.width;
                    canvas.height = (img.width > MAX_WIDTH) ? (img.height * scaleSize) : img.height;

                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, canvas.width, canvas.height);
                    const compressedDataUrl = canvas.toDataURL('image/jpeg', 0.85);

                    setData(prevData => ({
                        ...prevData,
                        images: [...prevData.images, { url: compressedDataUrl, color_value: '' }]
                    }));
                };
                img.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
    };

    const removeImage = (index) => {
        setData('images', data.images.filter((_, i) => i !== index));
    };

    const updateImageColor = (index, colorVal) => {
        const updated = [...data.images];
        updated[index].color_value = colorVal;
        setData('images', updated);
    };

    // Variant Matrix Auto-Generator
    useEffect(() => {
        if (!data.title || (!selectedSizes.length && !selectedColors.length)) {
            return;
        }

        const sizeVals = selectedSizes.length ? selectedSizes : [{ value: 'DEFAULT', id: null }];
        const colorVals = selectedColors.length ? selectedColors : [{ value: 'DEFAULT', id: null }];

        const newVariants = [];
        sizeVals.forEach(size => {
            colorVals.forEach(color => {
                const skuSlug = (data.title.replace(/[^a-zA-Z0-9]/g, '') + '-' + (size.id ? size.value : '') + '-' + (color.id ? color.value.substring(0, 3) : '')).toUpperCase().replace(/--+/g, '-');

                const attrIds = [];
                if (size.id) {
                    const parsedSizeId = typeof size.id === 'number' ? size.id : (parseInt(size.id) || size.id);
                    attrIds.push(parsedSizeId);
                }
                if (color.id) {
                    if (typeof color.id === 'string' && color.id.startsWith('custom')) {
                        attrIds.push(`custom:${color.color_code || color.value}`);
                    } else {
                        const parsedColorId = typeof color.id === 'number' ? color.id : (parseInt(color.id) || color.id);
                        attrIds.push(parsedColorId);
                    }
                }

                newVariants.push({
                    sku: skuSlug,
                    stock_quantity: 10,
                    price_override: '',
                    attribute_value_ids: attrIds,
                    size_name: size.id ? size.value : '',
                    color_name: color.id ? color.value : '',
                    label: `${size.id ? 'Size ' + size.value : ''} ${color.id ? 'Color ' + color.value : ''}`.trim()
                });
            });
        });

        setData('variants', newVariants);
    }, [selectedSizes, selectedColors, data.title]);

    const handleSizeToggle = (valObj) => {
        if (selectedSizes.some(s => s.id === valObj.id)) {
            setSelectedSizes(selectedSizes.filter(s => s.id !== valObj.id));
        } else {
            setSelectedSizes([...selectedSizes, valObj]);
        }
    };

    const handleColorToggle = (valObj) => {
        if (selectedColors.some(c => c.id === valObj.id)) {
            setSelectedColors(selectedColors.filter(c => c.id !== valObj.id));
        } else {
            setSelectedColors([...selectedColors, valObj]);
        }
    };

    const handleColorSampled = (colorData) => {
        const newColor = {
            id: 'custom-' + Date.now(),
            value: colorData.value,
            color_code: colorData.color_code
        };
        setSelectedColors(prev => [...prev, newColor]);

        if (pickerModal.imageIndex !== null && pickerModal.imageIndex !== undefined) {
            updateImageColor(pickerModal.imageIndex, colorData.value);
        }
    };

    const updateVariantField = (idx, field, value) => {
        const updated = [...data.variants];
        updated[idx][field] = value;
        setData('variants', updated);
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post('/admin/products');
    };

    return (
        <AdminLayout title="Create New Product">
            <form onSubmit={handleSubmit} className="space-y-8 max-w-4xl">

                {/* Global Validation Error Banner */}
                {Object.keys(errors).length > 0 && (
                    <div className="p-4 rounded-2xl bg-rose-50 border border-rose-200 text-rose-800 text-xs space-y-1.5 shadow-sm">
                        <div className="flex items-center space-x-2 font-bold uppercase tracking-wider text-rose-900">
                            <svg className="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                            </svg>
                            <span>Please fix the following validation errors:</span>
                        </div>
                        <ul className="list-disc list-inside space-y-1 font-medium pl-1">
                            {Object.entries(errors).map(([key, msg]) => (
                                <li key={key}>
                                    <strong className="capitalize">{key.replace(/\./g, ' ')}:</strong> {msg}
                                </li>
                            ))}
                        </ul>
                    </div>
                )}

                {/* 1. Core Product Details */}
                <div className="bg-white border border-[#E6DFD5] rounded-2xl p-6 space-y-4 shadow-sm">
                    <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-3">
                        1. Product Details & Material Specifications
                    </h2>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Product Title *</label>
                            <input
                                type="text"
                                value={data.title}
                                onChange={(e) => setData('title', e.target.value)}
                                required
                                placeholder="e.g. Seraphina Corset Silk Evening Dress"
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                            {errors.title && <p className="text-xs text-rose-600 mt-1">{errors.title}</p>}
                        </div>
                        <div>
                            <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Product Code</label>
                            <input
                                type="text"
                                value={data.product_code}
                                onChange={(e) => setData('product_code', e.target.value)}
                                placeholder="e.g. LULU-DR-001"
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div>
                            <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Category *</label>
                            <select
                                value={data.category_id}
                                onChange={(e) => setData('category_id', e.target.value)}
                                required
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            >
                                {categories?.map(cat => (
                                    <option key={cat.id} value={cat.id}>{cat.name}</option>
                                ))}
                            </select>
                            {errors.category_id && <p className="text-xs text-rose-600 mt-1">{errors.category_id}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Base Price ($) *</label>
                            <input
                                type="number"
                                step="0.01"
                                value={data.base_price}
                                onChange={(e) => setData('base_price', e.target.value)}
                                required
                                placeholder="189.00"
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                            {errors.base_price && <p className="text-xs text-rose-600 mt-1">{errors.base_price}</p>}
                        </div>

                        <div>
                            <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Fabric / Material</label>
                            <input
                                type="text"
                                value={data.material}
                                onChange={(e) => setData('material', e.target.value)}
                                placeholder="e.g. 100% Pure Mulberry Silk, Satin"
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                        </div>
                    </div>

                    <div>
                        <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Description</label>
                        <textarea
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            rows="4"
                            placeholder="Sculpted bodice with boning support, draped corset detail..."
                            className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                        />
                    </div>
                </div>

                {/* 2. Media Gallery & Eyedropper Color Extraction */}
                <div className="bg-white border border-[#E6DFD5] rounded-2xl p-6 space-y-4 shadow-sm">
                    <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-3">
                        2. Product Media & Color Mapping
                    </h2>

                    <div className="flex items-center gap-4">
                        <label className="cursor-pointer bg-[#8C6554] hover:bg-[#755243] text-white px-5 py-3 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm flex items-center space-x-2 transition-all min-h-[44px]">
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 9a2 2 0 012-2h0.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Take Photo / Upload Gallery</span>
                            <input
                                type="file"
                                accept="image/*"
                                multiple
                                capture="environment"
                                onChange={handleImageUpload}
                                className="hidden"
                            />
                        </label>
                    </div>

                    {data.images.length > 0 && (
                        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4 pt-2">
                            {data.images.map((img, idx) => (
                                <div key={idx} className="relative bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl p-2.5 space-y-2 group shadow-xs">
                                    <img src={img.url} alt={`Upload ${idx + 1}`} className="w-full h-36 object-cover rounded-lg" />
                                    <button
                                        type="button"
                                        onClick={() => removeImage(idx)}
                                        className="absolute top-3 right-3 bg-stone-900/80 text-white rounded-full w-6 h-6 flex items-center justify-center text-xs font-bold shadow-md hover:bg-rose-700"
                                    >
                                        ✕
                                    </button>

                                    {/* Eyedropper Surface Color Sampler Button */}
                                    <button
                                        type="button"
                                        onClick={() => setPickerModal({ isOpen: true, imageUrl: img.url, imageIndex: idx })}
                                        className="w-full py-2 px-2 bg-[#8C6554]/10 hover:bg-[#8C6554]/20 text-[#8C6554] rounded-lg text-[10px] font-bold uppercase tracking-wider transition-colors flex items-center justify-center space-x-1 min-h-[36px]"
                                    >
                                        <svg className="w-3.5 h-3.5 text-[#8C6554]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5" />
                                        </svg>
                                        <span>Pick Fabric Color</span>
                                    </button>

                                    <div>
                                        <label className="block text-[10px] font-bold uppercase text-stone-600 mb-0.5">Map to Colour:</label>
                                        <select
                                            value={img.color_value}
                                            onChange={(e) => updateImageColor(idx, e.target.value)}
                                            className="w-full text-xs bg-white border border-[#E6DFD5] rounded px-2 py-1.5 text-stone-900"
                                        >
                                            <option value="">(All Variants)</option>
                                            {selectedColors.map(c => (
                                                <option key={c.id} value={c.value}>{c.value}</option>
                                            ))}
                                        </select>
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>

                {/* 3. Sizes & Variant Matrix Builder */}
                <div className="bg-white border border-[#E6DFD5] rounded-2xl p-6 space-y-5 shadow-sm">
                    <div className="border-b border-[#E6DFD5] pb-3">
                        <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F]">
                            3. Per-Size & Per-Color Stock Matrix
                        </h2>
                        <p className="text-xs text-stone-500 mt-0.5">
                            Select available sizes and colors to generate stock rows for each variant combination.
                        </p>
                    </div>

                    {sizeAttr && (
                        <div>
                            <label className="block text-xs font-bold uppercase text-stone-700 mb-2">Available Sizes</label>
                            <div className="flex flex-wrap gap-2">
                                {sizeAttr.values.map(val => {
                                    const selected = selectedSizes.some(s => s.id === val.id);
                                    return (
                                        <button
                                            type="button"
                                            key={val.id}
                                            onClick={() => handleSizeToggle(val)}
                                            className={`px-4 py-2 text-xs font-bold uppercase rounded-xl border transition-all min-h-[44px] ${selected ? 'bg-[#8C6554] text-white border-[#8C6554] shadow-sm' : 'bg-[#FAF8F5] text-stone-700 border-[#E6DFD5] hover:border-[#8C6554]'}`}
                                        >
                                            {val.value}
                                        </button>
                                    );
                                })}
                            </div>
                        </div>
                    )}

                    {colorAttr && (
                        <div className="pt-2">
                            <div className="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-3 mb-3">
                                <label className="block text-xs font-bold uppercase text-stone-700">Available Colours</label>

                                <div className="flex flex-wrap items-center gap-2">
                                    {/* Button to open Eyedropper on uploaded images */}
                                    {data.images.length > 0 && (
                                        <button
                                            type="button"
                                            onClick={() => setPickerModal({ isOpen: true, imageUrl: data.images[0].url, imageIndex: 0 })}
                                            className="px-3 py-1.5 bg-[#8C6554]/15 hover:bg-[#8C6554]/25 border border-[#8C6554]/30 rounded-xl text-[10px] font-bold uppercase text-[#8C6554] transition-all flex items-center space-x-1.5"
                                        >
                                            <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5" />
                                            </svg>
                                            <span>🎯 Sample Fabric Color from Image</span>
                                        </button>
                                    )}
                                </div>
                            </div>

                            <div className="flex flex-wrap gap-3 items-center">
                                {colorAttr.values.map(val => {
                                    const selected = selectedColors.some(c => c.id === val.id);
                                    return (
                                        <button
                                            type="button"
                                            key={val.id}
                                            onClick={() => handleColorToggle(val)}
                                            className={`w-9 h-9 rounded-full border-2 transition-transform min-w-[36px] min-h-[36px] ${selected ? 'ring-2 ring-[#8C6554] scale-110 border-white shadow-md' : 'border-stone-300 hover:scale-110'}`}
                                            style={{ backgroundColor: val.color_code || '#000000' }}
                                            title={val.value}
                                        ></button>
                                    );
                                })}
                                {selectedColors.filter(c => String(c.id).startsWith('custom-')).map(c => (
                                    <div key={c.id} className="flex items-center space-x-1.5 bg-[#FAF8F5] px-3 py-1.5 rounded-full border border-[#E6DFD5]">
                                        <span className="w-4 h-4 rounded-full border border-stone-300 shadow-xs" style={{ backgroundColor: c.color_code }}></span>
                                        <span className="text-[10px] text-stone-900 font-mono font-bold">{c.value}</span>
                                        <button type="button" onClick={() => setSelectedColors(selectedColors.filter(sc => sc.id !== c.id))} className="text-stone-400 hover:text-rose-600 text-sm font-bold ml-1">&times;</button>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}

                    {/* Generated Combinations Matrix */}
                    {data.variants.length > 0 && (
                        <div className="pt-4 border-t border-[#E6DFD5] space-y-3">
                            <div className="flex items-center justify-between">
                                <h3 className="text-xs font-bold uppercase tracking-wider text-[#8C6554]">
                                    Variant Stock Rows ({data.variants.length})
                                </h3>
                                <span className="text-[10px] text-stone-500">Set individual stock per size & colour</span>
                            </div>
                            <div className="space-y-3">
                                {data.variants.map((v, idx) => (
                                    <div key={idx} className="bg-[#FAF8F5] p-4 rounded-xl border border-[#E6DFD5] grid grid-cols-1 sm:grid-cols-4 gap-3 items-center">
                                        <div className="sm:col-span-2">
                                            <span className="text-xs font-bold text-[#221F1F] uppercase block">{v.label || 'Standard Variant'}</span>
                                            <span className="text-[10px] text-stone-500 font-mono">Product Code: {v.sku}</span>
                                        </div>
                                        <div>
                                            <label className="block text-[9px] uppercase text-stone-500 font-bold mb-1">SKU</label>
                                            <input
                                                type="text"
                                                value={v.sku}
                                                onChange={(e) => updateVariantField(idx, 'sku', e.target.value)}
                                                className="w-full px-3 py-2 bg-white border border-[#E6DFD5] rounded-lg text-xs text-stone-900 uppercase font-mono"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-[9px] uppercase text-stone-500 font-bold mb-1">Stock Qty</label>
                                            <input
                                                type="number"
                                                min="0"
                                                value={v.stock_quantity}
                                                onChange={(e) => updateVariantField(idx, 'stock_quantity', e.target.value)}
                                                className="w-full px-3 py-2 bg-white border border-[#E6DFD5] rounded-lg text-xs font-bold text-stone-900"
                                            />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>

                <button
                    type="submit"
                    disabled={processing || data.variants.length === 0}
                    className="w-full py-4 bg-[#8C6554] hover:bg-[#755243] text-white font-bold text-xs uppercase tracking-widest rounded-full transition-all shadow-md disabled:opacity-50 min-h-[48px]"
                >
                    {processing ? 'Publishing Product...' : 'Publish Product & Variant Matrix'}
                </button>
            </form>

            {/* Image Eyedropper Fabric Color Sampler Modal */}
            <ImageColorPickerModal
                isOpen={pickerModal.isOpen}
                imageUrl={pickerModal.imageUrl}
                onClose={() => setPickerModal({ isOpen: false, imageUrl: '', imageIndex: null })}
                onColorSelected={handleColorSampled}
            />
        </AdminLayout>
    );
}
