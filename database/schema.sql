-- ============================================
-- Expense Tracker - Database Schema
-- Import this in phpMyAdmin (SQL tab) or run via MySQL CLI
-- ============================================

-- 1. Create the database
CREATE DATABASE IF NOT EXISTS expense_tracker;
USE expense_tracker;

-- 2. Users table (who is using the app)
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,   -- we will store a HASHED password, never plain text
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- 3. Categories table (Food, Transport, Rent, etc.)
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

-- 4. Expenses table (the core data - links to users and categories)
CREATE TABLE IF NOT EXISTS expenses (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    category_id INT NOT NULL,
    amount DECIMAL(10,2) NOT NULL,
    description VARCHAR(255),
    expense_date DATE NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id)
);

-- 5. Seed some default categories so the app isn't empty on first run
INSERT INTO categories (name) VALUES
    ('Food'),
    ('Transport'),
    ('Rent'),
    ('Utilities'),
    ('Entertainment'),
    ('Health'),
    ('Other');
