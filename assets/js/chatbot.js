// Chatbot utilities for Gulio app

class ChatbotService {
    constructor() {
        this.conversationHistory = [];
        this.currentContext = null;
        this.presets = {
            'cultural_coach': {
                name: 'Cultural Coach',
                description: 'Learn about local customs and culture',
                icon: 'graduation-cap',
                responses: this.getCulturalResponses()
            },
            'safety_check': {
                name: 'Safety Check',
                description: 'Get safety tips and advice',
                icon: 'shield-alt',
                responses: this.getSafetyResponses()
            },
            'find_nearby': {
                name: 'Find Nearby',
                description: 'Discover services around you',
                icon: 'map-marker-alt',
                responses: this.getFindNearbyResponses()
            },
        };
    }

    // Get response based on message and context
    getResponse(message, context = null) {
        const lowerMessage = message.toLowerCase();
        this.currentContext = context;
        
        // Add to conversation history
        this.conversationHistory.push({
            type: 'user',
            message: message,
            timestamp: new Date().toISOString()
        });

        let response = '';

        // Check for greetings
        if (this.isGreeting(lowerMessage)) {
            response = this.getGreetingResponse();
        }
        // Check for cultural questions
        else if (this.isCulturalQuestion(lowerMessage)) {
            response = this.getCulturalResponse(lowerMessage);
        }
        // Check for safety questions
        else if (this.isSafetyQuestion(lowerMessage)) {
            response = this.getSafetyResponse(lowerMessage);
        }
        // Check for location/service questions
        else if (this.isLocationQuestion(lowerMessage)) {
            response = this.getLocationResponse(lowerMessage);
        }
        // Check for emergency
        else if (this.isEmergency(lowerMessage)) {
            response = this.getEmergencyResponse();
        }
        // Default response
        else {
            response = this.getDefaultResponse(lowerMessage);
        }

        // Add response to conversation history
        this.conversationHistory.push({
            type: 'bot',
            message: response,
            timestamp: new Date().toISOString()
        });

        return response;
    }

    // Check if message is a greeting
    isGreeting(message) {
        const greetings = ['hello', 'hi', 'hey', 'akwaaba', 'good morning', 'good afternoon', 'good evening'];
        return greetings.some(greeting => message.includes(greeting));
    }

    // Check if message is about culture
    isCulturalQuestion(message) {
        const culturalKeywords = ['culture', 'custom', 'tradition', 'greet', 'handshake', 'time', 'dress', 'food', 'language'];
        return culturalKeywords.some(keyword => message.includes(keyword));
    }

    // Check if message is about safety
    isSafetyQuestion(message) {
        const safetyKeywords = ['safe', 'safety', 'danger', 'unsafe', 'scam', 'fraud', 'police', 'emergency', 'help', 'robbery', 'theft', 'pickpocket', 'crime', 'secure', 'protect', 'risk', 'warning', 'caution', 'night', 'dark', 'walking', 'taxi', 'transport', 'money', 'cash', 'atm', 'bank', 'beach', 'swim', 'water', 'health', 'medical', 'hospital', 'doctor', 'food safety', 'drink', 'water', 'accommodation', 'hotel', 'guesthouse'];
        return safetyKeywords.some(keyword => message.includes(keyword));
    }

    // Check if message is about finding locations/services
    isLocationQuestion(message) {
        const locationKeywords = ['find', 'where', 'nearby', 'close', 'barber', 'food', 'restaurant', 'shop', 'store', 'service'];
        return locationKeywords.some(keyword => message.includes(keyword));
    }


    // Check if message is an emergency
    isEmergency(message) {
        const emergencyKeywords = ['help', 'emergency', 'urgent', 'danger', 'police', 'ambulance', 'fire'];
        return emergencyKeywords.some(keyword => message.includes(keyword));
    }

