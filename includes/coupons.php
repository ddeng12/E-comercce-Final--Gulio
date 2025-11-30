<?php
/**
 * Coupon/Discount Management Functions
 */

require_once __DIR__ . '/database.php';

/**
 * Validate and apply coupon code
 */
function validateCoupon($code, $subtotal, $userId = null) {
    $db = Database::getInstance();
    
    try {
        // Get coupon
        $sql = "
            SELECT * FROM coupons 
            WHERE code = :code AND status = 'active'
        ";
        
        $coupon = $db->fetchOne($sql, ['code' => strtoupper(trim($code))]);
        
        if (!$coupon) {
            return [
                'valid' => false,
                'message' => 'Invalid coupon code'
            ];
        }
        
        // Check if expired
        $now = date('Y-m-d H:i:s');
        if ($coupon['valid_until'] && $coupon['valid_until'] < $now) {
            return [
                'valid' => false,
                'message' => 'Coupon has expired'
            ];
        }
        
        if ($coupon['valid_from'] && $coupon['valid_from'] > $now) {
            return [
                'valid' => false,
                'message' => 'Coupon is not yet valid'
            ];
        }
        
        // Check minimum amount
        if ($subtotal < $coupon['minimum_amount']) {
            return [
                'valid' => false,
                'message' => 'Minimum order amount of GHS ' . number_format($coupon['minimum_amount'], 2) . ' required'
            ];
        }
        
        // Check usage limit
        if ($coupon['usage_limit'] && $coupon['used_count'] >= $coupon['usage_limit']) {
            return [
                'valid' => false,
                'message' => 'Coupon usage limit reached'
            ];
        }
        
        // Calculate discount
        $discountAmount = 0;
        
        if ($coupon['discount_type'] === 'percentage') {
            $discountAmount = ($subtotal * $coupon['discount_value']) / 100;
            
            // Apply maximum discount if set
            if ($coupon['maximum_discount'] && $discountAmount > $coupon['maximum_discount']) {
                $discountAmount = $coupon['maximum_discount'];
            }
        } else {
            // Fixed amount
            $discountAmount = $coupon['discount_value'];
            
            // Don't exceed subtotal
            if ($discountAmount > $subtotal) {
                $discountAmount = $subtotal;
            }
        }
        
        return [
            'valid' => true,
            'coupon_id' => $coupon['id'],
            'code' => $coupon['code'],
            'name' => $coupon['name'],
            'discount_amount' => round($discountAmount, 2),
            'discount_type' => $coupon['discount_type'],
            'discount_value' => $coupon['discount_value']
        ];
        
    } catch (Exception $e) {
        Logger::error('Coupon validation failed', ['error' => $e->getMessage()]);
        return [
            'valid' => false,
            'message' => 'Error validating coupon'
        ];
    }
}

/**
 * Record coupon usage
 */
function recordCouponUsage($couponId, $orderId, $userId, $discountAmount) {
    $db = Database::getInstance();
    
    try {
        // Record usage
        $usageSql = "
            INSERT INTO coupon_usage (coupon_id, order_id, user_id, discount_amount)
            VALUES (:coupon_id, :order_id, :user_id, :discount_amount)
        ";
        
        $db->insert($usageSql, [
            'coupon_id' => $couponId,
            'order_id' => $orderId,
            'user_id' => $userId,
            'discount_amount' => $discountAmount
        ]);
        
        // Update coupon used count
        $updateSql = "
            UPDATE coupons 
            SET used_count = used_count + 1
            WHERE id = :coupon_id
        ";
        
        $db->query($updateSql, ['coupon_id' => $couponId]);
        
        return ['success' => true];
    } catch (Exception $e) {
        Logger::error('Coupon usage recording failed', ['error' => $e->getMessage()]);
        return ['success' => false];
    }
}

/**
 * Get all coupons (for admin)
 */
function getAllCoupons() {
    $db = Database::getInstance();
    
    $sql = "
        SELECT c.*, 
               (SELECT COUNT(*) FROM coupon_usage cu WHERE cu.coupon_id = c.id) as actual_usage
        FROM coupons c
        ORDER BY c.created_at DESC
    ";
    
    return $db->fetchAll($sql);
}

/**
 * Create coupon (for admin)
 */
function createCoupon($couponData) {
    $db = Database::getInstance();
    
    try {
        $sql = "
            INSERT INTO coupons (
                code, name, description, discount_type, discount_value,
                minimum_amount, maximum_discount, usage_limit,
                valid_from, valid_until, status, applicable_to
            ) VALUES (
                :code, :name, :description, :discount_type, :discount_value,
                :minimum_amount, :maximum_discount, :usage_limit,
                :valid_from, :valid_until, :status, :applicable_to
            )
        ";
        
        $params = [
            'code' => strtoupper(trim($couponData['code'])),
            'name' => $couponData['name'],
            'description' => $couponData['description'] ?? null,
            'discount_type' => $couponData['discount_type'] ?? 'percentage',
            'discount_value' => $couponData['discount_value'],
            'minimum_amount' => $couponData['minimum_amount'] ?? 0,
            'maximum_discount' => $couponData['maximum_discount'] ?? null,
            'usage_limit' => $couponData['usage_limit'] ?? null,
            'valid_from' => $couponData['valid_from'] ?? null,
            'valid_until' => $couponData['valid_until'] ?? null,
            'status' => $couponData['status'] ?? 'active',
            'applicable_to' => $couponData['applicable_to'] ?? 'all'
        ];
        
        $couponId = $db->insert($sql, $params);
        
        return [
            'success' => true,
            'coupon_id' => $couponId
        ];
    } catch (Exception $e) {
        Logger::error('Coupon creation failed', ['error' => $e->getMessage()]);
        return [
            'success' => false,
            'message' => $e->getMessage()
        ];
    }
}

