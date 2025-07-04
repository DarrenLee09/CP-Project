<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $supplier_id = $_POST['supplier_id'];
    $new_supplier_id = $_POST['new_supplier_id'];
    $product_type = trim($_POST['product_type']);
    $description = trim($_POST['description']);
    $quantity = $_POST['quantity'];
    $price = $_POST['price'];
    $status = $_POST['status'];
    
    if (empty($product_id) || empty($supplier_id) || empty($new_supplier_id) || 
        empty($product_type) || !is_numeric($quantity) || !is_numeric($price)) {
        $_SESSION['error'] = "Please fill in all required fields with valid data.";
        header("Location: update.php");
        exit();
    }
    
    if ($quantity < 0 || $price < 0) {
        $_SESSION['error'] = "Quantity and price must be positive numbers.";
        header("Location: update.php");
        exit();
    }
    
    if (!is_numeric($product_id) || $product_id <= 0) {
        $_SESSION['error'] = "Product ID looks weird. Try again.";
        header("Location: update.php");
        exit();
    }
    
    $valid_statuses = ['A', 'B', 'C'];
    if (!in_array($status, $valid_statuses)) {
        $_SESSION['error'] = "Invalid status value.";
        header("Location: update.php");
        exit();
    }
    
    $check_supplier_sql = "SELECT SupplierID FROM Suppliers WHERE SupplierID = ?";
    $check_supplier_stmt = $conn->prepare($check_supplier_sql);
    $check_supplier_stmt->bind_param("i", $new_supplier_id);
    $check_supplier_stmt->execute();
    $check_supplier_result = $check_supplier_stmt->get_result();
    
    if ($check_supplier_result->num_rows === 0) {
        $_SESSION['error'] = "Selected supplier does not exist.";
        $check_supplier_stmt->close();
        header("Location: update.php");
        exit();
    }
    $check_supplier_stmt->close();
    
    $check_relationship_sql = "SELECT ProductID, SupplierID FROM Product WHERE ProductID = ? AND SupplierID = ?";
    $check_relationship_stmt = $conn->prepare($check_relationship_sql);
    $check_relationship_stmt->bind_param("ii", $product_id, $supplier_id);
    $check_relationship_stmt->execute();
    $check_relationship_result = $check_relationship_stmt->get_result();
    
    if ($check_relationship_result->num_rows === 0) {
        $_SESSION['error'] = "Product-supplier relationship not found.";
        $check_relationship_stmt->close();
        header("Location: update.php");
        exit();
    }
    $check_relationship_stmt->close();
    
    if ($supplier_id != $new_supplier_id) {
        $check_new_relationship_sql = "SELECT ProductID, SupplierID FROM Product WHERE ProductID = ? AND SupplierID = ?";
        $check_new_relationship_stmt = $conn->prepare($check_new_relationship_sql);
        $check_new_relationship_stmt->bind_param("ii", $product_id, $new_supplier_id);
        $check_new_relationship_stmt->execute();
        $check_new_relationship_result = $check_new_relationship_stmt->get_result();
        
        if ($check_new_relationship_result->num_rows > 0) {
            $_SESSION['error'] = "Product already has a relationship with this supplier.";
            $check_new_relationship_stmt->close();
            header("Location: update.php");
            exit();
        }
        $check_new_relationship_stmt->close();
    }
    
    if ($supplier_id == $new_supplier_id) {
        $sql = "UPDATE Product SET ProductType = ?, Description = ?, Quantity = ?, Price = ?, Status = ? 
                WHERE ProductID = ? AND SupplierID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ssddsii", $product_type, $description, $quantity, $price, $status, $product_id, $supplier_id);
    } else {
        $delete_sql = "DELETE FROM Product WHERE ProductID = ? AND SupplierID = ?";
        $delete_stmt = $conn->prepare($delete_sql);
        $delete_stmt->bind_param("ii", $product_id, $supplier_id);
        
        if (!$delete_stmt->execute()) {
            $_SESSION['error'] = "Error updating product: " . $conn->error;
            $delete_stmt->close();
            header("Location: update.php");
            exit();
        }
        $delete_stmt->close();
        
        $sql = "INSERT INTO Product (ProductID, ProductType, Description, Quantity, Price, Status, SupplierID) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("issddsi", $product_id, $product_type, $description, $quantity, $price, $status, $new_supplier_id);
    }
    
    if ($stmt->execute()) {
        $_SESSION['success'] = "Product updated successfully! Product ID: $product_id";
        
        header("Location: inventory.php");
        exit();
    } else {
        $_SESSION['error'] = "Error updating product: " . $conn->error;
        header("Location: update.php");
        exit();
    }
    
    $stmt->close();
    
} else {
    header("Location: update.php");
    exit();
}
?> 