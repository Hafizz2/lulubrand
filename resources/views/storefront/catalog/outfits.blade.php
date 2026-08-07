@extends('storefront.layouts.app')

@section('title', 'Curated Outfit Looks — LULU Couture')
@section('meta_description', 'Discover full outfits and curated style looks compiled by the designers at LULU Couture. Shop coordinated pieces together.')

@section('content')
<div class="bg-[#FAF2F2] min-h-screen py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        
        <!-- Breadcrumb -->
        <nav class="flex text-xs uppercase tracking-widest text-[#666666] mb-8">
            <ol class="flex items-center space-x-2">
                <li><a href="{{ route('storefront.home') }}" class="hover:text-[#1A1A1A] transition-colors">Home</a></li>
                <li><span class="mx-2">/</span></li>
                <li class="text-[#1A1A1A]">Curated Looks</li>
            </ol>
        </nav>

        <!-- Page Header -->
        <div class="text-center max-w-2xl mx-auto mb-16 space-y-4">
            <span class="text-xs font-bold uppercase tracking-[0.3em] text-[#82203E] block">LULU Coordinates</span>
            <h1 class="text-4xl sm:text-5xl font-serif font-light text-[#1A1A1A]">Curated Looks</h1>
            <div class="w-12 h-px bg-[#82203E] mx-auto my-6"></div>
            <p class="text-sm text-[#666666] font-light leading-relaxed">
                Explore our signature coordinated sets, compiled to perfection. Purchase the entire coordinated look together or select only your favorite individual pieces.
            </p>
        </div>

        @if($outfits->isEmpty())
            <!-- Empty State -->
            <div class="bg-white border border-[#EEEEEE] p-16 text-center max-w-md mx-auto rounded-sm shadow-xs">
                <svg class="w-12 h-12 text-stone-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path>
                </svg>
                <h3 class="text-lg font-serif font-medium text-[#1A1A1A] mb-2">No looks curated yet</h3>
                <p class="text-xs text-[#666666] mb-8">We are compiling our signature coordinates. Check back soon!</p>
                <a href="{{ route('catalog.index') }}" class="inline-block bg-[#1A1A1A] text-white px-8 py-3 text-xs uppercase tracking-widest hover:bg-[#82203E] transition-colors">Shop All Products</a>
            </div>
        @else
            <!-- Outfits Grid -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-8 lg:gap-12">
                @foreach($outfits as $outfit)
                    <div class="group flex flex-col bg-white border border-[#EEEEEE] overflow-hidden hover:shadow-lg transition-all duration-300">
                        <!-- Outfit Image Aspect 3/4 -->
                        <a href="{{ route('catalog.outfit', $outfit->slug) }}" class="aspect-[3/4] relative overflow-hidden bg-[#F9F9F9] block">
                            <img src="{{ $outfit->image_url }}" 
                                 alt="{{ $outfit->name }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                            <div class="absolute bottom-4 left-4 bg-[#1A1A1A]/85 backdrop-blur-xs px-3 py-1.5 rounded-full text-[9px] uppercase font-bold tracking-widest text-white">
                                {{ count($outfit->product_ids ?? []) }} Items in Look
                            </div>
                        </a>

                        <!-- Outfit Details -->
                        <div class="p-6 sm:p-8 flex-1 flex flex-col justify-between space-y-4">
                            <div class="space-y-2">
                                <h3 class="text-lg font-serif font-medium text-[#1A1A1A] group-hover:text-[#82203E] transition-colors">
                                    <a href="{{ route('catalog.outfit', $outfit->slug) }}">{{ $outfit->name }}</a>
                                </h3>
                                @if($outfit->description)
                                    <p class="text-xs text-[#666666] line-clamp-3 font-light leading-relaxed">
                                        {{ strip_tags($outfit->description) }}
                                    </p>
                                @endif
                            </div>
                            
                            <div class="pt-4 border-t border-[#EEEEEE]">
                                <a href="{{ route('catalog.outfit', $outfit->slug) }}" 
                                   class="inline-flex items-center text-[10px] font-bold uppercase tracking-widest text-[#1A1A1A] group-hover:text-[#82203E] transition-colors">
                                    <span>Discover Curated Look</span>
                                    <svg class="w-3.5 h-3.5 ml-2 transform group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"></path>
                                    </svg>
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Pagination -->
            <div class="mt-16">
                {{ $outfits->links() }}
            </div>
        @endif

    </div>
</div>
@endsection
