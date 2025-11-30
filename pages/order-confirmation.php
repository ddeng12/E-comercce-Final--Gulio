<?php
require_once 'includes/cart.php';
require_once 'includes/paystack.php';
require_once 'includes/orders.php';
require_once 'includes/helpers.php';

$orderId = $_GET['order_id'] ?? null;
$reference = $_GET['reference'] ?? null;

if (!$orderId || !$reference) {
    header('Location: ?page=cart');
    exit;
}

// Get pending order from session
$pendingOrder = $_SESSION['pending_order'] ?? null;

if (!$pendingOrder || $pendingOrder['order_id'] != $orderId) {
    header('Location: ?page=cart');
    exit;
}

// Verify payment with Paystack
$paystack = new PaystackPayment();
$paymentVerification = $paystack->verifyPayment($reference);

$paymentSuccessful = false;
$paymentDetails = null;

if ($paymentVerification && $paymentVerification['status'] && $paymentVerification['data']['status'] === 'success') {
    $paymentSuccessful = true;
    $paymentDetails = $paymentVerification['data'];
    
    // Update order in database
    $db = Database::getInstance();
    $updateSql = "
        UPDATE orders 
        SET payment_status = 'paid',
            payment_reference = :reference,
            payment_method = :payment_method,
            status = 'confirmed'
        WHERE id = :order_id
    ";
    
    $db->query($updateSql, [
        'reference' => $reference,
        'payment_method' => $paymentDetails['channel'] ?? 'paystack',
        'order_id' => $orderId
    ]);
    
    // Clear the cart after successful payment
    $cart = new ShoppingCart();
    $cart->clear();
    
    // Clear pending order from session
    unset($_SESSION['pending_order']);
    
    // Log successful order
    Logger::info('Order completed successfully', [
        'order_id' => $orderId,
        'payment_reference' => $reference,
        'amount' => $paymentDetails['amount'] / 100,
        'customer_email' => $paymentDetails['customer']['email']
    ]);
    
    // Set success message in session for display on cart page
    $_SESSION['order_success'] = [
        'order_id' => $orderId,
        'amount' => $paymentDetails['amount'] / 100,
        'message' => 'Payment successful! Your order has been confirmed.'
    ];
}

// Get order from database
$order = getOrderById($orderId);
if (!$order) {
    header('Location: ?page=cart');
    exit;
}

// If payment was successful, redirect to cart page after a brief success message
if ($paymentSuccessful) {
    ?>
    <div class="success-redirect-container">
        <div class="success-message">
            <div class="success-icon">
                <i class="fas fa-check-circle"></i>
            </div>
            <h1>Payment Successful!</h1>
            <p>Thank you for your order #<?= htmlspecialchars($orderId) ?>!</p>
            <p>Redirecting you to your cart...</p>
            <div class="loading-spinner">
                <i class="fas fa-spinner fa-spin"></i>
            </div>
        </div>
    </div>
    
    <script>
        // Redirect to cart page after 3 seconds
        setTimeout(function() {
            window.location.href = '?page=cart';
        }, 3000);
    </script>
    
    <style>
    .success-redirect-container {
        display: flex;
        align-items: center;
        justify-content: center;
        min-height: 80vh;
        padding: 20px;
    }
    
    .success-message {
        text-align: center;
        background: white;
        padding: 60px 40px;
        border-radius: 20px;
        box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        max-width: 500px;
    }
    
    .success-icon {
        font-size: 80px;
        color: #27ae60;
        margin-bottom: 30px;
    }
    
    .success-message h1 {
        font-size: 32px;
        color: #333;
        margin-bottom: 15px;
    }
    
    .success-message p {
        font-size: 18px;
        color: #666;
        margin-bottom: 15px;
    }
    
    .loading-spinner {
        font-size: 24px;
        color: var(--primary-orange);
        margin-top: 30px;
    }
    </style>
    
    <?php
    exit; // Don't show the rest of the page
}
?>

