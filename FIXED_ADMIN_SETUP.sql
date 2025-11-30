-- FIXED ADMIN SETUP - Copy ALL of this to phpMyAdmin
-- This includes all required columns including updated_at

-- Step 1: Add account_role column if it doesn't exist
ALTER TABLE users ADD COLUMN IF NOT EXISTS account_role ENUM('guest', 'user', 'vendor', 'admin', 'super_admin') DEFAULT 'user';

-- Step 2: Update existing users to have proper roles
UPDATE users SET account_role = 'user' WHERE account_role IS NULL OR account_role = '';

-- Step 3: Delete any existing admin/vendor users to avoid conflicts
DELETE FROM users WHERE email IN ('admin@gulio.com', 'vendor@gulio.com');

-- Step 4: Create admin user with ALL required fields
INSERT INTO users (name, email, password, account_role, phone, trust_pref, created_at, updated_at) 
VALUES (
    'System Administrator', 
    'admin@gulio.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'admin', 
    '+233000000000', 
    'balanced', 
    NOW(),
    NOW()
);

-- Step 5: Create vendor user with ALL required fields
INSERT INTO users (name, email, password, account_role, phone, trust_pref, created_at, updated_at) 
VALUES (
    'Test Vendor', 
    'vendor@gulio.com', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 
    'vendor', 
    '+233000000001', 
    'balanced', 
    NOW(),
    NOW()
);

-- Step 6: Verify the users were created correctly
SELECT id, name, email, account_role, created_at FROM users WHERE account_role IN ('admin', 'vendor');
