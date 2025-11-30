<?php
$user = $_SESSION['user'];
$presets = getChatbotPresets();
?>

<div class="chatbot-screen">
    <div class="header">
        <button class="back-btn" onclick="history.back()">
            <i class="fas fa-arrow-left"></i>
        </button>
        <h1>Smart Chat</h1>
        <p>Your AI assistant for Accra</p>
    </div>
    
    <div class="content">
        <!-- Chat Presets -->
        <div class="presets-section">
            <h3>Quick Start</h3>
            <div class="presets-grid">
                <?php foreach ($presets as $preset): ?>
                <div class="preset-card" data-preset="<?= $preset['id'] ?>">
                    <div class="preset-icon">
                        <i class="fas fa-<?= $preset['icon'] ?>"></i>
                    </div>
                    <div class="preset-content">
                        <h4><?= htmlspecialchars($preset['name']) ?></h4>
                        <p><?= htmlspecialchars($preset['description']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
        
        <!-- Chat Interface -->
        <div class="chat-container">
            <div class="chat-messages" id="chat-messages">
                <div class="chat-message bot">
                    <div class="message-bubble bot">
                        <div class="bot-avatar">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="message-content">
                            <strong>Gulio Assistant</strong><br>
                            Akwaaba! 👋 Welcome to Accra! I'm your friendly local helper - think of me as your guide who knows the city inside and out. I can help with safety tips, cultural advice, finding services, or just answering any questions you have. What would you like to know? 😊
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="chat-input">
                <input type="text" id="chat-input" placeholder="Ask me anything about Accra...">
                <button class="chat-send" id="chat-send">
                    <i class="fas fa-paper-plane"></i>
                </button>
            </div>
        </div>
        
        <!-- Quick Phrases -->
        <div class="quick-phrases">
            <h3>Common Questions</h3>
            <div class="phrase-grid">
                <button class="phrase-btn" data-phrase="Where can I find good food?">
                    Where can I find good food?
                </button>
                <button class="phrase-btn" data-phrase="How do I get around Accra?">
                    How do I get around Accra?
                </button>
                <button class="phrase-btn" data-phrase="What are comprehensive safety tips for Accra?">
                    What are comprehensive safety tips for Accra?
                </button>
                <button class="phrase-btn" data-phrase="How do I avoid scams in Accra?">
                    How do I avoid scams in Accra?
                </button>
                <button class="phrase-btn" data-phrase="Is it safe to walk at night?">
                    Is it safe to walk at night?
                </button>
                <button class="phrase-btn" data-phrase="What are emergency numbers?">
                    What are emergency numbers?
                </button>
                <button class="phrase-btn" data-phrase="How do I stay safe using taxis?">
                    How do I stay safe using taxis?
                </button>
                <button class="phrase-btn" data-phrase="What about money and ATM safety?">
                    What about money and ATM safety?
                </button>
                <button class="phrase-btn" data-phrase="Is the beach safe to swim?">
                    Is the beach safe to swim?
                </button>
                <button class="phrase-btn" data-phrase="What about food and water safety?">
                    What about food and water safety?
                </button>
                <button class="phrase-btn" data-phrase="How do I greet people?">
                    How do I greet people?
                </button>
                <button class="phrase-btn" data-phrase="Where can I get my phone fixed?">
                    Where can I get my phone fixed?
                </button>
                <button class="phrase-btn" data-phrase="What should I know about Ghanaian culture?">
                    What should I know about Ghanaian culture?
                </button>
            </div>
        </div>
        
        <!-- Emergency Help -->
        <div class="emergency-section">
            <h3>Emergency Help</h3>
            <div class="emergency-options">
                <a href="tel:191" class="emergency-btn">
                    <i class="fas fa-phone"></i>
                    <div>
                        <strong>Police</strong>
                        <span>191</span>
                    </div>
                </a>
                <a href="tel:193" class="emergency-btn">
                    <i class="fas fa-ambulance"></i>
                    <div>
                        <strong>Ambulance</strong>
                        <span>193</span>
                    </div>
                </a>
                <a href="tel:192" class="emergency-btn">
                    <i class="fas fa-fire"></i>
                    <div>
                        <strong>Fire Service</strong>
                        <span>192</span>
                    </div>
                </a>
            </div>
        </div>
    </div>
</div>

<style>
.chatbot-screen {
    min-height: 100vh;
    background: var(--light-gray);
}

.chatbot-screen .header {
    background: linear-gradient(135deg, var(--primary-orange), var(--primary-teal));
    color: var(--white);
    padding: var(--space-lg) var(--space-md) var(--space-md);
    text-align: center;
    position: relative;
}

.chatbot-screen .header h1 {
    font-size: 24px;
    font-weight: 700;
    margin-bottom: var(--space-xs);
}

.chatbot-screen .header p {
    font-size: 14px;
    opacity: 0.9;
}

.presets-section {
    background: var(--white);
    padding: var(--space-lg);
    margin-bottom: var(--space-md);
    box-shadow: var(--shadow-sm);
}

.presets-section h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--space-lg);
}

