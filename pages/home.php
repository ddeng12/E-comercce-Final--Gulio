<?php
require_once __DIR__ . '/../includes/auth.php';

$authUser = Auth::user();
$user = $_SESSION['user'];
$vendors = getVendors();
$quickHelpOptions = getQuickHelpOptions();

// Update session with auth user data if logged in
if ($authUser) {
    $user['name'] = $authUser['name'] ?? $user['name'];
    $user['email'] = $authUser['email'] ?? $user['email'];
}

$hour = (int) date('H');
$greetingTime = $hour < 12 ? 'Morning' : ($hour < 18 ? 'Afternoon' : 'Evening');
$displayName = $authUser ? Security::escape($authUser['name']) : Security::escape($user['name'] ?: 'Friend');
?>

<div class="home-screen">
    <header class="hero-section">
        <div class="hero-background"></div>
        <div class="hero-container">
            <div class="hero-copy">
                <h1>Feel at home in Accra with Gulio</h1>
                <p>
                    We match you with vetted vendors, friendly city buddies, and instant tools so settling in and getting things done feels effortless.
                </p>
                <div class="hero-primary-card">
                    <div class="hero-card-content">
                        <div class="hero-card-text">
                            <h2>Need something right now?</h2>
                        </div>
                        <button class="quick-help-btn hero-primary" type="button" id="quick-help-toggle" aria-expanded="false" aria-controls="quick-help-panel">
                            <i class="fas fa-headset"></i>
                            Quick Help Hotline
                        </button>
                    </div>
                    <div class="hero-contact-panel" id="quick-help-panel" aria-hidden="true">
                        <p class="hero-contact-subtitle">Reach our support crew 24/7</p>
                        <ul class="hero-contact-details">
                            <li>
                                <i class="fas fa-phone"></i>
                                <a href="tel:+233591934593">+233&nbsp;591&nbsp;934&nbsp;593</a>
                                <span>Hotline (voice & SMS)</span>
                            </li>
                            <li>
                                <i class="fab fa-whatsapp"></i>
                                <a href="https://wa.me/233591934593" target="_blank" rel="noopener">WhatsApp Chat</a>
                                <span>Instant support & directions</span>
                            </li>
                            <li>
                                <i class="fas fa-envelope"></i>
                                <a href="mailto:support@gulio.com">support@gulio.com</a>
                                <span>Email desk</span>
                            </li>
                        </ul>
                    </div>
                </div>
                <div class="hero-secondary-actions">
                    <a class="hero-secondary" href="#explore">
                        Explore services
                        <i class="fas fa-arrow-down"></i>
                    </a>
                </div>
            </div>
            <div class="hero-visual">
                <div class="hero-illustration">
                    <div class="hero-pin">
                        <i class="fas fa-map-marker-alt"></i>
                    </div>
                    <div class="hero-bubble hero-bubble--vendors">
                        <i class="fas fa-store"></i>
                        <span>Trusted Vendors</span>
                    </div>
                    <div class="hero-bubble hero-bubble--buddy">
                        <i class="fas fa-user-friends"></i>
                        <span>City Buddies</span>
                    </div>
                </div>
            </div>
        </div>
    </header>
    
    <div class="content" id="explore">
        <!-- Smart Recommendations -->
        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Smart Recommendations</h3>
                <span class="card-subtitle">Based on your <?= htmlspecialchars($user['starter_pack'] ?: 'preferences') ?> pack</span>
            </div>
            
            <div class="recommendations-carousel">
                <?php foreach (array_slice($vendors, 0, 3) as $vendor): ?>
                <div class="recommendation-card" data-vendor-id="<?= $vendor['id'] ?>">
                    <div class="recommendation-image">
                        <i class="fas fa-<?= $vendor['category'] == 'barber' ? 'cut' : ($vendor['category'] == 'food' ? 'utensils' : ($vendor['category'] == 'phone_repair' ? 'mobile-alt' : 'store')) ?>"></i>
                    </div>
                    <div class="recommendation-content">
                        <h4><?= htmlspecialchars($vendor['name']) ?></h4>
                        <p><?= ucfirst(str_replace('_', ' ', $vendor['category'])) ?></p>
                        <div class="recommendation-badges">
                            <?php foreach ($vendor['badges'] as $badge): ?>
                            <span class="badge badge-<?= strpos($badge, 'Scout') !== false ? 'scout' : (strpos($badge, 'Local') !== false ? 'local' : 'verified') ?>">
                                <?= htmlspecialchars($badge) ?>
                            </span>
                            <?php endforeach; ?>
                        </div>
                        <div class="recommendation-footer">
                            <span class="distance"><?= formatDistance($vendor['distance']) ?></span>
                            <span class="price"><?= formatPrice($vendor['price_items'][0]) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Map/List Toggle -->
        <div class="view-toggle">
            <button class="toggle-btn active" data-view="list">
                <i class="fas fa-list"></i>
                List
            </button>
            <button class="toggle-btn" data-view="map">
                <i class="fas fa-map"></i>
                Map
            </button>
        </div>
        
        <!-- Filters -->
        <div class="filters">
            <button class="filter-btn active" data-filter="">
                <i class="fas fa-th"></i>
                All
            </button>
            <button class="filter-btn" data-filter="verified">
                <i class="fas fa-shield-alt"></i>
                Verified Only
            </button>
            <button class="filter-btn" data-filter="price">
                <i class="fas fa-dollar-sign"></i>
                Price
            </button>
            <button class="filter-btn" data-filter="distance">
                <i class="fas fa-map-marker-alt"></i>
                Distance
            </button>
            <button class="filter-btn" data-filter="language">
                <i class="fas fa-language"></i>
                Language
            </button>
        </div>
        
        <!-- Nearby Vendors -->
        <div class="vendors-section">
            <div class="section-header">
                <h3>Nearby Services</h3>
                <a href="?page=vendors" class="view-all-link">View All</a>
            </div>
            
            <!-- List View -->
            <div id="vendors-list" class="vendors-list">
                <?php foreach (array_slice($vendors, 0, 5) as $vendor): ?>
                <div class="vendor-card" data-vendor-id="<?= $vendor['id'] ?>" data-vendor-name="<?= htmlspecialchars($vendor['name']) ?>">
                    <div class="vendor-header">
                        <div class="vendor-info">
                            <h4><?= htmlspecialchars($vendor['name']) ?></h4>
                            <div class="vendor-category"><?= ucfirst(str_replace('_', ' ', $vendor['category'])) ?></div>
                        </div>
                        <div class="vendor-rating">
                            <span class="trust-score"><?= $vendor['trust_score'] ?></span>
                            <i class="fas fa-star"></i>
                        </div>
                    </div>
                    
                    <div class="vendor-details">
                        <span class="vendor-distance"><?= formatDistance($vendor['distance']) ?></span>
                        <span class="vendor-price"><?= formatPrice($vendor['price_items'][0]) ?></span>
                    </div>
                    
                    <div class="vendor-badges">
                        <?php foreach ($vendor['badges'] as $badge): ?>
                        <span class="badge badge-<?= strpos($badge, 'Scout') !== false ? 'scout' : (strpos($badge, 'Local') !== false ? 'local' : 'verified') ?>">
                            <?= htmlspecialchars($badge) ?>
                        </span>
                        <?php endforeach; ?>
                    </div>
                    
                    <div class="vendor-actions">
                        <button class="btn btn-outline btn-sm" data-vendor-id="<?= $vendor['id'] ?>">
                            <i class="fas fa-info-circle"></i> Details
                        </button>
                        <button class="btn btn-primary btn-sm" data-book-vendor="<?= $vendor['id'] ?>">
                            <i class="fas fa-calendar"></i> Book
                        </button>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Map View -->
            <div id="vendors-map" class="vendors-map" style="display: none;">
                <div class="map-container">
                    <div class="map-header">
                        <div class="user-location">
                            <i class="fas fa-location-arrow"></i>
                            <span>Your Location</span>
                        </div>
                        <div class="map-legend">
                            <div class="legend-item">
                                <div class="legend-color" style="background: var(--primary-orange);"></div>
                                <span>You</span>
                            </div>
                            <div class="legend-item">
                                <div class="legend-color" style="background: var(--primary-teal);"></div>
                                <span>Services</span>
                            </div>
                        </div>
                    </div>
                    
                    <div class="map-area">
                        <!-- User location marker -->
                        <div class="map-marker user-marker" style="top: 50%; left: 50%;">
                            <i class="fas fa-location-arrow"></i>
                            <div class="marker-tooltip">Your Location</div>
                        </div>
                        
                        <!-- Vendor markers -->
                        <?php foreach (array_slice($vendors, 0, 5) as $index => $vendor): ?>
                        <div class="map-marker vendor-marker" 
                             data-vendor-id="<?= $vendor['id'] ?>"
                             style="top: <?= 30 + ($index * 15) ?>%; left: <?= 30 + ($index * 20) ?>%;">
                            <i class="fas fa-<?= $vendor['category'] == 'barber' ? 'cut' : ($vendor['category'] == 'food' ? 'utensils' : ($vendor['category'] == 'phone_repair' ? 'mobile-alt' : 'store')) ?>"></i>
                            <div class="marker-tooltip">
                                <strong><?= htmlspecialchars($vendor['name']) ?></strong><br>
                                <?= ucfirst(str_replace('_', ' ', $vendor['category'])) ?><br>
                                <span class="marker-distance"><?= formatDistance($vendor['distance']) ?></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                        
                        <!-- Map grid lines for visual appeal -->
                        <div class="map-grid">
                            <?php for($i = 0; $i < 10; $i++): ?>
                                <div class="grid-line-h" style="top: <?= $i * 10 ?>%;"></div>
                                <div class="grid-line-v" style="left: <?= $i * 10 ?>%;"></div>
                            <?php endfor; ?>
                        </div>
                    </div>
                    
                    <div class="map-footer">
                        <button class="btn btn-outline btn-sm" onclick="centerMap()">
                            <i class="fas fa-crosshairs"></i> Center Map
                        </button>
                        <button class="btn btn-outline btn-sm" onclick="openExternalMap()">
                            <i class="fas fa-external-link-alt"></i> Open in Maps
                        </button>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Quick Actions -->
        <div class="quick-actions">
            <a href="?page=chatbot" class="quick-action-btn">
                <i class="fas fa-robot"></i>
                <span>Smart Chat</span>
            </a>
            <a href="?page=city-buddy" class="quick-action-btn">
                <i class="fas fa-user-friends"></i>
                <span>City Buddy</span>
            </a>
            <a href="?page=settings" class="quick-action-btn">
                <i class="fas fa-cog"></i>
                <span>Settings</span>
            </a>
        </div>
    </div>
