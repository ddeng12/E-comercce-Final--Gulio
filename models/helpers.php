<?php
/**
 * Helper Functions
 * Centralized helper functions used across the application
 */

/**
 * Get product image URL (works on any server)
 * Handles relative paths, absolute paths, and full URLs
 */
function getProductImageUrl($imageUrl) {
    if (empty($imageUrl)) {
        return 'https://via.placeholder.com/500x500/f0f0f0/999999?text=No+Image';
    }
    
    // If already absolute URL (http/https), return as is
    if (preg_match('/^https?:\/\//', $imageUrl)) {
        return $imageUrl;
    }
    
    // If starts with /, it's already absolute from document root - return as is
    if (preg_match('/^\//', $imageUrl)) {
        return $imageUrl;
    }
    
    // Relative path - make it absolute from document root
    return '/' . ltrim($imageUrl, '/');
}