.presets-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-md);
}

.preset-card {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-md);
    background: var(--light-gray);
    border-radius: var(--radius-md);
    cursor: pointer;
    transition: all 0.3s ease;
}

.preset-card:hover {
    background: var(--medium-gray);
    transform: translateY(-2px);
}

.preset-icon {
    width: 50px;
    height: 50px;
    background: var(--primary-orange);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 20px;
}

.preset-content h4 {
    font-size: 14px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--space-xs);
}

.preset-content p {
    font-size: 12px;
    color: var(--text-medium);
    line-height: 1.4;
}

.chat-container {
    background: var(--white);
    border-radius: var(--radius-md);
    margin-bottom: var(--space-md);
    box-shadow: var(--shadow-sm);
    overflow: hidden;
}

.chat-messages {
    height: 300px;
    padding: var(--space-lg);
    overflow-y: auto;
    background: var(--light-gray);
}

.chat-message {
    margin-bottom: var(--space-lg);
    display: flex;
    align-items: flex-start;
    gap: var(--space-md);
}

.chat-message.user {
    flex-direction: row-reverse;
}

.message-bubble {
    max-width: 80%;
    padding: var(--space-md);
    border-radius: var(--radius-md);
    font-size: 14px;
    line-height: 1.5;
    position: relative;
}

.message-bubble.user {
    background: var(--primary-orange);
    color: var(--white);
    border-bottom-right-radius: var(--space-xs);
}

.message-bubble.bot {
    background: var(--white);
    color: var(--text-dark);
    border: 1px solid var(--medium-gray);
    border-bottom-left-radius: var(--space-xs);
}

.bot-avatar {
    width: 30px;
    height: 30px;
    background: var(--primary-teal);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: var(--white);
    font-size: 14px;
    margin-bottom: var(--space-sm);
}

.message-content {
    line-height: 1.6;
}

.message-content p {
    margin: 0.5em 0;
}

.message-content p:first-child {
    margin-top: 0;
}

.message-content p:last-child {
    margin-bottom: 0;
}

.message-content strong {
    font-weight: 600;
    color: var(--text-dark);
}

.chat-input {
    display: flex;
    padding: var(--space-md);
    background: var(--white);
    border-top: 1px solid var(--medium-gray);
    gap: var(--space-sm);
}

.chat-input input {
    flex: 1;
    padding: var(--space-md);
    border: 1px solid var(--medium-gray);
    border-radius: var(--radius-md);
    font-size: 14px;
    outline: none;
}

.chat-input input:focus {
    border-color: var(--primary-orange);
}

.chat-send {
    background: var(--primary-orange);
    color: var(--white);
    border: none;
    border-radius: var(--radius-md);
    padding: var(--space-md);
    cursor: pointer;
    min-width: 50px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.chat-send:hover {
    background: #E55A2B;
}

.quick-phrases {
    background: var(--white);
    padding: var(--space-lg);
    margin-bottom: var(--space-md);
    box-shadow: var(--shadow-sm);
}

.quick-phrases h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--space-lg);
}

.phrase-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: var(--space-sm);
}

.phrase-btn {
    background: var(--light-gray);
    border: 1px solid var(--medium-gray);
    border-radius: var(--radius-md);
    padding: var(--space-md);
    font-size: 12px;
    cursor: pointer;
    transition: all 0.3s ease;
    text-align: center;
    line-height: 1.4;
}

.phrase-btn:hover {
    background: var(--medium-gray);
    border-color: var(--primary-orange);
}

.emergency-section {
    background: var(--white);
    padding: var(--space-lg);
    margin-bottom: var(--space-md);
    box-shadow: var(--shadow-sm);
}

.emergency-section h3 {
    font-size: 18px;
    font-weight: 600;
    color: var(--text-dark);
    margin-bottom: var(--space-lg);
}

.emergency-options {
    display: flex;
    flex-direction: column;
    gap: var(--space-md);
}

.emergency-btn {
    display: flex;
    align-items: center;
    gap: var(--space-md);
    padding: var(--space-lg);
    background: var(--light-gray);
    border-radius: var(--radius-md);
    text-decoration: none;
    color: var(--text-dark);
    transition: all 0.3s ease;
}

.emergency-btn:hover {
    background: var(--medium-gray);
    transform: translateY(-2px);
}

.emergency-btn i {
    font-size: 24px;
    color: var(--accent-red);
}

.emergency-btn strong {
    display: block;
    font-size: 16px;
    font-weight: 600;
    margin-bottom: var(--space-xs);
}

