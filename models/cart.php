<?php
/**
 * Shopping Cart Management
 * Handles cart operations for the e-commerce system
 */

class ShoppingCart {
    private $db;
    private $userId;
    private $sessionId;
    
    public function __construct() {
        $this->db = Database::getInstance();
        $this->userId = $_SESSION['user']['id'] ?? null;
        $this->sessionId = session_id();
    }
    
    /**
     * Add item to cart
     */
    public function addItem($productId, $quantity = 1, $variantId = null) {
        // Validate product exists and is active
        $product = $this->getProduct($productId);
        if (!$product) {
            return ['success' => false, 'message' => 'Product not found'];
        }
        
        if ($product['status'] !== 'active') {
            return ['success' => false, 'message' => 'Product is not available'];
        }
        
        // Check stock
        $availableStock = $variantId ? $this->getVariantStock($variantId) : $product['stock_quantity'];
        if ($availableStock < $quantity) {
            return ['success' => false, 'message' => 'Not enough stock available'];
        }
        
        // Check if item already exists in cart
        $existingItem = $this->getCartItem($productId, $variantId);
        
        if ($existingItem) {
            // Update quantity
            $newQuantity = $existingItem['quantity'] + $quantity;
            if ($availableStock < $newQuantity) {
                return ['success' => false, 'message' => 'Not enough stock available'];
            }
            
            $sql = "UPDATE shopping_cart SET quantity = :quantity, updated_at = NOW() WHERE id = :id";
            $this->db->query($sql, ['quantity' => $newQuantity, 'id' => $existingItem['id']]);
        } else {
            // Add new item
            $sql = "
                INSERT INTO shopping_cart (user_id, session_id, product_id, variant_id, quantity)
                VALUES (:user_id, :session_id, :product_id, :variant_id, :quantity)
            ";
            
            $this->db->query($sql, [
                'user_id' => $this->userId,
                'session_id' => $this->sessionId,
                'product_id' => $productId,
                'variant_id' => $variantId,
                'quantity' => $quantity
            ]);
        }
        
        Logger::info('Item added to cart', [
            'user_id' => $this->userId,
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity' => $quantity
        ]);
        
        return ['success' => true, 'message' => 'Item added to cart'];
    }
    
    /**
     * Update cart item quantity
     */
    public function updateQuantity($cartItemId, $quantity) {
        if ($quantity <= 0) {
            return $this->removeItem($cartItemId);
        }
        
        // Get cart item
        $cartItem = $this->getCartItemById($cartItemId);
        if (!$cartItem) {
            return ['success' => false, 'message' => 'Cart item not found'];
        }
        
        // Check stock
        $availableStock = $cartItem['variant_id'] ? 
            $this->getVariantStock($cartItem['variant_id']) : 
            $cartItem['stock_quantity'];
            
        if ($availableStock < $quantity) {
            return ['success' => false, 'message' => 'Not enough stock available'];
        }
        
        $sql = "UPDATE shopping_cart SET quantity = :quantity, updated_at = NOW() WHERE id = :id";
        $this->db->query($sql, ['quantity' => $quantity, 'id' => $cartItemId]);
        
        return ['success' => true, 'message' => 'Cart updated'];
    }
    
    /**
     * Remove item from cart
     */
    public function removeItem($cartItemId) {
        $sql = "DELETE FROM shopping_cart WHERE id = :id AND (user_id = :user_id OR session_id = :session_id)";
        $this->db->query($sql, [
            'id' => $cartItemId,
            'user_id' => $this->userId,
            'session_id' => $this->sessionId
        ]);
        
        return ['success' => true, 'message' => 'Item removed from cart'];
    }
    
