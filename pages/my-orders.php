<?php
// Require authentication for this page
if (!isset($_SESSION['user']) || $_SESSION['user']['id'] === null) {
    header('Location: ?page=login&redirect=my-orders');
    exit;
}

require_once __DIR__ . '/../includes/orders.php';
require_once __DIR__ . '/../includes/helpers.php';

$userId = $_SESSION['user']['id'];

// Get orders from database
$userOrders = getOrdersByUserId($userId, 50, 0);
?>

<div class="orders-container">
    <div class="orders-wrapper">
        <!-- Page Header -->
        <div class="orders-header">
            <h1><i class="fas fa-shopping-bag"></i> My Orders</h1>
            <p>View and track all your orders</p>
        </div>

        <?php if (empty($userOrders)): ?>
            <!-- No Orders -->
            <div class="no-orders">
                <div class="no-orders-icon">
                    <i class="fas fa-shopping-bag"></i>
                </div>
                <h2>No Orders Yet</h2>
                <p>You haven't placed any orders yet. Start shopping to see your orders here!</p>
                <a href="?page=products" class="btn btn-primary">
                    <i class="fas fa-shopping-cart"></i> Start Shopping
                </a>
            </div>
        <?php else: ?>
            <!-- Orders List -->
            <div class="orders-list">
                <?php foreach ($userOrders as $order): 
                    $orderId = $order['id'];
                    // Get full order details with items
                    $fullOrder = getOrderById($orderId);
                    if (!$fullOrder) continue;
                ?>
                    <div class="order-card">
                        <!-- Order Header -->
                        <div class="order-header">
                            <div class="order-info">
                                <h3>Order #<?= htmlspecialchars($fullOrder['order_number']) ?></h3>
                                <div class="order-meta">
                                    <span class="order-date">
                                        <i class="fas fa-calendar"></i>
                                        <?= date('F j, Y \a\t g:i A', strtotime($fullOrder['created_at'])) ?>
                                    </span>
                                    <span class="order-status <?= strtolower($fullOrder['status']) ?>">
                                        <i class="fas fa-check-circle"></i>
                                        <?= ucfirst($fullOrder['status']) ?>
                                    </span>
                                </div>
                            </div>
                            <div class="order-total">
                                <span class="total-label">Total</span>
                                <span class="total-amount">GHS <?= number_format($fullOrder['total_amount'] ?? 0, 2) ?></span>
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div class="order-items">
                            <h4>Items (<?= count($fullOrder['items'] ?? []) ?>)</h4>
                            <div class="items-grid">
                                <?php foreach ($fullOrder['items'] ?? [] as $item): 
                                    // Get image from item (already processed in getOrderById)
                                    $imageUrl = $item['image_url'] ?? null;
                                    // Fallback to product_data if image_url not set
                                    if (empty($imageUrl)) {
                                        $productData = json_decode($item['product_data'] ?? '{}', true);
                                        $imageUrl = $productData['image_url'] ?? null;
                                    }
                                ?>
                                    <div class="order-item">
                                        <div class="item-image">
                                            <img src="<?= htmlspecialchars(getProductImageUrl($imageUrl ?? '')) ?>" 
                                                 alt="<?= htmlspecialchars($item['product_name']) ?>"
                                                 onerror="this.src='https://via.placeholder.com/60x60/f0f0f0/999999?text=No+Image';">
                                            <span class="item-qty"><?= $item['quantity'] ?></span>
                                        </div>
                                        <div class="item-details">
                                            <h5><?= htmlspecialchars($item['product_name']) ?></h5>
                                            <?php if (!empty($item['variant_name'])): ?>
                                                <p class="item-variant"><?= htmlspecialchars($item['variant_name']) ?></p>
                                            <?php endif; ?>
                                            <p class="item-price">GHS <?= number_format($item['total_price'], 2) ?></p>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>

                        <!-- Order Details -->
                        <div class="order-details">
                            <div class="details-grid">
                                <!-- Payment Info -->
                                <div class="detail-section">
                                    <h5><i class="fas fa-credit-card"></i> Payment</h5>
                                    <p><strong>Method:</strong> <?= ucfirst(str_replace('_', ' ', $fullOrder['payment_method'] ?? 'paystack')) ?></p>
                                    <?php if (!empty($fullOrder['payment_reference'])): ?>
                                        <p><strong>Reference:</strong> <?= htmlspecialchars($fullOrder['payment_reference']) ?></p>
                                    <?php endif; ?>
                                    <p><strong>Status:</strong> <span class="payment-status <?= strtolower($fullOrder['payment_status']) ?>"><?= ucfirst($fullOrder['payment_status']) ?></span></p>
                                </div>

                                <!-- Shipping Info -->
                                <div class="detail-section">
                                    <h5><i class="fas fa-truck"></i> Delivery</h5>
                                    <p><strong>Method:</strong> <?= ucfirst(str_replace('_', ' ', $fullOrder['shipping_method'] ?? 'delivery')) ?></p>
                                    <?php if (!empty($fullOrder['shipping_address'])): ?>
                                        <p><strong>Address:</strong> <?= htmlspecialchars($fullOrder['shipping_address']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($fullOrder['shipping_city'])): ?>
                                        <p><strong>City:</strong> <?= htmlspecialchars($fullOrder['shipping_city']) ?><?= !empty($fullOrder['shipping_region']) ? ', ' . htmlspecialchars($fullOrder['shipping_region']) : '' ?></p>
                                    <?php endif; ?>
                                </div>

                                <!-- Customer Info -->
                                <div class="detail-section">
                                    <h5><i class="fas fa-user"></i> Customer</h5>
                                    <?php if (!empty($fullOrder['shipping_name'])): ?>
                                        <p><strong>Name:</strong> <?= htmlspecialchars($fullOrder['shipping_name']) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($fullOrder['guest_email']) || !empty($fullOrder['user_email'])): ?>
                                        <p><strong>Email:</strong> <?= htmlspecialchars($fullOrder['guest_email'] ?? $fullOrder['user_email'] ?? '') ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($fullOrder['shipping_phone'])): ?>
                                        <p><strong>Phone:</strong> <?= htmlspecialchars($fullOrder['shipping_phone']) ?></p>
                                    <?php endif; ?>
                                </div>

                                <!-- Order Summary -->
                                <div class="detail-section">
                                    <h5><i class="fas fa-receipt"></i> Summary</h5>
                                    <p><strong>Subtotal:</strong> GHS <?= number_format($fullOrder['subtotal'] ?? 0, 2) ?></p>
                                    <?php if (!empty($fullOrder['tax_amount']) && $fullOrder['tax_amount'] > 0): ?>
                                        <p><strong>Tax:</strong> GHS <?= number_format($fullOrder['tax_amount'], 2) ?></p>
                                    <?php endif; ?>
                                    <?php if (!empty($fullOrder['discount_amount']) && $fullOrder['discount_amount'] > 0): ?>
                                        <p><strong>Discount:</strong> -GHS <?= number_format($fullOrder['discount_amount'], 2) ?></p>
                                    <?php endif; ?>
                                    <p><strong>Shipping:</strong> <?= ($fullOrder['shipping_amount'] ?? 0) > 0 ? 'GHS ' . number_format($fullOrder['shipping_amount'], 2) : 'Free' ?></p>
                                    <p><strong>Total:</strong> <span class="total-highlight">GHS <?= number_format($fullOrder['total_amount'] ?? 0, 2) ?></span></p>
                                </div>
                            </div>

                            <?php if (!empty($fullOrder['notes'])): ?>
                                <div class="order-instructions">
                                    <h5><i class="fas fa-comment"></i> Special Instructions</h5>
                                    <p><?= nl2br(htmlspecialchars($fullOrder['notes'])) ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Order Actions -->
                        <div class="order-actions">
                            <button onclick="toggleOrderDetails('<?= $orderId ?>')" class="btn btn-secondary">
                                <i class="fas fa-eye"></i> <span id="toggle-text-<?= $orderId ?>">View Details</span>
                            </button>
                            <a href="?page=invoice&order_id=<?= $orderId ?>" target="_blank" class="btn btn-secondary">
                                <i class="fas fa-file-invoice"></i> View Invoice
                            </a>
                            <a href="?page=products" class="btn btn-primary">
                                <i class="fas fa-shopping-cart"></i> Order Again
                            </a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Orders Summary -->
            <div class="orders-summary">
                <div class="summary-card">
                    <h3>Order Statistics</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="stat-number"><?= count($userOrders) ?></div>
                            <div class="stat-label">Total Orders</div>
                        </div>
                        <div class="stat-item">
                            <div class="stat-number">GHS <?= number_format(array_sum(array_map(function($order) { return (float)($order['total_amount'] ?? 0); }, $userOrders)), 2) ?></div>
                            <div class="stat-label">Total Spent</div>
                        </div>
                        <div class="stat-item">
                            <?php
                            // Calculate total items purchased
                            $totalItems = 0;
                            foreach ($userOrders as $order) {
                                $fullOrder = getOrderById($order['id']);
                                if ($fullOrder && !empty($fullOrder['items'])) {
                                    $totalItems += count($fullOrder['items']);
                                } elseif (!empty($order['item_count'])) {
                                    $totalItems += (int)$order['item_count'];
                                }
                            }
                            ?>
                            <div class="stat-number"><?= $totalItems ?></div>
                            <div class="stat-label">Items Purchased</div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>

