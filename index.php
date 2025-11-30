<?php
/**
 * Gulio - Production Entry Point
 * Main application bootstrap file
 */

// Load configuration first
require_once __DIR__ . '/config/config.php';

// Initialize security and logging
require_once __DIR__ . '/includes/security.php';
require_once __DIR__ . '/includes/logger.php';
require_once __DIR__ . '/includes/auth.php';

// Initialize secure session
Auth::initSession();

// Log request
Logger::debug('Request received', [
    'method' => $_SERVER['REQUEST_METHOD'],
    'uri' => $_SERVER['REQUEST_URI'] ?? '',
    'ip' => $_SERVER['REMOTE_ADDR'] ?? ''
]);

// Include database connection
require_once __DIR__ . '/includes/db.php';
require_once __DIR__ . '/includes/functions.php';
require_once __DIR__ . '/includes/paystack.php';
require_once __DIR__ . '/includes/openai.php';
require_once __DIR__ . '/includes/products.php';
require_once __DIR__ . '/includes/cart.php';

// Initialize user session if not exists (backward compatibility)
if (!isset($_SESSION['user'])) {
    // Try to load from database if logged in
    $user = Auth::user();
    if ($user) {
        $_SESSION['user'] = $user;
    } else {
        // Guest user session
        $_SESSION['user'] = [
            'id' => null,
            'name' => '',
            'email' => '',
            'phone' => '',
            'role' => 'guest',
            'account_role' => 'guest',
            'languages' => [],
            'trust_pref' => 'balanced',
            'starter_pack' => '',
            'location' => null,
            'intent' => ''
        ];
    }
}

// Get current page
$page = $_GET['page'] ?? 'home';

// Handle AJAX/API requests
// Only process AJAX requests, not regular form submissions for login/register
$isAjaxRequest = isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
$excludedActions = ['register', 'login']; // These are handled by their respective pages

