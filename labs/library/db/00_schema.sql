-- Create tables
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  name VARCHAR(100) NOT NULL,
  password_hash VARCHAR(255) NOT NULL,
  bio TEXT,
  avatar_path VARCHAR(255),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE IF NOT EXISTS books (
  id INT AUTO_INCREMENT PRIMARY KEY,
  owner_name VARCHAR(100) NOT NULL,
  title VARCHAR(255) NOT NULL,
  author VARCHAR(255) NOT NULL,
  cover_path VARCHAR(255),
  description TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Sample users
INSERT IGNORE INTO users (username, name, password_hash, bio, avatar_path)
VALUES
  ('admin', 'Administrator', 'b7463760284fd06773ac2a48e29b0acf', 'System administrator. Loves security challenges and database puzzles.', NULL),
  ('user1', 'User One', '32250170a0dca92d53ec9624f336ca24', 'A passionate PHP developer. Always looking for new vulnerabilities.', NULL),
  ('user2', 'User Two', '32250170a0dca92d53ec9624f336ca24', 'DevOps enthusiast. Enjoys working with containers and automation.', NULL);

-- Sample books
INSERT IGNORE INTO books (owner_name, title, author, cover_path, description)
VALUES
  ('Administrator', 'SQL Injection', 'Evil Author', NULL, 'A classic book about exploiting SQL vulnerabilities. Includes hands-on labs.'),
  ('User One', 'PHP Security', 'Ivan', NULL, 'A practical guide to secure PHP development. Covers common mistakes and best practices.'),
  ('User Two', 'Docker for Devs', 'Maria', NULL, 'Learn how to use Docker for development and deployment. Real-world examples included.'),
  ('User One', 'Second Order', 'Ivan', NULL, 'Demonstrates second order SQL injection. Try to find the hidden vulnerability!');