    // Get greeting response
    getGreetingResponse() {
        const greetings = [
            'Akwaaba! Welcome to Accra! 👋 I\'m here to help you feel at home and stay safe. Whether you need safety tips, cultural advice, or help finding services, I\'ve got you covered. What would you like to know?',
            'Hello! Welcome to Accra! 😊 I\'m your local helper - think of me as your friendly guide who knows the city inside out. I can help with safety, culture, finding services, or just answering questions. What\'s on your mind?',
            'Hi there! Akwaaba to Accra! 🌟 I\'m here to make your stay safe and enjoyable. Need help with anything? Ask me about safety, local customs, where to find things, or anything else - I\'m here to help!'
        ];
        return greetings[Math.floor(Math.random() * greetings.length)];
    }

    // Get cultural response
    getCulturalResponse(message) {
        if (message.includes('greet') || message.includes('hello')) {
            return `Hey! Great question! 😊 Greeting people properly in Ghana is really important and shows respect. Here's how to do it right:\n\n👋 **The Proper Greeting:**\n• Always start with "Akwaaba" (ah-kwah-bah) which means "welcome"\n• Shake hands with your RIGHT hand (the left hand is considered unclean)\n• After shaking, it's polite to ask "Ete sen?" (eh-teh sen) which means "How are you?"\n• Wait for their response - don't rush! They might say "Eye" (eh-yeh) meaning "fine"\n• You can respond with "Eye, medaase" (fine, thank you)\n\n💡 **Real Example:**\nWhen you meet someone, say: "Akwaaba! Ete sen?" and extend your right hand. They'll appreciate this gesture!\n\n🎯 **Pro Tip:** Ghanaians are very warm and friendly. A good greeting can open doors and make people more willing to help you. Even if you're just asking for directions, start with "Akwaaba" and you'll get a much warmer response!`;
        }
        if (message.includes('time') || message.includes('late')) {
            return `Ah, "Ghana time"! 😄 This is something every visitor needs to understand, and it can be frustrating if you're not prepared.\n\n⏰ **What is Ghana Time?**\nGhana time is flexible - people often arrive 15-30 minutes (sometimes even an hour) after the agreed time. This is completely normal and NOT considered rude here!\n\n📅 **Real Examples:**\n• If someone says "meet at 2pm" - they might arrive at 2:15pm or 2:30pm\n• A party starting at 6pm? People will start arriving around 6:30pm\n• Even business meetings can start 15-20 minutes late\n\n💡 **How to Handle It:**\n• Don't take it personally - it's cultural, not disrespectful\n• Plan accordingly - if something starts at 2pm, you can arrive at 2:15pm\n• For important things (like flights, medical appointments), still be on time\n• Use it as an opportunity to relax and not stress about punctuality\n\n🎯 **Pro Tip:** If you're meeting a vendor or City Buddy, you can confirm the time but don't be surprised if they're a bit late. Bring a book or enjoy the moment - it's part of the Ghanaian way of life!`;
        }
        if (message.includes('dress') || message.includes('clothes')) {
            return `Great question! What you wear matters here, and showing respect through your clothing will help you blend in better. 👗\n\n👔 **Dressing Guidelines:**\n• **Modest is best** - especially when visiting religious sites, markets, or formal events\n• Avoid very revealing clothing (low-cut tops, very short shorts/skirts)\n• Light, breathable fabrics work best in the heat (cotton, linen)\n• Cover your shoulders and knees when visiting churches or mosques\n\n🌡️ **Practical Tips:**\n• It's HOT here! Light colors reflect heat better\n• Loose-fitting clothes are more comfortable\n• Bring a light scarf or shawl - useful for covering up when needed\n• Comfortable walking shoes are essential\n\n💡 **Real Examples:**\n• For markets: Light cotton shirt and pants/skirt (knee-length or longer)\n• For beaches: Swimwear is fine, but cover up when leaving the beach\n• For restaurants: Smart casual is usually fine\n• For religious sites: Cover shoulders and knees, remove shoes if required\n\n🎯 **Pro Tip:** When in doubt, dress more conservatively. You can always adjust, but you can't "un-see" something inappropriate. Ghanaians appreciate when visitors respect their cultural norms!`;
        }
        if (message.includes('food') || message.includes('eat')) {
            return `Oh, you're in for a treat! Ghanaian food is absolutely delicious! 🍛 Let me give you the inside scoop:\n\n🍽️ **Must-Try Dishes:**\n• **Jollof Rice** - The national dish! Spiced rice with tomatoes, onions, and sometimes chicken or fish. Every restaurant has their own version.\n• **Banku with Fish** - Fermented corn dough served with grilled fish and spicy pepper sauce. A local favorite!\n• **Red Red** - Fried plantains with beans stew. Sweet and savory - so good!\n• **Waakye** - Rice and beans cooked together, served with spaghetti, fried plantain, and your choice of protein\n• **Kelewele** - Spicy fried plantains - perfect street food snack!\n\n🍴 **Where to Eat:**\n• **Street Food:** Look for busy stalls with lots of locals - that's a good sign! Try places around Osu, Labone, or East Legon\n• **Restaurants:** For your first time, try places like Buka Restaurant, Asanka Local, or Chez Clarisse\n• **Markets:** Makola Market and Kaneshie Market have amazing food sections\n\n💡 **Eating Tips:**\n• Always wash your hands before eating (many places provide handwashing stations)\n• Eat with your right hand if eating traditional style\n• It's okay to ask what's in a dish if you have allergies\n• Start with small portions if trying something new\n• Drink bottled water with your meals\n\n🎯 **Pro Tip:** Don't be afraid to try street food! Just choose busy vendors with high turnover (fresh food) and good hygiene. The best jollof rice I've had was from a street vendor in Osu! 😋`;
        }
        if (message.includes('language')) {
            return `Language is a great way to connect with locals! Here's what you need to know: 🗣️\n\n📚 **The Language Situation:**\nEnglish is the official language and widely spoken, especially in Accra. But learning a few Twi phrases will make people smile and show you care!\n\n💬 **Essential Phrases to Learn:**\n• **"Akwaaba"** (ah-kwah-bah) - Welcome\n• **"Medaase"** (meh-dah-seh) - Thank you\n• **"Ete sen?"** (eh-teh sen) - How are you?\n• **"Eye"** (eh-yeh) - Fine/Okay\n• **"Mepa wo kyew"** (meh-pah woh chyew) - Please/Excuse me\n• **"Wo ho te sen?"** (woh hoh teh sen) - How are you? (more formal)\n• **"Me fre wo"** (meh freh woh) - My name is...\n• **"Wo din de sen?"** (woh din deh sen) - What is your name?\n\n💡 **How to Use Them:**\n• Start conversations with "Akwaaba" - instant connection!\n• Say "Medaase" when someone helps you - they'll appreciate it\n• If someone greets you with "Ete sen?", respond with "Eye, medaase"\n\n🎯 **Real Example:**\nWhen you enter a shop, say "Akwaaba! Ete sen?" The shopkeeper will be delighted and more willing to help you. When you leave, say "Medaase!" and you'll see big smiles!\n\n🌟 **Pro Tip:** Don't worry about perfect pronunciation - Ghanaians love when visitors try! Even a broken "Akwaaba" is better than nothing. They'll help you learn and might even teach you more phrases!`;
        }
        return `Ghanaian culture is beautiful and welcoming! Let me share what makes it special: 🌍\n\n❤️ **Core Values:**\n• **Respect for Elders** - Always greet elders first, use "sir" or "ma" when addressing them\n• **Hospitality** - Ghanaians are incredibly welcoming. "Akwaaba" isn't just a word - it's a way of life!\n• **Community** - People look out for each other. You'll see this everywhere\n• **Patience** - Things move at their own pace. Embrace it!\n\n💡 **Practical Examples:**\n• If an older person enters a room, stand up or acknowledge them\n• When someone offers you food, it's polite to accept (even a small amount)\n• If you're lost, people will go out of their way to help you\n• Don't be surprised if strangers greet you on the street - it's normal!\n\n🎯 **Pro Tip:** The best way to experience Ghanaian culture is to be open, patient, and respectful. Smile, greet people, and don't be in a rush. You'll find that Ghanaians are some of the friendliest people in the world! 😊`;
    }

