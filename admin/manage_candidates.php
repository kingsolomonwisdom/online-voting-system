<?php
require_once '../includes/functions.php';

// Redirect if not logged in as admin
if (!isAdminLoggedIn()) {
    $_SESSION['error'] = "You must be logged in as admin to access this page";
    redirect('../index.php');
}

// Process form submission for adding/editing candidate
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // For adding a new candidate
    if (isset($_POST['add'])) {
        $position_id = clean($_POST['position_id']);
        $firstname = clean($_POST['firstname']);
        $lastname = clean($_POST['lastname']);
        $platform = clean($_POST['platform']);
        
        // Validate input
        if (empty($position_id) || empty($firstname) || empty($lastname) || empty($platform)) {
            $_SESSION['error'] = "All fields are required";
        } else {
            // Insert new candidate
            $stmt = $conn->prepare("INSERT INTO candidates (position_id, firstname, lastname, platform) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("isss", $position_id, $firstname, $lastname, $platform);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Candidate added successfully";
            } else {
                $_SESSION['error'] = "Error adding candidate: " . $conn->error;
            }
        }
    }
    
    // For editing an existing candidate
    if (isset($_POST['edit'])) {
        $id = clean($_POST['id']);
        $position_id = clean($_POST['position_id']);
        $firstname = clean($_POST['firstname']);
        $lastname = clean($_POST['lastname']);
        $platform = clean($_POST['platform']);
        
        // Validate input
        if (empty($position_id) || empty($firstname) || empty($lastname) || empty($platform)) {
            $_SESSION['error'] = "All fields are required";
        } else {
            // Update candidate
            $stmt = $conn->prepare("UPDATE candidates SET position_id = ?, firstname = ?, lastname = ?, platform = ? WHERE id = ?");
            $stmt->bind_param("isssi", $position_id, $firstname, $lastname, $platform, $id);
            
            if ($stmt->execute()) {
                $_SESSION['message'] = "Candidate updated successfully";
            } else {
                $_SESSION['error'] = "Error updating candidate: " . $conn->error;
            }
        }
    }
    
    // For deleting a candidate
    if (isset($_POST['delete'])) {
        $id = clean($_POST['id']);
        
        // Delete candidate
        $stmt = $conn->prepare("DELETE FROM candidates WHERE id = ?");
        $stmt->bind_param("i", $id);
        
        if ($stmt->execute()) {
            $_SESSION['message'] = "Candidate deleted successfully";
        } else {
            $_SESSION['error'] = "Error deleting candidate: " . $conn->error;
        }
    }
    
    // Redirect to avoid resubmission
    redirect('../admin/manage_candidates.php');
}

include '../includes/header.php';

// Get candidate to edit if ID is provided
$candidate_to_edit = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    $id = clean($_GET['edit']);
    $stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows == 1) {
        $candidate_to_edit = $result->fetch_assoc();
    }
}

// Get all positions for dropdown
$positions = $conn->query("SELECT * FROM positions ORDER BY position_order");

// Get all candidates with position details (JOIN)
$candidates = $conn->query("SELECT c.*, p.position_name 
                          FROM candidates c 
                          JOIN positions p ON c.position_id = p.id 
                          ORDER BY p.position_order, c.lastname");
?>

<h2 style="text-align: center; margin-bottom: 30px;">Manage Candidates</h2>

<div style="margin-bottom: 30px;">
    <h3><?php echo ($candidate_to_edit) ? 'Edit Candidate' : 'Add New Candidate'; ?></h3>
    <form action="../admin/manage_candidates.php" method="POST">
        <?php if ($candidate_to_edit): ?>
            <input type="hidden" name="id" value="<?php echo $candidate_to_edit['id']; ?>">
        <?php endif; ?>
        
        <div class="form-group">
            <label for="position_id">Position:</label>
            <select name="position_id" id="position_id" class="form-control" required>
                <option value="">Select Position</option>
                <?php while ($position = $positions->fetch_assoc()): ?>
                    <option value="<?php echo $position['id']; ?>" <?php echo ($candidate_to_edit && $candidate_to_edit['position_id'] == $position['id']) ? 'selected' : ''; ?>>
                        <?php echo $position['position_name']; ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        
        <div class="form-group">
            <label for="firstname">First Name:</label>
            <input type="text" name="firstname" id="firstname" class="form-control" value="<?php echo ($candidate_to_edit) ? $candidate_to_edit['firstname'] : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="lastname">Last Name:</label>
            <input type="text" name="lastname" id="lastname" class="form-control" value="<?php echo ($candidate_to_edit) ? $candidate_to_edit['lastname'] : ''; ?>" required>
        </div>
        
        <div class="form-group">
            <label for="platform">Platform/Bio:</label>
            <textarea name="platform" id="platform" class="form-control" rows="5" required><?php echo ($candidate_to_edit) ? $candidate_to_edit['platform'] : ''; ?></textarea>
        </div>
        
        <div class="form-group">
            <?php if ($candidate_to_edit): ?>
                <button type="submit" name="edit" class="btn btn-success">Update Candidate</button>
                <a href="../admin/manage_candidates.php" class="btn btn-primary">Cancel</a>
            <?php else: ?>
                <button type="submit" name="add" class="btn btn-primary">Add Candidate</button>
            <?php endif; ?>
        </div>
    </form>
</div>

<h3>All Candidates</h3>
<?php if ($candidates->num_rows == 0): ?>
    <p>No candidates added yet.</p>
<?php else: ?>
    <table class="table">
        <thead>
            <tr>
                <th>Position</th>
                <th>Name</th>
                <th>Platform</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($candidate = $candidates->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $candidate['position_name']; ?></td>
                    <td><?php echo $candidate['firstname'] . ' ' . $candidate['lastname']; ?></td>
                    <td><?php echo substr($candidate['platform'], 0, 50) . '...'; ?></td>
                    <td>
                        <a href="../admin/manage_candidates.php?edit=<?php echo $candidate['id']; ?>" class="btn btn-primary">Edit</a>
                        
                        <form action="../admin/manage_candidates.php" method="POST" style="display: inline;">
                            <input type="hidden" name="id" value="<?php echo $candidate['id']; ?>">
                            <button type="submit" name="delete" class="btn btn-danger" onclick="return confirm('Are you sure you want to delete this candidate?');">Delete</button>
                        </form>
                    </td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>
<?php endif; ?>

<?php include '../includes/footer.php'; ?> 