@extends('storefront.layouts.app')
@section('title', 'My Wishlist — LULU Couture')

@section('content')
<div x-data="accountTabs()" @popstate.window="handlePopState($event)" class="bg-[#FAF2F2] min-h-screen py-12 relative" id="account-section-top">
    <!-- Loading spinner overlay -->
    <div x-show="loading" x-cloak class="absolute inset-0 bg-white/40 backdrop-blur-xs flex items-center justify-center z-50 transition-all duration-300">
        <div class="animate-spin rounded-full h-10 w-10 border-2 border-[#82203E] border-t-transparent"></div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Breadcrumb -->
        <nav class="flex text-xs uppercase tracking-widest text-[#666666] mb-12">
            <ol class="flex items-center space-x-2">
                <li><a href="{{ route('storefront.home') }}" class="hover:text-[#1A1A1A]">Home</a></li>
                <li><span class="mx-2">/</span></li>
                <li><a href="{{ route('account.profile') }}" data-account-tab="{{ route('account.profile') }}" @click.prevent="navigate($event, '{{ route('account.profile') }}')" class="hover:text-[#1A1A1A]">Account</a></li>
                <li><span class="mx-2">/</span></li>
                <li class="text-[#1A1A1A]">Wishlist</li>
            </ol>
        </nav>

        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
            <!-- Sidebar -->
            <aside class="lg:w-1/4 w-full">
                <h1 class="font-serif text-3xl lg:text-4xl text-[#1A1A1A] mb-6 lg:mb-8 text-center lg:text-left">My Account</h1>
                
                <!-- Horizontal scrollable nav on mobile, standard list on desktop -->
                <nav class="flex lg:flex-col gap-2 lg:gap-4 overflow-x-auto lg:overflow-x-visible pb-4 lg:pb-0 mb-8 lg:mb-0 border-b border-[#EEEEEE] lg:border-none scrollbar-none whitespace-nowrap lg:whitespace-normal px-4 lg:px-0">
                    <a href="{{ route('account.profile') }}" data-account-tab="{{ route('account.profile') }}" @click.prevent="navigate($event, '{{ route('account.profile') }}')" class="inline-block lg:block text-xs lg:text-sm uppercase tracking-widest px-4 py-2 lg:px-0 lg:py-0 border border-stone-200 lg:border-none rounded-full lg:rounded-none hover:bg-[#82203E]/5 lg:hover:bg-transparent text-[#666666] hover:text-[#1A1A1A] transition-all">
                        Profile Overview
                    </a>
                    <a href="{{ route('account.orders') }}" data-account-tab="{{ route('account.orders') }}" @click.prevent="navigate($event, '{{ route('account.orders') }}')" class="inline-block lg:block text-xs lg:text-sm uppercase tracking-widest px-4 py-2 lg:px-0 lg:py-0 border border-stone-200 lg:border-none rounded-full lg:rounded-none hover:bg-[#82203E]/5 lg:hover:bg-transparent text-[#666666] hover:text-[#1A1A1A] transition-all">
                        My Orders
                    </a>
                    <a href="{{ route('account.wishlist') }}" data-account-tab="{{ route('account.wishlist') }}" @click.prevent="navigate($event, '{{ route('account.wishlist') }}')" class="inline-block lg:block text-xs lg:text-sm uppercase tracking-widest font-semibold px-4 py-2 lg:px-0 lg:py-0 border border-[#1A1A1A] lg:border-none rounded-full lg:rounded-none bg-[#1A1A1A] lg:bg-transparent text-white lg:text-[#1A1A1A]">
                        Wishlist ({{ $wishlistItems->total() }})
                    </a>
                    <a href="{{ route('account.points') }}" data-account-tab="{{ route('account.points') }}" @click.prevent="navigate($event, '{{ route('account.points') }}')" class="inline-block lg:block text-xs lg:text-sm uppercase tracking-widest px-4 py-2 lg:px-0 lg:py-0 border border-stone-200 lg:border-none rounded-full lg:rounded-none hover:bg-[#82203E]/5 lg:hover:bg-transparent text-[#666666] hover:text-[#1A1A1A] transition-all">
                        LULU Points
                    </a>
                    
                    <form method="POST" action="{{ route('logout') }}" class="inline-block lg:block lg:mt-8 ml-auto lg:ml-0">
                        @csrf
                        <button type="submit" class="text-xs lg:text-sm uppercase tracking-widest text-[#82203E] hover:text-[#1A1A1A] transition-colors px-4 py-2 lg:px-0 lg:py-0 border border-stone-200 lg:border-none rounded-full lg:rounded-none cursor-pointer">
                            Sign Out
                        </button>
                    </form>
                </nav>
            </aside>

            <!-- Main Content Container with dynamic id for AJAX swap -->
            <main class="lg:w-3/4" id="account-main-content">
                <h2 class="text-3xl font-serif text-[#1A1A1A] mb-8">My Wishlist <span class="text-lg text-[#666666] ml-2 font-sans">({{ $wishlistItems->total() }} items)</span></h2>

                @if($wishlistItems->isEmpty())
                    <div class="bg-white p-6 sm:p-12 rounded-sm border border-[#EEEEEE] text-center">
                        <svg class="w-12 h-12 text-[#EEEEEE] mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"></path></svg>
                        <p class="text-lg font-serif text-[#1A1A1A] mb-2">Your wishlist is empty</p>
                        <p class="text-[#666666] mb-8">Save items you love to view them later.</p>
                        <a href="{{ route('catalog.index') }}" class="inline-block bg-[#1A1A1A] text-white px-8 py-3 text-xs uppercase tracking-widest hover:bg-[#82203E] transition-colors">Explore Our Collection</a>
                    </div>
                @else
                    <div class="grid grid-cols-2 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-8">
                        @foreach($wishlistItems as $item)
                            <div class="group relative flex flex-col">
                                <!-- Product Image -->
                                <div class="relative w-full aspect-[3/4] bg-stone-100 overflow-hidden mb-4">
                                    <a href="{{ route('catalog.show', $item->product->product_code) }}" class="block w-full h-full">
                                        @if($item->product->primaryImage)
                                            <img src="{{ $item->product->primaryImage->url }}" alt="{{ $item->product->title }}" class="w-full h-full object-cover transition-transform duration-700 group-hover:scale-105">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center text-stone-300">No Image</div>
                                        @endif
                                    </a>
                                    <!-- Remove Button -->
                                    <form action="{{ route('wishlist.toggle', $item->product) }}" method="POST" class="absolute top-4 right-4 z-10">
                                        @csrf
                                        <button type="submit" class="w-8 h-8 bg-white/90 rounded-full flex items-center justify-center text-[#1A1A1A] hover:bg-[#82203E] hover:text-white transition-colors shadow-sm" aria-label="Remove from wishlist">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                        </button>
                                    </form>
                                </div>
                                
                                <!-- Product Info -->
                                <div>
                                    @if($item->product->category)
                                        <div class="text-[10px] uppercase tracking-widest text-[#666666] mb-1">{{ $item->product->category->name }}</div>
                                    @endif
                                    <h3 class="text-sm font-medium text-[#1A1A1A] mb-1 line-clamp-1">
                                        <a href="{{ route('catalog.show', $item->product->product_code) }}" class="hover:text-[#82203E] transition-colors">{{ $item->product->title }}</a>
                                    </h3>
                                    <div class="text-sm text-[#1A1A1A]">{{ number_format($item->product->base_price / 100, 2) }} Birr</div>
                                </div>
                                
                                <!-- Add to Cart (Quick add) -->
                                <div class="mt-4">
                                    <a href="{{ route('catalog.show', $item->product->product_code) }}" class="block w-full border border-[#1A1A1A] text-center py-2 text-xs uppercase tracking-widest text-[#1A1A1A] hover:bg-[#1A1A1A] hover:text-white transition-colors">
                                        View Product
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-12">
                        {{ $wishlistItems->links() }}
                    </div>
                @endif
            </main>
        </div>
    </div>
</div>
@endsection
