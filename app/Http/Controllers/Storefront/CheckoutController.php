<?php

namespace App\Http\Controllers\Storefront;

use App\Events\OrderPlaced;
use App\Http\Controllers\Controller;
use App\Http\Requests\CheckoutRequest;
use App\Models\BankAccount;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\PickupTime;
use App\Models\PickupTimeOverride;
use App\Models\ProductVariant;
use App\Models\StockMovement;
use App\Models\SystemSetting;
use App\Models\VerifiedTransaction;
use App\Services\CartService;
use App\Services\Payments\PaymentGatewayManager;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutController extends Controller
{
    protected CartService $cartService;
    protected PaymentGatewayManager $paymentManager;

    public function __construct(CartService $cartService, PaymentGatewayManager $paymentManager)
    {
        $this->cartService = $cartService;
        $this->paymentManager = $paymentManager;
    }

    public function index()
    {
        $summary = $this->cartService->getCartSummary();

        if ($summary['count'] === 0) {
            return redirect()->route('cart.index')->with('error', 'Your shopping bag is empty.');
        }

        $settings = SystemSetting::getAllCheckoutSettings();
        $bankAccounts = BankAccount::where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $pickupTimes = PickupTime::where('is_active', true)->orderBy('sort_order', 'asc')->get();
        $blockedDates = json_decode($settings['blocked_dates'] ?? '[]', true);
        $blockedDaysOfWeek = json_decode($settings['blocked_days_of_week'] ?? '[]', true);
        $shippingRates = \App\Models\ShippingRate::where('is_active', true)->get();

        return view('storefront.checkout.index', compact(
            'summary',
            'settings',
            'bankAccounts',
            'pickupTimes',
            'blockedDates',
            'blockedDaysOfWeek',
            'shippingRates'
        ));
    }

    public function checkSlotAvailability(Request $request)
    {
        $date = $request->query('date');
        if (! $date) {
            return response()->json(['full_slots' => [], 'blocked' => false]);
        }

        $overrides = PickupTimeOverride::where('override_date', $date)->get();
        $fullSlotIds = $overrides->where('status', 'full')->pluck('pickup_time_id')->filter()->toArray();
        $isBlocked = $overrides->whereNull('pickup_time_id')->where('status', 'blocked')->count() > 0;

        return response()->json([
            'full_slots' => $fullSlotIds,
            'blocked' => $isBlocked,
        ]);
    }

    public function store(CheckoutRequest $request)
    {
        $validated = $request->validated();

        $summary = $this->cartService->getCartSummary();
        if ($summary['count'] === 0) {
            return back()->withErrors(['cart' => 'Your shopping bag is empty.']);
        }

        $settings = SystemSetting::getAllCheckoutSettings();

        try {
            $order = DB::transaction(function () use ($validated, $summary, $settings, $request) {
                $orderNumber = 'LULU-' . strtoupper(Str::random(6));

                // 1. Logistics Delivery Fee Calculation (stored in minor units)
                $deliveryFeeCents = 0;
                if (in_array($validated['logistics_mode'], ['delivery_fixed', 'delivery_rider'], true)) {
                    $country = $validated['customer_country'] ?? 'Ethiopia';
                    if ($country === 'Ethiopia') {
                        $city = $validated['customer_city'] ?? '';
                        if ($city === 'Addis Ababa') {
                            $district = $validated['customer_district'] ?? '';
                            $rate = \App\Models\ShippingRate::where('country', 'Ethiopia')
                                ->where('city', 'Addis Ababa')
                                ->where('district', $district)
                                ->where('is_active', true)
                                ->first();
                            $deliveryFeeCents = $rate ? $rate->cost_cents : 0;
                        } else {
                            $rate = \App\Models\ShippingRate::where('country', 'Ethiopia')
                                ->where('city', $city)
                                ->where('is_active', true)
                                ->first();
                            $deliveryFeeCents = $rate ? $rate->cost_cents : 0;
                        }
                    } else {
                        // International delivery — staff will contact the customer to quote shipping
                        $deliveryFeeCents = 0;
                    }
                }

                // 2. Final Total Calculation
                $subtotal = $summary['subtotal'];
                $discountAmount = $summary['discount_amount'];
                $finalTotal = max(0, $subtotal - $discountAmount + $deliveryFeeCents);

                // 3. Deposit vs Balance Calculation
                $depositCents = 0;
                $balanceCents = $finalTotal;
                if (($settings['deposit_required'] ?? '0') === '1') {
                    $pct = (float) ($settings['deposit_percentage'] ?? 50);
                    $depositCents = (int) round($finalTotal * ($pct / 100));
                    $balanceCents = max(0, $finalTotal - $depositCents);
                }

                // 4. Handle Payment Proof Screenshot Upload
                $paymentProofUrl = null;
                if ($request->hasFile('payment_proof')) {
                    $path = $request->file('payment_proof')->store('proofs', 'public');
                    $paymentProofUrl = '/storage/' . $path;
                }

                // 5. Create Base Order
                $order = Order::create([
                    'order_number' => $orderNumber,
                    'user_id' => Auth::id(),
                    'customer_name' => $validated['customer_name'],
                    'customer_phone' => $validated['customer_phone'],
                    'customer_address' => $validated['customer_address'] ?? 'Pickup at Store',
                    'customer_city' => $validated['customer_city'] ?? 'Addis Ababa',
                    'customer_country' => $validated['customer_country'] ?? 'Ethiopia',
                    'customer_district' => $validated['customer_district'] ?? null,
                    'status' => 'pending',
                    'payment_method' => $validated['payment_method'],
                    'payment_status' => 'unpaid',
                    'logistics_mode' => $validated['logistics_mode'],
                    'delivery_fee' => $deliveryFeeCents,
                    'preferred_date' => $validated['preferred_date'],
                    'preferred_time' => $validated['preferred_time'],
                    'google_maps_link' => $validated['google_maps_link'] ?? null,
                    'payment_proof' => $paymentProofUrl,
                    'confirmed_transaction_id' => $validated['confirmed_transaction_id'] ?? null,
                    'bank_account_id' => ($validated['payment_method'] === 'transfer') ? ($validated['selected_bank_id'] ?? null) : null,
                    'deposit_amount' => $depositCents,
                    'balance_due' => $balanceCents,
                    'subtotal' => $subtotal,
                    'discount_amount' => $discountAmount,
                    'total' => $finalTotal,
                    'notes' => $validated['notes'] ?? null,
                ]);

                // 6. Record Verified Transaction if Transaction ID/Link provided
                if (! empty($validated['confirmed_transaction_id'])) {
                    VerifiedTransaction::create([
                        'bank_account_id' => $validated['selected_bank_id'] ?? null,
                        'transaction_id' => trim($validated['confirmed_transaction_id']),
                        'amount' => ($depositCents > 0) ? $depositCents : $finalTotal,
                        'order_id' => $order->id,
                    ]);
                }

                // 7. Process Items with Atomic Stock Decrement
                foreach ($summary['items'] as $itemData) {
                    $variantId = $itemData['variant_id'];
                    $qty = $itemData['quantity'];

                    $affectedRows = ProductVariant::where('id', $variantId)
                        ->where('stock_quantity', '>=', $qty)
                        ->decrement('stock_quantity', $qty);

                    if ($affectedRows === 0) {
                        throw new Exception("Item '{$itemData['title']}' ({$itemData['sku']}) is sold out or has insufficient stock.");
                    }

                    $updatedVariant = ProductVariant::find($variantId);

                    StockMovement::create([
                        'product_variant_id' => $variantId,
                        'delta' => -$qty,
                        'resulting_quantity' => $updatedVariant->stock_quantity,
                        'reason' => 'order_placed',
                        'actor_id' => Auth::id(),
                    ]);

                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $updatedVariant->product_id,
                        'product_variant_id' => $variantId,
                        'product_title' => $itemData['title'],
                        'variant_sku' => $itemData['sku'],
                        'unit_price' => $itemData['unit_price'],
                        'quantity' => $qty,
                        'total_price' => $itemData['total_price'],
                    ]);
                }

                // 8. Process Payment via Gateway Driver (COD or Transfer)
                $paymentDriver = $this->paymentManager->driver($validated['payment_method']);
                $paymentDriver->processPayment($order, $validated);

                return $order;
            });

            // 9. Dispatch Domain Event
            OrderPlaced::dispatch($order);

            // 10. Clear Cart & Redirect to Confirmation
            $this->cartService->clearCart();

            return redirect()->route('order.confirmation', $order->order_number)
                ->with('success', "Order #{$order->order_number} placed successfully!");

        } catch (Exception $e) {
            return back()->withErrors(['checkout' => $e->getMessage()])->withInput();
        }
    }
}
