<?php
/**
 * Product Management Functions
 * Handles all product-related operations for the e-commerce system
 */

/**
 * Get products with filters and pagination
 */
function getProducts($filters = [], $limit = 20, $offset = 0) {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Return empty array if database is not available
    if (!$pdo) {
        return [];
    }
    
    $where = ['p.status = :status'];
    $params = ['status' => 'active'];
    
    // Category filter
    if (!empty($filters['category'])) {
        $where[] = 'p.category = :category';
        $params['category'] = $filters['category'];
    }
    
    // Vendor filter
    if (!empty($filters['vendor_id'])) {
        $where[] = 'p.vendor_id = :vendor_id';
        $params['vendor_id'] = $filters['vendor_id'];
    }
    
    // Price range filter
    if (!empty($filters['min_price'])) {
        $where[] = 'p.price >= :min_price';
        $params['min_price'] = $filters['min_price'];
    }
    if (!empty($filters['max_price'])) {
        $where[] = 'p.price <= :max_price';
        $params['max_price'] = $filters['max_price'];
    }
    
    // Search filter
    if (!empty($filters['search'])) {
        $searchTerm = '%' . $filters['search'] . '%';
        $where[] = '(p.name LIKE :search1 OR p.description LIKE :search2 OR p.short_description LIKE :search3)';
        $params['search1'] = $searchTerm;
        $params['search2'] = $searchTerm;
        $params['search3'] = $searchTerm;
    }
    
    // Featured filter
    if (!empty($filters['featured'])) {
        $where[] = 'p.featured = 1';
    }
    
    // In stock filter
    if (!empty($filters['in_stock'])) {
        $where[] = 'p.stock_quantity > 0';
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "
        SELECT 
            p.*,
            v.name as vendor_name,
            v.trust_score,
            v.badges,
            pc.name as category_name,
            pc.cultural_significance,
            (SELECT AVG(rating) FROM product_reviews pr WHERE pr.product_id = p.id AND pr.status = 'approved') as avg_rating,
            (SELECT COUNT(*) FROM product_reviews pr WHERE pr.product_id = p.id AND pr.status = 'approved') as review_count,
            (SELECT COUNT(*) FROM wishlist w WHERE w.product_id = p.id) as wishlist_count
        FROM products p
        LEFT JOIN vendors v ON p.vendor_id = v.id
        LEFT JOIN product_categories pc ON p.category = pc.slug
        WHERE {$whereClause}
        ORDER BY p.featured DESC, p.id DESC
        LIMIT :limit OFFSET :offset
    ";
    
    $params['limit'] = $limit;
    $params['offset'] = $offset;
    
    $products = $db->fetchAll($sql, $params);
    
    // Process products
    foreach ($products as $key => $product) {
        $product['images'] = json_decode($product['images'] ?: '[]', true);
        $product['tags'] = json_decode($product['tags'] ?: '[]', true);
        $product['badges'] = json_decode($product['badges'] ?: '[]', true);
        $product['avg_rating'] = $product['avg_rating'] ? round($product['avg_rating'], 1) : 0;
        $product['review_count'] = (int)$product['review_count'];
        $product['wishlist_count'] = (int)$product['wishlist_count'];
        $product['main_image'] = !empty($product['images']) ? $product['images'][0] : '/assets/images/product-placeholder.jpg';
        $product['formatted_price'] = 'GHS ' . number_format($product['price'], 2);
        $product['has_discount'] = $product['compare_price'] && $product['compare_price'] > $product['price'];
        $product['discount_percent'] = $product['has_discount'] ? round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100) : 0;
        
        // Update the array with processed data
        $products[$key] = $product;
    }
    
    return $products;
}

/**
 * Get single product by ID
 */
