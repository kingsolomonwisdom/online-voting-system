<?php
require_once 'includes/functions.php';

// Verify CSRF token if provided
if (isset($_GET['token'])) {
    if (!verifyCSRFToken($_GET['token'])) {
        $_SESSION['error'] = "Invalid logout request";
        redirect('index.php');
    }
}

// Record logout time if user was logged in
if (isAdminLoggedIn()) {
    $admin_id = $_SESSION['admin'];
    $stmt = $conn->prepare("UPDATE admins SET last_logout = NOW() WHERE id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
} elseif (isVoterLoggedIn()) {
    $voter_id = $_SESSION['voter'];
    $stmt = $conn->prepare("UPDATE voters SET last_logout = NOW() WHERE id = ?");
    $stmt->bind_param("i", $voter_id);
    $stmt->execute();
}

// Destroy all session data
session_unset();
session_destroy();

// Start a new session and set a message
session_start();
$_SESSION['message'] = "You have been successfully logged out";

// Redirect to the login page
redirect('index.php');
?> 