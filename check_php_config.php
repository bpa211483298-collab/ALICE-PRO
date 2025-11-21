<?php
// Check if SQLite3 extension is loaded
if (!extension_loaded('sqlite3')) {
    echo "SQLite3 extension is NOT loaded.\n";
    
    // Try to load it manually
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        if (PHP_MAJOR_VERSION >= 8) {
            dl('php_sqlite3.dll');
        } else {
            dl('sqlite3.dll');
        }
    } else {
        dl('sqlite3.so');
    }
    
    // Check again
    if (!extension_loaded('sqlite3')) {
        echo "Failed to load SQLite3 extension.\n";
        echo "PHP is looking for extensions in: " . ini_get('extension_dir') . "\n";
        echo "Please check that the extension files exist and are readable.\n";
    } else {
        echo "SQLite3 extension was loaded dynamically!\n";
    }
} else {
    echo "SQLite3 extension is loaded.\n";
}

// List all loaded extensions
echo "\nLoaded extensions:\n";
$extensions = get_loaded_extensions();
sort($extensions);
echo implode(", ", $extensions) . "\n";

// Check if we can use SQLite3
if (class_exists('SQLite3')) {
    echo "\nSQLite3 class is available.\n";
    
    try {
        $db = new SQLite3(':memory:');
        $db->exec('CREATE TABLE test (id INTEGER PRIMARY KEY, name TEXT)');
        $db->exec("INSERT INTO test (name) VALUES ('test')");
        
        $result = $db->query('SELECT * FROM test');
        while ($row = $result->fetchArray(SQLITE3_ASSOC)) {
            echo "Test query result: " . print_r($row, true) . "\n";
        }
        
        echo "SQLite3 in-memory test successful!\n";
    } catch (Exception $e) {
        echo "SQLite3 error: " . $e->getMessage() . "\n";
    }
} else {
    echo "\nSQLite3 class is NOT available.\n";
    
    // Check extension directory
    $extDir = ini_get('extension_dir');
    echo "Extension directory: $extDir\n";
    
    // Check if the extension file exists
    $extFile = 'php_sqlite3.dll';
    if (file_exists("$extDir/$extFile")) {
        echo "Extension file $extFile exists in $extDir\n";
    } else {
        echo "Extension file $extFile NOT found in $extDir\n";
    }
}

// Check PHP version and architecture
echo "\nPHP Version: " . PHP_VERSION . "\n";
echo "PHP Architecture: " . (PHP_INT_SIZE * 8) . "-bit\n";
echo "System: " . PHP_OS . "\n";

// Check SQLite3 version
if (function_exists('sqlite_libversion')) {
    echo "SQLite Library Version: " . sqlite_libversion() . "\n";
} elseif (class_exists('SQLite3')) {
    echo "SQLite3 Library Version: " . SQLite3::version()['versionString'] . "\n";
} else {
    echo "Could not determine SQLite version.\n";
}
