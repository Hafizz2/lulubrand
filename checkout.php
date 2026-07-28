<?php
/**
 * Crumbs & Crust – Checkout Page
 */
require_once __DIR__ . '/config/helpers.php';
startSession();

// Capture flash early before header.php consumes it
$checkoutError = null;
$flash = getFlash();
if ($flash) {
    if ($flash['type'] === 'error') $checkoutError = $flash['message'];
    else $_SESSION['flash'] = $flash; // Put back non-errors for the header to render
}

$cart = $_SESSION['cart'] ?? [];
if (empty($cart)) {
    header('Location: ' . BASE_URL . '/cart');
    exit;
}

// 5-Minute Session Timer Logic
if (!isset($_SESSION['checkout_started_at'])) {
    $_SESSION['checkout_started_at'] = time();
} else {
    $elapsed = time() - $_SESSION['checkout_started_at'];
    if ($elapsed > 1200) { // 20 minutes session limit
        unset($_SESSION['checkout_started_at']);
        unset($_SESSION['checkout_data']); // Clear persistent data
        setFlash('error', 'Checkout session expired. Please review your cart and try again.');
        header('Location: ' . BASE_URL . '/cart');
        exit;
    }
}

$checkoutData = $_SESSION['checkout_data'] ?? [];
$db = getDB();
$settings = getAllSettings();

// Fetch Pickup Times
$pickupTimes = $db->query("SELECT * FROM pickup_times WHERE is_active = 1 ORDER BY sort_order ASC")->fetchAll();

// Handle AJAX request for time availability
if (isset($_GET['check_availability']) && isset($_GET['date'])) {
    $checkDate = $_GET['date'];
    $stmt = $db->prepare("SELECT pickup_time_id FROM pickup_time_overrides WHERE override_date = ? AND status = 'full'");
    $stmt->execute([$checkDate]);
    $fullSlotIds = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    header('Content-Type: application/json');
    echo json_encode(['full_slots' => $fullSlotIds]);
    exit;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRF($_POST['csrf_token'] ?? '')) {
        setFlash('error', 'Invalid form submission. Please try again.');
        header('Location: ' . BASE_URL . '/checkout');
        exit;
    }

    $name    = trim($_POST['customer_name'] ?? '');
    $phone   = trim($_POST['customer_phone'] ?? '');
    $email   = trim($_POST['customer_email'] ?? '');
    $address = trim($_POST['customer_address'] ?? '');
    $gMapLink = trim($_POST['google_maps_link'] ?? '');
    $logistics = $_POST['logistics_mode'] ?? 'pickup';
    $payMethod = $_POST['payment_method'] ?? '';
    $selectedBankId = (int)($_POST['selected_bank_id'] ?? 0);
    $transactionId = trim($_POST['confirmed_transaction_id'] ?? '');
    $prefDate  = $_POST['preferred_date'] ?? '';
    $prefTime  = $_POST['preferred_time'] ?? '';
    $instructions = trim($_POST['special_instructions'] ?? '');

    // Server-side Validation
    $errors = [];
    if (!$name) $errors[] = "Full Name is required.";
    if (!$phone) $errors[] = "Phone Number is required.";
    if (!$prefDate) $errors[] = "Preferred Date is required.";
    if (!$prefTime) $errors[] = "Preferred Time is required.";
    if (!$payMethod) $errors[] = "Please select a payment method.";
    if ($logistics !== 'pickup' && !$address) $errors[] = "Delivery Address is required.";
    
    // Check if date is blocked
    $blocked = getBlockedDates();
    if (in_array($prefDate, $blocked)) $errors[] = "The selected date is unavailable.";

    // Check if time slot is full
    $stmtCheck = $db->prepare("SELECT id FROM pickup_time_overrides WHERE override_date = ? AND pickup_time_id = (SELECT id FROM pickup_times WHERE time_label = ? LIMIT 1) AND status = 'full'");
    $stmtCheck->execute([$prefDate, $prefTime]);
    if ($stmtCheck->fetch()) $errors[] = "The selected time slot is fully booked for this date.";

    // Calculate totals first for verification
    $subtotal = array_sum(array_column($cart, 'line_total'));
    $deliveryFee = 0;
    if ($logistics === 'delivery_fixed') {
        $deliveryFee = (float)($settings['delivery_fixed_fee'] ?? 0);
    }
    $total = $subtotal + $deliveryFee;
    $depositAmount = 0;
    if (($settings['deposit_required'] ?? '0') === '1') {
        $pct = (float)($settings['deposit_percentage'] ?? 50);
        $depositAmount = round($total * ($pct / 100), 2);
    }
    $expectedAmount = ($depositAmount > 0) ? $depositAmount : $total;

    // Payment proof requirement and Automatic Verification
    $paymentProof = null;
    if ($payMethod === 'transfer' || $payMethod === 'full') {
        if (!$selectedBankId) {
            $errors[] = "Please select the bank you transferred to.";
        } elseif (!isset($_FILES['payment_proof']) || $_FILES['payment_proof']['error'] !== UPLOAD_ERR_OK) {
            $errors[] = "Payment proof screenshot is mandatory for bank transfers.";
        } else {
            $paymentProof = processUploadedImage($_FILES['payment_proof'], UPLOADS_PATH . '/proofs');
            if (!$paymentProof) {
                $errors[] = "Invalid payment proof image.";
            } elseif ($transactionId) {
                // Uniqueness check: Ensure this ID hasn't been used for another order
                $stmt = $db->prepare("SELECT id FROM verified_transactions WHERE transaction_id = ?");
                $stmt->execute([$transactionId]);
                if ($stmt->fetch()) {
                    $errors[] = "This Transaction ID or Link has already been used for another order.";
                }
            }
        }
    }

    if (!empty($errors)) {
        $_SESSION['checkout_data'] = $_POST; // Save input data to session on error
        setFlash('error', implode('<br>', $errors));
        header('Location: ' . BASE_URL . '/checkout');
        exit;
    }

    $balanceDue = $total - $depositAmount;
    $trackingCode = generateTrackingCode();

    $db->beginTransaction();
    try {
        // Insert order
        $stmt = $db->prepare("INSERT INTO orders (tracking_code, customer_name, customer_phone, customer_email, customer_address, google_map_link, logistics_mode, delivery_fee, preferred_date, preferred_time, special_instructions, payment_method, subtotal, total, deposit_amount, balance_due, payment_proof) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->execute([$trackingCode, $name, $phone, $email, $address, $gMapLink, $logistics, $deliveryFee, $prefDate, $prefTime, $instructions, $payMethod, $subtotal, $total, $depositAmount, $balanceDue, $paymentProof]);
        $orderId = $db->lastInsertId();

        // Record verified transaction
        if ($transactionId && $paymentProof) {
            $stmt = $db->prepare("INSERT INTO verified_transactions (bank_id, transaction_id, amount, order_id) VALUES (?, ?, ?, ?)");
            $stmt->execute([$selectedBankId, $transactionId, $expectedAmount, $orderId]);
        }

        // Insert order items
        foreach ($cart as $item) {
            $variantJson = !empty($item['variants']) ? json_encode($item['variants']) : null;
            $addonsJson = !empty($item['addons']) ? json_encode($item['addons']) : null;
            $itemMessage = $item['cake_message'] ?? null;

            $stmt = $db->prepare("INSERT INTO order_items (order_id, product_id, product_name, variant_info, addons_info, cake_message, unit_price, quantity, line_total) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->execute([$orderId, $item['product_id'], $item['product_name'], $variantJson, $addonsJson, $itemMessage, $item['unit_price'], $item['quantity'], $item['line_total']]);
        }

        // Initial status history
        $stmt = $db->prepare("INSERT INTO order_status_history (order_id, status, notes) VALUES (?, 'pending', 'Order placed by customer and verified')");
        $stmt->execute([$orderId]);

        $db->commit();
    } catch (Exception $e) {
        $db->rollBack();
        error_log("Checkout error: " . $e->getMessage());
        setFlash('error', 'There was a problem saving your order. Please try again or contact support.');
        header('Location: ' . BASE_URL . '/checkout');
        exit;
    }

    // Prepare Absolute URLs for Telegram (must be absolute for buttons to work)
    $protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' || isset($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https') ? 'https' : 'http';
    $host = $_SERVER['HTTP_HOST'];
    $fullAdminUrl = $protocol . '://' . $host . ADMIN_URL;

    // Send Telegram Notification
    $msg = "🔔 <b>New Order Placed!</b>\n\n";
    $msg .= "Order ID: #{$trackingCode}\n";
    $msg .= "Customer: {$name}\n";
    $msg .= "Phone: <a href='tel:{$phone}'>{$phone}</a>\n";
    $msg .= "Total: " . formatPrice($total) . "\n";
    $msg .= "Payment: " . strtoupper($payMethod) . "\n";
    if ($transactionId) {
        if (filter_var($transactionId, FILTER_VALIDATE_URL)) {
            $msg .= "Receipt: <a href='{$transactionId}'>Click to View Receipt Link</a>\n";
        } else {
            $msg .= "Transaction ID: <code>{$transactionId}</code>\n";
        }
    }
    $msg .= "Delivery: " . str_replace('_', ' ', $logistics) . "\n";
    $msg .= "Date: {$prefDate} {$prefTime}\n\n";
    $msg .= "Check details in admin panel.";

    $db = null; // Close DB BEFORE the potentially slow Telegram network call
    try {
        sendTelegramNotification($msg);
    } catch (Exception $e) {
        error_log("Telegram notification failure: " . $e->getMessage());
    }

    unset($_SESSION['checkout_data']); // Clear data on successful order
    unset($_SESSION['checkout_started_at']);
    // Clear cart
    $_SESSION['cart'] = [];

    header('Location: ' . BASE_URL . '/order_success?code=' . $trackingCode);
    exit;
}

