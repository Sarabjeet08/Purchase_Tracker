-- Create the database
CREATE DATABASE IF NOT EXISTS purchase_tracker;
USE purchase_tracker;

-- Create users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create purchases table
CREATE TABLE IF NOT EXISTS purchases (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    product_name VARCHAR(255) NOT NULL,
    quantity INT NOT NULL,
    unit_price DECIMAL(10,2) NOT NULL,
    total_amount DECIMAL(10,2) NOT NULL,
    purchase_date DATE NOT NULL,
    receipt_path VARCHAR(255),
    category VARCHAR(50),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


-- Create categories table for purchase categorization
CREATE TABLE IF NOT EXISTS categories (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(50) NOT NULL UNIQUE,
    description TEXT,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Insert default categories
INSERT INTO categories (name, description) VALUES 
('Food & Beverages', 'Groceries, restaurants, and drinks'),
('Transportation', 'Public transport, fuel, and vehicle maintenance'),
('Utilities', 'Electricity, water, internet, and phone bills'),
('Shopping', 'Clothing, electronics, and other retail purchases'),
('Entertainment', 'Movies, games, and leisure activities'),
('Healthcare', 'Medical expenses and medications'),
('Education', 'Books, courses, and educational materials'),
('Other', 'Miscellaneous expenses');

-- Create indexes for better performance
CREATE INDEX idx_purchases_user_id ON purchases(user_id);
CREATE INDEX idx_purchases_purchase_date ON purchases(purchase_date);
CREATE INDEX idx_purchases_category ON purchases(category);
CREATE INDEX idx_users_username ON users(username); 