@extends('storefront.layouts.app')
@section('title', 'My Account — LULU Couture')

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
                <li><a href="{{ route('storefront.home') }}" class="hover:text-[#1A1A1A]">{{ __('nav_home') }}</a></li>
                <li><span class="mx-2">/</span></li>
                <li class="text-[#1A1A1A]">{{ __('account_profile') }}</li>
            </ol>
        </nav>

        <div class="flex flex-col lg:flex-row gap-8 lg:gap-12">
            <!-- Sidebar -->
            <aside class="lg:w-1/4 w-full">
                <h1 class="font-serif text-3xl lg:text-4xl text-[#1A1A1A] mb-6 lg:mb-8 text-center lg:text-left">{{ __('profile_title') }}</h1>
                
                <!-- Horizontal scrollable nav on mobile, standard list on desktop -->
                <nav class="flex lg:flex-col gap-2 lg:gap-4 overflow-x-auto lg:overflow-x-visible pb-4 lg:pb-0 mb-8 lg:mb-0 border-b border-[#EEEEEE] lg:border-none scrollbar-none whitespace-nowrap lg:whitespace-normal px-4 lg:px-0">
                    <a href="{{ route('account.profile') }}" data-account-tab="{{ route('account.profile') }}" @click.prevent="navigate($event, '{{ route('account.profile') }}')" class="inline-block lg:block text-xs lg:text-sm uppercase tracking-widest font-semibold px-4 py-2 lg:px-0 lg:py-0 border border-[#1A1A1A] lg:border-none rounded-full lg:rounded-none bg-[#1A1A1A] lg:bg-transparent text-white lg:text-[#1A1A1A]">
                        {{ __('account_profile') }}
                    </a>
                    <a href="{{ route('account.orders') }}" data-account-tab="{{ route('account.orders') }}" @click.prevent="navigate($event, '{{ route('account.orders') }}')" class="inline-block lg:block text-xs lg:text-sm uppercase tracking-widest px-4 py-2 lg:px-0 lg:py-0 border border-stone-200 lg:border-none rounded-full lg:rounded-none hover:bg-[#82203E]/5 lg:hover:bg-transparent text-[#666666] hover:text-[#1A1A1A] transition-all">
                        {{ __('account_orders') }}
                    </a>
                    <a href="{{ route('account.wishlist') }}" data-account-tab="{{ route('account.wishlist') }}" @click.prevent="navigate($event, '{{ route('account.wishlist') }}')" class="inline-block lg:block text-xs lg:text-sm uppercase tracking-widest px-4 py-2 lg:px-0 lg:py-0 border border-stone-200 lg:border-none rounded-full lg:rounded-none hover:bg-[#82203E]/5 lg:hover:bg-transparent text-[#666666] hover:text-[#1A1A1A] transition-all">
                        {{ __('account_wishlist') }} ({{ $wishlistCount ?? 0 }})
                    </a>
                    <a href="{{ route('account.points') }}" data-account-tab="{{ route('account.points') }}" @click.prevent="navigate($event, '{{ route('account.points') }}')" class="inline-block lg:block text-xs lg:text-sm uppercase tracking-widest px-4 py-2 lg:px-0 lg:py-0 border border-stone-200 lg:border-none rounded-full lg:rounded-none hover:bg-[#82203E]/5 lg:hover:bg-transparent text-[#666666] hover:text-[#1A1A1A] transition-all">
                        {{ __('account_points') }}
                    </a>
                    
                    <form method="POST" action="{{ route('logout') }}" class="inline-block lg:block lg:mt-8 ml-auto lg:ml-0">
                        @csrf
                        <button type="submit" class="text-xs lg:text-sm uppercase tracking-widest text-[#82203E] hover:text-[#1A1A1A] transition-colors px-4 py-2 lg:px-0 lg:py-0 border border-stone-200 lg:border-none rounded-full lg:rounded-none cursor-pointer">
                            {{ __('nav_logout') }}
                        </button>
                    </form>
                </nav>
            </aside>

            <!-- Main Content Container with dynamic id for AJAX swap -->
            <main class="lg:w-3/4 space-y-12" id="account-main-content">
                <!-- Welcome -->
                <div>
                    <h2 class="text-2xl font-serif text-[#1A1A1A] mb-2">{{ __('auth_logged_in_as', ['name' => $user->first_name ?? $user->name]) }}</h2>
                </div>

                <!-- Stats Row -->
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                    <div class="bg-white p-6 sm:p-8 rounded-sm border border-[#EEEEEE] text-center">
                        <div class="text-4xl font-serif text-[#1A1A1A] mb-2">{{ $recentOrders->count() ?? 0 }}</div>
                        <div class="text-xs uppercase tracking-widest text-[#666666]">{{ __('account_orders') }}</div>
                        <a href="{{ route('account.orders') }}" class="inline-block mt-4 text-xs underline text-[#1A1A1A]">{{ __('home_view_all') }}</a>
                    </div>
                    <div class="bg-white p-6 sm:p-8 rounded-sm border border-[#EEEEEE] text-center">
                        <div class="text-4xl font-serif text-[#1A1A1A] mb-2">{{ $wishlistCount ?? 0 }}</div>
                        <div class="text-xs uppercase tracking-widest text-[#666666]">{{ __('account_wishlist') }}</div>
                        <a href="{{ route('account.wishlist') }}" class="inline-block mt-4 text-xs underline text-[#1A1A1A]">{{ __('home_view_all') }}</a>
                    </div>
                    <div class="bg-white p-6 sm:p-8 rounded-sm border border-[#EEEEEE] text-center">
                        <div class="text-4xl font-serif text-[#82203E] mb-2">{{ number_format($balance ?? 0) }}</div>
                        <div class="text-xs uppercase tracking-widest text-[#666666]">{{ __('account_points') }}</div>
                        <a href="{{ route('account.points') }}" class="inline-block mt-4 text-xs underline text-[#1A1A1A]">{{ __('home_view_all') }}</a>
                    </div>
                </div>

                <!-- Profile Edit Form -->
                <div class="bg-white p-6 sm:p-8 rounded-sm border border-[#EEEEEE]">
                    <h3 class="text-lg font-serif text-[#1A1A1A] mb-6">{{ __('profile_personal_details') }}</h3>
                    <form action="{{ route('account.profile.update') }}" method="POST" class="space-y-6">
                        @csrf
                        @method('PUT')
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label for="name" class="block text-xs uppercase tracking-widest text-[#666666] mb-2">{{ __('profile_full_name') }}</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="w-full border-b border-[#EEEEEE] py-2 focus:outline-none focus:border-[#1A1A1A] bg-transparent transition-colors" required>
                                @error('name')<span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>@enderror
                            </div>
                            <div>
                                <label for="email" class="block text-xs uppercase tracking-widest text-[#666666] mb-2">{{ __('profile_email') }}</label>
                                <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="w-full border-b border-[#EEEEEE] py-2 focus:outline-none focus:border-[#1A1A1A] bg-transparent transition-colors" required>
                                @error('email')<span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>@enderror
                            </div>
                            <div class="md:col-span-2">
                                <label for="phone" class="block text-xs uppercase tracking-widest text-[#666666] mb-2">{{ __('profile_phone') }}</label>
                                <input type="tel" id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" class="w-full border-b border-[#EEEEEE] py-2 focus:outline-none focus:border-[#1A1A1A] bg-transparent transition-colors">
                                @error('phone')<span class="text-xs text-rose-500 mt-1 block">{{ $message }}</span>@enderror
                            </div>
                        </div>
                        <div class="pt-4">
                            <button type="submit" class="bg-[#1A1A1A] text-white px-8 py-3 text-xs uppercase tracking-widest hover:bg-[#82203E] transition-colors">{{ __('btn_save_changes') }}</button>
                        </div>
                    </form>
                </div>

                <!-- Recent Orders -->
                <div>
                    <h3 class="text-lg font-serif text-[#1A1A1A] mb-6">Recent Orders</h3>
                    @if(empty($recentOrders) || $recentOrders->isEmpty())
                        <div class="bg-white p-8 rounded-sm border border-[#EEEEEE] text-center">
                            <p class="text-[#666666] mb-4">You haven't placed any orders yet.</p>
                            <a href="{{ route('catalog.index') }}" class="inline-block bg-[#1A1A1A] text-white px-8 py-3 text-xs uppercase tracking-widest hover:bg-[#82203E] transition-colors">Start Shopping</a>
                        </div>
                    @else
                        <div class="space-y-4">
                            @foreach($recentOrders->take(3) as $order)
                                <div class="bg-white p-6 rounded-sm border border-[#EEEEEE] flex flex-col md:flex-row items-center justify-between gap-4">
                                    <div>
                                        <div class="text-sm font-medium text-[#1A1A1A]">Order #{{ $order->order_number }}</div>
                                        <div class="text-xs text-[#666666] mt-1">{{ $order->created_at->format('F j, Y') }}</div>
                                    </div>
                                    <div class="flex items-center gap-6">
                                        <div class="text-sm text-[#1A1A1A]">{{ number_format($order->total / 100, 2) }} Birr</div>
                                        <span class="px-3 py-1 text-[10px] uppercase tracking-widest rounded-full 
                                            @if($order->status === 'pending') bg-stone-100 text-stone-700
                                            @elseif($order->status === 'confirmed') bg-emerald-100 text-emerald-700
                                            @elseif($order->status === 'packed') bg-blue-100 text-blue-700
                                            @elseif($order->status === 'shipped') bg-amber-100 text-amber-700
                                            @elseif($order->status === 'delivered') bg-green-100 text-green-700
                                            @elseif($order->status === 'cancelled') bg-rose-100 text-rose-700
                                            @else bg-gray-100 text-gray-700 @endif">
                                            {{ $order->status }}
                                        </span>
                                        <a href="{{ route('order.confirmation', $order->order_number) }}" class="text-xs uppercase tracking-widest text-[#1A1A1A] underline hover:text-[#82203E] transition-colors">View Details</a>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>
</div>
@endsection
