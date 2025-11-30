<?php
/**
 * DIRECT IMAGE FIX - No more fake images!
 * This will directly update the database with proper matching images
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/database.php';

try {
    $db = Database::getInstance();
    
    echo "<h2>🔧 DIRECT FIX - Updating Images Right Now...</h2>";
    
    // Get all current products first
    $products = $db->fetchAll("SELECT id, name FROM products ORDER BY id");
    
    echo "<h3>Current Products in Database:</h3>";
    foreach ($products as $product) {
        echo "<p>ID: {$product['id']} - Name: " . htmlspecialchars($product['name']) . "</p>";
    }
    
    // Direct SQL updates with REAL matching images
    $directUpdates = [
        // Update by product name with REAL images
        "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=400&h=400&fit=crop&q=80' WHERE name LIKE '%Kente%'",
        "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=400&h=400&fit=crop&q=80' WHERE name LIKE '%Shea%'",
        "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1578985545062-69928b1d9587?w=400&h=400&fit=crop&q=80' WHERE name LIKE '%Cocoa%'",
        "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1493225457124-a3eb161ffa5f?w=400&h=400&fit=crop&q=80' WHERE name LIKE '%Djembe%' OR name LIKE '%Drum%'",
        "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1586023492125-27b2c045efd7?w=400&h=400&fit=crop&q=80' WHERE name LIKE '%Basket%'",
        "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1566478989037-eec170784d0b?w=400&h=400&fit=crop&q=80' WHERE name LIKE '%Plantain%'",
        "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=400&h=400&fit=crop&q=80' WHERE name LIKE '%Bead%' OR name LIKE '%Jewelry%'",
        "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1598300042247-d088f8ab3a91?w=400&h=400&fit=crop&q=80' WHERE name LIKE '%Mask%' AND name LIKE '%Face%'",
        "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1541961017774-22349e4a1262?w=400&h=400&fit=crop&q=80' WHERE name LIKE '%Adinkra%' OR (name LIKE '%Mask%' AND name NOT LIKE '%Face%')",
        "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&h=400&fit=crop&q=80' WHERE name LIKE '%Moringa%'",
        "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1582582621959-48d27397dc69?w=400&h=400&fit=crop&q=80' WHERE name LIKE '%Batik%' OR name LIKE '%Fabric%'"
    ];
    
    echo "<h3>Applying Direct Updates:</h3>";
    $totalUpdated = 0;
    
    foreach ($directUpdates as $sql) {
        try {
            $result = $db->query($sql);
            if ($result) {
                echo "<p>✅ Applied: " . htmlspecialchars($sql) . "</p>";
                $totalUpdated++;
            }
        } catch (Exception $e) {
            echo "<p>❌ Failed: " . htmlspecialchars($e->getMessage()) . "</p>";
        }
    }
    
    // Also update any remaining products with a default image
    $defaultUpdate = "UPDATE products SET image_url = 'https://images.unsplash.com/photo-1556228720-195a672e8a03?w=400&h=400&fit=crop&q=80' WHERE image_url IS NULL OR image_url = ''";
    $db->query($defaultUpdate);
    
    echo "<h3>🎉 DONE! Applied {$totalUpdated} direct updates!</h3>";
    
    // Show updated products
    $updatedProducts = $db->fetchAll("SELECT id, name, image_url FROM products ORDER BY id");
    echo "<h3>Updated Products:</h3>";
    foreach ($updatedProducts as $product) {
        echo "<p><strong>" . htmlspecialchars($product['name']) . "</strong><br>";
        echo "Image: " . htmlspecialchars($product['image_url']) . "</p>";
    }
    
    echo "<p><a href='?page=products' style='background: #28a745; color: white; padding: 15px 30px; text-decoration: none; border-radius: 5px; font-weight: bold;'>CHECK PRODUCTS NOW - IMAGES SHOULD BE FIXED!</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
?>

<style>
body {
    font-family: Arial, sans-serif;
    max-width: 1000px;
    margin: 20px auto;
    padding: 20px;
    background: #f5f5f5;
}
h2, h3 { color: #333; }
p { margin: 8px 0; padding: 5px; background: white; border-radius: 3px; }
</style>