<style>
.orders-container {
    max-width: 1200px;
    margin: 0 auto;
    padding: 20px;
}

.orders-header {
    text-align: center;
    margin-bottom: 40px;
}

.orders-header h1 {
    font-size: 32px;
    color: #333;
    margin-bottom: 10px;
}

.orders-header p {
    color: #666;
    font-size: 16px;
}

/* No Orders State */
.no-orders {
    text-align: center;
    padding: 80px 20px;
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
}

.no-orders-icon {
    font-size: 80px;
    color: #ddd;
    margin-bottom: 30px;
}

.no-orders h2 {
    font-size: 24px;
    color: #333;
    margin-bottom: 15px;
}

.no-orders p {
    color: #666;
    margin-bottom: 30px;
    font-size: 16px;
}

/* Orders List */
.orders-list {
    display: flex;
    flex-direction: column;
    gap: 25px;
    margin-bottom: 40px;
}

.order-card {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    overflow: hidden;
    transition: transform 0.3s ease;
}

.order-card:hover {
    transform: translateY(-2px);
}

.order-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 25px;
    border-bottom: 1px solid #f0f0f0;
    background: #f8f9fa;
}

.order-info h3 {
    font-size: 20px;
    color: #333;
    margin-bottom: 8px;
}

.order-meta {
    display: flex;
    gap: 20px;
    align-items: center;
}

