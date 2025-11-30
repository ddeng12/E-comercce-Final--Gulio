<?php
/**
 * Helper functions for Gulio
 * Updated for production with database support
 */

require_once __DIR__ . '/database.php';

function getVendors($lat = 5.6037, $lng = -0.1870, $category = '') {
    global $sampleData, $pdo;
    
    // Try database first
    if ($pdo !== null) {
        try {
            $db = Database::getInstance();
            $sql = "SELECT * FROM vendors WHERE status = 'active'";
            $params = [];
            
            if ($category && $category !== 'all') {
                $sql .= " AND category = :category";
                $params['category'] = $category;
            }
            
            $sql .= " ORDER BY (POW(lat - :lat, 2) + POW(lng - :lng, 2)) ASC LIMIT 50";
            $params['lat'] = $lat;
            $params['lng'] = $lng;
            
            $vendors = $db->fetchAll($sql, $params);
            
            // Convert JSON fields and calculate distance
            foreach ($vendors as &$vendor) {
                $vendor['photos'] = json_decode($vendor['photos'] ?? '[]', true) ?: [];
                $vendor['languages'] = json_decode($vendor['languages'] ?? '[]', true) ?: [];
                $vendor['price_items'] = json_decode($vendor['price_items'] ?? '[]', true) ?: [];
                $vendor['badges'] = json_decode($vendor['badges'] ?? '[]', true) ?: [];
                $vendor['distance'] = calculateDistance($lat, $lng, $vendor['lat'], $vendor['lng']);
            }
            
            return $vendors;
        } catch (Exception $e) {
            Logger::warning('Failed to load vendors from database, using sample data', ['error' => $e->getMessage()]);
        }
    }
    
    // Fallback to sample data
    $vendors = $sampleData['vendors'] ?? [];
    
    // Filter by category if specified
    if ($category && $category !== 'all') {
        $vendors = array_filter($vendors, function($vendor) use ($category) {
            return $vendor['category'] === $category;
        });
    }
    
    // Calculate distance (simplified for prototype)
    foreach ($vendors as &$vendor) {
        $vendor['distance'] = calculateDistance($lat, $lng, $vendor['lat'], $vendor['lng']);
    }
    
    // Sort by distance
    usort($vendors, function($a, $b) {
        return $a['distance'] <=> $b['distance'];
    });
    
    return $vendors;
}

function getVendorById($id) {
    global $sampleData;
    
    foreach ($sampleData['vendors'] as $vendor) {
        if ($vendor['id'] == $id) {
            return $vendor;
        }
    }
    
    return null;
}

function getVendorReviews($vendorId) {
    global $sampleData;
    
    return array_filter($sampleData['reviews'], function($review) use ($vendorId) {
        return $review['vendor_id'] == $vendorId;
    });
}

function getCityBuddies() {
    global $sampleData;
    return $sampleData['city_buddies'];
}

function calculateDistance($lat1, $lng1, $lat2, $lng2) {
    // Simplified distance calculation for prototype
    $latDiff = abs($lat1 - $lat2);
    $lngDiff = abs($lng1 - $lng2);
    return sqrt($latDiff * $latDiff + $lngDiff * $lngDiff) * 111; // Rough km conversion
}


