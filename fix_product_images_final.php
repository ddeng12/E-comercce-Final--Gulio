<?php
require_once 'includes/database.php';

$db = Database::getInstance();

// First, let's see what products we have
$products = $db->fetchAll('SELECT id, name, image_url FROM products');

echo "<h2>Current Products:</h2>";
foreach ($products as $product) {
    echo "<p>ID: {$product['id']} - Name: {$product['name']} - Image: " . ($product['image_url'] ?? 'NULL') . "</p>";
}

// Now let's create some real image URLs and update the database
$imageUpdates = [
    'Kente' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=400&fit=crop&crop=center',
    'Adinkra Cloth' => 'https://images.unsplash.com/photo-1594736797933-d0401ba2fe65?w=400&h=400&fit=crop&crop=center',
    'Wooden Mask' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=400&fit=crop&crop=center',
    'Beaded Jewelry' => 'https://images.unsplash.com/photo-1515562141207-7a88fb7ce338?w=400&h=400&fit=crop&crop=center',
    'Shea Butter' => 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?w=400&h=400&fit=crop&crop=center',
    'Palm Oil' => 'https://images.unsplash.com/photo-1474979266404-7eaacbcd87c5?w=400&h=400&fit=crop&crop=center',
    'Plantain Chips' => 'https://images.unsplash.com/photo-1578662996442-48f60103fc96?w=400&h=400&fit=crop&crop=center',
    'Gari' => 'https://images.unsplash.com/photo-1556909114-f6e7ad7d3136?w=400&h=400&fit=crop&crop=center'
];

echo "<h2>Updating Product Images:</h2>";

foreach ($products as $product) {
    $productName = $product['name'];
    
    // Find matching image URL
    $imageUrl = null;
    foreach ($imageUpdates as $name => $url) {
        if (stripos($productName, $name) !== false || stripos($name, $productName) !== false) {
            $imageUrl = $url;
            break;
        }
    }
    
    // If no specific match, use a generic product image
    if (!$imageUrl) {
        $imageUrl = 'https://images.unsplash.com/photo-1556228578-8c89e6adf883?w=400&h=400&fit=crop&crop=center';
    }
    
    // Update the database
    $db->query('UPDATE products SET image_url = ? WHERE id = ?', [$imageUrl, $product['id']]);
    
    echo "<p>Updated {$productName} with image: {$imageUrl}</p>";
}

echo "<h2>✅ All product images updated successfully!</h2>";
echo "<p><a href='?page=products'>View Products Page</a></p>";
?>
