<?php
$user = $_SESSION['user'];
$buddies = getCityBuddies();
?>

<div class="city-buddy-screen">
    <div class="header">
        <button class="back-btn" onclick="history.back()">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h1>City Buddy</h1>
        <p>Connect with verified locals</p>
    </div>
    
    <div class="content">
        <!-- Search and Filters -->
        <div class="search-section">
            <div class="search-bar">
                <i class="fas fa-search"></i>
                <input type="text" placeholder="Search by name, specialty, or language..." id="buddy-search">
            </div>
            
            <div class="filter-options">
                <button class="filter-btn active" data-filter="all">
                    <i class="fas fa-th"></i>
                    All
                </button>
                <button class="filter-btn" data-filter="available">
                    <i class="fas fa-circle"></i>
                    Available Now
                </button>
                <button class="filter-btn" data-filter="top-rated">
                    <i class="fas fa-star"></i>
                    Top Rated
                </button>
                <button class="filter-btn" data-filter="budget">
                    <i class="fas fa-dollar-sign"></i>
                    Budget Friendly
                </button>
            </div>
        </div>
        
        <!-- Buddy List -->
        <div class="buddies-list" id="buddies-list">
            <?php foreach ($buddies as $buddy): ?>
            <div class="buddy-card" data-buddy-id="<?= $buddy['id'] ?>">
                <div class="buddy-header">
                    <div class="buddy-avatar">
                        <?= substr($buddy['name'], 0, 1) ?>
                    </div>
                    <div class="buddy-info">
                        <h3><?= htmlspecialchars($buddy['name']) ?></h3>
                        <div class="buddy-rating">
                            <div class="stars">
                                <?php for ($i = 1; $i <= 5; $i++): ?>
                                    <i class="fas fa-star <?= $i <= floor($buddy['rating']) ? 'filled' : '' ?>"></i>
                                <?php endfor; ?>
                            </div>
                            <span class="rating-text"><?= $buddy['rating'] ?> (<?= $buddy['verified_visits'] ?> visits)</span>
                        </div>
                        <div class="buddy-languages">
                            <i class="fas fa-language"></i>
                            <span><?= implode(', ', $buddy['languages']) ?></span>
                        </div>
                    </div>
                    <div class="buddy-status">
                        <div class="status-indicator online"></div>
                        <span class="status-text">Available</span>
                    </div>
                </div>
                
                <div class="buddy-specialties">
                    <h4>Specialties</h4>
                    <div class="specialty-tags">
                        <?php foreach ($buddy['specialties'] as $specialty): ?>
                        <span class="specialty-tag"><?= htmlspecialchars($specialty) ?></span>
                        <?php endforeach; ?>
                    </div>
                </div>
                
                <div class="buddy-badges">
                    <?php foreach ($buddy['badges'] as $badge): ?>
                    <span class="badge badge-verified"><?= htmlspecialchars($badge) ?></span>
                    <?php endforeach; ?>
                </div>
                
                <div class="buddy-footer">
                    <div class="buddy-rate">
                        <span class="rate-amount">GHS <?= $buddy['rate'] ?></span>
                        <span class="rate-period">/hour</span>
                    </div>
                    <div class="buddy-actions">
                        <button class="btn btn-outline btn-sm" data-view-profile="<?= $buddy['id'] ?>">
                            <i class="fas fa-user"></i>
                            Profile
                        </button>
                        <button class="btn btn-primary btn-sm" data-book-buddy="<?= $buddy['id'] ?>">
                            <i class="fas fa-calendar"></i>
                            Book
                        </button>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- How It Works -->
        <div class="how-it-works">
            <h3>How City Buddy Works</h3>
            <div class="steps">
                <div class="step">
                    <div class="step-number">1</div>
                    <div class="step-content">
                        <h4>Choose Your Buddy</h4>
                        <p>Browse verified locals with specialties that match your needs</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">2</div>
                    <div class="step-content">
                        <h4>Book & Meet</h4>
                        <p>Schedule a meeting and meet in a safe, public location</p>
                    </div>
                </div>
                <div class="step">
                    <div class="step-number">3</div>
                    <div class="step-content">
                        <h4>Get Help</h4>
                        <p>Your buddy will guide you and help you navigate Accra</p>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Safety Guidelines -->
        <div class="safety-guidelines">
            <h3>Safety Guidelines</h3>
            <div class="guidelines-list">
                <div class="guideline-item">
                    <i class="fas fa-shield-alt"></i>
                    <div class="guideline-content">
                        <h4>Meet in Public</h4>
                        <p>Always meet in well-lit, public places like malls or cafes</p>
                    </div>
                </div>
                <div class="guideline-item">
                    <i class="fas fa-id-card"></i>
                    <div class="guideline-content">
                        <h4>Verify Identity</h4>
                        <p>Ask to see their ID and verify their Gulio profile</p>
                    </div>
                </div>
                <div class="guideline-item">
                    <i class="fas fa-phone"></i>
                    <div class="guideline-content">
                        <h4>Stay Connected</h4>
                        <p>Share your location with friends and keep your phone charged</p>
                    </div>
                </div>
                <div class="guideline-item">
                    <i class="fas fa-exclamation-triangle"></i>
                    <div class="guideline-content">
                        <h4>Trust Your Instincts</h4>
                        <p>If something feels wrong, leave immediately and report it</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.city-buddy-screen {
    min-height: 100vh;
    background: var(--light-gray);
}

