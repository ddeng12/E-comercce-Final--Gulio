<?php
/**
 * Fix Admin Login - Verify and Reset Admin User
 */

require_once __DIR__ . '/config/config.php';
require_once __DIR__ . '/includes/database.php';
require_once __DIR__ . '/includes/auth.php';

$db = Database::getInstance();

echo "<h2>🔧 Fixing Admin Login</h2>";

// Admin credentials
$adminEmail = 'admin@gulio.com';
$adminPassword = 'secret';
$adminName = 'System Administrator';

// Check if admin exists
$existingAdmin = $db->fetchOne("SELECT * FROM users WHERE email = ?", [$adminEmail]);

if ($existingAdmin) {
    echo "<p>✅ Admin user found in database</p>";
    echo "<pre>";
    print_r($existingAdmin);
    echo "</pre>";
    
    // Check if account_role column exists
    $columns = $db->fetchAll("SHOW COLUMNS FROM users LIKE 'account_role'");
    if (empty($columns)) {
        echo "<p>⚠️ account_role column missing. Adding it...</p>";
        $db->query("ALTER TABLE users ADD COLUMN account_role VARCHAR(20) DEFAULT 'user'");
        echo "<p>✅ account_role column added</p>";
    }
    
    // Update admin user with correct password and role
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    // Check if updated_at column exists
    $columns = $db->fetchAll("SHOW COLUMNS FROM users LIKE 'updated_at'");
    $hasUpdatedAt = !empty($columns);
    
    if ($hasUpdatedAt) {
        $updateSql = "UPDATE users SET 
                      password = ?, 
                      account_role = 'admin',
                      role = 'admin',
                      updated_at = NOW()
                      WHERE email = ?";
    } else {
        $updateSql = "UPDATE users SET 
                      password = ?, 
                      account_role = 'admin',
                      role = 'admin'
                      WHERE email = ?";
    }
    
    $result = $db->query($updateSql, [$hashedPassword, $adminEmail]);
    
    if ($result) {
        echo "<p>✅ Admin user updated successfully!</p>";
    } else {
        echo "<p>❌ Failed to update admin user</p>";
    }
} else {
    echo "<p>⚠️ Admin user not found. Creating new admin user...</p>";
    
    // Check if updated_at column exists
    $columns = $db->fetchAll("SHOW COLUMNS FROM users LIKE 'updated_at'");
    $hasUpdatedAt = !empty($columns);
    
    $hashedPassword = password_hash($adminPassword, PASSWORD_DEFAULT);
    
    if ($hasUpdatedAt) {
        $insertSql = "INSERT INTO users (name, email, password, account_role, role, phone, trust_pref, created_at, updated_at) 
                      VALUES (?, ?, ?, 'admin', 'admin', '+233000000000', 'balanced', NOW(), NOW())";
    } else {
        $insertSql = "INSERT INTO users (name, email, password, account_role, role, phone, trust_pref, created_at) 
                      VALUES (?, ?, ?, 'admin', 'admin', '+233000000000', 'balanced', NOW())";
    }
    
    $result = $db->query($insertSql, [$adminName, $adminEmail, $hashedPassword]);
    
    if ($result) {
        echo "<p>✅ Admin user created successfully!</p>";
    } else {
        echo "<p>❌ Failed to create admin user</p>";
    }
}

// Verify the admin user
$admin = $db->fetchOne("SELECT id, name, email, account_role, role FROM users WHERE email = ?", [$adminEmail]);

echo "<hr>";
echo "<h3>✅ Admin User Verification:</h3>";
if ($admin) {
    echo "<div style='background: #d4edda; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
    echo "<p><strong>ID:</strong> " . $admin['id'] . "</p>";
    echo "<p><strong>Name:</strong> " . htmlspecialchars($admin['name']) . "</p>";
    echo "<p><strong>Email:</strong> " . htmlspecialchars($admin['email']) . "</p>";
    echo "<p><strong>Account Role:</strong> " . ($admin['account_role'] ?? 'NOT SET') . "</p>";
    echo "<p><strong>Role:</strong> " . ($admin['role'] ?? 'NOT SET') . "</p>";
    echo "</div>";
    
    // Test password verification
    $testUser = $db->fetchOne("SELECT password FROM users WHERE email = ?", [$adminEmail]);
    if ($testUser && password_verify($adminPassword, $testUser['password'])) {
        echo "<p style='color: green;'>✅ Password verification successful!</p>";
    } else {
        echo "<p style='color: red;'>❌ Password verification failed!</p>";
    }
} else {
    echo "<p style='color: red;'>❌ Admin user still not found after creation attempt</p>";
}

echo "<hr>";
echo "<h3>🔑 Login Credentials:</h3>";
echo "<div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>";
echo "<p><strong>Email:</strong> {$adminEmail}</p>";
echo "<p><strong>Password:</strong> {$adminPassword}</p>";
echo "<p><strong>Login URL:</strong> <a href='?page=login'>Click here to login</a></p>";
echo "</div>";

echo "<hr>";
echo "<h3>📋 Database Check:</h3>";
$allUsers = $db->fetchAll("SELECT id, name, email, account_role, role FROM users LIMIT 10");
echo "<table border='1' cellpadding='10' style='border-collapse: collapse; width: 100%;'>";
echo "<tr><th>ID</th><th>Name</th><th>Email</th><th>Account Role</th><th>Role</th></tr>";
foreach ($allUsers as $user) {
    echo "<tr>";
    echo "<td>" . $user['id'] . "</td>";
    echo "<td>" . htmlspecialchars($user['name']) . "</td>";
    echo "<td>" . htmlspecialchars($user['email']) . "</td>";
    echo "<td>" . ($user['account_role'] ?? 'NULL') . "</td>";
    echo "<td>" . ($user['role'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";
?>

<style>
body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    max-width: 900px;
    margin: 50px auto;
    padding: 20px;
    background: #f5f5f5;
    line-height: 1.6;
}

h2, h3 {
    color: #333;
}

table {
    margin-top: 20px;
}

th {
    background: #667eea;
    color: white;
    padding: 10px;
    text-align: left;
}

td {
    padding: 10px;
    background: white;
}
</style>

