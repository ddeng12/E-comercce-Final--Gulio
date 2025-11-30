<?php
/**
 * Security Utilities
 * Provides CSRF protection, input validation, XSS prevention, and other security features
 */

class Security {
    
    /**
     * Generate CSRF token
     */
    public static function generateCSRFToken() {
        if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
            $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
        }
        return $_SESSION[CSRF_TOKEN_NAME];
    }
    
    /**
     * Validate CSRF token
     */
    public static function validateCSRFToken($token) {
        return isset($_SESSION[CSRF_TOKEN_NAME]) && 
               hash_equals($_SESSION[CSRF_TOKEN_NAME], $token);
    }
    
    /**
     * Sanitize input string
     */
    public static function sanitizeInput($input, $type = 'string') {
        if (is_null($input)) {
            return null;
        }
        
        switch ($type) {
            case 'string':
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
            case 'email':
                return filter_var(trim($input), FILTER_SANITIZE_EMAIL);
            case 'int':
                return filter_var($input, FILTER_SANITIZE_NUMBER_INT);
            case 'float':
                return filter_var($input, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            case 'url':
                return filter_var(trim($input), FILTER_SANITIZE_URL);
            case 'html':
                return strip_tags($input);
            default:
                return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
        }
    }
    
    /**
     * Validate email
     */
    public static function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }
    
    /**
     * Validate phone number (Ghana format)
     */
    public static function validatePhone($phone) {
        // Remove any non-digit characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        // Ghana phone numbers: +233XXXXXXXXX or 0XXXXXXXXX
        return preg_match('/^(\+233|0)[0-9]{9}$/', $phone);
    }
    
    /**
     * Validate input against rules
     */
    public static function validateInput($data, $rules) {
        $errors = [];
        
        foreach ($rules as $field => $ruleSet) {
            $value = $data[$field] ?? null;
            
            // Required check
            if (isset($ruleSet['required']) && $ruleSet['required'] && empty($value)) {
                $errors[$field] = ucfirst($field) . ' is required';
                continue;
            }
            
            // Skip further validation if field is empty and not required
            if (empty($value) && (!isset($ruleSet['required']) || !$ruleSet['required'])) {
                continue;
            }
            
            // Type validation
            if (isset($ruleSet['type'])) {
                switch ($ruleSet['type']) {
                    case 'email':
                        if (!self::validateEmail($value)) {
                            $errors[$field] = ucfirst($field) . ' must be a valid email';
                        }
                        break;
                    case 'phone':
                        if (!self::validatePhone($value)) {
                            $errors[$field] = ucfirst($field) . ' must be a valid phone number';
                        }
                        break;
                    case 'int':
                        if (!is_numeric($value) || intval($value) != $value) {
                            $errors[$field] = ucfirst($field) . ' must be an integer';
                        }
                        break;
                    case 'float':
                        if (!is_numeric($value)) {
                            $errors[$field] = ucfirst($field) . ' must be a number';
                        }
                        break;
                }
            }
            
            // Length validation
            if (isset($ruleSet['min_length']) && strlen($value) < $ruleSet['min_length']) {
                $errors[$field] = ucfirst($field) . ' must be at least ' . $ruleSet['min_length'] . ' characters';
            }
            if (isset($ruleSet['max_length']) && strlen($value) > $ruleSet['max_length']) {
                $errors[$field] = ucfirst($field) . ' must not exceed ' . $ruleSet['max_length'] . ' characters';
            }
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }
    
    /**
     * Escape output for HTML
     */
    public static function escape($string) {
        return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
    }
    
    /**
     * Generate secure random string
     */
    public static function randomString($length = 32) {
        return bin2hex(random_bytes($length / 2));
    }
    
    /**
     * Hash password
     */
    public static function hashPassword($password) {
        return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
    }
    
    /**
     * Verify password
     */
    public static function verifyPassword($password, $hash) {
        return password_verify($password, $hash);
    }
    
    /**
     * Rate limiting check
     */
    public static function checkRateLimit($identifier, $limit = API_RATE_LIMIT, $period = 3600) {
        $key = 'rate_limit_' . $identifier;
        $current = $_SESSION[$key] ?? ['count' => 0, 'reset' => time() + $period];
        
        if (time() > $current['reset']) {
            $current = ['count' => 0, 'reset' => time() + $period];
        }
        
        if ($current['count'] >= $limit) {
            return false;
        }
        
        $current['count']++;
        $_SESSION[$key] = $current;
        return true;
    }
}

