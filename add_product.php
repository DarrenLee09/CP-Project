<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connection.php';

$suppliers = array();
$sql = "SELECT SupplierID, SupplierName FROM Suppliers ORDER BY SupplierName";
$res = $conn->query($sql);

if ($res && $res->num_rows > 0) {
    while ($row = $res->fetch_assoc()) {
        $suppliers[] = $row;
    }
}

$next_id_sql = "SELECT MAX(ProductID) as max_id FROM Product";
$next_id_result = $conn->query($next_id_sql);
$next_id = 1;
if ($next_id_result && $next_id_result->num_rows > 0) {
    $row = $next_id_result->fetch_assoc();
    $next_id = ($row['max_id'] ?? 0) + 1;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - Inventory Management</title>
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
            max-width: 800px;
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
            margin-bottom: 1.5rem;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
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
            font-family: inherit;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #667eea;
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .required {
            color: #dc3545;
        }

        .help-text {
            font-size: 0.875rem;
            color: #666;
            margin-top: 0.25rem;
        }

        .product-id-display {
            background: #e9ecef;
            padding: 0.75rem;
            border-radius: 5px;
            font-family: 'Courier New', monospace;
            font-weight: 600;
            color: #667eea;
            text-align: center;
            font-size: 1.1rem;
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

        .form-actions {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e9ecef;
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

        .form-preview {
            background: #f8f9fa;
            padding: 1.5rem;
            border-radius: 5px;
            margin-bottom: 1.5rem;
            border-left: 4px solid #667eea;
        }

        .form-preview h4 {
            color: #667eea;
            margin-bottom: 1rem;
        }

        .preview-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
        }

        .preview-item {
            display: flex;
            flex-direction: column;
        }

        .preview-label {
            font-weight: 600;
            color: #666;
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }

        .preview-value {
            color: #333;
            font-size: 1rem;
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
                <li><a href="update.php">Add/Update Product</a></li>
                <li><a href="delete.php">Delete Product</a></li>
                <li><a href="logout.php">Logout</a></li>
            </ul>
        </div>
    </nav>

    <div class="main-container">
        <div class="page-header">
            <h1 class="page-title">Add New Product</h1>
            <p class="page-subtitle">Add a new product to your invntory</p>
        </div>

        <div class="form-container">
            <form action="add_handler.php" method="POST" id="addProductForm">
                <div class="form-section">
                    <h3>Product Identification</h3>
                    
                    <div class="form-group">
                        <label for="product_id">Product ID <span class="required">*</span></label>
                        <div class="product-id-display">
                            <?php echo $next_id; ?>
                        </div>
                        <input type="hidden" id="product_id" name="product_id" value="<?php echo $next_id; ?>">
                        <div class="help-text">This is the unique identifier for your product (auto-generated)</div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Basic Information</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="product_type">Product Type <span class="required">*</span></label>
                            <input type="text" id="product_type" name="product_type" 
                                   placeholder="Enter product type (e.g., TV, Mouse, Keyboard)" required>
                            <div class="help-text">Enter the type/category of the product (e.g., TV, Mouse, Keyboard)</div>
                        </div>
                        <div class="form-group">
                            <label for="supplier_id">Supplier <span class="required">*</span></label>
                            <select id="supplier_id" name="supplier_id" required>
                                <option value="">Select a supplier</option>
                                <?php foreach ($suppliers as $sup): ?>
                                    <option value="<?php echo htmlspecialchars($sup['SupplierID']); ?>">
                                        <?php echo htmlspecialchars($sup['SupplierName']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="help-text">Choose the supplier for this product</div>
                        </div>
                    </div>

                    <div class="form-group full-width">
                        <label for="description">Description</label>
                        <textarea id="description" name="description" 
                                  placeholder="Enter product description (e.g., Samsung 55-inch 4K Smart TV, Wireless Gaming Mouse)"></textarea>
                        <div class="help-text">Provide the specific product name/details (e.g., Samsung 55-inch 4K Smart TV, Wireless Gaming Mouse)</div>
                    </div>
                </div>

                <div class="form-section">
                    <h3>Inventory Details</h3>
                    
                    <div class="form-row">
                        <div class="form-group">
                            <label for="quantity">Quantity <span class="required">*</span></label>
                            <input type="number" id="quantity" name="quantity" 
                                   placeholder="0" min="0" required>
                            <div class="help-text">Current stock quantity</div>
                        </div>
                        <div class="form-group">
                            <label for="price">Price ($) <span class="required">*</span></label>
                            <input type="number" id="price" name="price" 
                                   placeholder="0.00" min="0" step="0.01" required>
                            <div class="help-text">Unit price in dollars</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Status <span class="required">*</span></label>
                        <select id="status" name="status" required>
                            <option value="">Select status</option>
                            <option value="A">Active (A)</option>
                            <option value="B">Inactive (B)</option>
                            <option value="C">Low Stock (C)</option>
                        </select>
                        <div class="help-text">Current status of the product (A=Active, B=Inactive, C=Low Stock)</div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <a href="inventory.php" class="btn btn-secondary">Cancel</a>
                    <button type="submit" class="btn btn-primary">Add Product</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        document.getElementById('addProductForm').addEventListener('submit', function(e) {
            var requiredFields = ['product_type', 'supplier_id', 'quantity', 'price', 'status'];
            var isValid = true;
            requiredFields.forEach(function(fieldId) {
                var field = document.getElementById(fieldId);
                if (!field.value.trim()) {
                    field.style.borderColor = '#dc3545';
                    isValid = false;
                } else {
                    field.style.borderColor = '#e1e5e9';
                }
            });
            if (!isValid) {
                e.preventDefault();
                alert('Fill all required fields!');
            }
        });

        document.querySelectorAll('input, select, textarea').forEach(function(field) {
            field.addEventListener('blur', function() {
                if (this.hasAttribute('required') && !this.value.trim()) {
                    this.style.borderColor = '#dc3545';
                } else {
                    this.style.borderColor = '#e1e5e9';
                }
            });
        });
    </script>
</body>
</html> 