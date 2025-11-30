<?php
// Validate vendor ID
$vendorId = isset($_GET['id']) ? filter_var($_GET['id'], FILTER_VALIDATE_INT) : null;

if (!$vendorId || $vendorId <= 0) {
    header('Location: ?page=vendors');
    exit;
}

$vendor = getVendorById($vendorId);
$reviews = getVendorReviews($vendorId);

if (!$vendor) {
    header('Location: ?page=vendors');
    exit;
}
?>

<div class="vendor-detail-screen">
    <div class="header">
        <button class="back-btn" onclick="history.back()" aria-label="Go back to previous page">
            <i class="fas fa-arrow-left" aria-hidden="true"></i>
        </button>
        <h1><?= htmlspecialchars($vendor['name']) ?></h1>
        <p><?= ucfirst(str_replace('_', ' ', $vendor['category'])) ?></p>
    </div>
    
    <div class="content">
        <!-- Vendor Gallery -->
        <div class="vendor-gallery">
            <div class="main-image">
                <i class="fas fa-<?= $vendor['category'] == 'barber' ? 'cut' : ($vendor['category'] == 'food' ? 'utensils' : ($vendor['category'] == 'phone_repair' ? 'mobile-alt' : 'store')) ?>"></i>
            </div>
            <div class="gallery-thumbnails">
                <div class="thumbnail active">
                    <i class="fas fa-<?= $vendor['category'] == 'barber' ? 'cut' : ($vendor['category'] == 'food' ? 'utensils' : ($vendor['category'] == 'phone_repair' ? 'mobile-alt' : 'store')) ?>"></i>
                </div>
                <div class="thumbnail">
                    <i class="fas fa-<?= $vendor['category'] == 'barber' ? 'cut' : ($vendor['category'] == 'food' ? 'utensils' : ($vendor['category'] == 'phone_repair' ? 'mobile-alt' : 'store')) ?>"></i>
                </div>
                <div class="thumbnail">
                    <i class="fas fa-<?= $vendor['category'] == 'barber' ? 'cut' : ($vendor['category'] == 'food' ? 'utensils' : ($vendor['category'] == 'phone_repair' ? 'mobile-alt' : 'store')) ?>"></i>
                </div>
            </div>
        </div>
        
        <!-- Vendor Info -->
        <div class="vendor-info-section">
            <div class="vendor-header">
                <div class="vendor-title">
                    <h2><?= htmlspecialchars($vendor['name']) ?></h2>
                    <div class="vendor-category"><?= ucfirst(str_replace('_', ' ', $vendor['category'])) ?></div>
                </div>
                <div class="vendor-rating">
                    <span class="trust-score"><?= $vendor['trust_score'] ?></span>
                    <div class="stars">
                        <?php for ($i = 1; $i <= 5; $i++): ?>
                            <i class="fas fa-star <?= $i <= floor($vendor['trust_score']) ? 'filled' : '' ?>"></i>
                        <?php endfor; ?>
                    </div>
                    <div class="rating-text"><?= $vendor['verified_reviews_count'] ?> verified reviews</div>
                </div>
            </div>
            
            <div class="vendor-address">
                <i class="fas fa-map-marker-alt"></i>
                <span><?= htmlspecialchars($vendor['address']) ?></span>
                <button class="btn btn-outline btn-sm" onclick="openMaps()">
                    <i class="fas fa-directions"></i>
                    Directions
                </button>
            </div>
            
            <div class="vendor-languages">
                <i class="fas fa-language"></i>
                <span>Speaks: <?= implode(', ', $vendor['languages']) ?></span>
            </div>
        </div>
        
        <!-- Trust Badges -->
        <div class="trust-badges-section">
            <h3>Trust & Verification</h3>
            <div class="trust-badges">
                <?php foreach ($vendor['badges'] as $badge): ?>
                <div class="trust-badge">
                    <i class="fas fa-<?= strpos($badge, 'Scout') !== false ? 'user-check' : (strpos($badge, 'Local') !== false ? 'users' : 'star') ?>"></i>
                    <span><?= htmlspecialchars($badge) ?></span>
                </div>
                <?php endforeach; ?>
                <div class="trust-badge">
                    <i class="fas fa-calendar"></i>
                    <span>Verified <?= getTimeAgo($vendor['last_verified_date']) ?></span>
                </div>
            </div>
        </div>
        
        <!-- Pricing -->
        <div class="pricing-section">
            <h3>Services & Pricing</h3>
            <div class="price-list">
                <?php foreach ($vendor['price_items'] as $item): ?>
                <div class="price-item">
                    <div class="service-name"><?= htmlspecialchars($item['service']) ?></div>
                    <div class="service-price"><?= formatPrice($item) ?></div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Cultural & Safety Tips -->
        <div class="tips-section">
            <h3>Cultural & Safety Tips</h3>
            <div class="tips-list">
                <div class="tip-item">
                    <i class="fas fa-handshake"></i>
                    <div class="tip-content">
                        <h4>Greeting</h4>
                        <p>Always greet with "Akwaaba" (welcome) and shake hands</p>
                    </div>
                </div>
                <div class="tip-item">
                    <i class="fas fa-clock"></i>
                    <div class="tip-content">
                        <h4>Timing</h4>
                        <p>Ghanaian time is flexible - arrive 15-30 minutes after agreed time</p>
                    </div>
                </div>
                <div class="tip-item">
                    <i class="fas fa-shield-alt"></i>
                    <div class="tip-content">
                        <h4>Safety</h4>
                        <p>Meet in public places and verify vendor identity before payment</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Reviews -->
        <div class="reviews-section">
            <div class="reviews-header">
                <h3>Verified Reviews</h3>
                <div class="review-stats">
                    <span class="verified-count"><?= $vendor['verified_reviews_count'] ?> verified</span>
                    <span class="total-count"><?= $vendor['total_reviews'] ?> total</span>
                </div>
            </div>
            
            <div class="reviews-list">
                <?php foreach (array_slice($reviews, 0, 5) as $review): ?>
                <div class="review">
                    <div class="review-header">
                        <div class="review-user">
                            <span class="user-name"><?= htmlspecialchars($review['user_name']) ?></span>
                            <?php if ($review['verified_visit']): ?>
                            <span class="review-verified">Verified Visit</span>
                            <?php endif; ?>
                        </div>
                        <div class="review-rating">
                            <?php for ($i = 1; $i <= 5; $i++): ?>
                                <i class="fas fa-star <?= $i <= $review['rating'] ? 'filled' : '' ?>"></i>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="review-content">
                        <?= htmlspecialchars($review['comment']) ?>
                    </div>
                    
                    <?php if (!empty($review['tags'])): ?>
                    <div class="review-tags">
                        <?php foreach ($review['tags'] as $tag): ?>
                        <span class="review-tag"><?= htmlspecialchars($tag) ?></span>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                    
                    <div class="review-date">
                        <?= getTimeAgo($review['date']) ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <?php if (count($reviews) > 5): ?>
            <button class="btn btn-outline btn-full" onclick="showAllReviews()">
                View All Reviews (<?= count($reviews) ?>)
            </button>
            <?php endif; ?>
        </div>
        
        <!-- Action Buttons -->
        <div class="action-buttons">
            <button class="btn btn-outline btn-lg" data-call-vendor="<?= $vendor['id'] ?>">
                <i class="fas fa-phone"></i>
                Call
            </button>
            <button class="btn btn-outline btn-lg" data-whatsapp-vendor="<?= $vendor['id'] ?>">
                <i class="fab fa-whatsapp"></i>
                WhatsApp
            </button>
            <button class="btn btn-primary btn-lg" data-book-vendor="<?= $vendor['id'] ?>" aria-label="Book an appointment with <?= htmlspecialchars($vendor['name']) ?>">
                <i class="fas fa-calendar" aria-hidden="true"></i>
                Book Now
            </button>
        </div>
        
        <!-- Additional Actions -->
        <div class="additional-actions">
            <button class="action-btn" data-add-starter>
                <i class="fas fa-plus"></i>
                <span>Add to Starter Pack</span>
            </button>
            <button class="action-btn" data-report>
                <i class="fas fa-flag"></i>
                <span>Report</span>
            </button>
        </div>
    </div>
