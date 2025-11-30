<?php
require_once __DIR__ . '/../includes/products.php';
require_once __DIR__ . '/../includes/cart.php';
require_once __DIR__ . '/../includes/helpers.php';

// Get filters from URL
$category = $_GET['category'] ?? '';
$search = $_GET['search'] ?? '';
$minPrice = $_GET['min_price'] ?? '';
$maxPrice = $_GET['max_price'] ?? '';
$sort = $_GET['sort'] ?? 'newest';
$page = max(1, (int)($_GET['p'] ?? 1));

// Build filters array
$filters = [];
if ($category) $filters['category'] = $category;
if ($search) $filters['search'] = $search;
if ($minPrice) $filters['min_price'] = (float)$minPrice;
if ($maxPrice) $filters['max_price'] = (float)$maxPrice;

// Pagination
$limit = 12;
$offset = ($page - 1) * $limit;

// Get products
$products = getProducts($filters, $limit, $offset);
$totalProducts = getProductCount($filters);
$totalPages = ceil($totalProducts / $limit);

// Initialize cart
$cart = new ShoppingCart();
$cartCount = $cart->getItemCount();
?>

<div class="products-screen">
    <div class="header">
        <button class="back-btn" onclick="history.back()">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h1>Local Marketplace</h1>
        <p>Authentic Ghanaian products & crafts</p>
        <div class="cart-icon" onclick="showCart()">
            <i class="fas fa-shopping-cart"></i>
            <span class="cart-count" id="cart-count"><?= $cartCount ?></span>
        </div>
    </div>
    
    <div class="content">
        <!-- FEATURED PRODUCTS SECTION REMOVED - Cache Buster: <?= time() ?> -->
        
        
        <!-- Search and Filters -->
        <div class="search-filters">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" id="product-search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
            </div>
            
            <div class="filter-row">
                <div class="price-filter">
                    <label>Price Range:</label>
                    <input type="number" id="min-price" placeholder="Min" value="<?= $minPrice ?>" min="0">
                    <span>to</span>
                    <input type="number" id="max-price" placeholder="Max" value="<?= $maxPrice ?>" min="0">
                </div>
                
                <div class="sort-filter">
                    <label>Sort by:</label>
                    <select id="sort-select">
                        <option value="newest" <?= $sort === 'newest' ? 'selected' : '' ?>>Newest First</option>
                        <option value="price_low" <?= $sort === 'price_low' ? 'selected' : '' ?>>Price: Low to High</option>
                        <option value="price_high" <?= $sort === 'price_high' ? 'selected' : '' ?>>Price: High to Low</option>
                        <option value="rating" <?= $sort === 'rating' ? 'selected' : '' ?>>Highest Rated</option>
                        <option value="popular" <?= $sort === 'popular' ? 'selected' : '' ?>>Most Popular</option>
                    </select>
                </div>
                
                <button class="filter-btn" onclick="applyFilters()">
                    <i class="fas fa-filter"></i> Apply
                </button>
            </div>
        </div>
        
        <!-- Products Grid -->
        <div class="products-section">
            <div class="section-header">
                <h3>
                    <?php if ($category): ?>
                        <?= htmlspecialchars(ucwords(str_replace('-', ' ', $category))) ?>
                    <?php elseif ($search): ?>
                        Search Results for "<?= htmlspecialchars($search) ?>"
                    <?php else: ?>
                        All Products
                    <?php endif; ?>
                </h3>
                <span class="results-count"><?= $totalProducts ?> products found</span>
            </div>
            
            <div class="products-grid" id="products-grid">
                <?php foreach ($products as $product): ?>
                <div class="product-card" data-product-id="<?= $product['id'] ?>">
                    <div class="product-image" onclick="viewProduct(<?= $product['id'] ?>)">
                        <img src="<?= htmlspecialchars(getProductImageUrl($product['image_url'] ?? '')) ?>" 
                             alt="<?= htmlspecialchars($product['name']) ?>" 
                             style="width: 100%; height: 100%; object-fit: cover; display: block; background: #f8f9fa;"
                             onerror="this.src='https://via.placeholder.com/240x240/f0f0f0/999999?text=No+Image';"
                             onload="this.style.opacity='1';"
                             loading="eager">
                        <?php if (isset($product['has_discount']) && $product['has_discount']): ?>
                        <div class="discount-badge">-<?= $product['discount_percent'] ?>%</div>
                        <?php endif; ?>
                        <?php if (!$product['stock_quantity']): ?>
                        <div class="out-of-stock-overlay">Out of Stock</div>
                        <?php endif; ?>
                    </div>
                    
                    <div class="product-info">
                        <h4 class="product-name" onclick="viewProduct(<?= $product['id'] ?>)"><?= htmlspecialchars($product['name']) ?></h4>
                        
                        <div class="product-price">
                            <span class="current-price">GHS <?= number_format($product['price'], 2) ?></span>
                        </div>
                        
                        <div class="vendor-name">
                            <span class="category-tag"><?= ucfirst(str_replace('_', ' ', $product['category'])) ?></span>
                            <span class="brand-name"><?= htmlspecialchars($product['vendor_name'] ?? 'Kofi\'s Cuts') ?></span>
                        </div>
                        
                        <div class="product-actions">
                            <button class="btn btn-primary" onclick="viewProduct(<?= $product['id'] ?>)">
                                View Product
                            </button>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <?php if ($page > 1): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $page - 1])) ?>" class="page-btn">
                    <i class="fas fa-chevron-left"></i> Previous
                </a>
                <?php endif; ?>
                
                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $i])) ?>" 
                   class="page-btn <?= $i === $page ? 'active' : '' ?>">
                    <?= $i ?>
                </a>
                <?php endfor; ?>
                
                <?php if ($page < $totalPages): ?>
                <a href="?<?= http_build_query(array_merge($_GET, ['p' => $page + 1])) ?>" class="page-btn">
                    Next <i class="fas fa-chevron-right"></i>
                </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php
