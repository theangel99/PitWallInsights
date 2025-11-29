-- PitWall F1 Website Database Schema
-- MySQL/MariaDB Database Setup

-- Create database (if needed)
-- CREATE DATABASE pitwall_f1 CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
-- USE pitwall_f1;

-- Users table for admin authentication
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor') DEFAULT 'editor',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_username (username)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Articles table for news and content
CREATE TABLE IF NOT EXISTS articles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE NOT NULL,
    excerpt TEXT,
    content LONGTEXT NOT NULL,
    category ENUM('novice', 'analize', 'intervjuji', 'tehnologija', 'ekipe') DEFAULT 'novice',
    featured BOOLEAN DEFAULT FALSE,
    image_url VARCHAR(500),
    author_id INT,
    views INT DEFAULT 0,
    read_time INT DEFAULT 5,
    published BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    published_at TIMESTAMP NULL,
    FOREIGN KEY (author_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_slug (slug),
    INDEX idx_category (category),
    INDEX idx_published (published),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Constructors (Teams) table
CREATE TABLE IF NOT EXISTS constructors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    short_name VARCHAR(50),
    color VARCHAR(7),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY unique_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Drivers table
CREATE TABLE IF NOT EXISTS drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    first_name VARCHAR(100) NOT NULL,
    last_name VARCHAR(100) NOT NULL,
    full_name VARCHAR(200) NOT NULL,
    nationality VARCHAR(3),
    constructor_id INT,
    number INT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (constructor_id) REFERENCES constructors(id) ON DELETE SET NULL,
    INDEX idx_constructor (constructor_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Races table
CREATE TABLE IF NOT EXISTS races (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season INT NOT NULL,
    round INT NOT NULL,
    name VARCHAR(200) NOT NULL,
    circuit_name VARCHAR(200) NOT NULL,
    location VARCHAR(200),
    country VARCHAR(100),
    date DATE NOT NULL,
    time TIME,
    laps INT,
    distance DECIMAL(6,3),
    completed BOOLEAN DEFAULT FALSE,
    pole_position_driver_id INT,
    fastest_lap_driver_id INT,
    fastest_lap_time VARCHAR(20),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (pole_position_driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
    FOREIGN KEY (fastest_lap_driver_id) REFERENCES drivers(id) ON DELETE SET NULL,
    INDEX idx_season_round (season, round),
    INDEX idx_date (date)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Race results table
CREATE TABLE IF NOT EXISTS race_results (
    id INT AUTO_INCREMENT PRIMARY KEY,
    race_id INT NOT NULL,
    driver_id INT NOT NULL,
    constructor_id INT NOT NULL,
    position INT,
    grid_position INT,
    points DECIMAL(4,1) DEFAULT 0,
    laps_completed INT,
    time_or_retired VARCHAR(100),
    status ENUM('finished', 'retired', 'dnf', 'dns', 'disqualified') DEFAULT 'finished',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (race_id) REFERENCES races(id) ON DELETE CASCADE,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    FOREIGN KEY (constructor_id) REFERENCES constructors(id) ON DELETE CASCADE,
    UNIQUE KEY unique_race_driver (race_id, driver_id),
    INDEX idx_race (race_id),
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Driver standings table
CREATE TABLE IF NOT EXISTS standings_drivers (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season INT NOT NULL,
    driver_id INT NOT NULL,
    position INT NOT NULL,
    points DECIMAL(6,1) DEFAULT 0,
    wins INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (driver_id) REFERENCES drivers(id) ON DELETE CASCADE,
    UNIQUE KEY unique_season_driver (season, driver_id),
    INDEX idx_season (season),
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Constructor standings table
CREATE TABLE IF NOT EXISTS standings_constructors (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season INT NOT NULL,
    constructor_id INT NOT NULL,
    position INT NOT NULL,
    points DECIMAL(6,1) DEFAULT 0,
    wins INT DEFAULT 0,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (constructor_id) REFERENCES constructors(id) ON DELETE CASCADE,
    UNIQUE KEY unique_season_constructor (season, constructor_id),
    INDEX idx_season (season),
    INDEX idx_position (position)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Race highlights table
CREATE TABLE IF NOT EXISTS race_highlights (
    id INT AUTO_INCREMENT PRIMARY KEY,
    race_id INT NOT NULL,
    highlight TEXT NOT NULL,
    sort_order INT DEFAULT 0,
    FOREIGN KEY (race_id) REFERENCES races(id) ON DELETE CASCADE,
    INDEX idx_race (race_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Insert default admin user (password: admin123 - CHANGE THIS!)
-- Password hash for 'admin123'
INSERT INTO users (username, email, password, role) VALUES
('admin', 'admin@pitwall.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin')
ON DUPLICATE KEY UPDATE username=username;

-- Insert sample constructors (2024 season)
INSERT INTO constructors (name, short_name, color) VALUES
('Red Bull Racing', 'Red Bull', '#3671C6'),
('Mercedes', 'Mercedes', '#27F4D2'),
('Ferrari', 'Ferrari', '#E8002D'),
('McLaren', 'McLaren', '#FF8000'),
('Aston Martin', 'Aston Martin', '#229971'),
('Alpine', 'Alpine', '#FF87BC'),
('Williams', 'Williams', '#64C4FF'),
('RB', 'RB', '#6692FF'),
('Haas', 'Haas', '#B6BABD'),
('Sauber', 'Sauber', '#52E252')
ON DUPLICATE KEY UPDATE name=VALUES(name);
