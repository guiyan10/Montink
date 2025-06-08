<?php
// Database configuration
define('DB_HOST', 'localhost');
define('DB_NAME', 'montink');
define('DB_USER', 'root');
define('DB_PASS', '');

// Application configuration
define('APP_NAME', 'Montink');
define('APP_URL', 'http://localhost/testeMontink');

// Email configuration
define('SMTP_HOST', 'smtp.gmail.com');
define('SMTP_PORT', 587);
define('SMTP_USER', 'your-email@gmail.com');
define('SMTP_PASS', 'your-app-password');
define('SMTP_FROM', 'your-email@gmail.com');
define('SMTP_FROM_NAME', 'Montink');

// Webhook configuration
define('WEBHOOK_SECRET', 'your-webhook-secret-key'); // Change this to a secure random string 