function submitReview($vendorId, $rating, $tags, $comment) {
    try {
        $db = Database::getInstance();
        $userId = $_SESSION['user']['id'] ?? null;
        $userName = $_SESSION['user']['name'] ?? 'Anonymous';
        
        if (!$userId) {
            return ['success' => false, 'message' => 'Please log in to submit a review'];
        }
        
        $reviewId = $db->insert('reviews', [
            'vendor_id' => $vendorId,
            'user_id' => $userId,
            'user_name' => Security::sanitizeInput($userName),
            'rating' => $rating,
            'verified_visit' => 0,
            'tags' => !empty($tags) ? json_encode($tags) : null,
            'comment' => Security::sanitizeInput($comment),
            'status' => 'pending',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        
        // Update vendor review counts
        $db->query("UPDATE vendors SET total_reviews = total_reviews + 1 WHERE id = :id", ['id' => $vendorId]);
        
        Logger::info('Review submitted', ['review_id' => $reviewId, 'vendor_id' => $vendorId, 'user_id' => $userId]);
        
        return [
            'success' => true,
            'message' => 'Review submitted successfully',
            'review_id' => $reviewId
        ];
    } catch (Exception $e) {
        Logger::error('Review submission failed', ['error' => $e->getMessage()]);
        return [
            'success' => false,
            'message' => 'Failed to submit review. Please try again.'
        ];
    }
}

function getStarterPacks() {
    return [
        'student' => [
            'name' => 'Student Pack',
            'description' => 'Essential services for students',
            'services' => ['Food', 'Laundry', 'Phone Repair', 'Transportation'],
            'vendors' => [5, 6, 4, 1] // HotBowl, Laundry, Phone Repair, Barber
        ],
        'expat' => [
            'name' => 'Expat Pack',
            'description' => 'Comprehensive services for expats',
            'services' => ['Food', 'Tailor', 'Phone Repair', 'Laundry', 'Cultural Guide'],
            'vendors' => [5, 2, 4, 6, 3] // HotBowl, Tailor, Phone Repair, Laundry, Thrift
        ],
        'worker' => [
            'name' => 'Worker Pack',
            'description' => 'Professional services for workers',
            'services' => ['Barber', 'Tailor', 'Food', 'Phone Repair'],
            'vendors' => [1, 2, 5, 4] // Barber, Tailor, Food, Phone Repair
        ],
        'tourist' => [
            'name' => 'Tourist Pack',
            'description' => 'Tourist-friendly services',
            'services' => ['Food', 'Cultural Guide', 'Transportation', 'Shopping'],
            'vendors' => [5, 3, 1, 2] // Food, Thrift, Barber, Tailor
        ]
    ];
}

function getQuickHelpOptions() {
    return [
        [
            'id' => 'barber',
            'name' => 'Barber now',
            'icon' => 'scissors',
            'color' => '#FF6B35'
        ],
        [
            'id' => 'phone_repair',
            'name' => 'Phone repair',
            'icon' => 'mobile-alt',
            'color' => '#4ECDC4'
        ],
        [
            'id' => 'food',
            'name' => 'Food near me',
            'icon' => 'utensils',
            'color' => '#45B7D1'
        ],
        [
            'id' => 'city_buddy',
            'name' => 'Book City Buddy',
            'icon' => 'user-friends',
            'color' => '#96CEB4'
        ],
        [
            'id' => 'emergency',
            'name' => 'Emergency',
            'icon' => 'exclamation-triangle',
            'color' => '#FF4757'
        ]
    ];
}

function getChatbotPresets() {
    return [
        [
            'id' => 'cultural_coach',
            'name' => 'Cultural Coach',
            'description' => 'Learn about local customs and culture',
            'icon' => 'graduation-cap'
        ],
        [
            'id' => 'safety_check',
            'name' => 'Safety Check',
            'description' => 'Get safety tips and advice',
            'icon' => 'shield-alt'
        ],
        [
            'id' => 'find_nearby',
            'name' => 'Find Nearby',
            'description' => 'Discover services around you',
            'icon' => 'map-marker-alt'
        ],
    ];
}

function getTrustScoreDescription($score) {
    if ($score >= 4.5) {
        return 'Excellent - Highly trusted by community';
    } elseif ($score >= 4.0) {
        return 'Good - Well trusted by community';
    } elseif ($score >= 3.5) {
        return 'Fair - Moderately trusted';
    } else {
        return 'New - Recently added to platform';
    }
}

function formatPrice($priceItem) {
    // Handle both array and individual values
    if (is_array($priceItem)) {
        $min = $priceItem['min'] ?? 0;
        $max = $priceItem['max'] ?? 0;
    } else {
        // If single value passed, use it for both min and max
        $min = $max = $priceItem;
    }
    
    if ($min == $max) {
        return 'GHS ' . $min;
    }
    return 'GHS ' . $min . ' - ' . $max;
}

function formatDistance($distance) {
    if ($distance < 1) {
        return round($distance * 1000) . 'm';
    }
    return round($distance, 1) . 'km';
}

function getTimeAgo($date) {
    $now = new DateTime();
    $reviewDate = new DateTime($date);
    $diff = $now->diff($reviewDate);
    
    if ($diff->days > 0) {
        return $diff->days . ' days ago';
    } elseif ($diff->h > 0) {
        return $diff->h . ' hours ago';
    } else {
        return $diff->i . ' minutes ago';
    }
}
?>

