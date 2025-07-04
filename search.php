<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connection.php';

$search_results = [];
$search_performed = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['search_term'])) {
    $search_type = $_POST['search_type'];
    $search_term = $_POST['search_term'];
    $search_performed = true;

    switch($search_type) {
        case 'product_id':
            $sql = "SELECT p.ProductID, p.ProductType, p.Quantity, p.Price, p.Status, s.SupplierName 
                    FROM Product p 
                    LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID 
                    WHERE p.ProductID = ?";
            break;
        case 'product_type':
            $sql = "SELECT p.ProductID, p.ProductType, p.Quantity, p.Price, p.Status, s.SupplierName 
                    FROM Product p 
                    LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID 
                    WHERE p.ProductType LIKE ?";
            $search_term = "%$search_term%";
            break;
        case 'supplier_name':
            $sql = "SELECT p.ProductID, p.ProductType, p.Quantity, p.Price, p.Status, s.SupplierName 
                    FROM Product p 
                    LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID 
                    WHERE s.SupplierName LIKE ?";
            $search_term = "%$search_term%";
            break;
        default:
            $sql = "SELECT p.ProductID, p.ProductType, p.Quantity, p.Price, p.Status, s.SupplierName 
                    FROM Product p 
                    LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID 
                    WHERE p.ProductType LIKE ? OR s.SupplierName LIKE ?";
            $search_term = "%$search_term%";
            break;
    }

    $stmt = $conn->prepare($sql);
    
    if ($search_type === 'product_id') {
        $stmt->bind_param("i", $search_term);
    } else {
        $stmt->bind_param("s", $search_term);
    }
    
    $stmt->execute();
    $result = $stmt->get_result();
    
    while ($row = $result->fetch_assoc()) {
        $search_results[] = $row;
    }
    
    $stmt->close();

    if ($result->num_rows === 0) {
        // $no_results = true; // quick fix for empty search
        $search_results = [];
        $_SESSION['error'] = "Nope, nothing found. Try something else."; // less formal
    }
    // TODO: add fuzzy search
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search - Inventory Management</title>
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
            max-width: 1200px;
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

        .search-form-container {
            background: white;
            padding: 2rem;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            margin-bottom: 2rem;
        }

        .search-form {
            display: flex;
            gap: 1rem;
            align-items: end;
            flex-wrap: wrap;
        }

        .form-group {
            flex: 1;
            min-width: 200px;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: #333;
        }

        .form-group select,
        .form-group input {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e1e5e9;
            border-radius: 5px;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }

        .form-group select:focus,
        .form-group input:focus {
            outline: none;
            border-color: #667eea;
        }

        .search-btn {
            padding: 0.75rem 2rem;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }

        .search-btn:hover {
            transform: translateY(-2px);
        }

        .results-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .results-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 1rem 2rem;
        }

        .results-title {
            font-size: 1.5rem;
            font-weight: 600;
        }

        .results-table {
            width: 100%;
            border-collapse: collapse;
        }

        .results-table th {
            background: #f8f9fa;
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #333;
            border-bottom: 2px solid #e9ecef;
        }

        .results-table td {
            padding: 1rem;
            border-bottom: 1px solid #e9ecef;
            color: #333;
        }

        .results-table tr:hover {
            background: #f8f9fa;
        }

        .status-active {
            background: #d4edda;
            color: #155724;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-inactive {
            background: #f8d7da;
            color: #721c24;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .status-low-stock {
            background: #fff3cd;
            color: #856404;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.875rem;
            font-weight: 600;
        }

        .price {
            font-weight: 600;
            color: #28a745;
        }

        .quantity-low {
            color: #dc3545;
            font-weight: 600;
        }

        .no-results {
            text-align: center;
            padding: 3rem;
            color: #666;
            font-style: italic;
        }

        .search-info {
            background: #e3f2fd;
            color: #1976d2;
            padding: 1rem;
            border-radius: 5px;
            margin-bottom: 1rem;
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

            .search-form {
                flex-direction: column;
            }

            .form-group {
                min-width: auto;
            }

            .results-table {
                font-size: 0.875rem;
            }

            .results-table th,
            .results-table td {
                padding: 0.5rem;
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
            <h1 class="page-title">Search Products</h1>
            <p class="page-subtitle">Find products by ID, type, or supplier</p>
        </div>

        <div class="search-form-container">
            <form method="POST" class="search-form">
                <div class="form-group">
                    <label for="search_type">Search By</label>
                    <select id="search_type" name="search_type" required>
                        <option value="product_type">Product Type</option>
                        <option value="product_id">Product ID</option>
                        <option value="supplier_name">Supplier Name</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="search_term">Search Term</label>
                    <input type="text" id="search_term" name="search_term" 
                           placeholder="Enter search term..." required>
                </div>

                <button type="submit" class="search-btn">Search</button>
            </form>
        </div>

        <?php if ($search_performed): ?>
            <div class="results-container">
                <div class="results-header">
                    <h2 class="results-title">
                        Search Results 
                        (<?php echo count($search_results); ?> found)
                    </h2>
                </div>

                <?php if (!empty($search_results)): ?>
                    <table class="results-table">
                        <thead>
                            <tr>
                                <th>Product ID</th>
                                <th>Product Type</th>
                                <th>Quantity</th>
                                <th>Price</th>
                                <th>Status</th>
                                <th>Supplier Name</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($search_results as $row): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($row['ProductID']); ?></td>
                                    <td><?php echo htmlspecialchars($row['ProductType']); ?></td>
                                    <td class="<?php echo $row['Quantity'] < 10 ? 'quantity-low' : ''; ?>">
                                        <?php echo htmlspecialchars($row['Quantity']); ?>
                                    </td>
                                    <td class="price">$<?php echo number_format($row['Price'], 2); ?></td>
                                    <td>
                                        <?php 
                                        $status = $row['Status'];
                                        $statusClass = '';
                                        $statusText = '';
                                        switch($status) {
                                            case 'A':
                                                $statusClass = 'status-active';
                                                $statusText = 'Active (A)';
                                                break;
                                            case 'B':
                                                $statusClass = 'status-inactive';
                                                $statusText = 'Inactive (B)';
                                                break;
                                            case 'C':
                                                $statusClass = 'status-low-stock';
                                                $statusText = 'Low Stock (C)';
                                                break;
                                            default:
                                                $statusClass = 'status-active';
                                                $statusText = $status;
                                        }
                                        ?>
                                        <span class="<?php echo $statusClass; ?>">
                                            <?php echo htmlspecialchars($statusText); ?>
                                        </span>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['SupplierName'] ?? 'N/A'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div class="no-results">
                        <p>No products found matching your search criteria.</p>
                        <p>Try a different search term or search type.</p>
                    </div>
                <?php endif; ?>
            </div>
        <?php endif; ?>
    </div>
</body>
</html> 