.order-date {
    color: #666;
    font-size: 14px;
}

.order-status {
    padding: 4px 12px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
}

.order-status.confirmed {
    background: #d4edda;
    color: #155724;
}

.order-total {
    text-align: right;
}

.total-label {
    display: block;
    color: #666;
    font-size: 14px;
    margin-bottom: 4px;
}

.total-amount {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-orange);
}

/* Order Items */
.order-items {
    padding: 25px;
    border-bottom: 1px solid #f0f0f0;
}

.order-items h4 {
    margin-bottom: 15px;
    color: #333;
}

.items-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
    gap: 15px;
}

.order-item {
    display: flex;
    gap: 15px;
    align-items: center;
    padding: 15px;
    background: #f8f9fa;
    border-radius: 8px;
}

.item-image {
    position: relative;
    width: 60px;
    height: 60px;
    border-radius: 8px;
    overflow: hidden;
    flex-shrink: 0;
}

.item-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.item-qty {
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

.item-details h5 {
    margin-bottom: 4px;
    color: #333;
    font-size: 14px;
}

.item-variant {
    font-size: 12px;
    color: #667eea;
    background: #e6f3ff;
    padding: 2px 6px;
    border-radius: 10px;
    display: inline-block;
    margin-bottom: 4px;
}

.item-vendor {
    font-size: 12px;
    color: #666;
    margin-bottom: 4px;
}

.item-price {
    font-weight: 600;
    color: var(--primary-orange);
    font-size: 14px;
}

/* Order Details */
.order-details {
    padding: 25px;
    background: #f8f9fa;
    display: none; /* Hidden by default */
}

.details-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 25px;
    margin-bottom: 20px;
}