.emergency-btn span {
    font-size: 14px;
    color: var(--text-medium);
}

/* Typing Indicator */
.typing-dots {
    display: flex;
    gap: 4px;
    align-items: center;
    padding: 8px 0;
}

.typing-dots span {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: var(--text-medium);
    animation: typing 1.4s infinite ease-in-out;
}

.typing-dots span:nth-child(1) {
    animation-delay: -0.32s;
}

.typing-dots span:nth-child(2) {
    animation-delay: -0.16s;
}

@keyframes typing {
    0%, 80%, 100% {
        transform: scale(0.8);
        opacity: 0.5;
    }
    40% {
        transform: scale(1);
        opacity: 1;
    }
}

/* Fallback Indicator */
.fallback-indicator {
    background: rgba(255, 193, 7, 0.1);
    color: #ff6b35;
    font-size: 11px;
    padding: 4px 8px;
    border-radius: 12px;
    margin-bottom: 8px;
    display: flex;
    align-items: center;
    gap: 4px;
}

.fallback-indicator i {
    font-size: 10px;
}

/* Chat Input States */
.chat-input input:disabled {
    background: var(--light-gray);
    color: var(--text-medium);
    cursor: not-allowed;
}

.chat-send:disabled {
    background: var(--medium-gray);
    cursor: not-allowed;
}

