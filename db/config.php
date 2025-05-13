<?php
session_start();

// Database configuration
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'online_voting_system';

// Create connection
$conn = new mysqli($host, $username, $password, $database);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Set character set to utf8mb4
$conn->set_charset("utf8mb4");

// Function to secure user input
function clean($data) {
    global $conn;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    $data = $conn->real_escape_string($data);
    return $data;
}

// Function to generate a secure hash of passwords
function password_encrypt($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);
}

// Function to verify password - using PHP's native password_verify function directly
function password_verify_custom($password, $hash) {
    return password_verify($password, $hash);
}

// Simple debug function - use only during development, remove in production
function debug_to_console($data) {
    echo '<script>';
    echo 'console.log(' . json_encode($data) . ')';
    echo '</script>';
}
?> 