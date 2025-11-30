<?php
/**
 * Fix Database Schema - Add Missing Columns
 */

// Load configuration and includes
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/database.php';

try {
    $db = Database::getInstance();
    
    echo "<h2>🔧 Fixing Database Schema...</h2>";
    
    // Add missing columns to products table
    $alterProductsQueries = [
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS image_url VARCHAR(500) DEFAULT NULL AFTER status",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER image_url",
        "ALTER TABLE products ADD COLUMN IF NOT EXISTS updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER created_at"
    ];
    
    foreach ($alterProductsQueries as $query) {
        try {
            $db->query($query);
            echo "<p>✅ Products table updated successfully</p>";
        } catch (Exception $e) {
            // Column might already exist, try alternative approach
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "<p>ℹ️ Column already exists in products table</p>";
            } else {
                echo "<p>⚠️ Products table update: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
    
    // Add missing columns to product_categories table  
    $alterCategoriesQueries = [
        "ALTER TABLE product_categories ADD COLUMN IF NOT EXISTS icon VARCHAR(50) DEFAULT NULL AFTER description",
        "ALTER TABLE product_categories ADD COLUMN IF NOT EXISTS sort_order INT DEFAULT 0 AFTER icon",
        "ALTER TABLE product_categories ADD COLUMN IF NOT EXISTS is_active BOOLEAN DEFAULT TRUE AFTER sort_order"
    ];
    
    foreach ($alterCategoriesQueries as $query) {
        try {
            $db->query($query);
            echo "<p>✅ Product categories table updated successfully</p>";
        } catch (Exception $e) {
            if (strpos($e->getMessage(), 'Duplicate column') !== false) {
                echo "<p>ℹ️ Column already exists in product_categories table</p>";
            } else {
                echo "<p>⚠️ Categories table update: " . htmlspecialchars($e->getMessage()) . "</p>";
            }
        }
    }
    
    echo "<h3>🎉 Database schema updated successfully!</h3>";
    echo "<p><a href='populate_products_fixed.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Next: Populate Products</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
    echo "<p>Database connection error occurred.</p>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f5f5f5;
}
h2, h3 {
    color: #333;
}
p {
    margin: 10px 0;
}
</style>
