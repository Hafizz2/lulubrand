@extends('storefront.layouts.app')

@section('title', 'All Collections — LULU Couture')

@section('content')
<div x-data="{
    searchQuery: '',
    matchCategory(name, desc) {
        if (!this.searchQuery.trim()) return true;
        const q = this.searchQuery.toLowerCase();
        return name.toLowerCase().includes(q) || (desc && desc.toLowerCase().includes(q));
    }
}" class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-6 sm:py-12 space-y-6 sm:space-y-10">

    <!-- Page Header -->
    <div class="text-center max-w-xl mx-auto space-y-3">
        <h1 class="text-[24px] sm:text-[32px] font-serif font-normal text-[#1A1A1A]">
            Collections
        </h1>
        <p class="text-[11px] text-[#666666] uppercase tracking-[0.1em] font-medium">
            {{ count($categories) }} COLLECTIONS
        </p>

        <!-- Search Bar -->
        <div class="relative max-w-md mx-auto pt-2">
            <input type="text" 
                   x-model="searchQuery" 
                   placeholder="Search collections..." 
                   class="w-full px-5 py-3 pl-11 bg-white border border-[#EEEEEE] text-[12px] text-[#1A1A1A] placeholder-[#999999] focus:outline-none focus:border-[#1A1A1A] transition-colors">
            <svg class="w-4 h-4 text-[#999999] absolute left-4 top-1/2 transform -translate-y-1/2 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z" />
            </svg>
            <button x-show="searchQuery" @click="searchQuery = ''" class="absolute right-4 top-1/2 transform -translate-y-1/2 mt-1 text-[#999999] hover:text-[#1A1A1A] text-xs">&times;</button>
        </div>
    </div>

    <!-- Category Horizontal Tabs -->
    <div class="flex items-center space-x-6 overflow-x-auto no-scrollbar py-3 border-b border-[#EEEEEE] -mx-4 px-4 sm:mx-0 sm:px-0">
        <button @click="searchQuery = ''" 
                :class="!searchQuery ? 'border-b-[1px] border-[#1A1A1A] text-[#1A1A1A]' : 'text-[#666666] hover:text-[#1A1A1A] border-b-[1px] border-transparent'"
                class="text-[10px] font-semibold uppercase tracking-[0.1em] flex-shrink-0 pb-1 transition-colors whitespace-nowrap">
            ALL ({{ count($categories) }})
        </button>
        @foreach($categories as $cat)
            <a href="{{ route('catalog.category', $cat->slug) }}" 
               class="text-[10px] font-semibold uppercase tracking-[0.1em] text-[#666666] hover:text-[#1A1A1A] border-b-[1px] border-transparent hover:border-[#1A1A1A] pb-1 flex-shrink-0 transition-colors whitespace-nowrap">
                {{ $cat->name }}
            </a>
        @endforeach
    </div>

    <!-- Category Grid (House of CB clean, no borders/shadows) -->
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-[2px] sm:gap-2 md:gap-4 lg:gap-6">
        @foreach($categories as $category)
            @php
                $count = $category->products_count ?? $category->products->count();
            @endphp
            <a href="{{ route('catalog.category', $category->slug) }}" 
               x-show="matchCategory('{{ addslashes($category->name) }}', '{{ addslashes($category->description ?? '') }}')"
               x-transition
               class="group flex flex-col bg-transparent overflow-hidden">
                
                <!-- 2:3 Portrait Image -->
                <div class="aspect-[2/3] w-full bg-[#F9F9F9] relative overflow-hidden">
                    <img src="{{ $category->image_url }}" 
                         alt="{{ $category->name }}" 
                         loading="lazy"
                         class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500 ease-out">
                    
                    <!-- Dark Gradient Overlay -->
                    <div class="absolute inset-0 bg-gradient-to-t from-[#000000]/70 via-[#000000]/10 to-transparent"></div>
                    
                    <!-- Title Overlaid at Bottom -->
                    <div class="absolute inset-x-0 bottom-0 p-3 sm:p-5 text-white">
                        <h3 class="text-[13px] sm:text-[16px] font-serif font-normal uppercase tracking-wider leading-tight line-clamp-2">
                            {{ $category->name }}
                        </h3>
                        @if($count > 0)
                            <span class="text-[9px] uppercase tracking-[0.1em] text-white/60 mt-1 block">
                                {{ $count }} {{ Str::plural('PIECE', $count) }}
                            </span>
                        @endif
                    </div>
                </div>
            </a>
        @endforeach
    </div>

    <!-- Empty State for Search -->
    <div x-show="searchQuery && !Array.from(document.querySelectorAll('[x-show*=\'matchCategory\']')).some(el => el.style.display !== 'none')" 
         class="text-center py-16 space-y-3">
        <p class="text-[11px] uppercase tracking-[0.1em] text-[#666666] font-medium">No collections match your search.</p>
        <button @click="searchQuery = ''" class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A] underline underline-offset-4">VIEW ALL COLLECTIONS</button>
    </div>

</div>
@endsection
