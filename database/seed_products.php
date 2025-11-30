<?php
/**
 * Product Seed Script
 * Adds sample products to the e-commerce system
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../includes/database.php';
require_once __DIR__ . '/../includes/logger.php';

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Seed Products - Gulio</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: #28a745; }
        .error { color: #dc3545; }
        .warning { color: #ffc107; }
        .btn { padding: 10px 20px; background: #FF6B35; color: white; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #e55a2b; }
        pre { background: #f8f9fa; padding: 15px; border-radius: 4px; overflow-x: auto; }
    </style>
</head>
<body>
    <h1>🛍️ Seed Products for Gulio Marketplace</h1>
    
    <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seed_products'])): ?>
        <div class="results">
            <h2>Seeding Products...</h2>
            
            <?php
            try {
                $db = Database::getInstance();
                
                // Check if products table exists
                $tables = $db->query("SHOW TABLES LIKE 'products'");
                if (empty($tables)) {
                    throw new Exception('Products table does not exist. Please run setup.php first to create the database tables.');
                }
                
                // Clear existing products if requested
                if (isset($_POST['clear_existing'])) {
                    echo '<p>Clearing existing products...</p>';
                    $db->execute("DELETE FROM product_reviews");
                    $db->execute("DELETE FROM wishlist");
                    $db->execute("DELETE FROM shopping_cart");
                    $db->execute("DELETE FROM order_items");
                    $db->execute("DELETE FROM orders");
                    $db->execute("DELETE FROM product_variants");
                    $db->execute("DELETE FROM products");
                    echo '<p class="success">✓ Existing products cleared</p>';
                }
                
                // Get existing vendors
                $vendors = $db->query("SELECT id, name, category FROM vendors WHERE status = 'active' LIMIT 10");
                if (empty($vendors)) {
                    throw new Exception('No vendors found. Please run the main seed script first to create vendors.');
                }
                
                echo '<p>Found ' . count($vendors) . ' vendors to add products for...</p>';
                
                // Sample products for each vendor
                $sampleProducts = [
                    'barber' => [
                        ['name' => 'Premium Hair Pomade', 'description' => 'Locally made hair pomade with shea butter and natural oils. Perfect for maintaining traditional Ghanaian hairstyles.', 'price' => 25.00, 'category' => 'beauty', 'cultural_context' => 'Shea butter is a traditional Ghanaian ingredient known for its moisturizing properties.'],
                        ['name' => 'Traditional Hair Pick', 'description' => 'Handcrafted wooden hair pick made from local hardwood. Essential tool for natural hair care.', 'price' => 15.00, 'category' => 'accessories', 'cultural_context' => 'Hair picks are important tools in African hair care traditions.'],
                        ['name' => 'Beard Oil Set', 'description' => 'Natural beard oil made with coconut oil and local herbs. Keeps beards healthy and well-groomed.', 'price' => 35.00, 'category' => 'beauty', 'cultural_context' => 'Made with traditional Ghanaian herbs used for centuries in grooming.']
                    ],
                    'tailor' => [
                        ['name' => 'Kente Cloth (Authentic)', 'description' => 'Hand-woven Kente cloth from Bonwire village. Each pattern tells a unique story and represents Akan culture.', 'price' => 150.00, 'category' => 'textiles', 'cultural_context' => 'Kente is the most famous Ghanaian textile, originally worn by royalty. Each color and pattern has specific meaning.'],
                        ['name' => 'Adinkra Symbols Fabric', 'description' => 'Traditional Adinkra printed fabric with symbolic meanings. Perfect for cultural ceremonies and events.', 'price' => 45.00, 'category' => 'textiles', 'cultural_context' => 'Adinkra symbols represent concepts and aphorisms in Akan culture. Each symbol has deep philosophical meaning.'],
                        ['name' => 'Dashiki Shirt (Ready-made)', 'description' => 'Beautifully tailored dashiki shirt in traditional patterns. Comfortable and culturally significant.', 'price' => 65.00, 'category' => 'traditional-clothing', 'cultural_context' => 'Dashiki represents African identity and pride, worn across West Africa.']
                    ],
                    'food' => [
                        ['name' => 'Shito (Pepper Sauce)', 'description' => 'Authentic Ghanaian shito made with dried fish, shrimp, and local peppers. Essential condiment for any meal.', 'price' => 12.00, 'category' => 'food-spices', 'cultural_context' => 'Shito is Ghana\'s most popular condiment, found on every dining table. Each family has their secret recipe.'],
                        ['name' => 'Palm Nut Soup Mix', 'description' => 'Pre-made palm nut soup base with authentic spices. Just add meat and vegetables for a traditional meal.', 'price' => 18.00, 'category' => 'food-spices', 'cultural_context' => 'Palm nut soup is a staple dish in Ghana, especially popular in the Central and Western regions.'],
                        ['name' => 'Kelewele Spice Blend', 'description' => 'Perfect spice mix for making kelewele (spiced fried plantain). Includes ginger, pepper, and traditional spices.', 'price' => 8.00, 'category' => 'food-spices', 'cultural_context' => 'Kelewele is a beloved Ghanaian street food, especially popular as an evening snack.']
                    ],
                    'phone_repair' => [
                        ['name' => 'Phone Case (Ghana Flag)', 'description' => 'Protective phone case featuring Ghana flag design. Shows your pride while protecting your device.', 'price' => 20.00, 'category' => 'accessories', 'cultural_context' => 'Displaying the Ghana flag shows national pride and helps newcomers connect with local culture.'],
                        ['name' => 'Adinkra Phone Grip', 'description' => 'Phone grip featuring traditional Adinkra symbols. Functional and culturally meaningful.', 'price' => 12.00, 'category' => 'accessories', 'cultural_context' => 'Adinkra symbols on everyday items help preserve and share Ghanaian cultural knowledge.'],
                        ['name' => 'Portable Power Bank', 'description' => 'Reliable power bank perfect for Ghana\'s power challenges. Keep your devices charged anywhere.', 'price' => 45.00, 'category' => 'electronics', 'cultural_context' => 'Power banks are essential in Ghana due to occasional power outages, locally called "dumsor".']
                    ],
                    'thrift' => [
                        ['name' => 'Vintage Ankara Bag', 'description' => 'Upcycled bag made from vintage Ankara fabric. Unique piece with sustainable fashion appeal.', 'price' => 35.00, 'category' => 'accessories', 'cultural_context' => 'Ankara fabric, though originally Indonesian, has become integral to West African fashion culture.'],
                        ['name' => 'Traditional Beads Set', 'description' => 'Collection of traditional Ghanaian beads. Each bead type has cultural significance and history.', 'price' => 28.00, 'category' => 'accessories', 'cultural_context' => 'Beads in Ghana represent wealth, status, and spiritual protection. Different regions have distinct bead-making traditions.'],
                        ['name' => 'Carved Wooden Mask', 'description' => 'Authentic carved mask representing traditional Ghanaian spirits and ancestors.', 'price' => 85.00, 'category' => 'art', 'cultural_context' => 'Masks are used in traditional ceremonies to connect with ancestors and spirits in Ghanaian culture.']
                    ]
                ];
                
                $productsCreated = 0;
                
                foreach ($vendors as $vendor) {
                    $vendorCategory = $vendor['category'];
                    $vendorProducts = $sampleProducts[$vendorCategory] ?? $sampleProducts['food']; // Default to food products
                    
                    echo '<p>Adding products for ' . htmlspecialchars($vendor['name']) . ' (' . $vendorCategory . ')...</p>';
                    
                    foreach ($vendorProducts as $productData) {
                        // Add some variation to prices
                        $basePrice = $productData['price'];
                        $price = $basePrice + (rand(-5, 10)); // Add some price variation
                        
                        $product = [
                            'vendor_id' => $vendor['id'],
                            'name' => $productData['name'],
                            'description' => $productData['description'],
                            'short_description' => substr($productData['description'], 0, 200),
                            'category' => $productData['category'],
                            'price' => $price,
                            'compare_price' => rand(0, 1) ? $price + rand(5, 20) : null, // Sometimes add discount
                            'stock_quantity' => rand(5, 50),
                            'low_stock_threshold' => 5,
                            'sku' => 'GUL-' . strtoupper(substr($productData['name'], 0, 3)) . '-' . $vendor['id'] . '-' . rand(100, 999),
                            'status' => 'active',
                            'featured' => rand(0, 1) ? 1 : 0, // 50% chance of being featured
                            'cultural_context' => $productData['cultural_context'],
                            'tags' => json_encode(['ghanaian', 'authentic', 'local', $vendorCategory]),
                            'images' => json_encode(['/assets/images/products/placeholder-' . $productData['category'] . '.jpg']),
                            'materials' => 'Natural materials sourced locally in Ghana',
                            'care_instructions' => 'Handle with care. Store in cool, dry place.',
                            'origin_story' => 'Crafted by local artisans in Accra, Ghana using traditional methods passed down through generations.'
                        ];
                        
                        $productId = $db->insert('products', $product);
                        
                        if ($productId) {
                            $productsCreated++;
                            
                            // Add some product variants for certain items
                            if (in_array($productData['category'], ['traditional-clothing', 'accessories'])) {
                                $sizes = ['S', 'M', 'L', 'XL'];
                                foreach ($sizes as $size) {
                                    $variant = [
                                        'product_id' => $productId,
                                        'name' => 'Size ' . $size,
                                        'sku' => $product['sku'] . '-' . $size,
                                        'stock_quantity' => rand(2, 15),
                                        'attributes' => json_encode(['size' => $size]),
                                        'status' => 'active'
                                    ];
                                    $db->insert('product_variants', $variant);
                                }
                            }
                        }
                    }
                }
                
                echo '<p class="success">✓ Successfully created ' . $productsCreated . ' products!</p>';
                echo '<p class="success">✓ Product seeding completed successfully!</p>';
                
                echo '<h3>What was created:</h3>';
                echo '<ul>';
                echo '<li>' . $productsCreated . ' products across different categories</li>';
                echo '<li>Product variants for clothing and accessories</li>';
                echo '<li>Cultural context for each product to help newcomers</li>';
                echo '<li>Authentic Ghanaian products with local significance</li>';
                echo '</ul>';
                
                echo '<p><strong>You can now visit:</strong></p>';
                echo '<ul>';
                echo '<li><a href="../?page=products">Products Page</a> - Browse the marketplace</li>';
                echo '<li><a href="../?page=home">Home Page</a> - See featured products</li>';
                echo '</ul>';
                
            } catch (Exception $e) {
                echo '<p class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                Logger::error('Product seeding failed', ['error' => $e->getMessage()]);
            }
            ?>
        </div>
    <?php else: ?>
        <div class="setup-form">
            <p>This script will populate your database with sample products for the Gulio marketplace.</p>
            
            <h3>What will be created:</h3>
            <ul>
                <li><strong>Authentic Ghanaian Products</strong> - Kente cloth, shito sauce, traditional beads, etc.</li>
                <li><strong>Cultural Context</strong> - Each product includes explanations for newcomers</li>
                <li><strong>Product Categories</strong> - Artisan crafts, textiles, food, beauty products, etc.</li>
                <li><strong>Product Variants</strong> - Sizes and variations for clothing and accessories</li>
                <li><strong>Local Vendor Integration</strong> - Products linked to existing service vendors</li>
            </ul>
            
            <form method="POST">
                <div style="margin: 20px 0;">
                    <label>
                        <input type="checkbox" name="clear_existing" value="1">
                        Clear existing products first (recommended for fresh start)
                    </label>
                </div>
                
                <button type="submit" name="seed_products" class="btn">
                    🌱 Seed Products Database
                </button>
            </form>
            
            <div style="margin-top: 30px; padding: 15px; background: #f8f9fa; border-radius: 4px;">
                <h4>Prerequisites:</h4>
                <ul>
                    <li>Database tables must be created (run <a href="../setup.php">setup.php</a> first)</li>
                    <li>Vendors must exist (run main seed script first)</li>
                    <li>Products table must be empty or you should check "Clear existing products"</li>
                </ul>
            </div>
        </div>
    <?php endif; ?>
</body>
</html>
