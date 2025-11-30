<?php
/**
 * Authentication and Authorization System
 * Handles user login, registration, and session management
 */

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/security.php';
require_once __DIR__ . '/logger.php';

class Auth {
    private $db;
    
    // User roles
    const ROLE_GUEST = 'guest';
    const ROLE_USER = 'user';
    const ROLE_VENDOR = 'vendor';
    const ROLE_ADMIN = 'admin';
    const ROLE_SUPER_ADMIN = 'super_admin';
    
    public function __construct() {
        try {
            $this->db = Database::getInstance();
        } catch (Exception $e) {
            // Log the error but don't throw - allow site to work with sample data
            Logger::warning('Auth: Database connection failed, authentication will be limited', ['error' => $e->getMessage()]);
            $this->db = null;
        }
    }
    
    /**
     * Initialize secure session
     */
    public static function initSession() {
        // Secure session configuration
        ini_set('session.cookie_httponly', 1);
        ini_set('session.cookie_secure', isset($_SERVER['HTTPS']));
        ini_set('session.use_strict_mode', 1);
        ini_set('session.cookie_samesite', 'Strict');
        
        session_name(SESSION_NAME);
        session_set_cookie_params([
            'lifetime' => SESSION_LIFETIME,
            'path' => '/',
            'domain' => '',
            'secure' => isset($_SERVER['HTTPS']),
            'httponly' => true,
            'samesite' => 'Strict'
        ]);
        
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }
    
