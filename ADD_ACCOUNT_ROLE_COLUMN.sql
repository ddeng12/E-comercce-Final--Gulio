-- Add account_role column to users table
-- Copy and paste these commands in phpMyAdmin SQL tab

-- First, let's see the current structure of users table
DESCRIBE users;

-- Add the account_role column to users table
ALTER TABLE users ADD COLUMN account_role ENUM('guest', 'user', 'vendor', 'admin', 'super_admin') DEFAULT 'user' AFTER role;

-- Update existing users to have proper roles
UPDATE users SET account_role = 'user' WHERE account_role IS NULL;

-- Set admin user
UPDATE users SET account_role = 'admin' WHERE email = 'admin@gulio.com';

-- Create admin user if it doesn't exist
INSERT IGNORE INTO users (name, email, password, account_role, phone, trust_pref, created_at) 
VALUES (
    'System Administrator', 
    'admin@gulio.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'admin', 
    '+233000000000', 
    'balanced', 
    NOW()
);

-- Create vendor user for testing
INSERT IGNORE INTO users (name, email, password, account_role, phone, trust_pref, created_at) 
VALUES (
    'Test Vendor', 
    'vendor@gulio.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'vendor', 
    '+233000000001', 
    'balanced', 
    NOW()
);

-- Verify the changes
SELECT id, name, email, account_role FROM users WHERE account_role IN ('admin', 'vendor');
