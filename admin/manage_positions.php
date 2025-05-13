<?php
require_once '../includes/functions.php';

// Redirect if not logged in as admin
if (!isAdminLoggedIn()) {
    $_SESSION['error'] = "You must be logged in as admin to access this page";
    redirect('../index.php');
}

// Process form submission for adding/editing position
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid form submission, please try again";
        redirect('../admin/manage_positions.php');
    }
    
    // For adding a new position
    if (isset($_POST['add'])) {
        $position_name = clean($_POST['position_name']);
        $max_votes = intval(clean($_POST['max_votes']));
        $position_order = intval(clean($_POST['position_order']));
        
        // Validate input
        if (empty($position_name)) {
            $_SESSION['error'] = "Position name is required";
        } elseif ($max_votes < 1) {
            $_SESSION['error'] = "Maximum votes must be at least 1";
        } else {
            // Check if position already exists
            $stmt = $conn->prepare("SELECT * FROM positions WHERE position_name = ?");
            $stmt->bind_param("s", $position_name);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $_SESSION['error'] = "Position already exists";
            } else {
                // Insert new position
                $stmt = $conn->prepare("INSERT INTO positions (position_name, max_votes, position_order) VALUES (?, ?, ?)");
                $stmt->bind_param("sii", $position_name, $max_votes, $position_order);
                
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Position added successfully";
                } else {
                    $_SESSION['error'] = "Error adding position: " . $conn->error;
                }
            }
        }
    }
    
    // For editing an existing position
    if (isset($_POST['edit'])) {
        $id = intval(clean($_POST['id']));
        $position_name = clean($_POST['position_name']);
        $max_votes = intval(clean($_POST['max_votes']));
        $position_order = intval(clean($_POST['position_order']));
        
        // Validate input
        if (empty($position_name)) {
            $_SESSION['error'] = "Position name is required";
        } elseif ($max_votes < 1) {
            $_SESSION['error'] = "Maximum votes must be at least 1";
        } else {
            // Check if position already exists (except for the current one)
            $stmt = $conn->prepare("SELECT * FROM positions WHERE position_name = ? AND id != ?");
            $stmt->bind_param("si", $position_name, $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $_SESSION['error'] = "Position name already exists";
            } else {
                // Update position
                $stmt = $conn->prepare("UPDATE positions SET position_name = ?, max_votes = ?, position_order = ? WHERE id = ?");
                $stmt->bind_param("siii", $position_name, $max_votes, $position_order, $id);
                
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Position updated successfully";
                } else {
                    $_SESSION['error'] = "Error updating position: " . $conn->error;
                }
            }
        }
    }
    
    // For deleting a position
    if (isset($_POST['delete'])) {
        $id = intval(clean($_POST['id']));
        
        // Check if there are candidates assigned to this position
        $stmt = $conn->prepare("SELECT * FROM candidates WHERE position_id = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if ($result->num_rows > 0) {
            $_SESSION['error'] = "Cannot delete position as it has candidates assigned to it";
        } else {
            // Check if there are votes for this position
            $stmt = $conn->prepare("SELECT * FROM votes WHERE position_id = ?");
            $stmt->bind_param("i", $id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $_SESSION['error'] = "Cannot delete position as it has votes recorded";
            } else {
                // Delete position
                $stmt = $conn->prepare("DELETE FROM positions WHERE id = ?");
                $stmt->bind_param("i", $id);
                
                if ($stmt->execute()) {
                    $_SESSION['message'] = "Position deleted successfully";
                } else {
                    $_SESSION['error'] = "Error deleting position: " . $conn->error;
                }
            }
        }
    }
    
    // Redirect to avoid resubmission
    redirect('../admin/manage_positions.php');
}

include '../includes/header.php';

// Get position to edit if ID is provided
$position_to_edit = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $id = intval(clean($_GET['edit']));
    $stmt = $conn->prepare("SELECT * FROM positions WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $position_to_edit = $result->fetch_assoc();
    }
}

// Get all positions
$positions = $conn->query("SELECT * FROM positions ORDER BY position_order");
?>

<h2 style="text-align: center; margin-bottom: 30px;">Manage Positions</h2>

<div style="margin-bottom: 30px;">
    <h3><?php echo ($position_to_edit) ? 'Edit Position' : 'Add New Position'; ?></h3>
    <form action="../admin/manage_positions.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <?php if ($position_to_edit): ?>
            <input type="hidden" name="id" value="<?php echo $position_to_edit['id']; ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="position_name">Position Name:</label>
            <input type="text" name="position_name" id="position_name" class="form-control" value="<?php echo ($position_to_edit) ? htmlspecialchars($position_to_edit['position_name']) : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="max_votes">Maximum Votes Allowed:</label>
            <input type="number" name="max_votes" id="max_votes" class="form-control" min="1" value="<?php echo ($position_to_edit) ? $position_to_edit['max_votes'] : '1'; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="position_order">Display Order:</label>
            <input type="number" name="position_order" id="position_order" class="form-control" min="0" value="<?php echo ($position_to_edit) ? $position_to_edit['position_order'] : '0'; ?>" required>
        </div>
        
        <div class="form-group">
            <?php if ($position_to_edit): ?>
                <button type="submit" name="edit" class="btn btn-success">Update Position</button>
                <a href="../admin/manage_positions.php" class="btn btn-primary">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add" class="btn btn-primary">Add Position</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<h3>Available Positions</h3>
<?php if ($positions->num_rows == 0): ?>
    <p>No positions added yet.</p>
<?php else: ?>
    <div style="overflow-x: auto;">
        <table class="table">
            <thead>
                <tr>
                    <th>Position Name</th>
                    <th>Max Votes</th>
                    <th>Display Order</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($position = $positions->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($position['position_name']); ?></td>
                        <td><?php echo $position['max_votes']; ?></td>
                        <td><?php echo $position['position_order']; ?></td>
                        <td>
                            <a href="../admin/manage_positions.php?edit=<?php echo $position['id']; ?>" class="btn btn-primary">Edit</a>
                            
                            <form action="../admin/manage_positions.php" method="POST" style="display: inline;">
                                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                                <input type="hidden" name="id" value="<?php echo $position['id']; ?>">
                                <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this position?');">Delete</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    </div>
<?php endif; ?>

<?php include '../includes/footer.php'; ?> 