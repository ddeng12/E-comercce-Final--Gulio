<?php
$category = $_GET['category'] ?? '';
try {
    $vendors = getVendors(5.6037, -0.1870, $category);
    if (!is_array($vendors)) {
        $vendors = [];
    }
} catch (Exception $e) {
    Logger::error('Error loading vendors', ['error' => $e->getMessage()]);
    $vendors = [];
}
?>

<div class="vendors-screen">
    <div class="header">
        <button class="back-btn" onclick="history.back()">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h1>Nearby Services</h1>
        <p><?= count($vendors) ?> services found</p>
    </div>
    
    <div class="content">
        <!-- Search and Filters -->
        <div class="search-section">
            <div class="search-bar">
                <i class="fas fa-search" aria-hidden="true"></i>
                <input type="text" placeholder="Search services..." id="search-input" aria-label="Search for services" autocomplete="off">
            </div>
            
            <div class="category-filters">
                <button class="filter-btn <?= $category == '' ? 'active' : '' ?>" data-filter="" aria-label="Show all services" aria-pressed="<?= $category == '' ? 'true' : 'false' ?>">
                    <i class="fas fa-th" aria-hidden="true"></i>
                    All
                </button>
                <button class="filter-btn <?= $category == 'barber' ? 'active' : '' ?>" data-filter="barber">
                    <i class="fas fa-cut"></i>
                    Barber
                </button>
                <button class="filter-btn <?= $category == 'food' ? 'active' : '' ?>" data-filter="food">
                    <i class="fas fa-utensils"></i>
                    Food
                </button>
                <button class="filter-btn <?= $category == 'phone_repair' ? 'active' : '' ?>" data-filter="phone_repair">
                    <i class="fas fa-mobile-alt"></i>
                    Phone Repair
                </button>
                <button class="filter-btn <?= $category == 'tailor' ? 'active' : '' ?>" data-filter="tailor">
                    <i class="fas fa-tshirt"></i>
                    Tailor
                </button>
                <button class="filter-btn <?= $category == 'laundry' ? 'active' : '' ?>" data-filter="laundry">
                    <i class="fas fa-tshirt"></i>
                    Laundry
                </button>
            </div>
        </div>
        
        <!-- Sort Options -->
        <div class="sort-section">
            <div class="sort-options">
                <button class="sort-btn active" data-sort="distance" aria-label="Sort by distance" aria-pressed="true">
                    <i class="fas fa-map-marker-alt" aria-hidden="true"></i>
                    Distance
                </button>
                <button class="sort-btn" data-sort="rating" aria-label="Sort by rating" aria-pressed="false">
                    <i class="fas fa-star" aria-hidden="true"></i>
                    Rating
                </button>
                <button class="sort-btn" data-sort="price" aria-label="Sort by price" aria-pressed="false">
                    <i class="fas fa-dollar-sign" aria-hidden="true"></i>
                    Price
                </button>
                <button class="sort-btn" data-sort="verified" aria-label="Sort by verification status" aria-pressed="false">
                    <i class="fas fa-shield-alt" aria-hidden="true"></i>
                    Verified
                </button>
            </div>
        </div>
        
        <!-- Vendors List -->
        <div id="vendors-list" class="vendors-list" role="list" aria-label="List of nearby services">
            <?php if (empty($vendors)): ?>
            <div class="empty-state" role="status" aria-live="polite">
                <div class="empty-icon" aria-hidden="true">
                    <i class="fas fa-search"></i>
                </div>
                <h3>No services found</h3>
                <p>Try adjusting your filters or search terms to find services in your area.</p>
                <button class="btn btn-primary" onclick="clearFilters()" aria-label="Clear all filters">
                    <i class="fas fa-redo"></i>
                    Clear Filters
                </button>
            </div>
            <?php else: ?>
            <?php foreach ($vendors as $vendor): ?>
            <div class="vendor-card" data-vendor-id="<?= $vendor['id'] ?>" role="listitem">
                <div class="vendor-header">
                    <div class="vendor-info">
                        <h3><?= htmlspecialchars($vendor['name']) ?></h3>
                        <div class="vendor-category"><?= ucfirst(str_replace('_', ' ', $vendor['category'])) ?></div>
                        <div class="vendor-address">
                            <i class="fas fa-map-marker-alt"></i>
                            <?= htmlspecialchars($vendor['address'] ?? 'Address not available') ?>
                        </div>
                    </div>
                    <div class="vendor-rating">
                        <span class="trust-score"><?= number_format($vendor['trust_score'] ?? 4.5, 1) ?></span>
                        <div class="stars">
                            <?php 
                            $rating = floor($vendor['trust_score'] ?? 4.5);
                            for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $rating ? 'filled' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                </div>
                
                <div class="vendor-details">
                    <div class="detail-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span><?= formatDistance($vendor['distance']) ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-dollar-sign"></i>
                        <span><?= !empty($vendor['price_items']) ? formatPrice($vendor['price_items'][0]) : 'Price on request' ?></span>
                    </div>
                    <div class="detail-item">
                        <i class="fas fa-clock"></i>
                        <span><?= rand(5, 30) ?> min</span>
                    </div>
                </div>
                
                <?php if (!empty($vendor['badges'])): ?>
                <div class="vendor-badges">
                    <?php foreach ($vendor['badges'] as $badge): ?>
                    <span class="badge badge-<?= strpos($badge, 'Scout') !== false ? 'scout' : (strpos($badge, 'Local') !== false ? 'local' : 'verified') ?>">
                        <?= htmlspecialchars($badge) ?>
                    </span>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($vendor['languages'])): ?>
                <div class="vendor-languages">
                    <i class="fas fa-language"></i>
                    <span><?= implode(', ', $vendor['languages']) ?></span>
                </div>
                <?php endif; ?>
                
                <div class="vendor-actions">
                    <button class="btn btn-outline btn-sm" data-vendor-id="<?= $vendor['id'] ?>" aria-label="View details for <?= htmlspecialchars($vendor['name']) ?>">
                        <i class="fas fa-info-circle" aria-hidden="true"></i>
                        Details
                    </button>
                    <button class="btn btn-primary btn-sm" data-book-vendor="<?= $vendor['id'] ?>" aria-label="Book appointment with <?= htmlspecialchars($vendor['name']) ?>">
                        <i class="fas fa-calendar" aria-hidden="true"></i>
                        Book
                    </button>
                    <button class="btn btn-secondary btn-sm" data-call-vendor="<?= $vendor['id'] ?>" aria-label="Call <?= htmlspecialchars($vendor['name']) ?>">
                        <i class="fas fa-phone" aria-hidden="true"></i>
                        Call
                    </button>
                </div>
            </div>
            <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.vendors-screen {
    min-height: 100vh;
    background: var(--light-gray);
}

