<?php
require_once 'db_connection.php';

echo "<h2>🔄 Safe Data Insertion with Prepared Statements</h2>";

// Check existing data first
echo "<h3>📊 Checking Existing Data...</h3>";

$check_users = $conn->query("SELECT COUNT(*) as count FROM Users");
$user_count = $check_users->fetch_assoc()['count'];

$check_suppliers = $conn->query("SELECT COUNT(*) as count FROM Suppliers");
$supplier_count = $check_suppliers->fetch_assoc()['count'];

$check_products = $conn->query("SELECT COUNT(*) as count FROM Product");
$product_count = $check_products->fetch_assoc()['count'];

$check_inventory = $conn->query("SELECT COUNT(*) as count FROM Inventory");
$inventory_count = $check_inventory->fetch_assoc()['count'];

echo "<p><strong>Current Database Status:</strong></p>";
echo "<p>👥 Users: $user_count</p>";
echo "<p>📋 Suppliers: $supplier_count</p>";
echo "<p>📦 Products: $product_count</p>";
echo "<p>📋 Inventory: $inventory_count</p>";

// Only insert if data doesn't exist
if ($supplier_count == 0) {
    echo "<h3>📋 Inserting Suppliers...</h3>";
    
    $suppliers_data = [
        [9512, 'Acme Corporation', '123 Main St', '205-288-8591', 'info@acme-corp.com'],
        [8642, 'Xerox Inc.', '456 High St', '505-398-8414', 'info@xrx.com'],
        [3579, 'RedPark Ltd.', '789 Park Ave', '604-683-2555', 'info@redpark.ca'],
        [7890, 'Samsung', '456 Seoul St', '909-763-4442', 'support@samsung.com'],
        [7671, 'LG Electronics', '789 Busan St', '668-286-5378', 'support@lge.kr'],
        [9876, 'Toshiba', '246 Osaka St', '90-6378-0835', 'support@toshiba.co.jp'],
        [3456, 'Panasonic', '246 Osaka St', '443-887-9967', 'support@panasonic.co.jp'],
        [8765, 'Philips', '789 Amsterdam St', '61-483-898-670', 'support@philips.au'],
        [1357, 'Sharp', '123 Tokyo St', '80-4745-3107', 'support@sharp.co.jp'],
        [9144, 'Fujitsu', '456 Tokyo St', '03-3556-7890', 'support@fujitsu.co.jp'],
        [8655, 'Dell', '246 Austin St', '505-351-3181', 'support@dell.com'],
        [3592, 'IBM', '456 New York St', '201-335-9423', 'support@ibm.com'],
        [7084, 'Acer', '135 Taipei St', '905-926-031', 'support@acer.tw'],
        [2345, 'MSI', '789 Mofan St', '943-299-465', 'support@msi.tw'],
        [6954, 'Apple', '246 Cupertino St', '202-918-2132', 'support@apple.com'],
        [9794, 'Amazon', '246 Seattle St', '555-343-8950', 'support@amazon.com'],
        [8692, 'Microsoft', '123 Redmond St', '505-549-0420', 'support@microsoft.com'],
        [7807, 'Intel', '2200 Mission College Blvd', '408-646-7611', 'support@intel.com'],
        [8672, 'AMD', '246 Santa Clara St', '312-866-2043', 'support@amd.com'],
        [4567, 'Qualcomm', '456 San Diego St', '44-7700-087231', 'info@qualcomm.co.uk']
    ];

    $supplier_sql = "INSERT INTO Suppliers (SupplierID, SupplierName, Address, Phone, Email) VALUES (?, ?, ?, ?, ?)";
    $supplier_stmt = $conn->prepare($supplier_sql);

    $suppliers_inserted = 0;
    foreach ($suppliers_data as $supplier) {
        $supplier_stmt->bind_param("issss", $supplier[0], $supplier[1], $supplier[2], $supplier[3], $supplier[4]);
        if ($supplier_stmt->execute()) {
            $suppliers_inserted++;
            echo "✅ Inserted supplier: {$supplier[1]}<br>";
        } else {
            echo "❌ Failed to insert supplier: {$supplier[1]} - " . $conn->error . "<br>";
        }
    }
    $supplier_stmt->close();
    echo "<br><strong>📊 Suppliers inserted: $suppliers_inserted</strong><br><br>";
} else {
    echo "<h3>📋 Suppliers already exist - skipping insertion</h3><br>";
}

