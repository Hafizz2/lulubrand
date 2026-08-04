import React, { useState } from 'react';
import { useForm } from '@inertiajs/react';
import AdminLayout from '../../Layouts/AdminLayout';
import ImageColorPickerModal from '../../Components/ImageColorPickerModal';

export default function Edit({ product, categories, attributes }) {
    // Initial extraction of existing sizes & colors from product variants
    const initialSizes = [];
    const initialColors = [];

    const sizeAttr = attributes?.find(a => a.slug === 'size');
    const colorAttr = attributes?.find(a => a.slug === 'colour');

    if (product.variants) {
        product.variants.forEach(v => {
            if (v.attribute_values) {
                v.attribute_values.forEach(av => {
                    if (av.attribute?.slug === 'size' || av.attribute_id === sizeAttr?.id) {
                        if (!initialSizes.some(s => s.id === av.id)) {
                            initialSizes.push({ id: av.id, value: av.value });
                        }
                    }
                    if (av.attribute?.slug === 'colour' || av.attribute_id === colorAttr?.id) {
                        if (!initialColors.some(c => c.id === av.id)) {
                            initialColors.push({ id: av.id, value: av.value, color_code: av.color_code || '#000000' });
                        }
                    }
                });
            }
        });
    }

    const { data, setData, post, processing, errors } = useForm({
        title: product.title || '',
        product_code: product.product_code || '',
        related_product_codes: product.related_product_codes || '',
        bundle_product_codes: product.bundle_product_codes || '',
        category_id: product.category_id || '',
        base_price: product.base_price ? (product.base_price / 100).toFixed(2) : '',
        material: product.material || '',
        status: product.status || 'published',
        is_new: product.is_new ?? false,
        description: product.description || '',
        images: product.images ? product.images.map(img => ({
            id: img.id,
            url: img.url,
            color_value: img.color_value || '',
            is_primary: img.is_primary ?? false
        })) : [],
        variants: product.variants ? product.variants.map(v => {
            const attrValIds = v.attribute_values ? v.attribute_values.map(av => av.id) : [];
            const sizeVal = v.attribute_values?.find(av => av.attribute?.slug === 'size' || av.attribute_id === sizeAttr?.id);
            const colorVal = v.attribute_values?.find(av => av.attribute?.slug === 'colour' || av.attribute_id === colorAttr?.id);
            return {
                id: v.id,
                sku: v.sku || '',
                stock_quantity: v.stock_quantity ?? 0,
                price_override: v.price_override ? (v.price_override / 100).toFixed(2) : '',
                attribute_value_ids: attrValIds,
                label: `${sizeVal ? 'Size ' + sizeVal.value : ''} ${colorVal ? 'Color ' + colorVal.value : ''}`.trim() || 'Standard Variant'
            };
        }) : [],
    });

    const [selectedSizes, setSelectedSizes] = useState(initialSizes);
    const [selectedColors, setSelectedColors] = useState(initialColors);
    const [pickerModal, setPickerModal] = useState({ isOpen: false, imageUrl: '', imageIndex: null });

    // Mobile Camera / Image Upload & Compression
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
                        images: [...prevData.images, { id: null, url: compressedDataUrl, color_value: '', is_primary: prevData.images.length === 0 }]
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

    // Variant Matrix Auto-Generator for Edit page
    React.useEffect(() => {
        if (!data.title || (!selectedSizes.length && !selectedColors.length)) {
            return;
        }

        const sizeVals = selectedSizes.length ? selectedSizes : [{ value: 'DEFAULT', id: null }];
        const colorVals = selectedColors.length ? selectedColors : [{ value: 'DEFAULT', id: null }];

        const newVariants = [];
        sizeVals.forEach(size => {
            colorVals.forEach(color => {
                // Find if this variant combination already exists in the original product variants
                const existing = data.variants.find(v => {
                    const hasSize = size.id ? v.attribute_value_ids.includes(size.id) : true;
                    const hasColor = color.id ? (typeof color.id === 'string' && color.id.startsWith('custom') 
                        ? v.attribute_value_ids.some(id => typeof id === 'string' && id.startsWith('custom:'))
                        : v.attribute_value_ids.includes(color.id)) : true;
                    return hasSize && hasColor;
                });

                const prefix = data.product_code ? data.product_code.replace(/[^a-zA-Z0-9]/g, '') : data.title.replace(/[^a-zA-Z0-9]/g, '');
                
                let colorSuffix = '';
                if (color.id) {
                    if (typeof color.id === 'string' && color.id.startsWith('custom')) {
                        colorSuffix = (color.color_code || color.value || '').replace('#', '');
                    } else {
                        colorSuffix = color.value.replace(/[^a-zA-Z0-9]/g, '').substring(0, 5);
                    }
                }
                const sizeSuffix = size.id ? size.value.replace(/[^a-zA-Z0-9]/g, '') : '';
                const skuSlug = `${prefix}-${sizeSuffix}-${colorSuffix}`.toUpperCase().replace(/--+/g, '-').replace(/-$/, '');

                const attrIds = [];
                if (size.id) attrIds.push(size.id);
                if (color.id) {
                    if (typeof color.id === 'string' && color.id.startsWith('custom')) {
                        attrIds.push(`custom:${color.color_code || color.value}`);
                    } else {
                        attrIds.push(color.id);
                    }
                }

                newVariants.push({
                    id: existing ? existing.id : null,
                    sku: existing ? existing.sku : skuSlug,
                    stock_quantity: existing ? existing.stock_quantity : 10,
                    price_override: existing ? existing.price_override : '',
                    attribute_value_ids: attrIds,
                    size_name: size.id ? size.value : '',
                    color_name: color.id ? color.value : '',
                    label: `${size.id ? 'Size ' + size.value : ''} ${color.id ? 'Color ' + color.value : ''}`.trim()
                });
            });
        });

        // Deduplicate SKUs across the newly generated list
        newVariants.forEach(v => {
            let baseSku = v.sku;
            let finalSku = baseSku;
            let counter = 1;
            while (newVariants.some(other => other !== v && other.sku === finalSku)) {
                finalSku = `${baseSku}-${counter}`;
                counter++;
            }
            v.sku = finalSku;
        });

        const signature = (list) => list.map(v => `${v.id}-${v.sku}-${v.stock_quantity}-${v.price_override}`).join('|');
        if (signature(newVariants) !== signature(data.variants)) {
            setData('variants', newVariants);
        }
    }, [selectedSizes, selectedColors, data.title, data.product_code]);

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
        post(`/admin/products/${product.id}/update`);
    };

    return (
        <AdminLayout title={`Edit ${product.title}`}>
            <form onSubmit={handleSubmit} className="space-y-8 max-w-4xl">

                {/* Validation Error Banner */}
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

                {/* 1. Core Product Info */}
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
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                        </div>
                        <div>
                            <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Product Code *</label>
                            <input
                                type="text"
                                value={data.product_code}
                                onChange={(e) => setData('product_code', e.target.value)}
                                required
                                placeholder="e.g. LULU-DR-001"
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                            {errors.product_code && <p className="text-xs text-rose-600 mt-1">{errors.product_code}</p>}
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Suggested Product Codes (Optional)</label>
                            <input
                                type="text"
                                value={data.related_product_codes}
                                onChange={(e) => setData('related_product_codes', e.target.value)}
                                placeholder="Comma separated, e.g. SHOE-01, BAG-02"
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                            <p className="text-[10px] text-stone-500 mt-1">Provide product codes of items to show under 'Complete the Look' suggestions list.</p>
                            {errors.related_product_codes && <p className="text-xs text-rose-600 mt-1">{errors.related_product_codes}</p>}
                        </div>
                        <div>
                            <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Full Outfit Bundle Items (Optional)</label>
                            <input
                                type="text"
                                value={data.bundle_product_codes}
                                onChange={(e) => setData('bundle_product_codes', e.target.value)}
                                placeholder="Comma separated, e.g. DRESS-01, SHOE-01, EARR-02"
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                            <p className="text-[10px] text-stone-500 mt-1">If this product is a 'Full Outfit' bundle, list the component product codes here.</p>
                            {errors.bundle_product_codes && <p className="text-xs text-rose-600 mt-1">{errors.bundle_product_codes}</p>}
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
                        </div>

                        <div>
                            <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Base Price (Birr) *</label>
                            <input
                                type="number"
                                step="0.01"
                                value={data.base_price}
                                onChange={(e) => setData('base_price', e.target.value)}
                                required
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                        </div>

                        <div>
                            <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Fabric / Material</label>
                            <input
                                type="text"
                                value={data.material}
                                onChange={(e) => setData('material', e.target.value)}
                                placeholder="e.g. 100% Heavy Silk Satin"
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            />
                        </div>
                    </div>

                    <div className="grid grid-cols-1 md:grid-cols-2 gap-4 pt-2">
                        <div>
                            <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Status</label>
                            <select
                                value={data.status}
                                onChange={(e) => setData('status', e.target.value)}
                                className="w-full px-4 py-3 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                            >
                                <option value="published">Published</option>
                                <option value="draft">Draft</option>
                                <option value="archived">Archived</option>
                            </select>
                        </div>

                        <div className="flex items-center space-x-3 pt-6">
                            <input
                                type="checkbox"
                                id="is_new"
                                checked={data.is_new}
                                onChange={(e) => setData('is_new', e.target.checked)}
                                className="w-5 h-5 text-[#8C6554] border-[#E6DFD5] rounded focus:ring-[#8C6554]"
                            />
                            <label htmlFor="is_new" className="text-xs font-bold uppercase tracking-wider text-stone-900">
                                Mark as New Arrival
                            </label>
                        </div>
                    </div>

                    <div>
                        <label className="block text-xs font-semibold uppercase text-stone-600 mb-1">Description</label>
                        <textarea
                            value={data.description}
                            onChange={(e) => setData('description', e.target.value)}
                            rows="4"
                            className="w-full p-4 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-sm text-stone-900 focus:outline-none focus:border-[#8C6554]"
                        />
                    </div>
                </div>

                {/* 2. Image Gallery & Eyedropper Color Extraction */}
                <div className="bg-white border border-[#E6DFD5] rounded-2xl p-6 space-y-4 shadow-sm">
                    <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-3">
                        2. Product Media & Color Mapping ({data.images.length} Photos)
                    </h2>

                    <div className="flex items-center gap-4">
                        <label className="cursor-pointer bg-[#8C6554] hover:bg-[#755243] text-white px-5 py-3 rounded-full text-xs font-bold uppercase tracking-wider shadow-sm flex items-center space-x-2 transition-all min-h-[44px]">
                            <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 9a2 2 0 012-2h0.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Add Photo / Take Picture</span>
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
                                    <img src={img.url} alt={`Photo ${idx + 1}`} className="w-full h-36 object-cover rounded-lg" />
                                    {idx === 0 && (
                                        <span className="absolute top-3 left-3 bg-[#8C6554] text-white text-[9px] font-bold uppercase px-2 py-0.5 rounded shadow">Primary</span>
                                    )}
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
                                            className="w-full text-xs bg-white border border-[#E6DFD5] rounded px-2 py-1 text-stone-900"
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

                {/* 3. Sizes & Variant Matrix Editor */}
                <div className="bg-white border border-[#E6DFD5] rounded-2xl p-6 space-y-4 shadow-sm">
                    <h2 className="text-xs font-serif font-bold uppercase tracking-widest text-[#221F1F] border-b border-[#E6DFD5] pb-3">
                        3. Per-Size & Per-Color Stock & Price Overrides
                    </h2>

                    {sizeAttr && (
                        <div>
                            <label className="block text-xs font-bold uppercase text-stone-700 mb-2">Sizes Selected</label>
                            <div className="flex flex-wrap gap-2">
                                {sizeAttr.values.map(val => {
                                    const selected = selectedSizes.some(s => s.id === val.id);
                                    return (
                                        <button
                                            type="button"
                                            key={val.id}
                                            onClick={() => handleSizeToggle(val)}
                                            className={`px-3.5 py-1.5 text-xs font-bold uppercase rounded-xl border transition-colors ${selected ? 'bg-[#8C6554] text-white border-[#8C6554]' : 'bg-[#FAF8F5] text-stone-700 border-[#E6DFD5] hover:border-[#8C6554]'}`}
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
                                <label className="block text-xs font-bold uppercase text-stone-700">Colours Selected</label>

                                <div className="flex flex-wrap items-center gap-2">
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
                                            className={`w-7 h-7 rounded-full border-2 transition-transform ${selected ? 'ring-2 ring-[#8C6554] scale-110 border-white' : 'border-stone-300 hover:scale-110'}`}
                                            style={{ backgroundColor: val.color_code || '#000000' }}
                                            title={val.value}
                                        ></button>
                                    );
                                })}
                            </div>
                        </div>
                    )}

                    {data.variants.length > 0 && (
                        <div className="pt-4 border-t border-[#E6DFD5] space-y-3">
                            <h3 className="text-xs font-bold uppercase tracking-wider text-[#8C6554]">
                                Variant Matrix ({data.variants.length} Stock Rows)
                            </h3>
                            <div className="space-y-3">
                                {data.variants.map((v, idx) => (
                                    <div key={idx} className="bg-[#FAF8F5] p-4 rounded-xl border border-[#E6DFD5] grid grid-cols-1 sm:grid-cols-4 gap-3 items-center">
                                        <div className="sm:col-span-1">
                                            <span className="text-xs font-bold text-[#221F1F] uppercase block">{v.label || `Variant #${idx + 1}`}</span>
                                        </div>
                                        <div>
                                            <label className="block text-[9px] uppercase text-stone-500 font-bold mb-1">SKU</label>
                                            <input
                                                type="text"
                                                value={v.sku}
                                                onChange={(e) => updateVariantField(idx, 'sku', e.target.value)}
                                                className="w-full px-3 py-1.5 bg-white border border-[#E6DFD5] rounded text-xs text-stone-900 uppercase font-mono"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-[9px] uppercase text-stone-500 font-bold mb-1">Stock Quantity</label>
                                            <input
                                                type="number"
                                                min="0"
                                                value={v.stock_quantity}
                                                onChange={(e) => updateVariantField(idx, 'stock_quantity', parseInt(e.target.value) || 0)}
                                                className="w-full px-3 py-1.5 bg-white border border-[#E6DFD5] rounded text-xs font-bold text-stone-900"
                                            />
                                        </div>
                                        <div>
                                            <label className="block text-[9px] uppercase text-stone-500 font-bold mb-1">Price Override (Birr)</label>
                                            <input
                                                type="number"
                                                step="0.01"
                                                placeholder="Default"
                                                value={v.price_override}
                                                onChange={(e) => updateVariantField(idx, 'price_override', e.target.value)}
                                                className="w-full px-3 py-1.5 bg-white border border-[#E6DFD5] rounded text-xs font-bold text-stone-900"
                                            />
                                        </div>
                                    </div>
                                ))}
                            </div>
                        </div>
                    )}
                </div>

                <div className="flex items-center justify-end space-x-4 pt-4">
                    <a
                        href="/admin/products"
                        className="px-6 py-3.5 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold text-xs uppercase tracking-wider rounded-full transition-all"
                    >
                        Cancel
                    </a>
                    <button
                        type="submit"
                        disabled={processing}
                        className="px-8 py-3.5 bg-[#8C6554] hover:bg-[#755243] text-white font-bold text-xs uppercase tracking-widest rounded-full shadow-md transition-all disabled:opacity-50"
                    >
                        {processing ? 'Saving Changes...' : 'Save Product & Variant Matrix'}
                    </button>
                </div>
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
