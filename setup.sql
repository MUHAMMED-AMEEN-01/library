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