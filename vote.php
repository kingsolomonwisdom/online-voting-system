<?php
require_once 'includes/functions.php';

// Redirect if not logged in as voter
if (!isVoterLoggedIn()) {
    $_SESSION['error'] = "You must be logged in as a voter to access the voting page";
    redirect('index.php');
}

// Process vote submission
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_vote'])) {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !verifyCSRFToken($_POST['csrf_token'])) {
        $_SESSION['error'] = "Invalid form submission, please try again";
        redirect('vote.php');
    }
    
    $positions = isset($_POST['positions']) ? $_POST['positions'] : [];
    $candidates = isset($_POST['candidates']) ? $_POST['candidates'] : [];
    $voter_id = $_SESSION['voter'];
    
    // Validate that voter selected a candidate for at least one position
    if (empty($candidates)) {
        $_SESSION['error'] = "Please select at least one candidate to vote for";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert votes for each selected position/candidate
            foreach ($candidates as $position_id => $candidate_id) {
                // Check if voter already voted for this position
                if (!hasVoted($voter_id, $position_id)) {
                    // Verify the candidate belongs to this position
                    $check_stmt = $conn->prepare("SELECT * FROM candidates WHERE id = ? AND position_id = ?");
                    $check_stmt->bind_param("ii", $candidate_id, $position_id);
                    $check_stmt->execute();
                    $check_result = $check_stmt->get_result();
                    
                    if ($check_result->num_rows > 0) {
                        $stmt = $conn->prepare("INSERT INTO votes (voter_id, position_id, candidate_id) VALUES (?, ?, ?)");
                        $stmt->bind_param("iii", $voter_id, $position_id, $candidate_id);
                        $stmt->execute();
                    } else {
                        throw new Exception("Invalid candidate selected");
                    }
                }
            }
            
            // Commit the transaction
            $conn->commit();
            $_SESSION['message'] = "Your votes have been recorded successfully!";
            
            // Check if all positions have been voted for
            if (hasCompletedVoting($voter_id)) {
                $_SESSION['message'] .= " You have completed voting for all positions.";
            }
            
            // Redirect to avoid resubmission
            redirect('results.php');
        } catch (Exception $e) {
            // An error occurred, rollback the transaction
            $conn->rollback();
            $_SESSION['error'] = "Error recording your votes: " . $e->getMessage();
        }
    }
}

include 'includes/header.php';

// Get all positions with their candidates
$positions_result = getPositions();
?>

<h2 style="text-align: center; margin-bottom: 30px;">Cast Your Vote</h2>

<?php if ($positions_result->num_rows == 0): ?>
    <p>No positions available for voting at this time.</p>
<?php else: ?>
    <form action="vote.php" method="POST">
        <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
        
        <?php while ($position = $positions_result->fetch_assoc()): 
            // Check if voter has already voted for this position
            $has_voted = hasVoted($_SESSION['voter'], $position['id']);
            
            // Get candidates for this position
            $candidates_result = getCandidatesByPosition($position['id']);
        ?>
            <div style="margin-bottom: 30px; padding: 20px; border: 1px solid #ddd; border-radius: 5px;">
                <h3><?php echo $position['position_name']; ?></h3>
                
                <?php if ($has_voted): ?>
                    <p style="color: #27ae60;"><strong>You have already voted for this position.</strong></p>
                <?php elseif ($candidates_result->num_rows == 0): ?>
                    <p>No candidates available for this position.</p>
                <?php else: ?>
                    <input type="hidden" name="positions[]" value="<?php echo $position['id']; ?>">
                    <p>Select up to <?php echo $position['max_votes']; ?> candidate(s):</p>
                    
                    <div style="display: flex; flex-wrap: wrap;">
                        <?php while ($candidate = $candidates_result->fetch_assoc()): ?>
                            <div style="flex: 0 0 33.333%; max-width: 33.333%; padding: 10px; box-sizing: border-box;">
                                <div style="border: 1px solid #eee; padding: 15px; border-radius: 5px; text-align: center;">
                                    <div style="font-weight: bold; margin-bottom: 10px;">
                                        <?php echo htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']); ?>
                                    </div>
                                    
                                    <div style="margin-bottom: 10px;">
                                        <div style="height: 80px; overflow-y: auto; text-align: left; padding: 5px; background-color: #f9f9f9; border-radius: 3px;">
                                            <?php echo nl2br(htmlspecialchars($candidate['platform'])); ?>
                                        </div>
                                    </div>
                                    
                                    <div>
                                        <label>
                                            <input type="radio" name="candidates[<?php echo $position['id']; ?>]" value="<?php echo $candidate['id']; ?>" required>
                                            Vote for this candidate
                                        </label>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php endif; ?>
            </div>
        <?php endwhile; ?>
        
        <?php
        // Check if there is at least one position that hasn't been voted on
        $can_vote = false;
        $positions_result->data_seek(0); // Reset the result set pointer
        while ($position = $positions_result->fetch_assoc()) {
            if (!hasVoted($_SESSION['voter'], $position['id']) && getCandidatesByPosition($position['id'])->num_rows > 0) {
                $can_vote = true;
                break;
            }
        }
        ?>
        
        <?php if ($can_vote): ?>
            <div style="text-align: center; margin-top: 20px;">
                <button type="submit" name="submit_vote" class="btn btn-success">Submit Votes</button>
            </div>
        <?php else: ?>
            <div style="text-align: center; margin-top: 20px;">
                <p>You have already voted for all available positions.</p>
                <a href="results.php" class="btn btn-primary">View Results</a>
            </div>
        <?php endif; ?>
    </form>
<?php endif; ?>

<?php include 'includes/footer.php'; ?> 