function getCategoryIcon($slug) {
    $icons = [
        'artisan-crafts' => 'hammer',
        'textiles' => 'tshirt',
        'art' => 'palette',
        'fresh-produce' => 'apple-alt',
        'cultural-items' => 'star-and-crescent',
        'traditional-clothing' => 'user-tie',
        'accessories' => 'gem',
        'local-brands' => 'store',
        'beauty' => 'spa',
        'food-spices' => 'pepper-hot'
    ];
    
    return $icons[$slug] ?? 'box';
}
?>

<style>
.products-screen {
    min-height: 100vh;
    background: var(--light-gray);
}

.products-screen .header {
    background: linear-gradient(135deg, var(--primary-orange), var(--primary-teal));
    color: var(--white);
    padding: var(--space-lg) var(--space-md) var(--space-md);
    text-align: center;
    position: relative;
}

.products-screen .header h1 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: var(--space-xs);
}

.products-screen .header p {
    font-size: 14px;
    opacity: 0.9;
}

.cart-icon {
    position: absolute;
    top: var(--space-lg);
    right: var(--space-md);
    cursor: pointer;
    font-size: 24px;
}

.cart-count {
    position: absolute;
    top: -8px;
    right: -8px;
    background: var(--accent-red);
    color: var(--white);
    border-radius: 50%;
    width: 20px;
    height: 20px;
    font-size: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.products-section {
    background: white;
    margin-bottom: 20px;
    padding: 30px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}

.products-section h3 {
    font-size: 18px;
    font-weight: 600;
    margin-bottom: 20px;
    color: #333;
}

.search-filters {
    background: var(--white);
    padding: var(--space-lg);
    margin-bottom: var(--space-md);
    box-shadow: var(--shadow-sm);
}

.search-bar {
    position: relative;
    margin-bottom: var(--space-md);
}

.search-bar i {
    position: absolute;
    left: var(--space-md);
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
}

.search-bar input {
    width: 100%;
    padding: var(--space-md) var(--space-md) var(--space-md) 40px;
    border: 1px solid var(--medium-gray);
    border-radius: var(--radius-md);
    font-size: 16px;
}

.filter-row {
    display: flex;
    gap: var(--space-md);
    align-items: end;
    flex-wrap: wrap;
}

.price-filter,
.sort-filter {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    flex-wrap: wrap;
}

.price-filter input,
.sort-filter select {
    padding: var(--space-sm) var(--space-md);
    border: 1px solid var(--medium-gray);
    border-radius: var(--radius-sm);
    font-size: 14px;
}

.price-filter input {
    width: 80px;
}

.filter-btn {
    background: var(--primary-teal);
    color: var(--white);
    border: none;
    padding: var(--space-sm) var(--space-md);
    border-radius: var(--radius-md);
    cursor: pointer;
    font-weight: 500;
}

.section-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-lg);
}

.results-count {
    color: var(--text-medium);
    font-size: 14px;
}

.products-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(240px, 1fr));
    gap: 18px;
    margin-top: 20px;
}

.product-card {
    background: white;
    border-radius: 12px;
    padding: 12px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border: 1px solid #f0f0f0;
    display: flex;
    flex-direction: column;
    height: auto;
    min-height: 340px;
    max-height: 380px;
    width: 280px;
    overflow: hidden;
    position: relative;
    box-sizing: border-box;
}

.product-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 4px 12px rgba(0,0,0,0.15);
}

.product-image {
    position: relative;
    height: 170px;
    width: 100%;
    overflow: hidden;
    cursor: pointer;
    background: #f8f9fa;
    border-radius: 10px;
    margin-bottom: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
}

