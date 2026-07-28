@extends('storefront.layouts.app')

@section('title', ($currentCategory ? $currentCategory->name : 'All Clothing') . ' — LULU Couture')

@section('content')
<div x-data="catalogFilter()" class="max-w-[1400px] mx-auto px-4 sm:px-6 lg:px-8 py-8 sm:py-12">

    <!-- Breadcrumb & Header Area -->
    <div class="mb-8">
        <div class="flex justify-center items-center space-x-2 text-[10px] uppercase tracking-[0.1em] text-[#666666] mb-4">
            <a href="/" class="hover:text-[#1A1A1A]">HOME</a>
            <span>/</span>
            <span class="text-[#1A1A1A]">{{ $currentCategory ? $currentCategory->name : 'ALL CLOTHING' }}</span>
        </div>

        <h1 class="text-[24px] sm:text-[32px] font-serif font-normal text-[#1A1A1A] text-center mb-2">
            {{ $currentCategory ? $currentCategory->name : 'All Clothing' }}
        </h1>
        <div class="text-[11px] font-medium text-[#666666] uppercase tracking-[0.1em] text-center mt-2">
            {{ $products->total() ?? 0 }} PRODUCTS
        </div>
    </div>

    <!-- Category Tab Bar -->
    <div class="mb-8 overflow-x-auto no-scrollbar -mx-4 px-4 sm:mx-0 sm:px-0">
        <div class="flex justify-center items-center space-x-8 min-w-max pb-1 border-b border-[#EEEEEE]">
            <a href="/catalog" class="pb-3 {{ !$currentCategory ? 'border-b-[2px] border-[#1A1A1A] text-[#1A1A1A]' : 'border-b-[2px] border-transparent text-[#666666] hover:text-[#1A1A1A]' }} text-[10px] font-semibold uppercase tracking-[0.1em] transition-colors -mb-[1px]">
                All Styles
            </a>
            @foreach($categories as $cat)
                <a href="/category/{{ $cat->slug }}" class="pb-3 {{ ($currentCategory && ($currentCategory->id === $cat->id || $currentCategory->parent_id === $cat->id)) ? 'border-b-[2px] border-[#1A1A1A] text-[#1A1A1A]' : 'border-b-[2px] border-transparent text-[#666666] hover:text-[#1A1A1A]' }} text-[10px] font-semibold uppercase tracking-[0.1em] transition-colors -mb-[1px]">
                    {{ $cat->name }}
                </a>
            @endforeach
        </div>
    </div>

    <!-- Filter + Sort Bar -->
    <div class="flex items-center justify-between py-4 border-b border-[#EEEEEE] mb-8">
        <button type="button" @click="mobileFiltersOpen = true" class="flex items-center space-x-2 md:hidden text-[#1A1A1A]">
            <span class="text-[10px] font-semibold uppercase tracking-[0.1em]">Filter & Sort</span>
            <span x-show="activeFilterCount > 0" class="text-[10px] font-medium ml-1" x-text="'(' + activeFilterCount + ')'"></span>
        </button>
        <div class="hidden md:flex items-center text-[#1A1A1A]">
             <span class="text-[10px] font-semibold uppercase tracking-[0.1em]">Filter & Sort</span>
        </div>

        <div class="flex items-center relative">
            <select x-model="filters.sort" @change="applyFilters()" class="appearance-none bg-transparent text-[10px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A] focus:outline-none pr-6 cursor-pointer">
                <option value="newest">Newest</option>
                <option value="price_asc">Price: Low to High</option>
                <option value="price_desc">Price: High to Low</option>
            </select>
            <div class="pointer-events-none absolute right-0 flex items-center text-[#1A1A1A]">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 9l-7 7-7-7"/></svg>
            </div>
        </div>
    </div>

    <div class="flex flex-col md:flex-row gap-12 items-start">
        
        <!-- Desktop Sidebar Faceted Filters -->
        <aside class="hidden md:block w-[240px] flex-shrink-0 sticky top-24">
            <div class="flex items-center justify-between mb-6">
                <span class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A]">Filters</span>
                <button type="button" @click="resetFilters()" class="text-[10px] uppercase tracking-[0.1em] text-[#666666] underline hover:text-[#1A1A1A]">
                    Reset
                </button>
            </div>

            <!-- Size Facet -->
            @php
                $sizeAttr = $attributes->firstWhere('slug', 'size');
            @endphp
            @if($sizeAttr)
                <div class="border-t border-[#EEEEEE] py-6">
                    <h4 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A] mb-4">Size</h4>
                    <div class="flex flex-wrap gap-2">
                        @foreach($sizeAttr->values as $sVal)
                            <label class="cursor-pointer">
                                <input type="checkbox" value="{{ $sVal->value }}" x-model="filters.sizes" @change="applyFilters()" class="sr-only peer">
                                <span class="flex items-center justify-center min-w-[36px] h-[36px] px-2 text-[11px] font-medium border border-[#E5E5E5] peer-checked:bg-[#1A1A1A] peer-checked:text-white peer-checked:border-[#1A1A1A] hover:border-[#1A1A1A] transition-colors">
                                    {{ $sVal->value }}
                                </span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Colour Facet -->
            @php
                $colorAttr = $attributes->firstWhere('slug', 'colour');
            @endphp
            @if($colorAttr)
                <div class="border-t border-[#EEEEEE] py-6">
                    <h4 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A] mb-4">Color</h4>
                    <div class="flex flex-wrap gap-3">
                        @foreach($colorAttr->values as $cVal)
                            <label class="cursor-pointer" title="{{ $cVal->value }}">
                                <input type="checkbox" value="{{ $cVal->value }}" x-model="filters.colors" @change="applyFilters()" class="sr-only peer">
                                <span class="block w-6 h-6 rounded-full border border-[#E5E5E5] peer-checked:outline peer-checked:outline-2 peer-checked:outline-offset-2 peer-checked:outline-[#1A1A1A] hover:border-[#1A1A1A] transition-all" style="background-color: {{ $cVal->color_code ?? '#000000' }}"></span>
                            </label>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Price Range -->
            <div class="border-t border-[#EEEEEE] py-6">
                <h4 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A] mb-4">Price Range (Birr)</h4>
                <div class="flex items-center space-x-2">
                    <input type="number" placeholder="Min" x-model="filters.min_price" @change="applyFilters()" class="w-full px-2 py-2 border border-[#E5E5E5] text-[11px] text-[#1A1A1A] focus:outline-none focus:border-[#1A1A1A]">
                    <span class="text-[#666666] text-[11px]">-</span>
                    <input type="number" placeholder="Max" x-model="filters.max_price" @change="applyFilters()" class="w-full px-2 py-2 border border-[#E5E5E5] text-[11px] text-[#1A1A1A] focus:outline-none focus:border-[#1A1A1A]">
                </div>
            </div>

            <!-- In Stock Only -->
            <div class="border-t border-[#EEEEEE] py-6">
                <label class="flex items-center space-x-3 cursor-pointer">
                    <input type="checkbox" x-model="filters.in_stock" @change="applyFilters()" class="w-4 h-4 border-[#E5E5E5] text-[#1A1A1A] rounded-none focus:ring-0 focus:ring-offset-0 bg-transparent">
                    <span class="text-[11px] font-medium uppercase tracking-[0.1em] text-[#1A1A1A]">In Stock Only</span>
                </label>
            </div>
        </aside>

        <!-- Mobile Filter Slide-Over Drawer -->
        <div x-show="mobileFiltersOpen" x-cloak class="fixed inset-0 z-50 md:hidden">
            <div x-show="mobileFiltersOpen" x-transition.opacity @click="mobileFiltersOpen = false" class="absolute inset-0 bg-black bg-opacity-40"></div>
            <div x-show="mobileFiltersOpen"
                 x-transition:enter="transform transition ease-in-out duration-300"
                 x-transition:enter-start="translate-x-full"
                 x-transition:enter-end="translate-x-0"
                 x-transition:leave="transform transition ease-in-out duration-300"
                 x-transition:leave-start="translate-x-0"
                 x-transition:leave-end="translate-x-full"
                 class="absolute inset-y-0 right-0 w-[85%] max-w-md bg-white flex flex-col justify-between shadow-xl">
                
                <div class="flex items-center justify-between px-6 py-5 border-b border-[#EEEEEE]">
                    <h3 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A]">Filters</h3>
                    <button @click="mobileFiltersOpen = false" class="text-[#1A1A1A] hover:text-[#666666]">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>

                <div class="flex-1 overflow-y-auto px-6 py-4">
                    <div class="flex justify-end mb-4">
                        <button type="button" @click="resetFilters()" class="text-[10px] uppercase tracking-[0.1em] text-[#666666] underline hover:text-[#1A1A1A]">
                            Reset All
                        </button>
                    </div>

                    <!-- Mobile Size -->
                    @if($sizeAttr)
                        <div class="py-4">
                            <h4 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A] mb-4">Size</h4>
                            <div class="flex flex-wrap gap-2">
                                @foreach($sizeAttr->values as $sVal)
                                    <label class="cursor-pointer">
                                        <input type="checkbox" value="{{ $sVal->value }}" x-model="filters.sizes" class="sr-only peer">
                                        <span class="flex items-center justify-center min-w-[36px] h-[36px] px-2 text-[11px] font-medium border border-[#E5E5E5] peer-checked:bg-[#1A1A1A] peer-checked:text-white peer-checked:border-[#1A1A1A]">
                                            {{ $sVal->value }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Mobile Colours -->
                    @if($colorAttr)
                        <div class="border-t border-[#EEEEEE] py-6">
                            <h4 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A] mb-4">Color</h4>
                            <div class="flex flex-wrap gap-3">
                                @foreach($colorAttr->values as $cVal)
                                    <label class="cursor-pointer" title="{{ $cVal->value }}">
                                        <input type="checkbox" value="{{ $cVal->value }}" x-model="filters.colors" class="sr-only peer">
                                        <span class="block w-6 h-6 rounded-full border border-[#E5E5E5] peer-checked:outline peer-checked:outline-2 peer-checked:outline-offset-2 peer-checked:outline-[#1A1A1A]" style="background-color: {{ $cVal->color_code ?? '#000000' }}"></span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <!-- Mobile Price -->
                    <div class="border-t border-[#EEEEEE] py-6">
                        <h4 class="text-[12px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A] mb-4">Price Range</h4>
                        <div class="flex items-center space-x-2">
                            <input type="number" placeholder="Min" x-model="filters.min_price" class="w-full px-3 py-3 border border-[#E5E5E5] text-[11px] focus:outline-none focus:border-[#1A1A1A]">
                            <span class="text-[#666666]">-</span>
                            <input type="number" placeholder="Max" x-model="filters.max_price" class="w-full px-3 py-3 border border-[#E5E5E5] text-[11px] focus:outline-none focus:border-[#1A1A1A]">
                        </div>
                    </div>

                    <!-- Mobile Stock -->
                    <div class="border-t border-[#EEEEEE] py-6">
                        <label class="flex items-center space-x-3 cursor-pointer">
                            <input type="checkbox" x-model="filters.in_stock" class="w-4 h-4 border-[#E5E5E5] text-[#1A1A1A] rounded-none focus:ring-0 focus:ring-offset-0 bg-transparent">
                            <span class="text-[11px] font-medium uppercase tracking-[0.1em] text-[#1A1A1A]">In Stock Only</span>
                        </label>
                    </div>
                </div>

                <div class="p-0 border-t border-[#EEEEEE]">
                    <button @click="applyFilters(); mobileFiltersOpen = false;" class="w-full h-[50px] bg-[#1A1A1A] text-white text-[11px] font-semibold uppercase tracking-[0.1em] flex items-center justify-center rounded-none">
                        Apply Filters <span x-show="activeFilterCount > 0" class="ml-1" x-text="'(' + activeFilterCount + ')'"></span>
                    </button>
                </div>
            </div>
        </div>

        <!-- Main Product Grid Area -->
        <div class="flex-1 w-full min-h-[500px]">
            <!-- Loading State -->
            <div x-show="loading" class="flex items-center justify-center py-20">
                <div class="w-6 h-6 border-2 border-[#1A1A1A] border-t-transparent rounded-full animate-spin"></div>
            </div>

            <!-- Dynamic Product Grid -->
            <div x-show="!loading" id="product-grid-container" class="transition-opacity duration-300">
                @include('storefront.partials.product_grid', ['products' => $products])
            </div>

            <!-- Load More Button -->
            <div x-show="hasMore && !loading" class="mt-16 text-center">
                <button @click="loadMore()" class="text-[11px] font-semibold uppercase tracking-[0.1em] text-[#1A1A1A] underline underline-offset-4 hover:text-[#666666] transition-colors">
                    Load More
                </button>
            </div>
        </div>

    </div>
