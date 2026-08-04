@extends('storefront.layouts.app')

@section('title', 'Order Confirmation #' . $order->order_number . ' — LULU Couture')

@section('content')
<div x-data="{ copied: false }" class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
    <!-- Success Banner -->
    <div class="bg-stone-900 text-white p-8 mb-8 text-center shadow-lg rounded-sm">
        <span class="text-[10px] font-bold uppercase tracking-[0.3em] text-[#C49A9A] block mb-2">Order Confirmed</span>
        <h1 class="text-3xl font-serif font-bold uppercase tracking-tight mb-3">Thank You For Your Order!</h1>
        <div class="inline-flex items-center space-x-2 text-stone-300 text-xs font-light tracking-wide bg-stone-800/80 px-4 py-2 rounded-full border border-stone-700">
            <span>Order Reference: <strong class="font-mono font-bold text-white uppercase tracking-wider select-all">{{ $order->order_number }}</strong></span>
            <button type="button" @click="navigator.clipboard.writeText('{{ $order->order_number }}'); copied = true; setTimeout(() => copied = false, 2000)" 
                    class="bg-[#8C6554] hover:bg-white hover:text-[#221F1F] text-white text-[9px] font-bold uppercase tracking-wider px-3 py-1 rounded-full transition-all">
                <span x-text="copied ? 'COPIED!' : 'COPY CODE'">COPY CODE</span>
            </button>
        </div>
    </div>

    <!-- Telegram Live Order Tracking & Notification Card -->
    <div class="bg-[#221F1F] text-white p-6 sm:p-8 rounded-sm shadow-xl mb-10 flex flex-col md:flex-row items-center justify-between gap-6 border border-stone-800">
        <div class="space-y-2 text-center md:text-left">
            <div class="flex items-center justify-center md:justify-start space-x-2">
                <span class="text-xl">🤖</span>
                <h3 class="text-sm font-serif font-bold uppercase tracking-[0.2em] text-[#C49A9A]">Track & Get Status Updates on Telegram</h3>
            </div>
            <p class="text-xs text-stone-300 font-light max-w-lg leading-relaxed">
                Connect your order to our official Telegram Bot to receive real-time updates on packing, shipping, and delivery notifications straight to your Telegram app!
            </p>
        </div>

        <a href="https://t.me/{{ config('services.telegram.bot_username', 'shoplulu_bot') }}?start={{ $order->order_number }}" target="_blank" 
           class="bg-[#8C6554] hover:bg-white hover:text-[#221F1F] text-white text-[11px] font-bold uppercase tracking-[0.2em] px-8 py-4 rounded-full shadow-lg transition-all flex items-center space-x-2 flex-shrink-0">
            <svg class="w-4 h-4 fill-current" viewBox="0 0 24 24"><path d="M12 0C5.37 0 0 5.37 0 12s5.37 12 12 12 12-5.37 12-12S18.63 0 12 0zm5.56 8.16l-2.12 10c-.15.7-.57.87-1.16.54l-3.25-2.4-1.57 1.51c-.17.17-.32.32-.65.32l.23-3.3 6.01-5.43c.26-.23-.06-.36-.4-.14l-7.43 4.68-3.2-.1c-.7-.22-.71-.7.15-1.04l12.5-4.82c.58-.21 1.09.14.89.88z"/></svg>
            <span>Start Telegram Bot &rarr;</span>
        </a>
    </div>

    <!-- Status Timeline Step Component -->
    @php
        $statuses = ['pending', 'confirmed', 'packed', 'shipped', 'delivered'];
        $currentIdx = array_search($order->status, $statuses);
        if ($currentIdx === false) $currentIdx = 0;
    @endphp

    <div class="bg-white border border-stone-200 p-6 mb-10">
        <h2 class="text-xs font-black uppercase tracking-widest text-stone-900 mb-6">Order Status Timeline</h2>
        
        @if(in_array($order->status, ['cancelled', 'refunded']))
            <div class="p-4 bg-rose-50 text-rose-800 text-xs font-bold uppercase tracking-wider text-center border border-rose-200">
                Order Status: {{ strtoupper($order->status) }}
            </div>
        @else
            <div class="grid grid-cols-5 gap-2 text-center">
                @foreach($statuses as $idx => $st)
                    <div class="flex flex-col items-center">
                        <div class="w-8 h-8 rounded-full flex items-center justify-center font-bold text-xs mb-2 {{ $idx <= $currentIdx ? 'bg-stone-900 text-white' : 'bg-stone-100 text-stone-400 border border-stone-300' }}">
                            {{ $idx + 1 }}
                        </div>
                        <span class="text-[10px] font-bold uppercase tracking-wider {{ $idx <= $currentIdx ? 'text-stone-900' : 'text-stone-400' }}">
                            {{ $st }}
                        </span>
                    </div>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Order & Customer Receipt Breakdown -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 mb-10">
        <!-- Customer & Delivery Details -->
        <div class="bg-white border border-stone-200 p-6 space-y-3">
            <h3 class="text-xs font-black uppercase tracking-widest text-stone-900 border-b border-stone-200 pb-2">Delivery & Fulfillment</h3>
            <p class="text-xs font-bold text-stone-900">{{ $order->customer_name }}</p>
            <p class="text-xs text-stone-600 font-light">Phone: {{ $order->customer_phone }}</p>
            <p class="text-xs text-stone-600 font-light">
                Logistics Mode: <strong class="uppercase text-stone-900">{{ str_replace('_', ' ', $order->logistics_mode) }}</strong>
            </p>
            <p class="text-xs text-stone-600 font-light">
                {{ $order->customer_address }}{{ $order->customer_district ? ', ' . $order->customer_district : '' }}{{ $order->customer_city ? ', ' . $order->customer_city : '' }}{{ $order->customer_country ? ', ' . $order->customer_country : ', Ethiopia' }}
            </p>

            @if($order->google_maps_link)
                <a href="{{ $order->google_maps_link }}" target="_blank" class="text-xs text-rose-600 font-bold underline flex items-center gap-1.5 pt-1">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                    Google Maps Pin Link
                </a>
            @endif

            <div class="pt-2 border-t border-stone-100 space-y-1">
                <span class="text-[10px] font-bold uppercase text-stone-500 block">Preferred Schedule</span>
                <p class="text-xs font-semibold text-stone-900">
                    {{ $order->preferred_date ? $order->preferred_date->format('F j, Y') : 'N/A' }} 
                    ({{ $order->preferred_time }})
                </p>
            </div>
        </div>

        <!-- Price & Payment Breakdown -->
        <div class="bg-white border border-stone-200 p-6 space-y-3">
            <h3 class="text-xs font-black uppercase tracking-widest text-stone-900 border-b border-stone-200 pb-2">Payment Summary</h3>
            <div class="flex justify-between text-xs text-stone-600">
                <span>Subtotal</span>
                <span class="font-semibold text-stone-900">{{ number_format($order->subtotal / 100, 2) }} Birr</span>
            </div>
            @if($order->discount_amount > 0)
                <div class="flex justify-between text-xs text-emerald-700 font-medium">
                    <span>Discount</span>
                    <span>-{{ number_format($order->discount_amount / 100, 2) }} Birr</span>
                </div>
            @endif
            <div class="flex justify-between text-xs text-stone-600">
                <span>Delivery Fee</span>
                <span class="font-semibold text-stone-900">{{ number_format($order->delivery_fee / 100, 2) }} Birr</span>
            </div>
            <div class="flex justify-between text-sm font-black text-stone-900 pt-2 border-t border-stone-100">
                <span>Total Amount</span>
                <span>{{ number_format($order->total / 100, 2) }} Birr</span>
            </div>

            @if($order->deposit_amount > 0)
                <div class="mt-3 pt-3 border-t border-stone-200 space-y-1 bg-stone-50 p-3 rounded">
                    <div class="flex justify-between text-xs font-bold text-stone-900">
                        <span>Deposit Paid / Due Now</span>
                        <span>{{ number_format($order->deposit_amount / 100, 2) }} Birr</span>
                    </div>
                    <div class="flex justify-between text-xs text-stone-600">
                        <span>Balance Remaining at Handover</span>
                        <span>{{ number_format($order->balance_due / 100, 2) }} Birr</span>
                    </div>
                </div>
            @endif

            <div class="pt-2 border-t border-stone-100 text-xs space-y-1">
                <p class="text-stone-600">Payment Method: <strong class="uppercase text-stone-900">{{ str_replace('_', ' ', $order->payment_method) }}</strong></p>
                @if($order->payment_method === 'transfer' && $order->bankAccount)
                    <p class="text-stone-600">Bank Account: <strong class="text-stone-900">{{ $order->bankAccount->bank_name }} ({{ $order->bankAccount->account_number }})</strong></p>
                @endif
                @if($order->payment_proof)
                    <a href="{{ $order->payment_proof }}" target="_blank" class="text-xs text-rose-600 font-bold underline block pt-1">📷 View Uploaded Receipt Proof</a>
                @endif
            </div>
        </div>
    </div>

    <!-- Items List -->
    <div class="bg-white border border-stone-200 p-6">
        <h3 class="text-xs font-black uppercase tracking-widest text-stone-900 border-b border-stone-200 pb-3 mb-4">Ordered Items</h3>
        <div class="space-y-4">
            @foreach($order->items as $item)
                <div class="flex items-center justify-between text-xs border-b border-stone-100 pb-3">
                    <div class="flex items-center space-x-3">
                        @if($item->variant && $item->variant->image)
                            <img src="{{ $item->variant->image->url }}" alt="{{ $item->product_title }}" class="w-12 h-16 object-cover bg-stone-100 rounded">
                        @elseif($item->product && $item->product->primaryImage)
                            <img src="{{ $item->product->primaryImage->url }}" alt="{{ $item->product_title }}" class="w-12 h-16 object-cover bg-stone-100 rounded">
                        @endif
                        <div>
                            <span class="font-bold text-stone-900 block">{{ $item->product_title }}</span>
                            <span class="text-[10px] text-stone-500 font-mono">Product Code: {{ $item->variant_sku }} &times; {{ $item->quantity }}</span>
                        </div>
                    </div>
                    <span class="font-bold text-stone-900">{{ number_format($item->total_price / 100, 2) }} Birr</span>
                </div>
            @endforeach
        </div>
    </div>
</div>
@endsection
