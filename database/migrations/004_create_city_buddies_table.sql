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

