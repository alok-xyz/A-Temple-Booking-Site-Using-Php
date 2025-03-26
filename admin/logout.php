<?php
require_once '../config/config.php';

// Destroy the session
session_destroy();

// Redirect to admin login page
header('Location: /ram/admin/login.php');
exit(); 