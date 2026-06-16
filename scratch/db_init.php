<?php
$host = '127.0.0.1';
$user = 'root';
$pass = '';

try {
    // Connect without selecting DB first
    $pdo = new PDO("mysql:host=$host;charset=utf8mb4", $user, $pass, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    
    // Read SQL file
    $sqlFile = __DIR__ . '/../database.sql';
    if (!file_exists($sqlFile)) {
        die("Error: database.sql not found at " . $sqlFile . "\n");
    }
    
    $sql = file_get_contents($sqlFile);
    
    // Execute SQL queries
    $pdo->exec($sql);
    echo "Database and tables initialized successfully.\n";
    
} catch (\PDOException $e) {
    echo "Database initialization error: " . $e->getMessage() . "\n";
    exit(1);
}
?>