    // Get safety response
    getSafetyResponse(message) {
        const lowerMsg = message.toLowerCase();
        
        // Emergency contacts
        if (lowerMsg.includes('emergency') || lowerMsg.includes('police') || lowerMsg.includes('ambulance') || lowerMsg.includes('fire')) {
            return '🚨 EMERGENCY CONTACTS:\n\n• Police: 191\n• Ambulance: 193\n• Fire Service: 192\n• Tourist Police: +233 302 664 698\n\nSave these numbers in your phone. For immediate help, call 191 or contact a City Buddy through the app.';
        }
        
        // Scams and fraud
        if (lowerMsg.includes('scam') || lowerMsg.includes('fraud') || lowerMsg.includes('cheat')) {
            return '⚠️ AVOIDING SCAMS:\n\n• Be wary of unsolicited offers, especially money-related\n• Never pay upfront for services without verification\n• Verify vendor identity before transactions\n• Meet in public places for all meetings\n• Don\'t share banking details with strangers\n• Be cautious of "too good to be true" deals\n• Use verified vendors from Gulio app\n• Trust your instincts - if something feels wrong, it probably is';
        }
        
        // Night safety
        if (lowerMsg.includes('night') || lowerMsg.includes('dark') || lowerMsg.includes('evening') || lowerMsg.includes('late')) {
            return '🌙 NIGHT SAFETY:\n\n• Avoid walking alone at night in unfamiliar areas\n• Use well-lit, busy streets\n• Take Uber or Bolt instead of walking\n• Keep valuables hidden and out of sight\n• Stay in groups when possible\n• Avoid isolated beaches or parks after dark\n• Inform someone of your whereabouts\n• Have emergency numbers ready\n• Consider hiring a City Buddy for evening activities';
        }
        
        // Transportation safety
        if (lowerMsg.includes('taxi') || lowerMsg.includes('transport') || lowerMsg.includes('uber') || lowerMsg.includes('bolt') || lowerMsg.includes('tro-tro') || lowerMsg.includes('driving')) {
            return '🚗 TRANSPORTATION SAFETY:\n\n• Use Uber or Bolt for safest rides (tracked, verified drivers)\n• Always negotiate taxi fares BEFORE getting in\n• Share your ride details with someone\n• Avoid unmarked taxis, especially at night\n• Be cautious with tro-tros (shared minibuses) - keep valuables secure\n• Don\'t display expensive items in vehicles\n• Lock doors and keep windows up in traffic\n• Trust your instincts about drivers\n• Have cash and mobile money ready for payment';
        }
        
        // Money and banking
        if (lowerMsg.includes('money') || lowerMsg.includes('cash') || lowerMsg.includes('atm') || lowerMsg.includes('bank') || lowerMsg.includes('payment')) {
            return '💰 MONEY SAFETY:\n\n• Don\'t carry large amounts of cash\n• Use ATMs in banks or shopping malls (avoid street ATMs)\n• Cover your PIN when using ATMs\n• Use mobile money (MTN Mobile Money, Vodafone Cash) when possible\n• Keep some cash separate from main wallet\n• Don\'t display money in public\n• Be cautious when counting money in public\n• Use hotel safes for valuables\n• Keep copies of important documents separate';
        }
        
        // Beach and water safety
        if (lowerMsg.includes('beach') || lowerMsg.includes('swim') || lowerMsg.includes('water') || lowerMsg.includes('ocean')) {
            return '🏖️ BEACH & WATER SAFETY:\n\n• Swim only at designated beaches with lifeguards\n• Be aware of strong currents and undertows\n• Don\'t swim alone, especially at unfamiliar beaches\n• Avoid swimming after dark\n• Don\'t leave valuables unattended on the beach\n• Use waterproof pouches for phones/wallets\n• Be cautious of jellyfish and sea urchins\n• Stay hydrated and use sunscreen\n• Respect local beach rules and customs';
        }
        
        // Food and water safety
        if (lowerMsg.includes('food safety') || lowerMsg.includes('eat') || lowerMsg.includes('drink') || lowerMsg.includes('water') || lowerMsg.includes('restaurant')) {
            return '🍽️ FOOD & WATER SAFETY:\n\n• Drink only bottled or filtered water\n• Avoid ice in drinks unless from trusted sources\n• Eat at busy restaurants (high turnover = fresher food)\n• Wash hands before eating\n• Be cautious with street food - choose vendors with good hygiene\n• Peel fruits yourself\n• Avoid raw salads unless from upscale restaurants\n• Stick to well-cooked foods\n• Use verified food vendors from Gulio app\n• Carry hand sanitizer';
        }
        
        // Health and medical
        if (lowerMsg.includes('health') || lowerMsg.includes('medical') || lowerMsg.includes('hospital') || lowerMsg.includes('doctor') || lowerMsg.includes('sick') || lowerMsg.includes('illness')) {
            return '🏥 HEALTH & MEDICAL:\n\n• Carry travel insurance with medical coverage\n• Know location of nearest hospital/clinic\n• Major hospitals: Korle Bu, 37 Military Hospital, Nyaho Medical Centre\n• Keep prescription medications in original containers\n• Bring mosquito repellent (malaria prevention)\n• Stay hydrated, especially in heat\n• Use sunscreen (strong sun in Ghana)\n• Get travel vaccinations before arrival\n• Know your blood type and allergies\n• Keep emergency medical contacts handy';
        }
        
        // Personal safety general
        if (lowerMsg.includes('personal') || lowerMsg.includes('protect') || lowerMsg.includes('secure')) {
            return '🛡️ PERSONAL SAFETY:\n\n• Keep copies of passport and important documents\n• Don\'t display expensive jewelry or electronics\n• Use hotel safes for valuables\n• Be aware of your surroundings\n• Trust your instincts\n• Stay in well-lit, populated areas\n• Avoid isolated places\n• Keep emergency contacts saved\n• Share your itinerary with someone\n• Use verified services from Gulio app';
        }
        
        // Accommodation safety
        if (lowerMsg.includes('hotel') || lowerMsg.includes('accommodation') || lowerMsg.includes('guesthouse') || lowerMsg.includes('hostel')) {
            return '🏨 ACCOMMODATION SAFETY:\n\n• Choose accommodations in safe, well-lit areas\n• Use hotel safes for valuables\n• Lock doors and windows\n• Don\'t open door to strangers\n• Verify identity of hotel staff\n• Keep emergency numbers by the phone\n• Know fire exits and emergency procedures\n• Don\'t leave valuables in plain sight\n• Use door chains when available\n• Report suspicious activity to management';
        }
        
        // Tourist areas
        if (lowerMsg.includes('tourist') || lowerMsg.includes('attraction') || lowerMsg.includes('visit') || lowerMsg.includes('sightseeing')) {
            return '🎯 TOURIST AREA SAFETY:\n\n• Be extra vigilant at tourist attractions (pickpocket hotspots)\n• Keep bags in front, not behind\n• Don\'t accept unsolicited help from strangers\n• Use official tour guides\n• Be cautious of "friendly" locals offering tours\n• Keep valuables secure and hidden\n• Stay with groups when possible\n• Use verified City Buddies for guided tours\n• Research areas before visiting\n• Trust official information sources';
        }
        
        // Technology and devices
        if (lowerMsg.includes('phone') || lowerMsg.includes('laptop') || lowerMsg.includes('camera') || lowerMsg.includes('device') || lowerMsg.includes('theft')) {
            return '📱 TECHNOLOGY SAFETY:\n\n• Don\'t display expensive phones/cameras in public\n• Use phone cases and screen protectors\n• Keep devices secure in crowded areas\n• Use tracking apps (Find My iPhone, etc.)\n• Back up photos and data regularly\n• Be cautious when using public WiFi\n• Don\'t leave devices unattended\n• Use hotel safes for laptops/tablets\n• Report stolen devices to police immediately\n• Keep device serial numbers recorded';
        }
        
        // General comprehensive safety response
        return '🛡️ COMPREHENSIVE SAFETY TIPS FOR ACCRA:\n\n📞 EMERGENCIES:\n• Police: 191 | Ambulance: 193 | Fire: 192\n• Tourist Police: +233 302 664 698\n\n✅ GENERAL SAFETY:\n• Always meet vendors in public places\n• Verify identity before transactions\n• Use verified services from Gulio app\n• Trust your instincts\n• Keep emergency numbers saved\n• Avoid walking alone at night\n• Use Uber/Bolt for safe transportation\n• Don\'t display valuables in public\n• Keep copies of important documents\n• Stay aware of your surroundings\n\n💰 MONEY:\n• Use ATMs in banks/malls only\n• Don\'t carry large cash amounts\n• Use mobile money when possible\n• Cover PIN when using ATMs\n\n🌙 NIGHT:\n• Avoid isolated areas\n• Use well-lit streets\n• Take taxis instead of walking\n• Stay in groups\n\n🍽️ FOOD/HEALTH:\n• Drink bottled/filtered water\n• Eat at busy restaurants\n• Wash hands frequently\n• Use mosquito repellent\n\nAsk me about specific safety topics: scams, transportation, money, beaches, health, or night safety!';
    }