$subtotal = array_sum(array_column($cart, 'line_total'));
$deliveryFee = (float)($settings['delivery_fixed_fee'] ?? 0);
$blockedDates = getBlockedDates();
$blockedDaysOfWeek = getBlockedDaysOfWeek(); // Fetch weekly blocked days
$nextDate = getNextAvailableDate();

$pageTitle = 'Checkout';
require_once __DIR__ . '/views/header.php';
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
    #checkout-form-wizard {
        background: #fff;
        padding: 2rem;
        border-radius: 30px;
        box-shadow: 0 10px 50px rgba(0,0,0,0.04);
        margin-bottom: 4rem;
    }

    /* Modern Progress Bar - Simplified & Sleek */
    .checkout-steps-indicator {
        display: flex;
        justify-content: space-between;
        margin-bottom: 4rem;
        padding: 0;
        list-style: none;
        overflow-x: auto;
        scrollbar-width: none;
        gap: 15px;
        position: relative;
    }
    .checkout-steps-indicator::-webkit-scrollbar { display: none; }

    .checkout-steps-indicator .step {
        flex: 1;
        text-align: center;
        font-size: 0.75rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
        color: var(--text-muted);
        padding-bottom: 15px;
        border-bottom: 3px solid #eee;
        transition: var(--transition-spring);
        min-width: 70px;
        white-space: nowrap;
    }
    .checkout-steps-indicator .step.active {
        color: var(--primary);
        border-bottom-color: var(--primary);
    }
    .checkout-steps-indicator .step.completed {
        color: var(--secondary);
        border-bottom-color: var(--secondary);
    }

    /* Beautiful Form Controls */
    .form-group label {
        display: block;
        font-weight: 700;
        font-size: 0.85rem;
        margin-bottom: 10px;
        color: var(--secondary);
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }
    .form-control {
        width: 100%;
        padding: 14px 18px;
        border-radius: 16px;
        border: 2px solid #f0f0f0;
        background: #fbfbfb;
        font-size: 1rem;
        transition: all 0.3s ease;
        color: var(--text-dark);
    }
    .form-control:focus {
        outline: none;
        border-color: var(--primary);
        background: #fff;
        box-shadow: 0 5px 15px rgba(var(--primary-rgb), 0.1);
    }

    /* Selection Cards (Logistics & Banks) */
    .variant-option, .bank-card, .payment-option {
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        padding: 1.8rem 1rem;
        background: var(--white);
        border: 2px solid #f0f0f0;
        border-radius: 20px;
        cursor: pointer;
        transition: var(--transition-spring);
        position: relative;
        height: 100%;
    }
    .variant-option input, .bank-card input, .payment-option input {
        position: absolute;
        opacity: 0;
    }
    .variant-option .icon-box, .bank-card .icon-box, .payment-option .icon-box {
        width: 60px;
        height: 60px;
        background: var(--accent);
        color: var(--primary);
        border-radius: 18px;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-bottom: 1rem;
        transition: 0.3s;
    }
    .variant-option .icon-box .material-symbols-rounded, .bank-card .icon-box .material-symbols-rounded,
    .payment-option .icon-box .material-symbols-rounded { font-size: 2rem; }

    .variant-option:has(input:checked), .bank-card:has(input:checked), .payment-option:has(input:checked) {
        border-color: var(--primary);
        background: #fff;
        box-shadow: 0 10px 25px rgba(0,0,0,0.05);
        transform: translateY(-5px);
    }

    /* Selection Indicator Icon */
    .variant-option .selection-indicator, .bank-card .selection-indicator, .payment-option .selection-indicator {
        position: absolute;
        top: 10px;
        right: 10px;
        width: 20px;
        height: 20px;
        background: var(--primary);
        color: white;
        border-radius: 50%;
        display: none;
        align-items: center;
        justify-content: center;
        font-size: 14px;
        font-weight: bold;
        z-index: 2;
    }

    .variant-option:has(input:checked) .selection-indicator, 
    .bank-card:has(input:checked) .selection-indicator, 
    .payment-option:has(input:checked) .selection-indicator {
        display: flex;
    }

    .variant-option:has(input:checked) .icon-box, .payment-option:has(input:checked) .icon-box {
        background: var(--primary);
        color: #fff;
    }

    .checkout-step { display: none; animation: fadeIn 0.4s ease; }
    .checkout-step.active { display: block; }

    .checkout-step h3, .checkout-step h4 {
        font-size: 1.5rem;
        margin-bottom: 2rem;
        display: flex;
        align-items: center;
        gap: 12px;
        color: var(--secondary);
    }
    .checkout-step h3 .material-symbols-rounded,
    .checkout-step h4 .material-symbols-rounded {
        font-size: 1.8rem;
    }

    .inline-error-banner {
        background: #fff1f2;
        color: #be123c;
        padding: 1rem;
        border-radius: var(--radius-md);
        margin-bottom: 1.5rem;
        display: none;
        align-items: center;
        gap: 10px;
        font-weight: 600;
        font-size: 0.9rem;
        border: 1px solid #fecaca;
    }

    /* Modern Upload Area */
    .upload-area {
        border: 2px dashed #ddd;
        border-radius: 20px;
        padding: 2rem;
        text-align: center;
        cursor: pointer;
        transition: 0.3s;
        background: #fafafa;
        position: relative;
    }
    .upload-area:hover { border-color: var(--primary); background: #f8fafc; }
    .upload-area .material-symbols-rounded { font-size: 3rem; color: var(--primary); margin-bottom: 10px; }
    .upload-area input { position: absolute; inset: 0; opacity: 0; cursor: pointer; }
    .upload-preview { 
        display: none; 
        margin-top: 1rem; 
        width: 100%; 
        max-height: 200px; 
        object-fit: contain; 
        border-radius: 12px; 
        border: 1px solid #eee;
    }

    .checkout-navigation {
        display: flex;
        gap: 1rem;
        margin-top: 2rem;
        padding-top: 1.5rem;
        border-top: 1px solid #f0f0f0;
    }

    .is-invalid { border-color: #ef4444 !important; background-color: #fef2f2 !important; }

    @keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }

    @media (max-width: 600px) {
        #checkout-form-wizard { padding: 1.2rem; border-radius: 0; }
        .checkout-navigation { flex-direction: column-reverse; }
    }

    /* Grid Utilities */
    .grid-cols-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; }
    .grid-cols-auto-fit-240 { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; }
    .grid-cols-auto-fit-180 { display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem; }
    
    .rider-disclaimer {
        background: #fdf8f3;
        padding: 1.2rem;
        border-radius: 18px;
        font-size: 0.9rem;
        margin: 1.5rem 0;
        display: flex;
        align-items: flex-start;
        gap: 1rem;
        color: var(--text-dark);
        border: 1px dashed var(--primary);
    }
    .rider-disclaimer .material-symbols-rounded {
        font-size: 1.4rem;
        color: var(--primary);
        flex-shrink: 0;
    }

    /* Utility for better radio/checkbox alignment */
    .form-check {
        display: flex;
        align-items: center;
        gap: 10px;
        cursor: pointer;
        padding: 10px 0;
    }

    /* Consistent Copy Button Size */
    .btn-xs {
        padding: 4px 10px;
        font-size: 0.7rem;
        height: auto;
        border-radius: 8px;
    }

    .timer-banner {
        background: var(--secondary);
        color: white;
        text-align: center;
        padding: 8px;
        font-weight: 700;
        border-radius: 12px;
        margin-bottom: 1.5rem;
    }

    /* Processing Overlay */
    #processing-overlay {
        position: fixed;
        inset: 0;
        background: rgba(255, 255, 255, 0.9);
        backdrop-filter: blur(8px);
        z-index: 10000;
        display: none;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        text-align: center;
    }
    .loading-spinner {
        width: 60px;
        height: 60px;
        border: 6px solid var(--accent);
        border-top: 6px solid var(--primary);
        border-radius: 50%;
        animation: spin 1s linear infinite;
        margin-bottom: 1.5rem;
    }
    @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
