@extends('storefront.layouts.app')
@section('title', 'My Orders — LULU Couture')

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
                <li class="text-[#1A1A1A]">My Orders</li>
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
                    <a href="{{ route('account.orders') }}" data-account-tab="{{ route('account.orders') }}" @click.prevent="navigate($event, '{{ route('account.orders') }}')" class="inline-block lg:block text-xs lg:text-sm uppercase tracking-widest font-semibold px-4 py-2 lg:px-0 lg:py-0 border border-[#1A1A1A] lg:border-none rounded-full lg:rounded-none bg-[#1A1A1A] lg:bg-transparent text-white lg:text-[#1A1A1A]">
                        My Orders
                    </a>
                    <a href="{{ route('account.wishlist') }}" data-account-tab="{{ route('account.wishlist') }}" @click.prevent="navigate($event, '{{ route('account.wishlist') }}')" class="inline-block lg:block text-xs lg:text-sm uppercase tracking-widest px-4 py-2 lg:px-0 lg:py-0 border border-stone-200 lg:border-none rounded-full lg:rounded-none hover:bg-[#82203E]/5 lg:hover:bg-transparent text-[#666666] hover:text-[#1A1A1A] transition-all">
                        Wishlist
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
                <h2 class="text-3xl font-serif text-[#1A1A1A] mb-8">Order History</h2>                 @if($orders->isEmpty())
                    <div class="bg-white p-6 sm:p-12 rounded-sm border border-[#EEEEEE] text-center">
                        <p class="text-lg font-serif text-[#1A1A1A] mb-2">No orders yet</p>
                        <p class="text-[#666666] mb-8">When you place orders, they will appear here.</p>
                        <a href="{{ route('catalog.index') }}" class="inline-block bg-[#1A1A1A] text-white px-8 py-3 text-xs uppercase tracking-widest hover:bg-[#82203E] transition-colors">Start Shopping</a>
                    </div>
                @else
                    <div class="space-y-8">
                        @foreach($orders as $order)
                            <div class="bg-white rounded-sm border border-[#EEEEEE] overflow-hidden">
                                <!-- Order Header -->
                                <div class="bg-stone-50 p-4 sm:p-6 border-b border-[#EEEEEE] flex flex-wrap items-center justify-between gap-4">
                                    <div class="flex gap-8">
                                        <div>
                                            <div class="text-[10px] uppercase tracking-widest text-[#666666] mb-1">Order Placed</div>
                                            <div class="text-sm text-[#1A1A1A]">{{ $order->created_at->format('M j, Y') }}</div>
                                        </div>
                                        <div>
                                            <div class="text-[10px] uppercase tracking-widest text-[#666666] mb-1">Total</div>
                                            <div class="text-sm text-[#1A1A1A]">{{ number_format($order->total / 100, 2) }} Birr</div>
                                        </div>
                                        <div>
                                            <div class="text-[10px] uppercase tracking-widest text-[#666666] mb-1">Order #</div>
                                            <div class="text-sm text-[#1A1A1A]">{{ $order->order_number }}</div>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4">
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
                                        <a href="{{ route('order.confirmation', $order->order_number) }}" class="text-xs uppercase tracking-widest text-[#1A1A1A] underline hover:text-[#82203E] transition-colors">Track Order</a>
                                    </div>
                                </div>
                                
                                <!-- Order Items -->
                                <div class="p-4 sm:p-6">
                                    <div class="space-y-6">
                                        @foreach($order->items as $item)
                                            <div class="flex gap-6 items-center">
                                                <div class="w-20 h-24 bg-stone-100 flex-shrink-0 overflow-hidden">
                                                    @if($item->variant && $item->variant->image)
                                                        <img src="{{ $item->variant->image->url }}" alt="{{ $item->product_title }}" class="w-full h-full object-cover">
                                                    @elseif($item->product && $item->product->primaryImage)
                                                        <img src="{{ $item->product->primaryImage->url }}" alt="{{ $item->product_title }}" class="w-full h-full object-cover">
                                                    @else
                                                        <div class="w-full h-full bg-stone-200"></div>
                                                    @endif
                                                </div>
                                                <div class="flex-1">
                                                    <h4 class="text-sm font-medium text-[#1A1A1A] mb-1">{{ $item->product_title }}</h4>
                                                    @if($item->variant_sku)
                                                        <p class="text-xs text-[#666666] mb-1">SKU: {{ $item->variant_sku }}</p>
                                                    @endif
                                                    <div class="flex items-center justify-between mt-2">
                                                        <span class="text-sm text-[#1A1A1A]">{{ number_format($item->unit_price / 100, 2) }} Birr</span>
                                                        <span class="text-sm text-[#666666]">Qty: {{ $item->quantity }}</span>
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-8">
                        {{ $orders->links() }}
                    </div>
                @endif
            </main>
        </div>
    </div>
</div>
@endsection