</div>

<style>
.home-screen {
    min-height: 100vh;
    background: var(--light-gray);
    color: var(--text-dark);
}

.hero-section {
    position: relative;
    overflow: hidden;
    padding: calc(var(--space-xl) * 1.2) var(--space-md) var(--space-xl);
    min-height: 92vh;
    display: flex;
    align-items: center;
}

.hero-background {
    position: absolute;
    inset: 0;
    background:
        radial-gradient(circle at 15% 25%, rgba(255, 255, 255, 0.45), transparent 55%),
        radial-gradient(circle at 85% 15%, rgba(255, 255, 255, 0.25), transparent 60%),
        linear-gradient(135deg, #FF7A3D 0%, #FF9F43 35%, #37C6B0 100%);
    opacity: 0.96;
    z-index: 0;
}

.hero-container {
    position: relative;
    z-index: 1;
    display: flex;
    flex-wrap: wrap;
    gap: calc(var(--space-xl) * 1.2);
    align-items: center;
    justify-content: space-between;
    max-width: 1180px;
    margin: 0 auto;
    width: 100%;
}

.hero-copy {
    flex: 1 1 420px;
    color: var(--white);
}

.hero-copy h1 {
    font-size: clamp(36px, 5vw, 56px);
    line-height: 1.15;
    margin: 0 0 var(--space-sm);
    font-weight: 700;
}

.hero-copy p {
    font-size: 18px;
    line-height: 1.7;
    max-width: 540px;
    margin-bottom: var(--space-lg);
    opacity: 0.95;
}

.hero-primary-card {
    background: rgba(255, 255, 255, 0.92);
    border-radius: var(--radius-xl);
    padding: var(--space-lg);
    box-shadow: 0 25px 60px rgba(15, 23, 42, 0.18);
    color: var(--text-dark);
    backdrop-filter: blur(6px);
    margin-bottom: var(--space-lg);
}

.hero-card-content {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: var(--space-lg);
    flex-wrap: wrap;
}

.hero-card-text h2 {
    font-size: 24px;
    margin-bottom: var(--space-xs);
}

.hero-card-text p {
    margin: 0;
    color: var(--text-medium);
    max-width: 360px;
}

.quick-help-btn {
    display: inline-flex;
    align-items: center;
    gap: var(--space-xs);
    border-radius: var(--radius-lg);
    padding: var(--space-md) var(--space-lg);
    font-weight: 600;
    cursor: pointer;
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    border: none;
}

.quick-help-btn i {
    font-size: 18px;
}

.quick-help-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
}

