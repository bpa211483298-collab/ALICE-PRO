<?php
// Check if SQLite extension is loaded
if (!extension_loaded('pdo_sqlite')) {
    echo "SQLite PDO extension is NOT loaded.\n";
    echo "Loaded extensions: " . implode(", ", get_loaded_extensions()) . "\n";
    
    // Try to load the extension dynamically
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        if (PHP_MAJOR_VERSION >= 8) {
            dl('php_pdo_sqlite.dll');
        } else {
            dl('pdo_sqlite.dll');
        }
    } else {
        dl('pdo_sqlite.so');
    }
    
    // Check again after trying to load
    if (!extension_loaded('pdo_sqlite')) {
        echo "Failed to load SQLite PDO extension.\n";
        echo "Please enable the following in your php.ini file:\n";
        echo "extension=pdo_sqlite\n";
        echo "extension=sqlite3\n";
        exit(1);
    } else {
        echo "SQLite PDO extension was loaded dynamically!\n";
    }
} else {
    echo "SQLite PDO extension is loaded.\n";
}

// Test SQLite connection
try {
    $db = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    echo "Successfully connected to SQLite database.\n";
    
    // Try to create a test table
    $db->exec("CREATE TABLE IF NOT EXISTS test (id INTEGER PRIMARY KEY, name TEXT)");
    echo "Test table created successfully.\n";
    
} catch (PDOException $e) {
    echo "SQLite Error: " . $e->getMessage() . "\n";
    
    // Check if database directory is writable
    $dbDir = __DIR__ . '/database';
    if (!is_writable($dbDir)) {
        echo "Database directory is not writable: $dbDir\n";
        echo "Please ensure the directory exists and is writable by the web server.\n";
    }
    
    // Check if we can create the database file
    $dbFile = $dbDir . '/database.sqlite';
    if (!file_exists($dbFile)) {
        echo "Database file does not exist. Attempting to create it...\n";
        if (touch($dbFile)) {
            echo "Database file created successfully at: $dbFile\n";
            echo "Please try running the migrations again.\n";
        } else {
            echo "Failed to create database file at: $dbFile\n";
            echo "Please create this file manually and make it writable.\n";
        }
    }
    
    exit(1);
}

echo "\nSQLite setup check completed successfully!\n";
