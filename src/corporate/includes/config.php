<?php
// Corporate config.php – Session management for corporate users
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Check if user is logged in and is a corporate user
if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'corporate'){
    header("location: ../login.php");
    exit;
}

// Ensure user_id is set
if(!isset($_SESSION['user_id'])){
    header("location: ../login.php");
    exit;
}

// Database connection using PDO
require_once '../db.php';
?>

