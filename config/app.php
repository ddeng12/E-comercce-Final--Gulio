<?php
/**
 * Application Configuration
 * All non-database application settings
 */

// Determine environment
// Auto-detect development on localhost
$detectedEnv = getenv('APP_ENV');
if (!$detectedEnv) {
    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
    $detectedEnv = (strpos($host, 'localhost') !== false || strpos($host, '127.0.0.1') !== false) ? 'development' : 'production';
}
define('ENVIRONMENT', $detectedEnv);

// Error reporting based on environment
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(E_ALL & ~E_NOTICE & ~E_DEPRECATED & ~E_STRICT);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
}

// Load environment variables from .env file
function loadEnv($filePath) {
    if (!file_exists($filePath)) {
        return;
    }
    
    $lines = file($filePath, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) {
            continue;
        }
        
        list($name, $value) = explode('=', $line, 2);
        $name = trim($name);
        $value = trim($value);
        
        if (!array_key_exists($name, $_SERVER) && !array_key_exists($name, $_ENV)) {
            putenv(sprintf('%s=%s', $name, $value));
            $_ENV[$name] = $value;
            $_SERVER[$name] = $value;
        }
    }
}

// Load .env file if it exists
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    loadEnv($envFile);
}

// Application Configuration
define('APP_NAME', getenv('APP_NAME') ?: 'Gulio');
define('APP_VERSION', getenv('APP_VERSION') ?: '1.0.0');
define('APP_URL', getenv('APP_URL') ?: 'http://localhost/prototype');
define('APP_TIMEZONE', getenv('APP_TIMEZONE') ?: 'Africa/Accra');

// Security Configuration
define('SESSION_NAME', getenv('SESSION_NAME') ?: 'GULIO_SESSION');
define('SESSION_LIFETIME', (int)(getenv('SESSION_LIFETIME') ?: 7200)); // 2 hours
define('CSRF_TOKEN_NAME', getenv('CSRF_TOKEN_NAME') ?: 'csrf_token');
define('HASH_ALGO', getenv('HASH_ALGO') ?: 'sha256');

// File Upload Configuration
define('UPLOAD_MAX_SIZE', (int)(getenv('UPLOAD_MAX_SIZE') ?: 5242880)); // 5MB
define('UPLOAD_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// API Configuration
define('API_RATE_LIMIT', (int)(getenv('API_RATE_LIMIT') ?: 100)); // requests per hour
define('API_VERSION', getenv('API_VERSION') ?: 'v1');

// Logging Configuration
define('LOG_PATH', getenv('LOG_PATH') ?: __DIR__ . '/../logs');
define('LOG_LEVEL', getenv('LOG_LEVEL') ?: 'INFO'); // DEBUG, INFO, WARNING, ERROR

// Email Configuration (for production notifications)
define('SMTP_HOST', getenv('SMTP_HOST') ?: '');
define('SMTP_PORT', (int)(getenv('SMTP_PORT') ?: 587));
define('SMTP_USER', getenv('SMTP_USER') ?: '');
define('SMTP_PASS', getenv('SMTP_PASS') ?: '');
define('SMTP_FROM_EMAIL', getenv('SMTP_FROM_EMAIL') ?: 'noreply@gulio.com');
define('SMTP_FROM_NAME', getenv('SMTP_FROM_NAME') ?: 'Gulio');

// Set timezone
date_default_timezone_set(APP_TIMEZONE);

// Ensure log directory exists (with error handling)
if (!is_dir(LOG_PATH)) {
    @mkdir(LOG_PATH, 0755, true);
    // If still doesn't exist, try alternative location
    if (!is_dir(LOG_PATH) && is_writable(__DIR__ . '/../')) {
        // Try to create in parent directory if current location fails
        $altPath = __DIR__ . '/../logs';
        @mkdir($altPath, 0755, true);
    }
}

