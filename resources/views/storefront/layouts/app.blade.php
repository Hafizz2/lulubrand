<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-screen">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'LULU — High-Fashion Women\'s Couture')</title>
    <meta name="description" content="@yield('meta_description', 'Discover LULU\'s high-fashion women\'s clothing collection. Express shipping on dresses, top arrivals, and luxury editorial couture.')">
    <link rel="canonical" href="@yield('canonical_url', request()->url())">

    <!-- SEO Meta Tags -->
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:site_name" content="LULU Couture">
    <meta property="og:title" content="@yield('title', 'LULU — High-Fashion Women\'s Couture')">
    <meta property="og:description" content="@yield('meta_description', 'Discover LULU\'s luxury editorial couture.')">
    <meta property="og:url" content="@yield('canonical_url', request()->url())">
    <meta property="og:image" content="@yield('og_image', asset('logo.png'))">

    <!-- Favicon Links -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('logo.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('logo.png') }}">

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Cormorant+Garamond:ital,wght@0,400;0,500;0,600;0,700;1,400;1,600&family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">

    <style>
        [x-cloak] { display: none !important; }
        .font-serif { font-family: 'Cormorant Garamond', Georgia, serif; }
        .font-sans { font-family: 'Plus Jakarta Sans', system-ui, -apple-system, sans-serif; }
        
        :root {
            --wine: #82203E;
            --cotton: #FAF2F2;
            --pinkPale: #F6DADF;
            --charcoal: #1A1A1A;
            --border-light: #EEEEEE;
            --text-muted: #666666;
        }

        .no-scrollbar::-webkit-scrollbar { display: none; }
        .no-scrollbar { -ms-overflow-style: none; scrollbar-width: none; }
    </style>

    @vite(['resources/js/storefront.js'])
</head>
<body x-data="miniCart()" @cart-updated.window="fetchCart(false); showToast($event.detail?.message || 'Item added to bag!')" @show-toast.window="showToast($event.detail?.message, $event.detail?.isError)" class="min-h-screen bg-[#FAF2F2] text-[#1A1A1A] font-sans antialiased flex flex-col selection:bg-[#82203E] selection:text-white">

    <!-- Page Loader -->
    <div id="pageLoader" class="fixed inset-0 z-50 bg-[#FAF2F2] flex items-center justify-center transition-opacity duration-700 ease-out">
        <img src="{{ asset('logo.png') }}" alt="LULU Couture Loading" class="h-16 md:h-20 w-auto object-contain animate-pulse">
    </div>

    <!-- Announcement Bar -->
    <div class="h-[40px] bg-[#F6DADF] text-[#82203E] flex items-center justify-center font-serif text-[13px] sm:text-[14px] tracking-[0.28px] relative z-50">
        <p class="font-medium">✦ COMPLIMENTARY SHIPPING & EASY RETURNS ✦</p>
    </div>

    <!-- Solid Sticky Header with Navigation Links on Desktop -->
    <header class="sticky top-0 z-40 bg-[#FAF2F2] border-b border-[#EEEEEE] h-16 sm:h-20 w-full flex items-center font-sans shadow-xs">
        <div class="w-full px-4 md:px-8 flex items-center justify-between relative">
            
            <!-- Left: Mobile Menu / Desktop Nav Buttons -->
            <div class="flex-1 flex items-center">
                <button @click="navMenuOpen = true" class="lg:hidden text-[11px] uppercase tracking-[0.1em] font-semibold text-[#1A1A1A] hover:text-[#82203E] transition-colors flex items-center space-x-2">
                    <svg class="w-5 h-5 text-[#1A1A1A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <span>MENU</span>
                </button>
                <nav class="hidden lg:flex items-center space-x-6 text-[11px] font-semibold uppercase tracking-[0.15em] text-[#1A1A1A]">
                    <a href="/" class="hover:text-[#82203E] transition-colors">Home</a>
                    <a href="/categories" class="hover:text-[#82203E] transition-colors">Categories</a>
                    <a href="/catalog?sort=newest" class="hover:text-[#82203E] transition-colors">New Arrivals</a>
                    <a href="/catalog" class="hover:text-[#82203E] transition-colors">Shop All</a>
                </nav>
            </div>

            <!-- Dead-Center Logo -->
            <a href="/" class="flex-shrink-0 absolute left-1/2 transform -translate-x-1/2 flex items-center justify-center">
                <img src="{{ asset('logo.png') }}" alt="LULU Couture" class="h-10 sm:h-12 w-auto object-contain">
            </a>

            <!-- Right: Mobile Search & Bag / Desktop Actions -->
            <div class="flex-1 flex items-center justify-end">
                <!-- Mobile Right -->
                <div class="flex lg:hidden items-center space-x-4">
                    <button @click="searchOpen = true" class="text-[#1A1A1A] hover:text-[#82203E] transition-colors p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    <button @click="openDrawer = true" class="text-[#1A1A1A] hover:text-[#82203E] transition-colors flex items-center p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span class="ml-1 text-[10px] font-bold" x-text="cart.count">0</span>
                    </button>
                </div>
                
                <!-- Desktop Right Actions -->
                <div class="hidden lg:flex items-center space-x-6 text-[11px] uppercase tracking-[0.15em] font-semibold text-[#1A1A1A]">
                    <button @click="searchOpen = true" class="flex items-center hover:text-[#82203E] transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        SEARCH
                    </button>
                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="hover:text-[#82203E] transition-colors uppercase text-[11px] font-semibold tracking-[0.15em]">
                                LOGOUT
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-[#82203E] transition-colors">
                            SIGN IN
                        </a>
                    @endauth
                    <div class="h-4 w-px bg-[#EEEEEE]"></div>
                    <button @click="openDrawer = true" class="hover:text-[#82203E] transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        BAG (<span x-text="cart.count">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Search Modal Overlay & Drawer -->
    <div x-show="searchOpen" x-cloak class="fixed inset-0 z-50 overflow-hidden font-sans">
        <div @click="searchOpen = false" class="absolute inset-0 bg-[#000000]/50 backdrop-blur-sm transition-opacity"></div>
        <div class="relative z-10 bg-[#FAF2F2] border-b border-[#EEEEEE] shadow-2xl p-6 sm:p-10 max-w-4xl mx-auto mt-0 sm:mt-12 rounded-b-2xl transition-transform">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-[#EEEEEE]">
                <h3 class="text-[12px] font-semibold uppercase tracking-[0.15em] text-[#1A1A1A]">SEARCH LULU CATALOG</h3>
                <button @click="searchOpen = false" class="text-[#1A1A1A] hover:text-[#82203E] font-bold text-xl p-1">&times;</button>
            </div>
            
            <form @submit.prevent="performSearch()" class="flex space-x-3">
                <div class="relative flex-1">
                    <input type="text" 
                           x-model="searchQuery" 
                           @keyup.enter="performSearch()"
                           placeholder="Search dresses, tops, outerwear, collections..." 
                           class="w-full px-5 py-3.5 pl-12 bg-white border border-[#EEEEEE] text-[13px] text-[#1A1A1A] placeholder-[#999999] focus:outline-none focus:border-[#1A1A1A] transition-colors">
                    <svg class="w-5 h-5 text-[#999999] absolute left-4 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <button type="submit" class="bg-[#1A1A1A] hover:bg-[#82203E] text-white text-[11px] font-semibold uppercase tracking-[0.15em] px-8 py-3.5 transition-colors">
                    SEARCH
                </button>
            </form>

            <div class="mt-6 pt-4 border-t border-[#EEEEEE] flex flex-wrap items-center gap-3 text-[11px] text-[#666666]">
                <span class="font-semibold uppercase tracking-wider text-[#1A1A1A]">Popular Searches:</span>
                <a href="/catalog?q=dress" @click="searchOpen = false" class="hover:text-[#82203E] underline">Dresses</a>
                <a href="/catalog?q=corset" @click="searchOpen = false" class="hover:text-[#82203E] underline">Corsets</a>
                <a href="/catalog?q=satin" @click="searchOpen = false" class="hover:text-[#82203E] underline">Satin</a>
                <a href="/categories" @click="searchOpen = false" class="hover:text-[#82203E] underline">Collections</a>
            </div>
        </div>
    </div>

    <!-- Slide-Over Mobile Menu -->
    <div x-show="navMenuOpen" x-cloak class="fixed inset-0 z-50 overflow-hidden font-sans">
        <div @click="navMenuOpen = false" class="absolute inset-0 bg-[#000000]/50 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-y-0 left-0 w-[85%] max-w-sm bg-[#FAF2F2] shadow-2xl flex flex-col transition-transform transform">
            <div class="flex items-center justify-between px-6 py-5 border-b border-[#EEEEEE]">
                <span class="text-[11px] uppercase tracking-[0.15em] font-bold text-[#1A1A1A]">NAVIGATION</span>
                <button @click="navMenuOpen = false" class="text-[#1A1A1A] p-2 hover:opacity-70">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-0">
                <a href="/" @click="navMenuOpen = false" class="block py-4 text-[11px] uppercase tracking-wider font-semibold text-[#1A1A1A] border-b border-[#EEEEEE]">HOME</a>
                <a href="/categories" @click="navMenuOpen = false" class="block py-4 text-[11px] uppercase tracking-wider font-semibold text-[#1A1A1A] border-b border-[#EEEEEE]">CATEGORIES & LOOKBOOKS</a>
                <a href="/catalog?sort=newest" @click="navMenuOpen = false" class="block py-4 text-[11px] uppercase tracking-wider font-semibold text-[#1A1A1A] border-b border-[#EEEEEE]">NEW ARRIVALS</a>
                <a href="/catalog" @click="navMenuOpen = false" class="block py-4 text-[11px] uppercase tracking-wider font-semibold text-[#1A1A1A] border-b border-[#EEEEEE]">SHOP ALL</a>
                <a href="/track" @click="navMenuOpen = false" class="block py-4 text-[11px] uppercase tracking-wider font-semibold text-[#1A1A1A] border-b border-[#EEEEEE]">TRACK ORDER</a>
            </div>
            
            <div class="p-6 border-t border-[#EEEEEE]">
                @auth
                    <span class="block text-[11px] uppercase tracking-wider text-[#666666] mb-4">LOGGED IN AS {{ Auth::user()->name }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full bg-[#1A1A1A] text-white py-3 text-[11px] uppercase tracking-wider font-semibold">LOGOUT</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block w-full text-center bg-[#1A1A1A] text-white py-3 text-[11px] uppercase tracking-wider font-semibold">SIGN IN</a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Slide-Over Mini-Cart Drawer -->
    <div x-show="openDrawer" x-cloak class="fixed inset-0 z-50 overflow-hidden font-sans">
        <div @click="openDrawer = false" class="absolute inset-0 bg-[#000000]/50 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-y-0 right-0 w-full max-w-sm bg-[#FAF2F2] shadow-2xl flex flex-col transition-transform transform">
            <div class="flex items-center justify-between px-6 py-5 border-b border-[#EEEEEE]">
                <span class="text-[11px] uppercase tracking-wider font-bold text-[#1A1A1A]">SHOPPING BAG (<span x-text="cart.count">0</span>)</span>
                <button @click="openDrawer = false" class="text-[#1A1A1A] p-2 hover:opacity-70">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-6">
                <template x-for="item in cart.items" :key="item.id">
                    <div class="flex space-x-4">
                        <img :src="item.image_url" :alt="item.title" class="w-20 h-28 object-cover bg-white">
                        <div class="flex-1 flex flex-col pt-1">
                            <span class="text-[11px] font-bold uppercase tracking-wider text-[#1A1A1A]" x-text="item.title"></span>
                            <span class="text-[10px] text-[#666666] uppercase mt-1" x-text="item.attributes"></span>
                            <span class="text-[11px] font-medium text-[#1A1A1A] mt-2" x-text="item.unit_price_formatted + ' x ' + item.quantity"></span>
                            <button @click="removeItem(item.id)" class="text-[9px] uppercase tracking-wider text-[#666666] underline mt-auto self-start hover:text-[#82203E]">REMOVE</button>
                        </div>
                    </div>
                </template>
                <div x-show="cart.count === 0" class="text-center py-12 text-[#666666] text-[11px] uppercase tracking-wider">
                    YOUR BAG IS CURRENTLY EMPTY.
                </div>
            </div>
            <div class="p-6 border-t border-[#EEEEEE] bg-[#FAF2F2]">
                <div class="flex justify-between text-[11px] uppercase font-bold text-[#1A1A1A] mb-6 tracking-wider">
                    <span>SUBTOTAL</span>
                    <span x-text="cart.subtotal_formatted">0.00 Birr</span>
                </div>
                <a href="/checkout" class="block w-full bg-[#1A1A1A] text-[#FFFFFF] text-center font-semibold text-[13px] uppercase tracking-[0.12em] py-3.5 hover:bg-[#82203E] transition-colors">
                    PROCEED TO CHECKOUT
                </a>
                <a href="/cart" class="block w-full text-center text-[10px] uppercase tracking-wider text-[#666666] mt-4 hover:text-[#1A1A1A] underline underline-offset-4">
                    VIEW SHOPPING BAG
                </a>
            </div>
        </div>
    </div>

    <!-- Main Storefront View -->
    <main class="flex-grow w-full">
        @yield('content')
    </main>

    <!-- Curved Floating Bag Button -->
    <button @click="openDrawer = true" class="fixed bottom-6 right-6 z-50 bg-[#1A1A1A] text-[#FFFFFF] p-4 rounded-full shadow-2xl hover:scale-105 hover:bg-[#82203E] transition-all flex items-center justify-center border-2 border-white group">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
        </svg>
        <span class="absolute -top-1.5 -right-1.5 bg-[#82203E] text-[#FFFFFF] text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full border-2 border-white shadow-sm" x-text="cart.count">0</span>
    </button>

    <!-- Editorial Footer -->
    <footer class="w-full bg-[#FAF2F2] border-t border-[#EEEEEE] font-sans pt-12 pb-16">
        <div class="max-w-7xl mx-auto px-4 md:px-8">
            
            <!-- Logo & Brand Header -->
            <div class="flex flex-col items-center text-center pb-10">
                <a href="/" class="inline-block mb-3">
                    <img src="{{ asset('logo.png') }}" alt="LULU Couture" class="h-16 sm:h-20 w-auto object-contain">
                </a>
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#82203E]">ADDIS ABABA • ETHIOPIA</p>
            </div>

            <!-- Links Grid (2 Columns on Mobile, 4 Columns on Desktop) -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 py-8 border-t border-b border-[#EEEEEE]">
                
                <!-- Column 1: Customer Care -->
                <div class="space-y-3">
                    <h4 class="text-[11px] sm:text-[12px] uppercase font-bold tracking-[0.15em] text-[#1A1A1A]">CUSTOMER CARE</h4>
                    <div class="flex flex-col space-y-2 text-[11px] text-[#666666]">
                        <a href="tel:+251911223344" class="hover:text-[#82203E] transition-colors">Contact Support</a>
                        <a href="/track" class="hover:text-[#82203E] transition-colors">Shipping & Returns</a>
                        <button type="button" @click="showGlobalSizeGuide = true" class="text-left hover:text-[#82203E] transition-colors">Size Guide</button>
                        <a href="/track" class="hover:text-[#82203E] transition-colors">Track Order</a>
                    </div>
                </div>

                <!-- Column 2: Boutique Location & Address -->
                <div class="space-y-3">
                    <h4 class="text-[11px] sm:text-[12px] uppercase font-bold tracking-[0.15em] text-[#1A1A1A]">BOUTIQUE LOCATION</h4>
                    <div class="flex flex-col space-y-1 text-[11px] text-[#666666]">
                        <p class="font-medium text-[#1A1A1A]">Bole Medhanialem</p>
                        <p>Edna Mall Area</p>
                        <p>Addis Ababa, Ethiopia</p>
                        <a href="tel:+251911223344" class="pt-1 font-semibold text-[#82203E] hover:underline">+251 911 223 344</a>
                    </div>
                </div>

                <!-- Column 3: Explore -->
                <div class="space-y-3">
                    <h4 class="text-[11px] sm:text-[12px] uppercase font-bold tracking-[0.15em] text-[#1A1A1A]">EXPLORE</h4>
                    <div class="flex flex-col space-y-2 text-[11px] text-[#666666]">
                        <a href="/catalog?sort=newest" class="hover:text-[#82203E] transition-colors">New Arrivals</a>
                        <a href="/categories" class="hover:text-[#82203E] transition-colors">Lookbook Collections</a>
                        <a href="/catalog" class="hover:text-[#82203E] transition-colors">Shop All Catalog</a>
                        <a href="/login" class="hover:text-[#82203E] transition-colors">Customer Account</a>
                    </div>
                </div>

                <!-- Column 4: Legal & Policies -->
                <div class="space-y-3">
                    <h4 class="text-[11px] sm:text-[12px] uppercase font-bold tracking-[0.15em] text-[#1A1A1A]">LEGAL & POLICIES</h4>
                    <div class="flex flex-col space-y-2 text-[11px] text-[#666666]">
                        <button type="button" @click="showPolicyModal = 'terms'" class="text-left hover:text-[#82203E] transition-colors">Terms & Conditions</button>
                        <button type="button" @click="showPolicyModal = 'privacy'" class="text-left hover:text-[#82203E] transition-colors">Privacy Policy</button>
                    </div>
                </div>

            </div>

            <!-- Footer Bottom Bar -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-[10px] text-[#666666] tracking-wider uppercase gap-4">
                <p>© {{ date('Y') }} LULU COUTURE. ALL RIGHTS RESERVED.</p>
                <div class="flex items-center space-x-6">
                    <a href="https://instagram.com/lulu__addis" target="_blank" class="hover:text-[#82203E] transition-colors">INSTAGRAM</a>
                    <a href="#" class="hover:text-[#82203E] transition-colors">FACEBOOK</a>
                    <a href="#" class="hover:text-[#82203E] transition-colors">TIKTOK</a>
                </div>
            </div>

        </div>
    </footer>

    <!-- Global Size Guide Modal -->
    <div x-show="showGlobalSizeGuide" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showGlobalSizeGuide = false" class="bg-white max-w-2xl w-full p-6 sm:p-8 rounded-sm border border-[#EEEEEE] shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6 border-b border-[#EEEEEE] pb-4">
                <h3 class="text-[14px] font-semibold uppercase tracking-[0.15em] text-[#1A1A1A]">LULU COUTURE SIZE GUIDE</h3>
                <button @click="showGlobalSizeGuide = false" class="text-[#1A1A1A] hover:text-[#82203E] font-bold text-xl p-1">&times;</button>
            </div>

            <div class="mb-6 border border-[#EEEEEE] p-4 bg-[#FAF2F2] space-y-2">
                <h4 class="text-[11px] font-bold uppercase tracking-wider text-[#82203E]">📏 How to Measure Accurately</h4>
                <div class="text-[12px] text-[#666666] leading-[1.6]">
                    • <strong>Bust:</strong> Measure around the fullest part of your bust.<br>
                    • <strong>Waist:</strong> Measure around your natural waistline.<br>
                    • <strong>Hips:</strong> Measure around the fullest part of your hips.
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-[12px] text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FAF2F2] text-[#1A1A1A] uppercase tracking-wider font-semibold">
                            <th class="p-3 border border-[#EEEEEE]">Size</th>
                            <th class="p-3 border border-[#EEEEEE]">Bust (in)</th>
                            <th class="p-3 border border-[#EEEEEE]">Waist (in)</th>
                            <th class="p-3 border border-[#EEEEEE]">Hips (in)</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr class="bg-white"><td class="p-3 border border-[#EEEEEE] font-medium text-[#1A1A1A]">XS</td><td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">31 - 33</td><td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">24 - 26</td><td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">34 - 36</td></tr>
                        <tr class="bg-white"><td class="p-3 border border-[#EEEEEE] font-medium text-[#1A1A1A]">S</td><td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">33 - 35</td><td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">26 - 28</td><td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">36 - 38</td></tr>
                        <tr class="bg-white"><td class="p-3 border border-[#EEEEEE] font-medium text-[#1A1A1A]">M</td><td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">35 - 37</td><td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">28 - 30</td><td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">38 - 40</td></tr>
                        <tr class="bg-white"><td class="p-3 border border-[#EEEEEE] font-medium text-[#1A1A1A]">L</td><td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">37 - 39</td><td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">30 - 32</td><td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">40 - 42</td></tr>
                        <tr class="bg-white"><td class="p-3 border border-[#EEEEEE] font-medium text-[#1A1A1A]">XL</td><td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">39 - 41</td><td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">32 - 34</td><td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">42 - 44</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Global Terms / Privacy Policy Modal -->
    <div x-show="showPolicyModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showPolicyModal = null" class="bg-white max-w-2xl w-full p-6 sm:p-8 rounded-sm border border-[#EEEEEE] shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6 border-b border-[#EEEEEE] pb-4">
                <h3 class="text-[14px] font-semibold uppercase tracking-[0.15em] text-[#1A1A1A]" x-text="showPolicyModal === 'terms' ? 'TERMS & CONDITIONS' : 'PRIVACY POLICY'"></h3>
                <button @click="showPolicyModal = null" class="text-[#1A1A1A] hover:text-[#82203E] font-bold text-xl p-1">&times;</button>
            </div>

            <div x-show="showPolicyModal === 'terms'" class="text-[12px] text-[#666666] leading-[1.8] space-y-4">
                <p>Welcome to LULU Couture. By browsing our store or placing an order, you agree to the following terms and conditions:</p>
                <ul class="list-disc pl-4 space-y-2">
                    <li><strong>Order Confirmation:</strong> All orders are subject to item availability and payment verification.</li>
                    <li><strong>Pricing:</strong> Prices are displayed in Ethiopian Birr (ETB) and are inclusive of standard handling. Express shipping charges are applied at checkout.</li>
                    <li><strong>Exchanges & Returns:</strong> Unworn items with original tags attached can be exchanged within 14 days of delivery.</li>
                </ul>
            </div>

            <div x-show="showPolicyModal === 'privacy'" class="text-[12px] text-[#666666] leading-[1.8] space-y-4">
                <p>At LULU Couture, your privacy is our highest priority:</p>
                <ul class="list-disc pl-4 space-y-2">
                    <li><strong>Data Collection:</strong> We only collect information required to process your orders and deliver exceptional customer service.</li>
                    <li><strong>Security:</strong> All customer data is encrypted and securely stored. We never share or sell personal information to third parties.</li>
                </ul>
            </div>
        </div>
    </div>

    <!-- Global Toast Notification -->
    <div x-show="toast.show" 
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 translate-y-4"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100 translate-y-0"
         x-transition:leave-end="opacity-0 translate-y-4"
         x-cloak
         class="fixed bottom-24 right-6 z-50 max-w-sm">
        <div :class="toast.isError ? 'bg-[#82203E] text-[#FFFFFF]' : 'bg-[#1A1A1A] text-[#FFFFFF]'"
             class="px-5 py-3.5 rounded-full shadow-2xl flex items-center space-x-3 border border-white">
            <span x-text="toast.isError ? '✕' : '✓'" class="font-bold text-[11px]"></span>
            <span x-text="toast.message" class="text-[11px] font-medium uppercase tracking-wider"></span>
        </div>
    </div>

    <script>
    window.addEventListener('load', function() {
        const loader = document.getElementById('pageLoader');
        if (loader) {
            loader.classList.add('opacity-0', 'pointer-events-none');
            setTimeout(() => { if (loader) loader.style.display = 'none'; }, 700);
        }
    });
    </script>
</body>
</html>