</style>
<script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>

<section class="section">
    <div class="container-sm">
        <div id="checkout-timer" class="timer-banner">
            Checkout expires in <span id="time-left">20:00</span>
        </div>

        <div id="checkout-form-wizard">
            <ul class="checkout-steps-indicator">
                <li class="step" data-step="0">Personal</li>
                <li class="step" data-step="1">Delivery</li>
                <li class="step" data-step="3">Payment</li>
                <li class="step" data-step="4">Summary</li>
            </ul>

            <div id="checkout-error-banner" class="inline-error-banner">
                <span class="material-symbols-rounded">error</span>
                <div class="error-text"></div>
            </div>

            <form method="POST" enctype="multipart/form-data" id="checkoutForm">
                <?= csrfField() ?>

                <!-- Step 0: Customer Info -->
                <div class="checkout-step" id="step-info">
                    <div class="section-content">
                        <h3><span class="material-symbols-rounded">person</span> Your Information</h3>
                        <div class="form-group">
                            <label>Full Name *</label>
                            <input type="text" name="customer_name" class="form-control" required placeholder="Enter your full name" value="<?= sanitize($checkoutData['customer_name'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Phone Number *</label>
                            <input type="tel" name="customer_phone" class="form-control" required placeholder="e.g. 0911223344" value="<?= sanitize($checkoutData['customer_phone'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label>Email Address</label>
                            <input type="email" name="customer_email" class="form-control" placeholder="For order updates (optional)" value="<?= sanitize($checkoutData['customer_email'] ?? '') ?>">
                        </div>
                    </div>
                    <div class="checkout-navigation">
                        <button type="button" class="btn btn-primary btn-lg btn-nav" data-nav="next">Next: Delivery</button>
                    </div>
                </div>

                <!-- Step 1: Delivery & Schedule -->
                <div class="checkout-step" id="step-delivery-schedule">
                    <div class="section-content">
                        <h3><span class="material-symbols-rounded">local_shipping</span> Delivery & Schedule</h3>
                        <h4 style="font-size: 1.2rem; margin-top: 2.5rem; margin-bottom: 1.5rem; color: var(--secondary);">
                            <span class="material-symbols-rounded">event_available</span> When would you like it?
                        </h4>
                        <div class="grid-cols-2 mb-3" style="margin-bottom:1rem;">
                            <div class="form-group">
                                <label>Preferred Date *</label>
                                <input type="text" name="preferred_date" id="datePicker" class="form-control" placeholder="Select date" required value="<?= sanitize($checkoutData['preferred_date'] ?? '') ?>">
                            </div>
                            <div class="form-group">
                                <label>Preferred Time *</label>
                                <select name="preferred_time" id="timePicker" class="form-control" required>
                                    <option value="">-- Pick Time --</option>
                                    <?php foreach ($pickupTimes as $pt): ?>
                                    <option value="<?= htmlspecialchars($pt['time_label']) ?>" data-id="<?= $pt['id'] ?>" <?= (($checkoutData['preferred_time'] ?? '') === $pt['time_label']) ? 'selected' : '' ?>><?= htmlspecialchars($pt['time_label']) ?></option>
                                    <?php endforeach; ?>
                                 </select>
                            </div>
                        </div>
                        <div class="grid-cols-auto-fit-180 mb-2">
                            <?php if (($settings['logistics_pickup'] ?? '0') === '1'): ?>
                            <label class="variant-option">
                                <input type="radio" name="logistics_mode" value="pickup" id="logPickup" <?= (($checkoutData['logistics_mode'] ?? 'pickup') === 'pickup') ? 'checked' : '' ?> onchange="updateTotals()">
                                <div class="selection-indicator"><span class="material-symbols-rounded" style="font-size:14px;">check</span></div>
                                <div class="icon-box"><span class="material-symbols-rounded">storefront</span></div>
                                <div>
                                    <strong style="display:block;">Pickup</strong>
                                    <span class="text-xs text-muted">From our shop</span>
                                </div>
                            </label>
                            <?php endif; ?>

                            <?php if (($settings['logistics_pickup'] ?? '0') === '1'): ?>
                            <div id="pickupInfo" class="rider-disclaimer" style="display:none; grid-column: span 2;">
                                <span class="material-symbols-rounded">location_on</span>
                                <div>
                                    <strong><?= sanitize($settings['pickup_location_name'] ?? 'Our Shop') ?></strong><br>
                                    <a href="<?= sanitize($settings['pickup_location_link'] ?? '#') ?>" target="_blank" class="text-xs">View on Google Maps</a>
                                </div>
                            </div>
                            <?php endif; ?>
                            <?php if (($settings['logistics_delivery_fixed'] ?? '0') === '1'): ?>
                            <label class="variant-option">
                                <input type="radio" name="logistics_mode" value="delivery_fixed" id="logFixed" <?= (($checkoutData['logistics_mode'] ?? '') === 'delivery_fixed') ? 'checked' : '' ?> onchange="updateTotals()">
                                <div class="selection-indicator"><span class="material-symbols-rounded" style="font-size:14px;">check</span></div>
                                <div class="icon-box"><span class="material-symbols-rounded">delivery_dining</span></div>
                                <div>
                                    <strong style="display:block;">Delivery</strong>
                                    <span class="text-xs text-muted"><?= formatPrice($deliveryFee) ?> flat fee</span>
                                </div>
                            </label>
                            <?php endif; ?>
                            <?php if (($settings['logistics_delivery_rider'] ?? '0') === '1'): ?>
                            <label class="variant-option">
                                <input type="radio" name="logistics_mode" value="delivery_rider" id="logRider" <?= (($checkoutData['logistics_mode'] ?? '') === 'delivery_rider') ? 'checked' : '' ?> onchange="updateTotals()">
                                <div class="selection-indicator"><span class="material-symbols-rounded" style="font-size:14px;">check</span></div>
                                <div class="icon-box"><span class="material-symbols-rounded">directions_car</span></div>
                                <div>
                                    <strong style="display:block;">Delivery</strong>
                                    <span class="text-xs text-muted">Paid to driver</span>
                                </div>
                            </label>
                            <?php endif; ?>
                        </div>

                        <div id="riderDisclaimer" class="rider-disclaimer" style="display:none;">
                            <span class="material-symbols-rounded">info</span>
                            <?= sanitize($settings['rider_disclaimer'] ?? 'Delivery fee will be paid directly to the rider upon arrival.') ?>
                        </div>

                        <div id="addressFields" style="display:none;">
                            <div class="form-group">
                                <label>Delivery Address *</label>
                                <textarea name="customer_address" id="customerAddress" class="form-control" rows="2" placeholder="Street, Building, Unit #"><?= sanitize($checkoutData['customer_address'] ?? '') ?></textarea>
                            </div>
                            <div class="form-group">
                                <label>Google Maps Link (Recommended)</label>
                                <div class="flex-gap-0-5">
                                    <input type="url" name="google_maps_link" class="form-control" placeholder="https://maps.app.goo.gl/..." value="<?= sanitize($checkoutData['google_maps_link'] ?? '') ?>">
                                    <a href="https://www.google.com/maps" target="_blank" class="btn btn-outline btn-sm map-button" style="padding:0.5rem; margin-top:1rem;">
                                        <span class="material-symbols-rounded">map</span> Open Maps
                                    </a>
                                </div>
                                <small class="text-muted">Help the Driver find you faster by pinning your location.</small>
                            </div>
                        </div>

                      

                        <div class="form-group mt-2" id="agreementCheckboxContainer" style="display:none; border-top:1px solid #eee; padding-top:1.5rem;">
                            <label class="form-check">
                                <input type="checkbox" id="agreeConditions" required>
                                <span class="text-sm">I agree by the delivery fee payment policy. *</span>
                            </label>
                        </div>

                    </div><!-- /section-content -->
                    <div class="checkout-navigation">
                        <button type="button" class="btn btn-outline btn-lg btn-nav" data-nav="prev">Previous</button>
                        <button type="button" class="btn btn-primary btn-lg btn-nav" data-nav="next">Next: Payment</button>
                    </div>
                </div>
                
                <!-- Step 3: Payment -->
                <div class="checkout-step" id="step-payment">
                    <div class="settings-section">
                        <h3><span class="material-symbols-rounded">account_balance_wallet</span> Payment Method</h3>
                        <div class="grid-cols-1 mb-4">
                            <?php if (($settings['payment_cod'] ?? '0') === '1'): ?>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="cod" <?= (($checkoutData['payment_method'] ?? 'cod') === 'cod') ? 'checked' : '' ?> onchange="togglePaymentProof()">
                                <div class="selection-indicator"><span class="material-symbols-rounded" style="font-size:14px;">check</span></div>
                                <div class="icon-box"><span class="material-symbols-rounded">payments</span></div>
                                <div>
                                    <strong style="display:block;">Cash on Delivery</strong>
                                    <span class="text-xs text-muted">Pay at handover</span>
                                </div>
                            </label>
                            <?php endif; ?>
                            <?php if (($settings['payment_transfer'] ?? '0') === '1'): ?>
                            <label class="payment-option">
                                <input type="radio" name="payment_method" value="transfer" <?= (($checkoutData['payment_method'] ?? '') === 'transfer') ? 'checked' : '' ?> onchange="togglePaymentProof()">
                                <div class="selection-indicator"><span class="material-symbols-rounded" style="font-size:14px;">check</span></div>
                                <div class="icon-box"><span class="material-symbols-rounded">account_balance</span></div>
                                <div>
                                    <strong style="display:block;">Bank Transfer</strong>
                                    <span class="text-xs text-muted">Verify via Receipt</span>
                                </div>
                            </label>
                            <?php endif; ?>
                        </div>

                        <div id="bankDetails" style="display:none;margin-top:1rem;">
                            <p class="text-sm fw-700 mb-3" style="color:var(--secondary);">1. SELECT YOUR BANK</p>
                            <div class="grid-cols-auto-fit-240 mb-2">
                                <?php
                                $banks = $db->query("SELECT * FROM bank_accounts WHERE is_active = 1 ORDER BY sort_order")->fetchAll();
                                foreach ($banks as $bank):
                                ?>
                                <label class="bank-card">
                                    <input type="radio" name="selected_bank_id" value="<?= $bank['id'] ?>" <?= ((int)($checkoutData['selected_bank_id'] ?? 0) === $bank['id']) ? 'checked' : '' ?>>
                                    <div class="selection-indicator"><span class="material-symbols-rounded" style="font-size:14px;">check</span></div>
                                    <div class="icon-box"><span class="material-symbols-rounded">account_balance_wallet</span></div>
                                    <div>
                                        <div class="bank-card-name">
                                            <?= sanitize($bank['bank_name']) ?>
                                        </div>
                                        <div class="bank-account-row mt-1">
                                            <code><?= sanitize($bank['account_number']) ?></code>
                                            <button type="button" class="btn btn-outline btn-xs" onclick="copyText('<?= sanitize($bank['account_number']) ?>', this)">Copy</button>
                                        </div>
                                        <div class="text-xs text-muted mt-1"><?= sanitize($bank['account_name']) ?></div>
                                    </div>
                                </label>
                                <?php endforeach; ?>
                            </div>

                            <p class="text-sm fw-700 mt-4 mb-3" style="color:var(--secondary);">2. UPLOAD SCREENSHOOT</p>
                            <div class="form-group">
                                <div class="upload-area" id="dropZone">
                                    <span class="material-symbols-rounded">cloud_upload</span>
                                    <p class="fw-600">Upload Receipt Screenshot</p>
                                    <p class="text-xs text-muted">JPG, PNG or WebP supported</p>
                                    <input type="file" name="payment_proof" id="paymentProofInput" accept="image/*" onchange="previewImage(this)">
                                    <img id="uploadPreview" class="upload-preview">
                                </div>
                            </div>

                            <div class="form-group transaction-confirm-group">
                                <label>3. RECEIPT LINK </label>
                                <input type="text" name="confirmed_transaction_id" id="transactionIdField" class="form-control" placeholder="Paste receipt link from the SMS the bank sent" value="<?= sanitize($checkoutData['confirmed_transaction_id'] ?? '') ?>">
                                <small class="text-muted">Paste your bank SMS link here for faster approval (optional but helpful!).</small>
                            </div>
                        </div>
                    </div>
                    <div class="checkout-navigation">
                        <button type="button" class="btn btn-outline btn-lg btn-nav" data-nav="prev">Previous</button>
                        <button type="button" class="btn btn-primary btn-lg btn-nav" data-nav="next">Next: Summary</button>
                    </div>
                </div>

                <!-- Step 4: Order Summary -->
                <div class="checkout-step" id="step-summary">
                    <div class="settings-section">
                        <h3><span class="material-symbols-rounded">shopping_bag</span> Order Summary</h3>
                        <div class="summary-list">
                            <?php foreach ($cart as $item): ?>
                            <div class="d-flex justify-between py-1 border-b">
                                <span class="text-sm"><?= $item['quantity'] ?>× <?= sanitize($item['product_name']) ?></span>
                                <span class="fw-600"><?= formatPrice($item['line_total']) ?></span>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <div class="mt-2">
                            <div class="d-flex justify-between text-sm">
                                <span>Subtotal</span>
                                <span><?= formatPrice($subtotal) ?></span>
                            </div>
                            <div class="d-flex justify-between text-sm" id="deliveryRow" style="display:none;">
                                <span>Delivery Fee</span>
                                <span id="deliveryFeeDisplay"><?= formatPrice($deliveryFee) ?></span>
                            </div>
                            <div class="d-flex justify-between fw-700" style="font-size:1.25rem;margin-top:0.75rem;padding-top:0.75rem;border-top:2px solid var(--dark);">
                                <span>Total</span>
                                <span id="totalDisplay"><?= formatPrice($subtotal) ?></span>
                            </div>
                        </div>

                        <?php if (($settings['deposit_required'] ?? '0') === '1'): ?>
                        <div class="mt-2 p-2 bg-accent rounded" id="depositInfo">
                            <div class="d-flex justify-between text-xs">
                                <span>Deposit Due Now (<?= $settings['deposit_percentage'] ?? 50 ?>%)</span>
                                <span class="fw-700" id="depositDisplay"></span>
                            </div>
                            <div class="d-flex justify-between text-xs mt-1">
                                <span>Balance Due at Handover</span>
                                <span class="fw-700" id="balanceDisplay"></span>
                            </div>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="checkout-navigation">
                        <button type="button" class="btn btn-outline btn-lg btn-nav" data-nav="prev">Previous</button>
                        <button type="submit" class="btn btn-primary btn-lg btn-nav" id="placeOrderBtn">
                            <span class="material-symbols-rounded">check_circle</span> ✨ Confirm & Place Order
                        </button>
                    </div>
                </div>
            </form>
        </div><!-- /#checkout-form-wizard -->
    </div><!-- /.container-sm -->

    <div id="processing-overlay">
        <div class="loading-spinner"></div>
        <h2 style="color: var(--secondary); font-weight: 800;">Processing Your Order</h2>
        <p style="color: var(--text-muted);">Please don't refresh or close this page.</p>
    </div>
