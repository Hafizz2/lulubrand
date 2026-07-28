@extends('storefront.layouts.app')

@section('title', 'LULU — High-Fashion Women\'s Clothing & Editorial Couture')

@section('content')
<style>
.hero-focal-img {
    object-position: var(--focal-mobile, center top) !important;
}
@media (min-width: 768px) {
    .hero-focal-img {
        object-position: var(--focal-desktop, center center) !important;
    }
}
</style>

<div class="space-y-12 sm:space-y-20 pb-0 bg-[#FAF2F2]">
    @php
        $slidesData = isset($heroBanners) && $heroBanners->count() > 0 
            ? $heroBanners->map(fn($b) => [
                'image' => $b->image_url,
                'mobile_image' => $b->mobile_image_url,
                'desktop_focal' => $b->desktop_focal_position ?? 'center center',
                'mobile_focal' => $b->mobile_focal_position ?? 'center top',
                'title' => $b->title,
                'subtitle' => $b->subtitle ?? 'COUTURE COLLECTION',
                'button_text' => $b->button_text ?? 'SHOP COLLECTION',
                'button_url' => $b->button_url ?? '/categories'
            ])->values()->toArray()
            : [
                [
                    'image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=1800&q=80',
                    'mobile_image' => 'https://images.unsplash.com/photo-1515886657613-9f3515b0c78f?auto=format&fit=crop&w=800&q=80',
                    'desktop_focal' => 'center center',
                    'mobile_focal' => 'center top',
                    'title' => 'Elevate Your Style',
                    'subtitle' => 'High-Fashion Couture Collection',
                    'button_text' => 'SHOP COLLECTION',
                    'button_url' => '/categories'
                ]
            ];
    @endphp

    <!-- Auto-Scrolling DB Hero Carousel with Per-Device Focal Positioning -->
    <div x-data="{
        activeSlide: 0,
        slides: {{ json_encode($slidesData) }},
        isMobile: window.innerWidth < 768,
        timer: null,
        startTimer() {
            this.stopTimer();
            if (this.slides.length > 1) {
                this.timer = setInterval(() => {
                    this.nextSlide();
                }, 5000);
            }
        },
        stopTimer() {
            if (this.timer) clearInterval(this.timer);
        },
        nextSlide() {
            this.activeSlide = (this.activeSlide + 1) % this.slides.length;
        },
        prevSlide() {
            this.activeSlide = (this.activeSlide - 1 + this.slides.length) % this.slides.length;
        },
        init() {
            this.startTimer();
            window.addEventListener('resize', () => {
                this.isMobile = window.innerWidth < 768;
            });
        }
    }" 
    @mouseenter="stopTimer()" 
    @mouseleave="startTimer()"
    class="relative w-full h-[70vh] sm:h-[85vh] overflow-hidden bg-[#1A1A1A] group">
        
        <template x-for="(slide, index) in slides" :key="index">
            <div x-show="activeSlide === index"
                 x-transition:enter="transition ease-out duration-1000"
                 x-transition:enter-start="opacity-0 scale-105"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-700"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0"
                 class="absolute inset-0 z-0 flex items-center justify-center">
                
                <!-- Responsive Device HTML5 Picture Banner & Native CSS Focal Positioning -->
                <picture class="absolute inset-0 w-full h-full block">
                    <source media="(max-width: 767px)" :srcset="slide.mobile_image || slide.image">
                    <img :src="slide.image" 
                         :alt="slide.title" 
                         :style="'--focal-mobile: ' + (slide.mobile_focal || 'center top') + '; --focal-desktop: ' + (slide.desktop_focal || 'center center')"
                         class="w-full h-full object-cover hero-focal-img">
                </picture>
                <div class="absolute inset-0 bg-gradient-to-t from-[#1A1A1A]/85 via-[#1A1A1A]/30 to-transparent"></div>
                
                <div class="relative z-10 flex flex-col items-center text-center px-4 w-full max-w-4xl mx-auto mt-auto mb-16 sm:mb-28">
                    <span class="text-[10px] sm:text-[11px] uppercase tracking-[0.3em] font-semibold text-[#F6DADF] mb-3 px-3.5 py-1 bg-[#1A1A1A]/40 backdrop-blur-xs rounded-full" x-text="slide.subtitle"></span>
                    <h1 class="text-[30px] sm:text-[48px] md:text-[56px] font-serif font-normal uppercase tracking-wider text-white mb-6 leading-tight drop-shadow-md" x-text="slide.title"></h1>
                    <a :href="slide.button_url || '/categories'" class="bg-white text-[#1A1A1A] hover:bg-[#82203E] hover:text-white text-[11px] font-semibold uppercase tracking-[0.2em] px-10 py-4 rounded-none transition-all shadow-lg" x-text="slide.button_text || 'SHOP COLLECTION'"></a>
                </div>
            </div>
        </template>

        <!-- Carousel Arrow Controls -->
        <template x-if="slides.length > 1">
            <div class="absolute inset-y-0 inset-x-4 flex justify-between items-center pointer-events-none z-20">
                <button type="button" @click="prevSlide(); startTimer();" class="pointer-events-auto w-10 h-10 rounded-full bg-white/30 hover:bg-white text-[#1A1A1A] backdrop-blur-md shadow-md flex items-center justify-center transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
                </button>
                <button type="button" @click="nextSlide(); startTimer();" class="pointer-events-auto w-10 h-10 rounded-full bg-white/30 hover:bg-white text-[#1A1A1A] backdrop-blur-md shadow-md flex items-center justify-center transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg>
                </button>
            </div>
        </template>

        <!-- Carousel Indicators -->
        <template x-if="slides.length > 1">
            <div class="absolute bottom-6 left-0 right-0 flex justify-center space-x-2 z-20">
                <template x-for="(slide, index) in slides" :key="'indicator-'+index">
                    <button type="button" 
                            @click="activeSlide = index; startTimer();" 
                            class="w-10 sm:w-12 h-[2px] transition-colors"
                            :class="activeSlide === index ? 'bg-white' : 'bg-white/30'"></button>
                </template>
            </div>
        </template>
    </div>

    <!-- New Arrivals -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-6">
            <div class="flex items-end justify-between border-b border-[#EEEEEE] pb-4">
                <h2 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A]">New Arrivals</h2>
                <a href="/catalog?sort=newest" class="text-[10px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A] underline underline-offset-4 hover:text-[#82203E] transition-colors">
                    VIEW ALL &rarr;
                </a>
            </div>

            @if(isset($newArrivals) && $newArrivals->count() > 0)
                @include('storefront.partials.product_grid', ['products' => $newArrivals])
            @else
                <div class="text-center py-12 text-[#666666] text-[12px] uppercase tracking-[0.1em] font-semibold">
                    New arrivals dropping soon.
                </div>
            @endif
        </div>
    </div>

    <!-- Shop By Category -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="space-y-6">
            <div class="flex items-end justify-between border-b border-[#EEEEEE] pb-4">
                <h2 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A]">Shop by Category</h2>
                <a href="/categories" class="text-[10px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A] underline underline-offset-4 hover:text-[#82203E] transition-colors">
                    VIEW ALL &rarr;
                </a>
            </div>

            <div class="-mx-4 px-4 sm:mx-0 sm:px-0 flex items-start gap-4 sm:gap-6 overflow-x-auto no-scrollbar py-2 sm:justify-center snap-x snap-mandatory" style="scrollbar-width: none; -ms-overflow-style: none;">
                @if(isset($featuredCategories) && $featuredCategories->count() > 0)
                    @foreach($featuredCategories as $cat)
                        <a href="{{ route('catalog.category', $cat->slug) }}" class="flex flex-col items-center flex-shrink-0 group snap-start w-[72px] sm:w-[96px]">
                            <div class="w-[72px] h-[72px] sm:w-[96px] sm:h-[96px] rounded-full overflow-hidden bg-[#EEEEEE] transition-transform duration-300 group-hover:scale-105">
                                <img src="{{ $cat->image_url }}" alt="{{ $cat->name }}" class="w-full h-full object-cover">
                            </div>
                            <span class="text-[10px] font-semibold uppercase tracking-[0.05em] text-[#1A1A1A] mt-2 text-center group-hover:text-[#82203E] transition-colors leading-tight line-clamp-2">
                                {{ $cat->name }}
                            </span>
                        </a>
                    @endforeach
                @else
                    <div class="text-center py-12 text-[#666666] text-[12px] uppercase tracking-[0.1em] font-semibold w-full">
                        Categories updating.
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Promo Banner -->
    <div class="w-full bg-[#1A1A1A] text-white py-16 px-4 flex flex-col items-center justify-center text-center space-y-4">
        <span class="text-[10px] uppercase tracking-[0.3em] text-[#F6DADF]">Limited Time</span>
        <h3 class="text-[28px] sm:text-[36px] font-serif font-normal uppercase text-white leading-tight">Spring Collection — 30% OFF</h3>
        <p class="text-[11px] tracking-[0.15em] text-[#999999] uppercase mt-2">Use code <strong class="font-bold text-white">SPRING30</strong> at checkout.</p>
        <a href="/catalog?sort=newest" class="mt-6 bg-white text-[#1A1A1A] hover:bg-[#82203E] hover:text-white text-[10px] font-semibold uppercase tracking-[0.2em] px-10 py-3.5 rounded-none transition-colors inline-block">
            SHOP PROMO
        </a>
    </div>
</div>
@endsection
