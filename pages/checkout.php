<?php
require_once 'includes/cart.php';
require_once 'includes/paystack.php';
require_once 'includes/helpers.php';

$cart = new ShoppingCart();
$cartItems = $cart->getItems();
$cartSummary = $cart->getSummary();

// Redirect to cart if empty
if (empty($cartItems)) {
    header('Location: ?page=cart');
    exit;
}

$user = $_SESSION['user'] ?? null;
?>

<div class="checkout-container">
    <div class="checkout-wrapper">
        <!-- Checkout Header -->
        <div class="checkout-header">
            <h1><i class="fas fa-credit-card"></i> Checkout</h1>
            <div class="checkout-steps">
                <div class="step active">
                    <span class="step-number">1</span>
                    <span class="step-label">Information</span>
                </div>
                <div class="step">
                    <span class="step-number">2</span>
                    <span class="step-label">Payment</span>
                </div>
                <div class="step">
                    <span class="step-number">3</span>
                    <span class="step-label">Confirmation</span>
                </div>
            </div>
        </div>

        <div class="checkout-content">
            <!-- Checkout Form -->
            <div class="checkout-form">
                <form id="checkoutForm">
                    <!-- Customer Information -->
                    <div class="form-section">
                        <h3><i class="fas fa-user"></i> Customer Information</h3>
                        
                        <?php if (!$user): ?>
                            <div class="login-prompt">
                                <p>Already have an account? <a href="?page=login&redirect=checkout">Sign in</a> for faster checkout</p>
                            </div>
                        <?php endif; ?>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="firstName">First Name *</label>
                                <input type="text" id="firstName" name="firstName" 
                                       value="<?= htmlspecialchars($user['name'] ?? '') ?>" required>
                            </div>
                            <div class="form-group">
                                <label for="lastName">Last Name *</label>
                                <input type="text" id="lastName" name="lastName" required>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="email">Email Address *</label>
                            <input type="email" id="email" name="email" 
                                   value="<?= htmlspecialchars($user['email'] ?? '') ?>" required>
                        </div>

                        <div class="form-group">
                            <label for="phone">Phone Number *</label>
                            <input type="tel" id="phone" name="phone" 
                                   value="<?= htmlspecialchars($user['phone'] ?? '') ?>" required>
                        </div>
                    </div>

                    <!-- Shipping Address -->
                    <div class="form-section">
                        <h3><i class="fas fa-map-marker-alt"></i> Shipping Address</h3>
                        
                        <div class="form-group">
                            <label for="address">Street Address *</label>
                            <input type="text" id="address" name="address" 
                                   placeholder="123 Main Street" required>
                        </div>

                        <div class="form-row">
                            <div class="form-group">
                                <label for="city">City *</label>
                                <input type="text" id="city" name="city" 
                                       value="Accra" required>
                            </div>
                            <div class="form-group">
                                <label for="region">Region *</label>
                                <select id="region" name="region" required>
                                    <option value="">Select Region</option>
                                    <option value="Greater Accra" selected>Greater Accra</option>
                                    <option value="Ashanti">Ashanti</option>
                                    <option value="Western">Western</option>
                                    <option value="Eastern">Eastern</option>
                                    <option value="Central">Central</option>
                                    <option value="Northern">Northern</option>
                                    <option value="Upper East">Upper East</option>
                                    <option value="Upper West">Upper West</option>
                                    <option value="Volta">Volta</option>
                                    <option value="Brong Ahafo">Brong Ahafo</option>
                                </select>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="landmark">Landmark (Optional)</label>
                            <input type="text" id="landmark" name="landmark" 
                                   placeholder="Near Circle Mall, Opposite Shell Station">
                        </div>
                    </div>

                    <!-- Delivery Options -->
                    <div class="form-section">
                        <h3><i class="fas fa-truck"></i> Delivery Options</h3>
                        
                        <div class="delivery-options">
                            <label class="delivery-option">
                                <input type="radio" name="delivery_method" value="standard" checked>
                                <div class="option-content">
                                    <div class="option-header">
                                        <span class="option-title">Standard Delivery</span>
                                        <span class="option-price">Free</span>
                                    </div>
                                    <div class="option-description">2-3 business days</div>
                                </div>
                            </label>

                            <label class="delivery-option">
                                <input type="radio" name="delivery_method" value="express">
                                <div class="option-content">
                                    <div class="option-header">
                                        <span class="option-title">Express Delivery</span>
                                        <span class="option-price">GHS 15.00</span>
                                    </div>
                                    <div class="option-description">Next business day</div>
                                </div>
                            </label>

                            <label class="delivery-option">
                                <input type="radio" name="delivery_method" value="same_day">
                                <div class="option-content">
                                    <div class="option-header">
                                        <span class="option-title">Same Day Delivery</span>
                                        <span class="option-price">GHS 25.00</span>
                                    </div>
                                    <div class="option-description">Within Accra only, order before 2 PM</div>
                                </div>
                            </label>
                        </div>
                    </div>

                    <!-- Special Instructions -->
                    <div class="form-section">
                        <h3><i class="fas fa-comment"></i> Special Instructions</h3>
                        <div class="form-group">
                            <label for="instructions">Delivery Instructions (Optional)</label>
                            <textarea id="instructions" name="instructions" rows="3" 
                                      placeholder="Any special delivery instructions..."></textarea>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="order-summary">
                <div class="summary-card">
                    <h3>Order Summary</h3>
                    
                    <!-- Order Items -->
                    <div class="order-items">
                        <?php foreach ($cartItems as $item): ?>
                            <div class="order-item">
                                <div class="item-image">
                                    <img src="<?= htmlspecialchars(getProductImageUrl($item['product_image'] ?? $item['main_image'] ?? '')) ?>" 
                                         alt="<?= htmlspecialchars($item['product_name']) ?>"
                                         onerror="this.src='https://via.placeholder.com/60x60/f0f0f0/999999?text=No+Image';">
                                    <span class="item-quantity"><?= $item['quantity'] ?></span>
                                </div>
                                <div class="item-details">
                                    <div class="item-name"><?= htmlspecialchars($item['product_name']) ?></div>
                                    <?php if (!empty($item['variant_name'])): ?>
                                        <div class="item-variant"><?= htmlspecialchars($item['variant_name']) ?></div>
                                    <?php endif; ?>
                                </div>
                                <div class="item-price">
                                    GHS <?= number_format($item['total_price'], 2) ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Coupon Code -->
                    <div class="coupon-section" style="margin: 20px 0; padding: 15px; background: #f8f9fa; border-radius: 8px;">
                        <label for="coupon_code" style="display: block; margin-bottom: 8px; font-weight: 600;">Have a coupon code?</label>
                        <div style="display: flex; gap: 10px;">
                            <input type="text" id="coupon_code" name="coupon_code" placeholder="Enter coupon code" 
                                   style="flex: 1; padding: 10px; border: 1px solid #ddd; border-radius: 4px;">
                            <button type="button" onclick="applyCoupon()" class="btn btn-secondary" style="white-space: nowrap;">
                                Apply
                            </button>
                        </div>
                        <div id="coupon-message" style="margin-top: 8px; font-size: 14px;"></div>
                    </div>

                    <!-- Price Breakdown -->
                    <div class="price-breakdown">
                        <div class="price-row">
                            <span>Subtotal</span>
                            <span id="subtotalAmount">GHS <?= number_format($cartSummary['subtotal'], 2) ?></span>
                        </div>
                        <div class="price-row" id="taxRow">
                            <span>VAT (12.5% inclusive)</span>
                            <span id="taxAmount">GHS 0.00</span>
                        </div>
                        <div class="price-row" id="discountRow" style="display: none;">
                            <span>Discount</span>
                            <span id="discountAmount" style="color: #28a745;">-GHS 0.00</span>
                        </div>
                        <div class="price-row" id="shippingRow">
                            <span>Shipping</span>
                            <span id="shippingCost">Free</span>
                        </div>
                        <div class="price-row total" id="totalRow">
                            <span>Total</span>
                            <span id="totalCost">GHS <?= number_format($cartSummary['total_amount'], 2) ?></span>
                        </div>
                    </div>

                    <!-- Payment Button -->
                    <button type="button" onclick="proceedToPayment()" class="btn btn-primary btn-large" id="paymentBtn">
                        <i class="fas fa-lock"></i> Proceed to Payment
                    </button>

                    <!-- Security Notice -->
                    <div class="security-notice">
                        <i class="fas fa-shield-alt"></i>
                        <span>Secure checkout powered by Paystack</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.checkout-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.checkout-header {
    margin-bottom: 40px;
}

