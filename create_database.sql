-- ============================================
-- GULIO COMPLETE DATABASE SETUP SCRIPT
-- Copy and paste ALL of this into phpMyAdmin SQL tab
-- ============================================

-- Step 1: Create the database
CREATE DATABASE IF NOT EXISTS `gulio_production` 
CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- Step 2: Use the database
USE `gulio_production`;

-- Step 3: Create all tables (in correct order for foreign keys)

-- Table 1: Users
CREATE TABLE IF NOT EXISTS `users` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(255) NOT NULL UNIQUE,
    `password` VARCHAR(255) NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `phone` VARCHAR(20) NULL,
    `role` ENUM('user', 'admin', 'scout', 'vendor') DEFAULT 'user',
    `profile_role` VARCHAR(50) NULL,
    `languages` JSON NULL,
    `trust_pref` ENUM('strict', 'balanced', 'open') DEFAULT 'balanced',
    `starter_pack` VARCHAR(50) NULL,
    `intent` VARCHAR(20) NULL,
    `location_lat` DECIMAL(10, 8) NULL,
    `location_lng` DECIMAL(11, 8) NULL,
    `location_address` TEXT NULL,
    `last_login` DATETIME NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_email` (`email`),
    INDEX `idx_role` (`role`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 2: Vendors
CREATE TABLE IF NOT EXISTS `vendors` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(255) NOT NULL,
    `category` VARCHAR(50) NOT NULL,
    `description` TEXT NULL,
    `lat` DECIMAL(10, 8) NOT NULL,
    `lng` DECIMAL(11, 8) NOT NULL,
    `address` TEXT NOT NULL,
    `phone` VARCHAR(20) NULL,
    `email` VARCHAR(255) NULL,
    `photos` JSON NULL,
    `languages` JSON NULL,
    `price_items` JSON NOT NULL,
    `trust_score` DECIMAL(3, 2) DEFAULT 0.00,
    `badges` JSON NULL,
    `verified` TINYINT(1) DEFAULT 0,
    `verified_by` INT UNSIGNED NULL,
    `verified_at` DATETIME NULL,
    `last_verified_date` DATE NULL,
    `verified_reviews_count` INT UNSIGNED DEFAULT 0,
    `total_reviews` INT UNSIGNED DEFAULT 0,
    `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_category` (`category`),
    INDEX `idx_verified` (`verified`),
    INDEX `idx_status` (`status`),
    INDEX `idx_location` (`lat`, `lng`),
    FOREIGN KEY (`verified_by`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 3: Reviews
CREATE TABLE IF NOT EXISTS `reviews` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `vendor_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `user_name` VARCHAR(255) NOT NULL,
    `rating` TINYINT UNSIGNED NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
    `verified_visit` TINYINT(1) DEFAULT 0,
    `tags` JSON NULL,
    `comment` TEXT NULL,
    `status` ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_vendor` (`vendor_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_rating` (`rating`),
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 4: City Buddies
CREATE TABLE IF NOT EXISTS `city_buddies` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NOT NULL,
    `name` VARCHAR(255) NOT NULL,
    `languages` JSON NOT NULL,
    `specialties` JSON NOT NULL,
    `rate` DECIMAL(10, 2) NOT NULL,
    `rating` DECIMAL(3, 2) DEFAULT 0.00,
    `verified_visits` INT UNSIGNED DEFAULT 0,
    `badges` JSON NULL,
    `photo` VARCHAR(255) NULL,
    `bio` TEXT NULL,
    `verified` TINYINT(1) DEFAULT 0,
    `status` ENUM('active', 'inactive', 'suspended') DEFAULT 'active',
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 5: Bookings
CREATE TABLE IF NOT EXISTS `bookings` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `vendor_id` INT UNSIGNED NOT NULL,
    `user_id` INT UNSIGNED NOT NULL,
    `service` VARCHAR(255) NOT NULL,
    `datetime` DATETIME NOT NULL,
    `meeting_point` VARCHAR(255) NOT NULL,
    `status` ENUM('pending', 'confirmed', 'completed', 'cancelled') DEFAULT 'pending',
    `notes` TEXT NULL,
    `created_at` DATETIME NOT NULL,
    `updated_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_vendor` (`vendor_id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_datetime` (`datetime`),
    INDEX `idx_status` (`status`),
    FOREIGN KEY (`vendor_id`) REFERENCES `vendors`(`id`) ON DELETE CASCADE,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 6: Sessions
CREATE TABLE IF NOT EXISTS `sessions` (
    `id` VARCHAR(128) NOT NULL,
    `user_id` INT UNSIGNED NULL,
    `ip_address` VARCHAR(45) NOT NULL,
    `user_agent` TEXT NULL,
    `data` TEXT NULL,
    `last_activity` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_last_activity` (`last_activity`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table 7: Audit Log
CREATE TABLE IF NOT EXISTS `audit_log` (
    `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `user_id` INT UNSIGNED NULL,
    `action` VARCHAR(100) NOT NULL,
    `table_name` VARCHAR(100) NULL,
    `record_id` INT UNSIGNED NULL,
    `old_values` JSON NULL,
    `new_values` JSON NULL,
    `ip_address` VARCHAR(45) NULL,
    `user_agent` TEXT NULL,
    `created_at` DATETIME NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `idx_user` (`user_id`),
    INDEX `idx_action` (`action`),
    INDEX `idx_created` (`created_at`),
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ============================================
-- Setup Complete!
-- ============================================
SELECT 'Database and all tables created successfully!' AS Status;