</div>

<style>
/* Ensure navbar doesn't appear duplicated on vendor-detail page */
.vendor-detail-screen {
    min-height: 100vh;
    background: var(--light-gray);
    margin-top: 0;
}

/* Make vendor-detail header distinct from navbar */
.vendor-detail-screen .header {
    background: linear-gradient(135deg, var(--primary-orange), var(--primary-teal));
    color: var(--white);
    padding: var(--space-lg) var(--space-md) var(--space-md);
    text-align: center;
    position: relative;
    margin-top: 0;
    border-top: 1px solid rgba(255, 255, 255, 0.1);
}

/* Ensure main-nav is properly positioned above vendor-detail header */
.main-nav {
    position: sticky;
    top: 0;
    z-index: 1000;
    background: var(--white);
    box-shadow: var(--shadow-sm);
}

.vendor-detail-screen .header h1 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: var(--space-xs);
}

.vendor-detail-screen .header p {
    font-size: 14px;
    opacity: 0.9;
}

.vendor-gallery {
    background: var(--white);
    padding: var(--space-lg);
    margin-bottom: var(--space-md);
    box-shadow: var(--shadow-sm);
}

.main-image {
    width: 100%;
    height: 200px;
    background: var(--primary-orange);
    border-radius: var(--radius-md);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 64px;
    margin-bottom: var(--space-md);
}