.vendors-screen .header {
    background: linear-gradient(135deg, var(--primary-orange), var(--primary-teal));
    color: var(--white);
    padding: var(--space-lg) var(--space-md) var(--space-md);
    text-align: center;
    position: relative;
}

.vendors-screen .header h1 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: var(--space-xs);
}

.vendors-screen .header p {
    font-size: 14px;
    opacity: 0.9;
}

.search-section {
    background: var(--white);
    padding: var(--space-md);
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
    border: 2px solid var(--medium-gray);
    border-radius: var(--radius-md);
    font-size: 16px;
}

.search-bar input:focus {
    outline: none;
    border-color: var(--primary-orange);
}

.category-filters {
    display: flex;
    gap: var(--space-sm);
    overflow-x: auto;
    padding-bottom: var(--space-sm);
}

.filter-btn {
    background: var(--light-gray);
    border: 1px solid var(--medium-gray);
    border-radius: var(--radius-md);
    padding: var(--space-sm) var(--space-md);
    font-size: 12px;
    font-weight: 500;
    color: var(--text-medium);
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: var(--space-xs);
}

.filter-btn.active {
    background: var(--primary-orange);
    color: var(--white);
    border-color: var(--primary-orange);
}

.sort-section {
    background: var(--white);
    padding: var(--space-md);
    margin-bottom: var(--space-md);
    box-shadow: var(--shadow-sm);
}

