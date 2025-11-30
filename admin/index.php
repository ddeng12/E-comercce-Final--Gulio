<?php
/**
 * Admin Panel - Complete Management Dashboard
 */

// Load configuration and includes
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/products.php';
require_once __DIR__ . '/../includes/cart.php';
require_once __DIR__ . '/../includes/helpers.php';

// Initialize session
Auth::initSession();

// Require admin access
Auth::requireAdmin('?page=login');

// Initialize database connection
$db = Database::getInstance();

// Get current admin user
$currentUser = Auth::user();
$userRole = Auth::getUserRole();

// Handle form submissions
$message = $_SESSION['admin_message'] ?? '';
if (isset($_SESSION['admin_message'])) {
    unset($_SESSION['admin_message']); // Clear after displaying
}
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action'])) {
        switch ($_POST['action']) {
            case 'logout':
                // Clear user session data
                if (isset($_SESSION['user'])) {
                    unset($_SESSION['user']);
                }
                // Clear all session data
                $_SESSION = [];
                // Regenerate session ID for security
                session_regenerate_id(true);
                // Redirect to home page (dynamic path)
                $basePath = getBasePath();
                header('Location: ' . $basePath);
                exit;
            case 'update_order_status':
                require_once __DIR__ . '/../includes/orders.php';
                $orderId = $_POST['order_id'] ?? 0;
                $status = $_POST['status'] ?? '';
                $paymentStatus = $_POST['payment_status'] ?? null;
                
                if ($orderId && $status) {
                    $result = updateOrderStatus($orderId, $status, $paymentStatus);
                    if ($result['success']) {
                        $message = 'Order status updated successfully!';
                    } else {
                        $error = $result['message'] ?? 'Failed to update order status';
                    }
                } else {
                    $error = 'Invalid order ID or status';
                }
                break;
            case 'add_product':
                $result = handleAddProduct();
                if ($result['success']) {
                    // Redirect to prevent duplicate submissions (PRG pattern)
                    $_SESSION['admin_message'] = $result['message'];
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $error = $result['message'];
                }
                break;
            case 'edit_product':
                $result = handleEditProduct();
                if ($result['success']) {
                    // Redirect to prevent duplicate submissions (PRG pattern)
                    $_SESSION['admin_message'] = $result['message'];
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $error = $result['message'];
                }
                break;
            case 'delete_product':
                $result = handleDeleteProduct();
                if ($result['success']) {
                    // Redirect to prevent duplicate submissions (PRG pattern)
                    $_SESSION['admin_message'] = $result['message'];
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $error = $result['message'];
                }
                break;
            case 'bulk_delete':
                $result = handleBulkDelete();
                if ($result['success']) {
                    // Redirect to prevent duplicate submissions (PRG pattern)
                    $_SESSION['admin_message'] = $result['message'];
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $error = $result['message'];
                }
                break;
            case 'bulk_featured':
                $result = handleBulkFeatured();
                if ($result['success']) {
                    // Redirect to prevent duplicate submissions (PRG pattern)
                    $_SESSION['admin_message'] = $result['message'];
                    header('Location: ' . $_SERVER['PHP_SELF']);
                    exit;
                } else {
                    $error = $result['message'];
                }
                break;
            case 'seed_sample_data':
                $result = seedSampleProducts();
                if ($result['success']) {
                    $message = $result['message'];
                } else {
                    $error = $result['message'];
                }
                break;
        }
    }
}

// Get filter/search parameters
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';
$status = $_GET['status'] ?? '';
$sort = $_GET['sort'] ?? 'newest';

// Build filters
$filters = [];
if ($search) $filters['search'] = $search;
if ($category) $filters['category'] = $category;
if ($status) $filters['status'] = $status;

// Get products with filters (admin version - shows all statuses)
$db = Database::getInstance();
$where = [];
$params = [];

// Category filter
if ($category) {
    $where[] = 'p.category = :category';
    $params['category'] = $category;
}

// Status filter
if ($status) {
    $where[] = 'p.status = :status';
    $params['status'] = $status;
} else {
    // Show all statuses in admin
    $where[] = 'p.status IS NOT NULL';
}

// Search filter
if ($search) {
    $where[] = '(p.name LIKE :search OR p.description LIKE :search OR p.short_description LIKE :search)';
    $params['search'] = '%' . $search . '%';
}

$whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

$sql = "SELECT p.*, v.name as vendor_name FROM products p 
        LEFT JOIN vendors v ON p.vendor_id = v.id 
        $whereClause 
        ORDER BY p.featured DESC, p.id DESC 
        LIMIT 100";

$products = $db->fetchAll($sql, $params);

// Get total count
$countSql = "SELECT COUNT(*) as count FROM products p $whereClause";
$totalProducts = $db->fetchOne($countSql, $params)['count'] ?? 0;

// Get analytics data
$analytics = getAnalytics();

// Get orders from database
require_once __DIR__ . '/../includes/orders.php';
$orders = getAllOrders([], 50, 0);

function handleAddProduct() {
    $db = Database::getInstance();
    
    try {
        $imagePath = '';
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/images/products/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new Exception('Failed to create upload directory. Please create assets/images/products/ manually with write permissions.');
                }
            }
            
            // Check if directory is writable
            if (!is_writable($uploadDir)) {
                // Try to make it writable
                @chmod($uploadDir, 0777);
                if (!is_writable($uploadDir)) {
                    throw new Exception('Upload directory is not writable. Please set permissions to 777 on assets/images/products/');
                }
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['product_image']['name']);
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadPath)) {
                // Save absolute path from document root for consistency
                $basePath = getBasePath();
                $imagePath = rtrim($basePath, '/') . '/assets/images/products/' . $fileName;
                // Set file permissions
                @chmod($uploadPath, 0644);
            } else {
                throw new Exception('Failed to move uploaded file. Check directory permissions.');
            }
        }
        
        $sql = "INSERT INTO products (vendor_id, name, description, short_description, category, price, stock_quantity, image_url, status, featured) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)";
        
        $params = [
            $_POST['vendor_id'] ?? 1,
            $_POST['name'],
            $_POST['description'] ?? '',
            $_POST['short_description'],
            $_POST['category'],
            $_POST['price'],
            $_POST['stock_quantity'],
            $imagePath,
            isset($_POST['featured']) ? 1 : 0
        ];
        
        $db->query($sql, $params);
        
        return ['success' => true, 'message' => 'Product added successfully!'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error adding product: ' . $e->getMessage()];
    }
}

