@if($products->count() > 0)
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-[2px] sm:gap-2 md:gap-4 lg:gap-6">
        @foreach($products as $product)
            @include('storefront.partials.product_card', ['product' => $product])
        @endforeach
    </div>
@else
    <div class="text-center py-16 bg-[#FAF8F5] border border-[#E6DFD5] p-8 rounded-3xl">
        <h3 class="text-base font-serif font-bold uppercase tracking-wider text-[#82203E] mb-2">No Products Match Your Filter</h3>
        <p class="text-xs text-stone-500 max-w-sm mx-auto mb-6">Try clearing some of your filter selections to view available catalog styles.</p>
    </div>
@endif