.sort-options {
    display: flex;
    gap: var(--space-sm);
    overflow-x: auto;
    padding-bottom: var(--space-sm);
}

.sort-btn {
    background: var(--light-gray);
    border: 1px solid var(--medium-gray);
    border-radius: var(--radius-md);
    padding: var(--space-sm) var(--space-md);
    font-size: 12px;
    font-weight: 500;
    color: var(--text-medium);
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
    display: flex;
    align-items: center;
    gap: var(--space-xs);
}

.sort-btn.active {
    background: var(--primary-teal);
    color: var(--white);
    border-color: var(--primary-teal);
}

.vendors-list {
    padding: 0 var(--space-md);
}

.vendor-card {
    background: var(--white);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
    margin-bottom: var(--space-md);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--medium-gray);
    cursor: pointer;
    transition: all 0.3s ease;
}

.vendor-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.vendor-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: var(--space-md);
}

.vendor-info h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--space-xs);
}

.vendor-category {
    font-size: 12px;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin-bottom: var(--space-xs);
}

.vendor-address {
    font-size: 14px;
    color: var(--text-medium);
    display: flex;
    align-items: center;
    gap: var(--space-xs);
}

.vendor-rating {
    text-align: right;
}

.trust-score {
    background: var(--verified-green);
    color: var(--white);
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-sm);
    font-size: 14px;
    font-weight: 600;
    display: inline-block;
    margin-bottom: var(--space-xs);
}

.stars {
    display: flex;
    gap: 2px;
}

.stars .fa-star {
    color: var(--accent-yellow);
    font-size: 12px;
}

.stars .fa-star:not(.filled) {
    color: var(--medium-gray);
}

.vendor-details {
    display: flex;
    gap: var(--space-lg);
    margin-bottom: var(--space-md);
    padding: var(--space-md) 0;
    border-top: 1px solid var(--medium-gray);
    border-bottom: 1px solid var(--medium-gray);
}

.detail-item {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    font-size: 14px;
    color: var(--text-medium);
}

.detail-item i {
    color: var(--primary-orange);
    width: 16px;
}

.vendor-badges {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-xs);
    margin-bottom: var(--space-md);
}

.badge {
    padding: 4px 8px;
    border-radius: var(--radius-sm);
    font-size: 11px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.badge-scout {
    background: var(--verified-green);
    color: var(--white);
}

.badge-local {
    background: var(--primary-teal);
    color: var(--white);
}

.badge-verified {
    background: var(--primary-orange);
    color: var(--white);
}

.vendor-languages {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    font-size: 14px;
    color: var(--text-medium);
    margin-bottom: var(--space-md);
}

.vendor-languages i {
    color: var(--primary-teal);
}

.vendor-actions {
    display: flex;
    gap: var(--space-sm);
}

.vendor-actions .btn {
    flex: 1;
}

.empty-state {
    text-align: center;
    padding: var(--space-2xl) var(--space-md);
}

.empty-icon {
    font-size: 64px;
    color: var(--text-light);
    margin-bottom: var(--space-lg);
}

.empty-state h3 {
    font-size: 20px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--space-md);
}

.empty-state p {
    font-size: 16px;
    color: var(--text-medium);
    margin-bottom: var(--space-lg);
}

/* Modal Styles */
.modal {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.5);
    display: flex;
    align-items: center;
    justify-content: center;
    z-index: 10000;
    padding: var(--space-md);
}

.modal-content {
    background: var(--white);
    border-radius: var(--radius-lg);
    max-width: 500px;
    width: 100%;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: var(--shadow-lg);
}

.modal-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: var(--space-lg);
    border-bottom: 1px solid var(--medium-gray);
}

.modal-title {
    font-size: 20px;
    font-weight: 600;
    color: var(--text-dark);
    margin: 0;
}

.modal-close {
    background: none;
    border: none;
    font-size: 28px;
    color: var(--text-medium);
    cursor: pointer;
    padding: 0;
    width: 32px;
    height: 32px;
    display: flex;
    align-items: center;
    justify-content: center;
    border-radius: var(--radius-sm);
    transition: background 0.3s ease;
}

