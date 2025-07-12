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
        $message = "Product with ID $product_id not found.";
    }
    
    $stmt->close();
}

$suppliers = [];
$suppliers_sql = "SELECT SupplierID, SupplierName FROM Suppliers ORDER BY SupplierName";
$suppliers_stmt = $conn->prepare($suppliers_sql);
$suppliers_stmt->execute();
$suppliers_result = $suppliers_stmt->get_result();

if ($suppliers_result && $suppliers_result->num_rows > 0) {
    while ($row = $suppliers_result->fetch_assoc()) {
        $suppliers[] = $row;
    }
}
$suppliers_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Update Product - Inventory Management</title>
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
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group input[readonly] {
            background: #f8f9fa;
            color: #666;
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

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .btn-success:hover {
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

        .entry-form {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            align-items: end;
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

            .entry-form {
                grid-template-columns: 1fr;
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
                <li><a href="delete.php">Delete Product</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="main-container">
        <div class="page-header">
            <h1 class="page-title">Update Product</h1>
            <p class="page-subtitle">Edit or update product detials</p>
        </div>

        <div class="form-container">
            <!-- Fetch Product Form -->
            <div class="form-section">
                <h3>Find Product to Update</h3>
                <form method="POST" action="update.php">
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product_id">Product ID <span class="required">*</span></label>
                            <input type="number" id="product_id" name="product_id" 
                                   placeholder="Enter Product ID" required>
                            <div class="help-text">Enter the Product ID you want to update</div>
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
                            
                            <form method="POST" action="update_handler.php" class="entry-form">
                                <input type="hidden" name="product_id" value="<?php echo $product['ProductID']; ?>">
                                <input type="hidden" name="supplier_id" value="<?php echo $product['SupplierID']; ?>">
                                
                                <div class="form-group">
                                    <label for="product_type_<?php echo $index; ?>">Product Type <span class="required">*</span></label>
                                    <input type="text" id="product_type_<?php echo $index; ?>" name="product_type" 
                                           value="<?php echo htmlspecialchars($product['ProductType']); ?>" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="supplier_id_<?php echo $index; ?>">Supplier <span class="required">*</span></label>
                                    <select id="supplier_id_<?php echo $index; ?>" name="new_supplier_id" required>
                                        <?php foreach ($suppliers as $supplier): ?>
                                            <option value="<?php echo htmlspecialchars($supplier['SupplierID']); ?>" 
                                                    <?php echo $supplier['SupplierID'] == $product['SupplierID'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($supplier['SupplierName']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="quantity_<?php echo $index; ?>">Quantity <span class="required">*</span></label>
                                    <input type="number" id="quantity_<?php echo $index; ?>" name="quantity" 
                                           value="<?php echo $product['Quantity']; ?>" min="0" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="price_<?php echo $index; ?>">Price ($) <span class="required">*</span></label>
                                    <input type="number" id="price_<?php echo $index; ?>" name="price" 
                                           value="<?php echo $product['Price']; ?>" min="0" step="0.01" required>
                                </div>
                                
                                <div class="form-group">
                                    <label for="status_<?php echo $index; ?>">Status <span class="required">*</span></label>
                                    <select id="status_<?php echo $index; ?>" name="status" required>
                                        <option value="A" <?php echo $product['Status'] === 'A' ? 'selected' : ''; ?>>Active (A)</option>
                                        <option value="B" <?php echo $product['Status'] === 'B' ? 'selected' : ''; ?>>Inactive (B)</option>
                                        <option value="C" <?php echo $product['Status'] === 'C' ? 'selected' : ''; ?>>Low Stock (C)</option>
                                    </select>
                                </div>
                                
                                <div class="form-group">
                                    <label for="description_<?php echo $index; ?>">Description</label>
                                    <textarea id="description_<?php echo $index; ?>" name="description" rows="3"><?php echo htmlspecialchars($product['Description'] ?? ''); ?></textarea>
                                </div>
                                
                                <div class="form-group">
                                    <button type="submit" class="btn btn-success">💾 Update This Entry</button>
                                </div>
                            </form>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <!-- Form Actions -->
            <div class="form-actions">
                <a href="inventory.php" class="btn btn-secondary">Back to Inventory</a>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const forms = document.querySelectorAll('form');
            forms.forEach(form => {
                form.addEventListener('submit', function(e) {
                    const requiredFields = form.querySelectorAll('[required]');
                    let isValid = true;

                    requiredFields.forEach(field => {
                        if (!field.value.trim()) {
                            field.style.borderColor = '#dc3545';
                            isValid = false;
                        } else {
                            field.style.borderColor = '#e1e5e9';
                        }
                    });

                    if (!isValid) {
                        e.preventDefault();
                        alert('Please fill in all required fields marked with *');
                    }
                });
            });
        });
    </script>
</body>
</html> 