    /**
     * Get cart items
     */
    public function getItems() {
        $where = [];
        $params = [];
        
        if ($this->userId) {
            $where[] = 'c.user_id = :user_id';
            $params['user_id'] = $this->userId;
        } else {
            $where[] = 'c.session_id = :session_id';
            $params['session_id'] = $this->sessionId;
        }
        
        $whereClause = implode(' OR ', $where);
        
        $sql = "
            SELECT 
                c.*,
                p.name as product_name,
                p.price as product_price,
                p.image_url as product_image_url,
                p.images as product_images,
                p.stock_quantity,
                p.status as product_status,
                pv.name as variant_name,
                pv.price as variant_price,
                pv.stock_quantity as variant_stock,
                v.name as vendor_name,
                v.id as vendor_id
            FROM shopping_cart c
            LEFT JOIN products p ON c.product_id = p.id
            LEFT JOIN product_variants pv ON c.variant_id = pv.id
            LEFT JOIN vendors v ON p.vendor_id = v.id
            WHERE ({$whereClause}) AND p.status = 'active'
            ORDER BY c.added_at DESC
        ";
        
        $items = $this->db->fetchAll($sql, $params);
        
        foreach ($items as $key => $item) {
            // Get image URL - prefer image_url, then first image from images array
            $imageUrl = $item['product_image_url'] ?? null;
            if (empty($imageUrl)) {
                $item['product_images'] = json_decode($item['product_images'] ?: '[]', true);
                $imageUrl = !empty($item['product_images']) ? $item['product_images'][0] : null;
            }
            $item['product_image'] = $imageUrl;
            $item['main_image'] = $imageUrl ?: '/assets/images/product-placeholder.jpg';
            
            // Use variant price if available, otherwise product price
            $item['unit_price'] = $item['variant_price'] ?: $item['product_price'];
            $item['total_price'] = $item['unit_price'] * $item['quantity'];
            $item['formatted_unit_price'] = 'GHS ' . number_format($item['unit_price'], 2);
            $item['formatted_total_price'] = 'GHS ' . number_format($item['total_price'], 2);
            
            // Check stock availability
            $availableStock = $item['variant_id'] ? $item['variant_stock'] : $item['stock_quantity'];
            $item['available_stock'] = $availableStock;
            $item['in_stock'] = $availableStock >= $item['quantity'];
            $item['stock_warning'] = $availableStock < $item['quantity'];
            
            // Update the array with processed data
            $items[$key] = $item;
        }
        
        return $items;
    }
    
    /**
     * Get cart summary
     */
    public function getSummary() {
        $items = $this->getItems();
        
        $summary = [
            'item_count' => 0,
            'total_quantity' => 0,
            'subtotal' => 0,
            'tax_amount' => 0,
            'shipping_amount' => 0,
            'total_amount' => 0,
            'has_out_of_stock' => false,
            'vendors' => []
        ];
        
        foreach ($items as $item) {
            if (!$item['in_stock']) {
                $summary['has_out_of_stock'] = true;
                continue; // Skip out of stock items in calculations
            }
            
            $summary['item_count']++;
            $summary['total_quantity'] += $item['quantity'];
            $summary['subtotal'] += $item['total_price'];
            
            // Group by vendor for potential multi-vendor shipping
            if (!isset($summary['vendors'][$item['vendor_id']])) {
                $summary['vendors'][$item['vendor_id']] = [
                    'name' => $item['vendor_name'],
                    'items' => 0,
                    'subtotal' => 0
                ];
            }
            
            $summary['vendors'][$item['vendor_id']]['items']++;
            $summary['vendors'][$item['vendor_id']]['subtotal'] += $item['total_price'];
        }
        
        // Calculate tax (if applicable)
        $summary['tax_amount'] = 0; // Ghana VAT could be added here
        
        // Calculate shipping (free shipping for now)
        $summary['shipping_amount'] = 0; // Free shipping
        
        // Calculate total
        $summary['total_amount'] = $summary['subtotal'] + $summary['tax_amount'] + $summary['shipping_amount'];
        
        // Format amounts
        $summary['formatted_subtotal'] = 'GHS ' . number_format($summary['subtotal'], 2);
        $summary['formatted_tax_amount'] = 'GHS ' . number_format($summary['tax_amount'], 2);
        $summary['formatted_shipping_amount'] = 'GHS ' . number_format($summary['shipping_amount'], 2);
        $summary['formatted_total_amount'] = 'GHS ' . number_format($summary['total_amount'], 2);
        
        return $summary;
    }
    
    /**
     * Clear cart
     */
    public function clear() {
        $where = [];
        $params = [];
        
        if ($this->userId) {
            $where[] = 'user_id = :user_id';
            $params['user_id'] = $this->userId;
        } else {
            $where[] = 'session_id = :session_id';
            $params['session_id'] = $this->sessionId;
        }
        
        $whereClause = implode(' OR ', $where);
        
        $sql = "DELETE FROM shopping_cart WHERE {$whereClause}";
        $this->db->query($sql, $params);
        
        return ['success' => true, 'message' => 'Cart cleared'];
    }
    