.modal-close:hover {
    background: var(--light-gray);
}

#booking-form {
    padding: var(--space-lg);
}

.form-group {
    margin-bottom: var(--space-md);
}

.form-label {
    display: block;
    margin-bottom: var(--space-xs);
    font-weight: 500;
    color: var(--text-dark);
    font-size: 14px;
}

.form-select,
.form-input {
    width: 100%;
    padding: var(--space-md);
    border: 2px solid var(--medium-gray);
    border-radius: var(--radius-md);
    font-size: 16px;
    transition: border-color 0.3s ease;
}

.form-select:focus,
.form-input:focus {
    outline: none;
    border-color: var(--primary-orange);
}

.checkbox-item {
    margin: var(--space-md) 0;
    padding: var(--space-md);
    background: var(--light-gray);
    border-radius: var(--radius-md);
}

.checkbox-item input[type="checkbox"] {
    margin-right: var(--space-sm);
}

.checkbox-content {
    display: flex;
    flex-direction: column;
    gap: var(--space-xs);
}

.checkbox-title {
    font-weight: 600;
    color: var(--text-dark);
}

.checkbox-description {
    font-size: 14px;
    color: var(--text-medium);
}

.form-actions {
    display: flex;
    gap: var(--space-sm);
    margin-top: var(--space-lg);
}

.btn-outline {
    background: var(--white);
    border: 2px solid var(--medium-gray);
    color: var(--text-dark);
}

.btn-outline:hover {
    background: var(--light-gray);
}

.btn-sm {
    padding: var(--space-sm) var(--space-md);
    font-size: 14px;
}

/* Toast Notification Styles */
.toast-notification {
    display: flex;
    align-items: center;
    font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, Cantarell, sans-serif;
}

.toast-content {
    display: flex;
    align-items: center;
    gap: 12px;
    width: 100%;
}

.toast-icon {
    font-size: 20px;
    display: flex;
    align-items: center;
    flex-shrink: 0;
}

.toast-message {
    flex: 1;
    line-height: 1.4;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('search-input');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const vendorCards = document.querySelectorAll('.vendor-card');
            
            vendorCards.forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const category = card.querySelector('.vendor-category').textContent.toLowerCase();
                const address = card.querySelector('.vendor-address').textContent.toLowerCase();
                
                if (name.includes(query) || category.includes(query) || address.includes(query)) {
                    card.style.display = 'block';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    }
    
    // Filter buttons
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const filter = this.dataset.filter;
            window.location.href = `?page=vendors&category=${filter}`;
        });
    });
    
    // Sort buttons
    const sortBtns = document.querySelectorAll('.sort-btn');
    sortBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            sortBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const sortBy = this.dataset.sort;
            sortVendors(sortBy);
        });
    });
    
    // Vendor card clicks
    const vendorCards = document.querySelectorAll('.vendor-card');
    vendorCards.forEach(card => {
        card.addEventListener('click', function(e) {
            if (!e.target.closest('.btn')) {
                const vendorId = this.dataset.vendorId;
                window.location.href = `?page=vendor-detail&id=${vendorId}`;
            }
        });
    });
    
    // Booking buttons
    const bookBtns = document.querySelectorAll('[data-book-vendor]');
    bookBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.stopPropagation();
            const vendorId = this.dataset.bookVendor;
            await showVendorBookingModal(vendorId);
        });
    });
});

