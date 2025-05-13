<?php 
// Use dirname to handle relative paths better
$functions_path = __DIR__ . '/functions.php';
require_once $functions_path;

// Generate CSRF token for this session
$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Online Voting System</title>
    <style>
        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            background-color: #f4f6f9;
            color: #333;
        }
        .container {
            width: 90%;
            max-width: 1200px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #3498db;
            color: #fff;
            padding: 15px 0;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .header h1 {
            text-align: center;
            font-size: 24px;
        }
        .navbar {
            background-color: #2980b9;
            padding: 10px 0;
        }
        .navbar ul {
            display: flex;
            justify-content: center;
            list-style: none;
            flex-wrap: wrap;
        }
        .navbar li {
            margin: 5px 15px;
        }
        .navbar a {
            color: #fff;
            text-decoration: none;
            font-weight: bold;
            padding: 8px 12px;
            border-radius: 4px;
            transition: background-color 0.3s;
            display: inline-block;
        }
        .navbar a:hover {
            background-color: #1c6ca1;
        }
        .content {
            margin-top: 20px;
            background: #fff;
            padding: 20px;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }
        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 4px;
        }
        .alert-danger {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .form-group {
            margin-bottom: 15px;
        }
        .form-control {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 16px;
        }
        .btn {
            display: inline-block;
            padding: 10px 15px;
            cursor: pointer;
            border: none;
            border-radius: 4px;
            font-size: 16px;
            text-align: center;
            text-decoration: none;
        }
        .btn-primary {
            background-color: #3498db;
            color: white;
        }
        .btn-success {
            background-color: #2ecc71;
            color: white;
        }
        .btn-danger {
            background-color: #e74c3c;
            color: white;
        }
        .table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .table th, .table td {
            padding: 12px 15px;
            border: 1px solid #ddd;
            text-align: left;
        }
        .table th {
            background-color: #f2f2f2;
        }
        .table tr:nth-child(even) {
            background-color: #f8f8f8;
        }
        
        /* Responsive adjustments */
        @media (max-width: 768px) {
            .navbar ul {
                flex-direction: column;
                align-items: center;
            }
            .navbar li {
                margin: 5px 0;
                width: 100%;
                text-align: center;
            }
            .navbar a {
                display: block;
                width: 100%;
            }
            .table {
                display: block;
                overflow-x: auto;
            }
        }
    </style>
</head>
<body>
    <div class="header">
        <h1>Online Voting System</h1>
    </div>
    
    <div class="navbar">
        <ul>
            <?php if(isAdminLoggedIn()): ?>
                <li><a href="<?php echo getBaseUrl(); ?>dashboard.php">Dashboard</a></li>
                <li><a href="<?php echo getBaseUrl(); ?>admin/manage_positions.php">Positions</a></li>
                <li><a href="<?php echo getBaseUrl(); ?>admin/manage_candidates.php">Candidates</a></li>
                <li><a href="<?php echo getBaseUrl(); ?>admin/manage_voters.php">Voters</a></li>
                <li><a href="<?php echo getBaseUrl(); ?>results.php">Results</a></li>
                <li><a href="<?php echo getBaseUrl(); ?>logout.php?token=<?php echo $csrf_token; ?>">Logout</a></li>
            <?php elseif(isVoterLoggedIn()): ?>
                <li><a href="<?php echo getBaseUrl(); ?>vote.php">Vote</a></li>
                <li><a href="<?php echo getBaseUrl(); ?>results.php">Results</a></li>
                <li><a href="<?php echo getBaseUrl(); ?>logout.php?token=<?php echo $csrf_token; ?>">Logout</a></li>
            <?php else: ?>
                <li><a href="<?php echo getBaseUrl(); ?>index.php">Login</a></li>
            <?php endif; ?>
        </ul>
    </div>
    
    <div class="container">
        <div class="content"><?php
            if(isset($_SESSION['message'])) {
                echo displaySuccess($_SESSION['message']);
                unset($_SESSION['message']);
            }
            if(isset($_SESSION['error'])) {
                echo displayError($_SESSION['error']);
                unset($_SESSION['error']);
            }
        ?> 