    /**
     * Get cart item count
     */
    public function getItemCount() {
        $where = [];
        $params = [];
        
        if ($this->userId) {
            $where[] = 'user_id = :user_id';
            $params['user_id'] = $this->userId;
        } else {
            $where[] = 'session_id = :session_id';
            $params['session_id'] = $this->sessionId;
        }
        
        $whereClause = implode(' OR ', $where);
        
        $sql = "SELECT COALESCE(SUM(quantity), 0) as count FROM shopping_cart WHERE {$whereClause}";
        $result = $this->db->fetchOne($sql, $params);
        
        return (int)$result['count'];
    }
    
    /**
     * Merge guest cart with user cart on login
     */
    public function mergeGuestCart($newUserId) {
        if (!$this->sessionId) return;
        
        // Get guest cart items
        $sql = "SELECT * FROM shopping_cart WHERE session_id = :session_id AND user_id IS NULL";
        $guestItems = $this->db->fetchAll($sql, ['session_id' => $this->sessionId]);
        
        foreach ($guestItems as $guestItem) {
            // Check if user already has this item
            $existingItem = $this->db->fetchOne(
                "SELECT * FROM shopping_cart WHERE user_id = :user_id AND product_id = :product_id AND variant_id = :variant_id",
                [
                    'user_id' => $newUserId,
                    'product_id' => $guestItem['product_id'],
                    'variant_id' => $guestItem['variant_id']
                ]
            );
            
            if ($existingItem) {
                // Update quantity
                $newQuantity = $existingItem['quantity'] + $guestItem['quantity'];
                $this->db->query(
                    "UPDATE shopping_cart SET quantity = :quantity WHERE id = :id",
                    ['quantity' => $newQuantity, 'id' => $existingItem['id']]
                );
            } else {
                // Transfer item to user
                $this->db->query(
                    "UPDATE shopping_cart SET user_id = :user_id WHERE id = :id",
                    ['user_id' => $newUserId, 'id' => $guestItem['id']]
                );
            }
        }
        
        // Remove any remaining guest items
        $this->db->query(
            "DELETE FROM shopping_cart WHERE session_id = :session_id AND user_id IS NULL",
            ['session_id' => $this->sessionId]
        );
    }
    
    // Private helper methods
    
    private function getProduct($productId) {
        $sql = "SELECT * FROM products WHERE id = :id";
        return $this->db->fetchOne($sql, ['id' => $productId]);
    }
    
    private function getVariantStock($variantId) {
        $sql = "SELECT stock_quantity FROM product_variants WHERE id = :id";
        $result = $this->db->fetchOne($sql, ['id' => $variantId]);
        return $result ? $result['stock_quantity'] : 0;
    }
    
    private function getCartItem($productId, $variantId = null) {
        $where = [];
        $params = ['product_id' => $productId];
        
        if ($this->userId) {
            $where[] = 'user_id = :user_id';
            $params['user_id'] = $this->userId;
        } else {
            $where[] = 'session_id = :session_id';
            $params['session_id'] = $this->sessionId;
        }
        
        if ($variantId) {
            $where[] = 'variant_id = :variant_id';
            $params['variant_id'] = $variantId;
        } else {
            $where[] = 'variant_id IS NULL';
        }
        
        $where[] = 'product_id = :product_id';
        
        $whereClause = implode(' AND ', $where);
        
        $sql = "SELECT * FROM shopping_cart WHERE {$whereClause}";
        return $this->db->fetchOne($sql, $params);
    }
    
    private function getCartItemById($cartItemId) {
        $sql = "
            SELECT c.*, p.stock_quantity, pv.stock_quantity as variant_stock
            FROM shopping_cart c
            LEFT JOIN products p ON c.product_id = p.id
            LEFT JOIN product_variants pv ON c.variant_id = pv.id
            WHERE c.id = :id AND (c.user_id = :user_id OR c.session_id = :session_id)
        ";
        
        return $this->db->fetchOne($sql, [
            'id' => $cartItemId,
            'user_id' => $this->userId,
            'session_id' => $this->sessionId
        ]);
    }
}