</section>

<script>
const subtotal = <?= $subtotal ?>;
const fixedDeliveryFee = <?= $deliveryFee ?>;
const depositRequired = <?= ($settings['deposit_required'] ?? '0') === '1' ? 'true' : 'false' ?>;
const depositPct = parseFloat('<?= (float)($settings['deposit_percentage'] ?? 50) ?>');
const currencySymbol = '<?= $settings['currency_symbol'] ?? '$' ?>';

// Combine specific blocked dates and recurring blocked days of week for Flatpickr
const flatpickrDisable = <?= json_encode($blockedDates) ?>;
const blockedDaysOfWeek = <?= json_encode($blockedDaysOfWeek) ?>;

const finalDisableArray = [...flatpickrDisable];
if (blockedDaysOfWeek.length > 0) {
    finalDisableArray.push(function(date) {
        return blockedDaysOfWeek.includes(date.getDay());
    });
}

let timeLeft = 1200 - (<?= time() - ($_SESSION['checkout_started_at'] ?? time()) ?>);
const checkoutSteps = document.querySelectorAll('.checkout-step'); // Now 4 steps
const stepIndicators = document.querySelectorAll('.checkout-steps-indicator .step');
let currentStep = 0;

function fmt(n) { return currencySymbol + n.toFixed(2); }

