CREATE DATABASE IF NOT EXISTS `rook_library`;
USE `rook_library`;

-- 1. BOOKS TABLE
CREATE TABLE IF NOT EXISTS `books` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `author` varchar(255) NOT NULL,
  `call_number` varchar(50) NOT NULL UNIQUE,
  `shelf_number` varchar(50) DEFAULT NULL,
  `genre` varchar(100) DEFAULT 'General',
  `language` varchar(50) DEFAULT 'English',
  `status` enum('available','checked-out','lost') DEFAULT 'available',
  `cover_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 2. PATRONS (STUDENTS) TABLE
CREATE TABLE IF NOT EXISTS `patrons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `card_number` varchar(50) NOT NULL UNIQUE, -- This is the Admission Number
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 3. CIRCULATION TABLE
CREATE TABLE IF NOT EXISTS `checkouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `book_id` int(11) NOT NULL,
  `patron_id` int(11) NOT NULL,
  `checkout_date` datetime DEFAULT CURRENT_TIMESTAMP,
  `due_date` datetime DEFAULT NULL,
  `return_date` datetime DEFAULT NULL,
  `status` enum('active','returned') DEFAULT 'active',
  PRIMARY KEY (`id`),
  FOREIGN KEY (`book_id`) REFERENCES `books`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`patron_id`) REFERENCES `patrons`(`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


-- Run this in PHPMyAdmin to add the admin table
CREATE TABLE IF NOT EXISTS `admins` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL, -- We will store hashed passwords
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert a default admin user (Username: admin, Password: password123)
-- Note: In a real app, use password_hash(). For this example, we will use simple text for learning.
INSERT INTO `admins` (`username`, `password`) VALUES ('admin', 'password123');

-- 1. Add Category to Patrons (Students/Staff)
-- This lets us know if they are UG, PG, or Staff
ALTER TABLE patrons ADD COLUMN category ENUM('UG', 'PG', 'Staff') DEFAULT 'UG';

-- 2. Add Fine Amount to Checkouts
-- This stores how much fine was paid when a book is returned
ALTER TABLE checkouts ADD COLUMN fine_amount DECIMAL(10,2) DEFAULT 0.00;