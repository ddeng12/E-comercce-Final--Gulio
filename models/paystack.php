<?php
/**
 * Paystack Payment Integration
 * Handles payment processing for Gulio services
 */

class PaystackPayment {
    private $secretKey;
    private $publicKey;
    private $baseUrl;
    
    public function __construct() {
        $this->secretKey = 'sk_test_941fd4b4c2575cea60614998dc0255eff745c2ce';
        $this->publicKey = 'pk_test_39dc2ad96f7ea663a603503d07da30f598323e2f';
        $this->baseUrl = 'https://api.paystack.co';
    }
    
    /**
     * Initialize payment transaction
     */
    public function initializePayment($email, $amount, $reference, $metadata = []) {
        $url = $this->baseUrl . '/transaction/initialize';
        
        $data = [
            'email' => $email,
            'amount' => $amount * 100, // Convert to kobo
            'reference' => $reference,
            'currency' => 'GHS',
            'metadata' => $metadata,
            'callback_url' => $this->getCallbackUrl()
        ];
        
        $response = $this->makeRequest($url, $data);
        return $response;
    }
    
    /**
     * Verify payment transaction
     */
    public function verifyPayment($reference) {
        $url = $this->baseUrl . '/transaction/verify/' . $reference;
        
        $response = $this->makeRequest($url, null, 'GET');
        return $response;
    }
    
    /**
     * Make HTTP request to Paystack API
     */
    private function makeRequest($url, $data = null, $method = 'POST') {
        $ch = curl_init();
        
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->secretKey,
                'Content-Type: application/json',
            ],
        ]);
        
        if ($data && $method === 'POST') {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        }
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return json_decode($response, true);
        } else {
            Logger::error('Paystack API error', [
                'url' => $url,
                'http_code' => $httpCode,
                'response' => $response
            ]);
            return [
                'status' => false,
                'message' => 'Payment service temporarily unavailable'
            ];
        }
    }
    
    /**
     * Generate unique payment reference
     */
    public static function generateReference($prefix = 'GULIO') {
        return $prefix . '_' . time() . '_' . uniqid();
    }
    
    /**
     * Get callback URL for payment verification
     */
    private function getCallbackUrl() {
        $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $path = dirname($_SERVER['REQUEST_URI'] ?? '');
        
        return $protocol . '://' . $host . $path . '?page=payment-callback';
    }
    
    /**
     * Get public key for frontend
     */
    public function getPublicKey() {
        return $this->publicKey;
    }
    
    /**
     * Format amount for display
     */
    public static function formatAmount($amount) {
        return 'GHS ' . number_format($amount, 2);
    }
}
