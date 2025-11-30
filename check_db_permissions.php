<?php
/**
 * Database Permissions Check Script
 * Checks what databases the user can access
 */

require_once __DIR__ . '/config/config.php';

echo "<!DOCTYPE html>
<html>
<head>
    <title>Database Permissions Check</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; }
        .success { color: green; background: #d4edda; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: red; background: #f8d7da; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .info { color: #004085; background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #856404; background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0; }
        h1 { color: #333; }
        h2 { color: #666; margin-top: 30px; }
        ul { line-height: 1.8; }
        code { background: #f4f4f4; padding: 2px 6px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>🔐 Database Permissions Check</h1>";

try {
    // Try to connect without specifying a database first
    $dsn = sprintf("mysql:host=%s;charset=%s", DB_HOST, DB_CHARSET);
    
    $options = [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ];
    
    echo "<div class='info'>Attempting to connect to MySQL server...</div>";
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    echo "<div class='success'>✅ Connected to MySQL server successfully!</div>";
    
    // Get list of databases the user can access
    echo "<h2>Available Databases:</h2>";
    $stmt = $pdo->query("SHOW DATABASES");
    $databases = $stmt->fetchAll(PDO::FETCH_COLUMN);
    
    if (empty($databases)) {
        echo "<div class='error'>❌ No databases found or user has no access to any databases.</div>";
    } else {
        echo "<div class='success'>✅ Found " . count($databases) . " accessible database(s):</div>";
        echo "<ul>";
        $targetFound = false;
        foreach ($databases as $db) {
            $isTarget = ($db === DB_NAME);
            if ($isTarget) {
                $targetFound = true;
                echo "<li><strong style='color: green;'>" . htmlspecialchars($db) . " ← This is your target database!</strong></li>";
            } else {
                echo "<li>" . htmlspecialchars($db) . "</li>";
            }
        }
        echo "</ul>";
        
        if (!$targetFound) {
            echo "<div class='warning'>⚠️ <strong>WARNING:</strong> The database <code>" . htmlspecialchars(DB_NAME) . "</code> was NOT found in the list above!</div>";
            echo "<div class='info'>";
            echo "<strong>Possible reasons:</strong><br><br>";
            echo "1. <strong>Database doesn't exist:</strong> You need to create it in Hostinger hPanel<br>";
            echo "2. <strong>Database name is different:</strong> Check the exact name in Hostinger (case-sensitive)<br>";
            echo "3. <strong>User not assigned:</strong> The database user needs to be assigned to the database<br><br>";
            echo "<strong>How to fix in Hostinger hPanel:</strong><br><br>";
            echo "1. Go to <strong>Databases → MySQL Databases</strong><br>";
            echo "2. Find your database: <code>" . htmlspecialchars(DB_NAME) . "</code><br>";
            echo "3. Click <strong>Manage</strong> or <strong>Edit</strong><br>";
            echo "4. Under <strong>Users</strong> or <strong>Privileges</strong>, make sure user <code>" . htmlspecialchars(DB_USER) . "</code> is listed<br>";
            echo "5. If not listed, click <strong>Add User</strong> or <strong>Assign User</strong> and select <code>" . htmlspecialchars(DB_USER) . "</code><br>";
            echo "6. Grant <strong>ALL PRIVILEGES</strong> to the user<br>";
            echo "</div>";
        } else {
            // Try to connect to the specific database
            echo "<h2>Testing Connection to Target Database:</h2>";
            try {
                $dsnWithDb = sprintf("mysql:host=%s;dbname=%s;charset=%s", DB_HOST, DB_NAME, DB_CHARSET);
                $pdoDb = new PDO($dsnWithDb, DB_USER, DB_PASS, $options);
                echo "<div class='success'>✅ <strong>SUCCESS!</strong> Can connect to database <code>" . htmlspecialchars(DB_NAME) . "</code>!</div>";
                
                // Check tables
                $stmt = $pdoDb->query("SHOW TABLES");
                $tables = $stmt->fetchAll(PDO::FETCH_COLUMN);
                
                if (empty($tables)) {
                    echo "<div class='warning'>⚠️ Database is empty. You need to run <a href='setup.php'>setup.php</a> to create tables.</div>";
                } else {
                    echo "<div class='success'>✅ Database has " . count($tables) . " table(s). Ready to use!</div>";
                }
            } catch (PDOException $e) {
                echo "<div class='error'>❌ Cannot connect to database <code>" . htmlspecialchars(DB_NAME) . "</code></div>";
                echo "<div class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
                echo "<div class='info'>";
                echo "Even though the database appears in the list, the user may not have proper permissions.<br>";
                echo "Go to Hostinger hPanel → Databases → MySQL Databases → Manage your database → Assign user and grant ALL PRIVILEGES.";
                echo "</div>";
            }
        }
    }
    
    // Check user privileges
    echo "<h2>User Privileges:</h2>";
    try {
        $stmt = $pdo->query("SHOW GRANTS FOR CURRENT_USER()");
        $grants = $stmt->fetchAll(PDO::FETCH_COLUMN);
        
        echo "<div class='info'>Current user privileges:</div>";
        echo "<ul>";
        foreach ($grants as $grant) {
            echo "<li><code>" . htmlspecialchars($grant) . "</code></li>";
        }
        echo "</ul>";
    } catch (PDOException $e) {
        echo "<div class='warning'>Could not retrieve user privileges: " . htmlspecialchars($e->getMessage()) . "</div>";
    }
    
} catch (PDOException $e) {
    echo "<div class='error'>❌ <strong>CONNECTION FAILED!</strong></div>";
    echo "<div class='error'>Error: " . htmlspecialchars($e->getMessage()) . "</div>";
    
    if (strpos($e->getMessage(), 'Access denied') !== false) {
        echo "<div class='info'>";
        echo "<strong>Authentication failed. Please check:</strong><br><br>";
        echo "1. Username: <code>" . htmlspecialchars(DB_USER) . "</code><br>";
        echo "2. Password: Make sure it matches exactly in Hostinger hPanel<br>";
        echo "3. User exists: Verify the user exists in Hostinger → Databases → MySQL Users<br>";
        echo "</div>";
    }
}

echo "</body></html>";
?>