.detail-section h5 {
    color: #333;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.detail-section p {
    margin-bottom: 5px;
    color: #666;
    font-size: 14px;
}

.payment-status {
    color: #27ae60;
    font-weight: 600;
}

.total-highlight {
    color: var(--primary-orange);
    font-weight: 600;
}

.order-instructions {
    margin-top: 20px;
    padding: 15px;
    background: white;
    border-radius: 8px;
    border-left: 4px solid var(--primary-orange);
}

.order-instructions h5 {
    color: #333;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    gap: 8px;
}

/* Order Actions */
.order-actions {
    padding: 20px 25px;
    display: flex;
    gap: 15px;
    justify-content: flex-end;
}

/* Orders Summary */
.orders-summary {
    background: white;
    border-radius: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    padding: 30px;
}

.summary-card h3 {
    margin-bottom: 20px;
    color: #333;
}

.stats-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
    gap: 20px;
}

.stat-item {
    text-align: center;
    padding: 20px;
    background: #f8f9fa;
    border-radius: 8px;
}

.stat-number {
    font-size: 28px;
    font-weight: 700;
    color: var(--primary-orange);
    margin-bottom: 5px;
}

.stat-label {
    color: #666;
    font-size: 14px;
}

/* Buttons */
.btn {
    padding: 10px 20px;
    border: none;
    border-radius: 8px;
    font-weight: 600;
    text-decoration: none;
    cursor: pointer;
    transition: all 0.3s ease;
    display: inline-flex;
    align-items: center;
    gap: 8px;
    font-size: 14px;
}

.btn-primary {
    background: var(--primary-orange);
    color: white;
}

.btn-primary:hover {
    background: #d35400;
    transform: translateY(-1px);
}

.btn-secondary {
    background: #f8f9fa;
    color: #333;
    border: 1px solid #ddd;
}

.btn-secondary:hover {
    background: #e9ecef;
}

/* Mobile Responsive */
@media (max-width: 768px) {
    .order-header {
        flex-direction: column;
        gap: 15px;
        text-align: center;
    }
    
    .order-meta {
        flex-direction: column;
        gap: 10px;
    }
    
    .items-grid {
        grid-template-columns: 1fr;
    }
    
    .details-grid {
        grid-template-columns: 1fr;
    }
    
    .order-actions {
        flex-direction: column;
    }
    
    .stats-grid {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
function toggleOrderDetails(orderId) {
    const detailsSection = document.querySelector(`#order-${orderId} .order-details`) || 
                          document.querySelector(`.order-card:nth-child(${Array.from(document.querySelectorAll('.order-card')).findIndex(card => card.innerHTML.includes(orderId)) + 1}) .order-details`);
    const toggleText = document.getElementById(`toggle-text-${orderId}`);
    
    if (detailsSection) {
        if (detailsSection.style.display === 'none' || detailsSection.style.display === '') {
            detailsSection.style.display = 'block';
            toggleText.textContent = 'Hide Details';
        } else {
            detailsSection.style.display = 'none';
            toggleText.textContent = 'View Details';
        }
    }
}
</script>