.gallery-thumbnails {
    display: flex;
    gap: var(--space-sm);
}

.thumbnail {
    width: 60px;
    height: 60px;
    background: var(--light-gray);
    border-radius: var(--radius-sm);
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--text-light);
    font-size: 24px;
    cursor: pointer;
    transition: all 0.3s ease;
}

.thumbnail.active {
    background: var(--primary-orange);
    color: var(--white);
}

.vendor-info-section {
    background: var(--white);
    padding: var(--space-lg);
    margin-bottom: var(--space-md);
    box-shadow: var(--shadow-sm);
}

.vendor-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    margin-bottom: var(--space-lg);
}

.vendor-title h2 {
    font-size: 24px;
    font-weight: 700;
    color: var(--text-dark);
    margin-bottom: var(--space-xs);
}

.vendor-category {
    font-size: 14px;
    color: var(--text-light);
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.vendor-rating {
    text-align: right;
}

.trust-score {
    background: var(--verified-green);
    color: var(--white);
    padding: var(--space-sm) var(--space-md);
    border-radius: var(--radius-md);
    font-size: 18px;
    font-weight: 700;
    display: inline-block;
    margin-bottom: var(--space-sm);
}

.stars {
    display: flex;
    gap: 2px;
    margin-bottom: var(--space-xs);
}

.stars .fa-star {
    color: var(--accent-yellow);
    font-size: 16px;
}

.stars .fa-star:not(.filled) {
    color: var(--medium-gray);
}

.rating-text {
    font-size: 12px;
    color: var(--text-medium);
}

.vendor-address {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    margin-bottom: var(--space-md);
    padding: var(--space-md);
    background: var(--light-gray);
    border-radius: var(--radius-md);
}

.vendor-address i {
    color: var(--primary-orange);
    font-size: 18px;
}

.vendor-address span {
    flex: 1;
    font-size: 16px;
    color: var(--text-dark);
}

.vendor-languages {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    font-size: 16px;
    color: var(--text-medium);
}

.vendor-languages i {
    color: var(--primary-teal);
    font-size: 18px;
}

.trust-badges-section,
.pricing-section,
.tips-section,
.reviews-section {
    background: var(--white);
    padding: var(--space-lg);
    margin-bottom: var(--space-md);
    box-shadow: var(--shadow-sm);
}

.trust-badges-section h3,
.pricing-section h3,
.tips-section h3,
.reviews-section h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--space-lg);
}

