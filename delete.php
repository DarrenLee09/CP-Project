<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connection.php';

$products = [];
$message = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['fetch_product'])) {
    $product_id = $_POST['product_id'];
    
    $sql = "SELECT p.ProductID, p.ProductType, p.Quantity, p.Price, p.Status, p.SupplierID, p.Description,
                   s.SupplierName 
            FROM Product p 
            LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID 
            WHERE p.ProductID = ?
            ORDER BY s.SupplierName";
    
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $product_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $products[] = $row;
        }
    } else {
        $message = "Can't find that product, sorry!";
    }
    
    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Delete Product - Inventory Management</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: #f8f9fa;
            color: #333;
        }

        .navbar {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 1rem 2rem;
            box-shadow: 0 2px 10px rgba(0, 0, 0, 0.1);
        }

        .nav-container {
            display: flex;
            justify-content: space-between;
            align-items: center;
            max-width: 1200px;
            margin: 0 auto;
        }

        .nav-brand {
            color: white;
            font-size: 24px;
            font-weight: bold;
            text-decoration: none;
        }

        .nav-menu {
            display: flex;
            list-style: none;
            gap: 2rem;
        }

        .nav-menu a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
            border-radius: 5px;
            transition: background-color 0.3s ease;
        }

        .nav-menu a:hover {
            background-color: rgba(255, 255, 255, 0.2);
        }

        .main-container {
            max-width: 1000px;
            margin: 2rem auto;
            padding: 0 2rem;
        }

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .page-title {
            font-size: 2rem;
            color: #333;
            margin-bottom: 0.5rem;
        }

        .page-subtitle {
            color: #666;
            font-size: 1.1rem;
        }

        .warning-box {
            background: #fff3cd;
            border: 1px solid #ffeaa7;
            color: #856404;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 2rem;
        }

        .warning-box h3 {
            margin-bottom: 0.5rem;
            color: #856404;
        }

        .form-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .form-section {
            margin-bottom: 2rem;
        }

        .form-section h3 {
            color: #333;
            margin-bottom: 1rem;
            font-size: 1.25rem;
            border-bottom: 2px solid #e9ecef;
            padding-bottom: 0.5rem;
        }

        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .form-group {
            margin-bottom: 1rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #667eea;
        }

        .btn {
            padding: 0.75rem 2rem;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            text-align: center;
        }

        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
        }

        .btn-secondary:hover {
            background: #5a6268;
        }

        .btn-danger {
            background: linear-gradient(135deg, #e74c3c, #c0392b);
            color: white;
        }

        .btn-danger:hover {
            transform: translateY(-2px);
        }

        .message {
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
        }

        .message.error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .message.success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .product-entries {
            margin-top: 2rem;
        }

        .product-entry {
            background: #f8f9fa;
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            position: relative;
        }

        .product-entry h4 {
            color: #667eea;
            margin-bottom: 1rem;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .supplier-badge {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .entry-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1rem;
        }

        .info-item {
            display: flex;
            flex-direction: column;
        }

        .info-label {
            font-weight: 600;
            color: #666;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .info-value {
            color: #333;
            font-size: 1rem;
        }

        .delete-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #e9ecef;
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
        }

        .help-text {
            font-size: 0.875rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .required {
            color: #dc3545;
        }

        .status {
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
            font-weight: 500;
        }

        .status.A {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }

        .status.B {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }

        .status.C {
            background: rgba(243, 156, 18, 0.1);
            color: #f39c12;
        }

        @media (max-width: 768px) {
            .nav-container {
                flex-direction: column;
                gap: 1rem;
            }

            .nav-menu {
                flex-wrap: wrap;
                justify-content: center;
            }

            .form-row {
                grid-template-columns: 1fr;
            }

            .entry-info {
                grid-template-columns: 1fr;
            }

            .delete-actions {
                flex-direction: column;
            }

            .form-actions {
                flex-direction: column;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <nav class="navbar">
        <div class="nav-container">
            <a href="dashboard.php" class="nav-brand">Inventory Management</a>
            <ul class="nav-menu">
                <li><a href="inventory.php">Inventory</a></li>
                <li><a href="search.php">Search</a></li>
                <li><a href="add_product.php">Add Product</a></li>
                <li><a href="update.php">Update Product</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="main-container">
        <div class="page-header">
            <h1 class="page-title">Delete Product</h1>
            <p class="page-subtitle">Remove products and supplier relationships from your inventory</p>
        </div>

        <div class="warning-box">
            <h3>⚠️ Warning</h3>
            <p>Deleting products will permanently remove them from your inventory. This action cannot be undone.</p>
        </div>

        <div class="form-container">
            <!-- Fetch Product Form -->
            <div class="form-section">
                <h3>Find Product to Delete</h3>
                <form method="POST" action="delete.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product_id">Product ID <span class="required">*</span></label>
                            <input type="number" id="product_id" name="product_id" 
                                   placeholder="Enter Product ID" required>
                            <div class="help-text">Enter the Product ID you want to delete</div>
                        </div>
                        <div class="form-group">
                            <button type="submit" name="fetch_product" class="btn btn-primary">🔍 Find Product</button>
                        </div>
                    </div>
                </form>
            </div>

            <?php if (!empty($message)): ?>
                <div class="message error">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <?php if (!empty($products)): ?>
                <div class="product-entries">
                    <h3>Product: <?php echo htmlspecialchars($products[0]['ProductType']); ?> (ID: <?php echo $products[0]['ProductID']; ?>)</h3>
                    
                    <?php foreach ($products as $index => $product): ?>
                        <div class="product-entry">
                            <h4>
                                Supplier Relationship #<?php echo $index + 1; ?>
                                <span class="supplier-badge"><?php echo htmlspecialchars($product['SupplierName']); ?></span>
                            </h4>
                            
                            <div class="entry-info">
                                <div class="info-item">
                                    <span class="info-label">Product Type</span>
                                    <span class="info-value"><?php echo htmlspecialchars($product['ProductType']); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Quantity</span>
                                    <span class="info-value"><?php echo $product['Quantity']; ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Price</span>
                                    <span class="info-value">$<?php echo number_format($product['Price'], 2); ?></span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Status</span>
                                    <span class="status <?php echo $product['Status']; ?>">
                                        <?php 
                                        $status_map = [
                                            'A' => 'Active (A)',
                                            'B' => 'Inactive (B)', 
                                            'C' => 'Low Stock (C)'
                                        ];
                                        echo $status_map[$product['Status']] ?? $product['Status'];
                                        ?>
                                    </span>
                                </div>
                                <div class="info-item">
                                    <span class="info-label">Description</span>
                                    <span class="info-value">
                                        <?php 
                                        $description = $product['Description'] ?? '';
                                        echo htmlspecialchars(strlen($description) > 50 ? 
                                            substr($description, 0, 50) . '...' : $description); 
                                        ?>
                                    </span>
                                </div>
                            </div>
                            
                            <div class="delete-actions">
                                <form method="POST" action="delete_handler.php" style="display: inline;">
                                    <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                                    <input type="hidden" name="supplier_id" value="<?php echo $product['SupplierID']; ?>">
                                    <button type="submit" class="btn btn-danger" 
                                            onclick="return confirm('Are you sure you want to delete this supplier relationship? This action cannot be undone.')">
                                        🗑️ Delete This Relationship
                                    </button>
                                </form>
                            </div>
                        </div>
                    <?php endforeach; ?>
                    
                    <!-- Delete All Relationships -->
                    <div class="product-entry" style="border-color: #e74c3c; background: #fff5f5;">
                        <h4 style="color: #e74c3c;">
                            ⚠️ Delete All Relationships
                            <span class="supplier-badge" style="background: linear-gradient(135deg, #e74c3c, #c0392b);">
                                <?php echo count($products); ?> Relationship(s)
                            </span>
                        </h4>
                        <p style="color: #e74c3c; margin-bottom: 1rem;">
                            This will delete ALL supplier relationships for this product. This action cannot be undone.
                        </p>
                        <div class="delete-actions">
                            <form method="POST" action="delete_handler.php" style="display: inline;">
                                <input type="hidden" name="product_id" value="<?php echo $products[0]['ProductID']; ?>">
                                <input type="hidden" name="delete_all" value="1">
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('⚠️ WARNING: This will delete ALL supplier relationships for Product ID <?php echo $products[0]['ProductID']; ?>. This action cannot be undone. Are you absolutely sure?')">
                                    🗑️ Delete ALL Relationships
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="inventory.php" class="btn btn-secondary">Back to Inventory</a>
            </div>
        </div>
    </div>
</body>
</html> 