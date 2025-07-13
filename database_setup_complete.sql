DROP DATABASE IF EXISTS inventory_management;
CREATE DATABASE inventory_management;
USE inventory_management;

-- Users table for authentication
CREATE TABLE IF NOT EXISTS Users (
    UserID INT AUTO_INCREMENT PRIMARY KEY,
    Username VARCHAR(50) UNIQUE NOT NULL,
    Password VARCHAR(255) NOT NULL,
    Email VARCHAR(100),
    CreatedAt TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Suppliers table
CREATE TABLE IF NOT EXISTS Suppliers (
    SupplierID INT PRIMARY KEY,
    SupplierName VARCHAR(100) NOT NULL,
    Address VARCHAR(200),
    Phone VARCHAR(20),
    Email VARCHAR(100)
);

-- Product table (main inventory table)
CREATE TABLE IF NOT EXISTS Product (
    ProductID INT,
    SupplierID INT,
    ProductType VARCHAR(100) NOT NULL,
    Description TEXT,
    Price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    Quantity INT NOT NULL DEFAULT 0,
    Status ENUM('A', 'B', 'C') DEFAULT 'A',
    PRIMARY KEY (ProductID, SupplierID),
    FOREIGN KEY (SupplierID) REFERENCES Suppliers(SupplierID) ON DELETE CASCADE
);

-- Inventory table (as per your original schema)
CREATE TABLE IF NOT EXISTS Inventory (
    ProductID INT PRIMARY KEY,
    ProductType VARCHAR(100) NOT NULL,
    Quantity INT NOT NULL DEFAULT 0,
    Price DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    Status ENUM('A', 'B', 'C') DEFAULT 'A',
    Supplier VARCHAR(100),
    FOREIGN KEY (ProductID) REFERENCES Product(ProductID) ON DELETE CASCADE
);

-- Insert default users
INSERT INTO Users (Username, Password, Email) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@inventory.com'),
('manager', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'manager@inventory.com'),
('user1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user1@inventory.com'),
('user2', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user2@inventory.com'),
('user3', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'user3@inventory.com');

-- Create indexes for better performance
CREATE INDEX idx_product_id ON Product(ProductID);
CREATE INDEX idx_supplier_id ON Suppliers(SupplierID);
CREATE INDEX idx_product_status ON Product(Status);
CREATE INDEX idx_inventory_product_id ON Inventory(ProductID);

SELECT 'Database Setup Complete!' as Status;
SELECT COUNT(*) as 'Total Users' FROM Users;
SELECT 'Tables Created: Users, Suppliers, Product, Inventory' as Tables; 