.product-image.no-image {
    display: flex;
    align-items: center;
    justify-content: center;
    background: #f8f9fa;
}

.product-image.no-image::before {
    content: "📦";
    font-size: 40px;
    color: #999;
}

.placeholder-icon {
    font-size: 40px;
    color: #999;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    height: 100%;
}

.product-image img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    object-position: center;
    border-radius: 8px;
    display: block !important;
    background: #fff;
    border: none;
    outline: none;
    opacity: 1;
    transition: opacity 0.3s ease;
}

.product-card:hover .product-image img {
    transform: scale(1.05);
}

.discount-badge {
    position: absolute;
    top: var(--space-sm);
    right: var(--space-sm);
    background: var(--accent-red);
    color: var(--white);
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 600;
}

.out-of-stock-overlay {
    position: absolute;
    inset: 0;
    background: rgba(0, 0, 0, 0.7);
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
}

.product-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 8px;
    min-height: 0;
    padding: 0;
    margin: 0;
    overflow: hidden;
}

.product-info h4,
.product-name {
    font-size: 16px;
    font-weight: 600;
    margin: 0 0 6px 0;
    padding: 0;
    cursor: pointer;
    color: #222;
    line-height: 1.4;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    display: block !important;
    visibility: visible !important;
    opacity: 1 !important;
    min-height: 22px;
    width: 100%;
    order: 1;
}

.product-info h4:hover,
.product-name:hover {
    color: var(--primary-orange);
}

.vendor-name {
    color: #666;
    font-size: 11px;
    margin: 0 0 8px 0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-wrap: wrap;
    gap: 4px;
    order: 3;
}

.category-tag {
    background: #e6f3ff;
    color: #667eea;
    padding: 3px 8px;
    border-radius: 12px;
    font-size: 11px;
    font-weight: 500;
    display: block;
    margin-bottom: 5px;
}

.brand-name {
    color: #666;
    font-size: 11px;
}

.cultural-context {
    display: flex;
    align-items: flex-start;
    gap: var(--space-xs);
    margin-bottom: var(--space-sm);
    padding: var(--space-sm);
    background: rgba(55, 198, 176, 0.1);
    border-radius: var(--radius-sm);
    font-size: 12px;
    color: var(--text-medium);
}

.cultural-context i {
    color: var(--primary-teal);
    margin-top: 2px;
}

.product-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    margin-bottom: 12px;
}

.badge {
    padding: 4px 8px;
    border-radius: 20px;
    font-size: 11px;
    font-weight: 500;
    text-transform: capitalize;
    letter-spacing: 0.2px;
}

.badge-scout {
    background: #e6f3ff;
    color: #667eea;
}

.badge-verified {
    background: #f0fff4;
    color: #38a169;
}

.product-price {
    margin: 0 0 8px 0;
    order: 2;
}

.current-price {
    font-size: 18px;
    font-weight: 700;
    color: #ff6b35;
    margin-bottom: 8px;
}

.compare-price {
    font-size: 14px;
    color: var(--text-light);
    text-decoration: line-through;
    margin-left: var(--space-sm);
}

.product-rating {
    display: flex;
    align-items: center;
    gap: 4px;
    margin-bottom: 8px;
}

.stars {
    display: flex;
    gap: 2px;
}

.stars .fa-star {
    color: #ffd700;
    font-size: 10px;
}

.stars .fa-star:not(.filled) {
    color: #ddd;
}

.product-rating span {
    font-size: 9px;
    color: #666;
}

.product-actions {
    margin-top: auto;
    padding-top: 8px;
    order: 4;
}