<div class="confirmation-container">
    <div class="confirmation-wrapper">
        
        <?php if ($paymentSuccessful): ?>
            <!-- Success Message -->
            <div class="success-header">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <h1>Payment Successful!</h1>
                <p>Thank you for your order. Your payment has been processed successfully.</p>
            </div>
            
            <!-- Order Details -->
            <div class="order-details">
                <div class="order-summary-card">
                    <h2>Order Summary</h2>
                    
                    <div class="order-info">
                        <div class="info-row">
                            <span class="label">Order ID:</span>
                            <span class="value">#<?= htmlspecialchars($orderId) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Payment Reference:</span>
                            <span class="value"><?= htmlspecialchars($reference) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Order Date:</span>
                            <span class="value"><?= date('F j, Y \a\t g:i A', strtotime($order['created_at'])) ?></span>
                        </div>
                        <div class="info-row">
                            <span class="label">Payment Method:</span>
                            <span class="value"><?= ucfirst($order['payment_method']) ?></span>
                        </div>
                    </div>
                </div>

                <!-- Customer Information -->
                <div class="customer-info-card">
                    <h3>Customer Information</h3>
                    <div class="customer-details">
                        <p><strong>Name:</strong> <?= htmlspecialchars($order['customer_info']['firstName'] . ' ' . $order['customer_info']['lastName']) ?></p>
                        <p><strong>Email:</strong> <?= htmlspecialchars($order['customer_info']['email']) ?></p>
                        <p><strong>Phone:</strong> <?= htmlspecialchars($order['customer_info']['phone']) ?></p>
                    </div>
                </div>

                <!-- Shipping Information -->
                <div class="shipping-info-card">
                    <h3>Shipping Information</h3>
                    <div class="shipping-details">
                        <p><strong>Address:</strong> <?= htmlspecialchars($order['shipping_address']['address']) ?></p>
                        <p><strong>City:</strong> <?= htmlspecialchars($order['shipping_address']['city']) ?></p>
                        <p><strong>Region:</strong> <?= htmlspecialchars($order['shipping_address']['region']) ?></p>
                        <?php if (!empty($order['shipping_address']['landmark'])): ?>
                            <p><strong>Landmark:</strong> <?= htmlspecialchars($order['shipping_address']['landmark']) ?></p>
                        <?php endif; ?>
                        <p><strong>Delivery Method:</strong> <?= ucfirst(str_replace('_', ' ', $order['delivery_method'])) ?></p>
                        <?php if (!empty($order['instructions'])): ?>
                            <p><strong>Instructions:</strong> <?= htmlspecialchars($order['instructions']) ?></p>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Order Items -->
            <div class="order-items">
                <h3>Items Ordered</h3>
                <div class="items-list">
                    <?php foreach ($order['items'] as $item): ?>
                        <div class="order-item">
                            <div class="item-image">
                                <img src="<?= htmlspecialchars(getProductImageUrl($item['image_url'] ?? '')) ?>" 
                                     alt="<?= htmlspecialchars($item['product_name']) ?>"
                                     onerror="this.src='https://via.placeholder.com/80x80/f0f0f0/999999?text=No+Image';">
                            </div>
                            <div class="item-details">
                                <h4><?= htmlspecialchars($item['product_name']) ?></h4>
                                <?php if (!empty($item['variant_name'])): ?>
                                    <p class="item-variant"><?= htmlspecialchars($item['variant_name']) ?></p>
                                <?php endif; ?>
                                <p class="item-vendor">by <?= htmlspecialchars($item['vendor_name'] ?? 'Unknown Vendor') ?></p>
                            </div>
                            <div class="item-quantity">
                                <span>Qty: <?= $item['quantity'] ?></span>
                            </div>
                            <div class="item-price">
                                <span>GHS <?= number_format($item['total_price'], 2) ?></span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- Order Total -->
            <div class="order-total">
                <div class="total-breakdown">
                    <div class="total-row">
                        <span>Subtotal:</span>
                        <span>GHS <?= number_format($order['subtotal'], 2) ?></span>
                    </div>
                    <div class="total-row">
                        <span>Shipping:</span>
                        <span><?= $order['shipping_cost'] > 0 ? 'GHS ' . number_format($order['shipping_cost'], 2) : 'Free' ?></span>
                    </div>
                    <div class="total-row final">
                        <span>Total Paid:</span>
                        <span>GHS <?= number_format($order['total_amount'], 2) ?></span>
                    </div>
                </div>
            </div>

            <!-- Next Steps -->
            <div class="next-steps">
                <h3>What's Next?</h3>
                <div class="steps-list">
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <div class="step-content">
                            <h4>Confirmation Email</h4>
                            <p>You'll receive an order confirmation email shortly at <?= htmlspecialchars($order['customer_info']['email']) ?></p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-box"></i>
                        </div>
                        <div class="step-content">
                            <h4>Order Processing</h4>
                            <p>We'll start preparing your order within 24 hours</p>
                        </div>
                    </div>
                    <div class="step">
                        <div class="step-icon">
                            <i class="fas fa-truck"></i>
                        </div>
                        <div class="step-content">
                            <h4>Delivery</h4>
                            <p>Your order will be delivered according to the selected delivery method</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons">
                <a href="?page=products" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
                <a href="?page=home" class="btn btn-secondary">
                    <i class="fas fa-home"></i> Back to Home
                </a>
            </div>

        <?php else: ?>
            <!-- Payment Failed -->
            <div class="error-header">
                <div class="error-icon">
                    <i class="fas fa-times-circle"></i>
                </div>
                <h1>Payment Failed</h1>
                <p>Unfortunately, your payment could not be processed. Please try again.</p>
            </div>

            <div class="error-details">
                <p>Order ID: #<?= htmlspecialchars($orderId) ?></p>
                <p>If you continue to experience issues, please contact our support team.</p>
            </div>

            <div class="action-buttons">
                <a href="?page=checkout" class="btn btn-primary">
                    <i class="fas fa-credit-card"></i> Try Again
                </a>
                <a href="?page=cart" class="btn btn-secondary">
                    <i class="fas fa-shopping-cart"></i> Back to Cart
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.confirmation-container {
    max-width: 800px;
    margin: 0 auto;
    padding: 20px;
}