// Timer Functionality
const timerInterval = setInterval(() => {
    if (timeLeft <= 0) {
        // Clear all storage on expiry
        sessionStorage.removeItem('checkout_progress');
        clearInterval(timerInterval);
        window.location.reload(); // PHP will handle the redirect
    }
    const mins = Math.floor(timeLeft / 60);
    const secs = timeLeft % 60;
    document.getElementById('time-left').textContent = `${mins.toString().padStart(2, '0')}:${secs.toString().padStart(2, '0')}`;
    timeLeft--;
}, 1000);

function showError(message) {
    const banner = document.getElementById('checkout-error-banner');
    banner.querySelector('.error-text').innerHTML = message;
    banner.style.display = 'flex';
    window.scrollTo({ top: banner.offsetTop - 100, behavior: 'smooth' });
}

function hideError() {
    document.getElementById('checkout-error-banner').style.display = 'none';
}

function showStep(stepIndex) {
    checkoutSteps.forEach((step, index) => {
        step.classList.toggle('active', index === stepIndex);
    });
    stepIndicators.forEach((indicator, index) => {
        indicator.classList.toggle('active', index === stepIndex);
        if (index < stepIndex) {
            indicator.classList.add('completed');
        } else {
            indicator.classList.remove('completed');
        }
    });

    // Handle navigation button visibility
    const prevBtn = document.querySelector('[data-nav="prev"]');
    const nextBtn = document.querySelector('[data-nav="next"]');
    const submitBtn = document.getElementById('placeOrderBtn');

    if (prevBtn) prevBtn.style.display = (stepIndex === 0) ? 'none' : 'flex';
    if (nextBtn) nextBtn.style.display = (stepIndex === checkoutSteps.length - 1) ? 'none' : 'flex';
    if (submitBtn) submitBtn.style.display = (stepIndex === checkoutSteps.length - 1) ? 'flex' : 'none';
}