.quick-help-btn.hero-primary {
    background: var(--primary-orange);
    color: var(--white);
    border: none;
    padding: var(--space-md) var(--space-xl);
    font-size: 16px;
    font-weight: 600;
    box-shadow: var(--shadow-lg);
}

.quick-help-btn.hero-primary.is-open {
    box-shadow: 0 18px 38px rgba(255, 122, 61, 0.35);
}

.hero-contact-panel {
    display: none;
    margin-top: var(--space-md);
    background: rgba(255, 255, 255, 0.92);
    border-radius: var(--radius-lg);
    padding: var(--space-md) var(--space-lg);
    box-shadow: 0 16px 40px rgba(15, 23, 42, 0.12);
    color: var(--text-dark);
}

.hero-contact-panel.is-open {
    display: block;
}

.hero-contact-subtitle {
    font-weight: 600;
    margin-bottom: var(--space-sm);
    color: var(--text-dark);
}

.hero-contact-details {
    list-style: none;
    padding: 0;
    margin: 0;
    display: grid;
    gap: var(--space-sm);
}

.hero-contact-details li {
    display: grid;
    grid-template-columns: auto 1fr;
    gap: var(--space-sm);
    align-items: center;
    background: rgba(55, 198, 176, 0.08);
    border-radius: var(--radius-md);
    padding: var(--space-sm) var(--space-md);
}