.chat-send:disabled:hover {
    transform: none;
    background: var(--medium-gray);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const chatMessages = document.getElementById('chat-messages');
    const chatInput = document.getElementById('chat-input');
    const chatSend = document.getElementById('chat-send');
    
    // Send message function
    async function sendMessage() {
        const message = chatInput.value.trim();
        if (!message) return;
        
        // Add user message
        addMessage(message, 'user');
        chatInput.value = '';
        
        // Disable input while processing
        chatInput.disabled = true;
        chatSend.disabled = true;
        
        // Show typing indicator
        showTypingIndicator();
        
        try {
            // Send to ChatGPT API
            const response = await getChatGPTResponse(message);
            hideTypingIndicator();
            addMessage(response.message, 'bot', response.fallback);
        } catch (error) {
            console.error('Chat error:', error);
            hideTypingIndicator();
            addMessage('Sorry, I\'m having trouble responding right now. Please try again in a moment.', 'bot', true);
        } finally {
            // Re-enable input
            chatInput.disabled = false;
            chatSend.disabled = false;
            chatInput.focus();
        }
    }
    
    // Get response from ChatGPT API
    async function getChatGPTResponse(message) {
        const formData = new FormData();
        formData.append('action', 'chat_message');
        formData.append('message', message);
        
        const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');
        if (csrfToken) {
            formData.append('csrf_token', csrfToken);
        }
        
        const response = await fetch('', {
            method: 'POST',
            body: formData,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        
        const result = await response.json();
        
        if (result.success) {
            return {
                message: result.message,
                fallback: result.fallback || false
            };
        } else {
            throw new Error(result.message || 'Unknown error');
        }
    }
    
    // Add message to chat
    function addMessage(message, sender, isFallback = false) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `chat-message ${sender}`;
        
        if (sender === 'bot') {
            const fallbackIndicator = isFallback ? '<div class="fallback-indicator"><i class="fas fa-exclamation-triangle"></i> Offline mode</div>' : '';
            messageDiv.innerHTML = `
                <div class="message-bubble bot">
                    <div class="bot-avatar">
                        <i class="fas fa-robot"></i>
                    </div>
                    <div class="message-content">
                        ${fallbackIndicator}
                        ${formatMessage(message)}
                    </div>
                </div>
            `;
        } else {
            messageDiv.innerHTML = `
                <div class="message-bubble user">${message}</div>
            `;
        }
        
        chatMessages.appendChild(messageDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Show typing indicator
    function showTypingIndicator() {
        const typingDiv = document.createElement('div');
        typingDiv.className = 'chat-message bot typing-indicator';
        typingDiv.id = 'typing-indicator';
        typingDiv.innerHTML = `
            <div class="message-bubble bot">
                <div class="bot-avatar">
                    <i class="fas fa-robot"></i>
                </div>
                <div class="message-content">
                    <div class="typing-dots">
                        <span></span>
                        <span></span>
                        <span></span>
                    </div>
                </div>
            </div>
        `;
        
        chatMessages.appendChild(typingDiv);
        chatMessages.scrollTop = chatMessages.scrollHeight;
    }
    
    // Hide typing indicator
    function hideTypingIndicator() {
        const typingIndicator = document.getElementById('typing-indicator');
        if (typingIndicator) {
            typingIndicator.remove();
        }
    }
    
    // Comprehensive safety response function
    function getComprehensiveSafetyResponse(lowerMsg) {
        // Emergency contacts
        if (lowerMsg.includes('emergency') || lowerMsg.includes('police') || lowerMsg.includes('ambulance') || lowerMsg.includes('fire')) {
            return `🚨 EMERGENCY CONTACTS:\n\n• Police: 191\n• Ambulance: 193\n• Fire Service: 192\n• Tourist Police: +233 302 664 698\n\nSave these numbers in your phone. For immediate help, call 191 or contact a City Buddy through the app.`;
        }
        
        // Scams and fraud - detailed with examples
        if (lowerMsg.includes('scam') || lowerMsg.includes('fraud') || lowerMsg.includes('cheat')) {
            return `⚠️ **AVOIDING SCAMS - This is Important!**\n\nI want to help you stay safe, so let me share the common scams and how to avoid them:\n\n🚫 **Common Scams in Accra:**\n\n1. **"I Need Help" Scam:**\n   • Someone approaches saying they need money for emergency\n   • They might have a "sick relative" or "lost wallet"\n   • **Red Flag:** They're very pushy and won't take no for an answer\n   • **What to Do:** Politely decline. Real emergencies don't need random strangers' money.\n\n2. **"Great Deal" Scam:**\n   • Someone offers you something "too good to be true"\n   • Example: "I can get you a phone for 100 GHS" (when it costs 500 GHS)\n   • **Red Flag:** Unrealistic prices, pressure to decide quickly\n   • **What to Do:** If it sounds too good to be true, it probably is. Walk away.\n\n3. **"Fake Vendor" Scam:**\n   • Someone claims to be from a business but isn't verified\n   • They want payment upfront before service\n   • **Red Flag:** Won't show ID, wants cash only, no receipt\n   • **What to Do:** Only use verified vendors from Gulio app. Always verify identity.\n\n4. **"ATM Helper" Scam:**\n   • Someone offers to "help" you at an ATM\n   • They might try to see your PIN or swap your card\n   • **Red Flag:** Anyone approaching you at an ATM\n   • **What to Do:** Never let anyone near you at an ATM. Cover your PIN always.\n\n💡 **Golden Rules:**\n• ✅ Always meet vendors in public places (cafes, shops, busy areas)\n• ✅ Verify identity - ask for ID, check their Gulio profile\n• ✅ Never pay 100% upfront - pay after service or 50/50\n• ✅ Don't share banking details, PINs, or passwords\n• ✅ Trust your instincts - if something feels wrong, it probably is\n• ✅ Use verified vendors from Gulio app - we've checked them!\n• ✅ Get receipts for everything\n• ✅ If pressured, walk away - there are always other options\n\n🎯 **Real Example:**\nSomeone approaches you saying "I can get you a great deal on a phone!" → Red flag! Real vendors don't approach you on the street. Use verified shops or our app.\n\n🌟 **Remember:** Most Ghanaians are honest and helpful! Scammers are the minority. But being aware helps you stay safe. When in doubt, use verified services from Gulio! 😊`;
        }
        
        // Night safety
        if (lowerMsg.includes('night') || lowerMsg.includes('dark') || lowerMsg.includes('evening') || lowerMsg.includes('late')) {
            return `🌙 NIGHT SAFETY:\n\n• Avoid walking alone at night in unfamiliar areas\n• Use well-lit, busy streets\n• Take Uber or Bolt instead of walking\n• Keep valuables hidden and out of sight\n• Stay in groups when possible\n• Avoid isolated beaches or parks after dark\n• Inform someone of your whereabouts\n• Have emergency numbers ready\n• Consider hiring a City Buddy for evening activities`;
        }
        
        // Transportation safety
        if (lowerMsg.includes('taxi') || lowerMsg.includes('transport') || lowerMsg.includes('uber') || lowerMsg.includes('bolt') || lowerMsg.includes('tro-tro') || lowerMsg.includes('driving')) {
            return `🚗 TRANSPORTATION SAFETY:\n\n• Use Uber or Bolt for safest rides (tracked, verified drivers)\n• Always negotiate taxi fares BEFORE getting in\n• Share your ride details with someone\n• Avoid unmarked taxis, especially at night\n• Be cautious with tro-tros (shared minibuses) - keep valuables secure\n• Don't display expensive items in vehicles\n• Lock doors and keep windows up in traffic\n• Trust your instincts about drivers\n• Have cash and mobile money ready for payment`;
        }
        
        // Money and banking
        if (lowerMsg.includes('money') || lowerMsg.includes('cash') || lowerMsg.includes('atm') || lowerMsg.includes('bank') || lowerMsg.includes('payment')) {
            return `💰 MONEY SAFETY:\n\n• Don't carry large amounts of cash\n• Use ATMs in banks or shopping malls (avoid street ATMs)\n• Cover your PIN when using ATMs\n• Use mobile money (MTN Mobile Money, Vodafone Cash) when possible\n• Keep some cash separate from main wallet\n• Don't display money in public\n• Be cautious when counting money in public\n• Use hotel safes for valuables\n• Keep copies of important documents separate`;
        }
        
        // Beach and water safety
        if (lowerMsg.includes('beach') || lowerMsg.includes('swim') || lowerMsg.includes('water') || lowerMsg.includes('ocean')) {
            return `🏖️ BEACH & WATER SAFETY:\n\n• Swim only at designated beaches with lifeguards\n• Be aware of strong currents and undertows\n• Don't swim alone, especially at unfamiliar beaches\n• Avoid swimming after dark\n• Don't leave valuables unattended on the beach\n• Use waterproof pouches for phones/wallets\n• Be cautious of jellyfish and sea urchins\n• Stay hydrated and use sunscreen\n• Respect local beach rules and customs`;
        }
        
        // Food and water safety
        if (lowerMsg.includes('food safety') || (lowerMsg.includes('food') && lowerMsg.includes('safe')) || lowerMsg.includes('drink') || (lowerMsg.includes('water') && !lowerMsg.includes('beach'))) {
            return `🍽️ FOOD & WATER SAFETY:\n\n• Drink only bottled or filtered water\n• Avoid ice in drinks unless from trusted sources\n• Eat at busy restaurants (high turnover = fresher food)\n• Wash hands before eating\n• Be cautious with street food - choose vendors with good hygiene\n• Peel fruits yourself\n• Avoid raw salads unless from upscale restaurants\n• Stick to well-cooked foods\n• Use verified food vendors from Gulio app\n• Carry hand sanitizer`;
        }
        
        // Health and medical
        if (lowerMsg.includes('health') || lowerMsg.includes('medical') || lowerMsg.includes('hospital') || lowerMsg.includes('doctor') || lowerMsg.includes('sick') || lowerMsg.includes('illness')) {
            return `🏥 HEALTH & MEDICAL:\n\n• Carry travel insurance with medical coverage\n• Know location of nearest hospital/clinic\n• Major hospitals: Korle Bu, 37 Military Hospital, Nyaho Medical Centre\n• Keep prescription medications in original containers\n• Bring mosquito repellent (malaria prevention)\n• Stay hydrated, especially in heat\n• Use sunscreen (strong sun in Ghana)\n• Get travel vaccinations before arrival\n• Know your blood type and allergies\n• Keep emergency medical contacts handy`;
        }
        
        // Accommodation safety
        if (lowerMsg.includes('hotel') || lowerMsg.includes('accommodation') || lowerMsg.includes('guesthouse') || lowerMsg.includes('hostel')) {
            return `🏨 ACCOMMODATION SAFETY:\n\n• Choose accommodations in safe, well-lit areas\n• Use hotel safes for valuables\n• Lock doors and windows\n• Don't open door to strangers\n• Verify identity of hotel staff\n• Keep emergency numbers by the phone\n• Know fire exits and emergency procedures\n• Don't leave valuables in plain sight\n• Use door chains when available\n• Report suspicious activity to management`;
        }
        
        // Tourist areas
        if (lowerMsg.includes('tourist') || lowerMsg.includes('attraction') || lowerMsg.includes('visit') || lowerMsg.includes('sightseeing')) {
            return `🎯 TOURIST AREA SAFETY:\n\n• Be extra vigilant at tourist attractions (pickpocket hotspots)\n• Keep bags in front, not behind\n• Don't accept unsolicited help from strangers\n• Use official tour guides\n• Be cautious of "friendly" locals offering tours\n• Keep valuables secure and hidden\n• Stay with groups when possible\n• Use verified City Buddies for guided tours\n• Research areas before visiting\n• Trust official information sources`;
        }
        
        // Technology and devices
        if (lowerMsg.includes('phone') && (lowerMsg.includes('safe') || lowerMsg.includes('theft')) || lowerMsg.includes('laptop') || lowerMsg.includes('camera') || lowerMsg.includes('device')) {
            return `📱 TECHNOLOGY SAFETY:\n\n• Don't display expensive phones/cameras in public\n• Use phone cases and screen protectors\n• Keep devices secure in crowded areas\n• Use tracking apps (Find My iPhone, etc.)\n• Back up photos and data regularly\n• Be cautious when using public WiFi\n• Don't leave devices unattended\n• Use hotel safes for laptops/tablets\n• Report stolen devices to police immediately\n• Keep device serial numbers recorded`;
        }
        
        // General comprehensive safety response
        return `🛡️ COMPREHENSIVE SAFETY TIPS FOR ACCRA:\n\n📞 EMERGENCIES:\n• Police: 191 | Ambulance: 193 | Fire: 192\n• Tourist Police: +233 302 664 698\n\n✅ GENERAL SAFETY:\n• Always meet vendors in public places\n• Verify identity before transactions\n• Use verified services from Gulio app\n• Trust your instincts\n• Keep emergency numbers saved\n• Avoid walking alone at night\n• Use Uber/Bolt for safe transportation\n• Don't display valuables in public\n• Keep copies of important documents\n• Stay aware of your surroundings\n\n💰 MONEY:\n• Use ATMs in banks/malls only\n• Don't carry large cash amounts\n• Use mobile money when possible\n• Cover PIN when using ATMs\n\n🌙 NIGHT:\n• Avoid isolated areas\n• Use well-lit streets\n• Take taxis instead of walking\n• Stay in groups\n\n🍽️ FOOD/HEALTH:\n• Drink bottled/filtered water\n• Eat at busy restaurants\n• Wash hands frequently\n• Use mosquito repellent\n\nAsk me about specific safety topics: scams, transportation, money, beaches, health, or night safety!`;
    }
    
    // Format message with line breaks and formatting
    function formatMessage(message) {
        // Convert markdown-style formatting to HTML
        let formatted = message
            .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>') // Bold
            .replace(/\n\n/g, '</p><p>') // Double line breaks
            .replace(/\n/g, '<br>'); // Single line breaks
        
        return '<p>' + formatted + '</p>';
    }
    
    // Fallback function (not used with ChatGPT, kept for reference)
    function getFallbackBotResponse(message) {
        const lowerMessage = message.toLowerCase();
        
        // Cultural tips - detailed and human
        if (lowerMessage.includes('greet') || lowerMessage.includes('hello')) {
            return `Hey! Great question! 😊 Greeting people properly in Ghana is really important and shows respect. Here's how to do it right:\n\n👋 **The Proper Greeting:**\n• Always start with "Akwaaba" (ah-kwah-bah) which means "welcome"\n• Shake hands with your RIGHT hand (the left hand is considered unclean)\n• After shaking, it's polite to ask "Ete sen?" (eh-teh sen) which means "How are you?"\n• Wait for their response - don't rush! They might say "Eye" (eh-yeh) meaning "fine"\n• You can respond with "Eye, medaase" (fine, thank you)\n\n💡 **Real Example:**\nWhen you meet someone, say: "Akwaaba! Ete sen?" and extend your right hand. They'll appreciate this gesture!\n\n🎯 **Pro Tip:** Ghanaians are very warm and friendly. A good greeting can open doors and make people more willing to help you. Even if you're just asking for directions, start with "Akwaaba" and you'll get a much warmer response!`;
        }
        
        // Safety tips - comprehensive
        if (lowerMessage.includes('safety') || lowerMessage.includes('safe') || lowerMessage.includes('scam') || lowerMessage.includes('fraud') || lowerMessage.includes('robbery') || lowerMessage.includes('theft') || lowerMessage.includes('pickpocket') || lowerMessage.includes('crime') || lowerMessage.includes('secure') || lowerMessage.includes('protect') || lowerMessage.includes('risk') || lowerMessage.includes('warning') || lowerMessage.includes('caution')) {
            return getComprehensiveSafetyResponse(lowerMessage);
        }
        
        // Food recommendations - detailed
        if (lowerMessage.includes('food') || lowerMessage.includes('eat') || lowerMessage.includes('restaurant')) {
            return `Oh, you're in for a treat! Ghanaian food is absolutely delicious! 🍛 Let me give you the inside scoop:\n\n🍽️ **Must-Try Dishes:**\n• **Jollof Rice** - The national dish! Spiced rice with tomatoes, onions, and sometimes chicken or fish. Every restaurant has their own version - it's a friendly competition!\n• **Banku with Fish** - Fermented corn dough served with grilled fish and spicy pepper sauce. A local favorite - try it at Asanka Local!\n• **Red Red** - Fried plantains with beans stew. Sweet and savory - so good!\n• **Waakye** - Rice and beans cooked together, served with spaghetti, fried plantain, and your choice of protein. Perfect breakfast!\n• **Kelewele** - Spicy fried plantains - perfect street food snack!\n\n🍴 **Where to Eat (Real Recommendations):**\n• **For Authentic Local:** Buka Restaurant in Osu, Asanka Local, or Chez Clarisse\n• **Street Food:** Look for busy stalls in Osu, Labone, or around Makola Market - high turnover means fresh food!\n• **Markets:** Makola Market and Kaneshie Market have amazing food sections - follow the locals!\n\n💡 **Eating Tips:**\n• Always wash your hands before eating (many places provide handwashing stations)\n• Eat with your right hand if eating traditional style\n• It's okay to ask what's in a dish if you have allergies\n• Start with small portions if trying something new\n• Drink bottled water with your meals\n\n🎯 **Pro Tip:** Don't be afraid to try street food! Just choose busy vendors with lots of locals - that's the best sign of good, fresh food. The best jollof rice I've had was from a street vendor in Osu! 😋 Want me to help you find a specific type of food or restaurant?`;
        }
        
        // Transportation - detailed
        if (lowerMessage.includes('transport') || lowerMessage.includes('get around') || lowerMessage.includes('taxi') || lowerMessage.includes('uber') || lowerMessage.includes('bolt') || lowerMessage.includes('tro-tro')) {
            return `Getting around Accra can be an adventure! Let me break down your options: 🚗\n\n🚕 **Ride-Hailing Apps (Safest & Easiest):**\n• **Uber** - Works great in Accra! Prices are fixed, drivers are verified, and you can track your ride. Perfect for safety.\n• **Bolt** - Similar to Uber, also very reliable. Sometimes slightly cheaper.\n• **Yango** - Another option, less common but still good\n\n💡 **Pro Tips for Apps:**\n• Always check the license plate matches the app\n• Share your ride details with someone\n• Use cash or mobile money for payment\n• Rate your driver - helps the community!\n\n🚌 **Tro-Tros (Budget Option):**\n• Shared minibuses - super cheap (usually 2-5 GHS)\n• Great for short distances and experiencing local life\n• BUT: Keep valuables secure, can be crowded, routes can be confusing\n• Ask locals which tro-tro goes where - they're helpful!\n\n🚖 **Regular Taxis:**\n• Yellow taxis are everywhere\n• **ALWAYS negotiate the fare BEFORE getting in!**\n• Example: "How much to Osu?" - agree on price first\n• Typical short trip: 10-20 GHS, longer: 20-50 GHS\n• If they quote too high, walk away - there are plenty of taxis\n\n🎯 **Real Examples:**\n• Airport to city center: Uber/Bolt = 50-80 GHS, Taxi = 60-100 GHS (negotiate!)\n• Osu to Labone: Uber/Bolt = 15-25 GHS, Tro-tro = 2-3 GHS\n• Short trips within city: Uber/Bolt = 10-20 GHS\n\n💡 **Safety Tips:**\n• Avoid unmarked taxis, especially at night\n• Don't display expensive items in vehicles\n• Trust your instincts about drivers\n• Have cash and mobile money ready\n\n🌟 **My Recommendation:** For your first few days, stick with Uber or Bolt until you get comfortable. Then try tro-tros for the authentic experience! Want help finding the best way to get somewhere specific?`;
        }
        
        // Phone repair - detailed
        if (lowerMessage.includes('phone') && lowerMessage.includes('repair')) {
            return `Oh no, phone trouble? Don't worry, I've got you covered! 📱\n\n🔧 **Verified Phone Repair Shops:**\n• **FastFix Mobiles** - Highly rated, fair pricing, verified on our app. They fix iPhones, Samsung, and all major brands.\n• **Phone Clinic** - Good for screen repairs and battery replacements\n• **Tech Support Ghana** - For more complex issues\n\n💡 **What to Expect:**\n• **Screen Repair:** Usually 200-500 GHS depending on phone model\n• **Battery Replacement:** 100-300 GHS\n• **Charging Port:** 50-150 GHS\n• **Software Issues:** 50-200 GHS\n\n🎯 **Pro Tips:**\n• Get a quote BEFORE they start work - ask "How much will this cost?"\n• Watch them work if possible - ensures they don't swap parts\n• Ask for warranty - good shops give 30-90 days\n• Use shops verified on Gulio app - we've checked them out!\n• Get a receipt - important for warranty claims\n\n⚠️ **Red Flags to Avoid:**\n• Shops that won't give a price upfront\n• Places that want to keep your phone overnight for simple fixes\n• Shops in very isolated areas\n• People who approach you on the street offering repairs\n\n🌟 **My Recommendation:** Use FastFix Mobiles or check our verified vendors list. They're trustworthy and won't overcharge. Want me to help you find the nearest one to your location?`;
        }
        
        // Culture - detailed
        if (lowerMessage.includes('culture') || lowerMessage.includes('custom') || lowerMessage.includes('tradition')) {
            return `Ghanaian culture is beautiful and welcoming! Let me share what makes it special: 🌍\n\n❤️ **Core Values:**\n• **Respect for Elders** - Always greet elders first, use "sir" or "ma" when addressing them. If an older person enters a room, stand up or acknowledge them.\n• **Hospitality** - Ghanaians are incredibly welcoming. "Akwaaba" isn't just a word - it's a way of life! People will invite you into their homes, share food, and go out of their way to help.\n• **Community** - People look out for each other. You'll see neighbors helping neighbors, strangers helping strangers.\n• **Patience** - Things move at their own pace. Embrace "Ghana time" - it's not laziness, it's a different philosophy of life!\n\n💡 **Practical Examples:**\n• **Right Hand Rule:** Use your RIGHT hand for everything - greetings, eating, giving/receiving items. The left hand is considered unclean.\n• **Ghana Time:** If someone says "meet at 2pm," they might arrive at 2:15pm or 2:30pm. This is normal! Don't take it personally.\n• **Food Sharing:** If someone offers you food, it's polite to accept (even a small amount). It's a sign of friendship.\n• **Street Greetings:** Don't be surprised if strangers greet you on the street - it's normal and friendly!\n• **Photos:** Always ask permission before taking photos of people, especially in markets or traditional areas.\n\n🎯 **Real Life Situations:**\n• You're lost? Ask someone "Excuse me, can you help me?" - they'll likely walk you there!\n• Someone offers you water or food? Accept graciously - it's a sign of respect.\n• An older person enters? Acknowledge them with "Good morning, sir/ma"\n• Taking a photo? "Can I take a photo?" - most people will say yes with a smile!\n\n🌟 **Pro Tip:** The best way to experience Ghanaian culture is to be open, patient, and respectful. Smile, greet people, and don't be in a rush. You'll find that Ghanaians are some of the friendliest people in the world! 😊 Want to know more about any specific aspect?`;
        }
        
        // Emergency - detailed and helpful
        if (lowerMessage.includes('emergency') || (lowerMessage.includes('help') && (lowerMessage.includes('urgent') || lowerMessage.includes('now') || lowerMessage.includes('immediate')))) {
            return `🚨 **EMERGENCY CONTACTS - SAVE THESE NOW!**\n\n📞 **Critical Numbers:**\n• **Police:** 191 (for crimes, theft, safety issues)\n• **Ambulance/Medical:** 193 (for health emergencies)\n• **Fire Service:** 192 (for fires, accidents)\n• **Tourist Police:** +233 302 664 698 (specifically for tourists)\n\n💡 **What to Do in an Emergency:**\n1. **Stay Calm** - Take a deep breath\n2. **Call the Right Number** - Police (191), Medical (193), Fire (192)\n3. **Give Your Location** - Be as specific as possible (street name, landmark, nearby building)\n4. **Describe the Situation** - What happened, how many people involved\n5. **Stay on the Line** - Don't hang up until they tell you to\n\n🎯 **Real Examples:**\n• **Theft/Robbery:** Call 191 immediately, give your location, describe what was taken and the person if you saw them\n• **Medical Emergency:** Call 193, describe symptoms, give exact location\n• **Lost/Confused:** Call Tourist Police (+233 302 664 698) - they're trained to help visitors\n\n🌟 **Additional Help:**\n• Contact a City Buddy through the app - they can help immediately\n• Go to the nearest police station or hospital\n• Ask locals for help - Ghanaians are very helpful in emergencies\n• Contact your embassy if needed\n\n💙 **Remember:** In a real emergency, don't hesitate to call. The operators speak English and are trained to help. Stay safe!`;
        }
        
        // Default response - more helpful
        return `I understand you're asking about "${message}". 😊 Let me help you!\n\nI can assist you with:\n• 🛡️ **Safety tips** - comprehensive advice for staying safe in Accra\n• 🌍 **Cultural guidance** - how to greet, dress, behave, and fit in\n• 🍽️ **Food recommendations** - where to eat, what to try, food safety\n• 🚗 **Transportation** - how to get around safely and affordably\n• 📍 **Finding services** - barbers, phone repair, food vendors, etc.\n• 👥 **City Buddies** - connect with verified locals who can guide you\n• 🆘 **Emergency help** - contacts and what to do in emergencies\n\nWhat specific help do you need? Just ask me anything - I'm here to make your stay in Accra safe and enjoyable! 💙`;
    }
    
    // Event listeners
    chatSend.addEventListener('click', sendMessage);
    
    chatInput.addEventListener('keypress', function(e) {
        if (e.key === 'Enter') {
            sendMessage();
        }
    });
    
    // Preset cards
    const presetCards = document.querySelectorAll('.preset-card');
    presetCards.forEach(card => {
        card.addEventListener('click', function() {
            const preset = this.dataset.preset;
            let message = '';
            
            switch (preset) {
                case 'cultural_coach':
                    message = 'Tell me about Ghanaian culture and customs';
                    break;
                case 'safety_check':
                    message = 'What safety tips should I know?';
                    break;
                case 'find_nearby':
                    message = 'Help me find services near me';
                    break;
            }
            
            if (message) {
                chatInput.value = message;
                sendMessage();
            }
        });
    });
    
    // Quick phrases
    const phraseBtns = document.querySelectorAll('.phrase-btn');
    phraseBtns.forEach(btn => {
        btn.addEventListener('click', function() {
            const phrase = this.dataset.phrase;
            chatInput.value = phrase;
            sendMessage();
        });
    });
});
</script>