.trust-badges {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-md);
}

.trust-badge {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    padding: var(--space-sm) var(--space-md);
    background: var(--light-gray);
    border-radius: var(--radius-md);
    font-size: 14px;
    font-weight: 500;
}

.trust-badge i {
    color: var(--verified-green);
    font-size: 16px;
}

.price-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}

.price-item {
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: var(--space-md);
    background: var(--light-gray);
    border-radius: var(--radius-md);
}

.service-name {
    font-size: 16px;
    font-weight: 500;
    color: var(--text-dark);
}

.service-price {
    font-size: 16px;
    font-weight: 700;
    color: var(--primary-orange);
}

.tips-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-lg);
}

.tip-item {
    display: flex;
    gap: var(--space-md);
}

.tip-item i {
    color: var(--primary-teal);
    font-size: 24px;
    margin-top: var(--space-xs);
}

.tip-content h4 {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--space-xs);
}

.tip-content p {
    font-size: 14px;
    color: var(--text-medium);
    line-height: 1.5;
}

.reviews-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--space-lg);
}

.review-stats {
    display: flex;
    gap: var(--space-md);
    font-size: 14px;
}

.verified-count {
    color: var(--verified-green);
    font-weight: 600;
}

.total-count {
    color: var(--text-medium);
}

.reviews-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-lg);
}

.review {
    padding: var(--space-md);
    background: var(--light-gray);
    border-radius: var(--radius-md);
}

.review-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--space-sm);
}

.review-user {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
}

.user-name {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
}

.review-verified {
    background: var(--verified-green);
    color: var(--white);
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-sm);
    font-size: 10px;
    font-weight: 600;
    text-transform: uppercase;
}

.review-rating {
    display: flex;
    gap: 2px;
}

.review-rating .fa-star {
    color: var(--accent-yellow);
    font-size: 12px;
}

.review-rating .fa-star:not(.filled) {
    color: var(--medium-gray);
}

.review-content {
    font-size: 14px;
    color: var(--text-dark);
    line-height: 1.5;
    margin-bottom: var(--space-sm);
}

.review-tags {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-xs);
    margin-bottom: var(--space-sm);
}

.review-tag {
    background: var(--primary-teal);
    color: var(--white);
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-sm);
    font-size: 11px;
    font-weight: 500;
}

.review-date {
    font-size: 12px;
    color: var(--text-light);
}

.action-buttons {
    display: flex;
    gap: var(--space-sm);
    margin-bottom: var(--space-lg);
    padding: 0 var(--space-md);
}

.action-buttons .btn {
    flex: 1;
}

.additional-actions {
    display: flex;
    gap: var(--space-sm);
    padding: 0 var(--space-md);
}

.action-btn {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: var(--space-md);
    background: var(--white);
    border: 1px solid var(--medium-gray);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.3s ease;
    text-decoration: none;
    color: var(--text-dark);
}

.action-btn:hover {
    background: var(--light-gray);
    border-color: var(--primary-orange);
    color: var(--primary-orange);
}

.action-btn i {
    font-size: 20px;
    margin-bottom: var(--space-xs);
}