function validateStep(stepIndex) {
    let isValid = true;
    hideError();
    const currentStepElement = checkoutSteps[stepIndex];
    const requiredInputs = currentStepElement.querySelectorAll('[required]');

    requiredInputs.forEach(input => {
        if (!input.value.trim()) {
            input.classList.add('is-invalid'); // Add a class for visual feedback
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });

    // Specific validation for Logistics step (Step 1)
    if (stepIndex === 1) { // Combined Delivery & Schedule step
        const logisticsMode = currentStepElement.querySelector('input[name="logistics_mode"]:checked')?.value;
        const agreementCheckboxContainer = document.getElementById('agreementCheckboxContainer');

        // Only validate agreement if it's visible (i.e., not pickup)
        if (agreementCheckboxContainer.style.display !== 'none') {
            const agreeBtn = document.getElementById('agreeConditions');
            if (!agreeBtn.checked) { showError("You must agree to the Terms & Conditions to proceed."); isValid = false; }
        }

        // Validate preferred date and time for this combined step
        const prefDate = document.getElementById('datePicker');
        const prefTime = currentStepElement.querySelector('select[name="preferred_time"]');
        if (!prefDate.value.trim()) { prefDate.classList.add('is-invalid'); isValid = false; } else { prefDate.classList.remove('is-invalid'); }
        if (!prefTime.value.trim()) { prefTime.classList.add('is-invalid'); isValid = false; } else { prefTime.classList.remove('is-invalid'); }

        // Check if the selected date is blocked
        const selectedDate = prefDate.value;
        if (selectedDate) {
            const selectedDayOfWeek = new Date(selectedDate).getDay(); 
            if (flatpickrDisable.includes(selectedDate) || blockedDaysOfWeek.includes(selectedDayOfWeek)) {
                showError("The selected date is unavailable. Please choose another date.");
                isValid = false;
            }
        }
    }

    // Specific validation for Payment step (Step 2)
   if (stepIndex === 2) {
        const selectedPaymentMethod = currentStepElement.querySelector('input[name="payment_method"]:checked');
        
        // 1. Force them to explicitly select a payment method
        if (!selectedPaymentMethod) {
            showError("Please select a payment method to proceed.");
            isValid = false;
        } else {
            const paymentMethod = selectedPaymentMethod.value;
            
            // 2. Enforce bank card selection and proof if Bank Transfer is chosen
            if (paymentMethod === 'transfer' || paymentMethod === 'full') {
                const selectedBank = currentStepElement.querySelector('input[name="selected_bank_id"]:checked');
                const proofInput = document.getElementById('paymentProofInput');

                if (!selectedBank) {
                    showError("Please select the bank you transferred to.");
                    isValid = false;
                } else if (!proofInput.files.length) {
                    showError("Please upload your payment proof screenshot.");
                    isValid = false;
                }
            }
        }
    }

    if (!isValid && !document.getElementById('checkout-error-banner').style.display) {
        showError("Please fill in all required fields marked with *");
    }

    return isValid;
}

function nextStep() {
    // Final step form submission logic
    if (currentStep === checkoutSteps.length - 1) {
        return; // Let the form handle submission
    }
    if (validateStep(currentStep)) {
        currentStep++;
        if (currentStep < checkoutSteps.length) {
            showStep(currentStep);
            // Re-call updateTotals and togglePaymentProof as step changes
            updateTotals();
            togglePaymentProof();
            saveProgress();
        }
    }
}

function prevStep() {
    hideError();
    currentStep--;
    if (currentStep >= 0) {
        showStep(currentStep);
        updateTotals();
        togglePaymentProof();
        saveProgress();
    }
}

function saveProgress() {
    const formData = new FormData(document.getElementById('checkoutForm'));
    const data = {};
    formData.forEach((value, key) => {
        if (key !== 'csrf_token' && key !== 'payment_proof') {
            data[key] = value;
        }
    });
    data['currentStep'] = currentStep;
    sessionStorage.setItem('checkout_progress', JSON.stringify(data));
}

function loadProgress() {
    const saved = sessionStorage.getItem('checkout_progress');
    if (!saved) return;
    const data = JSON.parse(saved);
    
    for (const key in data) {
        if (key === 'currentStep') {
            currentStep = parseInt(data[key]);
            continue;
        }
        const elements = document.getElementsByName(key);
        if (elements.length > 0) {
            const el = elements[0];
            if (el.type === 'radio') {
                const radio = document.querySelector(`input[name="${key}"][value="${data[key]}"]`);
                if (radio) radio.checked = true;
            } else if (el.type === 'checkbox') {
                el.checked = true;
            } else {
                el.value = data[key];
            }
        }
    }
}

document.getElementById('checkoutForm').addEventListener('submit', function(e) {
    if (!validateStep(currentStep)) {
        e.preventDefault();
        showError("There are some errors in your form. Please review.");
    } else {
        document.getElementById('processing-overlay').style.display = 'flex';
        sessionStorage.removeItem('checkout_progress');
    }
});

// Attach event listeners for navigation buttons
document.querySelectorAll('[data-nav="next"]').forEach(button => {
    button.addEventListener('click', nextStep);
});

document.querySelectorAll('[data-nav="prev"]').forEach(button => {
    button.addEventListener('click', prevStep);
});


function updateTotals() {
    const mode = document.querySelector('input[name="logistics_mode"]:checked')?.value || 'pickup';
    const deliveryRow = document.getElementById('deliveryRow');
    const addressFields = document.getElementById('addressFields');
    const addressInput = document.getElementById('customerAddress');
    const riderDisclaimer = document.getElementById('riderDisclaimer');
    const pickupInfo = document.getElementById('pickupInfo');
    const agreementCheckboxContainer = document.getElementById('agreementCheckboxContainer');
    
    let fee = 0;
    if (mode === 'delivery_fixed') {
        fee = fixedDeliveryFee;
    }
    
    // Toggle visibility of delivery-related fields
    if (mode !== 'pickup') {
        deliveryRow.style.display = 'flex';
        addressFields.style.display = 'block';
        addressInput.required = true;
        agreementCheckboxContainer.style.display = 'block'; // Show agreement for delivery
        if (pickupInfo) pickupInfo.style.display = 'none';
    } else {
        deliveryRow.style.display = 'none';
        addressFields.style.display = 'none';
        addressInput.required = false;
        agreementCheckboxContainer.style.display = 'none'; // Hide agreement for pickup
        if (pickupInfo) pickupInfo.style.display = 'flex';
        document.getElementById('agreeConditions').checked = false; // Uncheck if hidden
    }
    document.getElementById('agreeConditions').required = (mode !== 'pickup'); // Make required only for delivery

    if (riderDisclaimer) {
        riderDisclaimer.style.display = (mode === 'delivery_rider') ? 'block' : 'none';
    }
    
    document.getElementById('deliveryFeeDisplay').textContent = (mode === 'delivery_rider') ? 'Paid to rider' : fmt(fee);
    
    const total = subtotal + fee;
    document.getElementById('totalDisplay').textContent = fmt(total);
    
    if (depositRequired) {
        const deposit = Math.round(total * (depositPct / 100) * 100) / 100;
        const balance = Math.round((total - deposit) * 100) / 100; // Ensure balance is also rounded
        document.getElementById('depositDisplay').textContent = fmt(deposit);
        document.getElementById('balanceDisplay').textContent = fmt(balance);
    }
}

function togglePaymentProof() {
    const method = document.querySelector('input[name="payment_method"]:checked')?.value;
    const bankDetails = document.getElementById('bankDetails');
    const proofInput = document.getElementById('paymentProofInput');
    const transactionIdField = document.getElementById('transactionIdField');
    
    const isTransfer = (method === 'transfer' || method === 'full');
    bankDetails.style.display = isTransfer ? 'block' : 'none';
    proofInput.required = isTransfer;
    transactionIdField.required = false;
}

function previewImage(input) {
    if (!input.files || !input.files[0]) return;
    const preview = document.getElementById('uploadPreview');
    
    const reader = new FileReader();
    reader.onload = function(e) {
        preview.src = e.target.result;
        preview.style.display = 'block';
        input.parentElement.querySelector('.material-symbols-rounded').style.display = 'none';
        input.parentElement.querySelectorAll('p').forEach(p => p.style.display = 'none');
    }
    reader.readAsDataURL(input.files[0]);
}

// Function to handle copying text to clipboard
function copyText(text, buttonElement) {
    navigator.clipboard.writeText(text).then(function() {
        const originalText = buttonElement.textContent;
        buttonElement.textContent = 'Copied!';
        setTimeout(function() {
            buttonElement.textContent = originalText;
        }, 1500);
    }, function(err) {
        console.error('Could not copy text: ', err);
        alert('Failed to copy text. Please copy manually.');
    });
}


// Initialize Flatpickr
// Initialize Flatpickr
const fp = flatpickr("#datePicker", {
    minDate: "<?= $nextDate ?>",
    disable: finalDisableArray, 
    dateFormat: "Y-m-d", 
    altInput: true,
    altFormat: "F j, Y",
    disableMobile: true,
    // 1. Force Flatpickr to read whatever value was restored by loadProgress()
    defaultDate: document.getElementById('datePicker').value || null,
    onChange: function(selectedDates, dateStr, instance) { 
        saveProgress();
        updatePickupTimes(dateStr); }
});

// 2. Double-check after rendering to ensure session value is forced into Flatpickr
document.addEventListener('DOMContentLoaded', () => {
    const savedDate = document.getElementById('datePicker').value;
    if (savedDate && fp) {
        fp.setDate(savedDate, false); // Restores both the backend value and the pretty display text
    }
});

function updatePickupTimes(date) {
    if (!date) return;
    fetch(`checkout.php?check_availability=1&date=${date}`)
        .then(response => response.json())
        .then(data => {
            const timePicker = document.getElementById('timePicker');
            const options = timePicker.querySelectorAll('option');
            const fullSlotIds = data.full_slots.map(id => id.toString());
            
            options.forEach(opt => {
                if (opt.value === "") return;
                const slotId = opt.getAttribute('data-id');
                const originalLabel = opt.textContent.replace(' (FULLY BOOKED)', '');
                
                if (fullSlotIds.includes(slotId)) {
                    opt.disabled = true;
                    opt.textContent = originalLabel + ' (FULLY BOOKED)';
                } else {
                    opt.disabled = false;
                    opt.textContent = originalLabel;
                }
            });
        });
}

// Initial setup
loadProgress();
showStep(currentStep);
updateTotals();
togglePaymentProof();
if (document.getElementById('datePicker').value) {
    updatePickupTimes(document.getElementById('datePicker').value);
}

// Listen for inputs to save progress
document.getElementById('checkoutForm').addEventListener('input', saveProgress);
document.getElementById('checkoutForm').addEventListener('change', saveProgress);

/**
 * Bridge Server-side errors to UI Banner
 */
document.addEventListener('DOMContentLoaded', () => {
    const serverError = <?= json_encode($checkoutError) ?>;
    if (serverError) {
        // Ensure we are on the relevant step for payment if it's a verification error
        if (serverError.includes('Verification') || serverError.includes('transaction')) { 
            currentStep = 2; // Step 3 (Payment) is index 2
            showStep(2); 
        }
        showError(serverError);
    }
});
</script>
