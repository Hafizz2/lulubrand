@extends('storefront.layouts.app')
@section('title', 'LULU Points — LULU Couture')

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
                <li class="text-[#1A1A1A]">LULU Points</li>
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
                    <a href="{{ route('account.wishlist') }}" data-account-tab="{{ route('account.wishlist') }}" @click.prevent="navigate($event, '{{ route('account.wishlist') }}')" class="inline-block lg:block text-xs lg:text-sm uppercase tracking-widest px-4 py-2 lg:px-0 lg:py-0 border border-stone-200 lg:border-none rounded-full lg:rounded-none hover:bg-[#82203E]/5 lg:hover:bg-transparent text-[#666666] hover:text-[#1A1A1A] transition-all">
                        Wishlist
                    </a>
                    <a href="{{ route('account.points') }}" data-account-tab="{{ route('account.points') }}" @click.prevent="navigate($event, '{{ route('account.points') }}')" class="inline-block lg:block text-xs lg:text-sm uppercase tracking-widest font-semibold px-4 py-2 lg:px-0 lg:py-0 border border-[#1A1A1A] lg:border-none rounded-full lg:rounded-none bg-[#1A1A1A] lg:bg-transparent text-white lg:text-[#1A1A1A]">
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
            <main class="lg:w-3/4 space-y-8" id="account-main-content">
                <h2 class="text-3xl font-serif text-[#1A1A1A] mb-4">LULU Points</h2>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-8">
                    <!-- Balance Card -->
                    <div class="bg-[#82203E] text-white p-6 sm:p-8 rounded-sm relative overflow-hidden">
                        <!-- Decorative element -->
                        <div class="absolute -right-8 -top-8 w-32 h-32 rounded-full border-4 border-white/10"></div>
                        <div class="absolute -right-16 -bottom-16 w-48 h-48 rounded-full border-4 border-white/10"></div>
                        
                        <div class="relative z-10">
                            <div class="text-[10px] uppercase tracking-widest text-[#F6DADF] mb-2">Available Balance</div>
                            <div class="text-5xl font-serif mb-6">{{ number_format($balance ?? 0) }} <span class="text-xl font-sans">PTS</span></div>
                            
                            <div class="grid grid-cols-2 gap-4 border-t border-white/20 pt-4">
                                <div>
                                    <div class="text-[10px] uppercase tracking-widest text-[#F6DADF]">Lifetime Earned</div>
                                    <div class="text-lg font-serif">{{ number_format($loyaltyPoint->lifetime_earned ?? 0) }}</div>
                                </div>
                                <div>
                                    <div class="text-[10px] uppercase tracking-widest text-[#F6DADF]">Lifetime Redeemed</div>
                                    <div class="text-lg font-serif">{{ number_format($loyaltyPoint->lifetime_redeemed ?? 0) }}</div>
                                </div>
                            </div>
                        </div>
                    </div>                    <!-- How it works -->
                    <div class="bg-white p-6 sm:p-8 rounded-sm border border-[#EEEEEE]">
                        <h3 class="text-lg font-serif text-[#1A1A1A] mb-4">How LULU Points Work</h3>
                        <ul class="space-y-4">
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-[#82203E] mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <div>
                                    <div class="text-sm font-medium text-[#1A1A1A]">Earn Points</div>
                                    <div class="text-xs text-[#666666]">Every {{ number_format($settings['birr_per_point'] ?? 100) }} Birr spent = 1 point</div>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-[#82203E] mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"></path></svg>
                                <div>
                                    <div class="text-sm font-medium text-[#1A1A1A]">Redeem Points</div>
                                    <div class="text-xs text-[#666666]">1 point = {{ number_format(($settings['point_value_cents'] ?? 100) / 100, 2) }} Birr discount</div>
                                </div>
                            </li>
                            <li class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-[#82203E] mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                                <div>
                                    <div class="text-sm font-medium text-[#1A1A1A]">Minimum Redemption</div>
                                    <div class="text-xs text-[#666666]">You need at least {{ $settings['min_redeem'] ?? 50 }} points to redeem.</div>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Transaction History -->
                <div class="bg-white rounded-sm border border-[#EEEEEE] overflow-hidden">
                    <div class="p-6 border-b border-[#EEEEEE]">
                        <h3 class="text-lg font-serif text-[#1A1A1A]">Transaction History</h3>
                    </div>
                    
                    @if($history->isEmpty())
                        <div class="p-12 text-center">
                            <p class="text-[#666666]">You don't have any point transactions yet.</p>
                        </div>
                    @else
                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="bg-stone-50 border-b border-[#EEEEEE]">
                                        <th class="py-4 px-6 text-[10px] uppercase tracking-widest text-[#666666] font-medium">Date</th>
                                        <th class="py-4 px-6 text-[10px] uppercase tracking-widest text-[#666666] font-medium">Description</th>
                                        <th class="py-4 px-6 text-[10px] uppercase tracking-widest text-[#666666] font-medium">Type</th>
                                        <th class="py-4 px-6 text-[10px] uppercase tracking-widest text-[#666666] font-medium text-right">Points</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($history as $transaction)
                                        <tr class="border-b border-[#EEEEEE] last:border-b-0 hover:bg-stone-50/50 transition-colors">
                                            <td class="py-4 px-6 text-sm text-[#1A1A1A] whitespace-nowrap">{{ $transaction->created_at->format('M j, Y') }}</td>
                                            <td class="py-4 px-6">
                                                <div class="text-sm text-[#1A1A1A]">{{ $transaction->description }}</div>
                                                @if($transaction->source_type === 'order')
                                                    <div class="text-xs text-[#666666] mt-0.5">Order #{{ $transaction->source_id }}</div>
                                                @endif
                                            </td>
                                            <td class="py-4 px-6">
                                                @if($transaction->type === 'earn')
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium uppercase tracking-widest bg-green-100 text-green-800">
                                                        Earned
                                                    </span>
                                                @else
                                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-[10px] font-medium uppercase tracking-widest bg-red-100 text-red-800">
                                                        Redeemed
                                                    </span>
                                                @endif
                                            </td>
                                            <td class="py-4 px-6 text-sm font-medium text-right whitespace-nowrap @if($transaction->type === 'earn') text-green-600 @else text-red-600 @endif">
                                                {{ $transaction->type === 'earn' ? '+' : '-' }}{{ $transaction->points }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        <div class="p-6 border-t border-[#EEEEEE]">
                            {{ $history->links() }}
                        </div>
                    @endif
                </div>
            </main>
        </div>
    </div>
</div>
@endsection