    /**
     * Register new user
     */
    public function register($email, $password, $name, $phone = null, array $profile = []) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Database not available. Please run setup.php to initialize the database.'];
        }
        
        try {
            // Validate input
            $validation = Security::validateInput([
                'email' => $email,
                'password' => $password,
                'name' => $name,
                'phone' => $phone ?: ''
            ], [
                'email' => ['required' => true, 'type' => 'email'],
                'password' => ['required' => true, 'min_length' => 8],
                'name' => ['required' => true, 'min_length' => 2],
                'phone' => ['required' => false, 'type' => 'phone']
            ]);
            
            if (!$validation['valid']) {
                return ['success' => false, 'errors' => $validation['errors']];
            }
            
            // Check if user exists
            $existing = $this->db->fetchOne(
                "SELECT id FROM users WHERE email = :email",
                ['email' => Security::sanitizeInput($email, 'email')]
            );
            
            if ($existing) {
                return ['success' => false, 'message' => 'Email already registered'];
            }
        } catch (Exception $e) {
            Logger::error('Registration validation failed', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
        
        // Create user
        try {
            $profileRole = Security::sanitizeInput($profile['profile_role'] ?? $profile['role'] ?? '');
            $languages = isset($profile['languages']) ? array_filter(array_map(fn($lang) => Security::sanitizeInput($lang), (array) $profile['languages'])) : [];
            $trustPref = $profile['trust_pref'] ?? 'balanced';
            $trustPref = in_array($trustPref, ['strict', 'balanced', 'open']) ? $trustPref : 'balanced';
            $starterPack = Security::sanitizeInput($profile['starter_pack'] ?? '');
            $intent = Security::sanitizeInput($profile['intent'] ?? '');
            $locationLat = isset($profile['location_lat']) ? filter_var($profile['location_lat'], FILTER_VALIDATE_FLOAT) : null;
            $locationLng = isset($profile['location_lng']) ? filter_var($profile['location_lng'], FILTER_VALIDATE_FLOAT) : null;
            $locationAddress = Security::sanitizeInput($profile['location_address'] ?? '');
            
            $userId = $this->db->insert('users', [
                'email' => Security::sanitizeInput($email, 'email'),
                'password' => Security::hashPassword($password),
                'name' => Security::sanitizeInput($name),
                'phone' => $phone ? Security::sanitizeInput($phone) : null,
                'role' => 'user',
                'profile_role' => $profileRole ?: null,
                'languages' => !empty($languages) ? json_encode(array_values($languages)) : json_encode([]),
                'trust_pref' => $trustPref,
                'starter_pack' => $starterPack ?: null,
                'intent' => $intent ?: null,
                'location_lat' => $locationLat !== false ? $locationLat : null,
                'location_lng' => $locationLng !== false ? $locationLng : null,
                'location_address' => $locationAddress ?: null,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);
            
            Logger::info('User registered', ['user_id' => $userId, 'email' => $email]);
            
            return ['success' => true, 'user_id' => $userId];
        } catch (PDOException $e) {
            Logger::error('Registration database error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Registration failed. Please try again or contact support.'];
        } catch (Exception $e) {
            Logger::error('Registration error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Registration failed: ' . $e->getMessage()];
        }
    }
    
    /**
     * Login user
     */
    public function login($email, $password) {
        if (!$this->db) {
            return ['success' => false, 'message' => 'Database not available. Please run setup.php to initialize the database.'];
        }
        
        try {
            $user = $this->db->fetchOne(
                "SELECT * FROM users WHERE email = :email",
                ['email' => Security::sanitizeInput($email, 'email')]
            );
            
            if (!$user || !Security::verifyPassword($password, $user['password'])) {
                Logger::warning('Failed login attempt', ['email' => $email]);
                return ['success' => false, 'message' => 'Invalid email or password'];
            }
        } catch (PDOException $e) {
            Logger::error('Login database error', ['error' => $e->getMessage(), 'email' => $email]);
            return ['success' => false, 'message' => 'Login failed. Please try again or contact support.'];
        } catch (Exception $e) {
            Logger::error('Login error', ['error' => $e->getMessage(), 'email' => $email]);
            return ['success' => false, 'message' => 'Login failed: ' . $e->getMessage()];
        }
        
        // Set session
        try {
            $_SESSION['user'] = [
                'id' => $user['id'],
                'email' => $user['email'],
                'name' => $user['name'],
                'role' => $user['profile_role'] ?? $user['role'],
                'account_role' => $user['role'],
                'phone' => $user['phone'] ?? null,
                'languages' => json_decode($user['languages'] ?? '[]', true) ?: [],
                'trust_pref' => $user['trust_pref'] ?? 'balanced',
                'starter_pack' => $user['starter_pack'] ?? '',
                'intent' => $user['intent'] ?? '',
                'location' => ($user['location_lat'] !== null && $user['location_lng'] !== null) ? [
                    'lat' => (float) $user['location_lat'],
                    'lng' => (float) $user['location_lng'],
                    'address' => $user['location_address'] ?? 'Current Location'
                ] : null
            ];
            if (empty($_SESSION['user']['role'])) {
                $_SESSION['user']['role'] = $_SESSION['user']['account_role'];
            }
            
            // Update last login
            try {
                $this->db->update('users', 
                    ['last_login' => date('Y-m-d H:i:s')],
                    'id = :id',
                    ['id' => $user['id']]
                );
            } catch (Exception $e) {
                // Log but don't fail login if update fails
                Logger::warning('Failed to update last login', ['user_id' => $user['id'], 'error' => $e->getMessage()]);
            }
            
            Logger::info('User logged in', ['user_id' => $user['id']]);
            
            return ['success' => true, 'user' => $_SESSION['user']];
        } catch (Exception $e) {
            Logger::error('Login session error', ['error' => $e->getMessage()]);
            return ['success' => false, 'message' => 'Login failed. Please try again.'];
        }
    }
    
    /**
     * Logout user
     */
    public function logout() {
        if (isset($_SESSION['user'])) {
            Logger::info('User logged out', ['user_id' => $_SESSION['user']['id']]);
            unset($_SESSION['user']);
        }
        session_destroy();
    }
    
    /**
     * Check if user is logged in
     */
    public static function check() {
        return isset($_SESSION['user']) && isset($_SESSION['user']['id']) && $_SESSION['user']['id'] !== null;
    }
    
    /**
     * Get current user
     */
    public static function user() {
        return $_SESSION['user'] ?? null;
    }
    
    /**
     * Require authentication
     */
    public static function requireAuth() {
        if (!self::check()) {
            header('Location: ?page=login');
            exit;
        }
    }
    
    /**
     * Check if user has specific role
     */
    public static function hasRole($role) {
        $user = self::user();
        if (!$user) {
            return false;
        }
        
        $accountRole = $user['account_role'] ?? $user['role'] ?? self::ROLE_GUEST;
        return $accountRole === $role;
    }
    
    /**
     * Check if user is admin (admin or super_admin)
     */
    public static function isAdmin() {
        return self::hasRole(self::ROLE_ADMIN) || self::hasRole(self::ROLE_SUPER_ADMIN);
    }
    
    /**
     * Check if user is vendor
     */
    public static function isVendor() {
        return self::hasRole(self::ROLE_VENDOR);
    }
    
    /**
     * Check if user can manage products (admin, super_admin, or vendor)
     */
    public static function canManageProducts() {
        return self::isAdmin() || self::isVendor();
    }
    
    /**
     * Get user role
     */
    public static function getUserRole() {
        $user = self::user();
        if (!$user) {
            return self::ROLE_GUEST;
        }
        
        return $user['account_role'] ?? $user['role'] ?? self::ROLE_GUEST;
    }
    
    /**
     * Require admin access (redirect if not admin)
     */
    public static function requireAdmin($redirectUrl = '?page=login') {
        if (!self::isAdmin()) {
            Logger::warning('Unauthorized admin access attempt', [
                'user_id' => self::user()['id'] ?? null,
                'role' => self::getUserRole(),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            header("Location: $redirectUrl");
            exit;
        }
    }
    
    /**
     * Require product management access
     */
    public static function requireProductAccess($redirectUrl = '?page=login') {
        if (!self::canManageProducts()) {
            Logger::warning('Unauthorized product management access attempt', [
                'user_id' => self::user()['id'] ?? null,
                'role' => self::getUserRole(),
                'ip' => $_SERVER['REMOTE_ADDR'] ?? 'unknown'
            ]);
            
            header("Location: $redirectUrl");
            exit;
        }
    }
}

