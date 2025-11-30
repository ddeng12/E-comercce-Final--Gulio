<?php
require_once 'includes/products.php';
require_once 'includes/cart.php';
require_once 'includes/helpers.php';

$productId = $_GET['id'] ?? null;

if (!$productId) {
    header('Location: ?page=products');
    exit;
}

$product = getProductById($productId);

if (!$product) {
    header('Location: ?page=products');
    exit;
}

$relatedProducts = getRelatedProducts($product['category'], $productId, 4);
$variants = getProductVariants($productId);
?>

<div class="product-detail-container">
    <div class="product-detail-wrapper">
        <!-- Back Button -->
        <div class="back-navigation">
            <a href="?page=products" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back to Products
            </a>
        </div>

        <!-- Product Detail -->
        <div class="product-detail">
            <!-- Product Image -->
            <div class="product-image-section">
                <div class="main-image">
                    <img src="<?= htmlspecialchars(getProductImageUrl($product['image_url'] ?? '')) ?>" 
                         alt="<?= htmlspecialchars($product['name']) ?>"
                         id="mainProductImage"
                         onerror="this.src='https://via.placeholder.com/500x500/f0f0f0/999999?text=No+Image';">
                </div>
            </div>

            <!-- Product Info -->
            <div class="product-info-section">
                <div class="product-header">
                    <h1><?= htmlspecialchars($product['name']) ?></h1>
                    <div class="product-meta">
                        <span class="category-badge"><?= ucfirst(str_replace('_', ' ', $product['category'])) ?></span>
                        <span class="vendor-info">by <?= htmlspecialchars($product['vendor_name'] ?? 'Kofi\'s Cuts') ?></span>
                    </div>
                </div>

                <div class="price-section">
                    <div class="current-price">GHS <?= number_format($product['price'], 2) ?></div>
                    <?php if (!empty($product['compare_price']) && $product['compare_price'] > $product['price']): ?>
                        <div class="compare-price">GHS <?= number_format($product['compare_price'], 2) ?></div>
                        <div class="discount-badge">
                            <?= round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100) ?>% OFF
                        </div>
                    <?php endif; ?>
                </div>

                <?php if (!empty($product['description'])): ?>
                <div class="product-description">
                    <h3>Description</h3>
                    <p><?= nl2br(htmlspecialchars($product['description'])) ?></p>
                </div>
                <?php endif; ?>

                <!-- Stock Status -->
                <div class="stock-status">
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <span class="in-stock">
                            <i class="fas fa-check-circle"></i> In Stock (<?= $product['stock_quantity'] ?> available)
                        </span>
                    <?php else: ?>
                        <span class="out-of-stock">
                            <i class="fas fa-times-circle"></i> Out of Stock
                        </span>
                    <?php endif; ?>
                </div>

                <!-- Add to Cart Section -->
                <div class="add-to-cart-section">
                    <?php if ($product['stock_quantity'] > 0): ?>
                        <div class="quantity-selector">
                            <label for="quantity">Quantity:</label>
                            <div class="quantity-controls">
                                <button type="button" onclick="decreaseQuantity()" class="qty-btn" id="decreaseBtn">-</button>
                                <input type="number" id="quantity" value="1" min="1" max="<?= $product['stock_quantity'] ?>" onchange="validateQuantity()">
                                <button type="button" onclick="increaseQuantity()" class="qty-btn" id="increaseBtn">+</button>
                            </div>
                        </div>

                        <button onclick="addToCart(<?= $product['id'] ?>)" class="add-to-cart-btn">
                            <i class="fas fa-shopping-cart"></i> Add to Cart
                        </button>
                    <?php else: ?>
                        <button class="add-to-cart-btn disabled" disabled>
                            <i class="fas fa-times"></i> Out of Stock
                        </button>
                    <?php endif; ?>
                </div>

                <!-- Product Details -->
                <div class="product-details">
                    <h3>Product Details</h3>
                    <ul>
                        <?php if (!empty($product['sku'])): ?>
                            <li><strong>SKU:</strong> <?= htmlspecialchars($product['sku']) ?></li>
                        <?php endif; ?>
                        <li><strong>Category:</strong> <?= ucfirst(str_replace('_', ' ', $product['category'])) ?></li>
                        <?php if (!empty($product['weight'])): ?>
                            <li><strong>Weight:</strong> <?= $product['weight'] ?>kg</li>
                        <?php endif; ?>
                        <?php if (!empty($product['dimensions'])): ?>
                            <li><strong>Dimensions:</strong> <?= htmlspecialchars($product['dimensions']) ?></li>
                        <?php endif; ?>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Related Products -->
        <?php if (!empty($relatedProducts)): ?>
        <div class="related-products">
            <h3>Related Products</h3>
            <div class="related-products-grid">
                <?php foreach ($relatedProducts as $relatedProduct): ?>
                    <div class="related-product-card" onclick="viewProduct(<?= $relatedProduct['id'] ?>)">
                        <div class="related-product-image">
                            <img src="<?= htmlspecialchars($relatedProduct['image_url'] ?? 'https://via.placeholder.com/200x200/f0f0f0/999999?text=No+Image') ?>" 
                                 alt="<?= htmlspecialchars($relatedProduct['name']) ?>"
                                 onerror="this.src='https://via.placeholder.com/200x200/f0f0f0/999999?text=No+Image';">
                        </div>
                        <div class="related-product-info">
                            <h4><?= htmlspecialchars($relatedProduct['name']) ?></h4>
                            <div class="related-product-price">GHS <?= number_format($relatedProduct['price'], 2) ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<style>