</div>

<script>
function catalogFilter() {
    return {
        mobileFiltersOpen: false,
        loading: false,
        hasMore: {{ $products->hasMorePages() ? 'true' : 'false' }},
        page: {{ $products->currentPage() }},
        filters: {
            q: '{{ request('q', '') }}',
            sort: '{{ request('sort', 'newest') }}',
            sizes: @json(request('sizes') ? explode(',', request('sizes')) : []),
            colors: @json(request('colors') ? explode(',', request('colors')) : []),
            min_price: '{{ request('min_price', '') }}',
            max_price: '{{ request('max_price', '') }}',
            in_stock: {{ request('in_stock') ? 'true' : 'false' }}
        },
        get activeFilterCount() {
            let count = 0;
            if (this.filters.q) count++;
            if (this.filters.sizes.length) count += this.filters.sizes.length;
            if (this.filters.colors.length) count += this.filters.colors.length;
            if (this.filters.min_price) count++;
            if (this.filters.max_price) count++;
            if (this.filters.in_stock) count++;
            return count;
        },
        applyFilters() {
            this.loading = true;
            this.page = 1;
            const params = new URLSearchParams();
            if (this.filters.q) params.set('q', this.filters.q);
            if (this.filters.sort) params.set('sort', this.filters.sort);
            if (this.filters.sizes.length) params.set('sizes', this.filters.sizes.join(','));
            if (this.filters.colors.length) params.set('colors', this.filters.colors.join(','));
            if (this.filters.min_price) params.set('min_price', this.filters.min_price);
            if (this.filters.max_price) params.set('max_price', this.filters.max_price);
            if (this.filters.in_stock) params.set('in_stock', '1');

            const newUrl = window.location.pathname + '?' + params.toString();
            window.history.pushState({}, '', newUrl);

            fetch(newUrl, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                document.getElementById('product-grid-container').innerHTML = data.html;
                this.hasMore = data.has_more;
                this.loading = false;
            })
            .catch(() => { this.loading = false; });
        },
        loadMore() {
            this.page++;
            const params = new URLSearchParams(window.location.search);
            params.set('page', this.page);

            fetch(window.location.pathname + '?' + params.toString(), {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(res => res.json())
            .then(data => {
                const tempDiv = document.createElement('div');
                tempDiv.innerHTML = data.html;
                const newItems = tempDiv.querySelector('.grid');
                if (newItems) {
                    document.querySelector('#product-grid-container .grid').innerHTML += newItems.innerHTML;
                }
                this.hasMore = data.has_more;
            });
        },
        resetFilters() {
            this.filters.sizes = [];
            this.filters.colors = [];
            this.filters.min_price = '';
            this.filters.max_price = '';
            this.filters.in_stock = false;
            this.applyFilters();
        }
    }
}
</script>
@endsection