function handleEditProduct() {
    $db = Database::getInstance();
    $productId = $_POST['product_id'] ?? 0;
    
    if (!$productId) {
        return ['success' => false, 'message' => 'Product ID required'];
    }
    
    try {
        $imagePath = null;
        if (isset($_FILES['product_image']) && $_FILES['product_image']['error'] === UPLOAD_ERR_OK) {
            $uploadDir = __DIR__ . '/../assets/images/products/';
            
            // Create directory if it doesn't exist
            if (!is_dir($uploadDir)) {
                if (!mkdir($uploadDir, 0777, true)) {
                    throw new Exception('Failed to create upload directory. Please create assets/images/products/ manually with write permissions.');
                }
            }
            
            // Check if directory is writable
            if (!is_writable($uploadDir)) {
                // Try to make it writable
                @chmod($uploadDir, 0777);
                if (!is_writable($uploadDir)) {
                    throw new Exception('Upload directory is not writable. Please set permissions to 777 on assets/images/products/');
                }
            }
            
            $fileName = uniqid() . '_' . basename($_FILES['product_image']['name']);
            $uploadPath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['product_image']['tmp_name'], $uploadPath)) {
                // Save absolute path from document root for consistency
                $basePath = getBasePath();
                $imagePath = rtrim($basePath, '/') . '/assets/images/products/' . $fileName;
                // Set file permissions
                @chmod($uploadPath, 0644);
            } else {
                throw new Exception('Failed to move uploaded file. Check directory permissions.');
            }
        }
        
        $sql = "UPDATE products SET 
                name = ?, 
                description = ?, 
                short_description = ?, 
                category = ?, 
                price = ?, 
                stock_quantity = ?, 
                featured = ?";
        
        $params = [
            $_POST['name'],
            $_POST['description'] ?? '',
            $_POST['short_description'],
            $_POST['category'],
            $_POST['price'],
            $_POST['stock_quantity'],
            isset($_POST['featured']) ? 1 : 0
        ];
        
        if ($imagePath) {
            $sql .= ", image_url = ?";
            $params[] = $imagePath;
        }
        
        $sql .= " WHERE id = ?";
        $params[] = $productId;
        
        $db->query($sql, $params);
        
        return ['success' => true, 'message' => 'Product updated successfully!'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating product: ' . $e->getMessage()];
    }
}

function handleDeleteProduct() {
    $db = Database::getInstance();
    $productId = $_POST['product_id'] ?? 0;
    
    if (!$productId) {
        return ['success' => false, 'message' => 'Product ID required'];
    }
    
    try {
        // Check if product has been ordered (foreign key constraint)
        $orderCount = $db->fetchOne(
            "SELECT COUNT(*) as count FROM order_items WHERE product_id = ?",
            [$productId]
        );
        
        if ($orderCount && $orderCount['count'] > 0) {
            // Product has orders - use soft delete instead (set status to inactive)
            $db->query(
                "UPDATE products SET status = 'inactive' WHERE id = ?",
                [$productId]
            );
            return [
                'success' => true,
                'message' => 'Product deactivated successfully! (Cannot be permanently deleted because it has ' . $orderCount['count'] . ' order(s). Product is now hidden from the store.)'
            ];
        }
        
        // No orders - safe to delete permanently
        $db->query("DELETE FROM products WHERE id = ?", [$productId]);
        return ['success' => true, 'message' => 'Product deleted successfully!'];
    } catch (Exception $e) {
        // If it's a foreign key constraint error, provide a helpful message
        if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
            // Try soft delete as fallback
            try {
                $db->query(
                    "UPDATE products SET status = 'inactive' WHERE id = ?",
                    [$productId]
                );
                return [
                    'success' => true,
                    'message' => 'Product deactivated successfully! (Cannot be permanently deleted because it has orders. Product is now hidden from the store.)'
                ];
            } catch (Exception $e2) {
                return ['success' => false, 'message' => 'Cannot delete product: It has been ordered by customers. Please deactivate it instead by editing the product status.'];
            }
        }
        return ['success' => false, 'message' => 'Error deleting product: ' . $e->getMessage()];
    }
}

function handleBulkDelete() {
    $db = Database::getInstance();
    $productIds = $_POST['product_ids'] ?? [];
    
    if (empty($productIds)) {
        return ['success' => false, 'message' => 'No products selected'];
    }
    
    try {
        // Check which products have orders
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $productsWithOrders = $db->fetchAll(
            "SELECT DISTINCT product_id FROM order_items WHERE product_id IN ($placeholders)",
            $productIds
        );
        
        $productsWithOrdersIds = array_column($productsWithOrders, 'product_id');
        $productsToDelete = array_diff($productIds, $productsWithOrdersIds);
        $productsToDeactivate = array_intersect($productIds, $productsWithOrdersIds);
        
        $deletedCount = 0;
        $deactivatedCount = 0;
        
        // Delete products without orders
        if (!empty($productsToDelete)) {
            $deletePlaceholders = implode(',', array_fill(0, count($productsToDelete), '?'));
            $db->query("DELETE FROM products WHERE id IN ($deletePlaceholders)", array_values($productsToDelete));
            $deletedCount = count($productsToDelete);
        }
        
        // Soft delete (deactivate) products with orders
        if (!empty($productsToDeactivate)) {
            $deactivatePlaceholders = implode(',', array_fill(0, count($productsToDeactivate), '?'));
            $db->query("UPDATE products SET status = 'inactive' WHERE id IN ($deactivatePlaceholders)", array_values($productsToDeactivate));
            $deactivatedCount = count($productsToDeactivate);
        }
        
        $messages = [];
        if ($deletedCount > 0) {
            $messages[] = "$deletedCount product(s) deleted successfully";
        }
        if ($deactivatedCount > 0) {
            $messages[] = "$deactivatedCount product(s) deactivated (cannot be deleted because they have orders)";
        }
        
        return [
            'success' => true,
            'message' => implode('. ', $messages) . '.'
        ];
    } catch (Exception $e) {
        // If foreign key constraint error, try soft delete for all
        if (strpos($e->getMessage(), 'foreign key constraint') !== false) {
            try {
                $placeholders = implode(',', array_fill(0, count($productIds), '?'));
                $db->query("UPDATE products SET status = 'inactive' WHERE id IN ($placeholders)", $productIds);
                return [
                    'success' => true,
                    'message' => count($productIds) . ' product(s) deactivated (cannot be permanently deleted because they have orders)'
                ];
            } catch (Exception $e2) {
                return ['success' => false, 'message' => 'Error: Some products cannot be deleted because they have orders. Please deactivate them instead.'];
            }
        }
        return ['success' => false, 'message' => 'Error deleting products: ' . $e->getMessage()];
    }
}

