<?php
/**
 * SKuytov Photography - Configuration
 * Update these settings for your superhosting.bg environment
 */

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'your_database_name');  // Change this
define('DB_USER', 'your_database_user');  // Change this
define('DB_PASS', 'your_database_pass');  // Change this

// Site Configuration
define('SITE_NAME', 'SKuytov Photography');
define('SITE_URL', 'https://yourdomain.com');  // Change this to your actual domain

// If you want the "Download All" button to go to OneDrive/SharePoint (instead of ZIP generation), set this URL:
define('DOWNLOAD_ALL_URL', 'https://skuytovphotography-my.sharepoint.com/:u:/g/personal/s_kuytov_skuytov_eu/IQCHVl1GbmW-T6M5JjMRBGIaAdOR8R4SDIAlPkrmAG1o1uY?e=oBFFox');

define('UPLOAD_DIR', __DIR__ . '/../uploads/');
define('THUMB_DIR', __DIR__ . '/../uploads/thumbnails/');
define('MAX_UPLOAD_SIZE', 20 * 1024 * 1024); // 20MB per photo
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'webp']);
define('THUMB_WIDTH', 400);
define('THUMB_HEIGHT', 300);

// Session
session_start();

// Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 0);

// Timezone
date_default_timezone_set('Europe/Sofia');