.hero-contact-details i {
    font-size: 18px;
    color: var(--primary-teal);
}

.hero-contact-details a {
    color: var(--text-dark);
    text-decoration: none;
    font-weight: 600;
}

.hero-contact-details span {
    font-size: 12px;
    color: var(--text-medium);
}

.hero-secondary-actions {
    display: flex;
    align-items: center;
    gap: var(--space-lg);
    flex-wrap: wrap;
}

.hero-secondary {
    display: inline-flex;
    align-items: center;
    gap: var(--space-xs);
    color: rgba(255, 255, 255, 0.9);
    text-decoration: none;
    font-weight: 600;
    font-size: 15px;
}

.hero-visual {
    flex: 1 1 360px;
    display: flex;
    justify-content: center;
    padding: var(--space-lg);
}

.hero-illustration {
    position: relative;
    width: 360px;
    height: 360px;
    background: rgba(255, 255, 255, 0.18);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    box-shadow: 0 35px 80px rgba(15, 23, 42, 0.16);
    backdrop-filter: blur(6px);
}

.hero-pin {
    width: 140px;
    height: 140px;
    border-radius: 50%;
    background: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 48px;
    color: var(--primary-orange);
    box-shadow: 0 18px 40px rgba(255, 122, 61, 0.35);
}

.hero-bubble {
    position: absolute;
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    padding: var(--space-sm) var(--space-md);
    border-radius: var(--radius-xl);
    background: var(--white);
    color: var(--text-dark);
    box-shadow: 0 18px 40px rgba(15, 23, 42, 0.12);
    font-weight: 600;
    font-size: 14px;
}

