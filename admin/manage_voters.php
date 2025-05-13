<?php
require_once '../includes/functions.php';

// Redirect if not logged in as admin
if (!isAdminLoggedIn()) {
    $_SESSION['error'] = "You must be logged in as admin to access this page";
    redirect('../index.php');
}

// Process form submission for adding/editing voter
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // For adding a new voter
    if (isset($_POST['add'])) {
        $voter_id = clean($_POST['voter_id']);
        $password = $_POST['password'];
        $firstname = clean($_POST['firstname']);
        $lastname = clean($_POST['lastname']);
        
        // Validate input
        if (empty($voter_id) || empty($password) || empty($firstname) || empty($lastname)) {
            $_SESSION['error'] = "All fields are required";
        } else {
            // Check if voter ID already exists
            $stmt = $conn->prepare("SELECT * FROM voters WHERE voter_id = ?");
            $stmt->bind_param("s", $voter_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $_SESSION['error'] = "Voter ID already exists";
            } else {
                // Hash the password
                $hashed_password = password_encrypt($password);
                
                // Insert new voter
                $stmt = $conn->prepare("INSERT INTO voters (voter_id, password, firstname, lastname) VALUES (?, ?, ?, ?)");
                $stmt->bind_param("ssss", $voter_id, $hashed_password, $firstname, $lastname);
                
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Voter added successfully";
                } else {
                    $_SESSION['error'] = "Error adding voter: " . $conn->error;
                }
            }
        }
    }
    
    // For editing an existing voter
    if (isset($_POST['edit'])) {
        $id = clean($_POST['id']);
        $voter_id = clean($_POST['voter_id']);
        $firstname = clean($_POST['firstname']);
        $lastname = clean($_POST['lastname']);
        $password = $_POST['password'];
        $status = isset($_POST['status']) ? 1 : 0;
        
        // Validate input
        if (empty($voter_id) || empty($firstname) || empty($lastname)) {
            $_SESSION['error'] = "Voter ID, first name, and last name are required";
        } else {
            // Check if voter ID already exists (except for the current one)
            $stmt = $conn->prepare("SELECT * FROM voters WHERE voter_id = ? AND id != ?");
            $stmt->bind_param("si", $voter_id, $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $_SESSION['error'] = "Voter ID already exists";
            } else {
                // Update voter
                if (!empty($password)) {
                    // Hash the new password
                    $hashed_password = password_encrypt($password);
                    
                    // Update with new password
                    $stmt = $conn->prepare("UPDATE voters SET voter_id = ?, password = ?, firstname = ?, lastname = ?, status = ? WHERE id = ?");
                    $stmt->bind_param("ssssii", $voter_id, $hashed_password, $firstname, $lastname, $status, $id);
                } else {
                    // Update without changing password
                    $stmt = $conn->prepare("UPDATE voters SET voter_id = ?, firstname = ?, lastname = ?, status = ? WHERE id = ?");
                    $stmt->bind_param("sssii", $voter_id, $firstname, $lastname, $status, $id);
                }
                
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Voter updated successfully";
                } else {
                    $_SESSION['error'] = "Error updating voter: " . $conn->error;
                }
            }
        }
    }
    
    // For deleting a voter
    if (isset($_POST['delete'])) {
        $id = clean($_POST['id']);
        
        // Check if voter has already voted
        $stmt = $conn->prepare("SELECT * FROM votes WHERE voter_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Cannot delete voter as they have already cast votes";
        } else {
            // Delete voter
            $stmt = $conn->prepare("DELETE FROM voters WHERE id = ?");
            $stmt->bind_param("i", $id);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Voter deleted successfully";
            } else {
                $_SESSION['error'] = "Error deleting voter: " . $conn->error;
            }
        }
    }
    
    // Redirect to avoid resubmission
    redirect('../admin/manage_voters.php');
}

include '../includes/header.php';

// Get voter to edit if ID is provided
$voter_to_edit = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $id = clean($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM voters WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $voter_to_edit = $result->fetch_assoc();
    }
}

// Get all voters
$voters = $conn->query("SELECT * FROM voters ORDER BY lastname, firstname");
?>

<h2 style="text-align: center; margin-bottom: 30px;">Manage Voters</h2>

<div style="margin-bottom: 30px;">
    <h3><?php echo ($voter_to_edit) ? 'Edit Voter' : 'Add New Voter'; ?></h3>
    <form action="../admin/manage_voters.php" method="POST">
        <?php if ($voter_to_edit): ?>
            <input type="hidden" name="id" value="<?php echo $voter_to_edit['id']; ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="voter_id">Voter ID:</label>
            <input type="text" name="voter_id" id="voter_id" class="form-control" value="<?php echo ($voter_to_edit) ? $voter_to_edit['voter_id'] : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="password">Password:<?php echo ($voter_to_edit) ? ' (Leave blank to keep current password)' : ''; ?></label>
            <input type="password" name="password" id="password" class="form-control" <?php echo ($voter_to_edit) ? '' : 'required'; ?>>
        </div>
        
        <div class="form-group">
            <label for="firstname">First Name:</label>
            <input type="text" name="firstname" id="firstname" class="form-control" value="<?php echo ($voter_to_edit) ? $voter_to_edit['firstname'] : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="lastname">Last Name:</label>
            <input type="text" name="lastname" id="lastname" class="form-control" value="<?php echo ($voter_to_edit) ? $voter_to_edit['lastname'] : ''; ?>" required>
        </div>
        
        <?php if ($voter_to_edit): ?>
            <div class="form-group">
                <label>
                    <input type="checkbox" name="status" <?php echo ($voter_to_edit['status'] == 1) ? 'checked' : ''; ?>> Active Account
                </label>
            </div>
        <?php endif; ?>
        
        <div class="form-group">
            <?php if ($voter_to_edit): ?>
                <button type="submit" name="edit" class="btn btn-success">Update Voter</button>
                <a href="../admin/manage_voters.php" class="btn btn-primary">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add" class="btn btn-primary">Add Voter</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<h3>All Voters</h3>
<?php if ($voters->num_rows == 0): ?>
    <p>No voters added yet.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Voter ID</th>
                <th>Name</th>
                <th>Status</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($voter = $voters->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $voter['voter_id']; ?></td>
                    <td><?php echo $voter['firstname'] . ' ' . $voter['lastname']; ?></td>
                    <td><?php echo ($voter['status'] == 1) ? 'Active' : 'Inactive'; ?></td>
                    <td>
                        <a href="../admin/manage_voters.php?edit=<?php echo $voter['id']; ?>" class="btn btn-primary">Edit</a>
                        
                        <form action="../admin/manage_voters.php" method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $voter['id']; ?>">
                            <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this voter?');">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../includes/footer.php'; ?> 