<?php
/**
 * Create Admin User Script
 * Run this once to create an admin user for the system
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/auth.php';

try {
    $db = Database::getInstance();
    
    echo "<h2>👤 Creating Admin User...</h2>";
    
    // Admin user details
    $adminEmail = 'admin@gulio.com';
    $adminPassword = 'secret'; // Simple password for testing - change this!
    $adminName = 'System Administrator';
    
    // Check if admin already exists
    $existingAdmin = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$adminEmail]);
    
    if ($existingAdmin) {
        echo "<p>⚠️ Admin user already exists with email: {$adminEmail}</p>";
        
        // Update existing user to admin role
        $updateResult = $db->query("UPDATE users SET account_role = 'admin' WHERE email = ?", [$adminEmail]);
        if ($updateResult) {
            echo "<p>✅ Updated existing user to admin role</p>";
        }
    } else {
        // Create new admin user
        $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
        
        $insertSql = "INSERT INTO users (name, email, password, account_role, phone, trust_pref, created_at) VALUES (?, ?, ?, 'admin', '+233000000000', 'balanced', NOW())";
        
        $result = $db->query($insertSql, [
            $adminName,
            $adminEmail,
            $hashedPassword
        ]);
        
        if ($result) {
            echo "<p>✅ Admin user created successfully!</p>";
        } else {
            echo "<p>❌ Failed to create admin user</p>";
        }
    }
    
    // Also create a vendor user for testing
    $vendorEmail = 'vendor@gulio.com';
    $vendorPassword = 'secret';
    $vendorName = 'Test Vendor';
    
    $existingVendor = $db->fetchOne("SELECT id FROM users WHERE email = ?", [$vendorEmail]);
    
    if (!$existingVendor) {
        $hashedVendorPassword = password_hash($vendorPassword, PASSWORD_DEFAULT);
        
        $vendorResult = $db->query($insertSql, [
            $vendorName,
            $vendorEmail,
            $hashedVendorPassword
        ]);
        
        // Update to vendor role
        $db->query("UPDATE users SET account_role = 'vendor' WHERE email = ?", [$vendorEmail]);
        
        if ($vendorResult) {
            echo "<p>✅ Vendor user created successfully!</p>";
        }
    } else {
        echo "<p>ℹ️ Vendor user already exists</p>";
    }
    
    echo "<hr>";
    echo "<h3>🔑 Login Credentials:</h3>";
    echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h4>Admin Login:</h4>";
    echo "<p><strong>Email:</strong> {$adminEmail}</p>";
    echo "<p><strong>Password:</strong> {$adminPassword}</p>";
    echo "<p><strong>Access:</strong> <a href='?page=login'>Regular Login Page</a> (will auto-redirect to admin)</p>";
    echo "</div>";
    
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<h4>Vendor Login:</h4>";
    echo "<p><strong>Email:</strong> {$vendorEmail}</p>";
    echo "<p><strong>Password:</strong> {$vendorPassword}</p>";
    echo "<p><strong>Access:</strong> <a href='?page=login'>Regular Login Page</a> (will auto-redirect to admin)</p>";
    echo "</div>";
    
    echo "<hr>";
    echo "<h3>🚀 Next Steps:</h3>";
    echo "<ol>";
    echo "<li><a href='?page=login'>Login as Admin</a> (use admin credentials)</li>";
    echo "<li><a href='admin/'>Access Admin Dashboard</a></li>";
    echo "<li>Add products through the admin panel</li>";
    echo "<li><strong>IMPORTANT:</strong> Change the default passwords!</li>";
    echo "</ol>";
    
    echo "<div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0; border-left: 4px solid #ffc107;'>";
    echo "<h4>⚠️ Security Notice:</h4>";
    echo "<p>Please change the default passwords immediately after first login!</p>";
    echo "<p>Delete this script after creating the admin user for security.</p>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<h3>❌ Error: " . htmlspecialchars($e->getMessage()) . "</h3>";
}
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 800px;
    margin: 50px auto;
    padding: 20px;
    background: #f5f5f5;
    line-height: 1.6;
}

h2, h3, h4 {
    color: #333;
}

p {
    margin: 10px 0;
}

a {
    color: #007bff;
    text-decoration: none;
}

a:hover {
    text-decoration: underline;
}

ol {
    padding-left: 20px;
}

li {
    margin: 8px 0;
}
</style>
