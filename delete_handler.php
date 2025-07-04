<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $product_id = $_POST['product_id'];
    $supplier_id = isset($_POST['supplier_id']) ? $_POST['supplier_id'] : null;
    $delete_all = isset($_POST['delete_all']) ? $_POST['delete_all'] : false;
    
    if (empty($product_id)) {
        $_SESSION['error'] = "Invalid request data.";
        header("Location: delete.php");
        exit();
    }
    
    if (!is_numeric($product_id) || $product_id <= 0) {
        $_SESSION['error'] = "Invalid Product ID.";
        header("Location: delete.php");
        exit();
    }
    
    $check_product_sql = "SELECT ProductID FROM Product WHERE ProductID = ?";
    $check_product_stmt = $conn->prepare($check_product_sql);
    $check_product_stmt->bind_param("i", $product_id);
    $check_product_stmt->execute();
    $check_product_result = $check_product_stmt->get_result();
    
    if ($check_product_result->num_rows === 0) {
        $_SESSION['error'] = "Product not found.";
        $check_product_stmt->close();
        header("Location: delete.php");
        exit();
    }
    $check_product_stmt->close();
    
    if ($delete_all) {
        $sql = "DELETE FROM Product WHERE ProductID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $product_id);
        
        if ($stmt->execute()) {
            $deleted_count = $stmt->affected_rows;
            if ($deleted_count > 0) {
                $_SESSION['success'] = "Successfully deleted all {$deleted_count} supplier relationship(s) for Product ID {$product_id}.";
            } else {
                $_SESSION['error'] = "No relationships found to delete.";
            }
        } else {
            $_SESSION['error'] = "Error deleting product relationships: " . $conn->error;
        }
        
        $stmt->close();
        
    } else {
        if (empty($supplier_id)) {
            $_SESSION['error'] = "Supplier ID is required for deleting specific relationship.";
            header("Location: delete.php");
            exit();
        }
        
        if (!is_numeric($supplier_id) || $supplier_id <= 0) {
            $_SESSION['error'] = "Invalid Supplier ID.";
            header("Location: delete.php");
            exit();
        }
        
        $check_relationship_sql = "SELECT ProductID, SupplierID FROM Product WHERE ProductID = ? AND SupplierID = ?";
        $check_relationship_stmt = $conn->prepare($check_relationship_sql);
        $check_relationship_stmt->bind_param("ii", $product_id, $supplier_id);
        $check_relationship_stmt->execute();
        $check_relationship_result = $check_relationship_stmt->get_result();
        
        if ($check_relationship_result->num_rows === 0) {
            $_SESSION['error'] = "Product-supplier relationship not found.";
            $check_relationship_stmt->close();
            header("Location: delete.php");
            exit();
        }
        $check_relationship_stmt->close();
        
        $sql = "DELETE FROM Product WHERE ProductID = ? AND SupplierID = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("ii", $product_id, $supplier_id);
        
        if ($stmt->execute()) {
            if ($stmt->affected_rows > 0) {
                $_SESSION['success'] = "Successfully deleted supplier relationship for Product ID {$product_id}.";
            } else {
                $_SESSION['error'] = "Failed to delete supplier relationship.";
            }
        } else {
            $_SESSION['error'] = "Error deleting supplier relationship: " . $conn->error;
        }
        
        $stmt->close();
    }
    
} else {
    header("Location: delete.php");
    exit();
}

header("Location: delete.php");
exit();
?> 