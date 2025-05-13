<?php
require_once 'includes/functions.php';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirect('dashboard.php');
} elseif (isVoterLoggedIn()) {
    redirect('vote.php');
}

// Process login form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = clean($_POST['username']);
    $password = $_POST['password']; // Don't clean password as it can contain special chars
    $login_as = clean($_POST['login_as']);
    
    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Username and password are required";
    } else {
        // Admin login
        if ($login_as == 'admin') {
            $sql = "SELECT * FROM admins WHERE username = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $admin = $result->fetch_assoc();
                
                // Debugging - should be removed in production
                debug_to_console("Checking admin: " . $username);
                debug_to_console("Hash: " . substr($admin['password'], 0, 10) . "...");
                
                if (password_verify($password, $admin['password'])) {
                    // Update last login timestamp
                    $update = $conn->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
                    $update->bind_param("i", $admin['id']);
                    $update->execute();
                    
                    $_SESSION['admin'] = $admin['id'];
                    $_SESSION['admin_name'] = $admin['name'];
                    $_SESSION['last_activity'] = time();
                    redirect('dashboard.php');
                } else {
                    $_SESSION['error'] = "Invalid password";
                }
            } else {
                $_SESSION['error'] = "Admin not found";
            }
        } 
        // Voter login
        else {
            $sql = "SELECT * FROM voters WHERE voter_id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows == 1) {
                $voter = $result->fetch_assoc();
                
                // Debugging - should be removed in production
                debug_to_console("Checking voter: " . $username);
                debug_to_console("Hash: " . substr($voter['password'], 0, 10) . "...");
                
                if (password_verify($password, $voter['password'])) {
                    if ($voter['status'] == 1) {
                        // Update last login timestamp
                        $update = $conn->prepare("UPDATE voters SET last_login = NOW() WHERE id = ?");
                        $update->bind_param("i", $voter['id']);
                        $update->execute();
                        
                        $_SESSION['voter'] = $voter['id'];
                        $_SESSION['voter_name'] = $voter['firstname'] . ' ' . $voter['lastname'];
                        $_SESSION['last_activity'] = time();
                        redirect('vote.php');
                    } else {
                        $_SESSION['error'] = "Your account is deactivated. Please contact the administrator.";
                    }
                } else {
                    $_SESSION['error'] = "Invalid password";
                }
            } else {
                $_SESSION['error'] = "Voter ID not found";
            }
        }
    }
}

include 'includes/header.php';
?>

<h2 style="text-align: center; margin-bottom: 20px;">Login to Vote</h2>

<div style="max-width: 500px; margin: 0 auto;">
    <form action="index.php" method="POST">
        <div class="form-group">
            <label for="username">Username / Voter ID:</label>
            <input type="text" name="username" id="username" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:</label>
            <input type="password" name="password" id="password" class="form-control" required>
        </div>
        
        <div class="form-group">
            <label>Login as:</label>
            <div>
                <label style="margin-right: 20px;">
                    <input type="radio" name="login_as" value="voter" checked> Voter
                </label>
                <label>
                    <input type="radio" name="login_as" value="admin"> Admin
                </label>
            </div>
        </div>
        
        <div class="form-group" style="text-align: center;">
            <button type="submit" class="btn btn-primary">Login</button>
        </div>
    </form>
</div>

<div style="text-align: center; margin-top: 30px;">
    <p>Default Admin: <strong>admin</strong> / Password: <strong>admin123</strong></p>
    <p>Default Voter: <strong>VOT001</strong> / Password: <strong>voter123</strong></p>
</div>

<?php include 'includes/footer.php'; ?> 