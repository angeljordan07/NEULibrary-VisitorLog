CREATE DATABASE neu_library CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE neu_library;

CREATE TABLE admins (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(100) NOT NULL UNIQUE,
  email VARCHAR(150) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE visitors (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rfid VARCHAR(100) NOT NULL,
  name VARCHAR(150),
  password VARCHAR(255) NOT NULL,
  type ENUM('Student','Faculty','Employee') DEFAULT 'Student',
  program VARCHAR(100),
  reason VARCHAR(100),
  timestamp DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE blocked_users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rfid VARCHAR(100) NOT NULL UNIQUE,
  reason VARCHAR(255),
  blocked_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE student_records (
  id INT AUTO_INCREMENT PRIMARY KEY,
  rfid VARCHAR(100) NOT NULL UNIQUE,
  name VARCHAR(150) NOT NULL,
  type ENUM('Student','Faculty','Employee') DEFAULT 'Student',
  program VARCHAR(100),
  year_level VARCHAR(50),
  created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Default admin account (password: Admin@123)
INSERT INTO admins (username, email, password) VALUES
('angelgrace', 'angelgrace@neu.admin.lib', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');