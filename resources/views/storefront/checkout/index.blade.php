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

    <form method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data" id="checkoutForm" @submit="if (!validateCurrentStep(0) || !validateCurrentStep(1) || !validateCurrentStep(2)) $event.preventDefault()" class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-start">
        @csrf

        <!-- Left Column: Checkout Steps (lg:col-span-7) -->
        <div class="lg:col-span-7 space-y-10">

            <!-- Step 1: Contact -->
            <div class="bg-white p-8 sm:p-10 shadow-xl border border-[#E6DFD5] relative" :class="currentStep !== 0 ? 'opacity-50 pointer-events-none' : ''">
                <div class="absolute -left-4 top-10 bg-[#221F1F] text-white w-8 h-8 flex items-center justify-center font-bold text-xs rounded-sm shadow-md">1</div>
                
                <h2 class="text-xl font-serif font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-8 border-b border-[#E6DFD5] pb-4">
                    Contact Details
                </h2>

                <div class="space-y-6">
                    <div>
                        <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">Full Name *</label>
                        <input type="text" name="customer_name" x-model="form.customer_name" @input="errors.customer_name = false" required 
                               :class="errors.customer_name ? 'border-[#C49A9A] ring-2 ring-[#C49A9A]/30 bg-rose-50/50' : 'border-transparent focus:border-[#8C6554]'"
                               class="w-full px-4 py-3 bg-[#F3EEE8] border text-sm text-[#221F1F] focus:outline-none transition-all rounded-sm shadow-inner">
                        <p x-show="errors.customer_name" class="text-[10px] font-bold uppercase tracking-widest text-[#C49A9A] mt-1">Full name is required.</p>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">Phone Number *</label>
                            <input type="tel" name="customer_phone" x-model="form.customer_phone" @input="errors.customer_phone = false" required 
                                   :class="errors.customer_phone ? 'border-[#C49A9A] ring-2 ring-[#C49A9A]/30 bg-rose-50/50' : 'border-transparent focus:border-[#8C6554]'"
                                   class="w-full px-4 py-3 bg-[#F3EEE8] border text-sm text-[#221F1F] focus:outline-none transition-all rounded-sm shadow-inner">
                            <p x-show="errors.customer_phone" class="text-[10px] font-bold uppercase tracking-widest text-[#C49A9A] mt-1">Phone number is required.</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">Email Address</label>
                            <input type="email" name="customer_email" x-model="form.customer_email" class="w-full px-4 py-3 bg-[#F3EEE8] border border-transparent focus:border-[#8C6554] text-sm text-[#221F1F] focus:outline-none transition-colors rounded-sm shadow-inner">
                        </div>
                    </div>

                    <div class="flex justify-end pt-6" x-show="currentStep === 0">
                        <button type="button" @click="nextStep()" class="bg-[#221F1F] hover:bg-[#8C6554] text-white font-bold text-[11px] uppercase tracking-[0.2em] px-10 py-4 rounded-full shadow-lg transition-all">
                            Continue
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
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">Date *</label>
                            <input type="text" name="preferred_date" id="datePicker" x-model="form.preferred_date" required 
                                   :class="errors.preferred_date ? 'border-[#C49A9A] ring-2 ring-[#C49A9A]/30 bg-rose-50/50' : 'border-transparent focus:border-[#8C6554]'"
                                   class="w-full px-4 py-3 bg-[#F3EEE8] border text-sm text-[#221F1F] focus:outline-none transition-all rounded-sm shadow-inner">
                            <p x-show="errors.preferred_date" class="text-[10px] font-bold uppercase tracking-widest text-[#C49A9A] mt-1">Please select a date.</p>
                        </div>
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">Time *</label>
                            <select name="preferred_time" x-model="form.preferred_time" @change="errors.preferred_time = false" required 
                                    :class="errors.preferred_time ? 'border-[#C49A9A] ring-2 ring-[#C49A9A]/30 bg-rose-50/50' : 'border-transparent focus:border-[#8C6554]'"
                                    class="w-full px-4 py-3 bg-[#F3EEE8] border text-sm text-[#221F1F] focus:outline-none transition-all rounded-sm shadow-inner">
                                <option value="">Select Time</option>
                                @foreach($pickupTimes as $slot)
                                    <option value="{{ $slot->time_label }}" data-id="{{ $slot->id }}">
                                        {{ $slot->time_label }}
                                    </option>
                                @endforeach
                            </select>
                            <p x-show="errors.preferred_time" class="text-[10px] font-bold uppercase tracking-widest text-[#C49A9A] mt-1">Please select a time slot.</p>
                        </div>
                    </div>

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
                                        <span class="font-bold text-xs uppercase tracking-wider text-[#221F1F]">Flat Fee Delivery ({{ $settings['currency_symbol'] }}{{ number_format((float)($settings['delivery_fixed_fee'] ?? 15), 2) }})</span>
                                    </div>
                                </label>
                            @endif

                            @if(($settings['logistics_delivery_rider'] ?? '1') === '1')
                                <label :class="form.logistics_mode === 'delivery_rider' ? 'border-[#8C6554] bg-[#F9F6F0]' : 'border-[#E6DFD5] bg-white'" class="p-4 border rounded-sm cursor-pointer flex items-center justify-between transition-all">
                                    <div class="flex items-center space-x-3">
                                        <input type="radio" name="logistics_mode" value="delivery_rider" x-model="form.logistics_mode" class="text-[#8C6554] focus:ring-[#8C6554]">
                                        <span class="font-bold text-xs uppercase tracking-wider text-[#221F1F]">Express Courier (Paid to Driver)</span>
                                    </div>
                                </label>
                            @endif
                        </div>
                    </div>

                    <!-- Address -->
                    <div x-show="form.logistics_mode !== 'pickup'" class="space-y-6 pt-4 border-t border-[#E6DFD5]">
                        <div>
                            <label class="block text-[10px] font-bold uppercase tracking-[0.2em] text-[#221F1F] mb-2">Street Address *</label>
                            <textarea name="customer_address" x-model="form.customer_address" @input="errors.customer_address = false" :required="form.logistics_mode !== 'pickup'" rows="2" 
                                      :class="errors.customer_address ? 'border-[#C49A9A] ring-2 ring-[#C49A9A]/30 bg-rose-50/50' : 'border-transparent focus:border-[#8C6554]'"
                                      class="w-full px-4 py-3 bg-[#F3EEE8] border text-sm text-[#221F1F] focus:outline-none transition-all rounded-sm shadow-inner"></textarea>
                            <p x-show="errors.customer_address" class="text-[10px] font-bold uppercase tracking-widest text-[#C49A9A] mt-1">Delivery street address is required.</p>
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
                                    <span class="font-bold text-xs uppercase tracking-wider text-[#221F1F]">Bank Transfer</span>
                                </div>
                            </label>
                        @endif
                    </div>

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

                    <div class="flex justify-end pt-6" x-show="currentStep === 2">
                        <button type="submit" class="bg-[#221F1F] hover:bg-[#8C6554] text-white font-bold text-[11px] uppercase tracking-[0.2em] px-10 py-4 rounded-full shadow-lg transition-all pointer-events-auto">
                            Place Order
                        </button>
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

                <!-- Totals -->
                <div class="border-t border-[#E6DFD5] pt-6 space-y-4 text-[11px] font-bold uppercase tracking-widest text-[#221F1F]">
                    <div class="flex justify-between">
                        <span class="text-[#A38B7E]">Subtotal</span>
                        <span class="font-mono">{{ $summary['subtotal_formatted'] }}</span>
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
            </div>
        </div>
    </form>
