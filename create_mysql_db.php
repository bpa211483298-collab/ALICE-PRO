<?php
// Database configuration
$host = '127.0.0.1';
$port = 3306;
$username = 'root';
$password = '';
$database = 'alice_pro';

// Try to connect to MySQL server
$conn = @new mysqli($host, $username, $password, '', $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error . "\n");
}

echo "Successfully connected to MySQL server.\n";

// Create database if not exists
$sql = "CREATE DATABASE IF NOT EXISTS `$database`";
if ($conn->query($sql) === TRUE) {
    echo "Database '$database' created successfully or already exists.\n";
} else {
    die("Error creating database: " . $conn->error . "\n");
}

// Select the database
if ($conn->select_db($database)) {
    echo "Successfully selected database '$database'.\n";
} else {
    die("Error selecting database: " . $conn->error . "\n");
}

// Test creating a table
$sql = "CREATE TABLE IF NOT EXISTS `test` (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL
)";

if ($conn->query($sql) === TRUE) {
    echo "Test table created successfully.\n";
} else {
    die("Error creating table: " . $conn->error . "\n");
}

$conn->close();
echo "MySQL setup completed successfully!\n";