function handleBulkFeatured() {
    $db = Database::getInstance();
    $productIds = $_POST['product_ids'] ?? [];
    $featured = isset($_POST['featured']) ? 1 : 0;
    
    if (empty($productIds)) {
        return ['success' => false, 'message' => 'No products selected'];
    }
    
    try {
        $placeholders = implode(',', array_fill(0, count($productIds), '?'));
        $params = array_merge([$featured], $productIds);
        $db->query("UPDATE products SET featured = ? WHERE id IN ($placeholders)", $params);
        return ['success' => true, 'message' => count($productIds) . ' product(s) updated successfully!'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error updating products: ' . $e->getMessage()];
    }
}

function getAnalytics() {
    $db = Database::getInstance();
    
    try {
        // Total products
        $totalProducts = $db->fetchOne("SELECT COUNT(*) as count FROM products")['count'] ?? 0;
        
        // Active products
        $activeProducts = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE status = 'active'")['count'] ?? 0;
        
        // Low stock products
        $lowStock = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE stock_quantity <= 5 AND stock_quantity > 0")['count'] ?? 0;
        
        // Out of stock
        $outOfStock = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE stock_quantity = 0")['count'] ?? 0;
        
        // Total orders
        require_once __DIR__ . '/../includes/orders.php';
        $totalOrders = getOrderCount();
        
        // Total revenue
        $revenueResult = $db->fetchOne("SELECT SUM(total_amount) as revenue FROM orders WHERE payment_status = 'paid'");
        $totalRevenue = $revenueResult['revenue'] ?? 0;
        
        // Featured products
        $featuredProducts = $db->fetchOne("SELECT COUNT(*) as count FROM products WHERE featured = 1")['count'] ?? 0;
        
        return [
            'total_products' => $totalProducts,
            'active_products' => $activeProducts,
            'low_stock' => $lowStock,
            'out_of_stock' => $outOfStock,
            'total_orders' => $totalOrders,
            'total_revenue' => $totalRevenue,
            'featured_products' => $featuredProducts
        ];
    } catch (Exception $e) {
        return [
            'total_products' => 0,
            'active_products' => 0,
            'low_stock' => 0,
            'out_of_stock' => 0,
            'total_orders' => 0,
            'total_revenue' => 0,
            'featured_products' => 0
        ];
    }
}

function getOrders() {
    $orders = $_SESSION['completed_orders'] ?? [];
    
    // Sort by date (newest first)
    usort($orders, function($a, $b) {
        return strtotime($b['created_at'] ?? '') - strtotime($a['created_at'] ?? '');
    });
    
    return array_slice($orders, 0, 20); // Return latest 20 orders
}

function seedSampleProducts() {
    $db = Database::getInstance();
    
    try {
        $sampleProducts = [
            [
                'name' => 'Kente Cloth Traditional Scarf',
                'description' => 'Authentic handwoven Kente cloth scarf from Ghana. Each piece tells a story through its intricate patterns and vibrant colors.',
                'short_description' => 'Handwoven Kente cloth scarf with traditional patterns',
                'category' => 'traditional_clothing',
                'price' => 45.00,
                'stock' => 15,
                'image' => 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=400&h=400&fit=crop',
                'featured' => 1
            ],
            [
                'name' => 'Shea Butter Natural Skincare',
                'description' => 'Pure, unrefined shea butter sourced directly from northern Ghana. Perfect for moisturizing and healing dry skin.',
                'short_description' => 'Pure unrefined shea butter from Ghana',
                'category' => 'beauty_products',
                'price' => 18.50,
                'stock' => 30,
                'image' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=400&h=400&fit=crop',
                'featured' => 1
            ],
            [
                'name' => 'Adinkra Symbol Wall Art',
                'description' => 'Beautiful wooden wall art featuring traditional Adinkra symbols. Each symbol carries deep meaning in Ghanaian culture.',
                'short_description' => 'Handcrafted wooden Adinkra symbol art',
                'category' => 'artisan_crafts',
                'price' => 65.00,
                'stock' => 8,
                'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=400&fit=crop',
                'featured' => 0
            ],
            [
                'name' => 'Plantain Chips - Spicy',
                'description' => 'Crispy plantain chips seasoned with traditional Ghanaian spices. A perfect healthy snack with authentic flavors.',
                'short_description' => 'Crispy spiced plantain chips',
                'category' => 'food_items',
                'price' => 8.99,
                'stock' => 50,
                'image' => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&h=400&fit=crop',
                'featured' => 0
            ],
            [
                'name' => 'Handwoven Basket Set',
                'description' => 'Set of 3 handwoven baskets made from local materials. Perfect for storage and home decoration.',
                'short_description' => 'Set of 3 handwoven storage baskets',
                'category' => 'artisan_crafts',
                'price' => 32.00,
                'stock' => 12,
                'image' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=400&h=400&fit=crop',
                'featured' => 1
            ],
            [
                'name' => 'African Print Face Masks',
                'description' => 'Stylish and comfortable face masks made with authentic African print fabrics. Pack of 3 different patterns.',
                'short_description' => 'Pack of 3 African print face masks',
                'category' => 'accessories',
                'price' => 15.00,
                'stock' => 25,
                'image' => 'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=400&h=400&fit=crop',
                'featured' => 0
            ],
            [
                'name' => 'Cocoa Powder - Premium Grade',
                'description' => 'Premium quality cocoa powder from Ghana\'s finest cocoa beans. Perfect for baking and hot chocolate.',
                'short_description' => 'Premium Ghanaian cocoa powder',
                'category' => 'food_items',
                'price' => 22.50,
                'stock' => 20,
                'image' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&h=400&fit=crop',
                'featured' => 1
            ],
            [
                'name' => 'Beaded Jewelry Set',
                'description' => 'Elegant beaded necklace and earring set handcrafted by local artisans using traditional techniques.',
                'short_description' => 'Handcrafted beaded necklace and earring set',
                'category' => 'accessories',
                'price' => 28.00,
                'stock' => 18,
                'image' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=400&h=400&fit=crop',
                'featured' => 0
            ]
        ];
        
        $sql = "INSERT INTO products (vendor_id, name, description, short_description, category, price, stock_quantity, image_url, status, featured) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?)";
        
        foreach ($sampleProducts as $product) {
            $params = [
                1,
                $product['name'],
                $product['description'],
                $product['short_description'],
                $product['category'],
                $product['price'],
                $product['stock'],
                $product['image'],
                $product['featured']
            ];
            
            $db->query($sql, $params);
        }
        
        return ['success' => true, 'message' => 'Sample products added successfully!'];
    } catch (Exception $e) {
        return ['success' => false, 'message' => 'Error seeding products: ' . $e->getMessage()];
    }
}

// Get product categories for filter (use enum values from products table)
$categoryOptions = [
    'artisan_crafts' => 'Artisan Crafts',
    'textiles' => 'Textiles',
    'art' => 'Art',
    'fresh_produce' => 'Fresh Produce',
    'cultural_items' => 'Cultural Items',
    'traditional_clothing' => 'Traditional Clothing',
    'accessories' => 'Accessories',
    'local_brands' => 'Local Brands',
    'electronics' => 'Electronics',
    'beauty_products' => 'Beauty Products',
    'food_items' => 'Food Items',
    'other' => 'Other'
];
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Gulio</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        :root {
            --primary-orange: #FF6B35;
            --primary-teal: #4ECDC4;
            --dark-gray: #333;
            --light-gray: #f5f5f5;
            --white: #ffffff;
            --success: #27ae60;
            --danger: #e74c3c;
            --warning: #f39c12;
        }
        
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: var(--light-gray);
            color: var(--dark-gray);
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        /* Header */
        .header {
            background: linear-gradient(135deg, var(--primary-orange), var(--primary-teal));
            color: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        
        .header-content {
            display: flex;
            justify-content: space-between;
            align-items: center;
            flex-wrap: wrap;
            gap: 20px;
        }
        
        .header-left h1 {
            font-size: 2.5em;
            margin-bottom: 10px;
        }
        
        .header-right {
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .user-info {
            text-align: right;
        }
        
        .user-name {
            font-weight: 600;
            font-size: 16px;
            display: block;
            margin-bottom: 5px;
        }
        
        .user-role {
            font-size: 12px;
            background: rgba(255,255,255,0.2);
            padding: 4px 12px;
            border-radius: 20px;
            text-transform: uppercase;
            display: inline-block;
        }
        
        .logout-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 1px solid rgba(255,255,255,0.3);
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .logout-btn:hover {
            background: rgba(255,255,255,0.3);
            transform: translateY(-2px);
        }
        
        /* Alerts */
        .alert {
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .alert.success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid var(--success);
        }
        
        .alert.error {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid var(--danger);
        }
        
        /* Analytics Cards */
        .analytics-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            padding: 25px;
            border-radius: 12px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary-orange);
            transition: transform 0.3s ease;
        }
        
        .stat-card:hover {
            transform: translateY(-5px);
        }
        
        .stat-card.teal {
            border-left-color: var(--primary-teal);
        }
        
        .stat-card.success {
            border-left-color: var(--success);
        }
        
        .stat-card.warning {
            border-left-color: var(--warning);
        }
        
        .stat-card.danger {
            border-left-color: var(--danger);
        }
        
        .stat-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 8px;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        
        .stat-value {
            font-size: 32px;
            font-weight: 700;
            color: var(--dark-gray);
        }
        
        .stat-icon {
            font-size: 24px;
            color: var(--primary-orange);
            margin-bottom: 10px;
        }
        
        /* Sections */
        .section {
            background: white;
            padding: 30px;
            border-radius: 12px;
            margin-bottom: 30px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .section-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
            flex-wrap: wrap;
            gap: 15px;
        }
        
        .section h2 {
            color: var(--primary-orange);
            font-size: 1.8em;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        /* Search and Filters */
        .filters-bar {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 20px;
            align-items: center;
        }
        
        .filter-group {
            flex: 1;
            min-width: 200px;
        }
        
        .filter-group input,
        .filter-group select {
            width: 100%;
            padding: 10px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
        }
        
        .filter-group input:focus,
        .filter-group select:focus {
            outline: none;
            border-color: var(--primary-orange);
        }
        
        .search-box {
            position: relative;
        }
        
        .search-box i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #999;
        }
        
        .search-box input {
            padding-left: 40px;
        }
        
        /* Bulk Actions */
        .bulk-actions {
            display: flex;
            gap: 10px;
            align-items: center;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 8px;
        }
        
        .bulk-actions input[type="checkbox"] {
            margin-right: 10px;
        }
        
        /* Buttons */
        .btn {
            padding: 12px 24px;
            border: none;
            border-radius: 8px;
            font-size: 14px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }
        
        .btn-primary {
            background: var(--primary-orange);
            color: white;
        }
        
        .btn-primary:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }
        
        .btn-secondary {
            background: var(--primary-teal);
            color: white;
        }
        
        .btn-secondary:hover {
            background: #3db8ae;
        }
        
        .btn-danger {
            background: var(--danger);
            color: white;
        }
        
        .btn-danger:hover {
            background: #c0392b;
        }
        
        .btn-sm {
            padding: 6px 12px;
            font-size: 12px;
        }
        
        /* Forms */
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-group label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: #555;
        }
        
        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e1e5e9;
            border-radius: 8px;
            font-size: 14px;
            transition: border-color 0.3s;
        }
        
        .form-group input:focus,
        .form-group textarea:focus,
        .form-group select:focus {
            outline: none;
            border-color: var(--primary-orange);
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
        }
        
        /* Products Grid */
        .products-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .product-card {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
            border: 2px solid transparent;
        }
        
        .product-card:hover {
            transform: translateY(-5px);
            border-color: var(--primary-orange);
        }
        
        .product-card.selected {
            border-color: var(--primary-teal);
        }
        
        .product-image {
            width: 100%;
            height: 200px;
            object-fit: cover;
        }
        
        .product-info {
            padding: 15px;
        }
        
        .product-name {
            font-weight: 600;
            margin-bottom: 8px;
            color: var(--dark-gray);
            font-size: 16px;
        }
        
        .product-price {
            color: var(--primary-orange);
            font-weight: 700;
            font-size: 1.2em;
            margin-bottom: 5px;
        }
        
        .product-meta {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 10px;
            font-size: 12px;
            color: #666;
        }
        
        .product-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f0f0f0;
        }
        
        .product-actions .btn {
            flex: 1;
        }
        
        .featured-badge {
            background: var(--warning);
            color: white;
            padding: 2px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 600;
            margin-left: 8px;
        }
        
        /* Orders Table */
        .orders-table {
            width: 100%;
            border-collapse: collapse;
        }
        
        .orders-table th,
        .orders-table td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #f0f0f0;
        }
        
        .orders-table th {
            background: #f8f9fa;
            font-weight: 600;
            color: var(--dark-gray);
        }
        
        .orders-table tr:hover {
            background: #f8f9fa;
        }
        
        /* Modal */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        
        .modal.active {
            display: flex;
        }
        
        .modal-content {
            background: white;
            padding: 30px;
            border-radius: 12px;
            max-width: 600px;
            width: 90%;
            max-height: 90vh;
            overflow-y: auto;
        }
        
        .modal-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }
        
        .modal-header h3 {
            color: var(--primary-orange);
        }
        
        .close-btn {
            background: none;
            border: none;
            font-size: 24px;
            cursor: pointer;
            color: #999;
        }
        
        .close-btn:hover {
            color: var(--dark-gray);
        }
        
        /* Responsive */
        @media (max-width: 768px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .products-grid {
                grid-template-columns: 1fr;
            }
            
            .analytics-grid {
                grid-template-columns: 1fr;
            }
            
            .filters-bar {
                flex-direction: column;
            }
        }
    </style>
    <script>
        // Define modal functions immediately - must be available before page loads
        function openAddProductModal() {
            const modal = document.getElementById('addProductModal');
            if (modal) {
                modal.classList.add('active');
            } else {
                console.error('Add product modal not found');
            }
        }
        
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
            }
        }
        
        function openEditProductModal(productId) {
            // This will be populated when the page loads with product data
            console.log('Opening edit modal for product:', productId);
            // The actual implementation is in the body script where products data is available
        }
        
        function deleteProduct(productId) {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="product_id" value="${productId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        }
        
        // Attach to window for extra safety
        window.openAddProductModal = openAddProductModal;
        window.closeModal = closeModal;
        window.openEditProductModal = openEditProductModal;
        window.deleteProduct = deleteProduct;
    </script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="header-content">
                <div class="header-left">
                    <h1><i class="fas fa-tachometer-alt"></i> Admin Dashboard</h1>
                    <p>Manage your products, orders, and analytics</p>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <span class="user-name"><?= htmlspecialchars($currentUser['name'] ?? 'Admin') ?></span>
                        <span class="user-role"><?= ucfirst($userRole) ?></span>
                    </div>
                    <form method="POST" style="display: inline;">
                        <input type="hidden" name="action" value="logout">
                        <button type="submit" class="logout-btn">
                            <i class="fas fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Alerts -->
        <?php if ($message): ?>
            <div class="alert success">
                <i class="fas fa-check-circle"></i>
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alert error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <!-- Analytics -->
        <div class="analytics-grid">
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-box"></i></div>
                <div class="stat-label">Total Products</div>
                <div class="stat-value"><?= $analytics['total_products'] ?></div>
            </div>
            <div class="stat-card teal">
                <div class="stat-icon"><i class="fas fa-check-circle"></i></div>
                <div class="stat-label">Active Products</div>
                <div class="stat-value"><?= $analytics['active_products'] ?></div>
            </div>
            <div class="stat-card success">
                <div class="stat-icon"><i class="fas fa-shopping-cart"></i></div>
                <div class="stat-label">Total Orders</div>
                <div class="stat-value"><?= $analytics['total_orders'] ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-icon"><i class="fas fa-money-bill-wave"></i></div>
                <div class="stat-label">Total Revenue</div>
                <div class="stat-value">GHS <?= number_format($analytics['total_revenue'], 2) ?></div>
            </div>
            <div class="stat-card warning">
                <div class="stat-icon"><i class="fas fa-exclamation-triangle"></i></div>
                <div class="stat-label">Low Stock</div>
                <div class="stat-value"><?= $analytics['low_stock'] ?></div>
            </div>
            <div class="stat-card danger">
                <div class="stat-icon"><i class="fas fa-times-circle"></i></div>
                <div class="stat-label">Out of Stock</div>
                <div class="stat-value"><?= $analytics['out_of_stock'] ?></div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="section">
            <h2><i class="fas fa-bolt"></i> Quick Actions</h2>
            <form method="POST" style="display: inline-block; margin-right: 15px;">
                <input type="hidden" name="action" value="seed_sample_data">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-database"></i> Add Sample Products
                </button>
            </form>
            <button onclick="openAddProductModal()" class="btn btn-primary">
                <i class="fas fa-plus"></i> Add New Product
            </button>
        </div>

        <!-- Products Management -->
        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-boxes"></i> Products (<?= $totalProducts ?>)</h2>
            </div>

            <!-- Search and Filters -->
            <form method="GET" action="" class="filters-bar">
                <div class="filter-group search-box">
                    <i class="fas fa-search"></i>
                    <input type="text" name="search" placeholder="Search products..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="filter-group">
                    <select name="category">
                        <option value="">All Categories</option>
                        <?php foreach ($categoryOptions as $key => $label): ?>
                            <option value="<?= $key ?>" <?= $category === $key ? 'selected' : '' ?>>
                                <?= $label ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="filter-group">
                    <select name="status">
                        <option value="">All Status</option>
                        <option value="active" <?= $status === 'active' ? 'selected' : '' ?>>Active</option>
                        <option value="inactive" <?= $status === 'inactive' ? 'selected' : '' ?>>Inactive</option>
                        <option value="out_of_stock" <?= $status === 'out_of_stock' ? 'selected' : '' ?>>Out of Stock</option>
                    </select>
                </div>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-filter"></i> Filter
                </button>
                <?php if ($search || $category || $status): ?>
                    <a href="?" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Clear
                    </a>
                <?php endif; ?>
            </form>

            <!-- Bulk Actions -->
            <form method="POST" id="bulkForm" style="display: none;">
                <input type="hidden" name="action" id="bulkAction" value="">
                <div class="bulk-actions">
                    <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                    <span id="selectedCount">0 selected</span>
                    <button type="submit" onclick="setBulkAction('bulk_featured')" class="btn btn-sm btn-secondary">
                        <i class="fas fa-star"></i> Mark Featured
                    </button>
                    <button type="submit" onclick="setBulkAction('bulk_delete')" class="btn btn-sm btn-danger">
                        <i class="fas fa-trash"></i> Delete Selected
                    </button>
                </div>
            </form>

            <!-- Products Grid -->
            <?php if (empty($products)): ?>
                <p style="text-align: center; color: #666; padding: 40px;">
                    No products found. Add your first product to get started!
                </p>
            <?php else: ?>
                <div class="products-grid">
                    <?php foreach ($products as $product): ?>
                        <div class="product-card" data-product-id="<?= $product['id'] ?>">
                            <input type="checkbox" class="product-checkbox" value="<?= $product['id'] ?>" onchange="updateBulkActions()" style="position: absolute; top: 10px; left: 10px; z-index: 10;">
                            <?php
                            $imageUrl = getProductImageUrl($product['image_url'] ?? '');
                            ?>
                            <?php if ($imageUrl): ?>
                                <img src="<?= htmlspecialchars($imageUrl) ?>" 
                                     alt="<?= htmlspecialchars($product['name']) ?>" 
                                     class="product-image"
                                     onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="product-image" style="background: #f0f0f0; display: none; align-items: center; justify-content: center; color: #999;">
                                    <i class="fas fa-image" style="font-size: 48px;"></i>
                                </div>
                            <?php else: ?>
                                <div class="product-image" style="background: #f0f0f0; display: flex; align-items: center; justify-content: center; color: #999;">
                                    <i class="fas fa-image" style="font-size: 48px;"></i>
                                </div>
                            <?php endif; ?>
                            
                            <div class="product-info">
                                <div class="product-name">
                                    <?= htmlspecialchars($product['name']) ?>
                                    <?php if ($product['featured']): ?>
                                        <span class="featured-badge">Featured</span>
                                    <?php endif; ?>
                                </div>
                                <div class="product-price">GHS <?= number_format($product['price'], 2) ?></div>
                                <div class="product-meta">
                                    <span>Stock: <?= $product['stock_quantity'] ?></span>
                                    <span><?= ucfirst(str_replace('_', ' ', $product['category'])) ?></span>
                                </div>
                                <div class="product-actions">
                                    <button onclick="openEditProductModal(<?= $product['id'] ?>)" class="btn btn-sm btn-secondary">
                                        <i class="fas fa-edit"></i> Edit
                                    </button>
                                    <button onclick="deleteProduct(<?= $product['id'] ?>)" class="btn btn-sm btn-danger">
                                        <i class="fas fa-trash"></i> Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Orders Management -->
        <div class="section">
            <div class="section-header">
                <h2><i class="fas fa-shopping-bag"></i> Order Management</h2>
                <div style="display: flex; gap: 10px; align-items: center;">
                    <select id="orderStatusFilter" onchange="filterOrders()" style="padding: 8px; border: 1px solid #ddd; border-radius: 4px;">
                        <option value="">All Status</option>
                        <option value="pending">Pending</option>
                        <option value="confirmed">Confirmed</option>
                        <option value="processing">Processing</option>
                        <option value="shipped">Shipped</option>
                        <option value="delivered">Delivered</option>
                        <option value="cancelled">Cancelled</option>
                    </select>
                    <input type="text" id="orderSearch" placeholder="Search orders..." onkeyup="filterOrders()" 
                           style="padding: 8px; border: 1px solid #ddd; border-radius: 4px; width: 200px;">
                </div>
            </div>
            
            <?php if (empty($orders)): ?>
                <p style="text-align: center; color: #666; padding: 40px;">
                    No orders yet. Orders will appear here once customers make purchases.
                </p>
            <?php else: ?>
                <table class="orders-table" style="width: 100%; border-collapse: collapse; margin-top: 20px;">
                    <thead>
                        <tr style="background: var(--primary-teal); color: white;">
                            <th style="padding: 12px; text-align: left;">Order Number</th>
                            <th style="padding: 12px; text-align: left;">Customer</th>
                            <th style="padding: 12px; text-align: left;">Items</th>
                            <th style="padding: 12px; text-align: right;">Total</th>
                            <th style="padding: 12px; text-align: left;">Date</th>
                            <th style="padding: 12px; text-align: left;">Status</th>
                            <th style="padding: 12px; text-align: left;">Payment</th>
                            <th style="padding: 12px; text-align: center;">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr style="border-bottom: 1px solid #ddd;">
                                <td style="padding: 12px;">
                                    <strong>#<?= htmlspecialchars($order['order_number']) ?></strong>
                                </td>
                                <td style="padding: 12px;">
                                    <?= htmlspecialchars($order['user_name'] ?? $order['guest_email'] ?? 'Guest') ?>
                                    <?php if (!empty($order['user_email'])): ?>
                                        <br><small style="color: #666;"><?= htmlspecialchars($order['user_email']) ?></small>
                                    <?php endif; ?>
                                </td>
                                <td style="padding: 12px;"><?= $order['item_count'] ?? 0 ?> item(s)</td>
                                <td style="padding: 12px; text-align: right;">
                                    <strong>GHS <?= number_format($order['total_amount'] ?? 0, 2) ?></strong>
                                </td>
                                <td style="padding: 12px;">
                                    <?= date('M j, Y', strtotime($order['created_at'])) ?><br>
                                    <small style="color: #666;"><?= date('g:i A', strtotime($order['created_at'])) ?></small>
                                </td>
                                <td style="padding: 12px;">
                                    <select onchange="updateOrderStatus(<?= $order['id'] ?>, this.value)" 
                                            style="padding: 5px; border: 1px solid #ddd; border-radius: 4px;">
                                        <option value="pending" <?= $order['status'] == 'pending' ? 'selected' : '' ?>>Pending</option>
                                        <option value="confirmed" <?= $order['status'] == 'confirmed' ? 'selected' : '' ?>>Confirmed</option>
                                        <option value="processing" <?= $order['status'] == 'processing' ? 'selected' : '' ?>>Processing</option>
                                        <option value="shipped" <?= $order['status'] == 'shipped' ? 'selected' : '' ?>>Shipped</option>
                                        <option value="delivered" <?= $order['status'] == 'delivered' ? 'selected' : '' ?>>Delivered</option>
                                        <option value="cancelled" <?= $order['status'] == 'cancelled' ? 'selected' : '' ?>>Cancelled</option>
                                    </select>
                                </td>
                                <td style="padding: 12px;">
                                    <span style="padding: 4px 8px; border-radius: 4px; font-size: 12px; 
                                          background: <?= $order['payment_status'] == 'paid' ? '#28a745' : '#ffc107' ?>; 
                                          color: white;">
                                        <?= ucfirst($order['payment_status'] ?? 'pending') ?>
                                    </span>
                                </td>
                                <td style="padding: 12px; text-align: center;">
                                    <a href="../?page=invoice&order_id=<?= $order['id'] ?>" target="_blank" 
                                       class="btn btn-sm" style="padding: 6px 12px; font-size: 12px; background: var(--primary-orange); color: white; text-decoration: none;">
                                        <i class="fas fa-file-invoice"></i> Invoice
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Add Product Modal -->
    <div id="addProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-plus"></i> Add New Product</h3>
                <button class="close-btn" onclick="closeModal('addProductModal')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data">
                <input type="hidden" name="action" value="add_product">
                <div class="form-row">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" required>
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category" required>
                            <option value="artisan_crafts">Artisan Crafts</option>
                            <option value="textiles">Textiles</option>
                            <option value="art">Art</option>
                            <option value="fresh_produce">Fresh Produce</option>
                            <option value="cultural_items">Cultural Items</option>
                            <option value="traditional_clothing">Traditional Clothing</option>
                            <option value="accessories">Accessories</option>
                            <option value="local_brands">Local Brands</option>
                            <option value="electronics">Electronics</option>
                            <option value="beauty_products">Beauty Products</option>
                            <option value="food_items">Food Items</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Short Description *</label>
                    <input type="text" name="short_description" maxlength="500" required>
                </div>
                <div class="form-group">
                    <label>Full Description</label>
                    <textarea name="description" rows="4"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Price (GHS) *</label>
                        <input type="number" name="price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock_quantity" min="0" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Product Image</label>
                    <input type="file" name="product_image" accept="image/*">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="featured" value="1">
                        Featured Product
                    </label>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closeModal('addProductModal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Edit Product Modal -->
    <div id="editProductModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3><i class="fas fa-edit"></i> Edit Product</h3>
                <button class="close-btn" onclick="closeModal('editProductModal')">&times;</button>
            </div>
            <form method="POST" enctype="multipart/form-data" id="editProductForm">
                <input type="hidden" name="action" value="edit_product">
                <input type="hidden" name="product_id" id="edit_product_id">
                <div class="form-row">
                    <div class="form-group">
                        <label>Product Name *</label>
                        <input type="text" name="name" id="edit_name" required>
                    </div>
                    <div class="form-group">
                        <label>Category *</label>
                        <select name="category" id="edit_category" required>
                            <option value="artisan_crafts">Artisan Crafts</option>
                            <option value="textiles">Textiles</option>
                            <option value="art">Art</option>
                            <option value="fresh_produce">Fresh Produce</option>
                            <option value="cultural_items">Cultural Items</option>
                            <option value="traditional_clothing">Traditional Clothing</option>
                            <option value="accessories">Accessories</option>
                            <option value="local_brands">Local Brands</option>
                            <option value="electronics">Electronics</option>
                            <option value="beauty_products">Beauty Products</option>
                            <option value="food_items">Food Items</option>
                            <option value="other">Other</option>
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label>Short Description *</label>
                    <input type="text" name="short_description" id="edit_short_description" maxlength="500" required>
                </div>
                <div class="form-group">
                    <label>Full Description</label>
                    <textarea name="description" id="edit_description" rows="4"></textarea>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label>Price (GHS) *</label>
                        <input type="number" name="price" id="edit_price" step="0.01" min="0" required>
                    </div>
                    <div class="form-group">
                        <label>Stock Quantity *</label>
                        <input type="number" name="stock_quantity" id="edit_stock_quantity" min="0" required>
                    </div>
                </div>
                <div class="form-group">
                    <label>Product Image (leave empty to keep current)</label>
                    <input type="file" name="product_image" accept="image/*">
                </div>
                <div class="form-group">
                    <label>
                        <input type="checkbox" name="featured" id="edit_featured" value="1">
                        Featured Product
                    </label>
                </div>
                <div style="display: flex; gap: 10px; justify-content: flex-end;">
                    <button type="button" onclick="closeModal('editProductModal')" class="btn btn-secondary">Cancel</button>
                    <button type="submit" class="btn btn-primary">Update Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Define modal functions immediately in global scope
        function openAddProductModal() {
            const modal = document.getElementById('addProductModal');
            if (modal) {
                modal.classList.add('active');
            } else {
                console.error('Add product modal not found');
            }
        }
        
        function closeModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal) {
                modal.classList.remove('active');
            }
        }
        
        // Also attach to window for extra safety
        window.openAddProductModal = openAddProductModal;
        window.closeModal = closeModal;

        // Override with full implementation once page loads
        window.openEditProductModal = function(productId) {
            // Get product data from PHP
            const products = <?= json_encode($products ?? []) ?>;
            const product = products.find(p => p.id == productId);
            
            if (product) {
                const editProductId = document.getElementById('edit_product_id');
                const editName = document.getElementById('edit_name');
                const editCategory = document.getElementById('edit_category');
                const editShortDescription = document.getElementById('edit_short_description');
                const editDescription = document.getElementById('edit_description');
                const editPrice = document.getElementById('edit_price');
                const editStockQuantity = document.getElementById('edit_stock_quantity');
                const editFeatured = document.getElementById('edit_featured');
                
                if (editProductId) editProductId.value = product.id;
                if (editName) editName.value = product.name || '';
                if (editCategory) editCategory.value = product.category || '';
                if (editShortDescription) editShortDescription.value = product.short_description || '';
                if (editDescription) editDescription.value = product.description || '';
                if (editPrice) editPrice.value = product.price || 0;
                if (editStockQuantity) editStockQuantity.value = product.stock_quantity || 0;
                if (editFeatured) editFeatured.checked = product.featured == 1;
                
                const modal = document.getElementById('editProductModal');
                if (modal) {
                    modal.classList.add('active');
                } else {
                    console.error('Edit product modal not found');
                }
            } else {
                console.error('Product not found:', productId);
            }
        };
        
        // Override delete function with full implementation
        window.deleteProduct = function(productId) {
            if (confirm('Are you sure you want to delete this product? This action cannot be undone.')) {
                const form = document.createElement('form');
                form.method = 'POST';
                form.innerHTML = `
                    <input type="hidden" name="action" value="delete_product">
                    <input type="hidden" name="product_id" value="${productId}">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        };

        function toggleSelectAll() {
            const selectAll = document.getElementById('selectAll');
            const checkboxes = document.querySelectorAll('.product-checkbox');
            checkboxes.forEach(cb => cb.checked = selectAll.checked);
            updateBulkActions();
        }

        function updateBulkActions() {
            const checkboxes = document.querySelectorAll('.product-checkbox:checked');
            const count = checkboxes.length;
            const bulkForm = document.getElementById('bulkForm');
            
            if (count > 0) {
                bulkForm.style.display = 'block';
                document.getElementById('selectedCount').textContent = count + ' selected';
                
                // Update form with selected IDs
                const productIds = Array.from(checkboxes).map(cb => cb.value);
                bulkForm.innerHTML = `
                    <input type="hidden" name="action" id="bulkAction" value="">
                    <div class="bulk-actions">
                        <input type="checkbox" id="selectAll" onchange="toggleSelectAll()">
                        <span id="selectedCount">${count} selected</span>
                        <button type="submit" onclick="setBulkAction('bulk_featured')" class="btn btn-sm btn-secondary">
                            <i class="fas fa-star"></i> Mark Featured
                        </button>
                        <button type="submit" onclick="setBulkAction('bulk_delete')" class="btn btn-sm btn-danger">
                            <i class="fas fa-trash"></i> Delete Selected
                        </button>
                    </div>
                `;
                productIds.forEach(id => {
                    const input = document.createElement('input');
                    input.type = 'hidden';
                    input.name = 'product_ids[]';
                    input.value = id;
                    bulkForm.appendChild(input);
                });
            } else {
                bulkForm.style.display = 'none';
            }
        }

        function setBulkAction(action) {
            document.getElementById('bulkAction').value = action;
            if (action === 'bulk_delete') {
                const count = document.querySelectorAll('.product-checkbox:checked').length;
                if (!confirm(`Are you sure you want to delete ${count} product(s)? This cannot be undone.`)) {
                    return false;
                }
            }
            return true;
        }

        // Close modals on outside click
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.classList.remove('active');
                }
            });
        });
    });

    function updateOrderStatus(orderId, status) {
        if (!confirm('Are you sure you want to update this order status?')) {
            location.reload();
            return;
        }
        
        const form = document.createElement('form');
        form.method = 'POST';
        form.innerHTML = `
            <input type="hidden" name="action" value="update_order_status">
            <input type="hidden" name="order_id" value="${orderId}">
            <input type="hidden" name="status" value="${status}">
        `;
        document.body.appendChild(form);
        form.submit();
    }

    function filterOrders() {
        const statusFilter = document.getElementById('orderStatusFilter')?.value.toLowerCase() || '';
        const searchTerm = document.getElementById('orderSearch')?.value.toLowerCase() || '';
        const rows = document.querySelectorAll('.orders-table tbody tr');
        
        rows.forEach(row => {
            const select = row.querySelector('select');
            if (!select) return;
            
            const status = select.value.toLowerCase();
            const text = row.textContent.toLowerCase();
            
            const statusMatch = !statusFilter || status === statusFilter;
            const searchMatch = !searchTerm || text.includes(searchTerm);
            
            row.style.display = (statusMatch && searchMatch) ? '' : 'none';
        });
    }
    </script>
</body>
</html>