.hero-bubble i {
    font-size: 18px;
    color: var(--primary-orange);
}

.hero-bubble--vendors {
    top: 24px;
    right: -32px;
}

.hero-bubble--buddy {
    bottom: 52px;
    right: 6px;
}

.hero-bubble--buddy i {
    color: #FFB703;
}


.content {
    background: var(--white);
    border-radius: var(--radius-xl) var(--radius-xl) 0 0;
    padding: calc(var(--space-xl) * 1.05) var(--space-lg) var(--space-xl);
    margin-top: -60px;
    box-shadow: 0 -20px 50px rgba(16, 24, 40, 0.08);
}

.card {
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: var(--space-lg) var(--space-md);
    box-shadow: var(--shadow-sm);
}

.card + .card,
.card + .view-toggle {
    margin-top: var(--space-lg);
}

.card-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    gap: var(--space-md);
    margin-bottom: var(--space-md);
}

.card-title {
    font-size: 22px;
    font-weight: 700;
}

.card-subtitle {
    color: var(--text-medium);
    font-size: 14px;
}

.recommendations-carousel {
    display: flex;
    gap: var(--space-md);
    overflow-x: auto;
    padding-bottom: var(--space-sm);
    -webkit-overflow-scrolling: touch;
}

.recommendation-card {
    min-width: 220px;
    background: var(--white);
    border-radius: var(--radius-md);
    padding: var(--space-md);
    box-shadow: var(--shadow-sm);
    cursor: pointer;
    transition: all 0.3s ease;
}

.recommendation-card:hover {
    transform: translateY(-4px);
    box-shadow: var(--shadow-md);
}

.recommendation-image {
    width: 60px;
    height: 60px;
    background: rgba(255, 123, 77, 0.12);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--primary-orange);
    font-size: 24px;
    margin-bottom: var(--space-md);
}

.recommendation-content h4 {
    font-size: 16px;
    font-weight: 600;
    margin-bottom: var(--space-xs);
}

.recommendation-content p {
    font-size: 14px;
    color: var(--text-medium);
    margin-bottom: var(--space-sm);
}

.recommendation-badges {
    display: flex;
    flex-wrap: wrap;
    gap: var(--space-xs);
    margin-bottom: var(--space-sm);
}

.recommendation-footer {
    display: flex;
    justify-content: space-between;
    font-size: 12px;
    color: var(--text-medium);
}

.view-toggle {
    display: flex;
    background: var(--white);
    border-radius: var(--radius-md);
    padding: var(--space-xs);
    margin-top: var(--space-lg);
    margin-bottom: var(--space-md);
    box-shadow: var(--shadow-sm);
}

.toggle-btn {
    flex: 1;
    background: none;
    border: none;
    padding: var(--space-md);
    border-radius: var(--radius-sm);
    font-size: 14px;
    font-weight: 500;
    color: var(--text-medium);
    cursor: pointer;
    transition: all 0.3s ease;
}

.toggle-btn.active {
    background: var(--primary-orange);
    color: var(--white);
}

.filters {
    display: flex;
    gap: var(--space-sm);
    margin-bottom: var(--space-xl);
    overflow-x: auto;
    padding-bottom: var(--space-sm);
}

.filter-btn {
    background: var(--white);
    border: 1px solid var(--medium-gray);
    border-radius: var(--radius-md);
    padding: var(--space-sm) var(--space-md);
    font-size: 12px;
    font-weight: 500;
    color: var(--text-medium);
    cursor: pointer;
    transition: all 0.3s ease;
    white-space: nowrap;
}

.filter-btn.active {
    background: var(--primary-orange);
    color: var(--white);
    border-color: var(--primary-orange);
}

.vendors-section {
    margin-bottom: var(--space-xl);
}

.section-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: var(--space-md);
}

.section-header h3 {
    font-size: 20px;
    font-weight: 600;
}

.view-all-link {
    color: var(--primary-orange);
    text-decoration: none;
    font-weight: 600;
}

.quick-actions {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: var(--space-md);
    margin-top: var(--space-xl);
}