function sortVendors(sortBy) {
    const container = document.getElementById('vendors-list');
    const cards = Array.from(container.querySelectorAll('.vendor-card'));
    
    cards.sort((a, b) => {
        switch (sortBy) {
            case 'distance':
                const distanceTextA = a.querySelector('.detail-item span')?.textContent || '0 km';
                const distanceTextB = b.querySelector('.detail-item span')?.textContent || '0 km';
                const distanceA = parseFloat(distanceTextA.replace(/[^\d.]/g, '')) || 0;
                const distanceB = parseFloat(distanceTextB.replace(/[^\d.]/g, '')) || 0;
                return distanceA - distanceB;
                
            case 'rating':
                const ratingElA = a.querySelector('.trust-score');
                const ratingElB = b.querySelector('.trust-score');
                const ratingA = ratingElA ? parseFloat(ratingElA.textContent) || 0 : 0;
                const ratingB = ratingElB ? parseFloat(ratingElB.textContent) || 0 : 0;
                return ratingB - ratingA;
                
            case 'price':
                const priceA = a.querySelector('.vendor-price, .service-price');
                const priceB = b.querySelector('.vendor-price, .service-price');
                if (!priceA || !priceB) return 0;
                
                const priceTextA = priceA.textContent.match(/[\d.]+/);
                const priceTextB = priceB.textContent.match(/[\d.]+/);
                if (!priceTextA || !priceTextB) return 0;
                
                const numA = parseFloat(priceTextA[0]);
                const numB = parseFloat(priceTextB[0]);
                return numA - numB;
                
            case 'verified':
                const badgesA = a.querySelectorAll('.badge').length;
                const badgesB = b.querySelectorAll('.badge').length;
                return badgesB - badgesA;
                
            default:
                return 0;
        }
    });
    
    cards.forEach(card => container.appendChild(card));
}

function clearFilters() {
    window.location.href = '?page=vendors';
}

async function showVendorBookingModal(vendorId) {
    // Check if user is logged in
    const userId = <?= json_encode($_SESSION['user']['id'] ?? null) ?>;
    if (!userId) {
        // Save the vendor detail page as return URL
        const returnUrl = encodeURIComponent('?page=vendor-detail&id=' + vendorId + '&book=1');
        window.location.href = '?page=login&return=' + returnUrl;
        return;
    }

    // Get vendor data from the current page if available
    const vendorCard = document.querySelector(`[data-vendor-id="${vendorId}"]`);
    const vendorName = vendorCard?.querySelector('h3')?.textContent || 'Vendor';
    const priceItems = [];
    
    // Try to get price from the card
    const priceEl = vendorCard?.querySelector('.vendor-price, .service-price, .price');
    if (priceEl) {
        priceItems.push({ service: 'General Service', price: priceEl.textContent.trim() });
    } else {
        priceItems.push({ service: 'General Service', price: 'GHS 0' });
    }
    
    // Try to fetch more detailed vendor data
    try {
        const response = await fetch(`?page=vendor-detail&id=${vendorId}`);
        if (response.ok) {
            const html = await response.text();
            const parser = new DOMParser();
            const doc = parser.parseFromString(html, 'text/html');
            
            // Extract price items from the pricing section
            const priceList = doc.querySelectorAll('.price-item');
            if (priceList.length > 0) {
                priceItems.length = 0; // Clear default
                priceList.forEach(item => {
                    const service = item.querySelector('.service-name')?.textContent?.trim();
                    const priceText = item.querySelector('.service-price')?.textContent?.trim() || '';
                    if (service) {
                        priceItems.push({ service, price: priceText });
                    }
                });
            }
        }
    } catch (error) {
        console.log('Could not fetch detailed vendor data, using basic info');
    }

        // Create modal
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Book ${vendorName}</h2>
                    <button class="modal-close">&times;</button>
                </div>
                
                <form id="booking-form">
                    <div class="form-group">
                        <label class="form-label">Service</label>
                        <select class="form-select" name="service" required>
                            <option value="">Select a service</option>
                            ${priceItems.map(item => 
                                `<option value="${item.service}">${item.service} - ${item.price}</option>`
                            ).join('')}
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Preferred Date & Time</label>
                        <input type="datetime-local" class="form-input" name="datetime" required min="${new Date().toISOString().slice(0, 16)}">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Meeting Point</label>
                        <input type="text" class="form-input" name="meeting_point" 
                               placeholder="e.g., Main entrance, Shop front" required>
                    </div>
                    
                    <div class="checkbox-item">
                        <input type="checkbox" id="safety-confirm" required>
                        <label for="safety-confirm" class="checkbox-content">
                            <div class="checkbox-title">Safety Confirmation</div>
                            <div class="checkbox-description">
                                I understand to meet in a public place and verify the vendor's identity
                            </div>
                        </label>
                    </div>
                    
                    <div class="form-actions">
                        <button type="button" class="btn btn-outline" onclick="this.closest('.modal').remove()">
                            Cancel
                        </button>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-calendar-check"></i> Confirm Booking
                        </button>
                    </div>
                </form>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Close modal handlers
        modal.querySelector('.modal-close').addEventListener('click', () => modal.remove());
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });
        
        // Handle form submission
        const bookingForm = modal.querySelector('#booking-form');
        const submitButton = bookingForm.querySelector('button[type="submit"]');
        const originalButtonText = submitButton.innerHTML;
        
        bookingForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Disable button and show loading state
            submitButton.disabled = true;
            submitButton.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            const formData = new FormData(this);
            const bookingData = new FormData();
            bookingData.append('action', 'submit_booking');
            bookingData.append('vendor_id', vendorId);
            bookingData.append('service', formData.get('service'));
            bookingData.append('datetime', formData.get('datetime'));
            bookingData.append('meeting_point', formData.get('meeting_point'));
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                bookingData.append('csrf_token', csrfToken);
            }
            
            try {
                console.log('Submitting booking...', {
                    vendor_id: vendorId,
                    service: formData.get('service'),
                    datetime: formData.get('datetime'),
                    meeting_point: formData.get('meeting_point')
                });
                
                const response = await fetch('', {
                    method: 'POST',
                    body: bookingData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                console.log('Response status:', response.status);
                console.log('Response headers:', response.headers);
                
                const responseText = await response.text();
                console.log('Raw response:', responseText);
                
                let result;
                try {
                    result = JSON.parse(responseText);
                } catch (parseError) {
                    console.error('JSON parse error:', parseError);
                    console.error('Response was:', responseText);
                    throw new Error('Invalid response from server. Please try again.');
                }
                
                if (result.success) {
                    showToast('Booking confirmed! You will receive a confirmation SMS.', 'success');
                    setTimeout(() => {
                        modal.remove();
                        // Optionally reload the page or redirect
                        window.location.reload();
                    }, 1500);
                } else {
                    showToast(result.message || 'Error submitting booking', 'error');
                    submitButton.disabled = false;
                    submitButton.innerHTML = originalButtonText;
                }
            } catch (error) {
                console.error('Error submitting booking:', error);
                showToast(error.message || 'Error submitting booking. Please try again.', 'error');
                submitButton.disabled = false;
                submitButton.innerHTML = originalButtonText;
            }
        });
}

