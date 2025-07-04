<?php
session_start();

if (!isset($_SESSION['user_id']) || !isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Inventory Management</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
        }
        
        .container {
            max-width: 1200px;
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
        
        .user-info {
            text-align: right;
        }
        
        .user-info p {
            margin: 5px 0;
            color: #666;
        }
        
        .logout-btn {
            background: linear-gradient(45deg, #ff6b6b, #ee5a24);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            cursor: pointer;
            font-size: 14px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
        }
        
        .logout-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(255, 107, 107, 0.4);
        }
        
        .dashboard-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }
        
        .card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            text-align: center;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.15);
        }
        
        .card h2 {
            margin: 0 0 20px 0;
            color: #333;
            font-size: 1.8em;
            font-weight: 400;
        }
        
        .card p {
            color: #666;
            line-height: 1.6;
            margin-bottom: 25px;
        }
        
        .btn {
            display: inline-block;
            padding: 12px 25px;
            background: linear-gradient(45deg, #667eea, #764ba2);
            color: white;
            text-decoration: none;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
            font-size: 16px;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.4);
        }
        
        .btn-secondary {
            background: linear-gradient(45deg, #4ecdc4, #44a08d);
        }
        
        .btn-secondary:hover {
            box-shadow: 0 8px 25px rgba(78, 205, 196, 0.4);
        }
        
        .btn-warning {
            background: linear-gradient(45deg, #f39c12, #e67e22);
        }
        
        .btn-warning:hover {
            box-shadow: 0 8px 25px rgba(243, 156, 18, 0.4);
        }
        
        .btn-danger {
            background: linear-gradient(45deg, #e74c3c, #c0392b);
        }
        
        .btn-danger:hover {
            box-shadow: 0 8px 25px rgba(231, 76, 60, 0.4);
        }
        
        .stats-section {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
        }
        
        .stat-item {
            text-align: center;
            padding: 20px;
            background: rgba(102, 126, 234, 0.1);
            border-radius: 10px;
        }
        
        .stat-number {
            font-size: 2.5em;
            font-weight: bold;
            color: #667eea;
            margin: 0;
        }
        
        .stat-label {
            color: #666;
            margin: 5px 0 0 0;
            font-size: 14px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        
        @media (max-width: 768px) {
            .header {
                flex-direction: column;
                text-align: center;
            }
            
            .user-info {
                text-align: center;
                margin-top: 15px;
            }
            
            .dashboard-grid {
                grid-template-columns: 1fr;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Inventory Dashboard</h1>
            <div class="user-info">
                <p><strong>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</strong></p>
                <p>User ID: <?php echo $_SESSION['user_id']; ?></p>
                <a href="logout.php" class="logout-btn">🚪 Logout</a>
            </div>
        </div>
        
        <div class="stats-section">
            <h2 style="margin-top: 0; color: #333; text-align: center;">📈 Quick Statistics</h2>
            <div class="stats-grid">
                <div class="stat-item">
                    <p class="stat-number">15</p>
                    <p class="stat-label">Total Products</p>
                </div>
                <div class="stat-item">
                    <p class="stat-number">8</p>
                    <p class="stat-label">Categories</p>
                </div>
                <div class="stat-item">
                    <p class="stat-number">1,247</p>
                    <p class="stat-label">Total Quantity</p>
                </div>
                <div class="stat-item">
                    <p class="stat-number">$12,450</p>
                    <p class="stat-label">Total Value</p>
                </div>
            </div>
        </div>
        
        <div class="dashboard-grid">
            <div class="card">
                <h2>📦 View Inventory</h2>
                <p>Browse and manage your complete product inventory with detailed information about quantities, categories, and suppliers.</p>
                <a href="inventory.php" class="btn">View Inventory</a>
            </div>
            
            <div class="card">
                <h2>➕ Add Product</h2>
                <p>Add new products to your inventory with comprehensive details including name, quantity, category, and supplier information.</p>
                <a href="add_product.php" class="btn btn-secondary">Add Product</a>
            </div>
            
            <div class="card">
                <h2>🔍 Search Products</h2>
                <p>Quickly find specific products using advanced search functionality with filters for name, category, and supplier.</p>
                <a href="search.php" class="btn btn-warning">Search Products</a>
            </div>
            
            <div class="card">
                <h2>✏️ Update Products</h2>
                <p>Modify existing product information including quantities, prices, and other details to keep your inventory current.</p>
                <a href="update.php" class="btn btn-warning">Update Products</a>
            </div>
            
            <div class="card">
                <h2>🗑️ Delete Products</h2>
                <p>Remove products from your inventory that are no longer needed or available in your stock.</p>
                <a href="delete.php" class="btn btn-danger">Delete Products</a>
            </div>
            
            <div class="card">
                <h2>📊 Reports</h2>
                <p>Generate comprehensive reports and analytics to track inventory performance and make informed decisions.</p>
                <a href="#" class="btn btn-secondary" onclick="alert('Reports feature coming soon!')">View Reports</a>
            </div>
            

        </div>
    </div>
</body>
</html> 