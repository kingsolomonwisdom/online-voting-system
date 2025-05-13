<?php
// Fix the path to ensure it's properly loaded from any location
require_once __DIR__ . '/../db/config.php';

// Session timeout in seconds (30 minutes)
define('SESSION_TIMEOUT', 1800);

// Check for session timeout
function checkSessionTimeout() {
    if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity'] > SESSION_TIMEOUT)) {
        // Session has expired
        session_unset();
        session_destroy();
        return true;
    }
    
    // Update last activity time
    $_SESSION['last_activity'] = time();
    return false;
}

// Generate CSRF token
function generateCSRFToken() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

// Verify CSRF token
function verifyCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Function to determine the base URL for links
function getBaseUrl() {
    $base_url = '';
    $script_name = $_SERVER['SCRIPT_NAME'];
    $script_path = dirname($script_name);
    
    // Check if we're in admin folder and adjust path accordingly
    if (strpos($script_path, '/admin') !== false) {
        $base_url = '../';
    }
    
    return $base_url;
}

// Function to check if user is logged in as admin
function isAdminLoggedIn() {
    if (checkSessionTimeout()) {
        return false;
    }
    return isset($_SESSION['admin']) && !empty($_SESSION['admin']);
}

// Function to check if user is logged in as voter
function isVoterLoggedIn() {
    if (checkSessionTimeout()) {
        return false;
    }
    return isset($_SESSION['voter']) && !empty($_SESSION['voter']);
}

// Redirect function
function redirect($location) {
    header('Location: ' . $location);
    exit();
}

// Function to display error message
function displayError($message) {
    return "<div class='alert alert-danger'>$message</div>";
}

// Function to display success message
function displaySuccess($message) {
    return "<div class='alert alert-success'>$message</div>";
}

// Function to check if voter has already voted for a position
function hasVoted($voter_id, $position_id) {
    global $conn;
    
    $sql = "SELECT * FROM votes WHERE voter_id = ? AND position_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $voter_id, $position_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

// Function to get total votes for a candidate
function getCandidateVotes($candidate_id) {
    global $conn;
    
    $sql = "SELECT COUNT(*) as total FROM votes WHERE candidate_id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $candidate_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    
    return $row['total'];
}

// Function to get all positions
function getPositions() {
    global $conn;
    
    $sql = "SELECT * FROM positions WHERE status = 1 ORDER BY position_order";
    $result = $conn->query($sql);
    
    return $result;
}

// Function to get candidates by position
function getCandidatesByPosition($position_id) {
    global $conn;
    
    $sql = "SELECT * FROM candidates WHERE position_id = ? ORDER BY lastname, firstname";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $position_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result;
}

// Function to check if voter has completed voting
function hasCompletedVoting($voter_id) {
    global $conn;
    
    $sql = "SELECT p.id FROM positions p 
            LEFT JOIN votes v ON p.id = v.position_id AND v.voter_id = ? 
            WHERE v.id IS NULL AND p.status = 1";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $voter_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows == 0;
}

// Generate a random voter ID
function generateVoterId() {
    return 'VOT' . str_pad(mt_rand(1, 999), 3, '0', STR_PAD_LEFT);
}

// Validate and sanitize email
function validateEmail($email) {
    $email = filter_var($email, FILTER_SANITIZE_EMAIL);
    return filter_var($email, FILTER_VALIDATE_EMAIL) ? $email : false;
}
?> 