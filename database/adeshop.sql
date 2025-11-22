-- ADESHOP schema for MySQL
CREATE DATABASE IF NOT EXISTS adeshop DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE adeshop;

-- users (for admin and customers)
CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  nombres VARCHAR(100) NOT NULL,
  apellidos VARCHAR(100) NOT NULL,
  email VARCHAR(255) NOT NULL UNIQUE,
  cedula VARCHAR(20) NOT NULL UNIQUE,
  username VARCHAR(100) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  is_admin TINYINT(1) DEFAULT 0,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- categories
CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(100) NOT NULL
);

-- products
CREATE TABLE IF NOT EXISTS products (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(255) NOT NULL,
  description TEXT,
  price DECIMAL(10,2) NOT NULL DEFAULT 0,
  stock INT NOT NULL DEFAULT 0,
  image VARCHAR(255) NULL,
  category_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL
);

-- orders
CREATE TABLE IF NOT EXISTS orders (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  status VARCHAR(50) DEFAULT 'pending',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- order items
CREATE TABLE IF NOT EXISTS order_items (
  id INT AUTO_INCREMENT PRIMARY KEY,
  order_id INT NOT NULL,
  product_id INT NOT NULL,
  quantity INT NOT NULL DEFAULT 1,
  price DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
  FOREIGN KEY (product_id) REFERENCES products(id) ON DELETE CASCADE
);

-- sample data --
-- Usuario administrador por defecto: admin@admin.com / admin123
INSERT INTO users (nombres, apellidos, email, cedula, username, password, is_admin) VALUES
('Administrador', 'Sistema', 'admin@admin.com', '9999999999', 'admin', '$2y$10$JlDedjSIYNVdenuk1SS6neW02H7EZNOKRtBw0nJ9JBR04RRj5iIdq', 1) 
ON DUPLICATE KEY UPDATE 
    password = '$2y$10$JlDedjSIYNVdenuk1SS6neW02H7EZNOKRtBw0nJ9JBR04RRj5iIdq',
    is_admin = 1;

INSERT INTO categories (name) VALUES
('T-Shirts'),('Hoodies'),('Jeans')
ON DUPLICATE KEY UPDATE name=name;

INSERT INTO products (name, description, price, stock, image, category_id) VALUES
('Basic White Tee','Soft cotton t-shirt','9.99', 15, 'assets/images/white-tee.jpg',1),
('Logo Hoodie','Pullover hoodie with logo','29.99', 8, 'assets/images/hoodie.jpg',2),
('Slim Jeans','Blue slim fit jeans','39.99', 3, 'assets/images/jeans.jpg',3)
ON DUPLICATE KEY UPDATE name=VALUES(name);