.checkout-header h1 {
    font-size: 28px;
    color: #333;
    margin-bottom: 20px;
}

.checkout-steps {
    display: flex;
    gap: 30px;
}

.step {
    display: flex;
    align-items: center;
    gap: 10px;
    color: #999;
}

.step.active {
    color: var(--primary-orange);
}

.step-number {
    width: 30px;
    height: 30px;
    border-radius: 50%;
    background: #f0f0f0;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.step.active .step-number {
    background: var(--primary-orange);
    color: white;
}

.checkout-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 40px;
}

.checkout-form {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.form-section {
    margin-bottom: 40px;
}

.form-section h3 {
    font-size: 20px;
    color: #333;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.login-prompt {
    background: #e6f3ff;
    padding: 15px;
    border-radius: 8px;
    margin-bottom: 20px;
}

.login-prompt a {
    color: var(--primary-orange);
    text-decoration: none;
    font-weight: 600;
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.form-group {
    margin-bottom: 20px;
}

.form-group label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.form-group input,
.form-group select,
.form-group textarea {
    width: 100%;
    padding: 12px;
    border: 1px solid #ddd;
    border-radius: 8px;
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.form-group input:focus,
.form-group select:focus,
.form-group textarea:focus {
    outline: none;
    border-color: var(--primary-orange);
}

.delivery-options {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.delivery-option {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 20px;
    border: 2px solid #f0f0f0;
    border-radius: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.delivery-option:hover {
    border-color: var(--primary-orange);
}

.delivery-option input[type="radio"] {
    width: auto;
}

.delivery-option input[type="radio"]:checked + .option-content {
    color: var(--primary-orange);
}

.option-content {
    flex: 1;
}

.option-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 5px;
}

.option-title {
    font-weight: 600;
    font-size: 16px;
}

.option-price {
    font-weight: 600;
    color: var(--primary-orange);
}

.option-description {
    color: #666;
    font-size: 14px;
}

/* Order Summary */
.order-summary {
    position: sticky;
    top: 20px;
    height: fit-content;
}

.summary-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.summary-card h3 {
    font-size: 20px;
    margin-bottom: 25px;
    color: #333;
}

.order-items {
    margin-bottom: 25px;
}

.order-item {
    display: flex;
    align-items: center;
    gap: 15px;
    padding: 15px 0;
    border-bottom: 1px solid #f0f0f0;
}

.order-item:last-child {
    border-bottom: none;
}

.item-image {
    position: relative;
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-quantity {
    position: absolute;
    top: -8px;
    right: -8px;
    background: var(--primary-orange);
    color: white;
    border-radius: 50%;
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 12px;
    font-weight: 600;
}

.item-details {
    flex: 1;
}

.item-name {
    font-weight: 600;
    color: #333;
    margin-bottom: 4px;
}

.item-variant {
    font-size: 12px;
    color: #666;
}

.item-price {
    font-weight: 600;
    color: var(--primary-orange);
}

.price-breakdown {
    border-top: 1px solid #f0f0f0;
    padding-top: 20px;
}

.price-row {
    display: flex;
    justify-content: space-between;
    padding: 8px 0;
}

.price-row.total {
    border-top: 2px solid #f0f0f0;
    margin-top: 15px;
    padding-top: 15px;
    font-size: 18px;
    font-weight: 600;
    color: #333;
}

.btn {
    width: 100%;
    padding: 16px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
    font-size: 16px;
    margin-top: 25px;
}

.btn-primary {
    background: var(--primary-orange);
    color: white;
}

.btn-primary:hover {
    background: #d35400;
    transform: translateY(-2px);
}

.security-notice {
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    margin-top: 15px;
    color: #666;
    font-size: 14px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .checkout-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .form-row {
        grid-template-columns: 1fr;
    }
    
    .checkout-steps {
        flex-direction: column;
        gap: 15px;
    }
    
    .order-summary {
        position: static;
    }
}
</style>

<script>
// Global variables for calculations
let appliedCoupon = null;
const taxRate = 0.125; // 12.5% VAT

// Update shipping cost based on delivery method
document.addEventListener('DOMContentLoaded', function() {
    const deliveryOptions = document.querySelectorAll('input[name="delivery_method"]');
    const subtotal = <?= $cartSummary['subtotal'] ?>;
    
    deliveryOptions.forEach(option => {
        option.addEventListener('change', updateTotals);
    });
    
    // Update totals on page load
    updateTotals();
});

function updateTotals() {
    const subtotal = <?= $cartSummary['subtotal'] ?>;
    const selectedMethod = document.querySelector('input[name="delivery_method"]:checked')?.value || 'standard';
    
    let shipping = 0;
    let shippingText = 'Free';
    
    switch(selectedMethod) {
        case 'express':
            shipping = 15;
            shippingText = 'GHS 15.00';
            break;
        case 'same_day':
            shipping = 25;
            shippingText = 'GHS 25.00';
            break;
        default:
            shipping = 0;
            shippingText = 'Free';
    }
    
    // Calculate tax (VAT inclusive - extract VAT from price)
    // VAT inclusive formula: VAT = price * (rate / (100 + rate))
    const taxAmount = subtotal * (taxRate / (1 + taxRate));
    
    // Calculate discount
    const discountAmount = appliedCoupon ? appliedCoupon.discount_amount : 0;
    
    // Calculate total (VAT is already included in subtotal, so we don't add it)
    const total = subtotal + shipping - discountAmount;
    
    // Update display
    document.getElementById('shippingCost').textContent = shippingText;
    document.getElementById('taxAmount').textContent = 'GHS ' + taxAmount.toFixed(2);
    // VAT is always shown since it's inclusive
    document.getElementById('taxRow').style.display = 'flex';
    
    if (discountAmount > 0) {
        document.getElementById('discountAmount').textContent = '-GHS ' + discountAmount.toFixed(2);
        document.getElementById('discountRow').style.display = 'flex';
    } else {
        document.getElementById('discountRow').style.display = 'none';
    }
    
    document.getElementById('totalCost').textContent = 'GHS ' + total.toFixed(2);
}

async function applyCoupon() {
    const couponCode = document.getElementById('coupon_code').value.trim();
    const messageDiv = document.getElementById('coupon-message');
    
    if (!couponCode) {
        messageDiv.innerHTML = '<span style="color: #dc3545;">Please enter a coupon code</span>';
        return;
    }
    
    messageDiv.innerHTML = '<span style="color: #007bff;"><i class="fas fa-spinner fa-spin"></i> Validating...</span>';
    
    try {
        const response = await fetch('', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            body: JSON.stringify({
                action: 'validate_coupon',
                coupon_code: couponCode,
                subtotal: <?= $cartSummary['subtotal'] ?>
            })
        });
        
        const result = await response.json();
        
        if (result.valid) {
            appliedCoupon = result;
            messageDiv.innerHTML = '<span style="color: #28a745;"><i class="fas fa-check-circle"></i> ' + result.message + '</span>';
            updateTotals();
        } else {
            appliedCoupon = null;
            messageDiv.innerHTML = '<span style="color: #dc3545;"><i class="fas fa-times-circle"></i> ' + result.message + '</span>';
            updateTotals();
        }
    } catch (error) {
        messageDiv.innerHTML = '<span style="color: #dc3545;">Error validating coupon. Please try again.</span>';
        console.error('Coupon validation error:', error);
    }
}

async function proceedToPayment() {
    const form = document.getElementById('checkoutForm');
    const formData = new FormData(form);
    
    // Validate form
    if (!form.checkValidity()) {
        form.reportValidity();
        return;
    }
    
    const paymentBtn = document.getElementById('paymentBtn');
    paymentBtn.disabled = true;
    paymentBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
    
    try {
        // Get total amount including shipping
        const deliveryMethod = document.querySelector('input[name="delivery_method"]:checked').value;
        let shippingCost = 0;
        
        switch(deliveryMethod) {
            case 'express': shippingCost = 15; break;
            case 'same_day': shippingCost = 25; break;
        }
        
        // Calculate totals
        const subtotal = <?= $cartSummary['subtotal'] ?>;
        // VAT is inclusive, so extract it from the price
        const taxAmount = subtotal * (0.125 / (1 + 0.125)); // Extract 12.5% VAT from inclusive price
        const discountAmount = appliedCoupon ? appliedCoupon.discount_amount : 0;
        // VAT is already included in subtotal, so we don't add it
        const totalAmount = subtotal + shippingCost - discountAmount;
        
        // Prepare order data
        const orderData = {
            action: 'create_order',
            customer_info: {
                firstName: formData.get('firstName'),
                lastName: formData.get('lastName'),
                email: formData.get('email'),
                phone: formData.get('phone')
            },
            shipping_address: {
                address: formData.get('address'),
                city: formData.get('city'),
                region: formData.get('region'),
                landmark: formData.get('landmark')
            },
            delivery_method: deliveryMethod,
            instructions: formData.get('instructions'),
            coupon_code: appliedCoupon ? appliedCoupon.code : null,
            total_amount: totalAmount
        };
        
        // Create order and initialize payment
        console.log('Sending order data:', orderData);
        
        const response = await fetch('', {
            method: 'POST',
            body: JSON.stringify(orderData),
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        });
        
        console.log('Response status:', response.status);
        
        const responseText = await response.text();
        console.log('Raw response:', responseText);
        console.log('Response status:', response.status);
        
        if (!response.ok) {
            throw new Error(`Server error: ${response.status} ${response.statusText}`);
        }
        
        let result;
        try {
            result = JSON.parse(responseText.trim());
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Response text:', responseText);
            throw new Error('Invalid response from server. Please try again.');
        }
        
        if (result.success) {
            // Initialize Paystack payment
            initializePaystackPayment(result.order_id, totalAmount, orderData.customer_info);
        } else {
            const errorMsg = result.message || result.debug || 'Failed to create order';
            console.error('Order creation failed:', result);
            throw new Error(errorMsg);
        }
        
    } catch (error) {
        console.error('Checkout error:', error);
        console.error('Error stack:', error.stack);
        const errorMessage = error.message || 'Failed to process checkout. Please try again.';
        
        // Show notification
        showNotification(errorMessage, 'error');
        
        // Also show alert for debugging
        if (errorMessage.includes('vendor') || errorMessage.includes('Vendor')) {
            alert('Error: ' + errorMessage + '\n\nPlease ensure all products have vendors assigned. Contact support if this issue persists.');
        } else {
            alert('Checkout Error: ' + errorMessage + '\n\nPlease check the browser console for more details.');
        }
        
        paymentBtn.disabled = false;
        paymentBtn.innerHTML = '<i class="fas fa-lock"></i> Proceed to Payment';
    }
}

function initializePaystackPayment(orderId, amount, customerInfo) {
    const handler = PaystackPop.setup({
        key: 'pk_test_39dc2ad96f7ea663a603503d07da30f598323e2f', // Your Paystack public key
        email: customerInfo.email,
        amount: amount * 100, // Convert to kobo
        currency: 'GHS',
        ref: 'order_' + orderId + '_' + Math.floor((Math.random() * 1000000000) + 1),
        metadata: {
            order_id: orderId,
            customer_name: customerInfo.firstName + ' ' + customerInfo.lastName
        },
        callback: function(response) {
            // Payment successful
            window.location.href = '?page=order-confirmation&order_id=' + orderId + '&reference=' + response.reference;
        },
        onClose: function() {
            // Payment cancelled
            const paymentBtn = document.getElementById('paymentBtn');
            paymentBtn.disabled = false;
            paymentBtn.innerHTML = '<i class="fas fa-lock"></i> Proceed to Payment';
        }
    });
    
    handler.openIframe();
}

function showNotification(message, type) {
    const notification = document.createElement('div');
    notification.className = `notification ${type}`;
    notification.textContent = message;
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        padding: 15px 20px;
        border-radius: 8px;
        color: white;
        font-weight: 500;
        z-index: 1000;
        transition: all 0.3s ease;
        ${type === 'success' ? 'background: #27ae60;' : 'background: #e74c3c;'}
    `;
    
    document.body.appendChild(notification);
    
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}
</script>

<!-- Paystack Inline JS -->
<script src="https://js.paystack.co/v1/inline.js"></script>
