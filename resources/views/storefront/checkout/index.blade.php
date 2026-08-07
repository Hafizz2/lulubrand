@extends('storefront.layouts.app')

@section('title', 'Secure Checkout — LULU Couture')

@section('content')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<div x-data="checkoutWizard()" x-init="initWizard()" class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-16 space-y-12 bg-[#F9F6F0]">

    <!-- Clean Editorial Header -->
    <div class="text-center space-y-3 mb-8">
        <h1 class="text-3xl sm:text-5xl font-serif font-bold uppercase tracking-widest text-[#221F1F]">
            Checkout
        </h1>
        <div class="inline-flex items-center space-x-2 text-[10px] font-bold uppercase tracking-widest text-[#8C6554]">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
            <span>Secure Checkout • Session Expires In <span x-text="timerDisplay" class="font-mono">20:00</span></span>
        </div>
    </div>

    <!-- Form Validation Errors -->
    @if($errors->any())
        <div class="max-w-3xl mx-auto p-4 bg-white border border-[#C49A9A] text-[#221F1F] text-[11px] font-bold uppercase tracking-widest text-center mb-8">
            <ul class="space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data" id="checkoutForm" @submit.prevent="placeOrder($el)" class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
        <!-- Processing overlay -->
        <div x-show="isSubmitting" x-cloak class="fixed inset-0 bg-white/70 backdrop-blur-xs flex flex-col items-center justify-center z-50 transition-all duration-300">
            <div class="animate-spin rounded-full h-12 w-12 border-4 border-[#8C6554] border-t-transparent mb-4"></div>
            <p class="text-xs font-bold uppercase tracking-widest text-[#221F1F]">Placing your order, please wait...</p>
        </div>

        @csrf
        <input type="hidden" name="customer_country" :value="form.customer_country">
        <input type="hidden" name="customer_city" :value="form.customer_city">
        <input type="hidden" name="customer_district" :value="form.customer_district">

        <!-- Left Column: Checkout Steps (lg:col-span-7) -->
        <div class="lg:col-span-7 space-y-10">

            <!-- Step 1: Contact -->
            <div class="bg-white p-8 sm:p-10 shadow-xl border border-[#E6DFD5] relative" :class="currentStep !== 0 ? 'opacity-50 pointer-events-none' : ''">
                <div class="absolute -left-4 top-10 bg-[#221F1F] text-white w-8 h-8 flex items-center justify-center font-bold text-xs rounded-sm shadow-md">1</div>
                
                <h2 class="text-xl font-serif font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-8 border-b border-[#E6DFD5] pb-4">
                    {{ __('profile_personal_details') }}
                </h2>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">{{ __('profile_full_name') }} *</label>
                        <input type="text" name="customer_name" x-model="form.customer_name" @input="errors.customer_name = false" required 
                               :class="errors.customer_name ? 'border-[#C49A9A] ring-2 ring-[#C49A9A]/30 bg-rose-50/50' : 'border-transparent focus:border-[#8C6554]'"
                               class="w-full px-4 py-3 bg-[#F3EEE8] border text-sm text-[#221F1F] focus:outline-none transition-all rounded-sm shadow-inner">
                        <p x-show="errors.customer_name" class="text-[10px] font-bold uppercase tracking-widest text-[#C49A9A] mt-1">Full name is required.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">{{ __('profile_phone') }} *</label>
                            <input type="tel" name="customer_phone" x-model="form.customer_phone" @input="errors.customer_phone = false" required 
                                   :class="errors.customer_phone ? 'border-[#C49A9A] ring-2 ring-[#C49A9A]/30 bg-rose-50/50' : 'border-transparent focus:border-[#8C6554]'"
                                   class="w-full px-4 py-3 bg-[#F3EEE8] border text-sm text-[#221F1F] focus:outline-none transition-all rounded-sm shadow-inner">
                            <p x-show="errors.customer_phone" class="text-[10px] font-bold uppercase tracking-widest text-[#C49A9A] mt-1">Phone number is required.</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">{{ __('profile_email') }}</label>
                            <input type="email" name="customer_email" x-model="form.customer_email" class="w-full px-4 py-3 bg-[#F3EEE8] border border-transparent focus:border-[#8C6554] text-sm text-[#221F1F] focus:outline-none transition-colors rounded-sm shadow-inner">
                        </div>
                    </div>

                    <div class="flex justify-end pt-6" x-show="currentStep === 0">
                        <button type="button" @click="nextStep()" class="bg-[#221F1F] hover:bg-[#8C6554] text-white font-bold text-[11px] uppercase tracking-[0.2em] px-10 py-4 rounded-full shadow-lg transition-all">
                            {{ __('btn_continue') }}
                        </button>
                    </div>
                </div>
                
                <!-- Edit Button (Shows when past this step) -->
                <div x-show="currentStep > 0" class="absolute top-10 right-8">
                    <button type="button" @click="goToStep(0)" class="text-[10px] font-bold uppercase tracking-widest text-[#8C6554] hover:text-[#221F1F] transition-colors pointer-events-auto">
                        Edit
                    </button>
                </div>
            </div>

            <!-- Step 2: Delivery -->
            <div class="bg-white p-8 sm:p-10 shadow-xl border border-[#E6DFD5] relative" :class="currentStep !== 1 ? 'opacity-50 pointer-events-none' : ''">
                <div class="absolute -left-4 top-10 bg-[#221F1F] text-white w-8 h-8 flex items-center justify-center font-bold text-xs rounded-sm shadow-md">2</div>
                
                <h2 class="text-xl font-serif font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-8 border-b border-[#E6DFD5] pb-4">
                    Delivery Options
                </h2>

                <div class="space-y-8">
                    <!-- Date/Time -->
                    @if(($settings['schedule_enabled'] ?? '0') === '1')
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">Preferred Date *</label>
                                <input type="text" name="preferred_date" id="datePicker" x-model="form.preferred_date" required 
                                       :class="errors.preferred_date ? 'border-[#C49A9A] ring-2 ring-[#C49A9A]/30 bg-rose-50/50' : 'border-transparent focus:border-[#8C6554]'"
                                       class="w-full px-4 py-3 bg-[#F3EEE8] border text-sm text-[#221F1F] focus:outline-none transition-all rounded-sm shadow-inner">
                            </div>
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">Preferred Time *</label>
                                <select name="preferred_time" x-model="form.preferred_time" required
                                        :class="errors.preferred_time ? 'border-[#C49A9A] ring-2 ring-[#C49A9A]/30 bg-rose-50/50' : 'border-transparent focus:border-[#8C6554]'"
                                        class="w-full px-4 py-3 bg-[#F3EEE8] border text-sm text-[#221F1F] focus:outline-none transition-all rounded-sm shadow-inner">
                                    <option value="">Select Time</option>
                                    @foreach($pickupTimes as $slot)
                                        <option value="{{ $slot->time_label }}" data-id="{{ $slot->id }}">
                                            {{ $slot->time_label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    @endif

                    <!-- Logistics -->
                    <div class="space-y-4">
                        <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F]">Method *</label>
                        <div class="grid grid-cols-1 gap-4">
                            @if(($settings['logistics_pickup'] ?? '1') === '1')
                                <label :class="form.logistics_mode === 'pickup' ? 'border-[#8C6554] bg-[#F9F6F0]' : 'border-[#E6DFD5] bg-white'" class="p-4 border rounded-sm cursor-pointer flex items-center justify-between transition-all">
                                    <div class="flex items-center space-x-3">
                                        <input type="radio" name="logistics_mode" value="pickup" x-model="form.logistics_mode" class="text-[#8C6554] focus:ring-[#8C6554]">
                                        <span class="font-bold text-xs uppercase tracking-wider text-[#221F1F]">Store Pickup (Free)</span>
                                    </div>
                                </label>
                            @endif

                            @if(($settings['logistics_delivery_fixed'] ?? '1') === '1')
                                <label :class="form.logistics_mode === 'delivery_fixed' ? 'border-[#8C6554] bg-[#F9F6F0]' : 'border-[#E6DFD5] bg-white'" class="p-4 border rounded-sm cursor-pointer flex items-center justify-between transition-all">
                                    <div class="flex items-center space-x-3">
                                        <input type="radio" name="logistics_mode" value="delivery_fixed" x-model="form.logistics_mode" class="text-[#8C6554] focus:ring-[#8C6554]">
                                        <span class="font-bold text-xs uppercase tracking-wider text-[#221F1F]">Delivery (Courier / Shipping)</span>
                                    </div>
                                </label>
                            @endif

                            @if(($settings['logistics_delivery_rider'] ?? '1') === '1')
                                <label :class="form.logistics_mode === 'delivery_rider' ? 'border-[#8C6554] bg-[#F9F6F0]' : 'border-[#E6DFD5] bg-white'" class="p-4 border rounded-sm cursor-pointer flex items-center justify-between transition-all">
                                    <div class="flex items-center space-x-3">
                                        <input type="radio" name="logistics_mode" value="delivery_rider" x-model="form.logistics_mode" class="text-[#8C6554] focus:ring-[#8C6554]">
                                        <span class="font-bold text-xs uppercase tracking-wider text-[#221F1F]">Express Courier (Paid to Rider)</span>
                                    </div>
                                </label>
                            @endif
                        </div>
                    </div>

                    <!-- Address for Delivery Fixed (Dynamic Country / City / District Selection) -->
                    <div x-show="form.logistics_mode === 'delivery_fixed'" class="space-y-6 pt-4 border-t border-[#E6DFD5]">
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">Country *</label>
                                <select x-model="selectedCountryDropdown" @change="onCountryChange()" :required="form.logistics_mode === 'delivery_fixed'"
                                        class="w-full px-4 py-3 bg-[#F3EEE8] border border-transparent focus:border-[#8C6554] text-sm text-[#221F1F] focus:outline-none transition-colors rounded-sm shadow-inner">
                                    <option value="Ethiopia">Ethiopia</option>
                                    <template x-for="c in countries" :key="c">
                                        <option :value="c" x-text="c"></option>
                                    </template>
                                    <option value="Other">Other (Enter custom name...)</option>
                                </select>
                            </div>

                            <div x-show="selectedCountryDropdown === 'Other'">
                                <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">Custom Country Name *</label>
                                <input type="text" x-model="form.customer_country" :required="form.logistics_mode === 'delivery_fixed' && selectedCountryDropdown === 'Other'"
                                       placeholder="e.g. Italy, Australia"
                                       class="w-full px-4 py-3 bg-[#F3EEE8] border border-transparent focus:border-[#8C6554] text-sm text-[#221F1F] focus:outline-none transition-colors rounded-sm shadow-inner">
                            </div>

                            <!-- If Ethiopia, show city dropdown -->
                            <div x-show="form.customer_country === 'Ethiopia'">
                                <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">City *</label>
                                <select x-model="form.customer_city" @change="onCityChange()" :required="form.logistics_mode === 'delivery_fixed' && form.customer_country === 'Ethiopia'"
                                        class="w-full px-4 py-3 bg-[#F3EEE8] border border-transparent focus:border-[#8C6554] text-sm text-[#221F1F] focus:outline-none transition-colors rounded-sm shadow-inner">
                                    <option value="">Select City</option>
                                    <template x-for="c in cities" :key="c">
                                        <option :value="c" x-text="getCityOptionLabel(c)"></option>
                                    </template>
                                </select>
                            </div>

                            <!-- If International, show city text input -->
                            <div x-show="form.customer_country !== 'Ethiopia'">
                                <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">City / Region (Optional)</label>
                                <input type="text" x-model="form.customer_city"
                                       placeholder="e.g. London, New York"
                                       class="w-full px-4 py-3 bg-[#F3EEE8] border border-transparent focus:border-[#8C6554] text-sm text-[#221F1F] focus:outline-none transition-colors rounded-sm shadow-inner">
                            </div>
                        </div>

                        <!-- If Addis Ababa, show district dropdown -->
                        <div x-show="form.customer_country === 'Ethiopia' && form.customer_city === 'Addis Ababa'">
                            <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">District / Area *</label>
                            <select x-model="form.customer_district" @change="onDistrictChange()" :required="form.logistics_mode === 'delivery_fixed' && form.customer_country === 'Ethiopia' && form.customer_city === 'Addis Ababa'"
                                    class="w-full px-4 py-3 bg-[#F3EEE8] border border-transparent focus:border-[#8C6554] text-sm text-[#221F1F] focus:outline-none transition-colors rounded-sm shadow-inner">
                                <option value="">Select District</option>
                                <template x-for="d in districts" :key="d">
                                    <option :value="d" x-text="getDistrictOptionLabel(d)"></option>
                                </template>
                            </select>
                        </div>

                        <!-- Street Address -->
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">Street Address & Delivery Instructions *</label>
                            <textarea name="customer_address" x-model="form.customer_address" @input="errors.customer_address = false" :required="form.logistics_mode === 'delivery_fixed'" rows="2" 
                                      placeholder="e.g. House No. 123, behind Edna Mall"
                                      :class="errors.customer_address ? 'border-[#C49A9A] ring-2 ring-[#C49A9A]/30 bg-rose-50/50' : 'border-transparent focus:border-[#8C6554]'"
                                      class="w-full px-4 py-3 bg-[#F3EEE8] border text-sm text-[#221F1F] focus:outline-none transition-all rounded-sm shadow-inner"></textarea>
                            <p x-show="errors.customer_address" class="text-[10px] font-bold uppercase tracking-widest text-[#C49A9A] mt-1">Delivery address is required.</p>
                        </div>

                        <!-- Info box for International Shipping -->
                        <div x-show="form.customer_country !== 'Ethiopia' && form.customer_country !== ''" class="p-4 bg-[#F9F6F0] border-l-2 border-[#8C6554] space-y-2">
                            <p class="text-[11px] font-bold uppercase tracking-wider text-[#8C6554] flex items-center gap-2">
                                <svg class="w-4 h-4 text-[#8C6554]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 002 2h1.5m1.832-3.752a9 9 0 11-12.013-4.723" />
                                </svg>
                                International Shipping Notice
                            </p>
                            <p class="text-[11px] text-stone-600 normal-case leading-relaxed">
                                Since you are shipping outside of Ethiopia, our team will coordinate express international courier delivery. 
                                **Your shipping cost is calculated at 0.00 Birr initially**, and our staff will contact you shortly via email/phone after order placement to quote the exact shipping cost and complete coordination.
                            </p>
                        </div>
                    </div>

                    <!-- Address for Express Rider (Simple Text Input ONLY) -->
                    <div x-show="form.logistics_mode === 'delivery_rider'" class="space-y-6 pt-4 border-t border-[#E6DFD5]">
                        @if(!empty($settings['rider_disclaimer']))
                            <div class="p-4 bg-[#FAF8F5] border-l-2 border-[#8C6554] space-y-1">
                                <p class="text-[11px] font-bold uppercase tracking-wider text-[#8C6554] flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5 text-[#8C6554]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                                    </svg>
                                    Delivery Info / Instructions
                                </p>
                                <p class="text-[11px] text-stone-600 normal-case leading-relaxed">
                                    {{ $settings['rider_disclaimer'] }}
                                </p>
                            </div>
                        @endif
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">Delivery Address & Rider Instructions *</label>
                            <textarea name="customer_address_rider" x-model="form.customer_address" @input="errors.customer_address = false" :required="form.logistics_mode === 'delivery_rider'" rows="3" 
                                      placeholder="Enter your exact city, district, neighborhood, building, house number, or directions for the rider..."
                                      :class="errors.customer_address ? 'border-[#C49A9A] ring-2 ring-[#C49A9A]/30 bg-rose-50/50' : 'border-transparent focus:border-[#8C6554]'"
                                      class="w-full px-4 py-3 bg-[#F3EEE8] border text-sm text-[#221F1F] focus:outline-none transition-all rounded-sm shadow-inner"></textarea>
                            <p x-show="errors.customer_address" class="text-[10px] font-bold uppercase tracking-widest text-[#C49A9A] mt-1">Delivery address is required.</p>
                        </div>
                    </div>

                    <div class="flex justify-end pt-6" x-show="currentStep === 1">
                        <button type="button" @click="nextStep()" class="bg-[#221F1F] hover:bg-[#8C6554] text-white font-bold text-[11px] uppercase tracking-[0.2em] px-10 py-4 rounded-full shadow-lg transition-all pointer-events-auto">
                            Continue
                        </button>
                    </div>
                </div>

                <div x-show="currentStep > 1" class="absolute top-10 right-8">
                    <button type="button" @click="goToStep(1)" class="text-[10px] font-bold uppercase tracking-widest text-[#8C6554] hover:text-[#221F1F] transition-colors pointer-events-auto">
                        Edit
                    </button>
                </div>
            </div>

            <!-- Step 3: Payment -->
            <div class="bg-white p-8 sm:p-10 shadow-xl border border-[#E6DFD5] relative" :class="currentStep !== 2 ? 'opacity-50 pointer-events-none' : ''">
                <div class="absolute -left-4 top-10 bg-[#221F1F] text-white w-8 h-8 flex items-center justify-center font-bold text-xs rounded-sm shadow-md">3</div>
                
                <h2 class="text-xl font-serif font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-8 border-b border-[#E6DFD5] pb-4">
                    Payment
                </h2>

                <div class="space-y-8">
                    <div class="grid grid-cols-1 gap-4">
                        @if(($settings['payment_cod'] ?? '1') === '1')
                            <label :class="form.payment_method === 'cod' ? 'border-[#8C6554] bg-[#F9F6F0]' : 'border-[#E6DFD5] bg-white'" class="p-4 border rounded-sm cursor-pointer flex items-center justify-between transition-all">
                                <div class="flex items-center space-x-3">
                                    <input type="radio" name="payment_method" value="cod" x-model="form.payment_method" @change="form.selected_bank_id = ''" class="text-[#8C6554] focus:ring-[#8C6554]">
                                    <span class="font-bold text-xs uppercase tracking-wider text-[#221F1F]">Cash on Delivery</span>
                                </div>
                            </label>
                        @endif

                        @if(($settings['payment_transfer'] ?? '1') === '1')
                            <label :class="form.payment_method === 'transfer' ? 'border-[#8C6554] bg-[#F9F6F0]' : 'border-[#E6DFD5] bg-white'" class="p-4 border rounded-sm cursor-pointer flex items-center justify-between transition-all">
                                <div class="flex items-center space-x-3">
                                    <input type="radio" name="payment_method" value="transfer" x-model="form.payment_method" @change="if(!form.selected_bank_id) form.selected_bank_id = '{{ $bankAccounts->first()?->id }}'" class="text-[#8C6554] focus:ring-[#8C6554]">
                                    <span class="font-bold text-xs uppercase tracking-wider text-[#221F1F]">Local Bank / Mobile Transfer</span>
                                </div>
                            </label>
                        @endif

                        <!-- PayPal (International) -->
                        @if(($settings['payment_paypal'] ?? '1') === '1')
                            <label :class="form.payment_method === 'paypal' ? 'border-[#8C6554] bg-[#F9F6F0]' : 'border-[#E6DFD5] bg-white'" class="p-4 border rounded-sm cursor-pointer flex items-center justify-between transition-all">
                                <div class="flex items-center space-x-3">
                                    <input type="radio" name="payment_method" value="paypal" x-model="form.payment_method" @change="form.selected_bank_id = ''" class="text-[#8C6554] focus:ring-[#8C6554]">
                                    <span class="font-bold text-xs uppercase tracking-wider text-[#221F1F] flex items-center gap-1.5">
                                        PayPal <span class="text-[9px] font-semibold text-stone-500 lowercase">(international)</span>
                                    </span>
                                </div>
                                <img src="https://upload.wikimedia.org/wikipedia/commons/b/b5/PayPal.svg" alt="PayPal" class="h-4">
                            </label>
                        @endif

                        <!-- Credit / Debit Card -->
                        @if(($settings['payment_card'] ?? '1') === '1')
                            <label :class="form.payment_method === 'card' ? 'border-[#8C6554] bg-[#F9F6F0]' : 'border-[#E6DFD5] bg-white'" class="p-4 border rounded-sm cursor-pointer flex items-center justify-between transition-all">
                                <div class="flex items-center space-x-3">
                                    <input type="radio" name="payment_method" value="card" x-model="form.payment_method" @change="form.selected_bank_id = ''" class="text-[#8C6554] focus:ring-[#8C6554]">
                                    <span class="font-bold text-xs uppercase tracking-wider text-[#221F1F] flex items-center gap-1.5">
                                        Credit / Debit Card <span class="text-[9px] font-semibold text-stone-500 lowercase">(international)</span>
                                    </span>
                                </div>
                                <div class="flex gap-1.5 items-center">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/4/41/Visa_Logo.png" alt="Visa" class="h-3 w-auto object-contain">
                                    <img src="https://upload.wikimedia.org/wikipedia/commons/a/a4/Mastercard_2019_logo.svg" alt="Mastercard" class="h-3 w-auto object-contain">
                                </div>
                            </label>
                        @endif
                    </div>

                    <!-- Transfer Options -->
                    <div x-show="form.payment_method === 'transfer'" class="space-y-6 pt-4 border-t border-[#E6DFD5]">
                        <div class="grid grid-cols-1 gap-4">
                            @foreach($bankAccounts as $bank)
                                <label :class="form.selected_bank_id == {{ $bank->id }} ? 'border-[#8C6554] bg-[#F9F6F0]' : 'border-[#E6DFD5] bg-white'" class="p-4 border rounded-sm cursor-pointer block transition-all">
                                    <input type="radio" name="selected_bank_id" value="{{ $bank->id }}" x-model="form.selected_bank_id" class="sr-only">
                                    <div class="flex items-center justify-between">
                                        <div>
                                            <span class="font-bold text-xs uppercase tracking-wider text-[#221F1F] block">{{ $bank->bank_name }}</span>
                                            <span class="text-[10px] text-stone-500 font-semibold uppercase">{{ $bank->account_name }}</span>
                                        </div>
                                        <div class="text-right">
                                            <span class="text-xs font-mono font-bold text-[#8C6554] block">{{ $bank->account_number }}</span>
                                        </div>
                                    </div>
                                </label>
                            @endforeach
                        </div>

                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">Upload Transfer Receipt *</label>
                            <div :class="errors.payment_proof ? 'border-[#C49A9A] bg-rose-50/50 ring-2 ring-[#C49A9A]/30' : 'border-[#E6DFD5] bg-[#F3EEE8]'"
                                 class="border p-6 text-center cursor-pointer hover:border-[#8C6554] transition-all relative rounded-sm">
                                <input type="file" name="payment_proof" id="paymentProofInput" accept="image/*" @change="previewProof($event); errors.payment_proof = false" class="absolute inset-0 opacity-0 cursor-pointer z-10 w-full h-full">
                                <template x-if="!proofPreviewUrl">
                                    <span class="text-[11px] font-bold uppercase tracking-widest text-[#8C6554]">Tap to Upload Screenshot</span>
                                </template>
                                <template x-if="proofPreviewUrl">
                                    <div class="flex justify-center">
                                        <img :src="proofPreviewUrl" class="h-20 object-contain shadow-sm">
                                    </div>
                                </template>
                            </div>
                            <p x-show="errors.payment_proof" class="text-[10px] font-bold uppercase tracking-widest text-[#C49A9A] mt-1">Payment proof screenshot is required for bank transfers.</p>
                        </div>
                    </div>

                    <!-- PayPal Instructions -->
                    <div x-show="form.payment_method === 'paypal'" class="p-6 border border-[#E6DFD5] bg-[#FAF8F5] text-center space-y-3 pt-4 rounded-sm">
                        <div class="inline-flex items-center justify-center w-12 h-12 rounded-full bg-[#0070ba]/10 text-[#0070ba] mb-1">
                            <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M7.076 21.337H2.47a.641.641 0 0 1-.633-.74L4.944 3.328a.962.962 0 0 1 .951-.817h7.814c3.483 0 5.669 1.637 6.136 4.708.57 3.738-1.583 6.079-5.177 6.079h-1.921c-.482 0-.895.341-.98.815l-1.636 9.255a.643.643 0 0 1-.633.57z"/></svg>
                        </div>
                        <p class="text-xs font-bold uppercase tracking-wider text-[#0070ba]">Pay securely with PayPal</p>
                        <p class="text-[11px] text-stone-500 normal-case max-w-sm mx-auto leading-relaxed">
                            {{ $settings['paypal_instructions'] ?? 'Click \'Place Order\' below to proceed. You will be redirected to the secure PayPal portal to authorize payment.' }}
                        </p>
                    </div>

                    <!-- Credit / Debit Card Form -->
                    <div x-show="form.payment_method === 'card'" class="p-6 border border-[#E6DFD5] bg-[#FAF8F5] space-y-4 rounded-sm">
                        <p class="text-xs font-bold uppercase tracking-wider text-[#221F1F] border-b border-[#E6DFD5] pb-2">Credit / Debit Card Information</p>
                        <p class="text-[11px] text-stone-500 normal-case leading-relaxed">
                            {{ $settings['card_instructions'] ?? 'Fill in your credit or debit card details below. All transaction data is securely processed.' }}
                        </p>
                        <div class="space-y-4 pt-2">
                            <div>
                                <label class="block text-[9px] font-bold uppercase text-stone-500 mb-1">Cardholder Name</label>
                                <input type="text" placeholder="e.g. Sofia Abera" class="w-full px-3 py-2 bg-white border border-[#E6DFD5] text-xs focus:outline-none focus:border-[#8C6554] rounded-sm">
                            </div>
                            <div>
                                <label class="block text-[9px] font-bold uppercase text-stone-500 mb-1">Card Number</label>
                                <div class="relative">
                                    <input type="text" placeholder="•••• •••• •••• ••••" maxlength="19" class="w-full px-3 py-2 bg-white border border-[#E6DFD5] text-xs focus:outline-none focus:border-[#8C6554] rounded-sm font-mono tracking-widest">
                                </div>
                            </div>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-[9px] font-bold uppercase text-stone-500 mb-1">Expiration Date</label>
                                    <input type="text" placeholder="MM / YY" maxlength="5" class="w-full px-3 py-2 bg-white border border-[#E6DFD5] text-xs focus:outline-none focus:border-[#8C6554] rounded-sm font-mono text-center">
                                </div>
                                <div>
                                    <label class="block text-[9px] font-bold uppercase text-stone-500 mb-1">CVC / CVV</label>
                                    <input type="password" placeholder="•••" maxlength="3" class="w-full px-3 py-2 bg-white border border-[#E6DFD5] text-xs focus:outline-none focus:border-[#8C6554] rounded-sm font-mono text-center">
                                </div>
                            </div>
                        </div>
                    </div>
                    </div>


                </div>

        </div>

        <!-- Right Column: Order Summary (lg:col-span-5) -->
        <div class="lg:col-span-5 sticky top-10">
            <div class="bg-white p-8 shadow-2xl border border-[#E6DFD5] space-y-8">
                <h3 class="text-xl font-serif font-bold uppercase tracking-[0.2em] text-[#221F1F] border-b border-[#E6DFD5] pb-4">
                    Your Order
                </h3>

                <!-- Cart Items -->
                <div class="space-y-6 max-h-[40vh] overflow-y-auto pr-2">
                    @foreach($summary['items'] as $item)
                        <div class="flex items-center space-x-4">
                            <img src="{{ $item['image_url'] }}" alt="{{ $item['title'] }}" class="w-16 h-20 object-cover bg-[#F3EEE8]">
                            <div class="flex-1 min-w-0">
                                <span class="font-bold text-[11px] uppercase tracking-wider text-[#221F1F] block truncate">{{ $item['title'] }}</span>
                                <span class="text-[10px] text-[#A38B7E] uppercase tracking-widest block mt-1">{{ $item['attributes'] }}</span>
                                <span class="text-[10px] font-bold text-[#221F1F] block mt-1">QTY: {{ $item['quantity'] }}</span>
                            </div>
                            <span class="text-[11px] font-mono font-bold text-[#8C6554]">{{ $item['total_price_formatted'] }}</span>
                        </div>
                    @endforeach
                </div>

                <!-- Coupon Discount Section -->
                <div class="border-t border-[#E6DFD5] pt-6 space-y-3">
                    <label class="block text-[10px] font-bold uppercase tracking-widest text-[#221F1F]">Have a Coupon Code?</label>
                    <div class="flex space-x-2">
                        <input type="text" x-model="couponInput" placeholder="ENTER COUPON CODE" class="flex-1 px-3 py-2.5 bg-[#F3EEE8] border border-[#E6DFD5] text-[11px] font-mono focus:outline-none focus:border-[#8C6554] uppercase tracking-wider text-[#221F1F] rounded-sm">
                        <button type="button" @click="applyCouponCode()" :disabled="couponApplying" class="bg-[#221F1F] hover:bg-[#8C6554] text-white text-[10px] font-bold px-4 py-2.5 uppercase tracking-widest transition-colors rounded-sm flex-shrink-0">
                            <span x-show="!couponApplying">Apply</span>
                            <span x-show="couponApplying" x-cloak>...</span>
                        </button>
                    </div>
                    <p x-show="couponMessage" class="text-[10px] font-bold uppercase tracking-wider" :class="couponIsError ? 'text-rose-600' : 'text-emerald-700'" x-text="couponMessage"></p>
                </div>

                <!-- Loyalty Points Section -->
                <template x-if="loyaltyEnabled && userPoints >= loyaltyMinRedeem">
                    <div class="border-t border-[#E6DFD5] pt-6 space-y-4">
                        <div class="flex items-center justify-between">
                            <label class="text-[11px] font-bold uppercase tracking-widest text-[#221F1F] flex items-center space-x-2 cursor-pointer">
                                <input type="checkbox" x-model="redeemEnabled" @change="updateLoyaltyDiscount()" class="text-[#8C6554] focus:ring-[#8C6554] rounded-sm">
                                <span>Redeem LULU Points (Available: <span x-text="userPoints"></span> PTS)</span>
                            </label>
                        </div>
                        <div x-show="redeemEnabled" x-collapse class="space-y-3 pt-2">
                            <div class="flex items-center space-x-3">
                                <input type="number" 
                                       x-model.number="redeemPointsInput" 
                                       @input="updateLoyaltyDiscount()"
                                       :min="loyaltyMinRedeem"
                                       :max="userPoints"
                                       :disabled="!redeemEnabled"
                                       class="w-24 px-3 py-2 bg-white border border-[#E6DFD5] text-xs focus:outline-none focus:border-[#8C6554] rounded-sm font-mono text-center">
                                <span class="text-[10px] text-stone-500 font-bold uppercase tracking-wider">Points to redeem (Min: <span x-text="loyaltyMinRedeem"></span>)</span>
                            </div>
                            <p class="text-[10px] text-emerald-700 font-bold uppercase tracking-wider" x-show="loyaltyDiscount > 0">
                                {{ __('loyalty_discount') }}: -<span x-text="loyaltyDiscount.toFixed(2)"></span> Birr
                            </p>
                        </div>
                    </div>
                </template>
                <template x-if="loyaltyEnabled && userPoints < loyaltyMinRedeem && userPoints > 0">
                    <div class="border-t border-[#E6DFD5] pt-6">
                        <p class="text-[10px] text-stone-400 font-bold uppercase tracking-wider">
                            Redeem LULU Points (Available: <span x-text="userPoints"></span> PTS)
                        </p>
                        <p class="text-[9px] text-[#A38B7E] uppercase tracking-wider mt-1">
                            You need at least <span x-text="loyaltyMinRedeem"></span> points to start redeeming.
                        </p>
                    </div>
                </template>

                <!-- Hidden Input to submit points -->
                <input type="hidden" name="redeem_points" :value="form.redeem_points">

                <!-- Totals -->
                <div class="border-t border-[#E6DFD5] pt-6 space-y-4 text-[11px] font-bold uppercase tracking-widest text-[#221F1F]">
                    <div class="flex justify-between">
                        <span class="text-[#A38B7E]">Subtotal</span>
                        <span class="font-mono" x-text="subtotalDisplay">{{ $summary['subtotal_formatted'] }}</span>
                    </div>

                    <div class="flex justify-between text-emerald-700" x-show="couponDiscount > 0">
                        <span>{{ __('coupon_discount') }} (<span x-text="appliedCouponCode"></span>)</span>
                        <span class="font-mono text-emerald-700" x-text="'-' + couponDiscount.toFixed(2) + ' Birr'">-0.00 Birr</span>
                    </div>

                    <div class="flex justify-between text-emerald-700" x-show="loyaltyDiscount > 0">
                        <span>{{ __('loyalty_discount') }}</span>
                        <span class="font-mono text-emerald-700" x-text="'-' + loyaltyDiscount.toFixed(2) + ' Birr'">-0.00 Birr</span>
                    </div>

                    <div class="flex justify-between">
                        <span class="text-[#A38B7E]">Delivery</span>
                        <span class="font-mono text-[#8C6554]" x-text="deliveryFeeDisplay">Calculated</span>
                    </div>

                    <div class="flex justify-between text-sm pt-4 border-t border-[#E6DFD5]">
                        <span>Total</span>
                        <span class="font-mono" x-text="totalDisplay">$0.00</span>
                    </div>
                </div>

                <!-- Place Order Button inside summary card -->
                <div class="pt-6" x-show="currentStep === 2" x-transition>
                    <button type="submit" :disabled="isSubmitting" class="w-full bg-[#221F1F] hover:bg-[#8C6554] disabled:opacity-60 disabled:cursor-not-allowed text-white font-bold text-[11px] uppercase tracking-[0.2em] py-4 rounded-full shadow-lg transition-all cursor-pointer pointer-events-auto">
                        <span x-show="!isSubmitting">Place Order</span>
                        <span x-show="isSubmitting" x-cloak>Processing...</span>
                    </button>
                </div>
            </div>
        </div>
    </form>
</div>

<script>
function checkoutWizard() {
    const rawSubtotal = {{ $summary['subtotal'] / 100 }};
    const currency = '{{ $settings['currency_symbol'] ?? '$' }}';
    const blockedDates = @json($blockedDates);
    const blockedDaysOfWeek = @json($blockedDaysOfWeek);

    return {
        currentStep: 0,
        isSubmitting: false,
        timerSeconds: 1200,
        timerDisplay: '20:00',
        proofPreviewUrl: null,
        selectedCountryDropdown: 'Ethiopia',

        shippingRates: @json($shippingRates),
        countries: [],
        cities: [],
        districts: [],

        // Coupon state
        rawSubtotal: {{ $summary['subtotal'] / 100 }},
        couponDiscount: {{ $summary['discount_amount'] / 100 }},
        appliedCouponCode: '{{ $summary['discount_code'] }}',
        couponInput: '{{ $summary['discount_code'] }}',
        couponMessage: '',
        couponIsError: false,
        couponApplying: false,

        // Loyalty state
        loyaltyEnabled: @json($loyaltyEnabled),
        userPoints: @json($userPoints),
        loyaltyMinRedeem: @json($loyaltySettings['min_redeem'] ?? 50),
        pointValueCents: @json($loyaltySettings['point_value_cents'] ?? 100),
        redeemPointsInput: 0,
        loyaltyDiscount: 0,
        redeemEnabled: false,

        errors: {
            customer_name: false,
            customer_phone: false,
            preferred_date: false,
            preferred_time: false,
            customer_address: false,
            payment_proof: false,
        },

        form: {
            customer_name: '{{ Auth::user()?->name }}',
            customer_phone: '{{ Auth::user()?->phone }}',
            customer_email: '{{ Auth::user()?->email }}',
            logistics_mode: '{{ ($settings['logistics_pickup'] ?? '1') === '1' ? 'pickup' : 'delivery_fixed' }}',
            preferred_date: '',
            preferred_time: '',
            customer_country: 'Ethiopia',
            customer_city: '',
            customer_district: '',
            customer_address: '',
            payment_method: '{{ ($settings['payment_cod'] ?? '1') === '1' ? 'cod' : 'transfer' }}',
            selected_bank_id: '{{ ($settings['payment_cod'] ?? '1') === '1' ? '' : $bankAccounts->first()?->id }}',
            redeem_points: 0,
        },

        initWizard() {
            this.startTimer();
            this.initFlatpickr();

            this.$watch('form.logistics_mode', value => {
                if (value === 'pickup') {
                    this.form.customer_country = '';
                    this.form.customer_city = '';
                    this.form.customer_district = '';
                    this.form.customer_address = '';
                } else if (value === 'delivery_rider') {
                    this.form.customer_country = 'Ethiopia';
                    this.form.customer_city = 'Addis Ababa';
                    this.form.customer_district = '';
                } else if (value === 'delivery_fixed') {
                    this.form.customer_country = 'Ethiopia';
                }
            });

            // Populate other countries from database list (excluding Ethiopia which is default)
            const otherCountries = [...new Set(this.shippingRates.filter(r => r.country !== 'Ethiopia').map(r => r.country))];
            this.countries = otherCountries;

            // Populate cities for Ethiopia
            this.cities = [...new Set(this.shippingRates.filter(r => r.country === 'Ethiopia').map(r => r.city))];
        },

        startTimer() {
            setInterval(() => {
                if (this.timerSeconds <= 0) return;
                this.timerSeconds--;
                const m = Math.floor(this.timerSeconds / 60).toString().padStart(2, '0');
                const s = (this.timerSeconds % 60).toString().padStart(2, '0');
                this.timerDisplay = `${m}:${s}`;
            }, 1000);
        },

        initFlatpickr() {
            const disableRules = [...blockedDates];
            if (blockedDaysOfWeek.length > 0) disableRules.push(d => blockedDaysOfWeek.includes(d.getDay()));
            flatpickr("#datePicker", {
                minDate: "today", disable: disableRules, dateFormat: "Y-m-d", altInput: true, altFormat: "F j, Y",
                onChange: (selectedDates, dateStr) => {
                    this.form.preferred_date = dateStr;
                    this.errors.preferred_date = false;
                    this.checkTimeAvailability(dateStr);
                }
            });
        },

        checkTimeAvailability(date) {
            if (!date) return;
            fetch(`/checkout/slot-availability?date=${date}`)
                .then(res => res.json())
                .then(data => {
                    const select = document.querySelector('select[name="preferred_time"]');
                    if (!select) return;
                    Array.from(select.options).forEach(opt => {
                        const slotId = opt.getAttribute('data-id');
                        if (!slotId) return;
                        const originalLabel = opt.textContent.replace(' (FULLY BOOKED)', '');
                        if (data.full_slots.includes(parseInt(slotId))) {
                            opt.disabled = true; opt.textContent = originalLabel + ' (FULLY BOOKED)';
                        } else {
                            opt.disabled = false; opt.textContent = originalLabel;
                        }
                    });
                });
        },

        onCountryChange() {
            if (this.selectedCountryDropdown === 'Other') {
                this.form.customer_country = '';
            } else {
                this.form.customer_country = this.selectedCountryDropdown;
            }
            this.form.customer_city = '';
            this.form.customer_district = '';
            this.districts = [];
            this.errors.customer_address = false;
        },

        onCityChange() {
            this.form.customer_district = '';
            this.districts = [...new Set(this.shippingRates.filter(r => r.country === 'Ethiopia' && r.city === this.form.customer_city && r.district).map(r => r.district))];
        },

        getCityOptionLabel(city) {
            if (city === 'Addis Ababa') return 'Addis Ababa (price varies by district)';
            const rate = this.shippingRates.find(r => r.country === 'Ethiopia' && r.city === city && !r.district);
            return rate ? `${city} (${(rate.cost_cents / 100).toFixed(2)} Birr)` : city;
        },

        getDistrictOptionLabel(district) {
            const rate = this.shippingRates.find(r => r.country === 'Ethiopia' && r.city === 'Addis Ababa' && r.district === district);
            return rate ? `${district} (${(rate.cost_cents / 100).toFixed(2)} Birr)` : district;
        },

        onDistrictChange() {
            // Recalculates total automatically
        },

        get deliveryFee() {
            if (this.form.logistics_mode === 'pickup') return 0;
            if (this.form.logistics_mode === 'delivery_rider') return 0;
            if (this.form.customer_country !== 'Ethiopia') return 0;

            const city = this.form.customer_city;
            if (city === 'Addis Ababa') {
                const district = this.form.customer_district;
                const rate = this.shippingRates.find(r => r.country === 'Ethiopia' && r.city === 'Addis Ababa' && r.district === district);
                return rate ? (rate.cost_cents / 100) : 0;
            } else {
                const rate = this.shippingRates.find(r => r.country === 'Ethiopia' && r.city === city);
                return rate ? (rate.cost_cents / 100) : 0;
            }
        },

        get deliveryFeeDisplay() {
            if (this.form.logistics_mode === 'pickup') return 'Free';
            if (this.form.logistics_mode === 'delivery_rider') return 'Paid to Driver';
            if (this.form.customer_country !== 'Ethiopia') return 'Calculated by Staff';
            return this.deliveryFee.toFixed(2) + ' Birr';
        },

        get subtotalDisplay() {
            return this.rawSubtotal.toFixed(2) + ' Birr';
        },

        get totalAmount() { 
            return Math.max(0, this.rawSubtotal - this.couponDiscount - this.loyaltyDiscount + this.deliveryFee); 
        },
        get totalDisplay() { return this.totalAmount.toFixed(2) + ' Birr'; },

        applyCouponCode() {
            if (!this.couponInput || !this.couponInput.trim()) return;
            this.couponApplying = true;
            this.couponMessage = '';

            fetch('/cart/discount', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ code: this.couponInput })
            })
            .then(res => res.json())
            .then(data => {
                this.couponApplying = false;
                if (data.success && data.summary) {
                    this.couponDiscount = data.summary.discount_amount / 100;
                    this.appliedCouponCode = data.summary.discount_code;
                    this.couponMessage = data.message || 'Coupon applied successfully!';
                    this.couponIsError = false;
                } else {
                    this.couponMessage = (data.errors && data.errors.code) ? data.errors.code[0] : 'Invalid coupon code';
                    this.couponIsError = true;
                }
            })
            .catch(err => {
                this.couponApplying = false;
                this.couponMessage = 'Error applying coupon code';
                this.couponIsError = true;
            });
        },

        updateLoyaltyDiscount() {
            if (!this.redeemEnabled) {
                this.loyaltyDiscount = 0;
                this.form.redeem_points = 0;
                return;
            }
            
            let pts = parseInt(this.redeemPointsInput) || 0;
            if (pts > this.userPoints) pts = this.userPoints;
            if (pts < 0) pts = 0;
            this.redeemPointsInput = pts;
            
            if (pts >= this.loyaltyMinRedeem) {
                this.loyaltyDiscount = (pts * this.pointValueCents) / 100;
                this.form.redeem_points = pts;
            } else {
                this.loyaltyDiscount = 0;
                this.form.redeem_points = 0;
            }
        },

        validateCurrentStep(stepIdx) {
            let isValid = true;
            let missingField = '';

            if (stepIdx === 0) {
                if (!this.form.customer_name || !this.form.customer_name.trim()) {
                    this.errors.customer_name = true;
                    isValid = false;
                    missingField = 'Full Name';
                }
                if (!this.form.customer_phone || !this.form.customer_phone.trim()) {
                    this.errors.customer_phone = true;
                    isValid = false;
                    if (!missingField) missingField = 'Phone Number';
                }
            }

            if (stepIdx === 1) {
                const scheduleEnabled = '{{ $settings['schedule_enabled'] ?? '0' }}' === '1';
                if (scheduleEnabled) {
                    if (!this.form.preferred_date) {
                        this.errors.preferred_date = true;
                        isValid = false;
                        missingField = 'Preferred Date';
                    }
                    if (!this.form.preferred_time) {
                        this.errors.preferred_time = true;
                        isValid = false;
                        if (!missingField) missingField = 'Time Slot';
                    }
                }
                if (this.form.logistics_mode === 'delivery_fixed') {
                    if (!this.form.customer_country) {
                        isValid = false;
                        if (!missingField) missingField = 'Country';
                    }
                    if (this.form.customer_country === 'Ethiopia' && !this.form.customer_city) {
                        isValid = false;
                        if (!missingField) missingField = 'City';
                    }
                    if (this.form.customer_country === 'Ethiopia' && this.form.customer_city === 'Addis Ababa' && !this.form.customer_district) {
                        isValid = false;
                        if (!missingField) missingField = 'District';
                    }
                }
                if (this.form.logistics_mode !== 'pickup') {
                    if (!this.form.customer_address || !this.form.customer_address.trim()) {
                        this.errors.customer_address = true;
                        isValid = false;
                        if (!missingField) missingField = 'Delivery Address';
                    }
                }
            }

            if (stepIdx === 2 && this.form.payment_method === 'transfer') {
                const proofInput = document.getElementById('paymentProofInput');
                if (!this.proofPreviewUrl && (!proofInput || !proofInput.files.length)) {
                    this.errors.payment_proof = true;
                    isValid = false;
                    missingField = 'Payment Proof Receipt';
                }
            }

            if (!isValid && missingField) {
                window.dispatchEvent(new CustomEvent('show-toast', { 
                    detail: { message: 'Please fill in required field: ' + missingField, isError: true } 
                }));
            }

            return isValid;
        },

        validateLoyalty() {
            // Only validate loyalty if the user has opted in to redeem
            if (!this.redeemEnabled) return true;
            const pts = parseInt(this.redeemPointsInput) || 0;
            if (pts < this.loyaltyMinRedeem) {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: `Points to Redeem must be at least ${this.loyaltyMinRedeem}`, isError: true }
                }));
                return false;
            }
            if (pts > this.userPoints) {
                window.dispatchEvent(new CustomEvent('show-toast', {
                    detail: { message: `You only have ${this.userPoints} points available`, isError: true }
                }));
                return false;
            }
            return true;
        },

        placeOrder(form) {
            // Validate all steps + loyalty before submitting
            if (!this.validateCurrentStep(0)) return;
            if (!this.validateCurrentStep(1)) return;
            if (!this.validateCurrentStep(2)) return;
            if (!this.validateLoyalty()) return;

            this.isSubmitting = true;
            form.submit();
        },

        nextStep() {
            if (this.validateCurrentStep(this.currentStep)) {
                this.currentStep++;
            }
        },

        goToStep(idx) {
            if (idx < this.currentStep) this.currentStep = idx;
        },

        previewProof(e) {
            if (e.target.files[0]) this.proofPreviewUrl = URL.createObjectURL(e.target.files[0]);
        }
    }
}
</script>
@endsection