function showToast(message, type) {
    // Remove any existing toasts first
    const existingToasts = document.querySelectorAll('.toast-notification');
    existingToasts.forEach(toast => toast.remove());
    
    const toast = document.createElement('div');
    toast.className = `toast-notification toast-${type}`;
    
    const bgColor = type === 'success' ? '#27ae60' : (type === 'error' ? '#e74c3c' : '#f39c12');
    const icon = type === 'success' ? '<i class="fas fa-check-circle"></i>' : (type === 'error' ? '<i class="fas fa-exclamation-circle"></i>' : '<i class="fas fa-info-circle"></i>');
    
    toast.innerHTML = `
        <div class="toast-content">
            <span class="toast-icon">${icon}</span>
            <span class="toast-message">${message}</span>
        </div>
    `;
    
    toast.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${bgColor};
        color: white;
        padding: 16px 20px;
        border-radius: 12px;
        z-index: 100000;
        font-weight: 500;
        font-size: 15px;
        box-shadow: 0 8px 24px rgba(0,0,0,0.2);
        min-width: 300px;
        max-width: 400px;
        opacity: 0;
        transform: translateX(400px);
        transition: all 0.4s cubic-bezier(0.68, -0.55, 0.265, 1.55);
    `;
    
    document.body.appendChild(toast);
    
    // Trigger animation
    setTimeout(() => {
        toast.style.opacity = '1';
        toast.style.transform = 'translateX(0)';
    }, 10);
    
    // Auto remove after delay
    setTimeout(() => {
        toast.style.opacity = '0';
        toast.style.transform = 'translateX(400px)';
        setTimeout(() => {
            if (toast.parentNode) {
                toast.remove();
            }
        }, 400);
    }, 4000);
}
</script>

