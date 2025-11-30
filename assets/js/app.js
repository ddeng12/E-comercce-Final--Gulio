// Gulio City Companion - Main App JavaScript

class GulioApp {
    constructor() {
        this.currentPage = 'onboarding';
        this.user = {
            name: '',
            role: '',
            languages: [],
            trust_pref: 'balanced',
            starter_pack: '',
            location: null,
            intent: ''
        };
        this.vendors = [];
        this.init();
    }

    init() {
        this.bindEvents();
        this.loadUserData();
        this.setupServiceWorker();
    }

    bindEvents() {
        // Navigation
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-page]')) {
                e.preventDefault();
                this.navigateTo(e.target.dataset.page);
            }
            
            if (e.target.matches('[data-back]')) {
                e.preventDefault();
                this.goBack();
            }
        });

        // Form submissions (exclude auth forms - they submit normally)
        document.addEventListener('submit', (e) => {
            const form = e.target;
            // Skip AJAX handling for login/register forms - let them submit normally
            if (form.classList.contains('auth-form') || 
                form.querySelector('input[name="action"][value="login"]') || 
                form.querySelector('input[name="action"][value="register"]')) {
                return; // Let form submit normally
            }
            e.preventDefault();
            this.handleFormSubmit(form);
        });

        // Quick Help
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-quick-help]')) {
                this.showQuickHelpModal();
            }
            
            if (e.target.matches('[data-quick-option]')) {
                this.handleQuickHelpOption(e.target.dataset.quickOption);
            }
        });

        // Modal handling
        document.addEventListener('click', (e) => {
            if (e.target.matches('.modal-close, .modal')) {
                this.hideModal();
            }
        });

        // Vendor interactions
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-vendor-id]')) {
                this.showVendorDetail(e.target.dataset.vendorId);
            }
            
            if (e.target.matches('[data-book-vendor]')) {
                const vendorId = e.target.dataset.bookVendor;
                if (typeof window.showVendorBookingModal === 'function') {
                    window.showVendorBookingModal(vendorId);
                } else {
                    console.error('showVendorBookingModal function not found');
                }
            }
        });

        // Feedback
        document.addEventListener('click', (e) => {
            if (e.target.matches('[data-feedback]')) {
                this.showFeedbackModal(e.target.dataset.feedback);
            }
            
            if (e.target.matches('[data-feedback-tag]')) {
                this.toggleFeedbackTag(e.target);
            }
        });

        // Trust preference slider
        const trustSlider = document.getElementById('trust-slider');
        if (trustSlider) {
            trustSlider.addEventListener('input', (e) => {
                this.updateTrustPreference(e.target.value);
            });
        }

        // Language selection
        document.addEventListener('change', (e) => {
            if (e.target.matches('[data-language]')) {
                this.updateLanguages();
            }
        });

        // Starter pack selection
        document.addEventListener('change', (e) => {
            if (e.target.matches('[data-starter-pack]')) {
                this.updateStarterPack(e.target.value);
            }
        });
    }

    navigateTo(page) {
        this.currentPage = page;
        window.history.pushState({page}, '', `?page=${page}`);
        this.loadPage(page);
    }

    goBack() {
        window.history.back();
    }

    async loadPage(page) {
        try {
            const response = await fetch(`?page=${page}`, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const html = await response.text();
                document.getElementById('app').innerHTML = html;
                this.initializePage(page);
            }
        } catch (error) {
            console.error('Error loading page:', error);
            this.showToast('Error loading page', 'error');
        }
    }

    initializePage(page) {
        switch (page) {
            case 'home':
                this.initializeHomePage();
                break;
            case 'vendors':
                this.initializeVendorsPage();
                break;
            case 'chatbot':
                this.initializeChatbot();
                break;
            case 'city-buddy':
                this.initializeCityBuddy();
                break;
        }
    }

    async initializeHomePage() {
        // Load user's location
        await this.getUserLocation();
        
        // Load nearby vendors
        await this.loadNearbyVendors();
        
        // Initialize map if present
        if (document.getElementById('map')) {
            this.initializeMap();
        }
    }

    async initializeVendorsPage() {
        await this.loadVendors();
        this.setupVendorFilters();
    }

    async loadVendors(category = '') {
        try {
            const formData = new FormData();
            formData.append('action', 'get_vendors');
            formData.append('lat', this.user.location?.lat || 5.6037);
            formData.append('lng', this.user.location?.lng || -0.1870);
            formData.append('category', category);

            const response = await fetch('', {
                method: 'POST',
                body: formData
            });

            if (response.ok) {
                this.vendors = await response.json();
                this.renderVendors();
            }
        } catch (error) {
            console.error('Error loading vendors:', error);
            this.showToast('Error loading vendors', 'error');
        }
    }

    renderVendors() {
        const container = document.getElementById('vendors-list');
        if (!container) return;

        container.innerHTML = this.vendors.map(vendor => `
            <div class="vendor-card" data-vendor-id="${vendor.id}">
                <div class="vendor-header">
                    <div class="vendor-info">
                        <h3>${vendor.name}</h3>
                        <div class="vendor-category">${vendor.category.replace('_', ' ')}</div>
                    </div>
                    <div class="vendor-rating">
                        <span class="trust-score">${vendor.trust_score}</span>
                        <i class="fas fa-star"></i>
                    </div>
                </div>
                
                <div class="vendor-details">
                    <span class="vendor-distance">${this.formatDistance(vendor.distance)}</span>
                    <span class="vendor-price">${this.formatPrice(vendor.price_items[0])}</span>
                </div>
                
                <div class="vendor-badges">
                    ${vendor.badges.map(badge => `<span class="badge badge-${this.getBadgeClass(badge)}">${badge}</span>`).join('')}
                </div>
                
                <div class="vendor-actions">
                    <button class="btn btn-outline btn-sm" data-vendor-id="${vendor.id}">
                        <i class="fas fa-info-circle"></i> Details
                    </button>
                    <button class="btn btn-primary btn-sm" data-book-vendor="${vendor.id}">
                        <i class="fas fa-calendar"></i> Book
                    </button>
                </div>
            </div>
        `).join('');
    }

    setupVendorFilters() {
        const filterButtons = document.querySelectorAll('[data-filter]');
        filterButtons.forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const category = e.target.dataset.filter;
                this.loadVendors(category);
                
                // Update active filter
                filterButtons.forEach(b => b.classList.remove('active'));
                e.target.classList.add('active');
            });
        });
    }

    showVendorDetail(vendorId) {
        this.navigateTo(`vendor-detail&id=${vendorId}`);
    }

    showBookingModal(vendorId) {
        const vendor = this.vendors.find(v => v.id == vendorId);
        if (!vendor) return;

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
                            ${vendor.price_items.map(item => 
                                `<option value="${item.service}">${item.service} - ${this.formatPrice(item)}</option>`
                            ).join('')}
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Preferred Time</label>
                        <input type="datetime-local" class="form-input" name="datetime" required>
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
        
        // Handle form submission
        modal.querySelector('#booking-form').addEventListener('submit', (e) => {
            e.preventDefault();
            this.submitBooking(vendorId, new FormData(e.target));
        });
    }

    async submitBooking(vendorId, formData) {
        const submitBtn = document.querySelector('#booking-form button[type="submit"]');
        const originalBtnText = submitBtn ? submitBtn.innerHTML : '';
        
        // Show loading state
        if (submitBtn) {
            submitBtn.disabled = true;
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Processing...';
        }
        
        try {
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
            
            const response = await fetch('', {
                method: 'POST',
                body: bookingData,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            
            if (response.ok) {
                const result = await response.json();
                if (result.success) {
                    this.showToast('Booking confirmed! You will receive a confirmation SMS.', 'success');
                    const modal = document.querySelector('.modal');
                    if (modal) modal.remove();
                } else {
                    this.showToast(result.message || 'Unable to complete booking. Please try again.', 'error');
                    if (submitBtn) {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = originalBtnText;
                    }
                }
            } else {
                this.showToast('Unable to complete booking. Please check your connection and try again.', 'error');
                if (submitBtn) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = originalBtnText;
                }
            }
        } catch (error) {
            this.showToast('Connection error. Please check your internet and try again.', 'error');
            if (submitBtn) {
                submitBtn.disabled = false;
                submitBtn.innerHTML = originalBtnText;
            }
        }
    }

    showQuickHelpModal() {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Quick Help</h2>
                    <button class="modal-close">&times;</button>
                </div>
                
                <div class="quick-help-options">
                    <div class="quick-help-option" data-quick-option="barber">
                        <i class="fas fa-cut" style="color: #FF6B35;"></i>
                        <h3>Barber now</h3>
                    </div>
                    <div class="quick-help-option" data-quick-option="phone_repair">
                        <i class="fas fa-mobile-alt" style="color: #4ECDC4;"></i>
                        <h3>Phone repair</h3>
                    </div>
                    <div class="quick-help-option" data-quick-option="food">
                        <i class="fas fa-utensils" style="color: #45B7D1;"></i>
                        <h3>Food near me</h3>
                    </div>
                    <div class="quick-help-option" data-quick-option="city_buddy">
                        <i class="fas fa-user-friends" style="color: #96CEB4;"></i>
                        <h3>Book City Buddy</h3>
                    </div>
                    <div class="quick-help-option" data-quick-option="emergency">
                        <i class="fas fa-exclamation-triangle" style="color: #FF4757;"></i>
                        <h3>Emergency</h3>
                    </div>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    handleQuickHelpOption(option) {
        document.querySelector('.modal').remove();
        
        switch (option) {
            case 'barber':
            case 'phone_repair':
            case 'food':
                this.loadVendors(option);
                this.navigateTo('vendors');
                break;
            case 'city_buddy':
                this.navigateTo('city-buddy');
                break;
            case 'emergency':
                this.showEmergencyOptions();
                break;
        }
    }

    showEmergencyOptions() {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Emergency Help</h2>
                    <button class="modal-close">&times;</button>
                </div>
                
                <div class="emergency-options">
                    <a href="tel:191" class="btn btn-danger btn-lg btn-full">
                        <i class="fas fa-phone"></i> Call Police (191)
                    </a>
                    <a href="tel:193" class="btn btn-danger btn-lg btn-full">
                        <i class="fas fa-ambulance"></i> Call Ambulance (193)
                    </a>
                    <a href="tel:192" class="btn btn-danger btn-lg btn-full">
                        <i class="fas fa-fire"></i> Call Fire Service (192)
                    </a>
                    <button class="btn btn-outline btn-lg btn-full" onclick="this.closest('.modal').remove()">
                        <i class="fas fa-user-friends"></i> Contact City Buddy
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
    }

    showFeedbackModal(vendorId) {
        const modal = document.createElement('div');
        modal.className = 'modal';
        modal.innerHTML = `
            <div class="modal-content">
                <div class="modal-header">
                    <h2 class="modal-title">Rate Your Experience</h2>
                    <button class="modal-close">&times;</button>
                </div>
                
                <div class="feedback-widget">
                    <div class="feedback-actions">
                        <button class="feedback-btn thumbs-up" data-rating="5">
                            <i class="fas fa-thumbs-up"></i>
                        </button>
                        <button class="feedback-btn thumbs-down" data-rating="1">
                            <i class="fas fa-thumbs-down"></i>
                        </button>
                    </div>
                    
                    <div class="feedback-tags">
                        <button class="feedback-tag" data-tag="Great Service">Great Service</button>
                        <button class="feedback-tag" data-tag="Overcharged">Overcharged</button>
                        <button class="feedback-tag" data-tag="Unsafe">Unsafe</button>
                        <button class="feedback-tag" data-tag="Poor Quality">Poor Quality</button>
                        <button class="feedback-tag" data-tag="Late">Late</button>
                        <button class="feedback-tag" data-tag="Friendly">Friendly</button>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Additional Comments (Optional)</label>
                        <textarea class="form-textarea" name="comment" placeholder="Tell us about your experience..."></textarea>
                    </div>
                    
                    <button class="btn btn-primary btn-full" onclick="this.submitFeedback()">
                        <i class="fas fa-paper-plane"></i> Submit Feedback
                    </button>
                </div>
            </div>
        `;
        
        document.body.appendChild(modal);
        
        // Add feedback functionality
        modal.querySelectorAll('.feedback-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                modal.querySelectorAll('.feedback-btn').forEach(b => b.classList.remove('selected'));
                e.target.classList.add('selected');
            });
        });
        
        modal.querySelectorAll('.feedback-tag').forEach(tag => {
            tag.addEventListener('click', (e) => {
                e.target.classList.toggle('selected');
            });
        });
        
        // Add submit functionality
        modal.querySelector('.btn-primary').addEventListener('click', () => {
            this.submitFeedback(vendorId, modal);
        });
    }

    async submitFeedback(vendorId, modal) {
        const rating = modal.querySelector('.feedback-btn.selected')?.dataset.rating || 0;
        const selectedTags = Array.from(modal.querySelectorAll('.feedback-tag.selected')).map(t => t.dataset.tag);
        const comment = modal.querySelector('textarea').value;
        
        try {
            const formData = new FormData();
            const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
            
            formData.append('action', 'submit_review');
            formData.append('vendor_id', vendorId);
            formData.append('rating', rating);
            formData.append('tags', JSON.stringify(selectedTags));
            formData.append('comment', comment);
            if (csrfToken) {
                formData.append('csrf_token', csrfToken);
            }

            const response = await fetch('', {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });

            if (response.ok) {
                this.showToast('Thank you for your feedback!', 'success');
                modal.remove();
            }
        } catch (error) {
            console.error('Error submitting feedback:', error);
            this.showToast('Error submitting feedback', 'error');
        }
    }

    async handleFormSubmit(form) {
        const formData = new FormData(form);
        const action = formData.get('action') || form.dataset.action;
        
        if (!action) {
            console.log('No action specified for form');
            return;
        }
        
        // Add CSRF token if required
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
        }
        
        try {
            console.log('Submitting form with action:', action);
            const response = await fetch(window.location.href, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            });
            
            if (response.ok) {
                const contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    const result = await response.json();
                    console.log('Form response:', result);
                    if (result.success) {
                        this.showToast(result.message || 'Saved successfully!', 'success');
                        this.handleFormSuccess(action, formData);
                    } else {
                        this.showToast(result.message || 'Error occurred', 'error');
                    }
                } else {
                    // Server returned HTML instead of JSON (likely a PHP error)
                    const htmlResponse = await response.text();
                    console.error('Server returned HTML instead of JSON:', htmlResponse.substring(0, 200));
                    this.showToast('Server error: Check console for details', 'error');
                }
            } else {
                console.error('Response not ok:', response.status);
                this.showToast('Server error occurred (Status: ' + response.status + ')', 'error');
            }
        } catch (error) {
            console.error('Error submitting form:', error);
            if (error.message.includes('Unexpected token')) {
                this.showToast('Server returned invalid response. Check PHP errors.', 'error');
            } else {
                this.showToast('Error submitting form: ' + error.message, 'error');
            }
        }
    }

    handleFormSuccess(action, formData) {
        switch (action) {
            case 'update_profile':
                this.user.name = formData.get('name');
                this.user.role = formData.get('role');
                this.user.languages = formData.getAll('languages');
                this.saveUserData();
                break;
            case 'update_location':
                this.user.location = {
                    lat: parseFloat(formData.get('lat')),
                    lng: parseFloat(formData.get('lng')),
                    address: formData.get('address')
                };
                this.user.intent = formData.get('intent');
                this.saveUserData();
                break;
            case 'update_trust_pref':
                this.user.trust_pref = formData.get('trust_pref');
                this.saveUserData();
                break;
            case 'update_starter_pack':
                this.user.starter_pack = formData.get('starter_pack');
                this.saveUserData();
                break;
        }
    }

    async getUserLocation() {
        if (!navigator.geolocation) {
            this.showToast('Geolocation not supported', 'error');
            return;
        }
        
        return new Promise((resolve, reject) => {
            navigator.geolocation.getCurrentPosition(
                (position) => {
                    this.user.location = {
                        lat: position.coords.latitude,
                        lng: position.coords.longitude,
                        address: 'Current Location'
                    };
                    this.saveUserData();
                    resolve(this.user.location);
                },
                (error) => {
                    console.error('Geolocation error:', error);
                    this.showToast('Unable to get location', 'error');
                    reject(error);
                },
                { timeout: 10000, enableHighAccuracy: true }
            );
        });
    }

    async loadNearbyVendors() {
        if (!this.user.location) {
            await this.getUserLocation();
        }
        
        await this.loadVendors();
    }

    updateTrustPreference(value) {
        const labels = ['Strict Verified', 'Balanced', 'Open'];
        const label = document.querySelector('.trust-preference-label');
        if (label) {
            label.textContent = labels[value - 1] || 'Balanced';
        }
        
        this.user.trust_pref = value;
        this.saveUserData();
    }

    updateLanguages() {
        const checkboxes = document.querySelectorAll('[data-language]:checked');
        this.user.languages = Array.from(checkboxes).map(cb => cb.value);
        this.saveUserData();
    }

    updateStarterPack(pack) {
        this.user.starter_pack = pack;
        this.saveUserData();
    }

    loadUserData() {
        const saved = localStorage.getItem('gulio_user');
        if (saved) {
            this.user = { ...this.user, ...JSON.parse(saved) };
        }
    }

    saveUserData() {
        localStorage.setItem('gulio_user', JSON.stringify(this.user));
    }

    showToast(message, type = 'info') {
        const toast = document.createElement('div');
        toast.className = `toast ${type}`;
        toast.textContent = message;
        
        document.body.appendChild(toast);
        
        setTimeout(() => {
            toast.remove();
        }, 3000);
    }

    hideModal() {
        const modal = document.querySelector('.modal');
        if (modal) {
            modal.remove();
        }
    }

    formatDistance(distance) {
        if (distance < 1) {
            return Math.round(distance * 1000) + 'm';
        }
        return Math.round(distance * 10) / 10 + 'km';
    }

    formatPrice(priceItem) {
        // Handle both array and individual values
        if (Array.isArray(priceItem)) {
            const min = priceItem.min || 0;
            const max = priceItem.max || 0;
            if (min === max) {
                return `GHS ${min}`;
            }
            return `GHS ${min} - ${max}`;
        } else {
            // If single value passed, use it for both min and max
            return `GHS ${priceItem}`;
        }
    }

    getBadgeClass(badge) {
        if (badge.includes('Scout')) return 'scout';
        if (badge.includes('Local')) return 'local';
        if (badge.includes('Verified')) return 'verified';
        return 'reviews';
    }

    setupServiceWorker() {
        // Service worker disabled for now - can be enabled when sw.js is created
        // if ('serviceWorker' in navigator) {
        //     navigator.serviceWorker.register('/sw.js')
        //         .then(registration => console.log('SW registered'))
        //         .catch(error => console.log('SW registration failed'));
        // }
    }

    // Initialize chatbot
    initializeChatbot() {
        const chatContainer = document.querySelector('.chat-container');
        if (!chatContainer) return;

        const messagesContainer = chatContainer.querySelector('.chat-messages');
        const inputContainer = chatContainer.querySelector('.chat-input');
        const input = inputContainer.querySelector('input');
        const sendBtn = inputContainer.querySelector('.chat-send');

        const sendMessage = () => {
            const message = input.value.trim();
            if (!message) return;

            // Add user message
            this.addChatMessage(message, 'user');
            input.value = '';

            // Simulate bot response
            setTimeout(() => {
                const response = this.getChatbotResponse(message);
                this.addChatMessage(response, 'bot');
            }, 1000);
        };

        sendBtn.addEventListener('click', sendMessage);
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') {
                sendMessage();
            }
        });
    }

    addChatMessage(message, sender) {
        const messagesContainer = document.querySelector('.chat-messages');
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${sender}`;
        
        messageDiv.innerHTML = `
            <div class="message-bubble ${sender}">
                ${message}
            </div>
        `;
        
        messagesContainer.appendChild(messageDiv);
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    getChatbotResponse(message) {
        const responses = {
            'hello': 'Akwaaba! Welcome to Accra. How can I help you today?',
            'help': 'I can help you find services, translate text, give safety tips, or connect you with a local buddy.',
            'safety': 'Here are some safety tips: Always meet in public places, verify vendor identity, keep emergency numbers handy (191 for police), and trust your instincts.',
            'food': 'Great! I can help you find verified food vendors nearby. What type of food are you looking for?',
            'buddy': 'I can connect you with verified local buddies who can help you navigate Accra. Would you like to see available buddies?'
        };

        const lowerMessage = message.toLowerCase();
        for (const [key, response] of Object.entries(responses)) {
            if (lowerMessage.includes(key)) {
                return response;
            }
        }

        return 'I understand you need help. You can ask me about safety tips, finding services, translation, or connecting with local buddies.';
    }


    // Initialize city buddy
    initializeCityBuddy() {
        // Load city buddies
        this.loadCityBuddies();
    }

    async loadCityBuddies() {
        try {
            // Mock data - in real app this would be an API call
            const buddies = [
                {
                    id: 1,
                    name: 'Kofi Mensah',
                    languages: ['English', 'Twi', 'French'],
                    specialties: ['Food', 'Transportation', 'Shopping'],
                    rate: 15,
                    rating: 4.9,
                    verified_visits: 45,
                    badges: ['Verified Local', 'Top Rated']
                },
                {
                    id: 2,
                    name: 'Ama Serwaa',
                    languages: ['English', 'Twi', 'Ga'],
                    specialties: ['Culture', 'Safety', 'Nightlife'],
                    rate: 12,
                    rating: 4.7,
                    verified_visits: 32,
                    badges: ['Cultural Expert', 'Safety Guide']
                }
            ];

            this.renderCityBuddies(buddies);
        } catch (error) {
            console.error('Error loading city buddies:', error);
        }
    }

    renderCityBuddies(buddies) {
        const container = document.getElementById('buddies-list');
        if (!container) return;

        container.innerHTML = buddies.map(buddy => `
            <div class="buddy-card">
                <div class="buddy-header">
                    <div class="buddy-avatar">${buddy.name.charAt(0)}</div>
                    <div class="buddy-info">
                        <h3>${buddy.name}</h3>
                        <div class="buddy-rating">
                            <i class="fas fa-star"></i>
                            <span>${buddy.rating}</span>
                            <span>(${buddy.verified_visits} visits)</span>
                        </div>
                    </div>
                </div>
                
                <div class="buddy-specialties">
                    ${buddy.specialties.map(specialty => 
                        `<span class="specialty-tag">${specialty}</span>`
                    ).join('')}
                </div>
                
                <div class="buddy-footer">
                    <div class="buddy-rate">GHS ${buddy.rate}/hour</div>
                    <button class="btn btn-primary" data-buddy-id="${buddy.id}">
                        <i class="fas fa-calendar"></i> Book
                    </button>
                </div>
            </div>
        `).join('');
    }
}

// Initialize app when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    window.gulioApp = new GulioApp();
});

// Handle browser back/forward
window.addEventListener('popstate', (e) => {
    if (e.state && e.state.page) {
        window.gulioApp.loadPage(e.state.page);
    }
});

