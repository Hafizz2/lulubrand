@extends('storefront.layouts.app')

@section('title', 'Shopping Bag — LULU Couture')

@section('content')
<div x-data="fullCartPage()" class="max-w-[1440px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">
    <div class="mb-8 border-b border-[#EEEEEE] pb-4 flex items-center justify-between">
        <h1 class="text-[20px] sm:text-[24px] font-serif font-normal text-[#1A1A1A]">
            Shopping Bag (<span x-text="summary.count">{{ $summary['count'] }}</span>)
        </h1>
        <a href="/catalog" class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#666666] hover:text-[#1A1A1A] underline underline-offset-4">&larr; Continue Shopping</a>
    </div>

    @if(session('error'))
        <div class="mb-6 p-4 bg-rose-50 text-rose-800 text-[12px] font-medium border border-rose-200">
            {{ session('error') }}
        </div>
    @endif
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 text-emerald-800 text-[12px] font-medium border border-emerald-200">
            {{ session('success') }}
        </div>
    @endif

    <div x-show="summary.count === 0" class="text-center py-16 bg-[#FFFFFF] border border-[#EEEEEE] p-8">
        <h3 class="text-[14px] font-serif font-normal uppercase tracking-wider text-[#1A1A1A] mb-2">Your Bag is Empty</h3>
        <p class="text-[12px] text-[#666666] mb-6">Discover our latest new arrivals and couture drops.</p>
        <a href="/catalog" class="inline-block bg-[#1A1A1A] text-white text-[11px] font-semibold uppercase tracking-[0.12em] px-8 py-3.5 hover:bg-[#333333] transition-colors rounded-none">
            Shop Collection
        </a>
    </div>

    <div x-show="summary.count > 0" class="grid grid-cols-1 lg:grid-cols-12 gap-10">
        <!-- Item List (8 cols) -->
        <div class="lg:col-span-8 space-y-4">
            <template x-for="item in summary.items" :key="item.id">
                <div class="bg-[#FFFFFF] border border-[#EEEEEE] p-4 flex gap-4 items-center">
                    <img :src="item.image_url" :alt="item.title" class="w-20 h-28 object-cover bg-[#F9F9F9] flex-shrink-0">
                    
                    <div class="flex-1 min-w-0">
                        <a :href="item.product_url" class="text-[12px] font-semibold uppercase tracking-wider text-[#1A1A1A] hover:text-[#82203E] truncate block" x-text="item.title"></a>
                        <p class="text-[10px] text-[#666666] uppercase tracking-wider mt-0.5" x-text="item.attributes"></p>
                        <p class="text-[12px] font-medium text-[#1A1A1A] mt-1" x-text="item.unit_price_formatted"></p>

                        <!-- Quantity Control -->
                        <div class="flex items-center space-x-4 mt-3">
                            <div class="flex items-center border border-[#EEEEEE]">
                                <button type="button" @click="updateQty(item.id, item.quantity - 1)" class="px-3 py-1 text-xs font-bold text-[#1A1A1A] hover:bg-[#F9F9F9]">-</button>
                                <span class="px-3 py-1 text-xs font-medium text-[#1A1A1A]" x-text="item.quantity"></span>
                                <button type="button" @click="updateQty(item.id, item.quantity + 1)" class="px-3 py-1 text-xs font-bold text-[#1A1A1A] hover:bg-[#F9F9F9]">+</button>
                            </div>
                            <button type="button" @click="removeItem(item.id)" class="text-[10px] font-semibold uppercase text-[#666666] hover:text-[#82203E] tracking-wider underline">Remove</button>
                        </div>
                    </div>

                    <div class="text-right">
                        <span class="text-[13px] font-bold text-[#1A1A1A]" x-text="item.total_price_formatted"></span>
                    </div>
                </div>
            </template>
        </div>

        <!-- Summary & Coupon Sidebar (4 cols) -->
        <div class="lg:col-span-4">
            <div class="bg-[#FFFFFF] border border-[#EEEEEE] p-6 sticky top-24 space-y-6">
                <h2 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A] border-b border-[#EEEEEE] pb-3">Order Summary</h2>

                <!-- Coupon Form -->
                <div>
                    <form @submit.prevent="applyCoupon()" class="flex space-x-2">
                        <input type="text" x-model="couponCode" placeholder="COUPON CODE" class="flex-1 px-3 py-2.5 bg-[#F9F9F9] border border-[#EEEEEE] text-[11px] focus:outline-none focus:border-[#1A1A1A] uppercase tracking-wider text-[#1A1A1A] placeholder-[#999999]">
                        <button type="submit" class="bg-[#1A1A1A] text-white text-[10px] font-semibold px-4 py-2.5 uppercase tracking-wider hover:bg-[#333333] transition-colors rounded-none">Apply</button>
                    </form>
                    <p x-show="couponMessage" class="text-[11px] font-medium text-emerald-600 mt-1.5" x-text="couponMessage"></p>
                </div>

                <!-- Price Totals -->
                <div class="space-y-3 text-[12px] border-t border-b border-[#EEEEEE] py-4">
                    <div class="flex justify-between text-[#666666]">
                        <span>Subtotal</span>
                        <span class="font-medium text-[#1A1A1A]" x-text="summary.subtotal_formatted">0.00 Birr</span>
                    </div>
                    <div x-show="summary.discount_amount > 0" class="flex justify-between text-emerald-700 font-medium">
                        <span>Discount (<span x-text="summary.discount_code"></span>)</span>
                        <span x-text="'-' + summary.discount_amount_formatted"></span>
                    </div>
                    <div class="flex justify-between text-[#666666]">
                        <span>Estimated Shipping</span>
                        <span class="font-medium text-[#1A1A1A]">Complimentary</span>
                    </div>
                    <div class="flex justify-between text-[13px] font-bold text-[#1A1A1A] pt-3 border-t border-[#EEEEEE]">
                        <span>Total</span>
                        <span x-text="summary.total_formatted">0.00 Birr</span>
                    </div>
                </div>

                <a href="/checkout" class="block w-full bg-[#1A1A1A] text-white text-center font-semibold text-[13px] uppercase tracking-[0.12em] py-4 hover:bg-[#333333] transition-colors rounded-none">
                    Proceed to Checkout
                </a>
            </div>
        </div>
    </div>
</div>

<script>
function fullCartPage() {
    return {
        summary: @json($summary),
        couponCode: '',
        couponMessage: '',
        updateQty(itemId, newQty) {
            fetch('/cart/update/' + itemId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ quantity: newQty })
            })
            .then(res => res.json())
            .then(data => { if (data.summary) this.summary = data.summary; });
        },
        removeItem(itemId) {
            fetch('/cart/remove/' + itemId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(res => res.json())
            .then(data => { if (data.summary) this.summary = data.summary; });
        },
        applyCoupon() {
            if (!this.couponCode) return;
            fetch('/cart/discount', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ code: this.couponCode })
            })
            .then(res => res.json())
            .then(data => {
                if (data.summary) {
                    this.summary = data.summary;
                    this.couponMessage = data.message;
                }
            });
        }
    }
}
</script>
@endsection
