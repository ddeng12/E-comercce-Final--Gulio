-- Fix the admin user roles - Copy this to phpMyAdmin

-- Update the admin user to have correct role in both columns
UPDATE users SET role = 'admin' WHERE email = 'admin@gulio.com';

-- Update the vendor user to have correct role in both columns  
UPDATE users SET role = 'vendor' WHERE email = 'vendor@gulio.com';

-- Verify the changes
SELECT id, name, email, role, account_role FROM users WHERE email IN ('admin@gulio.com', 'vendor@gulio.com');
