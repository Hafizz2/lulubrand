@extends('storefront.layouts.app')
@section('title', $outfit->name . ' — LULU Couture')
@section('meta_description', Str::limit(strip_tags($outfit->description ?? 'Discover ' . $outfit->name . ' at LULU Couture.'), 150))
@section('og_type', 'product')
@section('og_image', $outfit->image_url)

@section('content')
<div x-data="outfitDetail()" class="pb-20 bg-[#FAF6F6]">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-10 flex flex-col lg:flex-row lg:gap-12 relative">
        
        <!-- Left: Outfit Large Hero Image -->
        <div class="w-full lg:w-7/12">
            <div class="aspect-[3/4] relative overflow-hidden bg-[#F9F9F9] border border-[#E6DFD5] rounded-xs">
                <img src="{{ $outfit->image_url }}" 
                     alt="{{ $outfit->name }}" 
                     class="w-full h-full object-cover">
                <div class="absolute bottom-4 left-4 bg-black/60 backdrop-blur-md px-3.5 py-1.5 rounded-full text-[10px] uppercase font-bold tracking-widest text-white shadow-sm pointer-events-none">
                    Curated Outfit Look
                </div>
            </div>
        </div>

        <!-- Right: Outfit Information & Component Selectors -->
        <div class="w-full lg:w-5/12 px-4 sm:px-6 lg:px-0 mt-6 lg:mt-0">
            <div class="lg:sticky lg:top-24 flex flex-col space-y-6">
                
                <!-- Outfit Title & Description -->
                <div>
                    <div class="text-[11px] uppercase tracking-[0.15em] font-semibold text-[#82203E] mb-1.5 flex items-center">
                        <svg class="w-3.5 h-3.5 mr-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01" />
                        </svg>
                        FULL OUTFIT LOOK
                    </div>
                    <h1 class="text-[26px] sm:text-[32px] font-serif font-normal leading-[1.25] text-[#1A1A1A]">
                        {{ $outfit->name }}
                    </h1>
                    @if($outfit->description)
                        <p class="text-[12px] text-stone-600 mt-3 font-sans leading-relaxed">
                            {!! nl2br(e($outfit->description)) !!}
                        </p>
                    @endif
                    <p class="text-[11px] text-stone-500 mt-2 font-sans italic">
                        This curated look contains multiple separate pieces. Select and configure individual items to buy them together or separately.
                    </p>
                </div>

                <!-- Bundled Items List -->
                @if($bundleProducts->count() > 0)
                    <div class="space-y-4 pt-2">
                        <h3 class="text-[11px] uppercase tracking-[0.08em] font-bold text-[#1A1A1A] border-b border-[#EEEEEE] pb-2">LOOK COMPONENTS</h3>
                        <div class="space-y-4 max-h-[400px] overflow-y-auto pr-1">
                            @foreach($bundleProducts as $index => $bp)
                                <div class="flex items-start gap-3 p-3 bg-white border border-[#EBE3E3] rounded-xs shadow-xs transition-opacity duration-300"
                                     :class="!bundleItems[{{ $index }}].checked && 'opacity-60 transition-opacity'">
                                    
                                    <!-- Checkbox -->
                                    <div class="pt-1">
                                        <input type="checkbox" 
                                               x-model="bundleItems[{{ $index }}].checked"
                                               class="w-4 h-4 rounded-xs accent-[#82203E] cursor-pointer">
                                    </div>

                                    <!-- Thumbnail -->
                                    <a href="/product/{{ $bp->product_code }}" target="_blank" class="w-14 h-20 bg-stone-100 border border-[#EEEEEE] overflow-hidden flex-shrink-0">
                                        <img src="{{ $bp->primaryImage ? $bp->primaryImage->url : ($bp->images->first() ? $bp->images->first()->url : '') }}" 
                                             alt="{{ $bp->title }}" 
                                             class="w-full h-full object-cover">
                                    </a>

                                    <!-- Item Details & Selectors -->
                                    <div class="flex-1 min-w-0 space-y-2">
                                        <div>
                                            <a href="/product/{{ $bp->product_code }}" target="_blank" class="text-[12px] font-semibold text-stone-900 hover:text-[#82203E] truncate block">
                                                {{ $bp->title }}
                                            </a>
                                            <div class="text-[11px] font-bold text-[#82203E] mt-0.5">
                                                <span x-text="bundleItems[{{ $index }}].price"></span> Birr
                                            </div>
                                        </div>

                                        <!-- Size & Colour Selectors -->
                                        <div class="flex flex-wrap gap-2 text-xs">
                                            <!-- Size Selector -->
                                            <template x-if="bundleItems[{{ $index }}].sizes.length > 0">
                                                <div>
                                                    <select x-model="bundleItems[{{ $index }}].selectedSize"
                                                            @change="updateBundleVariant({{ $index }})"
                                                            class="px-2.5 py-1 bg-[#FAF6F6] border border-[#D1D1D1] text-[10px] rounded-xs font-sans font-medium outline-none focus:border-[#82203E] cursor-pointer">
                                                        <template x-for="sz in bundleItems[{{ $index }}].sizes">
                                                            <option :value="sz" x-text="'Size: ' + sz"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </template>

                                            <!-- Colour Selector -->
                                            <template x-if="bundleItems[{{ $index }}].colours.length > 0">
                                                <div>
                                                    <select x-model="bundleItems[{{ $index }}].selectedColour"
                                                            @change="updateBundleVariant({{ $index }})"
                                                            class="px-2.5 py-1 bg-[#FAF6F6] border border-[#D1D1D1] text-[10px] rounded-xs font-sans font-medium outline-none focus:border-[#82203E] cursor-pointer">
                                                        <template x-for="cl in bundleItems[{{ $index }}].colours">
                                                            <option :value="cl.value" x-text="'Color: ' + cl.value"></option>
                                                        </template>
                                                    </select>
                                                </div>
                                            </template>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Add Bundle to Bag -->
                    <div class="pt-4 border-t border-[#EEEEEE] space-y-3">
                        <div class="flex items-center justify-between text-xs font-bold">
                            <span class="text-stone-600 uppercase tracking-wider">Look Total:</span>
                            <span class="text-base font-extrabold text-[#82203E]" x-text="bundleTotal + ' Birr'"></span>
                        </div>

                        <button @click="addBundleToBag()" 
                                :disabled="isAdding" 
                                class="w-full h-[50px] bg-[#82203E] hover:bg-black text-white text-[12px] font-bold uppercase tracking-[0.15em] rounded-none transition-colors disabled:opacity-50 shadow-md flex items-center justify-center space-x-2 cursor-pointer">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                            </svg>
                            <span x-text="isAdding ? 'ADDING ITEMS...' : 'ADD SELECTED TO BAG'">ADD SELECTED TO BAG</span>
                        </button>
                    </div>
                @else
                    <div class="p-6 bg-stone-50 border border-stone-200 text-center rounded-xs">
                        <p class="text-xs text-stone-500 uppercase tracking-widest">No components added to this outfit yet.</p>
                    </div>
                @endif

            </div>
        </div>
    </div>