.quick-action-btn {
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: var(--space-lg) var(--space-md);
    background: var(--white);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--text-dark);
    box-shadow: var(--shadow-sm);
    transition: all 0.3s ease;
}

.quick-action-btn:hover {
    transform: translateY(-2px);
    box-shadow: var(--shadow-md);
    color: var(--primary-orange);
}

.quick-action-btn i {
    font-size: 24px;
    margin-bottom: var(--space-sm);
}

.quick-action-btn span {
    font-size: 12px;
    font-weight: 500;
    text-align: center;
}

.vendor-actions {
    display: flex;
    gap: var(--space-sm);
    margin-top: var(--space-md);
}

.vendor-actions .btn {
    flex: 1;
}

/* Map Styles */
.vendors-map {
    background: var(--white);
    border-radius: var(--radius-lg);
    padding: var(--space-md);
    box-shadow: var(--shadow-sm);
}

.map-container {
    position: relative;
    width: 100%;
}

.map-header {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: var(--space-md);
    padding-bottom: var(--space-sm);
    border-bottom: 1px solid var(--light-gray);
}

.user-location {
    display: flex;
    align-items: center;
    gap: var(--space-sm);
    color: var(--primary-orange);
    font-weight: 600;
}

.map-legend {
    display: flex;
    gap: var(--space-md);
}

.legend-item {
    display: flex;
    align-items: center;
    gap: var(--space-xs);
    font-size: 12px;
}

.legend-color {
    width: 12px;
    height: 12px;
    border-radius: 50%;
}