.success-header, .error-header {
    text-align: center;
    margin-bottom: 40px;
    padding: 40px 20px;
    border-radius: 12px;
}

.success-header {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: white;
}

.error-header {
    background: linear-gradient(135deg, #e74c3c, #c0392b);
    color: white;
}

.success-icon, .error-icon {
    font-size: 60px;
    margin-bottom: 20px;
}

.success-header h1, .error-header h1 {
    font-size: 32px;
    margin-bottom: 10px;
}

.success-header p, .error-header p {
    font-size: 18px;
    opacity: 0.9;
}

.order-details {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    margin-bottom: 30px;
}

.order-summary-card,
.customer-info-card,
.shipping-info-card {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.order-summary-card {
    grid-column: 1 / -1;
}

.order-summary-card h2,
.customer-info-card h3,
.shipping-info-card h3 {
    margin-bottom: 20px;
    color: #333;
}

.order-info {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 15px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.label {
    font-weight: 600;
    color: #666;
}

.value {
    color: #333;
    font-weight: 500;
}

.customer-details p,
.shipping-details p {
    margin-bottom: 8px;
    color: #666;
}

.order-items {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.order-items h3 {
    margin-bottom: 20px;
    color: #333;
}

.items-list {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.order-item {
    display: grid;
    grid-template-columns: 80px 1fr auto auto;
    gap: 15px;
    align-items: center;
    padding: 15px;
    border: 1px solid #f0f0f0;
    border-radius: 8px;
}

.item-image {
    width: 80px;
    height: 80px;
    border-radius: 8px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details h4 {
    margin-bottom: 5px;
    color: #333;
}

.item-variant {
    font-size: 12px;
    color: #667eea;
    background: #e6f3ff;
    padding: 2px 8px;
    border-radius: 12px;
    display: inline-block;
    margin-bottom: 5px;
}

.item-vendor {
    font-size: 14px;
    color: #666;
}

.item-quantity {
    text-align: center;
    font-weight: 600;
    color: #666;
}

.item-price {
    text-align: right;
    font-weight: 600;
    color: var(--primary-orange);
    font-size: 16px;
}

.order-total {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.total-breakdown {
    max-width: 300px;
    margin-left: auto;
}

.total-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.total-row.final {
    border-bottom: none;
    border-top: 2px solid #f0f0f0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-top: 10px;
    padding-top: 15px;
}

.next-steps {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
}

.next-steps h3 {
    margin-bottom: 20px;
    color: #333;
}

.steps-list {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.step {
    display: flex;
    align-items: center;
    gap: 15px;
}

.step-icon {
    width: 50px;
    height: 50px;
    background: var(--primary-orange);
    color: white;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 20px;
}

.step-content h4 {
    margin-bottom: 5px;
    color: #333;
}

.step-content p {
    color: #666;
    margin: 0;
}

.action-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
}

.btn {
    padding: 15px 30px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    font-size: 16px;
}

.btn-primary {
    background: var(--primary-orange);
    color: white;
}

.btn-primary:hover {
    background: #d35400;
    transform: translateY(-2px);
}

.btn-secondary {
    background: #f8f9fa;
    color: #333;
    border: 1px solid #ddd;
}

.btn-secondary:hover {
    background: #e9ecef;
}

.error-details {
    background: white;
    padding: 25px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    text-align: center;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .order-details {
        grid-template-columns: 1fr;
    }
    
    .order-info {
        grid-template-columns: 1fr;
    }
    
    .order-item {
        grid-template-columns: 60px 1fr;
        gap: 10px;
    }
    
    .item-quantity,
    .item-price {
        grid-column: 1 / -1;
        text-align: left;
        margin-top: 10px;
        padding-top: 10px;
        border-top: 1px solid #f0f0f0;
    }
    
    .action-buttons {
        flex-direction: column;
    }
    
    .step {
        flex-direction: column;
        text-align: center;
    }
}
</style>
