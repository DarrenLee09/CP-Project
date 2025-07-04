<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $product_type = trim($_POST['product_type']);
    $description = trim($_POST['description']);
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    $supplier_id = $_POST['supplier_id'];
    
    if (empty($product_id) || empty($product_type) || empty($supplier_id) || !is_numeric($quantity) || !is_numeric($price)) {
        $_SESSION['error'] = "Please fill in all required fields with valid data.";
        header("Location: add_product.php");
        exit();
    }
    
    if ($quantity < 0 || $price < 0) {
        $_SESSION['error'] = "Quantity and price must be positive numbers.";
        header("Location: add_product.php");
        exit();
    }
    
    if (!is_numeric($product_id) || $product_id <= 0) {
        $_SESSION['error'] = "Invalid Product ID.";
        header("Location: add_product.php");
        exit();
    }
    
    $check_product_sql = "SELECT ProductID FROM Product WHERE ProductID = ?";
    $check_product_stmt = $conn->prepare($check_product_sql);
    $check_product_stmt->bind_param("i", $product_id);
    $check_product_stmt->execute();
    $check_product_result = $check_product_stmt->get_result();
    
    if ($check_product_result->num_rows > 0) {
        $_SESSION['error'] = "Product ID $product_id already exists. Please use a different ID.";
        $check_product_stmt->close();
        header("Location: add_product.php");
        exit();
    }
    $check_product_stmt->close();
    
    $check_supplier_sql = "SELECT SupplierID FROM Suppliers WHERE SupplierID = ?";
    $check_supplier_stmt = $conn->prepare($check_supplier_sql);
    $check_supplier_stmt->bind_param("i", $supplier_id);
    $check_supplier_stmt->execute();
    $check_supplier_result = $check_supplier_stmt->get_result();
    
    if ($check_supplier_result->num_rows === 0) {
        $_SESSION['error'] = "Selected supplier does not exist.";
        $check_supplier_stmt->close();
        header("Location: add_product.php");
        exit();
    }
    $check_supplier_stmt->close();
    
    $valid_statuses = ['A', 'B', 'C'];
    if (!in_array($status, $valid_statuses)) {
        $_SESSION['error'] = "Invalid status value.";
        header("Location: add_product.php");
        exit();
    }
    
    $sql = "INSERT INTO Product (ProductID, ProductType, Description, Quantity, Price, Status, SupplierID) 
            VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("issddsi", $product_id, $product_type, $description, $quantity, $price, $status, $supplier_id);
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Product added successfully! Product ID: $product_id";
        
        header("Location: inventory.php");
        exit();
    } else {
        $_SESSION['error'] = "Error adding product: " . $conn->error;
        header("Location: add_product.php");
        exit();
    }
    
    $stmt->close();
    
} else {
    header("Location: add_product.php");
    exit();
}
?> 