<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="min-h-screen">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=5.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#82203E">
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

    <!-- PWA Manifest + Apple Meta Tags -->
    <link rel="manifest" href="/manifest.json">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="LULU">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192x192.png') }}">
    <link rel="apple-touch-startup-image" href="{{ asset('icons/icon-512x512.png') }}">

    <!-- Favicon Links -->
    <link rel="icon" type="image/png" href="{{ asset('logo.png') }}">
    <link rel="shortcut icon" href="{{ asset('logo.png') }}">

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

        /* Language Modal */
        .lang-modal-overlay {
            position: fixed; inset: 0; z-index: 9999;
            background: rgba(26,26,26,0.85);
            backdrop-filter: blur(8px);
            display: flex; align-items: center; justify-content: center;
            padding: 1rem;
        }
        .lang-modal-card {
            background: #FAF2F2;
            max-width: 400px; width: 100%;
            padding: 2.5rem 2rem;
            text-align: center;
            border: 1px solid #EEEEEE;
            box-shadow: 0 25px 60px rgba(0,0,0,0.3);
        }

        /* PWA Prompt */
        .pwa-prompt {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 9000;
            background: #1A1A1A; color: white;
            padding: 1rem 1.25rem;
            display: flex; align-items: flex-start; gap: 1rem;
            border-top: 2px solid #82203E;
            transform: translateY(100%);
            transition: transform 0.4s cubic-bezier(0.16,1,0.3,1);
        }
        .pwa-prompt.pwa-prompt--visible { transform: translateY(0); }
        .pwa-prompt-icon {
            width: 48px; height: 48px; flex-shrink: 0;
            border-radius: 10px; overflow: hidden; border: 1px solid #333;
        }
        .pwa-prompt-ios {
            position: fixed; bottom: 0; left: 0; right: 0; z-index: 9000;
            background: white; color: #1A1A1A;
            padding: 1.25rem;
            text-align: center;
            box-shadow: 0 -4px 20px rgba(0,0,0,0.15);
            border-top: 3px solid #82203E;
            transform: translateY(100%);
            transition: transform 0.4s cubic-bezier(0.16,1,0.3,1);
            border-radius: 20px 20px 0 0;
        }
        .pwa-prompt-ios.pwa-prompt--visible { transform: translateY(0); }
        .pwa-arrow { 
            display: inline-block; 
            width: 0; height: 0;
            border-left: 8px solid transparent; border-right: 8px solid transparent;
            border-top: 10px solid #82203E;
            margin: 0 auto 0.5rem;
        }

        /* Push Notification Prompt */
        .push-prompt {
            position: fixed; top: 80px; right: 1rem; z-index: 8999;
            background: white; border: 1px solid #EEEEEE;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            max-width: 300px; width: calc(100vw - 2rem);
            padding: 1.25rem;
            border-radius: 2px;
            border-left: 3px solid #82203E;
            transform: translateX(120%);
            transition: transform 0.4s cubic-bezier(0.16,1,0.3,1);
        }
        .push-prompt.push-prompt--visible { transform: translateX(0); }
    </style>

    @vite(['resources/js/storefront.js'])