function getProductById($productId) {
    $db = Database::getInstance();
    
    $sql = "
        SELECT 
            p.*,
            v.name as vendor_name,
            v.trust_score,
            v.badges,
            v.phone as vendor_phone,
            v.address as vendor_address,
            pc.name as category_name,
            pc.cultural_significance,
            (SELECT AVG(rating) FROM product_reviews pr WHERE pr.product_id = p.id AND pr.status = 'approved') as avg_rating,
            (SELECT COUNT(*) FROM product_reviews pr WHERE pr.product_id = p.id AND pr.status = 'approved') as review_count
        FROM products p
        LEFT JOIN vendors v ON p.vendor_id = v.id
        LEFT JOIN product_categories pc ON p.category = pc.slug
        WHERE p.id = :id AND p.status = 'active'
    ";
    
    $product = $db->fetchOne($sql, ['id' => $productId]);
    
    if (!$product) {
        return null;
    }
    
    // Process product data
    $product['images'] = json_decode($product['images'] ?: '[]', true);
    $product['tags'] = json_decode($product['tags'] ?: '[]', true);
    $product['badges'] = json_decode($product['badges'] ?: '[]', true);
    $product['avg_rating'] = $product['avg_rating'] ? round($product['avg_rating'], 1) : 0;
    $product['review_count'] = (int)$product['review_count'];
    $product['main_image'] = !empty($product['images']) ? $product['images'][0] : '/assets/images/product-placeholder.jpg';
    $product['formatted_price'] = 'GHS ' . number_format($product['price'], 2);
    $product['has_discount'] = $product['compare_price'] && $product['compare_price'] > $product['price'];
    $product['discount_percent'] = $product['has_discount'] ? round((($product['compare_price'] - $product['price']) / $product['compare_price']) * 100) : 0;
    $product['in_stock'] = $product['stock_quantity'] > 0;
    $product['low_stock'] = $product['stock_quantity'] <= $product['low_stock_threshold'];
    
    // Get product variants
    $product['variants'] = getProductVariants($productId);
    
    // Get related products (same category, different vendor)
    $product['related_products'] = getRelatedProducts($productId, $product['category'], $product['vendor_id']);
    
    return $product;
}

/**
 * Get product variants
 */
function getProductVariants($productId) {
    $db = Database::getInstance();
    
    $sql = "
        SELECT *
        FROM product_variants
        WHERE product_id = :product_id AND status = 'active'
        ORDER BY name
    ";
    
    $variants = $db->fetchAll($sql, ['product_id' => $productId]);
    
    foreach ($variants as $key => $variant) {
        $variant['attributes'] = json_decode($variant['attributes'] ?: '{}', true);
        $variant['formatted_price'] = $variant['price'] ? 'GHS ' . number_format($variant['price'], 2) : null;
        $variant['in_stock'] = $variant['stock_quantity'] > 0;
        $variants[$key] = $variant;
    }
    
    return $variants;
}

/**
 * Get related products
 */
