<?php
/**
 * OpenAI ChatGPT Integration
 * Handles AI-powered chatbot responses for Gulio
 */

class OpenAIChat {
    private $apiKey;
    private $baseUrl;
    
    public function __construct() {
        $this->apiKey = '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';
        $this->baseUrl = 'https://api.openai.com/v1';
    }
    
    /**
     * Get chatbot response from ChatGPT
     */
    public function getChatResponse($userMessage, $context = []) {
        $systemPrompt = $this->buildSystemPrompt($context);
        
        $data = [
            'model' => 'gpt-3.5-turbo',
            'messages' => [
                [
                    'role' => 'system',
                    'content' => $systemPrompt
                ],
                [
                    'role' => 'user',
                    'content' => $userMessage
                ]
            ],
            'max_tokens' => 500,
            'temperature' => 0.7
        ];
        
        try {
            $response = $this->makeRequest('/chat/completions', $data);
            
            if ($response && isset($response['choices'][0]['message']['content'])) {
                return [
                    'success' => true,
                    'message' => trim($response['choices'][0]['message']['content']),
                    'usage' => $response['usage'] ?? null
                ];
            } else {
                Logger::error('OpenAI API invalid response', ['response' => $response]);
                return $this->getFallbackResponse($userMessage);
            }
            
        } catch (Exception $e) {
            Logger::error('OpenAI API error', ['error' => $e->getMessage()]);
            return $this->getFallbackResponse($userMessage);
        }
    }
    
    /**
     * Build system prompt for Gulio chatbot
     */
    private function buildSystemPrompt($context) {
        $userName = $context['user_name'] ?? 'Friend';
        $userRole = $context['user_role'] ?? 'newcomer';
        $userLanguages = $context['user_languages'] ?? ['English'];
        
        return "You are Gulio's AI assistant, helping newcomers navigate life in Accra, Ghana. 

Your role:
- Help users find local services (barbers, tailors, food, phone repair, etc.)
- Provide cultural tips and local customs advice
- Give safety recommendations for newcomers
- Assist with practical information about living in Accra
- Be friendly, helpful, and culturally sensitive

User context:
- Name: {$userName}
- Role: {$userRole}
- Languages: " . implode(', ', $userLanguages) . "

Guidelines:
- Keep responses concise but helpful (under 300 words)
- Focus on practical, actionable advice
- Include local cultural context when relevant
- Mention Gulio's services (vendors, city buddies) when appropriate
- Be encouraging and welcoming to newcomers
- If asked about specific locations, focus on Accra area
- For emergencies, direct to local emergency numbers

Always be helpful, friendly, and remember you're assisting someone new to Accra who may need extra guidance.";
    }
    
    /**
     * Make HTTP request to OpenAI API
     */
    private function makeRequest($endpoint, $data) {
        $url = $this->baseUrl . $endpoint;
        
        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => json_encode($data),
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json',
            ],
            CURLOPT_TIMEOUT => 30,
            CURLOPT_CONNECTTIMEOUT => 10
        ]);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        if ($error) {
            throw new Exception("cURL error: " . $error);
        }
        
        if ($httpCode !== 200) {
            throw new Exception("HTTP error: " . $httpCode . " - " . $response);
        }
        
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception("JSON decode error: " . json_last_error_msg());
        }
        
        return $decoded;
    }
    
    /**
     * Get fallback response when API fails
     */
    private function getFallbackResponse($userMessage) {
        $fallbackResponses = [
            'general' => "I'm here to help you navigate Accra! While I'm having trouble connecting right now, I can still assist you. Try asking about local services, cultural tips, or safety advice. You can also explore our verified vendors or connect with a City Buddy for personalized help!",
            
            'services' => "Looking for local services? Check out our verified vendors for barbers, tailors, food, and phone repair. All our vendors are community-verified and trusted by locals!",
            
            'safety' => "Safety first in Accra! Always meet vendors in public places, verify their identity, and trust your instincts. Our Scout-verified vendors have been background-checked for your peace of mind.",
            
            'culture' => "Welcome to Accra! Ghanaians are known for their warmth and hospitality. A friendly 'Akwaaba' (welcome) goes a long way. Don't hesitate to ask locals for help - they're usually happy to assist!",
            
            'emergency' => "For emergencies in Ghana: Police (191), Fire Service (192), Ambulance (193). For non-emergency help, you can always contact our support team or connect with a City Buddy."
        ];
        
        // Simple keyword matching for fallback
        $message = strtolower($userMessage);
        if (strpos($message, 'service') !== false || strpos($message, 'vendor') !== false || strpos($message, 'barber') !== false || strpos($message, 'food') !== false) {
            $response = $fallbackResponses['services'];
        } elseif (strpos($message, 'safe') !== false || strpos($message, 'danger') !== false || strpos($message, 'security') !== false) {
            $response = $fallbackResponses['safety'];
        } elseif (strpos($message, 'culture') !== false || strpos($message, 'custom') !== false || strpos($message, 'tradition') !== false) {
            $response = $fallbackResponses['culture'];
        } elseif (strpos($message, 'emergency') !== false || strpos($message, 'help') !== false || strpos($message, 'urgent') !== false) {
            $response = $fallbackResponses['emergency'];
        } else {
            $response = $fallbackResponses['general'];
        }
        
        return [
            'success' => true,
            'message' => $response,
            'fallback' => true
        ];
    }
    
    /**
     * Get conversation context from user session
     */
    public static function getUserContext() {
        $user = $_SESSION['user'] ?? [];
        
        return [
            'user_name' => $user['name'] ?? 'Friend',
            'user_role' => $user['role'] ?? 'newcomer',
            'user_languages' => $user['languages'] ?? ['English'],
            'starter_pack' => $user['starter_pack'] ?? '',
            'location' => $user['location']['address'] ?? 'Accra'
        ];
    }
}
