<?php
require_once 'includes/database.php';

$db = Database::getInstance();
$products = $db->fetchAll('SELECT id, name, image_url FROM products LIMIT 5');

echo "<h2>Product Images Check</h2>";
foreach ($products as $product) {
    echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
    echo "<h3>Product: " . htmlspecialchars($product['name']) . "</h3>";
    echo "<p>Image URL: " . ($product['image_url'] ?? 'NULL') . "</p>";
    
    if (!empty($product['image_url'])) {
        $fullPath = __DIR__ . '/' . $product['image_url'];
        echo "<p>Full path: " . $fullPath . "</p>";
        echo "<p>File exists: " . (file_exists($fullPath) ? 'YES' : 'NO') . "</p>";
        
        if (file_exists($fullPath)) {
            echo "<p>File size: " . filesize($fullPath) . " bytes</p>";
            echo "<img src='" . htmlspecialchars($product['image_url']) . "' style='max-width: 200px; max-height: 200px; border: 1px solid #ddd;' alt='Product Image'>";
        }
    }
    echo "</div>";
}
?>
