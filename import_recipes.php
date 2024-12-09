<?php

// Path to your SQLite database
$dbPath = __DIR__ . '/var/data.db';

// Path to your SQL file
$sqlFile = __DIR__ . '/recipe.sql';

try {
    // Connect to SQLite database
    $pdo = new PDO('sqlite:' . $dbPath);

    // Read the SQL file
    $sql = file_get_contents($sqlFile);

    if ($sql === false) {
        throw new Exception("Failed to read SQL file.");
    }

    // Execute the SQL commands
    $pdo->exec($sql);

    echo "Data imported successfully!\n";

} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}