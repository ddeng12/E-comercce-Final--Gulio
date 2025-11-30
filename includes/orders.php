<?php
/**
 * Order Management Functions
 * Handles order creation, retrieval, and management
 */

require_once __DIR__ . '/database.php';

/**
 * Create a new order in the database
 */
function createOrder($orderData) {
    $db = Database::getInstance();
    
    try {
        // Generate unique order number
        $orderNumber = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -6));
        
        // Start transaction
        $db->query('START TRANSACTION');
        
        // Insert order
        $orderSql = "
            INSERT INTO orders (
                order_number, user_id, guest_email, status, payment_status, payment_method, payment_reference,
                subtotal, tax_amount, shipping_amount, discount_amount, total_amount,
                shipping_name, shipping_phone, shipping_address, shipping_city, shipping_region, shipping_method,
                billing_name, billing_phone, billing_address, billing_city, billing_region,
                notes, created_at
            ) VALUES (
                :order_number, :user_id, :guest_email, :status, :payment_status, :payment_method, :payment_reference,
                :subtotal, :tax_amount, :shipping_amount, :discount_amount, :total_amount,
                :shipping_name, :shipping_phone, :shipping_address, :shipping_city, :shipping_region, :shipping_method,
                :billing_name, :billing_phone, :billing_address, :billing_city, :billing_region,
                :notes, NOW()
            )
        ";
        
        $orderParams = [
            'order_number' => $orderNumber,
            'user_id' => $orderData['user_id'] ?? null,
            'guest_email' => $orderData['guest_email'] ?? null,
            'status' => $orderData['status'] ?? 'pending',
            'payment_status' => $orderData['payment_status'] ?? 'pending',
            'payment_method' => $orderData['payment_method'] ?? 'paystack',
            'payment_reference' => $orderData['payment_reference'] ?? null,
            'subtotal' => $orderData['subtotal'] ?? 0,
            'tax_amount' => $orderData['tax_amount'] ?? 0,
            'shipping_amount' => $orderData['shipping_amount'] ?? 0,
            'discount_amount' => $orderData['discount_amount'] ?? 0,
            'total_amount' => $orderData['total_amount'] ?? 0,
            'shipping_name' => $orderData['shipping_name'] ?? null,
            'shipping_phone' => $orderData['shipping_phone'] ?? null,
            'shipping_address' => $orderData['shipping_address'] ?? null,
            'shipping_city' => $orderData['shipping_city'] ?? null,
            'shipping_region' => $orderData['shipping_region'] ?? null,
            'shipping_method' => $orderData['shipping_method'] ?? 'delivery',
            'billing_name' => $orderData['billing_name'] ?? null,
            'billing_phone' => $orderData['billing_phone'] ?? null,
            'billing_address' => $orderData['billing_address'] ?? null,
            'billing_city' => $orderData['billing_city'] ?? null,
            'billing_region' => $orderData['billing_region'] ?? null,
            'notes' => $orderData['notes'] ?? null
        ];
        
        $db->query($orderSql, $orderParams);
        $orderId = $db->getConnection()->lastInsertId();
        
        // Insert order items
        if (!empty($orderData['items']) && $orderId) {
            foreach ($orderData['items'] as $item) {
                // Validate required fields
                if (empty($item['product_id']) || empty($item['product_name'])) {
                    throw new Exception('Invalid order item: missing product_id or product_name');
                }
                
                // Get vendor_id from product if not in item
                $vendorId = $item['vendor_id'] ?? null;
                if (!$vendorId && !empty($item['product_id'])) {
                    try {
                        $vendorSql = "SELECT vendor_id FROM products WHERE id = :product_id";
                        $vendorResult = $db->fetchOne($vendorSql, ['product_id' => $item['product_id']]);
                        $vendorId = $vendorResult['vendor_id'] ?? null;
                    } catch (Exception $e) {
                        Logger::warning('Could not fetch vendor_id for product', [
                            'product_id' => $item['product_id'],
                            'error' => $e->getMessage()
                        ]);
                    }
                }
                
                // If still no vendor_id, try to get a default vendor or create one
                if (!$vendorId) {
                    try {
                        $defaultVendor = $db->fetchOne("SELECT id FROM vendors LIMIT 1");
                        $vendorId = $defaultVendor['id'] ?? null;
                    } catch (Exception $e) {
                        // If no vendors exist, we need to handle this
                        throw new Exception('No vendor found for product and no default vendor available. Please ensure products have vendors assigned.');
                    }
                }
                
                if (!$vendorId) {
                    throw new Exception('Cannot create order: vendor_id is required for all products');
                }
                
                $itemSql = "
                    INSERT INTO order_items (
                        order_id, product_id, variant_id, vendor_id,
                        product_name, variant_name, quantity, unit_price, total_price, product_data
                    ) VALUES (
                        :order_id, :product_id, :variant_id, :vendor_id,
                        :product_name, :variant_name, :quantity, :unit_price, :total_price, :product_data
                    )
                ";
                
                $itemParams = [
                    'order_id' => $orderId,
                    'product_id' => $item['product_id'],
                    'variant_id' => !empty($item['variant_id']) ? $item['variant_id'] : null,
                    'vendor_id' => $vendorId,
                    'product_name' => $item['product_name'],
                    'variant_name' => !empty($item['variant_name']) ? $item['variant_name'] : null,
                    'quantity' => (int)($item['quantity'] ?? 1),
                    'unit_price' => (float)($item['unit_price'] ?? 0),
                    'total_price' => (float)($item['total_price'] ?? 0),
                    'product_data' => json_encode($item['product_data'] ?? [])
                ];
                
                $db->query($itemSql, $itemParams);
            }
        }
        
        // Commit transaction
        $db->query('COMMIT');
        
        return [
            'success' => true,
            'order_id' => $orderId,
            'order_number' => $orderNumber
        ];
        
    } catch (Exception $e) {
        // Rollback on error
        try {
            $db->query('ROLLBACK');
        } catch (Exception $rollbackError) {
            // Ignore rollback errors
        }
        Logger::error('Order creation failed', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        return [
            'success' => false,
            'message' => 'Failed to create order: ' . $e->getMessage(),
            'debug' => $e->getMessage()
        ];
    }
}