</head>
<body x-data="miniCart()" @cart-updated.window="fetchCart(false); showToast($event.detail?.message || '{{ __('cart_bag') }} updated!')" @show-toast.window="showToast($event.detail?.message, $event.detail?.isError)" class="min-h-screen bg-[#FAF2F2] text-[#1A1A1A] font-sans antialiased flex flex-col selection:bg-[#82203E] selection:text-white">

    <!-- Page Loader -->
    <div id="pageLoader" class="fixed inset-0 z-[60] bg-[#FAF2F2] flex items-center justify-center transition-opacity duration-700 ease-out">
        <img src="{{ asset('logo.png') }}" alt="LULU Couture Loading" class="h-20 md:h-28 w-auto object-contain animate-pulse">
    </div>

    <!-- ═══ LANGUAGE SELECTION MODAL (first-time users only) ═══ -->
    <div id="langModal" class="lang-modal-overlay" style="display:none;">
        <div class="lang-modal-card">
            <img src="{{ asset('logo.png') }}" alt="LULU" class="h-16 w-auto mx-auto mb-6 object-contain">
            <h2 class="font-serif text-[24px] text-[#1A1A1A] mb-1">{{ __('lang_modal_title') }}</h2>
            <p class="text-[11px] uppercase tracking-[0.15em] text-[#666666] mb-8">{{ __('lang_modal_subtitle') }}</p>
            <div class="grid grid-cols-2 gap-3 mb-6">
                <button onclick="setLanguage('en')" 
                        class="py-3.5 border-2 border-[#1A1A1A] text-[#1A1A1A] hover:bg-[#1A1A1A] hover:text-white transition-all font-semibold text-[13px] tracking-wider uppercase flex flex-col items-center gap-1">
                    <span class="text-[18px]">🇬🇧</span>
                    <span>English</span>
                </button>
                <button onclick="setLanguage('am')"
                        class="py-3.5 border-2 border-[#82203E] text-[#82203E] hover:bg-[#82203E] hover:text-white transition-all font-semibold text-[13px] tracking-wider flex flex-col items-center gap-1">
                    <span class="text-[18px]">🇪🇹</span>
                    <span>አማርኛ</span>
                </button>
            </div>
            <p class="text-[10px] text-[#999999] uppercase tracking-wider">You can change this anytime from the menu</p>
        </div>
    </div>

    <!-- ═══ ANNOUNCEMENT BAR — conditional on active discount or custom message ═══ -->
    @php
        $announcementMessage = \App\Models\SystemSetting::get('announcement_message', '');
        $activeDiscount = \App\Models\Discount::where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>', now());
            })
            ->latest()
            ->first();

        $announcementBarText = '';
        if ($announcementMessage) {
            $announcementBarText = $announcementMessage;
        } elseif ($activeDiscount) {
            if ($activeDiscount->type === 'percentage') {
                $announcementBarText = '✦ ' . $activeDiscount->value . '% OFF — Use code ' . strtoupper($activeDiscount->code) . ' at checkout ✦';
            } else {
                $announcementBarText = '✦ ' . number_format($activeDiscount->value / 100, 2) . ' Birr OFF — Use code ' . strtoupper($activeDiscount->code) . ' at checkout ✦';
            }
        } else {
            $announcementBarText = __('announcement_shipping');
        }
    @endphp

    @if($announcementBarText)
        <div class="h-[36px] bg-[#F6DADF] text-[#82203E] flex items-center justify-center font-serif text-[12px] sm:text-[13px] tracking-[0.28px] relative z-50">
            <p class="font-medium px-4 text-center truncate">{{ $announcementBarText }}</p>
        </div>
    @endif

    <!-- ═══ STICKY HEADER ═══ -->
    <header class="sticky top-0 z-40 bg-[#FAF2F2] border-b border-[#EEEEEE] h-16 sm:h-20 w-full flex items-center font-sans shadow-xs">
        <div class="w-full px-4 md:px-8 flex items-center justify-between relative">
            
            <!-- Left: Mobile Menu / Desktop Nav Buttons -->
            <div class="flex-1 flex items-center">
                <button @click="navMenuOpen = true" class="lg:hidden text-[11px] uppercase tracking-[0.1em] font-semibold text-[#1A1A1A] hover:text-[#82203E] transition-colors flex items-center space-x-2">
                    <svg class="w-5 h-5 text-[#1A1A1A]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    <span>{{ __('nav_menu') }}</span>
                </button>
                <nav class="hidden lg:flex items-center space-x-6 text-[11px] font-semibold uppercase tracking-[0.15em] text-[#1A1A1A]">
                    <a href="/" class="hover:text-[#82203E] transition-colors">{{ __('nav_home') }}</a>
                    <a href="/categories" class="hover:text-[#82203E] transition-colors">{{ __('nav_categories') }}</a>
                    <a href="/catalog?sort=newest" class="hover:text-[#82203E] transition-colors">{{ __('nav_new_arrivals') }}</a>
                    <a href="/catalog" class="hover:text-[#82203E] transition-colors">{{ __('nav_shop_all') }}</a>
                </nav>
            </div>

            <!-- Dead-Center Logo — Larger on both mobile & desktop -->
            <a href="/" class="flex-shrink-0 absolute left-1/2 transform -translate-x-1/2 flex items-center justify-center">
                <img src="{{ asset('logo.png') }}" alt="LULU Couture" class="h-14 sm:h-[72px] w-auto object-contain">
            </a>

            <!-- Right: Search, Language, Bag -->
            <div class="flex-1 flex items-center justify-end">
                <!-- Mobile Right -->
                <div class="flex lg:hidden items-center space-x-3">
                    <!-- Language Toggle Mobile -->
                    <button onclick="toggleLanguage()" class="text-[10px] font-bold uppercase tracking-wider text-[#82203E] border border-[#82203E] rounded-full px-2.5 py-1 hover:bg-[#82203E] hover:text-white transition-all" title="Switch Language">
                        {{ app()->getLocale() === 'am' ? 'EN' : 'አማ' }}
                    </button>
                    <button @click="searchOpen = true" class="text-[#1A1A1A] hover:text-[#82203E] transition-colors p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    </button>
                    <button @click="openDrawer = true" class="text-[#1A1A1A] hover:text-[#82203E] transition-colors flex items-center p-1">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        <span class="ml-1 text-[10px] font-bold" x-text="cart.count">0</span>
                    </button>
                </div>
                
                <!-- Desktop Right Actions -->
                <div class="hidden lg:flex items-center space-x-5 text-[11px] uppercase tracking-[0.15em] font-semibold text-[#1A1A1A]">
                    <!-- Language Toggle Desktop -->
                    <button onclick="toggleLanguage()" class="flex items-center gap-1.5 hover:text-[#82203E] transition-colors text-[11px]" title="Switch Language / ቋንቋ ቀይር">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5h12M9 3v2m1.048 9.5A18.022 18.022 0 016.412 9m6.088 9h7M11 21l5-10 5 10M12.751 5C11.783 10.77 8.07 15.61 3 18.129"/></svg>
                        {{ app()->getLocale() === 'am' ? 'English' : 'አማርኛ' }}
                    </button>
                    <button @click="searchOpen = true" class="flex items-center hover:text-[#82203E] transition-colors">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                        {{ __('search_label') }}
                    </button>
                    @auth
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="hover:text-[#82203E] transition-colors uppercase text-[11px] font-semibold tracking-[0.15em]">
                                {{ __('nav_logout') }}
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="hover:text-[#82203E] transition-colors">
                            {{ __('nav_sign_in') }}
                        </a>
                    @endauth
                    <div class="h-4 w-px bg-[#EEEEEE]"></div>
                    <button @click="openDrawer = true" class="hover:text-[#82203E] transition-colors flex items-center">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                        {{ __('cart_bag') }} (<span x-text="cart.count">0</span>)
                    </button>
                </div>
            </div>
        </div>
    </header>

    <!-- Search Modal -->
    <div x-show="searchOpen" x-cloak class="fixed inset-0 z-50 overflow-hidden font-sans">
        <div @click="searchOpen = false" class="absolute inset-0 bg-[#000000]/50 backdrop-blur-sm transition-opacity"></div>
        <div class="relative z-10 bg-[#FAF2F2] border-b border-[#EEEEEE] shadow-2xl p-6 sm:p-10 max-w-4xl mx-auto mt-0 sm:mt-12 rounded-b-2xl transition-transform">
            <div class="flex items-center justify-between mb-6 pb-4 border-b border-[#EEEEEE]">
                <h3 class="text-[12px] font-semibold uppercase tracking-[0.15em] text-[#1A1A1A]">{{ __('search_catalog') }}</h3>
                <button @click="searchOpen = false" class="text-[#1A1A1A] hover:text-[#82203E] font-bold text-xl p-1">&times;</button>
            </div>
            <form @submit.prevent="performSearch()" class="flex space-x-3">
                <div class="relative flex-1">
                    <input type="text" 
                           x-model="searchQuery" 
                           @keyup.enter="performSearch()"
                           placeholder="{{ __('search_placeholder') }}" 
                           class="w-full px-5 py-3.5 pl-12 bg-white border border-[#EEEEEE] text-[13px] text-[#1A1A1A] placeholder-[#999999] focus:outline-none focus:border-[#1A1A1A] transition-colors">
                    <svg class="w-5 h-5 text-[#999999] absolute left-4 top-1/2 transform -translate-y-1/2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                <button type="submit" class="bg-[#1A1A1A] hover:bg-[#82203E] text-white text-[11px] font-semibold uppercase tracking-[0.15em] px-8 py-3.5 transition-colors">
                    {{ __('search_label') }}
                </button>
            </form>
            <div class="mt-6 pt-4 border-t border-[#EEEEEE] flex flex-wrap items-center gap-3 text-[11px] text-[#666666]">
                <span class="font-semibold uppercase tracking-wider text-[#1A1A1A]">{{ __('popular_searches') }}</span>
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
                <span class="text-[11px] uppercase tracking-[0.15em] font-bold text-[#1A1A1A]">{{ __('nav_navigation') }}</span>
                <button @click="navMenuOpen = false" class="text-[#1A1A1A] p-2 hover:opacity-70">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <div class="flex-1 overflow-y-auto px-6 py-4 space-y-0">
                <a href="/" @click="navMenuOpen = false" class="block py-4 text-[11px] uppercase tracking-wider font-semibold text-[#1A1A1A] border-b border-[#EEEEEE]">{{ __('nav_home') }}</a>
                <a href="/categories" @click="navMenuOpen = false" class="block py-4 text-[11px] uppercase tracking-wider font-semibold text-[#1A1A1A] border-b border-[#EEEEEE]">{{ __('nav_categories') }}</a>
                <a href="/catalog?sort=newest" @click="navMenuOpen = false" class="block py-4 text-[11px] uppercase tracking-wider font-semibold text-[#1A1A1A] border-b border-[#EEEEEE]">{{ __('nav_new_arrivals') }}</a>
                <a href="/catalog" @click="navMenuOpen = false" class="block py-4 text-[11px] uppercase tracking-wider font-semibold text-[#1A1A1A] border-b border-[#EEEEEE]">{{ __('nav_shop_all') }}</a>
                <a href="/track" @click="navMenuOpen = false" class="block py-4 text-[11px] uppercase tracking-wider font-semibold text-[#1A1A1A] border-b border-[#EEEEEE]">{{ __('nav_track_order') }}</a>
                
                <!-- Language Switch in mobile menu -->
                <div class="py-4 border-b border-[#EEEEEE]">
                    <p class="text-[9px] uppercase tracking-[0.15em] text-[#999] mb-3">{{ app()->getLocale() === 'am' ? 'Language / ቋንቋ' : 'Language / ቋንቋ' }}</p>
                    <div class="flex gap-2">
                        <form method="POST" action="{{ route('language.switch', 'en') }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-[11px] font-semibold uppercase tracking-wider border {{ app()->getLocale() === 'en' ? 'bg-[#1A1A1A] text-white border-[#1A1A1A]' : 'border-[#EEEEEE] text-[#666]' }} transition-all">
                                🇬🇧 English
                            </button>
                        </form>
                        <form method="POST" action="{{ route('language.switch', 'am') }}">
                            @csrf
                            <button type="submit" class="px-4 py-2 text-[11px] font-semibold tracking-wider border {{ app()->getLocale() === 'am' ? 'bg-[#82203E] text-white border-[#82203E]' : 'border-[#EEEEEE] text-[#666]' }} transition-all">
                                🇪🇹 አማርኛ
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            <div class="p-6 border-t border-[#EEEEEE]">
                @auth
                    <span class="block text-[11px] uppercase tracking-wider text-[#666666] mb-4">{{ __('auth_logged_in_as', ['name' => Auth::user()->name]) }}</span>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="w-full bg-[#1A1A1A] text-white py-3 text-[11px] uppercase tracking-wider font-semibold">{{ __('nav_logout') }}</button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block w-full text-center bg-[#1A1A1A] text-white py-3 text-[11px] uppercase tracking-wider font-semibold">{{ __('nav_sign_in') }}</a>
                @endauth
            </div>
        </div>
    </div>

    <!-- Mini-Cart Drawer -->
    <div x-show="openDrawer" x-cloak class="fixed inset-0 z-50 overflow-hidden font-sans">
        <div @click="openDrawer = false" class="absolute inset-0 bg-[#000000]/50 backdrop-blur-sm transition-opacity"></div>
        <div class="fixed inset-y-0 right-0 w-full max-w-sm bg-[#FAF2F2] shadow-2xl flex flex-col transition-transform transform">
            <div class="flex items-center justify-between px-6 py-5 border-b border-[#EEEEEE]">
                <span class="text-[11px] uppercase tracking-wider font-bold text-[#1A1A1A]">{{ __('cart_title') }} (<span x-text="cart.count">0</span>)</span>
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
                            <button @click="removeItem(item.id)" class="text-[9px] uppercase tracking-wider text-[#666666] underline mt-auto self-start hover:text-[#82203E]">{{ __('cart_remove') }}</button>
                        </div>
                    </div>
                </template>
                <div x-show="cart.count === 0" class="text-center py-12 text-[#666666] text-[11px] uppercase tracking-wider">
                    {{ __('cart_empty') }}
                </div>
            </div>
            <div class="p-6 border-t border-[#EEEEEE] bg-[#FAF2F2]">
                <div class="flex justify-between text-[11px] uppercase font-bold text-[#1A1A1A] mb-6 tracking-wider">
                    <span>{{ __('cart_subtotal') }}</span>
                    <span x-text="cart.subtotal_formatted">0.00 Birr</span>
                </div>
                <a href="/checkout" class="block w-full bg-[#1A1A1A] text-[#FFFFFF] text-center font-semibold text-[13px] uppercase tracking-[0.12em] py-3.5 hover:bg-[#82203E] transition-colors">
                    {{ __('cart_checkout') }}
                </a>
                <a href="/cart" class="block w-full text-center text-[10px] uppercase tracking-wider text-[#666666] mt-4 hover:text-[#1A1A1A] underline underline-offset-4">
                    {{ __('cart_view') }}
                </a>
            </div>
        </div>
    </div>

    <!-- Main Content -->
    <main class="flex-grow w-full">
        @yield('content')
    </main>

    <!-- Floating Bag Button -->
    <button @click="openDrawer = true" class="fixed bottom-6 right-6 z-[100] bg-[#1A1A1A] text-[#FFFFFF] p-4 rounded-full shadow-2xl hover:scale-105 hover:bg-[#82203E] transition-all flex items-center justify-center border-2 border-white group">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
        </svg>
        <span class="absolute -top-1.5 -right-1.5 bg-[#82203E] text-[#FFFFFF] text-[10px] font-bold w-5 h-5 flex items-center justify-center rounded-full border-2 border-white shadow-sm" x-text="cart.count">0</span>
    </button>

    <!-- ═══ EDITORIAL FOOTER ═══ -->
    @php
        $footerAddr1 = \App\Models\SystemSetting::get('footer_address_line1', 'Bole Medhanialem');
        $footerAddr2 = \App\Models\SystemSetting::get('footer_address_line2', 'Edna Mall Area');
        $footerAddr3 = \App\Models\SystemSetting::get('footer_address_line3', 'Addis Ababa, Ethiopia');
        $footerMaps = \App\Models\SystemSetting::get('footer_maps_link', 'https://maps.google.com');
        $footerPhone = \App\Models\SystemSetting::get('footer_phone', '+251 911 223 344');
        $cleanFooterPhone = preg_replace('/[^0-9+]/', '', $footerPhone);
        
        $footerInsta = \App\Models\SystemSetting::get('footer_instagram', 'https://instagram.com/lulu__addis');
        $footerFb = \App\Models\SystemSetting::get('footer_facebook', '#');
        $footerTiktok = \App\Models\SystemSetting::get('footer_tiktok', '#');
    @endphp

    <footer class="w-full bg-[#FAF2F2] border-t border-[#EEEEEE] font-sans pt-12 pb-16">
        <div class="max-w-7xl mx-auto px-4 md:px-8">
            
            <!-- Logo & Brand Header -->
            <div class="flex flex-col items-center text-center pb-10">
                <a href="/" class="inline-block mb-3">
                    <img src="{{ asset('logo.png') }}" alt="LULU Couture" class="h-20 sm:h-24 w-auto object-contain">
                </a>
                <p class="text-[11px] font-bold uppercase tracking-[0.2em] text-[#82203E]">ADDIS ABABA • ETHIOPIA</p>
            </div>

            <!-- Links Grid -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-6 sm:gap-8 py-8 border-t border-b border-[#EEEEEE]">
                
                <div class="space-y-3">
                    <h4 class="text-[11px] sm:text-[12px] uppercase font-bold tracking-[0.15em] text-[#1A1A1A]">{{ __('footer_customer_care') }}</h4>
                    <div class="flex flex-col space-y-2 text-[11px] text-[#666666]">
                        <a href="tel:{{ $cleanFooterPhone }}" class="hover:text-[#82203E] transition-colors">{{ __('footer_contact_support') }}</a>
                        <a href="/track" class="hover:text-[#82203E] transition-colors">{{ __('footer_shipping_returns') }}</a>
                        <button type="button" @click="showGlobalSizeGuide = true" class="text-left hover:text-[#82203E] transition-colors">{{ __('footer_size_guide') }}</button>
                        <a href="/track" class="hover:text-[#82203E] transition-colors">{{ __('footer_track_order') }}</a>
                    </div>
                </div>

                <div class="space-y-3">
                    <h4 class="text-[11px] sm:text-[12px] uppercase font-bold tracking-[0.15em] text-[#1A1A1A]">{{ __('footer_boutique_location') }}</h4>
                    <div class="flex flex-col space-y-1 text-[11px] text-[#666666]">
                        @if(!empty($footerMaps) && $footerMaps !== '#')
                            <a href="{{ $footerMaps }}" target="_blank" class="hover:text-[#82203E] transition-colors block">
                                <p class="font-medium text-[#1A1A1A]">{{ $footerAddr1 }}</p>
                                <p>{{ $footerAddr2 }}</p>
                                <p>{{ $footerAddr3 }}</p>
                            </a>
                        @else
                            <p class="font-medium text-[#1A1A1A]">{{ $footerAddr1 }}</p>
                            <p>{{ $footerAddr2 }}</p>
                            <p>{{ $footerAddr3 }}</p>
                        @endif
                        <a href="tel:{{ $cleanFooterPhone }}" class="pt-1 font-semibold text-[#82203E] hover:underline">{{ $footerPhone }}</a>
                    </div>
                </div>

                <div class="space-y-3">
                    <h4 class="text-[11px] sm:text-[12px] uppercase font-bold tracking-[0.15em] text-[#1A1A1A]">{{ __('footer_explore') }}</h4>
                    <div class="flex flex-col space-y-2 text-[11px] text-[#666666]">
                        <a href="/catalog?sort=newest" class="hover:text-[#82203E] transition-colors">{{ __('nav_new_arrivals') }}</a>
                        <a href="/categories" class="hover:text-[#82203E] transition-colors">{{ __('footer_lookbook') }}</a>
                        <a href="/catalog" class="hover:text-[#82203E] transition-colors">{{ __('footer_shop_all') }}</a>
                        <a href="/login" class="hover:text-[#82203E] transition-colors">{{ __('footer_account') }}</a>
                    </div>
                </div>

                <div class="space-y-3">
                    <h4 class="text-[11px] sm:text-[12px] uppercase font-bold tracking-[0.15em] text-[#1A1A1A]">{{ __('footer_legal') }}</h4>
                    <div class="flex flex-col space-y-2 text-[11px] text-[#666666]">
                        <button type="button" @click="showPolicyModal = 'terms'" class="text-left hover:text-[#82203E] transition-colors">{{ __('footer_terms') }}</button>
                        <button type="button" @click="showPolicyModal = 'privacy'" class="text-left hover:text-[#82203E] transition-colors">{{ __('footer_privacy') }}</button>
                    </div>
                </div>

            </div>

            <!-- Footer Bottom Bar -->
            <div class="pt-8 flex flex-col sm:flex-row items-center justify-between text-[10px] text-[#666666] tracking-wider uppercase gap-4">
                <p>{{ __('footer_copyright', ['year' => date('Y')]) }}</p>
                <div class="flex items-center space-x-6">
                    @if(!empty($footerInsta) && $footerInsta !== '#')
                        <a href="{{ $footerInsta }}" target="_blank" class="hover:text-[#82203E] transition-colors">{{ __('footer_instagram') }}</a>
                    @endif
                    @if(!empty($footerFb) && $footerFb !== '#')
                        <a href="{{ $footerFb }}" target="_blank" class="hover:text-[#82203E] transition-colors">{{ __('footer_facebook') }}</a>
                    @endif
                    @if(!empty($footerTiktok) && $footerTiktok !== '#')
                        <a href="{{ $footerTiktok }}" target="_blank" class="hover:text-[#82203E] transition-colors">{{ __('footer_tiktok') }}</a>
                    @endif
                </div>
            </div>

        </div>
    </footer>

    <!-- ═══ GLOBAL SIZE GUIDE MODAL (with US/UK/EU tabs) ═══ -->
    @php
        $globalSizeGuides = \App\Models\SizeGuide::where('is_active', true)->orderBy('sort_order')->get();
        $globalSizeGuideTitle = \App\Models\SystemSetting::get('size_guide_title', 'LULU Couture Size Guide');
        $globalSizeGuideDesc = \App\Models\SystemSetting::get('size_guide_description', '');
        $globalSizeUnit = \App\Models\SystemSetting::get('size_guide_unit', 'in');
        $hasMultiStandard = $globalSizeGuides->filter(fn($s) => $s->us_size || $s->uk_size || $s->eu_size)->count() > 0;
    @endphp

    <div x-show="showGlobalSizeGuide" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showGlobalSizeGuide = false" 
             x-data="{ sizeTab: 'int' }"
             class="bg-white max-w-2xl w-full p-6 sm:p-8 rounded-sm border border-[#EEEEEE] shadow-2xl max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-6 border-b border-[#EEEEEE] pb-4">
                <h3 class="text-[14px] font-semibold uppercase tracking-[0.15em] text-[#1A1A1A]">{{ $globalSizeGuideTitle }}</h3>
                <button @click="showGlobalSizeGuide = false" class="text-[#1A1A1A] hover:text-[#82203E] font-bold text-xl p-1">&times;</button>
            </div>

            @if($globalSizeGuideDesc)
                <div class="mb-6 border border-[#EEEEEE] p-4 bg-[#FAF2F2] space-y-2">
                    <div class="text-[12px] text-[#666666] leading-[1.6] whitespace-pre-line">{{ $globalSizeGuideDesc }}</div>
                </div>
            @endif

            @if($hasMultiStandard)
            <!-- Size Standard Tabs -->
            <div class="flex gap-1 mb-4 border-b border-[#EEEEEE]">
                <button @click="sizeTab = 'int'" :class="sizeTab === 'int' ? 'border-b-2 border-[#82203E] text-[#82203E]' : 'text-[#666]'" class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider transition-colors">INT</button>
                <button @click="sizeTab = 'us'" :class="sizeTab === 'us' ? 'border-b-2 border-[#82203E] text-[#82203E]' : 'text-[#666]'" class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider transition-colors">US</button>
                <button @click="sizeTab = 'uk'" :class="sizeTab === 'uk' ? 'border-b-2 border-[#82203E] text-[#82203E]' : 'text-[#666]'" class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider transition-colors">UK</button>
                <button @click="sizeTab = 'eu'" :class="sizeTab === 'eu' ? 'border-b-2 border-[#82203E] text-[#82203E]' : 'text-[#666]'" class="px-4 py-2.5 text-[11px] font-semibold uppercase tracking-wider transition-colors">EU</button>
            </div>
            @endif

            <div class="overflow-x-auto">
                <table class="w-full text-[12px] text-left border-collapse">
                    <thead>
                        <tr class="bg-[#FAF2F2] text-[#1A1A1A] uppercase tracking-wider font-semibold">
                            <th class="p-3 border border-[#EEEEEE]">{{ __('size_guide_size') }}</th>
                            @if($hasMultiStandard)
                                <th class="p-3 border border-[#EEEEEE]" x-show="sizeTab === 'int'">INT</th>
                                <th class="p-3 border border-[#EEEEEE]" x-show="sizeTab === 'us'">US</th>
                                <th class="p-3 border border-[#EEEEEE]" x-show="sizeTab === 'uk'">UK</th>
                                <th class="p-3 border border-[#EEEEEE]" x-show="sizeTab === 'eu'">EU</th>
                            @endif
                            <th class="p-3 border border-[#EEEEEE]">{{ __('size_guide_bust') }} ({{ $globalSizeUnit }})</th>
                            <th class="p-3 border border-[#EEEEEE]">{{ __('size_guide_waist') }} ({{ $globalSizeUnit }})</th>
                            <th class="p-3 border border-[#EEEEEE]">{{ __('size_guide_hips') }} ({{ $globalSizeUnit }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($globalSizeGuides as $size)
                            <tr class="bg-white hover:bg-[#FAF2F2] transition-colors">
                                <td class="p-3 border border-[#EEEEEE] font-medium text-[#1A1A1A]">{{ $size->name }}</td>
                                @if($hasMultiStandard)
                                    <td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]" x-show="sizeTab === 'int'">{{ $size->name }}</td>
                                    <td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]" x-show="sizeTab === 'us'">{{ $size->us_size ?? '—' }}</td>
                                    <td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]" x-show="sizeTab === 'uk'">{{ $size->uk_size ?? '—' }}</td>
                                    <td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]" x-show="sizeTab === 'eu'">{{ $size->eu_size ?? '—' }}</td>
                                @endif
                                <td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">{{ $size->bust ?? '—' }}</td>
                                <td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">{{ $size->waist ?? '—' }}</td>
                                <td class="p-3 border border-[#EEEEEE] text-[#4A4A4A]">{{ $size->hips ?? '—' }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- ═══ TERMS / PRIVACY POLICY MODAL — content from admin ═══ -->
    @php
        $termsContent = \App\Models\SystemSetting::get('terms_and_conditions', '');
        $privacyContent = \App\Models\SystemSetting::get('privacy_policy', '');
    @endphp

    <div x-show="showPolicyModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50 backdrop-blur-sm">
        <div @click.away="showPolicyModal = null" class="bg-white max-w-2xl w-full rounded-sm border border-[#EEEEEE] shadow-2xl max-h-[90vh] overflow-hidden flex flex-col">
            <!-- Header -->
            <div class="flex justify-between items-center px-6 sm:px-8 py-5 border-b border-[#EEEEEE] flex-shrink-0">
                <div class="flex items-center gap-4">
                    <div class="w-1 h-6 bg-[#82203E]"></div>
                    <h3 class="text-[14px] font-semibold uppercase tracking-[0.15em] text-[#1A1A1A]" x-text="showPolicyModal === 'terms' ? '{{ __('policy_terms_title') }}' : '{{ __('policy_privacy_title') }}'"></h3>
                </div>
                <button @click="showPolicyModal = null" class="text-[#1A1A1A] hover:text-[#82203E] font-bold text-xl p-1">&times;</button>
            </div>
            <!-- Scrollable Body -->
            <div class="flex-1 overflow-y-auto px-6 sm:px-8 py-6">
                <div x-show="showPolicyModal === 'terms'" class="prose-lulu">
                    @if($termsContent)
                        {!! nl2br(e($termsContent)) !!}
                    @else
                        <p class="text-[#666] text-[13px]">Terms & conditions will be published here soon. Please contact us for inquiries.</p>
                    @endif
                </div>
                <div x-show="showPolicyModal === 'privacy'" class="prose-lulu">
                    @if($privacyContent)
                        {!! nl2br(e($privacyContent)) !!}
                    @else
                        <p class="text-[#666] text-[13px]">Privacy policy will be published here soon. Please contact us for inquiries.</p>
                    @endif
                </div>
            </div>
            <!-- Footer bar -->
            <div class="px-6 sm:px-8 py-4 border-t border-[#EEEEEE] bg-[#FAF2F2] flex-shrink-0">
                <p class="text-[10px] text-[#999] uppercase tracking-wider text-center">LULU Couture • Addis Ababa, Ethiopia</p>
            </div>
        </div>
    </div>

    <!-- ═══ TOAST NOTIFICATION ═══ -->
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

    <!-- ═══ PWA INSTALL PROMPT (Android / Generic) ═══ -->
    <div id="pwaPrompt" class="pwa-prompt" role="dialog" aria-label="{{ __('pwa_install_title') }}">
        <img src="{{ asset('icons/icon-192x192.png') }}" alt="LULU" class="pwa-prompt-icon" onerror="this.src='{{ asset('logo.png') }}'">
        <div class="flex-1 min-w-0">
            <p class="text-[13px] font-bold uppercase tracking-wider mb-0.5">{{ __('pwa_install_title') }}</p>
            <p class="text-[11px] text-[#999] leading-snug mb-3">{{ __('pwa_install_body') }}</p>
            <div class="flex gap-2 flex-wrap">
                <button id="pwaInstallBtn" class="bg-[#82203E] hover:bg-[#6a1932] text-white text-[11px] font-semibold uppercase tracking-wider px-5 py-2 transition-colors rounded-sm">
                    {{ __('pwa_install_btn') }}
                </button>
                <button id="pwaDismissBtn" class="text-[#666] text-[11px] font-semibold uppercase tracking-wider px-3 py-2 transition-colors hover:text-white">
                    {{ __('pwa_install_dismiss') }}
                </button>
            </div>
        </div>
    </div>

    <!-- ═══ PWA INSTALL PROMPT — iOS specific ═══ -->
    <div id="pwaIosPrompt" class="pwa-prompt-ios" role="dialog">
        <div class="pwa-arrow"></div>
        <img src="{{ asset('icons/icon-192x192.png') }}" alt="LULU" class="w-12 h-12 mx-auto mb-3 rounded-xl border border-[#EEEEEE]" onerror="this.src='{{ asset('logo.png') }}'">
        <p class="text-[13px] font-bold text-[#1A1A1A] mb-1">{{ __('pwa_install_title') }}</p>
        <p class="text-[12px] text-[#666] mb-4">
            Tap <svg class="inline w-4 h-4 text-[#007AFF]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
            then <strong>"Add to Home Screen"</strong> to install the LULU app.
        </p>
        <button id="pwaIosDismissBtn" class="text-[11px] font-semibold uppercase tracking-wider text-[#666] px-4 py-2 hover:text-[#1A1A1A] transition-colors">
            {{ __('pwa_install_dismiss') }}
        </button>
    </div>

    <!-- ═══ PUSH NOTIFICATION PROMPT ═══ -->
    <div id="pushPrompt" class="push-prompt" role="dialog">
        <div class="flex items-start gap-3 mb-4">
            <div class="w-8 h-8 rounded-full bg-[#F6DADF] flex items-center justify-center flex-shrink-0 mt-0.5">
                <svg class="w-4 h-4 text-[#82203E]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
            </div>
            <div>
                <p class="text-[13px] font-bold text-[#1A1A1A] mb-1">{{ __('push_title') }}</p>
                <p class="text-[11px] text-[#666] leading-relaxed">{{ __('push_body') }}</p>
            </div>
        </div>
        <div class="flex gap-2">
            <button id="pushEnableBtn" class="flex-1 bg-[#82203E] hover:bg-[#6a1932] text-white text-[11px] font-semibold uppercase tracking-wider py-2.5 transition-colors rounded-sm">
                {{ __('push_enable') }}
            </button>
            <button id="pushDismissBtn" class="text-[11px] font-semibold uppercase tracking-wider text-[#666] px-3 py-2.5 hover:text-[#1A1A1A] transition-colors">
                {{ __('push_dismiss') }}
            </button>
        </div>
    </div>

    <!-- ═══ GLOBAL SCRIPTS ═══ -->
    <style>
        .prose-lulu { font-size: 13px; line-height: 1.8; color: #4A4A4A; }
        .prose-lulu strong { color: #1A1A1A; font-weight: 600; }
        .prose-lulu p + p { margin-top: 0.75rem; }
    </style>

    <script>
    // ── Page Loader ─────────────────────────────────────────────────────────
    window.addEventListener('load', function() {
        const loader = document.getElementById('pageLoader');
        if (loader) {
            loader.classList.add('opacity-0', 'pointer-events-none');
            setTimeout(() => { if (loader) loader.style.display = 'none'; }, 700);
        }
    });

    // ── Language Toggle (JS version for header button, uses form POST) ────────
    function toggleLanguage() {
        const currentLang = '{{ app()->getLocale() }}';
        const nextLang = currentLang === 'en' ? 'am' : 'en';
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/language/' + nextLang;
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }

    function setLanguage(lang) {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = '/language/' + lang;
        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').content;
        form.appendChild(csrf);
        document.body.appendChild(form);
        form.submit();
    }

    // ── Language Modal (first-time users) ────────────────────────────────────
    (function() {
        const LANG_KEY = 'lulu_lang_chosen';
        const modal = document.getElementById('langModal');
        if (!modal) return;
        // Show modal if user has never chosen a language
        if (!localStorage.getItem(LANG_KEY)) {
            modal.style.display = 'flex';
        }
        // Intercept language buttons to also persist the key
        modal.querySelectorAll('button[onclick]').forEach(btn => {
            const orig = btn.getAttribute('onclick');
            btn.setAttribute('onclick', `localStorage.setItem('${LANG_KEY}', '1'); ${orig}`);
        });
    })();

    // ── PWA + Push Logic ─────────────────────────────────────────────────────
    (function() {
        const PAGE_VISIT_KEY  = 'lulu_page_visits';
        const ORDER_DONE_KEY  = 'lulu_order_placed';
        const PWA_SHOWN_KEY   = 'lulu_pwa_shown';
        const PUSH_SHOWN_KEY  = 'lulu_push_shown';
        const PUSH_SUBS_KEY   = 'lulu_push_subscribed';

        let deferredInstallPrompt = null;

        // Count page visits
        let visits = parseInt(localStorage.getItem(PAGE_VISIT_KEY) || '0') + 1;
        localStorage.setItem(PAGE_VISIT_KEY, visits);

        const isIOS = /ipad|iphone|ipod/.test(navigator.userAgent.toLowerCase()) && !window.MSStream;
        const isStandalone = window.matchMedia('(display-mode: standalone)').matches || navigator.standalone === true;

        // Register Service Worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js', { scope: '/' })
                .then(reg => {
                    console.log('[PWA] Service worker registered:', reg.scope);
                })
                .catch(err => console.warn('[PWA] SW registration failed:', err));
        }

        // Capture beforeinstallprompt (Android/Chrome)
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredInstallPrompt = e;
            maybeShowPWAPrompt();
        });

        function shouldShowPWAPrompt() {
            if (isStandalone) return false;
            if (localStorage.getItem(PWA_SHOWN_KEY) === 'dismissed') return false;
            const orderPlaced = sessionStorage.getItem(ORDER_DONE_KEY);
            // Show after 1 order OR after 5-10 page visits
            return orderPlaced || visits >= 5;
        }

        function maybeShowPWAPrompt() {
            if (!shouldShowPWAPrompt()) return;
            if (isIOS) {
                showIOSPrompt();
            } else if (deferredInstallPrompt) {
                showAndroidPrompt();
            }
        }

        function showAndroidPrompt() {
            const prompt = document.getElementById('pwaPrompt');
            if (!prompt) return;
            setTimeout(() => prompt.classList.add('pwa-prompt--visible'), 800);

            document.getElementById('pwaInstallBtn')?.addEventListener('click', async () => {
                if (!deferredInstallPrompt) return;
                deferredInstallPrompt.prompt();
                const result = await deferredInstallPrompt.userChoice;
                localStorage.setItem(PWA_SHOWN_KEY, result.outcome === 'accepted' ? 'installed' : 'dismissed');
                prompt.classList.remove('pwa-prompt--visible');
                deferredInstallPrompt = null;
                // Show push prompt shortly after
                if (result.outcome === 'accepted') setTimeout(showPushPrompt, 2000);
            });

            document.getElementById('pwaDismissBtn')?.addEventListener('click', () => {
                localStorage.setItem(PWA_SHOWN_KEY, 'dismissed');
                prompt.classList.remove('pwa-prompt--visible');
            });
        }

        function showIOSPrompt() {
            const prompt = document.getElementById('pwaIosPrompt');
            if (!prompt) return;
            setTimeout(() => prompt.classList.add('pwa-prompt--visible'), 800);
            document.getElementById('pwaIosDismissBtn')?.addEventListener('click', () => {
                localStorage.setItem(PWA_SHOWN_KEY, 'dismissed');
                prompt.classList.remove('pwa-prompt--visible');
            });
        }

        // Trigger on page load if already has enough visits
        window.addEventListener('load', () => {
            setTimeout(maybeShowPWAPrompt, 1500);
            // Also show push notification prompt if app is installed
            if (isStandalone && !localStorage.getItem(PUSH_SHOWN_KEY) && !localStorage.getItem(PUSH_SUBS_KEY)) {
                setTimeout(showPushPrompt, 3000);
            }
        });

        // ── Push Notifications ───────────────────────────────────────────────
        function showPushPrompt() {
            if (!('Notification' in window) || !('serviceWorker' in navigator)) return;
            if (Notification.permission === 'granted') return; // Already granted
            if (localStorage.getItem(PUSH_SHOWN_KEY)) return;

            const prompt = document.getElementById('pushPrompt');
            if (!prompt) return;
            setTimeout(() => prompt.classList.add('push-prompt--visible'), 500);

            document.getElementById('pushEnableBtn')?.addEventListener('click', async () => {
                localStorage.setItem(PUSH_SHOWN_KEY, '1');
                prompt.classList.remove('push-prompt--visible');
                await requestPushPermission();
            });

            document.getElementById('pushDismissBtn')?.addEventListener('click', () => {
                localStorage.setItem(PUSH_SHOWN_KEY, '1');
                prompt.classList.remove('push-prompt--visible');
            });
        }

        async function requestPushPermission() {
            try {
                const permission = await Notification.requestPermission();
                if (permission !== 'granted') return;

                const reg = await navigator.serviceWorker.ready;
                // VAPID public key — set this in .env as VAPID_PUBLIC_KEY
                const vapidPublicKey = '{{ config("app.vapid_public_key", "") }}';
                if (!vapidPublicKey) {
                    console.warn('[PWA] VAPID public key not configured in .env');
                    return;
                }

                const subscription = await reg.pushManager.subscribe({
                    userVisibleOnly: true,
                    applicationServerKey: urlBase64ToUint8Array(vapidPublicKey),
                });

                const subJson = subscription.toJSON();
                await fetch('/push/subscribe', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify(subJson),
                });

                localStorage.setItem('lulu_push_subscribed', '1');
                console.log('[PWA] Push subscription saved.');
            } catch (err) {
                console.warn('[PWA] Push subscription failed:', err);
            }
        }

        function urlBase64ToUint8Array(base64String) {
            const padding = '='.repeat((4 - base64String.length % 4) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = window.atob(base64);
            return Uint8Array.from([...rawData].map(char => char.charCodeAt(0)));
        }

        // Mark order as placed (call from checkout confirmation page)
        window.luluMarkOrderPlaced = function() {
            sessionStorage.setItem(ORDER_DONE_KEY, '1');
            // Show PWA prompt after order
            setTimeout(maybeShowPWAPrompt, 2000);
        };
    })();
    </script>
</body>
</html>
