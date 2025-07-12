<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

require_once 'db_connection.php';

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$sort_by = isset($_GET['sort']) ? $_GET['sort'] : 'ProductID';
$sort_order = isset($_GET['order']) ? $_GET['order'] : 'ASC';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$supplier_filter = isset($_GET['supplier']) ? $_GET['supplier'] : '';
$quantity_filter = isset($_GET['quantity']) ? $_GET['quantity'] : '';

$allowed_sort_fields = ['ProductID', 'ProductType', 'Quantity', 'Price', 'Status', 'SupplierName'];
$allowed_sort_orders = ['ASC', 'DESC'];

if (!in_array($sort_by, $allowed_sort_fields)) {
    $sort_by = 'ProductID';
}
if (!in_array($sort_order, $allowed_sort_orders)) {
    $sort_order = 'ASC';
}

$sql = "SELECT p.ProductID, p.ProductType, p.Quantity, p.Price, p.Description, p.Status,
               s.SupplierName
        FROM Product p
        LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID
        WHERE 1=1";

$params = [];

if (!empty($search)) {
    $sql .= " AND (p.ProductType LIKE ? OR p.Description LIKE ? OR s.SupplierName LIKE ?)";
    $search_param = "%$search%";
    $params[] = $search_param;
    $params[] = $search_param;
    $params[] = $search_param;
}

if (!empty($status_filter)) {
    $sql .= " AND p.Status = ?";
    $params[] = $status_filter;
}

if (!empty($supplier_filter)) {
    $sql .= " AND s.SupplierName = ?";
    $params[] = $supplier_filter;
}

if (!empty($quantity_filter)) {
    switch ($quantity_filter) {
        case 'low':
            $sql .= " AND p.Quantity <= 10";
            break;
        case 'medium':
            $sql .= " AND p.Quantity > 10 AND p.Quantity <= 50";
            break;
        case 'high':
            $sql .= " AND p.Quantity > 50";
            break;
        case 'out':
            $sql .= " AND p.Quantity = 0";
            break;
    }
}

$sql .= " ORDER BY $sort_by $sort_order";

$stmt = $conn->prepare($sql);
if (!empty($params)) {
    $stmt->bind_param(str_repeat('s', count($params)), ...$params);
}
$stmt->execute();
$result = $stmt->get_result();

if (!$result) {
    $error_message = "Oops, couldn't get inventory. Try again later.";
}

$suppliers_sql = "SELECT DISTINCT s.SupplierName FROM Suppliers s 
                  INNER JOIN Product p ON s.SupplierID = p.SupplierID 
                  ORDER BY s.SupplierName";
