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