</div>

<script>
function checkoutWizard() {
    const rawSubtotal = {{ $summary['subtotal'] / 100 }};
    const fixedFee = {{ (float)($settings['delivery_fixed_fee'] ?? 15) }};
    const currency = '{{ $settings['currency_symbol'] ?? '$' }}';
    const blockedDates = @json($blockedDates);
    const blockedDaysOfWeek = @json($blockedDaysOfWeek);

    return {
        currentStep: 0,
        timerSeconds: 1200,
        timerDisplay: '20:00',
        proofPreviewUrl: null,

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
            customer_address: '',
            payment_method: '{{ ($settings['payment_cod'] ?? '1') === '1' ? 'cod' : 'transfer' }}',
            selected_bank_id: '{{ ($settings['payment_cod'] ?? '1') === '1' ? '' : $bankAccounts->first()?->id }}',
        },

        initWizard() {
            this.startTimer();
            this.initFlatpickr();
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

        get deliveryFee() { return this.form.logistics_mode === 'delivery_fixed' ? fixedFee : 0; },
        get deliveryFeeDisplay() {
            if (this.form.logistics_mode === 'pickup') return 'Free';
            if (this.form.logistics_mode === 'delivery_rider') return 'Paid to Driver';
            return this.deliveryFee.toFixed(2) + ' Birr';
        },
        get totalAmount() { return Math.max(0, rawSubtotal + this.deliveryFee); },
        get totalDisplay() { return this.totalAmount.toFixed(2) + ' Birr'; },

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
                if (this.form.logistics_mode !== 'pickup' && (!this.form.customer_address || !this.form.customer_address.trim())) {
                    this.errors.customer_address = true;
                    isValid = false;
                    if (!missingField) missingField = 'Delivery Address';
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