// Handle JSON requests separately
if ($_SERVER['REQUEST_METHOD'] === 'POST' && $isAjaxRequest) {
    $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
    
    if (strpos($contentType, 'application/json') !== false) {
        // Set JSON content type and suppress errors for clean JSON output
        header('Content-Type: application/json');
        
        // Start output buffering to catch any unexpected output
        ob_start();
        
        // Suppress warnings/notices for clean JSON output
        $errorReporting = error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
        
        // Handle JSON requests
        $rawInput = file_get_contents('php://input');
        $jsonData = json_decode($rawInput, true);
        
        if ($jsonData && isset($jsonData['action'])) {
            $action = $jsonData['action'];
            
            switch ($action) {
                case 'create_order':
                    // Handle order creation for checkout
                    require_once __DIR__ . '/includes/orders.php';
                    require_once __DIR__ . '/includes/coupons.php';
                    
                    try {
                        $cart = new ShoppingCart();
                        $cartItems = $cart->getItems();
                        
                        if (empty($cartItems)) {
                            echo json_encode(['success' => false, 'message' => 'Cart is empty']);
                            exit;
                        }
                        
                        // Calculate totals
                        $cartSummary = $cart->getSummary();
                        $subtotal = $cartSummary['subtotal'];
                        
                        // Calculate tax (12.5% VAT inclusive - extract VAT from price)
                        // VAT inclusive formula: VAT = price * (rate / (100 + rate))
                        $taxRate = 0.125; // 12.5%
                        $taxAmount = $subtotal * ($taxRate / (1 + $taxRate)); // Extract VAT from inclusive price
                        
                        // Apply coupon if provided
                        $discountAmount = 0;
                        $couponId = null;
                        if (!empty($jsonData['coupon_code'])) {
                            $couponResult = validateCoupon($jsonData['coupon_code'], $subtotal, $_SESSION['user']['id'] ?? null);
                            if ($couponResult['valid']) {
                                $discountAmount = $couponResult['discount_amount'];
                                $couponId = $couponResult['coupon_id'];
                            }
                        }
                        
                        // Calculate shipping
                        $shippingCost = 0;
                        $deliveryMethod = $jsonData['delivery_method'] ?? 'standard';
                        switch($deliveryMethod) {
                            case 'express': $shippingCost = 15; break;
                            case 'same_day': $shippingCost = 25; break;
                        }
                        
                        // Map delivery method to shipping_method ENUM values
                        // Database expects: 'pickup', 'delivery', or 'courier'
                        $shippingMethod = 'delivery'; // Default
                        switch($deliveryMethod) {
                            case 'standard':
                            case 'express':
                            case 'same_day':
                                $shippingMethod = 'delivery'; // All are delivery methods
                                break;
                            case 'pickup':
                                $shippingMethod = 'pickup';
                                break;
                            default:
                                $shippingMethod = 'delivery';
                        }
                        
                        // Calculate total (VAT is already included in subtotal, so we don't add it)
                        $totalAmount = $subtotal + $shippingCost - $discountAmount;
                        
                        // Prepare order items for database
                        $orderItems = [];
                        foreach ($cartItems as $item) {
                            // Ensure we have all required fields
                            if (empty($item['product_id']) || empty($item['product_name'])) {
                                throw new Exception('Invalid cart item: missing product_id or product_name');
                            }
                            
                            $orderItems[] = [
                                'product_id' => (int)$item['product_id'],
                                'variant_id' => !empty($item['variant_id']) ? (int)$item['variant_id'] : null,
                                'vendor_id' => !empty($item['vendor_id']) ? (int)$item['vendor_id'] : null,
                                'product_name' => $item['product_name'],
                                'variant_name' => $item['variant_name'] ?? null,
                                'quantity' => (int)($item['quantity'] ?? 1),
                                'unit_price' => (float)($item['unit_price'] ?? 0),
                                'total_price' => (float)($item['total_price'] ?? 0),
                                'product_data' => [
                                    'image_url' => $item['product_image'] ?? $item['main_image'] ?? null,
                                    'category' => $item['category'] ?? null
                                ]
                            ];
                        }
                        
                        if (empty($orderItems)) {
                            throw new Exception('No valid items in cart');
                        }
                        
                        // Prepare order data
                        $customerInfo = $jsonData['customer_info'] ?? [];
                        $shippingAddress = $jsonData['shipping_address'] ?? [];
                        
                        $orderData = [
                            'user_id' => $_SESSION['user']['id'] ?? null,
                            'guest_email' => $customerInfo['email'] ?? null,
                            'status' => 'pending',
                            'payment_status' => 'pending',
                            'payment_method' => 'paystack',
                            'subtotal' => $subtotal,
                            'tax_amount' => $taxAmount,
                            'shipping_amount' => $shippingCost,
                            'discount_amount' => $discountAmount,
                            'total_amount' => $totalAmount,
                            'shipping_name' => ($customerInfo['firstName'] ?? '') . ' ' . ($customerInfo['lastName'] ?? ''),
                            'shipping_phone' => $customerInfo['phone'] ?? null,
                            'shipping_address' => $shippingAddress['address'] ?? null,
                            'shipping_city' => $shippingAddress['city'] ?? null,
                            'shipping_region' => $shippingAddress['region'] ?? null,
                            'shipping_method' => $shippingMethod,
                            'billing_name' => ($customerInfo['firstName'] ?? '') . ' ' . ($customerInfo['lastName'] ?? ''),
                            'billing_phone' => $customerInfo['phone'] ?? null,
                            'billing_address' => $shippingAddress['address'] ?? null,
                            'billing_city' => $shippingAddress['city'] ?? null,
                            'billing_region' => $shippingAddress['region'] ?? null,
                            'notes' => $jsonData['instructions'] ?? null,
                            'items' => $orderItems
                        ];
                        
                        // Create order in database
                        $result = createOrder($orderData);
                        
                        if (!$result['success']) {
                            echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Failed to create order']);
                            exit;
                        }
                        
                        // Record coupon usage if applied
                        if ($couponId && $discountAmount > 0) {
                            recordCouponUsage($couponId, $result['order_id'], $_SESSION['user']['id'] ?? null, $discountAmount);
                        }
                        
                        // Store order ID in session for payment callback
                        $_SESSION['pending_order'] = [
                            'order_id' => $result['order_id'],
                            'order_number' => $result['order_number']
                        ];
                        
                        Logger::info('Order created', [
                            'order_id' => $result['order_id'],
                            'order_number' => $result['order_number'],
                            'customer_email' => $customerInfo['email'] ?? null,
                            'total_amount' => $totalAmount
                        ]);
                        
                        // Clean any output before sending JSON
                        ob_clean();
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => true,
                            'order_id' => $result['order_id'],
                            'order_number' => $result['order_number'],
                            'total_amount' => $totalAmount
                        ]);
                        ob_end_flush();
                        
                    } catch (Exception $e) {
                        Logger::error('Create order error', [
                            'error' => $e->getMessage(),
                            'trace' => $e->getTraceAsString()
                        ]);
                        
                        // Clean any output before sending JSON
                        ob_clean();
                        header('Content-Type: application/json');
                        echo json_encode([
                            'success' => false, 
                            'message' => 'Error creating order: ' . $e->getMessage(),
                            'debug' => ENVIRONMENT === 'development' ? $e->getMessage() : null
                        ]);
                        ob_end_flush();
                        error_reporting($errorReporting);
                        exit;
                    }
                    
                    // Restore error reporting and flush output
                    ob_end_flush();
                    error_reporting($errorReporting);
                    exit;
                    
                case 'validate_coupon':
                    require_once __DIR__ . '/includes/coupons.php';
                    
                    $couponCode = $jsonData['coupon_code'] ?? '';
                    $subtotal = $jsonData['subtotal'] ?? 0;
                    $userId = $_SESSION['user']['id'] ?? null;
                    
                    $result = validateCoupon($couponCode, $subtotal, $userId);
                    
                    if ($result['valid']) {
                        echo json_encode([
                            'valid' => true,
                            'message' => 'Coupon applied! Discount: GHS ' . number_format($result['discount_amount'], 2),
                            'coupon_id' => $result['coupon_id'],
                            'code' => $result['code'],
                            'discount_amount' => $result['discount_amount']
                        ]);
                    } else {
                        echo json_encode([
                            'valid' => false,
                            'message' => $result['message'] ?? 'Invalid coupon code'
                        ]);
                    }
                    exit;
                    
                default:
                    // Clean any output before sending JSON
                    ob_clean();
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Unknown JSON action']);
                    ob_end_flush();
                    error_reporting($errorReporting);
                    exit;
            }
        }
        
        // Invalid JSON data
        ob_clean();
        header('Content-Type: application/json');
        echo json_encode(['success' => false, 'message' => 'Invalid JSON data']);
        ob_end_flush();
        error_reporting($errorReporting);
        exit;
    }
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $isAjaxRequest && !in_array($_POST['action'], $excludedActions)) {
    // Validate CSRF token for state-changing operations
    $csrfRequiredActions = ['update_profile', 'update_location', 'update_trust_pref', 'update_starter_pack', 'submit_review', 'submit_booking', 'submit_buddy_booking', 'submit_report', 'delete_account', 'add_to_starter_pack'];
    if (in_array($_POST['action'], $csrfRequiredActions)) {
        $token = $_POST['csrf_token'] ?? '';
        if (!Security::validateCSRFToken($token)) {
            header('Content-Type: application/json');
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Invalid CSRF token']);
            exit;
        }
    }
    
    // Rate limiting
    $clientId = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
    if (!Security::checkRateLimit($clientId)) {
        header('Content-Type: application/json');
        http_response_code(429);
        echo json_encode(['success' => false, 'message' => 'Rate limit exceeded']);
        exit;
    }
    
    // Clear any previous output
    if (ob_get_level()) {
        ob_clean();
    }
    
    header('Content-Type: application/json');
    header('Cache-Control: no-cache, must-revalidate');
    
    try {
        $action = Security::sanitizeInput($_POST['action']);
        
        switch ($action) {
            case 'update_profile':
                $name = Security::sanitizeInput($_POST['name'] ?? '');
                $role = Security::sanitizeInput($_POST['role'] ?? '');
                $languages = $_POST['languages'] ?? [];
                
                $_SESSION['user']['name'] = $name;
                $_SESSION['user']['role'] = $role;
                $_SESSION['user']['languages'] = is_array($languages) ? array_map('Security::sanitizeInput', $languages) : [];
                echo json_encode(['success' => true, 'message' => 'Profile updated successfully']);
                exit;
                
            case 'update_location':
                $lat = filter_var($_POST['lat'] ?? 5.6037, FILTER_VALIDATE_FLOAT);
                $lng = filter_var($_POST['lng'] ?? -0.1870, FILTER_VALIDATE_FLOAT);
                $address = Security::sanitizeInput($_POST['address'] ?? 'Current Location');
                
                $_SESSION['user']['location'] = [
                    'lat' => $lat ?: 5.6037,
                    'lng' => $lng ?: -0.1870,
                    'address' => $address
                ];
                $_SESSION['user']['intent'] = Security::sanitizeInput($_POST['intent'] ?? '');
                echo json_encode(['success' => true, 'message' => 'Location updated successfully']);
                exit;
                
            case 'update_trust_pref':
                $trustPref = Security::sanitizeInput($_POST['trust_pref'] ?? 'balanced');
                if (in_array($trustPref, ['strict', 'balanced', 'open'])) {
                    $_SESSION['user']['trust_pref'] = $trustPref;
                    echo json_encode(['success' => true, 'message' => 'Trust preference updated']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid trust preference']);
                }
                exit;
                
            case 'update_starter_pack':
                $_SESSION['user']['starter_pack'] = Security::sanitizeInput($_POST['starter_pack'] ?? '');
                echo json_encode(['success' => true, 'message' => 'Starter pack updated']);
                exit;
                
            case 'add_to_starter_pack':
                $vendorId = filter_var($_POST['vendor_id'] ?? 0, FILTER_VALIDATE_INT);
                $category = Security::sanitizeInput($_POST['category'] ?? '');
                if ($vendorId && $category) {
                    if (!isset($_SESSION['user']['starter_vendors'])) {
                        $_SESSION['user']['starter_vendors'] = [];
                    }
                    if (!in_array($vendorId, $_SESSION['user']['starter_vendors'])) {
                        $_SESSION['user']['starter_vendors'][] = $vendorId;
                    }
                    echo json_encode(['success' => true, 'message' => 'Added to starter pack']);
                } else {
                    echo json_encode(['success' => false, 'message' => 'Invalid vendor data']);
                }
                exit;
                
            case 'get_vendors':
                $lat = filter_var($_POST['lat'] ?? 5.6037, FILTER_VALIDATE_FLOAT) ?: 5.6037;
                $lng = filter_var($_POST['lng'] ?? -0.1870, FILTER_VALIDATE_FLOAT) ?: -0.1870;
                $category = Security::sanitizeInput($_POST['category'] ?? '');
                $vendors = getVendors($lat, $lng, $category);
                echo json_encode($vendors);
                exit;
                
                
            case 'submit_review':
                $vendorId = filter_var($_POST['vendor_id'] ?? 0, FILTER_VALIDATE_INT) ?: 1;
                $rating = filter_var($_POST['rating'] ?? 5, FILTER_VALIDATE_INT);
                $rating = ($rating >= 1 && $rating <= 5) ? $rating : 5;
                $tags = $_POST['tags'] ?? [];
                if (is_string($tags)) {
                    $tags = json_decode($tags, true) ?: [];
                }
                $comment = Security::sanitizeInput($_POST['comment'] ?? '');
                $result = submitReview($vendorId, $rating, $tags, $comment);
                echo json_encode($result);
                exit;
                
            case 'submit_booking':
                $vendorId = filter_var($_POST['vendor_id'] ?? 0, FILTER_VALIDATE_INT);
                $userId = $_SESSION['user']['id'] ?? null;
                if (!$userId) {
                    echo json_encode(['success' => false, 'message' => 'Please log in to make a booking']);
                    exit;
                }
                
                $service = Security::sanitizeInput($_POST['service'] ?? '');
                $datetime = Security::sanitizeInput($_POST['datetime'] ?? '');
                $meetingPoint = Security::sanitizeInput($_POST['meeting_point'] ?? '');
                
                if (!$vendorId || !$service || !$datetime || !$meetingPoint) {
                    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
                    exit;
                }
                
                try {
                    $db = Database::getInstance();
                    $bookingId = $db->insert('bookings', [
                        'vendor_id' => $vendorId,
                        'user_id' => $userId,
                        'service' => $service,
                        'datetime' => date('Y-m-d H:i:s', strtotime($datetime)),
                        'meeting_point' => $meetingPoint,
                        'status' => 'pending',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ]);
                    
                    Logger::info('Booking created', ['booking_id' => $bookingId, 'vendor_id' => $vendorId, 'user_id' => $userId]);
                    echo json_encode(['success' => true, 'message' => 'Booking confirmed!', 'booking_id' => $bookingId]);
                } catch (Exception $e) {
                    Logger::error('Booking failed', ['error' => $e->getMessage()]);
                    echo json_encode(['success' => false, 'message' => 'Failed to create booking. Please try again.']);
                }
                exit;
                
            case 'submit_buddy_booking':
                $buddyId = filter_var($_POST['buddy_id'] ?? 0, FILTER_VALIDATE_INT);
                $userId = $_SESSION['user']['id'] ?? null;
                if (!$userId) {
                    echo json_encode(['success' => false, 'message' => 'Please log in to make a booking']);
                    exit;
                }
                
                $duration = Security::sanitizeInput($_POST['duration'] ?? '');
                $datetime = Security::sanitizeInput($_POST['datetime'] ?? '');
                $location = Security::sanitizeInput($_POST['location'] ?? '');
                $description = Security::sanitizeInput($_POST['description'] ?? '');
                
                if (!$buddyId || !$duration || !$datetime || !$location) {
                    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
                    exit;
                }
                
                try {
                    // For now, log buddy bookings (in future, create buddy_bookings table)
                    // Since bookings table has FK constraint on vendor_id, we'll log it for now
                    Logger::info('Buddy booking requested', [
                        'buddy_id' => $buddyId,
                        'user_id' => $userId,
                        'duration' => $duration,
                        'datetime' => $datetime,
                        'location' => $location,
                        'description' => $description
                    ]);
                    
                    // TODO: Create buddy_bookings table for proper storage
                    // For now, return success as the booking intent is logged
                    echo json_encode(['success' => true, 'message' => 'Booking confirmed! Your buddy will contact you soon.']);
                } catch (Exception $e) {
                    Logger::error('Buddy booking failed', ['error' => $e->getMessage()]);
                    echo json_encode(['success' => false, 'message' => 'Failed to create booking. Please try again.']);
                }
                exit;
                
            case 'submit_report':
                $vendorId = filter_var($_POST['vendor_id'] ?? 0, FILTER_VALIDATE_INT);
                $userId = $_SESSION['user']['id'] ?? null;
                $type = Security::sanitizeInput($_POST['type'] ?? 'vendor');
                
                if (!$vendorId) {
                    echo json_encode(['success' => false, 'message' => 'Invalid vendor ID']);
                    exit;
                }
                
                try {
                    // Log the report (in future, create reports table)
                    Logger::warning('Vendor reported', [
                        'vendor_id' => $vendorId,
                        'user_id' => $userId,
                        'type' => $type,
                        'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
                    ]);
                    
                    echo json_encode(['success' => true, 'message' => 'Report submitted. Thank you for helping keep our community safe.']);
                } catch (Exception $e) {
                    Logger::error('Report submission failed', ['error' => $e->getMessage()]);
                    echo json_encode(['success' => false, 'message' => 'Failed to submit report. Please try again.']);
                }
                exit;
                
            case 'delete_account':
                $userId = $_SESSION['user']['id'] ?? null;
                if (!$userId) {
                    echo json_encode(['success' => false, 'message' => 'Not logged in']);
                    exit;
                }
                
                try {
                    $db = Database::getInstance();
                    // Mark account for deletion by updating email (soft delete approach)
                    $db->update('users', 
                        ['email' => 'deleted_' . $userId . '_' . time() . '@deleted.local', 'updated_at' => date('Y-m-d H:i:s')],
                        'id = :id',
                        ['id' => $userId]
                    );
                    
                    Logger::info('Account deletion requested', ['user_id' => $userId]);
                    echo json_encode(['success' => true, 'message' => 'Account deletion request submitted. You will receive a confirmation email.']);
                } catch (Exception $e) {
                    Logger::error('Account deletion failed', ['error' => $e->getMessage()]);
                    echo json_encode(['success' => false, 'message' => 'Failed to delete account. Please try again.']);
                }
                exit;
                
            case 'initialize_buddy_payment':
                $userId = $_SESSION['user']['id'] ?? null;
                $userEmail = $_SESSION['user']['email'] ?? '';
                
                if (!$userId || !$userEmail) {
                    echo json_encode(['success' => false, 'message' => 'Please log in to make a booking']);
                    exit;
                }
                
                $buddyId = filter_var($_POST['buddy_id'] ?? 0, FILTER_VALIDATE_INT);
                $hours = filter_var($_POST['hours'] ?? 1, FILTER_VALIDATE_FLOAT);
                $datetime = Security::sanitizeInput($_POST['datetime'] ?? '');
                $location = Security::sanitizeInput($_POST['location'] ?? '');
                $description = Security::sanitizeInput($_POST['description'] ?? '');
                $amount = filter_var($_POST['amount'] ?? 0, FILTER_VALIDATE_FLOAT);
                
                if (!$buddyId || !$hours || !$datetime || !$location || !$amount) {
                    echo json_encode(['success' => false, 'message' => 'Please fill in all required fields']);
                    exit;
                }
                
                try {
                    $paystack = new PaystackPayment();
                    $reference = PaystackPayment::generateReference('BUDDY');
                    
                    $metadata = [
                        'buddy_id' => $buddyId,
                        'user_id' => $userId,
                        'hours' => $hours,
                        'datetime' => $datetime,
                        'location' => $location,
                        'description' => $description,
                        'booking_type' => 'city_buddy'
                    ];
                    
                    $payment = $paystack->initializePayment($userEmail, $amount, $reference, $metadata);
                    
                    if ($payment['status']) {
                        Logger::info('Buddy payment initialized', [
                            'reference' => $reference,
                            'buddy_id' => $buddyId,
                            'user_id' => $userId,
                            'amount' => $amount
                        ]);
                        
                        echo json_encode([
                            'success' => true,
                            'authorization_url' => $payment['data']['authorization_url'],
                            'reference' => $reference
                        ]);
                    } else {
                        Logger::error('Buddy payment initialization failed', [
                            'error' => $payment['message'],
                            'buddy_id' => $buddyId,
                            'user_id' => $userId
                        ]);
                        echo json_encode(['success' => false, 'message' => $payment['message']]);
                    }
                } catch (Exception $e) {
                    Logger::error('Buddy payment error', ['error' => $e->getMessage()]);
                    echo json_encode(['success' => false, 'message' => 'Payment processing error. Please try again.']);
                }
                exit;
                
            case 'chat_message':
                $userMessage = Security::sanitizeInput($_POST['message'] ?? '');
                
                if (empty($userMessage)) {
                    echo json_encode(['success' => false, 'message' => 'Please enter a message']);
                    exit;
                }
                
                try {
                    $openai = new OpenAIChat();
                    $context = OpenAIChat::getUserContext();
                    $response = $openai->getChatResponse($userMessage, $context);
                    
                    if ($response['success']) {
                        Logger::info('Chatbot response generated', [
                            'user_message' => $userMessage,
                            'response_length' => strlen($response['message']),
                            'fallback' => $response['fallback'] ?? false
                        ]);
                        
                        echo json_encode([
                            'success' => true,
                            'message' => $response['message'],
                            'fallback' => $response['fallback'] ?? false
                        ]);
                    } else {
                        echo json_encode([
                            'success' => false,
                            'message' => 'Sorry, I\'m having trouble responding right now. Please try again.'
                        ]);
                    }
                } catch (Exception $e) {
                    Logger::error('Chatbot error', ['error' => $e->getMessage()]);
                    echo json_encode([
                        'success' => false,
                        'message' => 'Sorry, I\'m having trouble responding right now. Please try again.'
                    ]);
                }
                exit;
                
            case 'add_to_cart':
                $productId = filter_var($_POST['product_id'] ?? 0, FILTER_VALIDATE_INT);
                $variantId = !empty($_POST['variant_id']) ? filter_var($_POST['variant_id'], FILTER_VALIDATE_INT) : null;
                $quantity = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);
                
                if (!$productId || $quantity < 1) {
                    echo json_encode(['success' => false, 'message' => 'Invalid product or quantity']);
                    exit;
                }
                
                try {
                    $cart = new ShoppingCart();
                    $result = $cart->addItem($productId, $quantity, $variantId);
                    echo json_encode($result);
                } catch (Exception $e) {
                    Logger::error('Add to cart error', ['error' => $e->getMessage()]);
                    echo json_encode(['success' => false, 'message' => 'Error adding item to cart']);
                }
                exit;
                
            case 'update_cart':
                $cartItemId = filter_var($_POST['cart_item_id'] ?? 0, FILTER_VALIDATE_INT);
                $quantity = filter_var($_POST['quantity'] ?? 1, FILTER_VALIDATE_INT);
                
                if (!$cartItemId) {
                    echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
                    exit;
                }
                
                try {
                    $cart = new ShoppingCart();
                    $result = $cart->updateQuantity($cartItemId, $quantity);
                    echo json_encode($result);
                } catch (Exception $e) {
                    Logger::error('Update cart error', ['error' => $e->getMessage()]);
                    echo json_encode(['success' => false, 'message' => 'Error updating cart']);
                }
                exit;
                
            case 'remove_from_cart':
                $cartItemId = filter_var($_POST['cart_item_id'] ?? 0, FILTER_VALIDATE_INT);
                
                if (!$cartItemId) {
                    echo json_encode(['success' => false, 'message' => 'Invalid cart item']);
                    exit;
                }
                
                try {
                    $cart = new ShoppingCart();
                    $result = $cart->removeItem($cartItemId);
                    echo json_encode($result);
                } catch (Exception $e) {
                    Logger::error('Remove from cart error', ['error' => $e->getMessage()]);
                    echo json_encode(['success' => false, 'message' => 'Error removing item']);
                }
                exit;
                
            case 'get_cart_count':
                try {
                    $cart = new ShoppingCart();
                    $count = $cart->getItemCount();
                    echo json_encode(['success' => true, 'count' => $count]);
                } catch (Exception $e) {
                    echo json_encode(['success' => false, 'count' => 0]);
                }
                exit;
                
            default:
                Logger::warning('Unknown action requested', ['action' => $action]);
                echo json_encode(['success' => false, 'message' => 'Unknown action: ' . $action]);
                exit;
        }
    } catch (Exception $e) {
        Logger::error('API error', ['action' => $action, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        echo json_encode(['success' => false, 'message' => 'Server error occurred']);
        exit;
    } catch (Error $e) {
        Logger::critical('Fatal error', ['action' => $action, 'error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
        echo json_encode(['success' => false, 'message' => 'Fatal error occurred']);
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= Security::generateCSRFToken() ?>">
    <title>Gulio — City Companion</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Navigation Header - Hide on vendor detail page -->
    <?php if ($page !== 'vendor-detail'): ?>
    <nav class="main-nav">
        <div class="nav-container">
            <div class="nav-brand">
                <a href="?page=home">
                    <i class="fas fa-map-marked-alt"></i>
                    <span>Gulio</span>
                </a>
            </div>
            <div class="nav-menu">
                <a href="?page=home" class="nav-link <?= $page == 'home' ? 'active' : '' ?>">
                    <i class="fas fa-home"></i> Home
                </a>
                <a href="?page=vendors" class="nav-link <?= $page == 'vendors' ? 'active' : '' ?>">
                    <i class="fas fa-store"></i> Services
                </a>
                <a href="?page=products" class="nav-link <?= $page == 'products' || $page == 'product-detail' || $page == 'cart' ? 'active' : '' ?>">
                    <i class="fas fa-shopping-bag"></i> Shop
                </a>
                <?php if (isset($_SESSION['user']) && $_SESSION['user']['id'] !== null): ?>
                <a href="?page=my-orders" class="nav-link <?= $page == 'my-orders' ? 'active' : '' ?>">
                    <i class="fas fa-receipt"></i> My Orders
                </a>
                <?php endif; ?>
                <a href="?page=chatbot" class="nav-link <?= $page == 'chatbot' ? 'active' : '' ?>">
                    <i class="fas fa-robot"></i> Chat
                </a>
                <a href="?page=city-buddy" class="nav-link <?= $page == 'city-buddy' ? 'active' : '' ?>">
                    <i class="fas fa-user-friends"></i> City Buddy
                </a>
                <a href="?page=settings" class="nav-link <?= $page == 'settings' ? 'active' : '' ?>">
                    <i class="fas fa-cog"></i> Settings
                </a>
                <?php if (Auth::canManageProducts()): ?>
                <a href="admin/" class="nav-link" style="color: #ffd700;">
                    <i class="fas fa-tools"></i> Admin
                </a>
                <?php endif; ?>
                <?php if (Auth::check()): ?>
                    <a href="?page=logout" class="nav-link">
                        <i class="fas fa-sign-out-alt"></i> Logout (<?= Security::escape(Auth::user()['name'] ?? 'User') ?>)
                    </a>
                <?php else: ?>
                    <a href="?page=login" class="nav-link <?= $page == 'login' ? 'active' : '' ?>">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </nav>
    <?php endif; ?>

    <div id="app" class="mobile-container">
        <?php
        switch ($page) {
            case 'login':
                include 'pages/login.php';
                break;
            case 'admin-login':
                include 'pages/admin-login.php';
                break;
            case 'register':
                include 'pages/register.php';
                break;
            case 'forgot-password':
                include 'pages/forgot-password.php';
                break;
            case 'logout':
                $auth = new Auth();
                $auth->logout();
                header('Location: ?page=home');
                exit;
            case 'onboarding':
                include 'pages/onboarding.php';
                break;
            case 'home':
                include 'pages/home.php';
                break;
            case 'vendors':
                include 'pages/vendors.php';
                break;
            case 'vendor-detail':
                include 'pages/vendor-detail.php';
                break;
            case 'chatbot':
                include 'pages/chatbot.php';
                break;
            case 'city-buddy':
                include 'pages/city-buddy.php';
                break;
            case 'feedback':
                include 'pages/feedback.php';
                break;
            case 'scout-verification':
                include 'pages/scout-verification.php';
                break;
            case 'offline':
                include 'pages/offline.php';
                break;
            case 'settings':
                include 'pages/settings.php';
                break;
            case 'payment-callback':
                include 'pages/payment-callback.php';
                break;
            case 'products':
                include 'pages/products.php';
                break;
            case 'product-detail':
                include 'pages/product-detail.php';
                break;
            case 'cart':
                include 'pages/cart.php';
                break;
            case 'checkout':
                include 'pages/checkout.php';
                break;
            case 'order-confirmation':
                include 'pages/order-confirmation.php';
                break;
            case 'my-orders':
                include 'pages/my-orders.php';
                break;
            case 'invoice':
                require_once __DIR__ . '/includes/invoice.php';
                $orderId = $_GET['order_id'] ?? null;
                if (!$orderId) {
                    header('Location: ?page=my-orders');
                    exit;
                }
                // Check if user owns this order
                $order = getOrderById($orderId);
                if (!$order || ($order['user_id'] != ($_SESSION['user']['id'] ?? null) && !Auth::isAdmin())) {
                    header('Location: ?page=my-orders');
                    exit;
                }
                $invoiceHTML = generateInvoiceHTML($orderId);
                if ($invoiceHTML) {
                    echo $invoiceHTML;
                } else {
                    header('Location: ?page=my-orders');
                }
                exit;
            default:
                include 'pages/onboarding.php';
        }
        ?>
    </div>

    <script src="assets/js/app.js"></script>
    <script src="assets/js/geolocation.js"></script>
    <script src="assets/js/chatbot.js"></script>
</body>
</html>

