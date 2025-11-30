<?php
/**
 * Direct Product Population Script
 * Run this to immediately add sample products with real images
 */

// Load configuration and includes
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/database.php';

try {
    $db = Database::getInstance();
    
    echo "<h2>🚀 Populating Products Database...</h2>";
    
    // Sample products with real image URLs
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
        ],
        [
            'name' => 'Traditional Djembe Drum',
            'description' => 'Authentic hand-carved djembe drum made by skilled artisans. Perfect for music lovers and cultural enthusiasts.',
            'short_description' => 'Hand-carved traditional djembe drum',
            'category' => 'artisan_crafts',
            'price' => 85.00,
            'stock' => 6,
            'image' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=400&h=400&fit=crop',
            'featured' => 1
        ],
        [
            'name' => 'Moringa Powder Superfood',
            'description' => 'Organic moringa powder packed with nutrients. Known as the "miracle tree" for its health benefits.',
            'short_description' => 'Organic moringa superfood powder',
            'category' => 'food_items',
            'price' => 16.99,
            'stock' => 35,
            'image' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&h=400&fit=crop',
            'featured' => 0
        ],
        [
            'name' => 'Batik Print Fabric',
            'description' => 'Beautiful batik print fabric perfect for clothing, home decor, or craft projects. Authentic African patterns.',
            'short_description' => 'Authentic batik print fabric by the yard',
            'category' => 'textiles',
            'price' => 12.50,
            'stock' => 40,
            'image' => 'https://images.unsplash.com/photo-1582582621959-48d27397dc69?w=400&h=400&fit=crop',
            'featured' => 0
        ],
        [
            'name' => 'Wooden Carved Mask',
            'description' => 'Traditional wooden mask carved by local artisans. Each mask represents different cultural meanings and stories.',
            'short_description' => 'Hand-carved traditional wooden mask',
            'category' => 'cultural_items',
            'price' => 55.00,
            'stock' => 10,
            'image' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=400&fit=crop',
            'featured' => 1
        ]
    ];
    
    $sql = "INSERT INTO products (vendor_id, name, description, short_description, category, price, stock_quantity, image_url, status, featured, created_at) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', ?, NOW())";
    
    $count = 0;
    foreach ($sampleProducts as $product) {
        $params = [
            1, // Default vendor ID
            $product['name'],
            $product['description'],
            $product['short_description'],
            $product['category'],
            $product['price'],
            $product['stock'],
            $product['image'],
            $product['featured']
        ];
        
        $result = $db->query($sql, $params);
        if ($result) {
            $count++;
            echo "<p>✅ Added: " . htmlspecialchars($product['name']) . "</p>";
        } else {
            echo "<p>❌ Failed to add: " . htmlspecialchars($product['name']) . "</p>";
        }
    }
    
    echo "<h3>🎉 Success! Added {$count} products to the database.</h3>";
    echo "<p><a href='?page=products' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Products Page</a></p>";
    echo "<p><a href='admin/' style='background: #ffd700; color: #333; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-left: 10px;'>Go to Admin Panel</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
    echo "<p>Make sure your database is properly configured and the products table exists.</p>";
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