.action-btn span {
    font-size: 12px;
    font-weight: 500;
    text-align: center;
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
    // Check if user should be redirected to booking modal after login
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.get('book') === '1') {
        const vendorId = <?= $vendor['id'] ?>;
        // Small delay to ensure page is fully loaded
        setTimeout(() => {
            showVendorBookingModal(vendorId);
        }, 300);
    }
    
    // Gallery thumbnails
    const thumbnails = document.querySelectorAll('.thumbnail');
    thumbnails.forEach(thumb => {
        thumb.addEventListener('click', function() {
            thumbnails.forEach(t => t.classList.remove('active'));
            this.classList.add('active');
        });
    });
    
    // Action buttons
    const callBtn = document.querySelector('[data-call-vendor]');
    if (callBtn) {
        const vendorPhone = '<?= htmlspecialchars($vendor['phone'] ?? '', ENT_QUOTES) ?>';
        if (vendorPhone) {
            callBtn.addEventListener('click', function() {
                window.location.href = 'tel:' + vendorPhone.replace(/\s+/g, '');
            });
        } else {
            callBtn.disabled = true;
            callBtn.title = 'Phone number not available';
        }
    }
    
    const whatsappBtn = document.querySelector('[data-whatsapp-vendor]');
    if (whatsappBtn) {
        const vendorPhone = '<?= htmlspecialchars($vendor['phone'] ?? '', ENT_QUOTES) ?>';
        if (vendorPhone) {
            whatsappBtn.addEventListener('click', function() {
                const phone = vendorPhone.replace(/[^0-9]/g, '');
                const whatsappUrl = phone.startsWith('233') ? `https://wa.me/${phone}` : `https://wa.me/233${phone}`;
                window.open(whatsappUrl, '_blank', 'noopener,noreferrer');
            });
        } else {
            whatsappBtn.disabled = true;
            whatsappBtn.title = 'Phone number not available';
        }
    }
    
    const bookBtn = document.querySelector('[data-book-vendor]');
    if (bookBtn) {
        bookBtn.addEventListener('click', function() {
            // Show booking modal
            showVendorBookingModal(this.dataset.bookVendor);
        });
    }
    
    // Additional actions
    const addStarterBtn = document.querySelector('[data-add-starter]');
    if (addStarterBtn) {
        addStarterBtn.addEventListener('click', async function() {
            const vendorId = <?= $vendor['id'] ?>;
            const vendorCategory = '<?= $vendor['category'] ?>';
            
            try {
                const formData = new FormData();
                formData.append('action', 'add_to_starter_pack');
                formData.append('vendor_id', vendorId);
                formData.append('category', vendorCategory);
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
                        addStarterBtn.innerHTML = '<i class="fas fa-check"></i> Added';
                        addStarterBtn.disabled = true;
                        showToast('Added to your starter pack!', 'success');
                    }
                }
            } catch (error) {
                console.error('Error adding to starter pack:', error);
                // Fallback: update localStorage
                const userData = JSON.parse(localStorage.getItem('gulio_user') || '{}');
                if (!userData.starter_vendors) userData.starter_vendors = [];
                if (!userData.starter_vendors.includes(vendorId)) {
                    userData.starter_vendors.push(vendorId);
                    localStorage.setItem('gulio_user', JSON.stringify(userData));
                    addStarterBtn.innerHTML = '<i class="fas fa-check"></i> Added';
                    addStarterBtn.disabled = true;
                    showToast('Added to your starter pack!', 'success');
                }
            }
        });
    }
    
    
    const reportBtn = document.querySelector('[data-report]');
    if (reportBtn) {
        reportBtn.addEventListener('click', async function() {
            if (confirm('Are you sure you want to report this vendor?')) {
                const vendorId = <?= $vendor['id'] ?>;
                
                try {
                    const formData = new FormData();
                    formData.append('action', 'submit_report');
                    formData.append('vendor_id', vendorId);
                    formData.append('type', 'vendor');
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
                            showToast('Report submitted. Thank you for helping keep our community safe.', 'success');
                        } else {
                            showToast(result.message || 'Error submitting report', 'error');
                        }
                    } else {
                        showToast('Error submitting report', 'error');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    showToast('Error submitting report', 'error');
                }
            }
        });
    }
});

function openMaps() {
    const lat = <?= $vendor['lat'] ?>;
    const lng = <?= $vendor['lng'] ?>;
    const address = encodeURIComponent('<?= htmlspecialchars($vendor['address'], ENT_QUOTES) ?>');
    
    // Try Google Maps first, fallback to Apple Maps on iOS
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    if (isIOS) {
        window.open(`maps://maps.google.com/maps?daddr=${lat},${lng}&amp;ll=`, '_blank');
    } else {
        window.open(`https://www.google.com/maps/dir/?api=1&destination=${lat},${lng}&destination_place_id=${address}`, '_blank', 'noopener,noreferrer');
    }
}

