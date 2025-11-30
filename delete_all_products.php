<?php
/**
 * Delete All Products Script
 * This will clear all products from the database
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/database.php';

try {
    $db = Database::getInstance();
    
    echo "<h2>🗑️ Deleting All Products...</h2>";
    
    // First, show current products
    $currentProducts = $db->fetchAll("SELECT id, name FROM products");
    echo "<h3>Current Products in Database:</h3>";
    
    if (empty($currentProducts)) {
        echo "<p>No products found in database.</p>";
    } else {
        echo "<p>Found " . count($currentProducts) . " products:</p>";
        foreach ($currentProducts as $product) {
            echo "<p>- ID: {$product['id']} | Name: " . htmlspecialchars($product['name']) . "</p>";
        }
    }
    
    // Delete all products
    echo "<h3>Deleting Products...</h3>";
    $deleteResult = $db->query("DELETE FROM products");
    
    if ($deleteResult) {
        echo "<p>✅ Successfully deleted all products!</p>";
        
        // Reset auto-increment
        $resetResult = $db->query("ALTER TABLE products AUTO_INCREMENT = 1");
        if ($resetResult) {
            echo "<p>✅ Reset product ID counter to start from 1</p>";
        }
        
        // Verify deletion
        $remainingProducts = $db->fetchAll("SELECT COUNT(*) as count FROM products");
        $count = $remainingProducts[0]['count'];
        
        if ($count == 0) {
            echo "<h3>🎉 SUCCESS! All products have been deleted!</h3>";
            echo "<p>The products table is now empty and ready for new products.</p>";
        } else {
            echo "<h3>⚠️ Warning: {$count} products still remain in database</h3>";
        }
        
    } else {
        echo "<p>❌ Failed to delete products</p>";
    }
    
    echo "<hr>";
    echo "<h3>What's Next?</h3>";
    echo "<p><a href='admin/' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>Go to Admin Panel</a>";
    echo "<a href='?page=products' style='background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>View Products Page</a></p>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
    echo "<p>Could not connect to database or execute deletion.</p>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f8f9fa;
    line-height: 1.6;
}

h2, h3 {
    color: #333;
    border-bottom: 2px solid #eee;
    padding-bottom: 10px;
}

p {
    margin: 10px 0;
    padding: 8px;
    background: white;
    border-radius: 5px;
    border-left: 4px solid #007bff;
}

hr {
    margin: 30px 0;
    border: none;
    border-top: 2px solid #eee;
}

a {
    display: inline-block;
    margin: 5px;
}
</style>