    // Get location response
    getLocationResponse(message) {
        if (message.includes('barber') || message.includes('hair')) {
            return 'I can help you find verified barbers nearby. What area are you in?';
        }
        if (message.includes('food') || message.includes('restaurant')) {
            return 'Great! I can help you find food vendors. Are you looking for local Ghanaian food or something specific?';
        }
        if (message.includes('phone') || message.includes('repair')) {
            return 'For phone repairs, I recommend verified vendors like FastFix Mobiles. They have good reviews and fair pricing.';
        }
        return 'I can help you find services near you. What type of service are you looking for?';
    }


    // Get emergency response
    getEmergencyResponse() {
        return 'If this is an emergency, call 191 for police, 193 for ambulance, or 192 for fire service. I can also connect you with a City Buddy for immediate help.';
    }

    // Get default response
    getDefaultResponse(message) {
        const responses = [
            'I understand you need help. You can ask me about cultural tips, safety advice, finding services, or connecting with local buddies.',
            'I\'m here to help you navigate Accra. You can ask about culture, safety, or services.',
            'How can I assist you today? I can help with cultural tips, finding services, safety advice, or connecting you with locals.'
        ];
        return responses[Math.floor(Math.random() * responses.length)];
    }

    // Get cultural responses
    getCulturalResponses() {
        return {
            'greeting': 'In Ghana, always greet with "Akwaaba" and shake hands. It\'s polite to ask "How are you?" and wait for a response.',
            'time': 'Ghanaian time is flexible - people often arrive 15-30 minutes after the agreed time. This is normal and expected.',
            'dress': 'Dress modestly, especially when visiting religious sites. Avoid revealing clothing in public.',
            'food': 'Try jollof rice, banku with fish, and fresh fruits. Always wash your hands before eating.',
            'language': 'English is widely spoken, but learning basic Twi phrases is appreciated.'
        };
    }

