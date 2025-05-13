-- Online Voting System Database

CREATE DATABASE IF NOT EXISTS online_voting_system;
USE online_voting_system;

-- Admin table
CREATE TABLE admins (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    name VARCHAR(100) NOT NULL,
    last_login DATETIME DEFAULT NULL,
    last_logout DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default admin (password: admin123)
-- Using a reliable hash for "admin123"
INSERT INTO admins (username, password, name) VALUES 
('admin', '$2y$10$wXrJDmFUDf9JlX1GrvNSUO.WExf.ZtUAgahSFJMFcWXfh1/GRXnDK', 'System Administrator');

-- Positions table
CREATE TABLE positions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    position_name VARCHAR(100) NOT NULL UNIQUE,
    max_votes INT NOT NULL DEFAULT 1,
    position_order INT NOT NULL DEFAULT 0,
    status TINYINT NOT NULL DEFAULT 1,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Voters table
CREATE TABLE voters (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voter_id VARCHAR(15) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    photo VARCHAR(150) NOT NULL DEFAULT 'profile.jpg',
    status TINYINT NOT NULL DEFAULT 1,
    last_login DATETIME DEFAULT NULL,
    last_logout DATETIME DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default voter (password: voter123)
-- Using a reliable hash for "voter123"
INSERT INTO voters (voter_id, password, firstname, lastname) VALUES 
('VOT001', '$2y$10$QUqeK7ROfLUGGPG7GAEwN.qc2AU/6XjsLm4P2XBM0SjkBv2aKzXcK', 'John', 'Doe');

-- Candidates table
CREATE TABLE candidates (
    id INT AUTO_INCREMENT PRIMARY KEY,
    position_id INT NOT NULL,
    firstname VARCHAR(50) NOT NULL,
    lastname VARCHAR(50) NOT NULL,
    photo VARCHAR(150) NOT NULL DEFAULT 'profile.jpg',
    platform TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (position_id) REFERENCES positions (id) ON DELETE CASCADE ON UPDATE CASCADE
);

-- Votes table
CREATE TABLE votes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    voter_id INT NOT NULL,
    position_id INT NOT NULL,
    candidate_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (voter_id) REFERENCES voters (id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (position_id) REFERENCES positions (id) ON DELETE CASCADE ON UPDATE CASCADE,
    FOREIGN KEY (candidate_id) REFERENCES candidates (id) ON DELETE CASCADE ON UPDATE CASCADE,
    UNIQUE KEY position_voter (position_id, voter_id)
);

-- Insert sample positions
INSERT INTO positions (position_name, max_votes, position_order) VALUES 
('President', 1, 1),
('Vice President', 1, 2),
('Secretary', 1, 3); 