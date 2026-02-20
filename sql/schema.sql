CREATE DATABASE IF NOT EXISTS miniseries_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE miniseries_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(120) NOT NULL,
    email VARCHAR(190) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('admin', 'user') NOT NULL DEFAULT 'user',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS series (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(180) NOT NULL,
    synopsis TEXT NOT NULL,
    release_year INT NOT NULL,
    seasons INT NOT NULL DEFAULT 1,
    genre VARCHAR(100) NOT NULL,
    cover_url VARCHAR(500) NULL,
    created_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_series_user FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE CASCADE
);
