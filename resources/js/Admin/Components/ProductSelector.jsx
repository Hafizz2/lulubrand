import React, { useState, useMemo } from 'react';

export default function ProductSelector({
    products = [],
    selectedIds = [],
    onChange,
    label = "Select Products",
    placeholder = "Search products by title or code..."
}) {
    const [searchQuery, setSearchQuery] = useState('');

    // Filter products based on search query
    const filteredProducts = useMemo(() => {
        if (!searchQuery.trim()) return products;
        const query = searchQuery.toLowerCase();
        return products.filter(p => 
            p.title?.toLowerCase().includes(query) || 
            p.product_code?.toLowerCase().includes(query)
        );
    }, [products, searchQuery]);

    // Group selected products to display them at the top or separately
    const selectedProductsMap = useMemo(() => {
        const map = new Map();
        products.forEach(p => {
            if (selectedIds.includes(p.id)) {
                map.set(p.id, p);
            }
        });
        return map;
    }, [products, selectedIds]);

    const handleToggle = (productId) => {
        let nextSelected;
        if (selectedIds.includes(productId)) {
            nextSelected = selectedIds.filter(id => id !== productId);
        } else {
            nextSelected = [...selectedIds, productId];
        }
        onChange(nextSelected);
    };

    const handleRemove = (productId) => {
        onChange(selectedIds.filter(id => id !== productId));
    };

    return (
        <div className="space-y-3">
            <label className="block text-xs font-semibold uppercase text-stone-600 tracking-wider">
                {label} ({selectedIds.length} Selected)
            </label>

            {/* Selected Products List */}
            {selectedIds.length > 0 && (
                <div className="flex flex-wrap gap-2 p-3 bg-[#FAF8F5] border border-[#E6DFD5] rounded-xl max-h-44 overflow-y-auto">
                    {selectedIds.map(id => {
                        const product = selectedProductsMap.get(id);
                        if (!product) return null;
                        return (
                            <div 
                                key={id} 
                                className="flex items-center space-x-2 bg-white border border-[#E6DFD5] pl-2 pr-3 py-1.5 rounded-lg shadow-xs hover:border-[#8C6554] transition-all"
                            >
                                <img 
                                    src={product.image_url || 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=800&q=80'} 
                                    alt={product.title} 
                                    className="w-6 h-8 object-cover rounded bg-stone-100 flex-shrink-0"
                                />
                                <div className="text-left min-w-0">
                                    <p className="text-[11px] font-bold text-stone-900 truncate max-w-[120px]">{product.title}</p>
                                    <p className="text-[9px] font-mono text-stone-400">{product.product_code || `ID: #${product.id}`}</p>
                                </div>
                                <button
                                    type="button"
                                    onClick={() => handleRemove(id)}
                                    className="text-stone-400 hover:text-rose-600 transition-colors p-0.5"
                                >
                                    <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2.5" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        );
                    })}
                </div>
            )}

            {/* Search Input */}
            <div className="relative">
                <input
                    type="text"
                    value={searchQuery}
                    onChange={(e) => setSearchQuery(e.target.value)}
                    placeholder={placeholder}
                    className="w-full pl-10 pr-4 py-2.5 bg-[#F9F6F0] border border-[#E6DFD5] rounded-xl text-xs text-stone-900 focus:outline-none focus:border-[#8C6554]"
                />
                <span className="absolute left-3.5 top-1/2 -translate-y-1/2 text-stone-400">
                    <svg className="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </span>
                {searchQuery && (
                    <button
                        type="button"
                        onClick={() => setSearchQuery('')}
                        className="absolute right-3.5 top-1/2 -translate-y-1/2 text-stone-400 hover:text-stone-600"
                    >
                        Clear
                    </button>
                )}
            </div>

            {/* Scrollable Search Results List */}
            <div className="border border-[#E6DFD5] bg-white rounded-xl divide-y divide-[#E6DFD5] max-h-60 overflow-y-auto shadow-inner">
                {filteredProducts.length > 0 ? (
                    filteredProducts.map(product => {
                        const isSelected = selectedIds.includes(product.id);
                        return (
                            <div 
                                key={product.id}
                                onClick={() => handleToggle(product.id)}
                                className={`flex items-center justify-between px-4 py-2.5 cursor-pointer transition-colors ${
                                    isSelected ? 'bg-[#8C6554]/5 hover:bg-[#8C6554]/10' : 'hover:bg-stone-50'
                                }`}
                            >
                                <div className="flex items-center space-x-3 min-w-0">
                                    <img 
                                        src={product.image_url || 'https://images.unsplash.com/photo-1539571696357-5a69c17a67c6?auto=format&fit=crop&w=800&q=80'} 
                                        alt={product.title} 
                                        className="w-8 h-10 object-cover rounded bg-stone-100 border border-stone-200 flex-shrink-0"
                                    />
                                    <div className="text-left min-w-0">
                                        <p className="text-xs font-bold text-stone-800 truncate">{product.title}</p>
                                        <p className="text-[10px] font-mono text-stone-500 font-medium">
                                            {product.product_code ? `Code: ${product.product_code}` : `ID: #${product.id}`}
                                        </p>
                                    </div>
                                </div>
                                
                                <div className="flex-shrink-0 ml-4">
                                    {isSelected ? (
                                        <span className="w-5 h-5 bg-[#8C6554] text-white rounded-full flex items-center justify-center shadow-sm">
                                            <svg className="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24">
                                                <path strokeLinecap="round" strokeLinejoin="round" d="M5 13l4 4L19 7" />
                                            </svg>
                                        </span>
                                    ) : (
                                        <span className="w-5 h-5 rounded-full border-2 border-stone-300 hover:border-[#8C6554] transition-colors" />
                                    )}
                                </div>
                            </div>
                        );
                    })
                ) : (
                    <div className="p-4 text-center text-xs text-stone-400 font-medium">
                        No products match your search.
                    </div>
                )}
            </div>
        </div>
    );
}
