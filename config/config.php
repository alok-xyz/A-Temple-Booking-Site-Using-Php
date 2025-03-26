<?php
// Base URL Configuration
define('BASE_URL', '/ram');  // Always use /ram

// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'ram_janmabhoomi');

// Razorpay Test Mode Credentials
define('RAZORPAY_KEY_ID', 'rzp_test_ykJT9pz3eI8bEH');  // Your test key ID
define('RAZORPAY_KEY_SECRET', '5bTagqXDtzR4WW23MYyD6Xy2');  // Your test key secret

// Database connection
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Start session if not already started
session_start();

// Helper functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['role']) && $_SESSION['role'] === 'admin';
} 