function showAllReviews() {
    const reviewsList = document.querySelector('.reviews-list');
    const allReviews = <?= json_encode($reviews) ?>;
    
    if (reviewsList && allReviews.length > 5) {
        reviewsList.innerHTML = '';
        allReviews.forEach(review => {
            const reviewDiv = document.createElement('div');
            reviewDiv.className = 'review';
            reviewDiv.innerHTML = `
                <div class="review-header">
                    <div class="review-user">
                        <span class="user-name">${escapeHtml(review.user_name)}</span>
                        ${review.verified_visit ? '<span class="review-verified">Verified Visit</span>' : ''}
                    </div>
                    <div class="review-rating">
                        ${Array.from({length: 5}, (_, i) => 
                            `<i class="fas fa-star ${i < review.rating ? 'filled' : ''}"></i>`
                        ).join('')}
                    </div>
                </div>
                <div class="review-content">${escapeHtml(review.comment)}</div>
                ${review.tags && review.tags.length ? `
                    <div class="review-tags">
                        ${review.tags.map(tag => `<span class="review-tag">${escapeHtml(tag)}</span>`).join('')}
                    </div>
                ` : ''}
                <div class="review-date">${review.date}</div>
            `;
            reviewsList.appendChild(reviewDiv);
        });
        
        const viewAllBtn = document.querySelector('button[onclick="showAllReviews()"]');
        if (viewAllBtn) {
            viewAllBtn.style.display = 'none';
        }
    }
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

// Global toast function for notifications
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

async function showVendorBookingModal(vendorId) {
    // Check if user is logged in
    const userId = <?= json_encode($_SESSION['user']['id'] ?? null) ?>;
    if (!userId) {
        // Save the current page URL with vendor ID as return URL
        const returnUrl = encodeURIComponent('?page=vendor-detail&id=' + vendorId + '&book=1');
        window.location.href = '?page=login&return=' + returnUrl;
        return;
    }

    // Use vendor data already available on the page (no need to fetch)
    const vendor = {
        id: <?= $vendor['id'] ?>,
        name: <?= json_encode($vendor['name']) ?>,
        price_items: <?= json_encode($vendor['price_items']) ?>
    };

        // Create modal
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Book ${vendor.name}</h2>
                    <button class="modal-close">&times;</button>
                </div>
                
                <form id="booking-form">
                    <div class="form-group">
                        <label class="form-label">Service</label>
                        <select class="form-select" name="service" required>
                            <option value="">Select a service</option>
                            ${vendor.price_items.map(item => {
                                const price = item.min === item.max ? `GHS ${item.min}` : `GHS ${item.min} - ${item.max}`;
                                return `<option value="${item.service}">${item.service} - ${price}</option>`;
                            }).join('')}
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
        const submitBtn = modal.querySelector('button[type="submit"]');
        const originalBtnText = submitBtn.innerHTML;
        
        modal.querySelector('#booking-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
            // Show loading state
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
            
            const formData = new FormData(this);
            const bookingData = new FormData();
            bookingData.append('action', 'submit_booking');
            bookingData.append('vendor_id', vendor.id);
            bookingData.append('service', formData.get('service'));
            bookingData.append('datetime', formData.get('datetime'));
            bookingData.append('meeting_point', formData.get('meeting_point'));
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                bookingData.append('csrf_token', csrfToken);
            }
            
            try {
                console.log('Submitting booking...', {
                    vendor_id: vendor.id,
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
                        window.location.reload();
                    }, 1500);
                } else {
                    showToast(result.message || 'Unable to complete booking. Please try again.', 'error');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            } catch (error) {
                console.error('Error submitting booking:', error);
                showToast(error.message || 'Connection error. Please check your internet and try again.', 'error');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        });
        
        document.body.appendChild(modal);
        
        // Close modal handlers
        modal.querySelector('.modal-close').addEventListener('click', () => modal.remove());
        modal.addEventListener('click', (e) => {
            if (e.target === modal) modal.remove();
        });
    }
    
    // Make function globally accessible
    window.showVendorBookingModal = showVendorBookingModal;
</script>