.product-detail-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.back-navigation {
    margin-bottom: 20px;
}

.back-btn {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    color: var(--primary-orange);
    text-decoration: none;
    font-weight: 500;
    padding: 8px 16px;
    border: 1px solid var(--primary-orange);
    border-radius: 8px;
    transition: all 0.3s ease;
}

.back-btn:hover {
    background: var(--primary-orange);
    color: white;
}

.product-detail {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-bottom: 40px;
}

.product-image-section {
    position: sticky;
    top: 20px;
    height: fit-content;
}

.main-image {
    width: 100%;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}

.main-image img {
    width: 100%;
    height: 500px;
    object-fit: cover;
    display: block;
}

.product-info-section {
    padding: 20px 0;
}

.product-header h1 {
    font-size: 32px;
    font-weight: 700;
    color: #333;
    margin-bottom: 10px;
}

.product-meta {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 20px;
}

.category-badge {
    background: #e6f3ff;
    color: #667eea;
    padding: 6px 12px;
    border-radius: 20px;
    font-size: 14px;
    font-weight: 500;
}

.vendor-info {
    color: #666;
    font-size: 16px;
}

.price-section {
    display: flex;
    align-items: center;
    gap: 15px;
    margin-bottom: 30px;
    flex-wrap: wrap;
}

.current-price {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary-orange);
}

.compare-price {
    font-size: 20px;
    color: #999;
    text-decoration: line-through;
}

.discount-badge {
    background: #e74c3c;
    color: white;
    padding: 4px 8px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: 600;
}

.product-description {
    margin-bottom: 30px;
}

.product-description h3 {
    font-size: 20px;
    margin-bottom: 10px;
    color: #333;
}

.product-description p {
    color: #666;
    line-height: 1.6;
    font-size: 16px;
}

.stock-status {
    margin-bottom: 30px;
}

.in-stock {
    color: #27ae60;
    font-weight: 500;
}

.out-of-stock {
    color: #e74c3c;
    font-weight: 500;
}

.add-to-cart-section {
    margin-bottom: 30px;
}

.quantity-selector {
    margin-bottom: 20px;
}

.quantity-selector label {
    display: block;
    margin-bottom: 8px;
    font-weight: 500;
    color: #333;
}

.quantity-controls {
    display: flex;
    align-items: center;
    gap: 0;
    width: fit-content;
    border: 1px solid #ddd;
    border-radius: 8px;
    overflow: hidden;
}

.qty-btn {
    background: #f8f9fa;
    border: none;
    padding: 12px 16px;
    cursor: pointer;
    font-size: 18px;
    font-weight: 600;
    color: #333;
    transition: background 0.3s ease;
}

.qty-btn:hover:not(:disabled) {
    background: #e9ecef;
}

.qty-btn:disabled {
    cursor: not-allowed;
    opacity: 0.5;
}

#quantity {
    border: none;
    padding: 12px 16px;
    text-align: center;
    width: 80px;
    font-size: 16px;
    font-weight: 500;
}

.add-to-cart-btn {
    width: 100%;
    padding: 16px;
    background: var(--primary-orange);
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 18px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 10px;
}

.add-to-cart-btn:hover:not(.disabled) {
    background: #d35400;
    transform: translateY(-2px);
}

.add-to-cart-btn.disabled {
    background: #ccc;
    cursor: not-allowed;
}

.product-details h3 {
    font-size: 20px;
    margin-bottom: 15px;
    color: #333;
}

.product-details ul {
    list-style: none;
    padding: 0;
}

.product-details li {
    padding: 8px 0;
    border-bottom: 1px solid #f0f0f0;
    color: #666;
}

.related-products {
    margin-top: 60px;
}

.related-products h3 {
    font-size: 24px;
    margin-bottom: 20px;
    color: #333;
}

.related-products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
    gap: 20px;
}