.product-actions .btn {
    width: 100%;
    padding: 8px 12px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 12px;
    border: none;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-outline {
    background: transparent;
    border: 2px solid #ff6b35 !important;
    color: #ff6b35;
}

.btn-outline:hover {
    background: #ff6b35;
    color: white;
}

.btn-primary {
    background: #ff6b35;
    color: white;
}

.btn-primary:hover {
    background: #e55a2b;
    transform: translateY(-1px);
}

.pagination {
    display: flex;
    justify-content: center;
    gap: var(--space-sm);
    margin-top: var(--space-xl);
}

.page-btn {
    padding: var(--space-sm) var(--space-md);
    background: var(--white);
    border: 1px solid var(--medium-gray);
    border-radius: var(--radius-sm);
    text-decoration: none;
    color: var(--text-dark);
    font-weight: 500;
    transition: all 0.3s ease;
}

.page-btn:hover,
.page-btn.active {
    background: var(--primary-orange);
    color: var(--white);
    border-color: var(--primary-orange);
}

@media (max-width: 768px) {
    .products-grid {
        grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
        gap: 12px;
    }
    
    .filter-row {
        flex-direction: column;
        align-items: stretch;
    }
    
    .product-card {
        height: 240px;
        width: 200px;
        padding: 8px;
    }
    
    .product-image {
        height: 140px;
    }
}

@media (max-width: 480px) {
    .products-grid {
        grid-template-columns: repeat(2, 1fr);
        gap: 10px;
    }
    
    .product-card {
        height: 220px;
        width: 180px;
        padding: 6px;
    }
    
    .product-image {
        height: 120px;
    }
    
    .product-info h4 {
        font-size: 12px;
    }
    
    .current-price {
        font-size: 14px;
    }
    
    .product-actions .btn {
        font-size: 10px;
        padding: 5px 8px;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('product-search');
    let searchTimeout;
    
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            clearTimeout(searchTimeout);
            searchTimeout = setTimeout(() => {
                applyFilters();
            }, 500);
        });
    }
    
    // Filter functionality
    const minPrice = document.getElementById('min-price');
    const maxPrice = document.getElementById('max-price');
    const sortSelect = document.getElementById('sort-select');
    
    if (minPrice) minPrice.addEventListener('change', applyFilters);
    if (maxPrice) maxPrice.addEventListener('change', applyFilters);
    if (sortSelect) sortSelect.addEventListener('change', applyFilters);
    
    // Handle image loading errors
    const productImages = document.querySelectorAll('.product-image img');
    productImages.forEach(img => {
        img.addEventListener('error', function() {
            this.style.display = 'none';
            this.parentElement.classList.add('no-image');
        });
        
        // Check if image is already broken
        if (img.complete && img.naturalHeight === 0) {
            img.style.display = 'none';
            img.parentElement.classList.add('no-image');
        }
    });
});

function filterByCategory(category) {
    const url = new URL(window.location);
    if (category) {
        url.searchParams.set('category', category);
    } else {
        url.searchParams.delete('category');
    }
    url.searchParams.delete('p'); // Reset to first page
    window.location.href = url.toString();
}

function applyFilters() {
    const url = new URL(window.location);
    
    const search = document.getElementById('product-search').value;
    const minPrice = document.getElementById('min-price').value;
    const maxPrice = document.getElementById('max-price').value;
    const sort = document.getElementById('sort-select').value;
    
    if (search) {
        url.searchParams.set('search', search);
    } else {
        url.searchParams.delete('search');
    }
    
    if (minPrice) {
        url.searchParams.set('min_price', minPrice);
    } else {
        url.searchParams.delete('min_price');
    }
    
    if (maxPrice) {
        url.searchParams.set('max_price', maxPrice);
    } else {
        url.searchParams.delete('max_price');
    }
    
    if (sort && sort !== 'newest') {
        url.searchParams.set('sort', sort);
    } else {
        url.searchParams.delete('sort');
    }
    
    url.searchParams.delete('p'); // Reset to first page
    window.location.href = url.toString();
}

function viewProduct(productId) {
    window.location.href = `?page=product-detail&id=${productId}`;
}

// Debug and fix image display
document.addEventListener('DOMContentLoaded', function() {
    const images = document.querySelectorAll('.product-image img');
    console.log('Found', images.length, 'product images');
    
    images.forEach((img, index) => {
        console.log('Image', index, 'src:', img.src);
        
        // Force display
        img.style.display = 'block !important';
        img.style.opacity = '1';
        img.style.visibility = 'visible';
        
        img.addEventListener('load', function() {
            console.log('Image loaded successfully:', this.src);
        });
        
        img.addEventListener('error', function() {
            console.log('Image failed to load, using fallback:', this.src);
            this.src = 'https://via.placeholder.com/240x240/e74c3c/ffffff?text=IMAGE';
        });
    });
});

async function addToCart(productId, variantId = null, quantity = 1) {
    try {
        const formData = new FormData();
        formData.append('action', 'add_to_cart');
        formData.append('product_id', productId);
        if (variantId) formData.append('variant_id', variantId);
        formData.append('quantity', quantity);
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
        }
        
        const response = await fetch('', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        if (response.ok) {
            const result = await response.json();
            if (result.success) {
                // Redirect to cart page after successful add
                window.location.href = '?page=cart';
            } else {
                showToast(result.message || 'Error adding to cart', 'error');
            }
        } else {
            showToast('Error adding to cart', 'error');
        }
    } catch (error) {
        console.error('Error:', error);
        showToast('Error adding to cart', 'error');
    }
}

function updateCartCount() {
    // Update cart count in header
    fetch('?action=get_cart_count', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            document.getElementById('cart-count').textContent = data.count;
        }
    })
    .catch(error => console.error('Error updating cart count:', error));
}

function showCart() {
    window.location.href = '?page=cart';
}

function showToast(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    toast.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:var(--primary-orange);color:white;padding:12px 24px;border-radius:8px;z-index:10000;';
    if (type === 'error') {
        toast.style.background = 'var(--accent-red)';
    }
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}
</script>