if ($product_count == 0) {
    echo "<h3>📦 Inserting Products...</h3>";
    
    $products_data = [
        [2591, 'Camera', 'Camera', 799.9, 50, 'B', 7890],
        [3374, 'Laptop', 'MacBook Pro', 1799.9, 30, 'A', 9876],
        [3034, 'Telephone', 'Cordless Phone', 299.99, 40, 'A', 3456],
        [3034, 'Telephone', 'Home telephone', 99.9, 25, 'A', 8765],
        [1234, 'TV', 'Plate TV', 799.9, 20, 'C', 9144],
        [1234, 'TV', 'Plate TV', 1499.99, 5, 'A', 7671],
        [2591, 'Camera', 'Instant Camera', 179.5, 30, 'C', 8642],
        [1516, 'Mouse', 'Wireless Mouse', 99.5, 30, 'A', 3579],
        [3034, 'Telephone', 'Home Telephone', 169.99, 15, 'A', 8692],
        [2591, 'Camera', 'Digital Camera', 499.9, 10, 'B', 9512],
        [3034, 'Telephone', 'Home Telephone', 59.5, 20, 'A', 8655],
        [2591, 'Camera', 'Digital Camera', 449.4, 50, 'A', 3592],
        [1234, 'TV', 'Plate TV', 699.7, 5, 'B', 7084],
        [1516, 'Mouse', 'Wireless Mouse', 69.9, 25, 'C', 2345],
        [3374, 'Laptop', 'Laptop', 1399.2, 10, 'B', 1357],
        [3374, 'Laptop', 'Refurbished Laptop', 1099.1, 20, 'A', 6954],
        [1516, 'Mouse', 'Wireless Mouse', 49.4, 50, 'B', 9794],
        [1516, 'Mouse', 'Wireless Mouse', 69.5, 20, 'A', 7807],
        [1234, 'TV', 'Plate TV', 599.3, 5, 'B', 8672],
        [3374, 'Laptop', 'Laptop', 1369.9, 15, 'A', 4567]
    ];

    $product_sql = "INSERT INTO Product (ProductID, ProductType, Description, Price, Quantity, Status, SupplierID) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $product_stmt = $conn->prepare($product_sql);

    $products_inserted = 0;
    foreach ($products_data as $product) {
        $product_stmt->bind_param("issddsi", $product[0], $product[1], $product[2], $product[3], $product[4], $product[5], $product[6]);
        if ($product_stmt->execute()) {
            $products_inserted++;
            echo "✅ Inserted product: {$product[1]} - {$product[2]}<br>";
        } else {
            echo "❌ Failed to insert product: {$product[1]} - {$product[2]} - " . $conn->error . "<br>";
        }
    }
    $product_stmt->close();
    echo "<br><strong>📊 Products inserted: $products_inserted</strong><br><br>";
} else {
    echo "<h3>📦 Products already exist - skipping insertion</h3><br>";
}

if ($inventory_count == 0) {
    echo "<h3>📋 Inserting Inventory...</h3>";
    
    $inventory_sql = "INSERT INTO Inventory (ProductID, ProductType, Quantity, Price, Status, Supplier) 
                      SELECT DISTINCT p.ProductID, p.ProductType, p.Quantity, p.Price, p.Status, s.SupplierName
                      FROM Product p 
                      LEFT JOIN Suppliers s ON p.SupplierID = s.SupplierID";
    $inventory_result = $conn->query($inventory_sql);
    
    if ($inventory_result) {
        $inventory_inserted = $conn->affected_rows;
        echo "✅ Inserted $inventory_inserted inventory records<br>";
    } else {
        echo "❌ Failed to insert inventory: " . $conn->error . "<br>";
    }
    echo "<br><strong>📊 Inventory records inserted: $inventory_inserted</strong><br><br>";
} else {
    echo "<h3>📋 Inventory already exists - skipping insertion</h3><br>";
}

// Final summary
echo "<h2>🎉 Database Status Summary</h2>";

$final_users = $conn->query("SELECT COUNT(*) as count FROM Users")->fetch_assoc()['count'];
$final_suppliers = $conn->query("SELECT COUNT(*) as count FROM Suppliers")->fetch_assoc()['count'];
$final_products = $conn->query("SELECT COUNT(*) as count FROM Product")->fetch_assoc()['count'];
$final_inventory = $conn->query("SELECT COUNT(*) as count FROM Inventory")->fetch_assoc()['count'];

echo "<p><strong>👥 Users:</strong> $final_users</p>";
echo "<p><strong>📋 Suppliers:</strong> $final_suppliers</p>";
echo "<p><strong>📦 Products:</strong> $final_products</p>";
echo "<p><strong>📋 Inventory:</strong> $final_inventory</p>";

echo "<br><a href='login.php' style='background: #667eea; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🚀 Go to Login</a>";

$conn->close();
?> 