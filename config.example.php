<?php
/**
 * Database Configuration Template
 * 
 * Copy this file to config.php and update with your database credentials
 * DO NOT commit config.php to version control
 */

// Database configuration
define('DB_HOST', 'localhost');           // Your database host (usually localhost)
define('DB_USER', 'root');                // Your database username
define('DB_PASS', '');                    // Your database password
define('DB_NAME', 'incident_response_db'); // Your database name

// Create database connection
function getDBConnection() {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    return $conn;
}

// Session configuration
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Optional: Set timezone (adjust to your location)
date_default_timezone_set('UTC');

// Optional: Error reporting (set to 0 in production)
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>