.city-buddy-screen .header {
    background: linear-gradient(135deg, var(--primary-orange), var(--primary-teal));
    color: var(--white);
    padding: var(--space-lg) var(--space-md) var(--space-md);
    text-align: center;
    position: relative;
}

.city-buddy-screen .header h1 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: var(--space-xs);
}

.city-buddy-screen .header p {
    font-size: 14px;
    opacity: 0.9;
}

.search-section {
    background: var(--white);
    padding: var(--space-lg);
    margin-bottom: var(--space-md);
    box-shadow: var(--shadow-sm);
}

.search-bar {
    position: relative;
    margin-bottom: var(--space-lg);
}

.search-bar i {
    position: absolute;
    left: var(--space-md);
    top: 50%;
    transform: translateY(-50%);
    color: var(--text-light);
}

.cost-display {
    padding: var(--space-md);
    background: var(--light-gray);
    border-radius: var(--radius-md);
    text-align: center;
}

#cost-amount {
    font-size: 24px;
    font-weight: 700;
    color: var(--primary-orange);
    display: block;
}

.cost-breakdown {
    color: var(--text-medium);
    font-size: 14px;
    margin-top: var(--space-xs);
}

.form-help {
    color: var(--text-medium);
    font-size: 12px;
    margin-top: var(--space-xs);
    display: block;
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

.filter-options {
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

.buddies-list {
    padding: 0 var(--space-md);
}

.buddy-card {
    background: var(--white);
    border-radius: var(--radius-md);
    padding: var(--space-lg);
    margin-bottom: var(--space-md);
    box-shadow: var(--shadow-sm);
    border: 1px solid var(--medium-gray);
    transition: all 0.3s ease;
}

.buddy-card:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.buddy-header {
    display: flex;
    align-items: flex-start;
    gap: var(--space-md);
    margin-bottom: var(--space-lg);
}

.buddy-avatar {
    width: 60px;
    height: 60px;
    background: var(--primary-teal);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 24px;
    font-weight: 700;
}

.buddy-info {
    flex: 1;
}

.buddy-info h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--space-xs);
}