$suppliers_stmt = $conn->prepare($suppliers_sql);
$suppliers_stmt->execute();
$suppliers_result = $suppliers_stmt->get_result();
$suppliers = [];
if ($suppliers_result) {
    while ($row = $suppliers_result->fetch_assoc()) {
        $suppliers[] = $row['SupplierName'];
    }
}
$suppliers_stmt->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inventory - Inventory Management</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1400px;
            margin: 0 auto;
            padding: 20px;
        }
        
        .header {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .header h1 {
            margin: 0;
            color: #333;
            font-size: 2.5em;
            font-weight: 300;
        }
        
        .nav-buttons {
            display: flex;
            gap: 15px;
        }
        
        .btn {
            padding: 10px 20px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 14px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(45deg, #4ecdc4, #44a08d);
        }
        
        .btn-secondary:hover {
            box-shadow: 0 5px 15px rgba(78, 205, 196, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
        }
        
        .btn-danger:hover {
            box-shadow: 0 5px 15px rgba(231, 76, 60, 0.4);
        }

        /* Search and Filter Section */
        .search-filter-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 25px;
            margin-bottom: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
        }

        .search-filter-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 20px;
            align-items: end;
        }

        .filter-group {
            display: flex;
            flex-direction: column;
        }

        .filter-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
            font-size: 14px;
        }

        .filter-input {
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        .filter-input:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .filter-select {
            padding: 12px 15px;
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            font-size: 14px;
            background: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .filter-select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .filter-actions {
            display: flex;
            gap: 10px;
            align-items: end;
        }

        .btn-clear {
            background: linear-gradient(45deg, #95a5a6, #7f8c8d);
            padding: 12px 20px;
        }

        .btn-clear:hover {
            box-shadow: 0 5px 15px rgba(149, 165, 166, 0.4);
        }

        /* Results Summary */
        .results-summary {
            background: rgba(255, 255, 255, 0.9);
            border-radius: 10px;
            padding: 15px 20px;
            margin-bottom: 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 14px;
            color: #666;
        }

        .results-count {
            font-weight: 600;
            color: #333;
        }

        .sort-controls {
            display: flex;
            gap: 10px;
            align-items: center;
        }

        .sort-controls label {
            font-weight: 600;
            color: #333;
            margin: 0;
        }
        
        .inventory-table {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .table-header {
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
        }
        
        .table-header h2 {
            margin: 0;
            font-size: 1.8em;
            font-weight: 400;
        }
        
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
        }
        
        th, td {
            padding: 15px;
            text-align: left;
            border-bottom: 1px solid #eee;
        }
        
        th {
            background: #f8f9fa;
            font-weight: 600;
            color: #333;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            position: relative;
        }

        th:hover {
            background: #e9ecef;
        }

        th.sortable::after {
            content: '↕';
            position: absolute;
            right: 10px;
            color: #999;
            font-size: 12px;
        }

        th.sort-asc::after {
            content: '↑';
            color: #667eea;
        }

        th.sort-desc::after {
            content: '↓';
            color: #667eea;
        }
        
        tr:hover {
            background: #f8f9fa;
        }
        
        .product-id {
            font-weight: 600;
            color: #667eea;
            font-family: 'Courier New', monospace;
        }
        
        .product-name {
            font-weight: 600;
            color: #333;
        }
        
        .quantity {
            font-weight: 600;
        }
        
        .quantity.low {
            color: #e74c3c;
        }
        
        .quantity.medium {
            color: #f39c12;
        }
        
        .quantity.high {
            color: #27ae60;
        }
        
        .price {
            font-weight: 600;
            color: #667eea;
        }
        
        .status {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .status.a {
            background: rgba(39, 174, 96, 0.1);
            color: #27ae60;
        }
        
        .status.b {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
        }
        
        .status.c {
            background: rgba(243, 156, 18, 0.1);
            color: #f39c12;
        }
        
        .supplier {
            color: #666;
            font-size: 14px;
        }
        
        .actions {
            display: flex;
            gap: 8px;
        }
        
        .action-btn {
            padding: 6px 12px;
            border: none;
            border-radius: 15px;
            cursor: pointer;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }
        
        .edit-btn {
            background: linear-gradient(45deg, #f39c12, #e67e22);
            color: white;
        }
        
        .edit-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(243, 156, 18, 0.4);
        }
        
        .delete-btn {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
            color: white;
        }
        
        .delete-btn:hover {
            transform: translateY(-1px);
            box-shadow: 0 3px 10px rgba(231, 76, 60, 0.4);
        }
        
        .error-message {
            background: rgba(231, 76, 60, 0.1);
            color: #e74c3c;
            padding: 15px;
            border-radius: 10px;
            margin-bottom: 20px;
            border-left: 4px solid #e74c3c;
        }
        
        .empty-state {
            text-align: center;
            padding: 60px 20px;
            color: #666;
        }
        
        .empty-state h3 {
            margin: 0 0 10px 0;
            color: #333;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
                gap: 15px;
            }
            
            .nav-buttons {
                flex-wrap: wrap;
                justify-content: center;
            }

            .search-filter-grid {
                grid-template-columns: 1fr;
            }

            .filter-actions {
                flex-direction: column;
            }

            .results-summary {
                flex-direction: column;
                gap: 10px;
                text-align: center;
            }

            .sort-controls {
                flex-direction: column;
                gap: 5px;
            }
            
            table {
                font-size: 14px;
            }
            
            th, td {
                padding: 10px 8px;
            }
            
            .actions {
                flex-direction: column;
                gap: 5px;
            }
            
            .action-btn {
                padding: 8px 12px;
                font-size: 11px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📦 Inventory Management</h1>
            <div class="nav-buttons">
                <a href="dashboard.php" class="btn">🏠 Dashboard</a>
                <a href="add_product.php" class="btn btn-secondary">➕ Add Product</a>
                <a href="search.php" class="btn">🔍 Search</a>
                <a href="logout.php" class="btn btn-danger">🚪 Logout</a>
            </div>
        </div>
        
        <?php if (isset($error_message)): ?>
            <div class="error-message">
                <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <!-- Search and Filter Section -->
        <div class="search-filter-section">
            <form method="GET" action="inventory.php" id="filterForm">
                <div class="search-filter-grid">
                    <div class="filter-group">
                        <label for="search">🔍 Search Products</label>
                        <input type="text" id="search" name="search" class="filter-input" 
                               placeholder="Search by product type, description, or supplier..." 
                               value="<?php echo htmlspecialchars($search); ?>">
                    </div>

                    <div class="filter-group">
                        <label for="status">📊 Status Filter</label>
                        <select id="status" name="status" class="filter-select">
                            <option value="">All Statuses</option>
                            <option value="A" <?php echo $status_filter === 'A' ? 'selected' : ''; ?>>Active (A)</option>
                            <option value="B" <?php echo $status_filter === 'B' ? 'selected' : ''; ?>>Inactive (B)</option>
                            <option value="C" <?php echo $status_filter === 'C' ? 'selected' : ''; ?>>Low Stock (C)</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="supplier">🏢 Supplier Filter</label>
                        <select id="supplier" name="supplier" class="filter-select">
                            <option value="">All Suppliers</option>
                            <?php foreach ($suppliers as $supplier): ?>
                                <option value="<?php echo htmlspecialchars($supplier); ?>" 
                                        <?php echo $supplier_filter === $supplier ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($supplier); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="quantity">📦 Quantity Filter</label>
                        <select id="quantity" name="quantity" class="filter-select">
                            <option value="">All Quantities</option>
                            <option value="out" <?php echo $quantity_filter === 'out' ? 'selected' : ''; ?>>Out of Stock (0)</option>
                            <option value="low" <?php echo $quantity_filter === 'low' ? 'selected' : ''; ?>>Low Stock (≤10)</option>
                            <option value="medium" <?php echo $quantity_filter === 'medium' ? 'selected' : ''; ?>>Medium Stock (11-50)</option>
                            <option value="high" <?php echo $quantity_filter === 'high' ? 'selected' : ''; ?>>High Stock (>50)</option>
                        </select>
                    </div>

                    <div class="filter-actions">
                        <button type="submit" class="btn">🔍 Apply Filters</button>
                        <a href="inventory.php" class="btn btn-clear">🔄 Clear All</a>
                    </div>
                </div>
            </form>
        </div>

        <div class="results-summary">
            <div class="results-count">
                📊 Showing <?php echo $result ? $result->num_rows : 0; ?> product(s)
                <?php if (!empty($search) || !empty($status_filter) || !empty($supplier_filter) || !empty($quantity_filter)): ?>
                    (filtered)
                <?php endif; ?>
            </div>
            <div class="sort-controls">
                <label for="sort">Sort by:</label>
                <select id="sort" name="sort" class="filter-select" onchange="updateSort()">
                    <option value="ProductID" <?php echo $sort_by === 'ProductID' ? 'selected' : ''; ?>>Product ID</option>
                    <option value="ProductType" <?php echo $sort_by === 'ProductType' ? 'selected' : ''; ?>>Product Type</option>
                    <option value="Quantity" <?php echo $sort_by === 'Quantity' ? 'selected' : ''; ?>>Quantity</option>
                    <option value="Price" <?php echo $sort_by === 'Price' ? 'selected' : ''; ?>>Price</option>
                    <option value="Status" <?php echo $sort_by === 'Status' ? 'selected' : ''; ?>>Status</option>
                    <option value="SupplierName" <?php echo $sort_by === 'SupplierName' ? 'selected' : ''; ?>>Supplier</option>
                </select>
                <select id="order" name="order" class="filter-select" onchange="updateSort()">
                    <option value="ASC" <?php echo $sort_order === 'ASC' ? 'selected' : ''; ?>>Ascending</option>
                    <option value="DESC" <?php echo $sort_order === 'DESC' ? 'selected' : ''; ?>>Descending</option>
                </select>
            </div>
        </div>
        
        <div class="inventory-table">
            <div class="table-header">
                <h2>📋 Product Inventory</h2>
            </div>
            
            <?php if ($result && $result->num_rows > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th class="sortable <?php echo $sort_by === 'ProductID' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" 
                                onclick="sortTable('ProductID')">Product ID</th>
                            <th class="sortable <?php echo $sort_by === 'ProductType' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" 
                                onclick="sortTable('ProductType')">Product Type</th>
                            <th class="sortable <?php echo $sort_by === 'Quantity' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" 
                                onclick="sortTable('Quantity')">Quantity</th>
                            <th class="sortable <?php echo $sort_by === 'Price' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" 
                                onclick="sortTable('Price')">Price</th>
                            <th class="sortable <?php echo $sort_by === 'Status' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" 
                                onclick="sortTable('Status')">Status</th>
                            <th class="sortable <?php echo $sort_by === 'SupplierName' ? ($sort_order === 'ASC' ? 'sort-asc' : 'sort-desc') : ''; ?>" 
                                onclick="sortTable('SupplierName')">Supplier</th>
                            <th>Description</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <?php 
                            $display_status = $row['Status'];
                            
                            if ($row['Quantity'] < 5 && $row['Status'] !== 'B') {
                                $display_status = 'C';
                            }
                            ?>
                            <tr>
                                <td class="product-id">#<?php echo $row['ProductID']; ?></td>
                                <td class="product-name"><?php echo htmlspecialchars($row['ProductType']); ?></td>
                                <td>
                                    <span class="quantity <?php 
                                        echo $row['Quantity'] <= 10 ? 'low' : 
                                            ($row['Quantity'] <= 50 ? 'medium' : 'high'); 
                                    ?>">
                                        <?php echo $row['Quantity']; ?>
                                    </span>
                                </td>
                                <td class="price">$<?php echo number_format($row['Price'], 2); ?></td>
                                <td>
                                    <span class="status <?php echo strtolower($display_status); ?>">
                                        <?php echo htmlspecialchars($display_status); ?>
                                    </span>
                                </td>
                                <td class="supplier">
                                    <?php echo htmlspecialchars($row['SupplierName'] ?? 'No Supplier'); ?>
                                </td>
                                <td>
                                    <?php 
                                    $description = $row['Description'] ?? '';
                                    echo htmlspecialchars(strlen($description) > 50 ? 
                                        substr($description, 0, 50) . '...' : $description); 
                                    ?>
                                </td>
                                <td class="actions">
                                    <a href="update.php?id=<?php echo $row['ProductID']; ?>" 
                                       class="action-btn edit-btn">✏️ Edit</a>
                                    <a href="delete.php?id=<?php echo $row['ProductID']; ?>" 
                                       class="action-btn delete-btn" 
                                       onclick="return confirm('Are you sure you want to delete this product?')">
                                        🗑️ Delete
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <div class="empty-state">
                    <h3>No products found</h3>
                    <p>
                        <?php if (!empty($search) || !empty($status_filter) || !empty($supplier_filter) || !empty($quantity_filter)): ?>
                            No products match your current filters. Try adjusting your search criteria.
                        <?php else: ?>
                            Your inventory is empty. Add your first product to get started!
                        <?php endif; ?>
                    </p>
                    <a href="add_product.php" class="btn btn-secondary">Add Product</a>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function updateSort() {
            const sortSelect = document.getElementById('sort');
            const orderSelect = document.getElementById('order');
            const currentUrl = new URL(window.location);
            
            currentUrl.searchParams.set('sort', sortSelect.value);
            currentUrl.searchParams.set('order', orderSelect.value);
            
            window.location.href = currentUrl.toString();
        }

        function sortTable(column) {
            const currentUrl = new URL(window.location);
            const currentSort = currentUrl.searchParams.get('sort');
            const currentOrder = currentUrl.searchParams.get('order');
            
            let newOrder = 'ASC';
            if (currentSort === column && currentOrder === 'ASC') {
                newOrder = 'DESC';
            }
            
            currentUrl.searchParams.set('sort', column);
            currentUrl.searchParams.set('order', newOrder);
            
            window.location.href = currentUrl.toString();
        }

        document.addEventListener('DOMContentLoaded', function() {
            const filterInputs = document.querySelectorAll('#status, #supplier, #quantity');
            filterInputs.forEach(input => {
                input.addEventListener('change', function() {
                    document.getElementById('filterForm').submit();
                });
            });

            let searchTimeout;
            const searchInput = document.getElementById('search');
            searchInput.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    document.getElementById('filterForm').submit();
                }, 500);
            });
        });
    </script>
</body>
</html> 