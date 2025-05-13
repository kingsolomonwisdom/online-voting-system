<?php
require_once 'db/config.php';

echo "<h1>Password Reset Utility</h1>";

// Generate a new hash for admin123
$admin_password = 'admin123';
$admin_hash = password_hash($admin_password, PASSWORD_BCRYPT);

// Generate a new hash for voter123
$voter_password = 'voter123';
$voter_hash = password_hash($voter_password, PASSWORD_BCRYPT);

echo "<h2>Generated Hashes:</h2>";
echo "<p>Admin password hash: " . $admin_hash . "</p>";
echo "<p>Voter password hash: " . $voter_hash . "</p>";

// Update admin password
$stmt = $conn->prepare("UPDATE admins SET password = ? WHERE username = 'admin'");
$stmt->bind_param("s", $admin_hash);
$admin_result = $stmt->execute();

// Update voter password
$stmt = $conn->prepare("UPDATE voters SET password = ? WHERE voter_id = 'VOT001'");
$stmt->bind_param("s", $voter_hash);
$voter_result = $stmt->execute();

echo "<h2>Results:</h2>";

if ($admin_result) {
    echo "<p style='color:green'>✓ Admin password updated successfully</p>";
} else {
    echo "<p style='color:red'>✗ Failed to update admin password: " . $conn->error . "</p>";
}

if ($voter_result) {
    echo "<p style='color:green'>✓ Voter password updated successfully</p>";
} else {
    echo "<p style='color:red'>✗ Failed to update voter password: " . $conn->error . "</p>";
}

// Test password verification
echo "<h2>Password Verification Test:</h2>";
$test_admin = $conn->query("SELECT password FROM admins WHERE username = 'admin'")->fetch_assoc();
$test_voter = $conn->query("SELECT password FROM voters WHERE voter_id = 'VOT001'")->fetch_assoc();

echo "<p>Admin: " . (password_verify($admin_password, $test_admin['password']) ? 
    "<span style='color:green'>✓ Password verification working</span>" : 
    "<span style='color:red'>✗ Password verification failed</span>") . "</p>";
echo "<p>Voter: " . (password_verify($voter_password, $test_voter['password']) ? 
    "<span style='color:green'>✓ Password verification working</span>" : 
    "<span style='color:red'>✗ Password verification failed</span>") . "</p>";

echo "<h2>Login Credentials:</h2>";
echo "<ul>";
echo "<li><strong>Admin:</strong> Username: admin / Password: admin123</li>";
echo "<li><strong>Voter:</strong> ID: VOT001 / Password: voter123</li>";
echo "</ul>";

echo "<p style='color:red'><strong>IMPORTANT:</strong> Delete this file after fixing the passwords!</p>";
echo "<p><a href='index.php' style='padding: 10px; background-color: #3498db; color: white; text-decoration: none; border-radius: 5px;'>Go to login page</a></p>";

// Close connection
$conn->close();
?> 