.buddy-rating {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
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

.rating-text {
    font-size: 12px;
    color: var(--text-medium);
}

.buddy-languages {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    font-size: 12px;
    color: var(--text-medium);
}

.buddy-languages i {
    color: var(--primary-teal);
}

.buddy-status {
    text-align: right;
}

.status-indicator {
    width: 12px;
    height: 12px;
    border-radius: 50%;
    margin: 0 auto var(--space-xs);
}

.status-indicator.online {
    background: var(--verified-green);
}

.status-indicator.offline {
    background: var(--text-light);
}

.status-text {
    font-size: 12px;
    color: var(--text-medium);
}

.buddy-specialties {
    margin-bottom: var(--space-lg);
}

.buddy-specialties h4 {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--space-sm);
}

.specialty-tags {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-xs);
}

.specialty-tag {
    background: var(--light-gray);
    color: var(--text-dark);
    padding: var(--space-xs) var(--space-sm);
    border-radius: var(--radius-sm);
    font-size: 12px;
    font-weight: 500;
}

.buddy-badges {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-xs);
    margin-bottom: var(--space-lg);
}

.buddy-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
}

.buddy-rate {
    display: flex;
    align-items: baseline;
    gap: var(--space-xs);
}

.rate-amount {
    font-size: 20px;
    font-weight: 700;
    color: var(--primary-orange);
}

.rate-period {
    font-size: 14px;
    color: var(--text-medium);
}

.buddy-actions {
    display: flex;
    gap: var(--space-sm);
}

.how-it-works {
    background: var(--white);
    padding: var(--space-lg);
    margin-bottom: var(--space-md);
    box-shadow: var(--shadow-sm);
}

.how-it-works h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--space-lg);
    text-align: center;
}

.steps {
    display: flex;
    flex-direction: column;
    gap: var(--space-lg);
}

.step {
    display: flex;
    align-items: flex-start;
    gap: var(--space-md);
}

.step-number {
    width: 40px;
    height: 40px;
    background: var(--primary-orange);
    color: var(--white);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 18px;
    font-weight: 700;
    flex-shrink: 0;
}

.step-content h4 {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--space-xs);
}

.step-content p {
    font-size: 14px;
    color: var(--text-medium);
    line-height: 1.5;
}

.safety-guidelines {
    background: var(--white);
    padding: var(--space-lg);
    margin-bottom: var(--space-md);
    box-shadow: var(--shadow-sm);
}

.safety-guidelines h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--space-lg);
    text-align: center;
}

.guidelines-list {
    display: flex;
    flex-direction: column;
    gap: var(--space-lg);
}

.guideline-item {
    display: flex;
    align-items: flex-start;
    gap: var(--space-md);
}

.guideline-item i {
    color: var(--accent-red);
    font-size: 20px;
    margin-top: var(--space-xs);
}

.guideline-content h4 {
    font-size: 16px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--space-xs);
}

.guideline-content p {
    font-size: 14px;
    color: var(--text-medium);
    line-height: 1.5;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Search functionality
    const searchInput = document.getElementById('buddy-search');
    if (searchInput) {
        searchInput.addEventListener('input', function() {
            const query = this.value.toLowerCase();
            const buddyCards = document.querySelectorAll('.buddy-card');
            
            buddyCards.forEach(card => {
                const name = card.querySelector('h3').textContent.toLowerCase();
                const specialties = card.querySelector('.specialty-tags').textContent.toLowerCase();
                const languages = card.querySelector('.buddy-languages span').textContent.toLowerCase();
                
                if (name.includes(query) || specialties.includes(query) || languages.includes(query)) {
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
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            const filter = this.dataset.filter;
            filterBuddies(filter);
        });
    });
    
    // Buddy actions
    const viewProfileBtns = document.querySelectorAll('[data-view-profile]');
    viewProfileBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const buddyId = this.dataset.viewProfile;
            showBuddyProfile(buddyId);
        });
    });
    
    const bookBuddyBtns = document.querySelectorAll('[data-book-buddy]');
    bookBuddyBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.stopPropagation();
            const buddyId = this.dataset.bookBuddy;
            showBuddyBookingModal(buddyId);
        });
    });
});

