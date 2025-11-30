<?php
/**
 * Create Product Categories Table and Data
 */

// Load configuration and includes
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/database.php';

try {
    $db = Database::getInstance();
    
    echo "<h2>🏷️ Setting up Product Categories...</h2>";
    
    // Create product_categories table if it doesn't exist
    $createCategoriesTable = "
    CREATE TABLE IF NOT EXISTS product_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) NOT NULL UNIQUE,
        description TEXT,
        icon VARCHAR(50),
        parent_id INT DEFAULT NULL,
        sort_order INT DEFAULT 0,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (parent_id) REFERENCES product_categories(id) ON DELETE SET NULL
    )";
    
    $db->query($createCategoriesTable);
    echo "<p>✅ Product categories table created/verified</p>";
    
    // Insert categories
    $categories = [
        ['name' => 'Artisan Crafts', 'slug' => 'artisan_crafts', 'description' => 'Handmade crafts by local artisans', 'icon' => 'fas fa-hammer'],
        ['name' => 'Textiles & Fabrics', 'slug' => 'textiles', 'description' => 'Traditional fabrics and textiles', 'icon' => 'fas fa-tshirt'],
        ['name' => 'Art & Sculptures', 'slug' => 'art', 'description' => 'Local art and sculptures', 'icon' => 'fas fa-palette'],
        ['name' => 'Fresh Produce', 'slug' => 'fresh_produce', 'description' => 'Fresh local produce and ingredients', 'icon' => 'fas fa-apple-alt'],
        ['name' => 'Cultural Items', 'slug' => 'cultural_items', 'description' => 'Traditional cultural items', 'icon' => 'fas fa-mask'],
        ['name' => 'Traditional Clothing', 'slug' => 'traditional_clothing', 'description' => 'Traditional Ghanaian clothing', 'icon' => 'fas fa-user-tie'],
        ['name' => 'Accessories', 'slug' => 'accessories', 'description' => 'Jewelry, bags, and accessories', 'icon' => 'fas fa-gem'],
        ['name' => 'Local Brands', 'slug' => 'local_brands', 'description' => 'Products from local Ghanaian brands', 'icon' => 'fas fa-store'],
        ['name' => 'Beauty & Personal Care', 'slug' => 'beauty_products', 'description' => 'Natural beauty and skincare products', 'icon' => 'fas fa-spa'],
        ['name' => 'Food & Spices', 'slug' => 'food_items', 'description' => 'Local food items and spices', 'icon' => 'fas fa-pepper-hot']
    ];
    
    $insertCategorySql = "INSERT IGNORE INTO product_categories (name, slug, description, icon, sort_order) VALUES (?, ?, ?, ?, ?)";
    
    $count = 0;
    foreach ($categories as $index => $category) {
        $params = [
            $category['name'],
            $category['slug'],
            $category['description'],
            $category['icon'],
            $index + 1
        ];
        
        $result = $db->query($insertCategorySql, $params);
        if ($result) {
            $count++;
            echo "<p>✅ Added category: " . htmlspecialchars($category['name']) . "</p>";
        }
    }
    
    echo "<h3>🎉 Success! Added {$count} product categories.</h3>";
    echo "<p><a href='populate_products.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>Next: Populate Products</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
    echo "<p>Database error occurred. Please check your database connection.</p>";
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
