# Inventory Management System - Setup Guide

## Step 1: Install PHP (Thread Safe Version)

1. **Download PHP Thread Safe Version:**
2. **Extract and Setup PHP:**
3. **Add PHP to System PATH:**

## Step 2: Database Setup

1. **Open MySQL Workbench:**

2. **Run Database Setup Script:**

   - Run the `database_setup.sql` file in MySQL Workbench

3. **Verify Database Creation:**

   - Check that the `inventory_management` database exists
   - Verify tables: `Users`, `Product`, `inventory`, `Suppliers`

4. **ADD data to Database**
   - Run the `inventory_data_insert.sql` file in MySQL Workbench

## Step 3: Configure Database Connection

1. **Edit Database Configuration:**
   - Open `db_connection.php` in your code editor
   - Update the credentials to match your MySQL setup:

```php
<?php
$host = 'localhost';        // Your MySQL host
$username = 'your_username'; // Your MySQL username usually root
$password = 'your_password'; // Your MySQL password the one you set
$database = 'inventory_management';  // Database name (should be 'inventory_management')
?>
```

2. **Test Database Connection:**
   - Save the file
   - The system will automatically test the connection when you run it

## Step 4: Run the Application

1. **Start PHP Development Server:**

   - Open Command Prompt in the project directory
   - Run: `php -S localhost:8000`
   - You should see: "Development Server (http://localhost:8000/) started"

2. **Access the Application:**
   - Open your web browser
   - Go to: `http://localhost:8000`
   - You'll be redirected to the login page

## Step 5: Login and Test

1. **Default Login Credentials:**

   - **Username:** `admin`
   - **Password:** `admin`

   OR

   - **Username:** `user2`
   - **Password:** `user2`

2. **Test Features:**
   - Login with the credentials above
   - Navigate through the dashboard
   - View inventory
   - Test CRUD operations (Create, Read, Update, Delete)