    // Get safety responses
    getSafetyResponses() {
        return {
            'general': '🛡️ COMPREHENSIVE SAFETY TIPS:\n\n• Always meet vendors in public places\n• Verify identity before transactions\n• Keep emergency numbers: Police (191), Ambulance (193), Fire (192)\n• Trust your instincts\n• Use verified services from Gulio app\n• Avoid walking alone at night\n• Don\'t display valuables in public\n• Use Uber/Bolt for safe transportation\n• Keep copies of important documents\n• Stay aware of your surroundings',
            'scam': '⚠️ AVOIDING SCAMS:\n\n• Be wary of unsolicited offers, especially money-related\n• Never pay upfront without verification\n• Verify vendor identity before transactions\n• Meet in public places\n• Don\'t share banking details\n• Be cautious of "too good to be true" deals\n• Use verified vendors from Gulio app\n• Trust your instincts',
            'emergency': '🚨 EMERGENCY CONTACTS:\n\n• Police: 191\n• Ambulance: 193\n• Fire Service: 192\n• Tourist Police: +233 302 664 698\n\nSave these numbers! For immediate help, call 191 or contact a City Buddy.',
            'night': '🌙 NIGHT SAFETY:\n\n• Avoid walking alone at night\n• Use well-lit, busy streets\n• Take Uber or Bolt instead of walking\n• Keep valuables hidden\n• Stay in groups when possible\n• Avoid isolated areas\n• Inform someone of your whereabouts\n• Have emergency numbers ready',
            'transport': '🚗 TRANSPORTATION SAFETY:\n\n• Use Uber or Bolt (safest, tracked rides)\n• Negotiate taxi fares BEFORE getting in\n• Share ride details with someone\n• Avoid unmarked taxis at night\n• Keep valuables secure in tro-tros\n• Don\'t display expensive items\n• Trust your instincts about drivers',
            'money': '💰 MONEY SAFETY:\n\n• Don\'t carry large cash amounts\n• Use ATMs in banks/malls only\n• Cover your PIN\n• Use mobile money when possible\n• Keep cash separate from wallet\n• Don\'t display money in public\n• Use hotel safes for valuables',
            'beach': '🏖️ BEACH SAFETY:\n\n• Swim only at designated beaches with lifeguards\n• Be aware of strong currents\n• Don\'t swim alone\n• Avoid swimming after dark\n• Don\'t leave valuables unattended\n• Use waterproof pouches\n• Stay hydrated and use sunscreen',
            'food': '🍽️ FOOD & WATER SAFETY:\n\n• Drink only bottled/filtered water\n• Avoid ice unless from trusted sources\n• Eat at busy restaurants\n• Wash hands before eating\n• Be cautious with street food\n• Peel fruits yourself\n• Use verified food vendors from Gulio app',
            'health': '🏥 HEALTH & MEDICAL:\n\n• Carry travel insurance\n• Know nearest hospital location\n• Major hospitals: Korle Bu, 37 Military, Nyaho\n• Bring mosquito repellent\n• Stay hydrated\n• Use sunscreen\n• Get travel vaccinations\n• Keep emergency medical contacts'
        };
    }

