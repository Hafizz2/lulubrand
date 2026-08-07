@props(['product'])
@php
    $primaryImgModel = $product->primaryImage ?? $product->images->first();
    $primaryImage = $primaryImgModel ? $primaryImgModel->url : '';
    $secondaryImgModel = $product->images->where('is_primary', false)->first();
    $secondaryImage = $secondaryImgModel ? $secondaryImgModel->url : $primaryImage;
    $titleParts = explode(' ', trim($product->title), 2);
    $modelName = strtoupper($titleParts[0]);
    $modelDesc = isset($titleParts[1]) ? strtolower($titleParts[1]) : '';
    $colourSwatches = [];
    foreach ($product->variants as $variant) {
        foreach ($variant->attributeValues as $val) {
            if ($val->attribute && $val->attribute->slug === 'colour') {
                if (!isset($colourSwatches[$val->value])) {
                    $colorImg = $product->images->firstWhere('color_value', $val->value);
                    $imgUrl = $colorImg ? $colorImg->url : ($variant->image ? $variant->image->url : $primaryImage);
                    $colourSwatches[$val->value] = [
                        'name' => $val->value,
                        'color_code' => $val->color_code ?? '#000000',
                        'image_url' => $imgUrl,
                        'price' => number_format(($variant->price_override ?? $product->base_price) / 100, 2),
                    ];
                }
            }
        }
    }
@endphp

@php $productUrl = route('catalog.show', $product->product_code ?: $product->slug); @endphp

<div x-data="{
        activeImage: '{{ asset(ltrim($primaryImage, '/')) }}',
        hoverImage: '{{ asset(ltrim($secondaryImage, '/')) }}',
        activePrice: '{{ number_format($product->base_price / 100, 2) }} Birr',
        isHovered: false
    }" 
    class="flex flex-col w-full bg-transparent">
    
    <div class="relative block aspect-[2/3] overflow-hidden bg-transparent">
        <a href="{{ $productUrl }}" 
           @mouseenter="isHovered = true" 
           @mouseleave="isHovered = false"
           class="block w-full h-full cursor-pointer">
            <!-- Primary / Active Image -->
            <img :src="isHovered ? hoverImage : activeImage" 
                 alt="{{ $product->title }}" 
                 loading="lazy"
                 class="object-cover w-full h-full transition-opacity duration-200 ease-in-out">
        </a>

        <!-- Badges -->
        <div class="absolute top-2 left-2 flex flex-col gap-1 z-10">
            @if($product->is_presale)
                <span class="bg-[#1A1A1A] text-white text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-none">PRE-SALE</span>
            @elseif($product->is_new)
                <span class="bg-[#82203E] text-white text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-none">NEW</span>
            @endif
            @if($product->variants->sum('stock_quantity') === 0 && !$product->is_presale)
                <span class="bg-[#C49A9A] text-white text-[9px] font-bold uppercase tracking-widest px-2 py-0.5 rounded-none">SOLD OUT</span>
            @endif
        </div>

        <!-- Wishlist Button -->
        <button type="button" 
                @click.stop.prevent="$store.wishlist.toggle({{ $product->id }})"
                class="absolute top-2 right-2 z-20 w-8 h-8 rounded-full bg-white/80 backdrop-blur-xs flex items-center justify-center text-[#1A1A1A] hover:bg-[#82203E] hover:text-white transition-all duration-300 shadow-xs cursor-pointer group/heart">
            <svg class="w-4.5 h-4.5 transition-transform duration-300 group-hover/heart:scale-110" 
                 :class="$store.wishlist.has({{ $product->id }}) ? 'fill-[#82203E] stroke-[#82203E]' : 'stroke-current fill-none'" 
                 viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
            </svg>
        </button>
    </div>

    <!-- Details Container -->
    <div class="flex flex-col px-0 lg:px-5 pb-5 pt-[12px] gap-[20px] lg:gap-[25px] bg-transparent">
        <div class="flex flex-col">
            <!-- Color Swatches -->
            @if(count($colourSwatches) > 0)
                <div class="flex flex-wrap min-h-[20px] gap-[6px]">
                    @foreach($colourSwatches as $swatch)
                        <button type="button" 
                                @click.prevent="activeImage = '{{ asset(ltrim($swatch['image_url'], '/')) }}'; activePrice = '{{ $swatch['price'] }} Birr'"
                                @mouseenter="activeImage = '{{ asset(ltrim($swatch['image_url'], '/')) }}'"
                                title="{{ $swatch['name'] }}"
                                class="w-[10px] h-[10px] rounded-full border-[1px] border-[#82203E]"
                                style="background-color: {{ $swatch['color_code'] }}">
                        </button>
                    @endforeach
                </div>
            @endif

            <!-- Title -->
            <a href="{{ $productUrl }}" class="flex flex-col">
                <span class="font-serif text-[13px] leading-[18px] tracking-[0.52px] uppercase font-semibold text-[#82203E]">
                    {{ $modelName }}
                </span>
                @if($modelDesc)
                    <span class="font-serif italic text-[13px] md:text-[14px] leading-[17px] tracking-[0.22px] md:tracking-[0.56px] text-[#82203E]/90 capitalize line-clamp-2">
                        {{ $modelDesc }}
                    </span>
                @endif
            </a>
        </div>

        <!-- Price -->
        <div class="font-sans font-bold text-[10px] leading-[9px] tracking-[0.4px] text-[#82203E]">
            <span x-text="activePrice">{{ number_format($product->base_price / 100, 2) }} Birr</span>
        </div>
    </div>
</div>
