<?php
session_start();
require_once 'includes/database.php';

$db = Database::getInstance();

echo "<h2>Current Cart Contents</h2>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>User ID: " . ($_SESSION['user']['id'] ?? 'null') . "</p>";

$cartItems = $db->fetchAll('SELECT * FROM shopping_cart WHERE session_id = ? OR user_id = ?', [
    session_id(), 
    $_SESSION['user']['id'] ?? null
]);

if (empty($cartItems)) {
    echo "<p>Cart is empty</p>";
} else {
    foreach ($cartItems as $item) {
        echo "<div style='border: 1px solid #ccc; margin: 10px; padding: 10px;'>";
        echo "<p>Product ID: {$item['product_id']}</p>";
        echo "<p>Quantity: {$item['quantity']}</p>";
        echo "<p>Session ID: {$item['session_id']}</p>";
        echo "<p>User ID: " . ($item['user_id'] ?? 'null') . "</p>";
        echo "<p>Added: {$item['added_at']}</p>";
        echo "</div>";
    }
}

// Also check the product stock again
echo "<h2>Product Stock Check</h2>";
$product = $db->fetchOne('SELECT * FROM products WHERE id = 1');
echo "<p>Product: {$product['name']}</p>";
echo "<p>Stock: {$product['stock_quantity']}</p>";
echo "<p>Status: {$product['status']}</p>";
?>