.map-area {
    position: relative;
    width: 100%;
    height: 300px;
    background: linear-gradient(135deg, #f8fafc 0%, #e2e8f0 100%);
    border-radius: var(--radius-md);
    overflow: hidden;
    border: 2px solid var(--light-gray);
}

.map-grid {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

.grid-line-h, .grid-line-v {
    position: absolute;
    background: rgba(148, 163, 184, 0.1);
}

.grid-line-h {
    width: 100%;
    height: 1px;
}

.grid-line-v {
    height: 100%;
    width: 1px;
}

.map-marker {
    position: absolute;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 16px;
    cursor: pointer;
    transform: translate(-50%, -50%);
    transition: all 0.3s ease;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    z-index: 10;
}

.map-marker:hover {
    transform: translate(-50%, -50%) scale(1.1);
    z-index: 20;
}

.user-marker {
    background: var(--primary-orange);
    border: 3px solid var(--white);
    animation: pulse 2s infinite;
}

.vendor-marker {
    background: var(--primary-teal);
    border: 2px solid var(--white);
}

.marker-tooltip {
    position: absolute;
    bottom: 100%;
    left: 50%;
    transform: translateX(-50%);
    background: var(--text-dark);
    color: var(--white);
    padding: var(--space-sm) var(--space-md);
    border-radius: var(--radius-sm);
    font-size: 12px;
    white-space: nowrap;
    opacity: 0;
    visibility: hidden;
    transition: all 0.3s ease;
    margin-bottom: var(--space-sm);
    z-index: 30;
}

.marker-tooltip::after {
    content: '';
    position: absolute;
    top: 100%;
    left: 50%;
    transform: translateX(-50%);
    border: 5px solid transparent;
    border-top-color: var(--text-dark);
}

.map-marker:hover .marker-tooltip {
    opacity: 1;
    visibility: visible;
}

.marker-distance {
    color: var(--accent-yellow);
    font-weight: 600;
}

.map-footer {
    display: flex;
    justify-content: center;
    gap: var(--space-md);
    margin-top: var(--space-md);
    padding-top: var(--space-sm);
    border-top: 1px solid var(--light-gray);
}

@keyframes pulse {
    0% {
        box-shadow: 0 0 0 0 rgba(255, 122, 61, 0.7);
    }
    70% {
        box-shadow: 0 0 0 10px rgba(255, 122, 61, 0);
    }
    100% {
        box-shadow: 0 0 0 0 rgba(255, 122, 61, 0);
    }
}

@media (max-width: 1024px) {
    .hero-container {
        flex-direction: column;
        text-align: center;
    }
    
    .hero-copy h1 {
        font-size: 36px;
    }
    
    .hero-copy p {
        margin-left: auto;
        margin-right: auto;
    }
    
    .hero-card-content {
        flex-direction: column;
        align-items: center;
        text-align: center;
    }
    
    .hero-card-text p {
        max-width: 100%;
    }
    
    .hero-secondary-actions {
        justify-content: center;
    }
    
    .content {
        padding: var(--space-xl) var(--space-md);
    }
}

@media (max-width: 768px) {
    .hero-copy h1 {
        font-size: 32px;
    }
    
    .hero-illustration {
        width: 260px;
        height: 260px;
    }
    
    .hero-primary-card {
        padding: var(--space-md);
    }
    
    .quick-help-btn.hero-primary {
        width: 100%;
    }
    
    .content {
        margin-top: -44px;
    }
    
    .quick-actions {
        grid-template-columns: repeat(2, 1fr);
    }
}

@media (max-width: 480px) {
    .hero-copy h1 {
        font-size: 28px;
    }
    
    .hero-copy p {
        font-size: 16px;
    }
    
    .hero-primary-card {
        padding: var(--space-md);
    }
    
    .content {
        margin-top: -28px;
    }
    
    .view-toggle {
        flex-direction: column;
    }
    
    .quick-actions {
        grid-template-columns: 1fr;
    }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // View toggle
    const toggleBtns = document.querySelectorAll('.toggle-btn');
    const vendorsList = document.getElementById('vendors-list');
    const vendorsMap = document.getElementById('vendors-map');
    
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            toggleBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            
            if (this.dataset.view === 'map') {
                vendorsList.style.display = 'none';
                vendorsMap.style.display = 'block';
                // Add click handlers to map markers
                addMapMarkerHandlers();
            } else {
                vendorsList.style.display = 'block';
                vendorsMap.style.display = 'none';
            }
        });
    });
    
    // Add map marker click handlers
    function addMapMarkerHandlers() {
        const vendorMarkers = document.querySelectorAll('.vendor-marker');
        vendorMarkers.forEach(marker => {
            marker.addEventListener('click', function() {
                const vendorId = this.dataset.vendorId;
                if (vendorId) {
                    window.location.href = `?page=vendor-detail&id=${vendorId}`;
                }
            });
        });
    }
    
    // Filter buttons
    const filterBtns = document.querySelectorAll('.filter-btn');
    const vendorCards = document.querySelectorAll('.vendor-card, .recommendation-card');
    
    filterBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            filterBtns.forEach(b => b.classList.remove('active'));
            this.classList.add('active');
            const filter = this.dataset.filter;
            
            vendorCards.forEach(card => {
                if (!filter || filter === '') {
                    card.style.display = '';
                    return;
                }
                
                let show = false;
                const badges = card.querySelectorAll('.badge');
                
                switch(filter) {
                    case 'verified':
                        show = Array.from(badges).some(b => 
                            b.textContent.includes('Verified') || b.textContent.includes('Scout')
                        );
                        break;
                    case 'price':
                        // Show vendors with price info
                        const priceEl = card.querySelector('.vendor-price, .price');
                        show = priceEl !== null;
                        break;
                    case 'distance':
                        // Show vendors with distance info
                        const distEl = card.querySelector('.vendor-distance, .distance');
                        show = distEl !== null;
                        break;
                    case 'language':
                        // Show vendors with language info
                        const langEl = card.querySelector('.vendor-languages');
                        show = langEl !== null;
                        break;
                    default:
                        show = true;
                }
                
                card.style.display = show ? '' : 'none';
            });
        });
    });
    
    // Recommendation cards
    const recCards = document.querySelectorAll('.recommendation-card');
    recCards.forEach(card => {
        card.addEventListener('click', function() {
            const vendorId = this.dataset.vendorId;
            window.location.href = `?page=vendor-detail&id=${vendorId}`;
        });
    });
    
    // Booking buttons
    const bookBtns = document.querySelectorAll('[data-book-vendor]');
    console.log('Found booking buttons:', bookBtns.length);
    bookBtns.forEach(btn => {
        btn.addEventListener('click', async function(e) {
            e.preventDefault();
            e.stopPropagation();
            const vendorId = this.dataset.bookVendor || this.getAttribute('data-book-vendor');
            console.log('Booking button clicked, vendor ID:', vendorId);
            if (vendorId && typeof showVendorBookingModal === 'function') {
                await showVendorBookingModal(vendorId);
            } else {
                console.error('showBookingModal not found or vendorId missing', { vendorId, showBookingModal: typeof showBookingModal });
                alert('Error: Unable to open booking form. Please try again.');
            }
        });
    });

    const quickHelpToggle = document.getElementById('quick-help-toggle');
    const quickHelpPanel = document.getElementById('quick-help-panel');
    if (quickHelpToggle && quickHelpPanel) {
        quickHelpToggle.addEventListener('click', function() {
            const isOpen = quickHelpPanel.classList.toggle('is-open');
            quickHelpToggle.classList.toggle('is-open', isOpen);
            quickHelpToggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            quickHelpPanel.setAttribute('aria-hidden', isOpen ? 'false' : 'true');
        });
    }
});

