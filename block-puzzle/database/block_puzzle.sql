CREATE DATABASE IF NOT EXISTS block_puzzle
  CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE block_puzzle;

CREATE TABLE IF NOT EXISTS scores (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(50) NOT NULL,
  level INT NOT NULL,
  score INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Bảng chứa hint / crypto config cho từng stage
CREATE TABLE IF NOT EXISTS puzzle_hints (
  id        INT AUTO_INCREMENT PRIMARY KEY,
  stage     INT NOT NULL,
  hint_type VARCHAR(50) NOT NULL,
  content   TEXT NOT NULL,
  updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
                         ON UPDATE CURRENT_TIMESTAMP,
  INDEX idx_stage_type (stage, hint_type)
);
