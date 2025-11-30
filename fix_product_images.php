<?php
/**
 * Fix Product Images - Update with Correct Matching Images
 */

// Load configuration and includes
require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/database.php';

try {
    $db = Database::getInstance();
    
    echo "<h2>🖼️ Fixing Product Images - Matching Real Images...</h2>";
    
    // Correct image mappings for each product
    $imageUpdates = [
        'Kente Cloth Traditional Scarf' => 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=400&h=400&fit=crop&q=80',
        'Shea Butter Natural Skincare' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=400&h=400&fit=crop&q=80',
        'Adinkra Symbol Wall Art' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=400&fit=crop&q=80',
        'Plantain Chips - Spicy' => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&h=400&fit=crop&q=80',
        'Handwoven Basket Set' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=400&h=400&fit=crop&q=80',
        'African Print Face Masks' => 'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=400&h=400&fit=crop&q=80',
        'Cocoa Powder - Premium Grade' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&h=400&fit=crop&q=80',
        'Beaded Jewelry Set' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=400&h=400&fit=crop&q=80',
        'Traditional Djembe Drum' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=400&h=400&fit=crop&q=80',
        'Moringa Powder Superfood' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&h=400&fit=crop&q=80',
        'Batik Print Fabric' => 'https://images.unsplash.com/photo-1582582621959-48d27397dc69?w=400&h=400&fit=crop&q=80',
        'Wooden Carved Mask' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=400&fit=crop&q=80'
    ];
    
    // Better matching images for Ghanaian products
    $betterImages = [
        'Kente Cloth Traditional Scarf' => 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=400&h=400&fit=crop&q=80', // Actual kente cloth
        'Shea Butter Natural Skincare' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=400&h=400&fit=crop&q=80', // Shea butter jar
        'Adinkra Symbol Wall Art' => 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=400&h=400&fit=crop&q=80', // African art/symbols
        'Plantain Chips - Spicy' => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&h=400&fit=crop&q=80', // Plantain chips
        'Handwoven Basket Set' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=400&h=400&fit=crop&q=80', // Woven baskets
        'African Print Face Masks' => 'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=400&h=400&fit=crop&q=80', // African print masks
        'Cocoa Powder - Premium Grade' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&h=400&fit=crop&q=80', // Cocoa powder
        'Beaded Jewelry Set' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=400&h=400&fit=crop&q=80', // African beaded jewelry
        'Traditional Djembe Drum' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=400&h=400&fit=crop&q=80', // Djembe drum
        'Moringa Powder Superfood' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&h=400&fit=crop&q=80', // Green powder
        'Batik Print Fabric' => 'https://images.unsplash.com/photo-1582582621959-48d27397dc69?w=400&h=400&fit=crop&q=80', // African fabric
        'Wooden Carved Mask' => 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=400&h=400&fit=crop&q=80' // African wooden mask
    ];
    
    // Even better - let's use more specific searches
    $specificImages = [
        'Kente Cloth Traditional Scarf' => 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=400&h=400&fit=crop&q=80',
        'Shea Butter Natural Skincare' => 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=400&h=400&fit=crop&q=80',
        'Adinkra Symbol Wall Art' => 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=400&h=400&fit=crop&q=80',
        'Plantain Chips - Spicy' => 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&h=400&fit=crop&q=80',
        'Handwoven Basket Set' => 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=400&h=400&fit=crop&q=80',
        'African Print Face Masks' => 'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=400&h=400&fit=crop&q=80',
        'Cocoa Powder - Premium Grade' => 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&h=400&fit=crop&q=80',
        'Beaded Jewelry Set' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=400&h=400&fit=crop&q=80',
        'Traditional Djembe Drum' => 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=400&h=400&fit=crop&q=80',
        'Moringa Powder Superfood' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&h=400&fit=crop&q=80'
    ];
    
    $updateSql = "UPDATE products SET image_url = ? WHERE name = ?";
    $count = 0;
    
    foreach ($specificImages as $productName => $imageUrl) {
        $result = $db->query($updateSql, [$imageUrl, $productName]);
        if ($result) {
            $count++;
            echo "<p>✅ Updated image for: " . htmlspecialchars($productName) . "</p>";
        } else {
            echo "<p>❌ Failed to update: " . htmlspecialchars($productName) . "</p>";
        }
    }
    
    echo "<h3>🎉 Updated {$count} product images with matching photos!</h3>";
    echo "<p><a href='?page=products' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Updated Products</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
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
