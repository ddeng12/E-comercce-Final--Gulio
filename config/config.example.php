<?php

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'your_database_name');
define('DB_USER', getenv('DB_USER') ?: 'your_database_user');
define('DB_PASS', getenv('DB_PASS') ?: 'your_database_password');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

require_once __DIR__ . '/app.php';