function getRelatedProducts($productId, $category, $excludeVendorId = null, $limit = 4) {
    $db = Database::getInstance();
    
    $where = ['p.id != :product_id', 'p.category = :category', 'p.status = "active"'];
    $params = ['product_id' => $productId, 'category' => $category];
    
    if ($excludeVendorId) {
        $where[] = 'p.vendor_id != :exclude_vendor_id';
        $params['exclude_vendor_id'] = $excludeVendorId;
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "
        SELECT 
            p.id, p.name, p.price, p.images, p.stock_quantity,
            v.name as vendor_name,
            (SELECT AVG(rating) FROM product_reviews pr WHERE pr.product_id = p.id AND pr.status = 'approved') as avg_rating
        FROM products p
        LEFT JOIN vendors v ON p.vendor_id = v.id
        WHERE {$whereClause}
        ORDER BY p.featured DESC, RAND()
        LIMIT :limit
    ";
    
    $params['limit'] = $limit;
    
    $products = $db->fetchAll($sql, $params);
    
    foreach ($products as $key => $product) {
        $product['images'] = json_decode($product['images'] ?: '[]', true);
        $product['main_image'] = !empty($product['images']) ? $product['images'][0] : '/assets/images/product-placeholder.jpg';
        $product['formatted_price'] = 'GHS ' . number_format($product['price'], 2);
        $product['avg_rating'] = $product['avg_rating'] ? round($product['avg_rating'], 1) : 0;
        $product['in_stock'] = $product['stock_quantity'] > 0;
        $products[$key] = $product;
    }
    
    return $products;
}

/**
 * Get product categories
 */
function getProductCategories($parentId = null) {
    $db = Database::getInstance();
    
    $where = ['status = "active"'];
    $params = [];
    
    if ($parentId === null) {
        $where[] = 'parent_id IS NULL';
    } else {
        $where[] = 'parent_id = :parent_id';
        $params['parent_id'] = $parentId;
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "
        SELECT *,
               (SELECT COUNT(*) FROM products p WHERE p.category = pc.slug AND p.status = 'active') as product_count
        FROM product_categories pc
        WHERE {$whereClause}
        ORDER BY sort_order, name
    ";
    
    return $db->fetchAll($sql, $params);
}

/**
 * Get featured products
 */
function getFeaturedProducts($limit = 8) {
    return getProducts(['featured' => true], $limit);
}

/**
 * Get products by vendor
 */
function getVendorProducts($vendorId, $limit = 20) {
    return getProducts(['vendor_id' => $vendorId], $limit);
}

/**
 * Search products
 */
function searchProducts($query, $filters = [], $limit = 20, $offset = 0) {
    $filters['search'] = $query;
    return getProducts($filters, $limit, $offset);
}

/**
 * Get product count for filters
 */
function getProductCount($filters = []) {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
    
    // Return 0 if database is not available
    if (!$pdo) {
        return 0;
    }
    
    $where = ['p.status = :status'];
    $params = ['status' => 'active'];
    
    // Apply same filters as getProducts
    if (!empty($filters['category'])) {
        $where[] = 'p.category = :category';
        $params['category'] = $filters['category'];
    }
    
    if (!empty($filters['vendor_id'])) {
        $where[] = 'p.vendor_id = :vendor_id';
        $params['vendor_id'] = $filters['vendor_id'];
    }
    
    if (!empty($filters['min_price'])) {
        $where[] = 'p.price >= :min_price';
        $params['min_price'] = $filters['min_price'];
    }
    
    if (!empty($filters['max_price'])) {
        $where[] = 'p.price <= :max_price';
        $params['max_price'] = $filters['max_price'];
    }
    
    if (!empty($filters['search'])) {
        $searchTerm = '%' . $filters['search'] . '%';
        $where[] = '(p.name LIKE :search1 OR p.description LIKE :search2 OR p.short_description LIKE :search3)';
        $params['search1'] = $searchTerm;
        $params['search2'] = $searchTerm;
        $params['search3'] = $searchTerm;
    }
    
    if (!empty($filters['featured'])) {
        $where[] = 'p.featured = 1';
    }
    
    if (!empty($filters['in_stock'])) {
        $where[] = 'p.stock_quantity > 0';
    }
    
    $whereClause = implode(' AND ', $where);
    
    $sql = "SELECT COUNT(*) as count FROM products p WHERE {$whereClause}";
    
    $result = $db->fetchOne($sql, $params);
    return (int)$result['count'];
}

/**
 * Format product price
 */
function formatProductPrice($price, $comparePrice = null) {
    $formatted = 'GHS ' . number_format($price, 2);
    
    if ($comparePrice && $comparePrice > $price) {
        $formatted .= ' <span class="compare-price">GHS ' . number_format($comparePrice, 2) . '</span>';
    }
    
    return $formatted;
}

/**
 * Get product categories for navigation
 */
function getProductCategoriesForNav() {
    $categories = getProductCategories();
    
    foreach ($categories as $key => $category) {
        $category['subcategories'] = getProductCategories($category['id']);
        $categories[$key] = $category;
    }
    
    return $categories;
}