/**
 * Update order status
 */
function updateOrderStatus($orderId, $status, $paymentStatus = null) {
    $db = Database::getInstance();
    
    try {
        $sql = "UPDATE orders SET status = :status";
        $params = ['status' => $status, 'order_id' => $orderId];
        
        if ($paymentStatus !== null) {
            $sql .= ", payment_status = :payment_status";
            $params['payment_status'] = $paymentStatus;
        }
        
        if ($status === 'shipped') {
            $sql .= ", shipped_at = NOW()";
        } elseif ($status === 'delivered') {
            $sql .= ", delivered_at = NOW()";
        }
        
        $sql .= " WHERE id = :order_id";
        
        $db->query($sql, $params);
        
        return ['success' => true];
    } catch (Exception $e) {
        Logger::error('Order status update failed', ['error' => $e->getMessage()]);
        return ['success' => false, 'message' => $e->getMessage()];
    }
}

/**
 * Get order by ID
 */
function getOrderById($orderId) {
    $db = Database::getInstance();
    
    $sql = "
        SELECT o.*, u.name as user_name, u.email as user_email
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE o.id = :order_id
    ";
    
    $order = $db->fetchOne($sql, ['order_id' => $orderId]);
    
    if ($order) {
        // Get order items with product image
        $itemsSql = "
            SELECT oi.*, 
                   p.image_url as product_image_url,
                   p.images as product_images
            FROM order_items oi
            LEFT JOIN products p ON oi.product_id = p.id
            WHERE oi.order_id = :order_id
        ";
        
        $items = $db->fetchAll($itemsSql, ['order_id' => $orderId]);
        
        // Process items to ensure image_url is available
        foreach ($items as $key => $item) {
            // Priority: 1. product_image_url from products table, 2. product_data JSON, 3. first image from images array
            $imageUrl = $item['product_image_url'] ?? null;
            
            // If no image_url, try product_data JSON
            if (empty($imageUrl)) {
                $productData = json_decode($item['product_data'] ?? '{}', true);
                $imageUrl = $productData['image_url'] ?? null;
            }
            
            // If still no image, try images array
            if (empty($imageUrl) && !empty($item['product_images'])) {
                $images = json_decode($item['product_images'], true);
                if (!empty($images) && is_array($images)) {
                    $imageUrl = $images[0] ?? null;
                }
            }
            
            $items[$key]['image_url'] = $imageUrl;
        }
        
        $order['items'] = $items ?: []; // Ensure items is always an array
    } else {
        // Return null or empty structure
        return null;
    }
    
    return $order;
}

/**
 * Get orders by user ID
 */
function getOrdersByUserId($userId, $limit = 50, $offset = 0) {
    $db = Database::getInstance();
    
    $sql = "
        SELECT o.*, 
               (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
        FROM orders o
        WHERE o.user_id = :user_id
        ORDER BY o.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    
    return $db->fetchAll($sql, [
        'user_id' => $userId,
        'limit' => $limit,
        'offset' => $offset
    ]);
}

/**
 * Get all orders (for admin)
 */
function getAllOrders($filters = [], $limit = 50, $offset = 0) {
    $db = Database::getInstance();
    
    $where = ['1=1'];
    $params = [];
    
    if (!empty($filters['status'])) {
        $where[] = 'o.status = :status';
        $params['status'] = $filters['status'];
    }
    
    if (!empty($filters['payment_status'])) {
        $where[] = 'o.payment_status = :payment_status';
        $params['payment_status'] = $filters['payment_status'];
    }
    
    if (!empty($filters['search'])) {
        $where[] = '(o.order_number LIKE :search OR o.guest_email LIKE :search OR u.name LIKE :search)';
        $params['search'] = '%' . $filters['search'] . '%';
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "
        SELECT o.*, u.name as user_name, u.email as user_email,
               (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as item_count
        FROM orders o
        LEFT JOIN users u ON o.user_id = u.id
        WHERE {$whereClause}
        ORDER BY o.created_at DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $params['limit'] = $limit;
    $params['offset'] = $offset;
    
    return $db->fetchAll($sql, $params);
}

/**
 * Get order count (for admin)
 */
function getOrderCount($filters = []) {
    $db = Database::getInstance();
    
    $where = ['1=1'];
    $params = [];
    
    if (!empty($filters['status'])) {
        $where[] = 'status = :status';
        $params['status'] = $filters['status'];
    }
    
    if (!empty($filters['payment_status'])) {
        $where[] = 'payment_status = :payment_status';
        $params['payment_status'] = $filters['payment_status'];
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "SELECT COUNT(*) as count FROM orders WHERE {$whereClause}";
    
    $result = $db->fetchOne($sql, $params);
    return (int)($result['count'] ?? 0);
}

