<?php
require_once 'includes/functions.php';

// Redirect if not logged in
if (!isAdminLoggedIn() && !isVoterLoggedIn()) {
    $_SESSION['error'] = "You must be logged in to view the results";
    redirect('index.php');
}

include 'includes/header.php';

// Get all positions
$positions_result = getPositions();
?>

<h2 style="text-align: center; margin-bottom: 30px;">Election Results</h2>

<?php if ($positions_result->num_rows == 0): ?>
    <p>No positions available for viewing results.</p>
<?php else: ?>
    <?php while ($position = $positions_result->fetch_assoc()): 
        // Get total votes for this position
        $total_votes_query = "SELECT COUNT(*) as total FROM votes WHERE position_id = ?";
        $stmt = $conn->prepare($total_votes_query);
        $stmt->bind_param("i", $position['id']);
        $stmt->execute();
        $total_votes_result = $stmt->get_result();
        $total_votes = $total_votes_result->fetch_assoc()['total'];
        
        // Get candidates with their vote counts for this position (Using SQL JOIN)
        $sql = "SELECT c.id, c.firstname, c.lastname, c.photo, c.platform, 
                COUNT(v.id) as vote_count 
                FROM candidates c 
                LEFT JOIN votes v ON v.candidate_id = c.id 
                WHERE c.position_id = ? 
                GROUP BY c.id 
                ORDER BY vote_count DESC, c.lastname, c.firstname";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $position['id']);
        $stmt->execute();
        $candidates_result = $stmt->get_result();
    ?>
        <div style="margin-bottom: 40px;">
            <h3 style="background-color: #f5f5f5; padding: 10px; border-radius: 5px;">
                <?php echo htmlspecialchars($position['position_name']); ?>
            </h3>
            
            <p><strong>Total votes cast for this position:</strong> <?php echo $total_votes; ?></p>
            
            <?php if ($candidates_result->num_rows == 0): ?>
                <p>No candidates available for this position.</p>
            <?php else: ?>
                <div style="display: flex; flex-wrap: wrap; margin-top: 20px;">
                    <?php 
                    $rank = 1;
                    $prev_votes = -1;
                    $real_rank = 0;
                    
                    while ($candidate = $candidates_result->fetch_assoc()): 
                        // Calculate percentage
                        $percentage = ($total_votes > 0) ? ($candidate['vote_count'] / $total_votes) * 100 : 0;
                        
                        // Determine rank (handle ties)
                        if ($candidate['vote_count'] != $prev_votes) {
                            $real_rank = $rank;
                        }
                        $prev_votes = $candidate['vote_count'];
                        
                        // Determine background color based on rank
                        $bg_color = "#ffffff";
                        if ($real_rank == 1) $bg_color = "#f9e090"; // Gold for 1st
                        else if ($real_rank == 2) $bg_color = "#e0e0e0"; // Silver for 2nd
                        else if ($real_rank == 3) $bg_color = "#cd7f32"; // Bronze for 3rd
                    ?>
                        <div style="flex: 0 0 100%; max-width: 100%; padding: 10px; box-sizing: border-box; 
                             @media (min-width: 768px) { flex: 0 0 33.333%; max-width: 33.333%; }">
                            <div style="border: 1px solid #ddd; padding: 15px; border-radius: 5px; background-color: <?php echo $bg_color; ?>;">
                                <div style="text-align: center; font-weight: bold; margin-bottom: 10px; font-size: 18px;">
                                    <?php echo $real_rank . '. ' . htmlspecialchars($candidate['firstname'] . ' ' . $candidate['lastname']); ?>
                                </div>
                                
                                <div style="margin-bottom: 15px; text-align: center;">
                                    <div style="font-size: 24px; font-weight: bold; color: #3498db;">
                                        <?php echo $candidate['vote_count']; ?>
                                    </div>
                                    <div>
                                        votes (<?php echo number_format($percentage, 2); ?>%)
                                    </div>
                                </div>
                                
                                <div style="height: 20px; background-color: #eee; border-radius: 10px; overflow: hidden; margin-bottom: 15px;">
                                    <div style="height: 100%; width: <?php echo $percentage; ?>%; background-color: #3498db;"></div>
                                </div>
                                
                                <div style="font-size: 14px;">
                                    <strong>Platform:</strong>
                                    <div style="max-height: 100px; overflow-y: auto; background-color: #f9f9f9; padding: 8px; border-radius: 3px; margin-top: 5px;">
                                        <?php echo nl2br(htmlspecialchars($candidate['platform'])); ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php 
                        $rank++;
                    endwhile; 
                    ?>
                </div>
            <?php endif; ?>
        </div>
    <?php endwhile; ?>
<?php endif; ?>

<?php include 'includes/footer.php'; ?> 