.related-product-card {
    background: white;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    cursor: pointer;
    transition: transform 0.3s ease;
}

.related-product-card:hover {
    transform: translateY(-4px);
}

.related-product-image {
    height: 150px;
    overflow: hidden;
}

.related-product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.related-product-info {
    padding: 15px;
}

.related-product-info h4 {
    font-size: 16px;
    margin-bottom: 8px;
    color: #333;
}

.related-product-price {
    font-size: 18px;
    font-weight: 600;
    color: var(--primary-orange);
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .product-detail {
        grid-template-columns: 1fr;
        gap: 20px;
    }
    
    .product-header h1 {
        font-size: 24px;
    }
    
    .current-price {
        font-size: 24px;
    }
    
    .main-image img {
        height: 300px;
    }
    
    .related-products-grid {
        grid-template-columns: repeat(2, 1fr);
    }
}
</style>

<script>
function increaseQuantity() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.getAttribute('max'));
    const current = parseInt(input.value) || 1;
    
    if (current < max) {
        input.value = current + 1;
        validateQuantity();
    }
}

function decreaseQuantity() {
    const input = document.getElementById('quantity');
    const current = parseInt(input.value) || 1;
    
    if (current > 1) {
        input.value = current - 1;
        validateQuantity();
    }
}

function validateQuantity() {
    const input = document.getElementById('quantity');
    const max = parseInt(input.getAttribute('max'));
    const min = parseInt(input.getAttribute('min'));
    let current = parseInt(input.value) || 1;
    
    // Ensure value is within bounds
    if (current < min) {
        current = min;
        input.value = current;
    }
    if (current > max) {
        current = max;
        input.value = current;
    }
    
    // Update button states
    const decreaseBtn = document.getElementById('decreaseBtn');
    const increaseBtn = document.getElementById('increaseBtn');
    
    decreaseBtn.disabled = current <= min;
    increaseBtn.disabled = current >= max;
    
    // Update button styles
    decreaseBtn.style.opacity = current <= min ? '0.5' : '1';
    increaseBtn.style.opacity = current >= max ? '0.5' : '1';
}

function viewProduct(productId) {
    window.location.href = `?page=product-detail&id=${productId}`;
}

async function addToCart(productId) {
    const quantityInput = document.getElementById('quantity');
    const quantity = parseInt(quantityInput.value) || 1;
    const addButton = document.querySelector('.add-to-cart-btn');
    
    // Validate quantity
    if (quantity < 1) {
        showNotification('Please select a valid quantity', 'error');
        return;
    }
    
    // Disable button during request
    addButton.disabled = true;
    addButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Adding...';
    
    try {
        const formData = new FormData();
        formData.append('action', 'add_to_cart');
        formData.append('product_id', productId);
        formData.append('quantity', quantity);

        console.log('Sending cart request:', {
            action: 'add_to_cart',
            product_id: productId,
            quantity: quantity
        });

        const response = await fetch('', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });

        console.log('Response status:', response.status);
        console.log('Response headers:', response.headers);

        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status}`);
        }

        const responseText = await response.text();
        console.log('Raw response length:', responseText.length);
        console.log('Raw response:', responseText);
        console.log('Character at position 172:', responseText.charAt(172));
        console.log('Characters around position 172:', responseText.substring(160, 185));
        
        let result;
        try {
            // Clean the response text of any potential whitespace/BOM
            const cleanResponse = responseText.trim();
            result = JSON.parse(cleanResponse);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            console.error('Failed to parse response:', responseText);
            showNotification('Server returned invalid response. Check console for details.', 'error');
            return;
        }
        
        if (result.success) {
            // Redirect to cart page after successful add
            window.location.href = '?page=cart';
        } else {
            showNotification(result.message || 'Failed to add to cart', 'error');
        }
    } catch (error) {
        console.error('Error adding to cart:', error);
        showNotification('Failed to add to cart. Please try again.', 'error');
    } finally {
        // Re-enable button
        addButton.disabled = false;
        addButton.innerHTML = '<i class="fas fa-shopping-cart"></i> Add to Cart';
    }
}

function showNotification(message, type) {
    // Create notification element
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
    
    // Remove after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => {
            document.body.removeChild(notification);
        }, 300);
    }, 3000);
}

function updateCartCount() {
    // Update cart count in navigation if you have one
    // This would need to be implemented based on your cart system
}

// Initialize page when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize quantity controls
    validateQuantity();
    
    // Add event listeners
    const quantityInput = document.getElementById('quantity');
    if (quantityInput) {
        quantityInput.addEventListener('input', validateQuantity);
        quantityInput.addEventListener('change', validateQuantity);
    }
    
    console.log('Product detail page initialized');
});
</script>