</div>

<script>
function outfitDetail() {
    return {
        bundleItems: [
            @foreach($bundleProducts as $index => $bp)
            {
                id: {{ $bp->id }},
                title: '{{ addslashes($bp->title) }}',
                checked: true,
                selectedSize: '{{ $bp->variants->first() ? ($bp->variants->first()->attributeValues->firstWhere('attribute.slug', 'size')->value ?? '') : '' }}',
                selectedColour: '{{ $bp->variants->first() ? ($bp->variants->first()->attributeValues->firstWhere('attribute.slug', 'colour')->value ?? '') : '' }}',
                activeVariantId: {{ $bp->variants->first() ? $bp->variants->first()->id : 'null' }},
                variants: {!! json_encode($bp->variants->map(function($v) use ($bp) { return ['id' => $v->id, 'price' => number_format(($v->price_override ?? $bp->base_price) / 100, 2, '.', ''), 'stock' => $v->stock_quantity, 'attrs' => $v->attributeValues->pluck('value')->toArray()]; })) !!},
                sizes: {!! json_encode($bp->variants->flatMap(function($v) { return $v->attributeValues->filter(function($av) { return $av->attribute->slug === 'size'; })->pluck('value'); })->unique()->values()->toArray()) !!},
                colours: {!! json_encode($bp->variants->flatMap(function($v) { return $v->attributeValues->filter(function($av) { return $av->attribute->slug === 'colour'; })->map(function($av) { return ['value' => $av->value, 'color_code' => $av->color_code]; }); })->unique('value')->values()->toArray()) !!},
                basePrice: {{ number_format($bp->base_price / 100, 2, '.', '') }},
                price: '{{ number_format(($bp->variants->first() && $bp->variants->first()->price_override ? $bp->variants->first()->price_override : $bp->base_price) / 100, 2, '.', '') }}'
            },
            @endforeach
        ],
        isAdding: false,

        init() {
            this.bundleItems.forEach((item, index) => {
                this.updateBundleVariant(index);
            });
        },

        updateBundleVariant(index) {
            const item = this.bundleItems[index];
            const matched = item.variants.find(v => {
                const hasSize = !item.selectedSize || v.attrs.includes(item.selectedSize);
                const hasColour = !item.selectedColour || v.attrs.includes(item.selectedColour);
                return hasSize && hasColour;
            });
            if (matched) {
                item.activeVariantId = matched.id;
                item.price = parseFloat(matched.price).toFixed(2);
            } else {
                item.activeVariantId = null;
                item.price = '0.00';
            }
        },

        get bundleTotal() {
            let total = 0;
            this.bundleItems.forEach(item => {
                if (item.checked && item.activeVariantId) {
                    total += parseFloat(item.price);
                }
            });
            return total.toFixed(2);
        },

        addBundleToBag() {
            const selected = this.bundleItems.filter(item => item.checked);
            if (selected.length === 0) {
                alert('Please select at least one item to add to bag.');
                return;
            }
            
            const missingVariant = selected.find(item => !item.activeVariantId);
            if (missingVariant) {
                alert(`Please select size/colour for ${missingVariant.title}`);
                return;
            }

            this.isAdding = true;
            
            const promises = selected.map(item => {
                return fetch('/cart/add', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: JSON.stringify({
                        product_variant_id: item.activeVariantId,
                        quantity: 1
                    })
                }).then(res => res.json());
            });

            Promise.all(promises)
                .then(results => {
                    this.isAdding = false;
                    const failed = results.filter(r => !r.success);
                    if (failed.length > 0) {
                        alert('Some outfit items could not be added. Please verify availability.');
                    } else {
                        window.dispatchEvent(new CustomEvent('cart-updated', { 
                            detail: { message: 'Curated look items added to your bag!' } 
                        }));
                    }
                })
                .catch(err => {
                    this.isAdding = false;
                    console.error(err);
                    alert('There was a problem adding the items to your bag. Please try again.');
                });
        }
    };
}
</script>
@endsection
