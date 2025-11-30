<?php
require_once 'includes/cart.php';
require_once 'includes/helpers.php';

$cart = new ShoppingCart();
$cartItems = $cart->getItems();
$cartSummary = $cart->getSummary();
$cartSubtotal = $cartSummary['subtotal'];
$cartTotal = $cartSummary['total_amount'];
$cartCount = $cartSummary['item_count'];

// Check for order success message
$orderSuccess = $_SESSION['order_success'] ?? null;
if ($orderSuccess) {
    unset($_SESSION['order_success']); // Clear it after showing
}
?>

<div class="cart-container">
    
    <?php if ($orderSuccess): ?>
        <!-- Order Success Message -->
        <div class="order-success-banner">
            <div class="success-content">
                <div class="success-icon">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="success-text">
                    <h3><?= htmlspecialchars($orderSuccess['message']) ?></h3>
                    <p>Order #<?= htmlspecialchars($orderSuccess['order_id']) ?> - Amount: GHS <?= number_format($orderSuccess['amount'], 2) ?></p>
                </div>
                <button onclick="this.parentElement.parentElement.style.display='none'" class="close-btn">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>
    <?php endif; ?>

<div class="cart-container">
    <div class="cart-wrapper">
        <!-- Cart Header -->
        <div class="cart-header">
            <h1><i class="fas fa-shopping-cart"></i> Shopping Cart</h1>
            <div class="cart-summary">
                <span class="cart-count"><?= $cartCount ?> item<?= $cartCount !== 1 ? 's' : '' ?></span>
                <span class="cart-total">Total: GHS <?= number_format($cartTotal, 2) ?></span>
            </div>
        </div>

        <?php if (empty($cartItems)): ?>
            <!-- Empty Cart -->
            <div class="empty-cart">
                <div class="empty-cart-icon">
                    <i class="fas fa-shopping-cart"></i>
                </div>
                <h2>Your cart is empty</h2>
                <p>Looks like you haven't added any items to your cart yet.</p>
                <a href="?page=products" class="btn btn-primary">
                    <i class="fas fa-shopping-bag"></i> Continue Shopping
                </a>
            </div>
        <?php else: ?>
            <!-- Cart Items -->
            <div class="cart-content">
                <div class="cart-items">
                    <?php foreach ($cartItems as $item): ?>
                        <div class="cart-item" data-cart-id="<?= $item['id'] ?>">
                            <div class="item-image">
                                <img src="<?= htmlspecialchars(getProductImageUrl($item['product_image'] ?? $item['main_image'] ?? '')) ?>" 
                                     alt="<?= htmlspecialchars($item['product_name']) ?>"
                                     onerror="this.src='https://via.placeholder.com/100x100/f0f0f0/999999?text=No+Image';">
                            </div>
                            
                            <div class="item-details">
                                <h3 class="item-name"><?= htmlspecialchars($item['product_name']) ?></h3>
                                <div class="item-meta">
                                    <?php if (!empty($item['variant_name'])): ?>
                                        <span class="item-variant"><?= htmlspecialchars($item['variant_name']) ?></span>
                                    <?php endif; ?>
                                    <span class="item-vendor">by <?= htmlspecialchars($item['vendor_name'] ?? 'Unknown Vendor') ?></span>
                                </div>
                                <div class="item-price">
                                    GHS <?= number_format($item['unit_price'], 2) ?>
                                </div>
                            </div>
                            
                            <div class="item-quantity">
                                <label>Quantity:</label>
                                <div class="quantity-controls">
                                    <button type="button" onclick="updateCartQuantity(<?= $item['id'] ?>, <?= $item['quantity'] - 1 ?>)" 
                                            class="qty-btn" <?= $item['quantity'] <= 1 ? 'disabled' : '' ?>>-</button>
                                    <input type="number" value="<?= $item['quantity'] ?>" 
                                           min="1" max="<?= $item['stock_quantity'] ?>"
                                           onchange="updateCartQuantity(<?= $item['id'] ?>, this.value)"
                                           class="qty-input">
                                    <button type="button" onclick="updateCartQuantity(<?= $item['id'] ?>, <?= $item['quantity'] + 1 ?>)" 
                                            class="qty-btn" <?= $item['quantity'] >= $item['stock_quantity'] ? 'disabled' : '' ?>>+</button>
                                </div>
                            </div>
                            
                            <div class="item-total">
                                <div class="item-subtotal">
                                    GHS <?= number_format($item['total_price'], 2) ?>
                                </div>
                                <button onclick="removeCartItem(<?= $item['id'] ?>)" class="remove-btn" title="Remove item">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>

                <!-- Cart Summary -->
                <div class="cart-summary-section">
                    <div class="summary-card">
                        <h3>Order Summary</h3>
                        
                        <div class="summary-row">
                            <span>Subtotal (<?= $cartCount ?> items)</span>
                            <span>GHS <?= number_format($cartSubtotal, 2) ?></span>
                        </div>
                        
                        <div class="summary-row">
                            <span>Shipping</span>
                            <span>Free</span>
                        </div>
                        
                        <div class="summary-row total">
                            <span>Total</span>
                            <span>GHS <?= number_format($cartTotal, 2) ?></span>
                        </div>
                        
                        <div class="checkout-actions">
                            <button onclick="proceedToCheckout()" class="btn btn-primary btn-large">
                                <i class="fas fa-credit-card"></i> Proceed to Checkout
                            </button>
                            <a href="?page=products" class="btn btn-secondary">
                                <i class="fas fa-arrow-left"></i> Continue Shopping
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.cart-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.order-success-banner {
    background: linear-gradient(135deg, #27ae60, #2ecc71);
    color: white;
    padding: 20px;
    border-radius: 12px;
    margin-bottom: 30px;
    box-shadow: 0 4px 15px rgba(39, 174, 96, 0.3);
    animation: slideDown 0.5s ease-out;
}

@keyframes slideDown {
    from {
        opacity: 0;
        transform: translateY(-20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.success-content {
    display: flex;
    align-items: center;
    gap: 20px;
}

.success-icon {
    font-size: 40px;
    color: white;
}

.success-text {
    flex: 1;
}

.success-text h3 {
    margin: 0 0 8px 0;
    font-size: 20px;
    font-weight: 600;
}

.success-text p {
    margin: 0;
    opacity: 0.9;
    font-size: 14px;
}

.close-btn {
    background: rgba(255, 255, 255, 0.2);
    border: none;
    color: white;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.close-btn:hover {
    background: rgba(255, 255, 255, 0.3);
}

.cart-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f0f0f0;
}

.cart-header h1 {
    font-size: 28px;
    color: #333;
    margin: 0;
}

.cart-summary {
    text-align: right;
}

.cart-count {
    display: block;
    color: #666;
    font-size: 14px;
}

.cart-total {
    display: block;
    font-size: 20px;
    font-weight: 600;
    color: var(--primary-orange);
}

/* Empty Cart */
.empty-cart {
    text-align: center;
    padding: 60px 20px;
}

.empty-cart-icon {
    font-size: 80px;
    color: #ddd;
    margin-bottom: 20px;
}

.empty-cart h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 10px;
}

.empty-cart p {
    color: #666;
    margin-bottom: 30px;
}

/* Cart Content */
.cart-content {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 40px;
}

.cart-items {
    display: flex;
    flex-direction: column;
    gap: 20px;
}

.cart-item {
    display: grid;
    grid-template-columns: 100px 1fr auto auto;
    gap: 20px;
    padding: 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    align-items: center;
}

.item-image {
    width: 100px;
    height: 100px;
    border-radius: 8px;
    overflow: hidden;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-details {
    flex: 1;
}

.item-name {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 8px;
}

.item-meta {
    display: flex;
    gap: 15px;
    margin-bottom: 8px;
}

.item-variant {
    background: #e6f3ff;
    color: #667eea;
    padding: 2px 8px;
    border-radius: 12px;
    font-size: 12px;
}

.item-vendor {
    color: #666;
    font-size: 14px;
}

.item-price {
    font-size: 16px;
    font-weight: 600;
    color: var(--primary-orange);
}

.item-quantity {
    text-align: center;
}

.item-quantity label {
    display: block;
    margin-bottom: 8px;
    font-size: 14px;
    color: #666;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.qty-btn {
    background: #f8f9fa;
    border: none;
    padding: 8px 12px;
    cursor: pointer;
    font-size: 16px;
    font-weight: 600;
    color: #333;
    transition: background 0.3s ease;
}

.qty-btn:hover:not(:disabled) {
    background: #e9ecef;
}

.qty-btn:disabled {
    opacity: 0.5;
    cursor: not-allowed;
}

.qty-input {
    border: none;
    padding: 8px 12px;
    text-align: center;
    width: 60px;
    font-size: 14px;
}

.item-total {
    text-align: center;
}

.item-subtotal {
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-bottom: 10px;
}

.remove-btn {
    background: #e74c3c;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 6px;
    cursor: pointer;
    transition: background 0.3s ease;
}

.remove-btn:hover {
    background: #c0392b;
}

/* Cart Summary */
.summary-card {
    background: white;
    padding: 30px;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    position: sticky;
    top: 20px;
}

.summary-card h3 {
    font-size: 20px;
    margin-bottom: 20px;
    color: #333;
}

.summary-row {
    display: flex;
    justify-content: space-between;
    padding: 10px 0;
    border-bottom: 1px solid #f0f0f0;
}

.summary-row.total {
    border-bottom: none;
    border-top: 2px solid #f0f0f0;
    font-size: 18px;
    font-weight: 600;
    color: #333;
    margin-top: 10px;
    padding-top: 15px;
}

.checkout-actions {
    margin-top: 30px;
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.btn {
    padding: 12px 24px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    text-align: center;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
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

.btn-large {
    padding: 16px 32px;
    font-size: 16px;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .cart-content {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .cart-item {
        grid-template-columns: 80px 1fr;
        gap: 15px;
    }
    
    .item-quantity,
    .item-total {
        grid-column: 1 / -1;
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 15px;
        padding-top: 15px;
        border-top: 1px solid #f0f0f0;
    }
    
    .cart-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
}
</style>

<script>
async function updateCartQuantity(cartItemId, newQuantity) {
    if (newQuantity < 1) {
        removeCartItem(cartItemId);
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'update_cart');
        formData.append('cart_item_id', cartItemId);
        formData.append('quantity', newQuantity);

        const response = await fetch('', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const result = await response.json();
        
        if (result.success) {
            location.reload(); // Refresh to show updated cart
        } else {
            showNotification(result.message || 'Failed to update cart', 'error');
        }
    } catch (error) {
        console.error('Error updating cart:', error);
        showNotification('Failed to update cart', 'error');
    }
}

async function removeCartItem(cartItemId) {
    if (!confirm('Are you sure you want to remove this item from your cart?')) {
        return;
    }
    
    try {
        const formData = new FormData();
        formData.append('action', 'remove_from_cart');
        formData.append('cart_item_id', cartItemId);

        const response = await fetch('', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        const result = await response.json();
        
        if (result.success) {
            location.reload(); // Refresh to show updated cart
        } else {
            showNotification(result.message || 'Failed to remove item', 'error');
        }
    } catch (error) {
        console.error('Error removing item:', error);
        showNotification('Failed to remove item', 'error');
    }
}

function proceedToCheckout() {
    window.location.href = '?page=checkout';
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