// Make function globally accessible
window.showVendorBookingModal = async function(vendorId) {
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
    let vendorName = vendorCard?.getAttribute('data-vendor-name') || vendorCard?.querySelector('h3, h4')?.textContent || 'Vendor';
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
            
            // Get vendor name from detail page
            const detailName = doc.querySelector('.vendor-detail-screen .header h1')?.textContent;
            if (detailName) vendorName = detailName.trim();
            
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
        console.log('Could not fetch detailed vendor data, using basic info', error);
    }

    // Create modal (always create, even if fetch failed)
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
    modal.querySelector('#booking-form').addEventListener('submit', async function(e) {
            e.preventDefault();
            
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
                const response = await fetch('', {
                    method: 'POST',
                    body: bookingData,
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                
                if (response.ok) {
                    const result = await response.json();
                    if (result.success) {
                        showToast('Booking confirmed! You will receive a confirmation SMS.', 'success');
                        modal.remove();
                    } else {
                        showToast(result.message || 'Error submitting booking', 'error');
                    }
                } else {
                    showToast('Error submitting booking', 'error');
                }
            } catch (error) {
                console.error('Error submitting booking:', error);
                showToast('Error submitting booking', 'error');
            }
        });
};

window.showToast = function(message, type) {
    const toast = document.createElement('div');
    toast.className = `toast ${type}`;
    toast.textContent = message;
    toast.style.cssText = 'position:fixed;bottom:20px;left:50%;transform:translateX(-50%);background:var(--primary-orange);color:white;padding:12px 24px;border-radius:8px;z-index:10000;';
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
};

// Map utility functions
window.centerMap = function() {
    showToast('Map centered on your location', 'success');
    // In a real implementation, this would center the map on user's GPS location
};

window.openExternalMap = function() {
    // Get user's location (default to Accra if not available)
    const userLat = 5.6037;  // Default Accra coordinates
    const userLng = -0.1870;
    
    // Try to get user's actual location
    if (navigator.geolocation) {
        navigator.geolocation.getCurrentPosition(
            function(position) {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                openMapsApp(lat, lng);
            },
            function(error) {
                // Fallback to default location
                openMapsApp(userLat, userLng);
            }
        );
    } else {
        // Fallback to default location
        openMapsApp(userLat, userLng);
    }
};

function openMapsApp(lat, lng) {
    // Detect device and open appropriate maps app
    const isIOS = /iPad|iPhone|iPod/.test(navigator.userAgent);
    const isAndroid = /Android/.test(navigator.userAgent);
    
    if (isIOS) {
        // Open Apple Maps
        window.open(`maps://maps.google.com/maps?daddr=${lat},${lng}&amp;ll=`, '_blank');
    } else if (isAndroid) {
        // Open Google Maps
        window.open(`geo:${lat},${lng}`, '_blank');
    } else {
        // Open Google Maps in browser
        window.open(`https://www.google.com/maps/@${lat},${lng},15z`, '_blank', 'noopener,noreferrer');
    }
}
</script>

