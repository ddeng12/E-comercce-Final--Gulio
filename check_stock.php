<?php
require_once 'includes/database.php';

$db = Database::getInstance();
$products = $db->fetchAll('SELECT id, name, stock_quantity, status FROM products');

echo "<h2>Product Stock Levels</h2>";
foreach ($products as $product) {
    echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
    echo "<h3>Product ID: {$product['id']} - {$product['name']}</h3>";
    echo "<p>Stock Quantity: {$product['stock_quantity']}</p>";
    echo "<p>Status: {$product['status']}</p>";
    echo "</div>";
}
?>
