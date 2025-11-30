<?php

define('DB_HOST', getenv('DB_HOST') ?: 'localhost');
define('DB_NAME', getenv('DB_NAME') ?: 'u801837948_gulio_prod');
define('DB_USER', getenv('DB_USER') ?: 'u801837948_gulio');
define('DB_PASS', getenv('DB_PASS') ?: 'Dreamsandnightmares3!');
define('DB_CHARSET', getenv('DB_CHARSET') ?: 'utf8mb4');

require_once __DIR__ . '/app.php';