    // Get find nearby responses
    getFindNearbyResponses() {
        return {
            'barber': 'I can help you find verified barbers nearby. What area are you in?',
            'food': 'I can help you find food vendors. Are you looking for local Ghanaian food or something specific?',
            'phone': 'For phone repairs, I recommend verified vendors like FastFix Mobiles. They have good reviews and fair pricing.',
            'general': 'I can help you find services near you. What type of service are you looking for?'
        };
    }


    // Get conversation history
    getConversationHistory() {
        return this.conversationHistory;
    }

    // Clear conversation history
    clearHistory() {
        this.conversationHistory = [];
    }

    // Get quick responses for common questions
    getQuickResponses() {
        return [
            'How do I greet people in Ghana?',
            'What are comprehensive safety tips for Accra?',
            'How do I avoid scams in Accra?',
            'Is it safe to walk at night?',
            'What are emergency numbers?',
            'How do I stay safe using taxis?',
            'What about money and ATM safety?',
            'Is the beach safe to swim?',
            'What about food and water safety?',
            'Where can I find good food?',
            'How do I get around the city?',
            'What should I know about Ghanaian culture?',
            'Where can I get my phone fixed?',
            'How do I use public transportation?',
            'What about health and medical safety?',
            'How do I stay safe at tourist attractions?'
        ];
    }

    // Get preset information
    getPreset(presetId) {
        return this.presets[presetId];
    }

    // Get all presets
    getAllPresets() {
        return this.presets;
    }

    // Set context
    setContext(context) {
        this.currentContext = context;
    }

    // Get current context
    getContext() {
        return this.currentContext;
    }
}

// Initialize chatbot service
window.chatbotService = new ChatbotService();

// Utility functions
function formatMessage(message, type) {
    const timestamp = new Date().toLocaleTimeString();
    return {
        message,
        type,
        timestamp
    };
}

function getMessageIcon(type) {
    return type === 'user' ? 'fas fa-user' : 'fas fa-robot';
}

function getMessageClass(type) {
    return type === 'user' ? 'user' : 'bot';
}

// Export for use in other modules
if (typeof module !== 'undefined' && module.exports) {
    module.exports = ChatbotService;
}

