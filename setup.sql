CREATE DATABASE IF NOT EXISTS online_store_db;
USE online_store_db;

CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(120) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    role ENUM('customer', 'admin') NOT NULL DEFAULT 'customer',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    description TEXT NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    stock INT NOT NULL DEFAULT 0,
    image_url VARCHAR(255) DEFAULT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_by INT DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_products_created_by FOREIGN KEY (created_by) REFERENCES users(id) ON DELETE SET NULL
);

CREATE TABLE IF NOT EXISTS site_content (
    id INT AUTO_INCREMENT PRIMARY KEY,
    content_key VARCHAR(80) NOT NULL UNIQUE,
    title VARCHAR(180) NOT NULL,
    body TEXT NOT NULL,
    updated_by INT DEFAULT NULL,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_content_updated_by FOREIGN KEY (updated_by) REFERENCES users(id) ON DELETE SET NULL
);

INSERT INTO users (name, email, password_hash, role)
VALUES ('System Admin', 'admin@novacart.test', '$2y$10$wKlp/6wfYKoL4F2QqWYLse56TYRXSmmWzPt9F3/rPcWrk8YATDxwu', 'admin')
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password_hash = VALUES(password_hash),
    role = VALUES(role);

INSERT INTO products (name, description, price, stock, image_url, created_by)
SELECT 'Wireless Headphones', 'Noise-cancelling headphones with 20-hour battery life.', 15999.00, 12, 'https://images.unsplash.com/photo-1505740420928-5e560c06d30e?w=700', id
FROM users WHERE email = 'admin@novacart.test'
LIMIT 1;

INSERT INTO products (name, description, price, stock, image_url, created_by)
SELECT 'Mechanical Keyboard', 'Compact RGB keyboard for productivity and gaming.', 12499.00, 8, 'https://images.unsplash.com/photo-1511467687858-23d96c32e4ae?w=700', id
FROM users WHERE email = 'admin@novacart.test'
LIMIT 1;

INSERT INTO site_content (content_key, title, body, updated_by)
SELECT 'home_hero', 'Shop smart. Move fast.', 'A demo online store with secure login, role-based access, and database-driven content.', id
FROM users WHERE email = 'admin@novacart.test'
LIMIT 1
ON DUPLICATE KEY UPDATE
    title = VALUES(title),
    body = VALUES(body),
    updated_by = VALUES(updated_by);
