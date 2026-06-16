CREATE DATABASE donut_shop;
USE donut_shop;

CREATE TABLE branches (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
);

CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'sales') NOT NULL,
    branch_id INT NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id)
);

CREATE TABLE products (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    price DECIMAL(10,2) NOT NULL,
    setup_cost DECIMAL(10,2) DEFAULT 50000, -- Biaya setup produksi (Wagner)
    holding_cost DECIMAL(10,2) DEFAULT 500  -- Biaya simpan per unit per hari (Wagner)
);

CREATE TABLE sales (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT,
    product_id INT,
    quantity INT NOT NULL,
    total_price DECIMAL(10,2) NOT NULL,
    sale_date DATE NOT NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

CREATE TABLE forecasts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    branch_id INT,
    product_id INT,
    forecast_date DATE NOT NULL,
    predicted_qty INT NOT NULL,
    FOREIGN KEY (branch_id) REFERENCES branches(id),
    FOREIGN KEY (product_id) REFERENCES products(id)
);

-- Data Dummy
INSERT INTO branches (name) VALUES ('Cabang Pusat'), ('Cabang Utara'), ('Cabang Selatan');
-- Password: password123 (hash: $2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi)
INSERT INTO users (username, password, role, branch_id) VALUES 
('admin', '$2y$10$92IXUNpkjO0rO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', NULL),
('sales1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sales', 1),
('sales2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'sales', 2);

INSERT INTO products (name, price, setup_cost, holding_cost) VALUES 
('Donat Coklat', 5000, 100000, 200),
('Donat Keju', 6000, 120000, 250),
('Donat Gula', 4000, 80000, 150);