function filterBuddies(filter) {
    const buddyCards = document.querySelectorAll('.buddy-card');
    
    buddyCards.forEach(card => {
        let show = true;
        
        switch (filter) {
            case 'available':
                const status = card.querySelector('.status-indicator');
                show = status.classList.contains('online');
                break;
            case 'top-rated':
                const rating = parseFloat(card.querySelector('.rating-text').textContent);
                show = rating >= 4.5;
                break;
            case 'budget':
                const rate = parseInt(card.querySelector('.rate-amount').textContent);
                show = rate <= 15;
                break;
            case 'all':
            default:
                show = true;
                break;
        }
        
        card.style.display = show ? 'block' : 'none';
    });
}

function showBuddyProfile(buddyId) {
    // Get buddy data
    const buddies = <?= json_encode($buddies) ?>;
    const buddy = buddies.find(b => b.id == buddyId);
    
    if (!buddy) {
        alert('Buddy profile not found');
        return;
    }
    
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h2 class="modal-title">${escapeHtml(buddy.name)}</h2>
                <button class="modal-close" onclick="this.closest('.modal').remove()">&times;</button>
            </div>
            <div class="buddy-profile">
                <div class="buddy-avatar-large" style="width: 100px; height: 100px; border-radius: 50%; background: var(--primary-orange); display: flex; align-items: center; justify-content: center; color: white; font-size: 36px; margin: 0 auto 16px;">
                    ${buddy.name.charAt(0)}
                </div>
                <div style="text-align: center; margin-bottom: 20px;">
                    <div style="display: flex; align-items: center; justify-content: center; gap: 8px; margin-bottom: 8px;">
                        <i class="fas fa-star" style="color: #FFB703;"></i>
                        <strong>${buddy.rating}</strong>
                        <span style="color: var(--text-medium);">(${buddy.verified_visits} visits)</span>
                    </div>
                    <div style="font-size: 18px; font-weight: 600; color: var(--primary-orange);">
                        GHS ${buddy.rate}/hour
                    </div>
                </div>
                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; margin-bottom: 8px;">Languages</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        ${buddy.languages.map(lang => `<span style="background: rgba(255, 123, 77, 0.1); padding: 4px 12px; border-radius: 16px; font-size: 14px;">${escapeHtml(lang)}</span>`).join('')}
                    </div>
                </div>
                <div style="margin-bottom: 20px;">
                    <h3 style="font-size: 16px; margin-bottom: 8px;">Specialties</h3>
                    <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                        ${buddy.specialties.map(spec => `<span style="background: rgba(55, 198, 176, 0.1); padding: 4px 12px; border-radius: 16px; font-size: 14px;">${escapeHtml(spec)}</span>`).join('')}
                    </div>
                </div>
                ${buddy.badges && buddy.badges.length ? `
                    <div style="margin-bottom: 20px;">
                        <h3 style="font-size: 16px; margin-bottom: 8px;">Badges</h3>
                        <div style="display: flex; flex-wrap: wrap; gap: 8px;">
                            ${buddy.badges.map(badge => `<span style="background: var(--primary-teal); color: white; padding: 4px 12px; border-radius: 16px; font-size: 14px;">${escapeHtml(badge)}</span>`).join('')}
                        </div>
                    </div>
                ` : ''}
                <button class="btn btn-primary btn-full" onclick="showBuddyBookingModal(${buddyId}); this.closest('.modal').remove();">
                    <i class="fas fa-calendar"></i> Book This Buddy
                </button>
            </div>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Close on background click
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            modal.remove();
        }
    });
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showBuddyBookingModal(buddyId) {
    // Get buddy data
    const buddies = <?= json_encode($buddies) ?>;
    const buddy = buddies.find(b => b.id == buddyId);
    
    if (!buddy) {
        alert('Buddy not found');
        return;
    }
    
    const modal = document.createElement('div');
    modal.className = 'modal';
    modal.innerHTML = `
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title">Book ${escapeHtml(buddy.name)}</h2>
                <button class="modal-close">&times;</button>
            </div>
            
            <form id="buddy-booking-form">
                <div class="form-group">
                    <label class="form-label">Hours Needed</label>
                    <input type="number" class="form-input" name="hours" min="1" max="12" step="0.5" 
                           placeholder="Enter hours (e.g., 2.5)" required>
                    <small class="form-help">Minimum 1 hour, maximum 12 hours per booking</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Estimated Cost</label>
                    <div class="cost-display">
                        <span id="cost-amount">GHS 0.00</span>
                        <small class="cost-breakdown">Rate: GHS ${buddy.rate}/hour</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Date & Time</label>
                    <input type="datetime-local" class="form-input" name="datetime" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Meeting Location</label>
                    <input type="text" class="form-input" name="location" 
                           placeholder="e.g., Accra Mall, Osu" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label">What do you need help with?</label>
                    <textarea class="form-textarea" name="description" 
                              placeholder="Describe what you need help with..."></textarea>
                </div>
                
                <div class="checkbox-item">
                    <input type="checkbox" id="safety-confirm" required>
                    <label for="safety-confirm" class="checkbox-content">
                        <div class="checkbox-title">Safety Confirmation</div>
                        <div class="checkbox-description">
                            I agree to meet in a public place and follow safety guidelines
                        </div>
                    </label>
                </div>
                
                <div class="form-actions">
                    <button type="button" class="btn btn-outline" onclick="this.closest('.modal').remove()">
                        Cancel
                    </button>
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-credit-card"></i> Pay & Book Now
                    </button>
                </div>
            </form>
        </div>
    `;
    
    document.body.appendChild(modal);
    
    // Calculate cost when hours change
    const hoursInput = modal.querySelector('input[name="hours"]');
    const costDisplay = modal.querySelector('#cost-amount');
    
    hoursInput.addEventListener('input', function() {
        const hours = parseFloat(this.value) || 0;
        const cost = hours * buddy.rate;
        costDisplay.textContent = `GHS ${cost.toFixed(2)}`;
    });
    
    // Handle form submission
    const submitBtn = modal.querySelector('button[type="submit"]');
    const originalBtnText = submitBtn.innerHTML;
    
    modal.querySelector('#buddy-booking-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        // Check if user is logged in
        const userId = <?= json_encode($_SESSION['user']['id'] ?? null) ?>;
        if (!userId) {
            alert('Please log in to book a city buddy');
            window.location.href = '?page=login';
            return;
        }
        
        const formData = new FormData(this);
        const hours = parseFloat(formData.get('hours'));
        const amount = hours * buddy.rate;
        
        if (!hours || hours < 1) {
            alert('Please enter at least 1 hour');
            return;
        }
        
        // Show loading state
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        
        try {
            const paymentData = new FormData();
            paymentData.append('action', 'initialize_buddy_payment');
            paymentData.append('buddy_id', buddyId);
            paymentData.append('hours', hours);
            paymentData.append('datetime', formData.get('datetime'));
            paymentData.append('location', formData.get('location'));
            paymentData.append('description', formData.get('description'));
            paymentData.append('amount', amount);
            
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            if (csrfToken) {
                paymentData.append('csrf_token', csrfToken);
            }
            
            const response = await fetch('', {
                method: 'POST',
                body: paymentData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    // Redirect to Paystack payment page
                    window.location.href = result.authorization_url;
                } else {
                    alert(result.message || 'Error initializing payment');
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            } else {
                alert('Error processing payment');
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        } catch (error) {
            console.error('Error:', error);
            alert('Error processing payment');
            submitBtn.disabled = false;
            submitBtn.innerHTML = originalBtnText;
        }
    });
    
    function showToast(message, type) {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        toast.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:var(--primary-orange);color:white;padding:12px 24px;border-radius:8px;z-index:10000;';
        document.body.appendChild(toast);
        setTimeout(() => toast.remove(), 3000);
    }
}
</script>

