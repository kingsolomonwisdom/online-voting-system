<?php
require_once 'includes/functions.php';

// Redirect if not logged in as admin
if (!isAdminLoggedIn()) {
    $_SESSION['error'] = "You must be logged in as admin to access the dashboard";
    redirect('index.php');
}

include 'includes/header.php';

// Get statistics for dashboard
$voters_count = $conn->query("SELECT COUNT(*) as total FROM voters")->fetch_assoc()['total'];
$active_voters = $conn->query("SELECT COUNT(*) as total FROM voters WHERE status = 1")->fetch_assoc()['total'];
$positions_count = $conn->query("SELECT COUNT(*) as total FROM positions")->fetch_assoc()['total'];
$candidates_count = $conn->query("SELECT COUNT(*) as total FROM candidates")->fetch_assoc()['total'];
$votes_count = $conn->query("SELECT COUNT(*) as total FROM votes")->fetch_assoc()['total'];

// Get total unique voters who voted
$voted_count = $conn->query("SELECT COUNT(DISTINCT voter_id) as total FROM votes")->fetch_assoc()['total'];

// Calculate voting percentage
$voting_percentage = ($active_voters > 0) ? ($voted_count / $active_voters) * 100 : 0;
?>

<h2 style="text-align: center; margin-bottom: 30px;">Admin Dashboard</h2>

<div style="display: flex; flex-wrap: wrap; justify-content: space-between; margin-bottom: 40px;">
    <div style="flex: 1; min-width: 200px; margin: 10px; background-color: #3498db; color: white; padding: 20px; border-radius: 5px; text-align: center;">
        <h3><?php echo $voters_count; ?></h3>
        <p>Total Voters</p>
        <div style="font-size: 14px; margin-top: 5px;">
            (<?php echo $active_voters; ?> active)
        </div>
    </div>
    
    <div style="flex: 1; min-width: 200px; margin: 10px; background-color: #2ecc71; color: white; padding: 20px; border-radius: 5px; text-align: center;">
        <h3><?php echo $positions_count; ?></h3>
        <p>Positions</p>
    </div>
    
    <div style="flex: 1; min-width: 200px; margin: 10px; background-color: #e74c3c; color: white; padding: 20px; border-radius: 5px; text-align: center;">
        <h3><?php echo $candidates_count; ?></h3>
        <p>Candidates</p>
    </div>
    
    <div style="flex: 1; min-width: 200px; margin: 10px; background-color: #9b59b6; color: white; padding: 20px; border-radius: 5px; text-align: center;">
        <h3><?php echo $votes_count; ?></h3>
        <p>Total Votes Cast</p>
    </div>
</div>

<div style="display: flex; flex-wrap: wrap; justify-content: center; margin-bottom: 40px;">
    <div style="flex: 0 0 90%; max-width: 500px; margin: 10px; background-color: #f39c12; color: white; padding: 20px; border-radius: 5px; text-align: center;">
        <h3><?php echo number_format($voting_percentage, 2); ?>%</h3>
        <p>Voter Participation Rate</p>
        <div style="margin-top: 10px; background-color: rgba(255,255,255,0.2); border-radius: 10px; height: 20px;">
            <div style="background-color: white; width: <?php echo min(100, $voting_percentage); ?>%; height: 100%; border-radius: 10px;"></div>
        </div>
        <div style="font-size: 14px; margin-top: 5px;">
            <?php echo $voted_count; ?> out of <?php echo $active_voters; ?> active voters have voted
        </div>
    </div>
</div>

<div style="margin-top: 20px;">
    <h3>Quick Links</h3>
    <div style="display: flex; flex-wrap: wrap; margin-top: 15px;">
        <a href="admin/manage_positions.php" style="text-decoration: none; margin: 10px; flex: 1 0 200px;">
            <div style="background-color: #f1f1f1; padding: 20px; border-radius: 5px; text-align: center; min-width: 200px;">
                <h4 style="color: #333;">Manage Positions</h4>
                <p>Add, edit or delete voting positions</p>
            </div>
        </a>
        
        <a href="admin/manage_candidates.php" style="text-decoration: none; margin: 10px; flex: 1 0 200px;">
            <div style="background-color: #f1f1f1; padding: 20px; border-radius: 5px; text-align: center; min-width: 200px;">
                <h4 style="color: #333;">Manage Candidates</h4>
                <p>Add, edit or delete candidates</p>
            </div>
        </a>
        
        <a href="admin/manage_voters.php" style="text-decoration: none; margin: 10px; flex: 1 0 200px;">
            <div style="background-color: #f1f1f1; padding: 20px; border-radius: 5px; text-align: center; min-width: 200px;">
                <h4 style="color: #333;">Manage Voters</h4>
                <p>Add, edit or delete voter accounts</p>
            </div>
        </a>
        
        <a href="results.php" style="text-decoration: none; margin: 10px; flex: 1 0 200px;">
            <div style="background-color: #f1f1f1; padding: 20px; border-radius: 5px; text-align: center; min-width: 200px;">
                <h4 style="color: #333;">View Results</h4>
                <p>See current voting results</p>
            </div>
        </a>
    </div>
</div>

<?php include 'includes/footer.php'; ?> 