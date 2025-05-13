# 🗳️ Online Voting System

A PHP and MySQL-based Online Voting System developed for a college course project.

## Features

- **Enhanced Security**
  - Session timeout detection
  - CSRF protection
  - Password hashing with bcrypt
  - Input validation and sanitization

- **User Authentication**
  - Voter Login
  - Admin Login
  - Session-based Security
  - Login/Logout tracking

- **Admin Panel**
  - Manage Positions (CRUD)
  - Manage Candidates (CRUD)
  - Manage Voters (CRUD)
  - View Election Results

- **Voting System**
  - Vote for candidates by position
  - Prevention of multiple votes for the same position
  - Results display with vote counts and percentages
  - Mobile-responsive interface

## Technical Requirements Met

- **CRUD Operations** - Full CRUD functionality for positions, candidates, and voters
- **Multiple Saving & SQL JOIN** - Implemented in the voting and results display systems
- **Session-based Authentication** - Secure login/logout system for both admin and voters
- **Responsive Design** - Works on mobile, tablet and desktop devices
- **Security Features** - CSRF protection, SQL injection prevention, XSS prevention

## Setup Instructions

1. **Database Setup**
   - Import the `database.sql` file into your MySQL server
   - This will create the necessary database and tables with sample data

2. **Server Configuration**
   - Place the project files in your web server directory (e.g., `htdocs` for XAMPP)
   - Make sure your web server (Apache) and MySQL are running

3. **Database Configuration**
   - Open `db/config.php`
   - Update the database connection settings if needed:
     ```php
     $host = 'localhost';
     $username = 'root';
     $password = '';
     $database = 'online_voting_system';
     ```

4. **Access the System**
   - Open your browser and navigate to `http://localhost/ovs` (or your installation path)
   - Use the default credentials:
     - **Admin:** username: `admin`, password: `admin123`
     - **Voter:** ID: `VOT001`, password: `voter123`

## Project Structure

```
online-voting-system/
│
├── index.php                # Login Page
├── dashboard.php            # Admin Dashboard
├── vote.php                 # Voting Page for Voters
├── results.php              # Voting Results Page
├── logout.php               # Session Logout
│
├── admin/
│   ├── manage_positions.php # Manage voting positions
│   ├── manage_candidates.php # Manage candidates
│   └── manage_voters.php    # Manage voter accounts
│
├── db/
│   └── config.php           # Database Connection
│
├── includes/
│   ├── functions.php        # Utility functions
│   ├── header.php           # Common header
│   └── footer.php           # Common footer
│
└── database.sql             # Database schema and sample data
```

## Security Features

- **SQL Injection Prevention**
  - Use of prepared statements for all database queries
  - Parameter binding for user input

- **XSS Prevention**
  - HTML escaping of all output with htmlspecialchars()
  - Input sanitization

- **CSRF Protection**
  - Token-based protection for all forms
  - Verification on form submission

- **Session Security**
  - Session timeout detection and handling
  - Login/logout tracking
  - Prevention of session fixation

## Author

- **Student:** Jcrist Vincent Orhen
- **Course:** Bachelor of Science in Information Technology - 1C
- **Instructor:** Christian Jade Nalagon