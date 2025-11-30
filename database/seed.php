<?php
/**
 * Database Seed Script
 * Populates the database with sample data for testing
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../models/database.php';
require_once __DIR__ . '/../includes/security.php';
require_once __DIR__ . '/../includes/logger.php';
require_once __DIR__ . '/../models/auth.php';

// Only allow in development or if explicitly enabled
if (ENVIRONMENT === 'production' && !isset($_GET['force'])) {
    die('Seeding is disabled in production. Use force=1 parameter if you really need to run this.');
}

$db = Database::getInstance();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gulio Database Seeder</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background: #f5f5f5;
        }
        .container {
            background: white;
            padding: 30px;
            border-radius: 8px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        h1 {
            color: #FF6B35;
            margin-bottom: 20px;
        }
        .step {
            margin: 15px 0;
            padding: 10px;
            background: #f9f9f9;
            border-left: 4px solid #4ECDC4;
        }
        .success {
            color: #28a745;
            font-weight: bold;
        }
        .error {
            color: #dc3545;
            font-weight: bold;
        }
        button {
            background: #FF6B35;
            color: white;
            border: none;
            padding: 12px 24px;
            border-radius: 4px;
            cursor: pointer;
            font-size: 16px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🌱 Gulio Database Seeder</h1>
        
        <?php
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['seed_data'])) {
            echo '<div class="step">';
            echo '<h2>Seeding Database...</h2>';
            
            try {
                // Clear existing data (optional)
                if (isset($_POST['clear_existing'])) {
                    echo '<p>Clearing existing data...</p>';
                    $db->query("SET FOREIGN_KEY_CHECKS = 0");
                    $db->query("TRUNCATE TABLE reviews");
                    $db->query("TRUNCATE TABLE bookings");
                    $db->query("TRUNCATE TABLE city_buddies");
                    $db->query("TRUNCATE TABLE vendors");
                    $db->query("DELETE FROM users WHERE email != 'admin@gulio.com'");
                    $db->query("SET FOREIGN_KEY_CHECKS = 1");
                    echo '<p class="success">✓ Existing data cleared</p>';
                }
                
                // Seed Users
                echo '<p>Seeding users...</p>';
                $users = [
                    ['email' => 'user1@gulio.com', 'password' => 'password123', 'name' => 'Sarah Mensah', 'phone' => '+233241234567', 'role' => 'user'],
                    ['email' => 'user2@gulio.com', 'password' => 'password123', 'name' => 'Kwame Asante', 'phone' => '+233242345678', 'role' => 'user'],
                    ['email' => 'user3@gulio.com', 'password' => 'password123', 'name' => 'Ama Serwaa', 'phone' => '+233243456789', 'role' => 'scout'],
                ];
                
                $userIds = [];
                $auth = new Auth();
                foreach ($users as $userData) {
                    $profileData = [
                        'profile_role' => 'worker',
                        'languages' => ['English'],
                        'trust_pref' => 'balanced',
                        'starter_pack' => 'worker',
                        'intent' => 'settle'
                    ];
                    $result = $auth->register($userData['email'], $userData['password'], $userData['name'], $userData['phone'], $profileData);
                    if ($result['success']) {
                        $userId = $result['user_id'];
                        $db->update('users', ['role' => $userData['role']], 'id = :id', ['id' => $userId]);
                        $userIds[] = $userId;
                        echo '<p class="success">✓ User created: ' . htmlspecialchars($userData['email']) . '</p>';
                    }
                }
                
                // Seed Vendors
                echo '<p>Seeding vendors...</p>';
                $vendors = [
                    [
                        'name' => "Kofi's Cuts",
                        'category' => 'barber',
                        'description' => 'Professional barber shop offering haircuts, beard trims, and grooming services',
                        'lat' => 5.6037,
                        'lng' => -0.1870,
                        'address' => 'Oxford Street, Osu, Accra',
                        'phone' => '+233241234567',
                        'email' => 'kofi@kofiscuts.com',
                        'photos' => ['barber1.jpg', 'barber2.jpg'],
                        'languages' => ['English', 'Twi'],
                        'price_items' => [
                            ['service' => 'Haircut', 'min' => 15, 'max' => 25],
                            ['service' => 'Beard Trim', 'min' => 8, 'max' => 12],
                            ['service' => 'Full Service', 'min' => 20, 'max' => 35]
                        ],
                        'trust_score' => 4.8,
                        'badges' => ['Scout Verified', 'Locals Recommend'],
                        'verified' => 1,
                        'verified_reviews_count' => 23,
                        'total_reviews' => 25
                    ],
                    [
                        'name' => "Mama Ayo Tailors",
                        'category' => 'tailor',
                        'description' => 'Expert tailoring services for dresses, suits, and alterations',
                        'lat' => 5.6047,
                        'lng' => -0.1880,
                        'address' => 'Labone, Accra',
                        'phone' => '+233242345678',
                        'email' => 'mama@ayotailors.com',
                        'photos' => ['tailor1.jpg', 'tailor2.jpg'],
                        'languages' => ['English', 'Yoruba'],
                        'price_items' => [
                            ['service' => 'Dress Alteration', 'min' => 20, 'max' => 40],
                            ['service' => 'New Dress', 'min' => 80, 'max' => 150],
                            ['service' => 'Suit Fitting', 'min' => 60, 'max' => 120]
                        ],
                        'trust_score' => 4.6,
                        'badges' => ['Verified Reviews 95%'],
                        'verified' => 1,
                        'verified_reviews_count' => 18,
                        'total_reviews' => 19
                    ],
                    [
                        'name' => "FastFix Mobiles",
                        'category' => 'phone_repair',
                        'description' => 'Quick and reliable phone repair services',
                        'lat' => 5.6057,
                        'lng' => -0.1890,
                        'address' => 'Adabraka, Accra',
                        'phone' => '+233243456789',
                        'email' => 'info@fastfix.com',
                        'photos' => ['phone1.jpg', 'phone2.jpg'],
                        'languages' => ['English', 'Hausa'],
                        'price_items' => [
                            ['service' => 'Screen Repair', 'min' => 30, 'max' => 80],
                            ['service' => 'Battery Replacement', 'min' => 15, 'max' => 35],
                            ['service' => 'Software Fix', 'min' => 10, 'max' => 25]
                        ],
                        'trust_score' => 4.7,
                        'badges' => ['Scout Verified', 'Verified Reviews 98%'],
                        'verified' => 1,
                        'verified_reviews_count' => 31,
                        'total_reviews' => 32
                    ],
                    [
                        'name' => "HotBowl Local",
                        'category' => 'food',
                        'description' => 'Authentic local Ghanaian cuisine',
                        'lat' => 5.6017,
                        'lng' => -0.1850,
                        'address' => 'Cantonments, Accra',
                        'phone' => '+233244567890',
                        'email' => 'hotbowl@local.com',
                        'photos' => ['food1.jpg', 'food2.jpg'],
                        'languages' => ['English', 'Twi', 'Ga'],
                        'price_items' => [
                            ['service' => 'Jollof Rice', 'min' => 8, 'max' => 12],
                            ['service' => 'Banku & Fish', 'min' => 10, 'max' => 15],
                            ['service' => 'Fried Rice', 'min' => 7, 'max' => 11]
                        ],
                        'trust_score' => 4.5,
                        'badges' => ['Locals Recommend', 'Verified Reviews 92%'],
                        'verified' => 1,
                        'verified_reviews_count' => 27,
                        'total_reviews' => 30
                    ],
                    [
                        'name' => "Mama Bright Laundry",
                        'category' => 'laundry',
                        'description' => 'Professional laundry and dry cleaning services',
                        'lat' => 5.6067,
                        'lng' => -0.1900,
                        'address' => 'Dansoman, Accra',
                        'phone' => '+233245678901',
                        'email' => 'mama@brightlaundry.com',
                        'photos' => ['laundry1.jpg', 'laundry2.jpg'],
                        'languages' => ['English', 'Twi'],
                        'price_items' => [
                            ['service' => 'Wash & Fold', 'min' => 3, 'max' => 5],
                            ['service' => 'Dry Cleaning', 'min' => 8, 'max' => 15],
                            ['service' => 'Ironing Only', 'min' => 2, 'max' => 4]
                        ],
                        'trust_score' => 4.3,
                        'badges' => ['Verified Reviews 88%'],
                        'verified' => 1,
                        'verified_reviews_count' => 16,
                        'total_reviews' => 19
                    ],
                ];
                
                $vendorIds = [];
                foreach ($vendors as $vendorData) {
                    $vendorDbData = [
                        'name' => $vendorData['name'],
                        'category' => $vendorData['category'],
                        'description' => $vendorData['description'],
                        'lat' => $vendorData['lat'],
                        'lng' => $vendorData['lng'],
                        'address' => $vendorData['address'],
                        'phone' => $vendorData['phone'],
                        'email' => $vendorData['email'],
                        'photos' => json_encode($vendorData['photos']),
                        'languages' => json_encode($vendorData['languages']),
                        'price_items' => json_encode($vendorData['price_items']),
                        'trust_score' => $vendorData['trust_score'],
                        'badges' => json_encode($vendorData['badges']),
                        'verified' => $vendorData['verified'],
                        'verified_reviews_count' => $vendorData['verified_reviews_count'],
                        'total_reviews' => $vendorData['total_reviews'],
                        'last_verified_date' => date('Y-m-d'),
                        'verified_at' => date('Y-m-d H:i:s'),
                        'status' => 'active',
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $vendorId = $db->insert('vendors', $vendorDbData);
                    $vendorIds[] = $vendorId;
                    echo '<p class="success">✓ Vendor created: ' . htmlspecialchars($vendorData['name']) . '</p>';
                }
                
                // Seed Reviews
                echo '<p>Seeding reviews...</p>';
                $reviews = [
                    ['vendor_id' => $vendorIds[0], 'user_id' => $userIds[0], 'user_name' => 'Sarah M.', 'rating' => 5, 'verified_visit' => 1, 'tags' => ['Great Service'], 'comment' => 'Excellent haircut, very professional and clean shop.', 'date' => date('Y-m-d', strtotime('-1 day'))],
                    ['vendor_id' => $vendorIds[0], 'user_id' => $userIds[1], 'user_name' => 'Kwame A.', 'rating' => 4, 'verified_visit' => 1, 'tags' => ['Great Service'], 'comment' => 'Good service, reasonable prices. Will come back.', 'date' => date('Y-m-d', strtotime('-3 days'))],
                    ['vendor_id' => $vendorIds[1], 'user_id' => $userIds[0], 'user_name' => 'Grace K.', 'rating' => 5, 'verified_visit' => 1, 'tags' => ['Great Service'], 'comment' => 'Perfect dress fitting, very skilled tailor.', 'date' => date('Y-m-d', strtotime('-2 days'))],
                    ['vendor_id' => $vendorIds[2], 'user_id' => $userIds[1], 'user_name' => 'Michael R.', 'rating' => 5, 'verified_visit' => 1, 'tags' => ['Great Service'], 'comment' => 'Fixed my phone screen perfectly, very professional.', 'date' => date('Y-m-d', strtotime('-5 days'))],
                    ['vendor_id' => $vendorIds[3], 'user_id' => $userIds[0], 'user_name' => 'Rebecca A.', 'rating' => 5, 'verified_visit' => 1, 'tags' => ['Great Service'], 'comment' => 'Amazing jollof rice, authentic taste!', 'date' => date('Y-m-d', strtotime('-1 day'))],
                ];
                
                foreach ($reviews as $reviewData) {
                    $reviewDbData = [
                        'vendor_id' => $reviewData['vendor_id'],
                        'user_id' => $reviewData['user_id'],
                        'user_name' => $reviewData['user_name'],
                        'rating' => $reviewData['rating'],
                        'verified_visit' => $reviewData['verified_visit'],
                        'tags' => json_encode($reviewData['tags']),
                        'comment' => $reviewData['comment'],
                        'status' => 'approved',
                        'created_at' => $reviewData['date'] . ' ' . date('H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $db->insert('reviews', $reviewDbData);
                    echo '<p class="success">✓ Review created</p>';
                }
                
                // Seed City Buddies
                echo '<p>Seeding city buddies...</p>';
                $buddies = [
                    [
                        'user_id' => $userIds[2],
                        'name' => 'Kofi Mensah',
                        'languages' => ['English', 'Twi', 'French'],
                        'specialties' => ['Food', 'Transportation', 'Shopping'],
                        'rate' => 15.00,
                        'rating' => 4.9,
                        'verified_visits' => 45,
                        'badges' => ['Verified Local', 'Top Rated'],
                        'bio' => 'Experienced local guide with extensive knowledge of Accra',
                        'verified' => 1,
                        'status' => 'active'
                    ],
                    [
                        'user_id' => $userIds[1],
                        'name' => 'Ama Serwaa',
                        'languages' => ['English', 'Twi', 'Ga'],
                        'specialties' => ['Culture', 'Safety', 'Nightlife'],
                        'rate' => 12.00,
                        'rating' => 4.7,
                        'verified_visits' => 32,
                        'badges' => ['Cultural Expert', 'Safety Guide'],
                        'bio' => 'Cultural expert specializing in helping newcomers understand local customs',
                        'verified' => 1,
                        'status' => 'active'
                    ],
                ];
                
                foreach ($buddies as $buddyData) {
                    $buddyDbData = [
                        'user_id' => $buddyData['user_id'],
                        'name' => $buddyData['name'],
                        'languages' => json_encode($buddyData['languages']),
                        'specialties' => json_encode($buddyData['specialties']),
                        'rate' => $buddyData['rate'],
                        'rating' => $buddyData['rating'],
                        'verified_visits' => $buddyData['verified_visits'],
                        'badges' => json_encode($buddyData['badges']),
                        'bio' => $buddyData['bio'],
                        'verified' => $buddyData['verified'],
                        'status' => $buddyData['status'],
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];
                    
                    $db->insert('city_buddies', $buddyDbData);
                    echo '<p class="success">✓ City buddy created: ' . htmlspecialchars($buddyData['name']) . '</p>';
                }
                
                echo '<p class="success"><strong>Database seeding completed successfully!</strong></p>';
                echo '<p><strong>Test Users:</strong></p>';
                echo '<ul>';
                echo '<li>user1@gulio.com / password123</li>';
                echo '<li>user2@gulio.com / password123</li>';
                echo '<li>user3@gulio.com / password123 (Scout)</li>';
                echo '</ul>';
                echo '<p><a href="index.php">Go to Application</a></p>';
                
            } catch (Exception $e) {
                echo '<p class="error">✗ Error: ' . htmlspecialchars($e->getMessage()) . '</p>';
                Logger::error('Seeding failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);
            }
            
            echo '</div>';
        } else {
            ?>
            <form method="POST">
                <div class="step">
                    <h3>What will be seeded?</h3>
                    <ul>
                        <li>3 Test Users (user1, user2, user3)</li>
                        <li>5 Vendors (Barber, Tailor, Phone Repair, Food, Laundry)</li>
                        <li>5 Reviews</li>
                        <li>2 City Buddies</li>
                    </ul>
                </div>
                
                <div class="step">
                    <label>
                        <input type="checkbox" name="clear_existing" value="1">
                        Clear existing data before seeding (keeps admin user)
                    </label>
                </div>
                
                <button type="submit" name="seed_data">Seed Database</button>
            </form>
            <?php
        }
        ?>
    </div>
</body>
</html>

