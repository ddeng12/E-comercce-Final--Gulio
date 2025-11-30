<?php
/**
 * Database Connection (Legacy Support)
 * This file maintains backward compatibility while using the new Database class
 */

require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/logger.php';

try {
    $db = Database::getInstance();
    $pdo = $db->getConnection();
} catch (Exception $e) {
    // Fallback to sample data if database connection fails
    Logger::warning('Database connection failed, using sample data', ['error' => $e->getMessage()]);
    $pdo = null;
}

// Initialize sample data if database doesn't exist
function initializeSampleData() {
    $sampleData = [
        'vendors' => [
            [
                'id' => 1,
                'name' => "Kofi's Cuts",
                'category' => 'barber',
                'lat' => 5.6037,
                'lng' => -0.1870,
                'address' => 'Osu, Accra',
                'photos' => ['barber1.jpg', 'barber2.jpg'],
                'languages' => ['English', 'Twi'],
                'price_items' => [
                    ['service' => 'Haircut', 'min' => 15, 'max' => 25],
                    ['service' => 'Beard Trim', 'min' => 8, 'max' => 12],
                    ['service' => 'Full Service', 'min' => 20, 'max' => 35]
                ],
                'trust_score' => 4.8,
                'badges' => ['Scout Verified', 'Locals Recommend'],
                'last_verified_date' => '2024-01-15',
                'verified_reviews_count' => 23,
                'total_reviews' => 25
            ],
            [
                'id' => 2,
                'name' => "Mama Ayo Tailors",
                'category' => 'tailor',
                'lat' => 5.6047,
                'lng' => -0.1880,
                'address' => 'Labone, Accra',
                'photos' => ['tailor1.jpg', 'tailor2.jpg'],
                'languages' => ['English', 'Yoruba'],
                'price_items' => [
                    ['service' => 'Dress Alteration', 'min' => 20, 'max' => 40],
                    ['service' => 'New Dress', 'min' => 80, 'max' => 150],
                    ['service' => 'Suit Fitting', 'min' => 60, 'max' => 120]
                ],
                'trust_score' => 4.6,
                'badges' => ['Verified Reviews 95%'],
                'last_verified_date' => '2024-01-10',
                'verified_reviews_count' => 18,
                'total_reviews' => 19
            ],
            [
                'id' => 3,
                'name' => "UsedThreads Thrift",
                'category' => 'thrift',
                'lat' => 5.6027,
                'lng' => -0.1860,
                'address' => 'East Legon, Accra',
                'photos' => ['thrift1.jpg', 'thrift2.jpg'],
                'languages' => ['English'],
                'price_items' => [
                    ['service' => 'T-Shirt', 'min' => 5, 'max' => 15],
                    ['service' => 'Jeans', 'min' => 10, 'max' => 25],
                    ['service' => 'Dress', 'min' => 8, 'max' => 20]
                ],
                'trust_score' => 4.2,
                'badges' => ['Locals Recommend'],
                'last_verified_date' => '2024-01-08',
                'verified_reviews_count' => 12,
                'total_reviews' => 15
            ],
            [
                'id' => 4,
                'name' => "FastFix Mobiles",
                'category' => 'phone_repair',
                'lat' => 5.6057,
                'lng' => -0.1890,
                'address' => 'Adabraka, Accra',
                'photos' => ['phone1.jpg', 'phone2.jpg'],
                'languages' => ['English', 'Hausa'],
                'price_items' => [
                    ['service' => 'Screen Repair', 'min' => 30, 'max' => 80],
                    ['service' => 'Battery Replacement', 'min' => 15, 'max' => 35],
                    ['service' => 'Software Fix', 'min' => 10, 'max' => 25]
                ],
                'trust_score' => 4.7,
                'badges' => ['Scout Verified', 'Verified Reviews 98%'],
                'last_verified_date' => '2024-01-12',
                'verified_reviews_count' => 31,
                'total_reviews' => 32
            ],
            [
                'id' => 5,
                'name' => "HotBowl Local",
                'category' => 'food',
                'lat' => 5.6017,
                'lng' => -0.1850,
                'address' => 'Cantonments, Accra',
                'photos' => ['food1.jpg', 'food2.jpg'],
                'languages' => ['English', 'Twi', 'Ga'],
                'price_items' => [
                    ['service' => 'Jollof Rice', 'min' => 8, 'max' => 12],
                    ['service' => 'Banku & Fish', 'min' => 10, 'max' => 15],
                    ['service' => 'Fried Rice', 'min' => 7, 'max' => 11]
                ],
                'trust_score' => 4.5,
                'badges' => ['Locals Recommend', 'Verified Reviews 92%'],
                'last_verified_date' => '2024-01-05',
                'verified_reviews_count' => 27,
                'total_reviews' => 30
            ],
            [
                'id' => 6,
                'name' => "Mama Bright Laundry",
                'category' => 'laundry',
                'lat' => 5.6067,
                'lng' => -0.1900,
                'address' => 'Dansoman, Accra',
                'photos' => ['laundry1.jpg', 'laundry2.jpg'],
                'languages' => ['English', 'Twi'],
                'price_items' => [
                    ['service' => 'Wash & Fold', 'min' => 3, 'max' => 5],
                    ['service' => 'Dry Cleaning', 'min' => 8, 'max' => 15],
                    ['service' => 'Ironing Only', 'min' => 2, 'max' => 4]
                ],
                'trust_score' => 4.3,
                'badges' => ['Verified Reviews 88%'],
                'last_verified_date' => '2024-01-03',
                'verified_reviews_count' => 16,
                'total_reviews' => 19
            ]
        ],
        'reviews' => [
            // Reviews for Kofi's Cuts
            ['id' => 1, 'vendor_id' => 1, 'user_name' => 'Sarah M.', 'rating' => 5, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Excellent haircut, very professional and clean shop.', 'date' => '2024-01-14'],
            ['id' => 2, 'vendor_id' => 1, 'user_name' => 'Kwame A.', 'rating' => 4, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Good service, reasonable prices. Will come back.', 'date' => '2024-01-12'],
            ['id' => 3, 'vendor_id' => 1, 'user_name' => 'Emma L.', 'rating' => 5, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Best barber in Osu! Highly recommended.', 'date' => '2024-01-10'],
            
            // Reviews for Mama Ayo Tailors
            ['id' => 4, 'vendor_id' => 2, 'user_name' => 'Grace K.', 'rating' => 5, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Perfect dress fitting, very skilled tailor.', 'date' => '2024-01-13'],
            ['id' => 5, 'vendor_id' => 2, 'user_name' => 'David O.', 'rating' => 4, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Good work on my suit, delivered on time.', 'date' => '2024-01-11'],
            ['id' => 6, 'vendor_id' => 2, 'user_name' => 'Mary T.', 'rating' => 5, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Excellent craftsmanship, very reasonable prices.', 'date' => '2024-01-09'],
            
            // Reviews for UsedThreads Thrift
            ['id' => 7, 'vendor_id' => 3, 'user_name' => 'Lisa P.', 'rating' => 4, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Great finds at affordable prices!', 'date' => '2024-01-12'],
            ['id' => 8, 'vendor_id' => 3, 'user_name' => 'John D.', 'rating' => 3, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Decent selection, some items need cleaning.', 'date' => '2024-01-10'],
            ['id' => 9, 'vendor_id' => 3, 'user_name' => 'Ama S.', 'rating' => 4, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Good quality second-hand clothes.', 'date' => '2024-01-08'],
            
            // Reviews for FastFix Mobiles
            ['id' => 10, 'vendor_id' => 4, 'user_name' => 'Michael R.', 'rating' => 5, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Fixed my phone screen perfectly, very professional.', 'date' => '2024-01-15'],
            ['id' => 11, 'vendor_id' => 4, 'user_name' => 'Patricia N.', 'rating' => 5, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Quick and reliable service, fair pricing.', 'date' => '2024-01-13'],
            ['id' => 12, 'vendor_id' => 4, 'user_name' => 'Samuel K.', 'rating' => 4, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Good repair work, phone working well now.', 'date' => '2024-01-11'],
            
            // Reviews for HotBowl Local
            ['id' => 13, 'vendor_id' => 5, 'user_name' => 'Rebecca A.', 'rating' => 5, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Amazing jollof rice, authentic taste!', 'date' => '2024-01-14'],
            ['id' => 14, 'vendor_id' => 5, 'user_name' => 'James M.', 'rating' => 4, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Good local food, generous portions.', 'date' => '2024-01-12'],
            ['id' => 15, 'vendor_id' => 5, 'user_name' => 'Akosua B.', 'rating' => 5, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Best local food in the area!', 'date' => '2024-01-10'],
            
            // Reviews for Mama Bright Laundry
            ['id' => 16, 'vendor_id' => 6, 'user_name' => 'Jennifer L.', 'rating' => 4, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Clean clothes, good service.', 'date' => '2024-01-13'],
            ['id' => 17, 'vendor_id' => 6, 'user_name' => 'Robert T.', 'rating' => 4, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Reliable laundry service, fair prices.', 'date' => '2024-01-11'],
            ['id' => 18, 'vendor_id' => 6, 'user_name' => 'Comfort A.', 'rating' => 5, 'verified_visit' => true, 'tags' => ['Great Service'], 'comment' => 'Excellent service, clothes come back fresh!', 'date' => '2024-01-09']
        ],
        'city_buddies' => [
            [
                'id' => 1,
                'name' => 'Kofi Mensah',
                'languages' => ['English', 'Twi', 'French'],
                'specialties' => ['Food', 'Transportation', 'Shopping'],
                'rate' => 15,
                'rating' => 4.9,
                'verified_visits' => 45,
                'badges' => ['Verified Local', 'Top Rated'],
                'photo' => 'buddy1.jpg'
            ],
            [
                'id' => 2,
                'name' => 'Ama Serwaa',
                'languages' => ['English', 'Twi', 'Ga'],
                'specialties' => ['Culture', 'Safety', 'Nightlife'],
                'rate' => 12,
                'rating' => 4.7,
                'verified_visits' => 32,
                'badges' => ['Cultural Expert', 'Safety Guide'],
                'photo' => 'buddy2.jpg'
            ],
            [
                'id' => 3,
                'name' => 'David Osei',
                'languages' => ['English', 'Hausa'],
                'specialties' => ['Business', 'Banking', 'Housing'],
                'rate' => 20,
                'rating' => 4.8,
                'verified_visits' => 28,
                'badges' => ['Business Expert', 'Verified Local'],
                'photo' => 'buddy3.jpg'
            ]
        ]
    ];
    
    return $sampleData;
}

// Get sample data
$sampleData = initializeSampleData();
?>

