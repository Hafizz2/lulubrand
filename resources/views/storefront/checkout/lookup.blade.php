@extends('storefront.layouts.app')

@section('title', 'Track Your Order — LULU Couture')

@section('content')
<div class="min-h-[70vh] flex items-center justify-center py-12 px-4">
    <div class="max-w-md w-full bg-white p-8 border border-stone-200 shadow-sm">
        <div class="text-center mb-8">
            <h2 class="text-2xl font-black uppercase tracking-widest text-stone-900">Track Order</h2>
            <p class="text-xs text-stone-500 uppercase tracking-widest mt-1">Lookup order status without logging in</p>
        </div>

        @if($errors->has('lookup'))
            <div class="mb-6 p-3 bg-rose-50 text-rose-800 text-xs font-semibold border border-rose-200">
                {{ $errors->first('lookup') }}
            </div>
        @endif

        <form method="POST" action="{{ route('order.lookup') }}" class="space-y-6">
            @csrf
            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-2">Order Number</label>
                <input type="text" name="order_number" value="{{ old('order_number') }}" required placeholder="e.g. LULU-ABC123" class="w-full px-4 py-3 bg-stone-50 border border-stone-300 text-sm focus:outline-none focus:border-stone-900 uppercase">
            </div>

            <div>
                <label class="block text-xs font-semibold uppercase tracking-wider text-stone-700 mb-2">Phone Number</label>
                <input type="text" name="phone" value="{{ old('phone') }}" required placeholder="+251..." class="w-full px-4 py-3 bg-stone-50 border border-stone-300 text-sm focus:outline-none focus:border-stone-900">
            </div>

            <button type="submit" class="w-full py-4 bg-stone-900 text-white font-bold text-xs uppercase tracking-widest hover:bg-stone-800 transition-colors shadow-md">
                Find Order Status
            </button>
        </form>
    </div>
</div>
@endsection
