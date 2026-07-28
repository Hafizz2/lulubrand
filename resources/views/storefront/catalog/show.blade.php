@extends('storefront.layouts.app')
@section('title', $product->title . ' — LULU Couture')
@section('meta_description', Str::limit(strip_tags($product->description ?? 'Discover ' . $product->title . ' at LULU Couture.'), 150))
@section('og_type', 'product')
@section('og_image', $product->primaryImage ? $product->primaryImage->url : ($product->images->first() ? $product->images->first()->url : asset('images/lulu-og-cover.jpg')))

@section('content')
<div x-data="productDetail()" class="pb-20">
    <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-6 lg:py-10 flex flex-col lg:flex-row lg:gap-12 relative">
        
        <!-- Left: Image Gallery (Mobile Carousel + Desktop Magnifier Gallery) -->
        <div class="w-full lg:w-7/12">
            
            <!-- Mobile Touch-Swipe Carousel with Chevrons & Next Hint -->
            <div class="block lg:hidden relative w-full overflow-hidden bg-[#F9F9F9]">
                <div x-ref="carousel" 
                     class="flex overflow-x-auto snap-x snap-mandatory no-scrollbar scroll-smooth" 
                     @scroll.debounce.20ms="activeSlide = Math.round($event.target.scrollLeft / $event.target.clientWidth)">
                    @foreach($product->images as $index => $img)
                        <div class="w-full flex-shrink-0 snap-center aspect-[3/4] relative">
                            <img src="{{ $img->url }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                        </div>
                    @endforeach
                </div>

                <!-- Left / Right Chevron Navigation Buttons -->
                <button type="button" 
                        @click="prevSlide()" 
                        x-show="activeSlide > 0"
                        class="absolute left-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/90 shadow-md flex items-center justify-center text-[#1A1A1A] hover:bg-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" 
                        @click="nextSlide()" 
                        x-show="activeSlide < totalSlides - 1"
                        class="absolute right-3 top-1/2 -translate-y-1/2 z-20 w-9 h-9 rounded-full bg-white/90 shadow-md flex items-center justify-center text-[#1A1A1A] hover:bg-white transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>

                <!-- Swipe Indicator Dots -->
                <div class="absolute bottom-4 left-0 right-0 flex justify-center items-center gap-2 z-10">
                    @foreach($product->images as $index => $img)
                        <button type="button" 
                                @click="goToSlide({{ $index }})"
                                :class="activeSlide === {{ $index }} ? 'w-2.5 h-2.5 bg-[#82203E]' : 'w-2 h-2 bg-black/30'" 
                                class="rounded-full transition-all duration-300"></button>
                    @endforeach
                </div>
            </div>

            <!-- Desktop Interactive 2-Column Gallery with Magnifier Zoom Effect -->
            <div class="hidden lg:flex gap-4">
                <!-- Vertical Thumbnails Rail -->
                <div class="w-20 flex-shrink-0 flex flex-col gap-3 max-h-[700px] overflow-y-auto no-scrollbar">
                    @foreach($product->images as $img)
                        <button type="button" 
                                @click="selectedImage = '{{ $img->url }}'"
                                :class="selectedImage === '{{ $img->url }}' ? 'border-[#82203E] opacity-100 ring-1 ring-[#82203E]' : 'border-[#EEEEEE] opacity-70 hover:opacity-100'"
                                class="w-full aspect-[3/4] border overflow-hidden bg-[#F9F9F9] transition-all">
                            <img src="{{ $img->url }}" alt="{{ $product->title }}" class="w-full h-full object-cover">
                        </button>
                    @endforeach
                </div>

                <!-- Active Large Image Viewer with Hover Magnifier Lens -->
                <div class="flex-1 aspect-[3/4] relative overflow-hidden bg-[#F9F9F9] cursor-crosshair border border-[#EEEEEE]"
                     @mouseenter="zoomActive = true"
                     @mouseleave="zoomActive = false"
                     @mousemove="handleZoom($event)">
                    
                    <img :src="selectedImage" 
                         alt="{{ $product->title }}" 
                         class="w-full h-full object-cover transition-transform duration-150 ease-out"
                         :style="zoomActive ? `transform: scale(2.2); transform-origin: ${zoomX}% ${zoomY}%;` : 'transform: scale(1);'">
                    
                    <div x-show="!zoomActive" class="absolute bottom-4 right-4 bg-white/90 backdrop-blur-md px-3 py-1.5 rounded-full text-[10px] uppercase font-bold tracking-widest text-[#1A1A1A] pointer-events-none shadow-xs">
                        Hover to Zoom
                    </div>
                </div>
            </div>

        </div>

        <!-- Right: Product Information & Selectors -->
        <div class="w-full lg:w-5/12 px-4 sm:px-6 lg:px-0 mt-6 lg:mt-0">
            <div class="lg:sticky lg:top-24 flex flex-col space-y-6">
                
                <!-- Title & Price -->
                <div>
                    <div class="text-[11px] uppercase tracking-[0.15em] font-semibold text-[#666666] mb-1.5">
                        {{ $product->category ? $product->category->name : 'COUTURE' }}
                    </div>
                    <h1 class="text-[20px] sm:text-[24px] font-serif font-normal leading-[1.25] text-[#1A1A1A]">
                        {{ $product->title }}
                    </h1>
                    <div class="text-[16px] sm:text-[18px] font-semibold text-[#1A1A1A] mt-2 flex items-center space-x-3">
                        <span x-text="activePrice">{{ number_format($product->base_price / 100, 2) }} Birr</span>
                        <span x-show="activeStock > 0 && activeStock <= 5" class="bg-[#F6DADF] text-[#82203E] text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full" x-text="'Only ' + activeStock + ' Left!'"></span>
                        <span x-show="activeStock === 0" class="bg-[#1A1A1A] text-white text-[10px] font-bold uppercase tracking-wider px-2.5 py-0.5 rounded-full">Out of Stock</span>
                    </div>
                </div>

                <!-- Color Swatches (Synchronized with images) -->
                @if(isset($attributesData['Colour']))
                    <div class="space-y-3 pt-2">
                        <div class="text-[11px] uppercase tracking-[0.08em] font-semibold text-[#1A1A1A] flex items-center justify-between">
                            <span>COLOUR: <span x-text="selectedColour" class="font-normal text-[#666666]"></span></span>
                        </div>
                        <div class="flex flex-wrap gap-3">
                            @foreach($attributesData['Colour'] as $colorVal)
                                <button type="button" 
                                        @click="selectColour('{{ addslashes($colorVal['value']) }}')"
                                        :class="selectedColour.toLowerCase() === '{{ strtolower($colorVal['value']) }}' ? 'outline outline-2 outline-[#1A1A1A] outline-offset-2 scale-110' : 'hover:scale-105'"
                                        class="w-8 h-8 rounded-full border border-[#E5E5E5] transition-all shadow-xs"
                                        style="background-color: {{ $colorVal['color_code'] ?? '#000000' }}"
                                        title="{{ $colorVal['value'] }}">
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Size Selector -->
                @if(isset($attributesData['Size']))
                    <div class="space-y-3 pt-2">
                        <div class="flex items-center justify-between">
                            <div class="text-[11px] uppercase tracking-[0.08em] font-semibold text-[#1A1A1A]">SELECT SIZE</div>
                            <button type="button" @click="showSizeGuide = true" class="text-[11px] uppercase tracking-[0.08em] font-semibold text-[#82203E] underline underline-offset-4">SIZE GUIDE</button>
                        </div>
                        <div class="grid grid-cols-4 gap-2">
                            @foreach($attributesData['Size'] as $sizeVal)
                                <button type="button" 
                                        @click="selectedSize = '{{ $sizeVal['value'] }}'; updateVariant()"
                                        :class="selectedSize === '{{ $sizeVal['value'] }}' ? 'bg-[#1A1A1A] text-white border-[#1A1A1A]' : 'bg-white text-[#1A1A1A] border-[#E5E5E5] hover:border-[#1A1A1A]'"
                                        class="h-[44px] border text-[12px] font-medium flex items-center justify-center transition-colors">
                                    {{ $sizeVal['value'] }}
                                </button>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Add to Bag CTA -->
                <div class="pt-4">
                    <button @click="addToBag()" 
                            :disabled="activeStock === 0 || !activeVariantId || isAdding" 
                            class="w-full h-[50px] bg-[#1A1A1A] hover:bg-[#82203E] text-white text-[13px] font-semibold uppercase tracking-[0.12em] rounded-none transition-colors disabled:opacity-50 shadow-md">
                        <span x-text="isAdding ? 'ADDING...' : (activeStock > 0 ? (activeVariantId ? 'ADD TO BAG' : 'SELECT VARIANT') : 'OUT OF STOCK')">ADD TO BAG</span>
                    </button>
                </div>

                <!-- Collapsible Accordion Sections -->
                <div class="pt-6 space-y-0">
                    <!-- DESCRIPTION -->
                    <div class="border-t border-[#E5E5E5]">
                        <button type="button" @click="descOpen = !descOpen" class="w-full flex justify-between items-center py-4 cursor-pointer">
                            <span class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A]">DESCRIPTION</span>
                            <span class="text-[16px] text-[#1A1A1A] transition-transform duration-300" :class="descOpen ? 'rotate-45' : ''">+</span>
                        </button>
                        <div x-show="descOpen" x-collapse>
                            <div class="text-[13px] leading-[1.6] text-[#4A4A4A] pb-4">
                                {{ $product->description }}
                                @if($product->material)
                                    <div class="mt-2 font-semibold">Material: {{ $product->material }}</div>
                                @endif
                            </div>
                        </div>
                    </div>

                    <!-- SIZE & FIT -->
                    <div class="border-t border-[#E5E5E5]">
                        <button type="button" @click="sizeOpen = !sizeOpen" class="w-full flex justify-between items-center py-4 cursor-pointer">
                            <span class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A]">SIZE & FIT</span>
                            <span class="text-[16px] text-[#1A1A1A] transition-transform duration-300" :class="sizeOpen ? 'rotate-45' : ''">+</span>
                        </button>
                        <div x-show="sizeOpen" x-collapse>
                            <div class="text-[13px] leading-[1.6] text-[#4A4A4A] pb-4">
                                <ul class="list-disc pl-4 space-y-1">
                                    <li>Fits true to size, take your normal size.</li>
                                    <li>Designed for a tailored, slim fit.</li>
                                    <li>Model is wearing size S.</li>
                                </ul>
                            </div>
                        </div>
                    </div>

                    <!-- DELIVERY & RETURNS -->
                    <div class="border-t border-b border-[#E5E5E5]">
                        <button type="button" @click="deliveryOpen = !deliveryOpen" class="w-full flex justify-between items-center py-4 cursor-pointer">
                            <span class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A]">DELIVERY & RETURNS</span>
                            <span class="text-[16px] text-[#1A1A1A] transition-transform duration-300" :class="deliveryOpen ? 'rotate-45' : ''">+</span>
                        </button>
                        <div x-show="deliveryOpen" x-collapse>
                            <div class="text-[13px] leading-[1.6] text-[#4A4A4A] pb-4">
                                <ul class="list-disc pl-4 space-y-1">
                                    <li>Express worldwide delivery available.</li>
                                    <li>Addis Ababa local pickup or express courier.</li>
                                    <li>Easy exchanges within 14 days of delivery.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>

    <!-- Related Products Grid -->
    @if($relatedProducts->count() > 0)
        <div class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 mt-16 lg:mt-24">
            <h2 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A] mb-6 text-center lg:text-left">COMPLETE THE LOOK</h2>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-[2px] sm:gap-2 md:gap-4 lg:gap-6">
                @foreach($relatedProducts as $rel)
                    @include('storefront.partials.product_card', ['product' => $rel])
                @endforeach
            </div>
        </div>
    @endif

    <!-- Dynamic Size Guide Modal -->
    <div x-show="showSizeGuide" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showSizeGuide = false" class="bg-white max-w-2xl w-full p-6 sm:p-8 rounded-sm border border-[#E5E5E5] shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6 border-b border-[#EEEEEE] pb-4">
                <h3 class="text-[14px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A]">{{ $sizeGuideTitle ?? 'SIZE GUIDE' }}</h3>
                <button @click="showSizeGuide = false" class="text-[#1A1A1A] hover:text-[#82203E] font-medium text-xl">&times;</button>
            </div>

            @if(!empty($sizeGuideDescription))
                <div class="mb-6 border border-[#EEEEEE] p-4 bg-[#F9F9F9]">
                    <div class="text-[13px] text-[#4A4A4A] leading-[1.6] whitespace-pre-line">
                        {{ $sizeGuideDescription }}
                    </div>
                </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-[12px] text-left border-collapse">
                    <thead>
                        <tr class="bg-[#F9F9F9] text-[#1A1A1A] uppercase tracking-wider font-semibold">
                            <th class="p-3 border border-[#EEEEEE]">Size</th>
                            <th class="p-3 border border-[#EEEEEE]">Bust ({{ $sizeGuideUnit ?? 'in' }})</th>
                            <th class="p-3 border border-[#EEEEEE]">Waist ({{ $sizeGuideUnit ?? 'in' }})</th>
                            <th class="p-3 border border-[#EEEEEE]">Hips ({{ $sizeGuideUnit ?? 'in' }})</th>
                            @if(($sizeGuides ?? collect())->pluck('length')->filter()->count() > 0)
                                <th class="p-3 border border-[#EEEEEE]">Length ({{ $sizeGuideUnit ?? 'in' }})</th>
                            @endif
                        </tr>
                    </thead>
                    <tbody>
                        @foreach(($sizeGuides ?? []) as $size)
                            <tr class="bg-white">
                                <td class="p-3 border border-[#EEEEEE] font-medium text-[#1A1A1A]">{{ $size->name }}</td>
                                <td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">{{ $size->bust ?? '—' }}</td>
                                <td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">{{ $size->waist ?? '—' }}</td>
                                <td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">{{ $size->hips ?? '—' }}</td>
                                @if(($sizeGuides ?? collect())->pluck('length')->filter()->count() > 0)
                                    <td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">{{ $size->length ?? '—' }}</td>
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
function productDetail() {
    const variants = @json($variantsJson ?? []);
    const images = @json($imagesJson ?? []);

    return {
        selectedImage: '{{ $product->primaryImage ? $product->primaryImage->url : ($product->images->first() ? $product->images->first()->url : "") }}',
        selectedSize: '{{ isset($attributesData["Size"]) ? reset($attributesData["Size"])["value"] : "" }}',
        selectedColour: '{{ isset($attributesData["Colour"]) ? reset($attributesData["Colour"])["value"] : "" }}',
        activeVariantId: null,
        activePrice: '{{ number_format($product->base_price / 100, 2) }} Birr',
        activeStock: {{ $product->variants->sum('stock_quantity') }},
        activeSku: '{{ $product->variants->first() ? $product->variants->first()->sku : "SKU-MAIN" }}',
        quantity: 1,
        showSizeGuide: false,
        isAdding: false,
        activeSlide: 0,
        totalSlides: {{ $product->images->count() }},
        descOpen: false,
        sizeOpen: false,
        deliveryOpen: false,
        zoomActive: false,
        zoomX: 50,
        zoomY: 50,

        init() {
            this.updateVariant();
        },

        selectColour(colourName) {
            this.selectedColour = colourName;
            this.updateVariant();
        },

        handleZoom(e) {
            const rect = e.currentTarget.getBoundingClientRect();
            const x = ((e.clientX - rect.left) / rect.width) * 100;
            const y = ((e.clientY - rect.top) / rect.height) * 100;
            this.zoomX = Math.max(0, Math.min(100, x));
            this.zoomY = Math.max(0, Math.min(100, y));
        },

        prevSlide() {
            if (this.activeSlide > 0) {
                this.goToSlide(this.activeSlide - 1);
            }
        },

        nextSlide() {
            if (this.activeSlide < this.totalSlides - 1) {
                this.goToSlide(this.activeSlide + 1);
            }
        },

        goToSlide(index) {
            this.activeSlide = index;
            if (this.$refs.carousel) {
                const width = this.$refs.carousel.clientWidth;
                this.$refs.carousel.scrollTo({ left: width * index, behavior: 'smooth' });
            }
        },

        updateVariant() {
            const matched = variants.find(v => {
                const hasSize = !this.selectedSize || v.attrs.includes(this.selectedSize);
                const hasColour = !this.selectedColour || v.attrs.includes(this.selectedColour);
                return hasSize && hasColour;
            });

            // Match color photo dynamically
            const colorMatchImage = images.find(img => img.color_value && img.color_value.toLowerCase() === (this.selectedColour || '').toLowerCase());
            
            if (colorMatchImage) {
                this.selectedImage = colorMatchImage.url;
            } else if (matched && matched.image) {
                this.selectedImage = matched.image;
            }

            if (matched) {
                this.activeVariantId = matched.id;
                this.activePrice = matched.price + ' Birr';
                this.activeStock = matched.stock;
                this.activeSku = matched.sku;
            } else {
                this.activeVariantId = null;
                this.activeStock = 0;
            }
        },

        addToBag() {
            if (!this.activeVariantId) {
                alert('Please select your preferred size and colour.');
                return;
            }

            this.isAdding = true;

            fetch('/cart/add', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    product_variant_id: this.activeVariantId,
                    quantity: this.quantity
                })
            })
            .then(res => res.json())
            .then(data => {
                this.isAdding = false;
                if (data.success) {
                    window.dispatchEvent(new CustomEvent('cart-updated', { 
                        detail: { message: '{{ addslashes($product->title) }} added to bag!' } 
                    }));
                } else if (data.errors) {
                    alert(Object.values(data.errors).flat().join('\n'));
                }
            })
            .catch(err => {
                this.isAdding = false;
                console.error(err);
                alert('There was a problem adding the item to your bag.');
            });
        }
    }
}
</script>
@endsection
