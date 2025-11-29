<?php
// PitWall F1 CMS - Database Configuration
// IMPORTANT: Update these values with your actual database credentials

define('DB_HOST', 'localhost');
define('DB_NAME', 'fnc30751_pitwall_f1');
define('DB_USER', 'fnc30751_pitwall_f1');
define('DB_PASS', 'The.Angel99!');
define('DB_CHARSET', 'utf8mb4');

// Site Configuration
define('SITE_NAME', 'PitWall Insights');
define('SITE_URL', 'https://pitwall-insights.com');
define('ADMIN_URL', SITE_URL . '/admin');

// Security
define('SESSION_LIFETIME', 7200); // 2 hours in seconds
define('HASH_ALGO', PASSWORD_DEFAULT);

// Pagination
define('ITEMS_PER_PAGE', 20);

// Upload settings
define('UPLOAD_DIR', dirname(__DIR__) . '/uploads/');
define('MAX_FILE_SIZE', 5242880); // 5MB in bytes
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Timezone
date_default_timezone_set('Europe/Ljubljana');

// Error reporting (disable in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
