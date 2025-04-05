<?php

define('DB_HOST', 'localhost');     
define('DB_NAME', 'grocery_shop');  
define('DB_USER', 'root');           
define('DB_PASS', '');               
define('DB_CHARSET', 'utf8mb4');  

error_reporting(E_ALL);
ini_set('display_errors', '1');

try {
    $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
    
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,  
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,        
        PDO::ATTR_EMULATE_PREPARES   => false,                 
        PDO::ATTR_PERSISTENT         => true                     
    ];
    
    $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
    
    $pdo->setAttribute(PDO::ATTR_STRINGIFY_FETCHES, false);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

} catch (PDOException $e) {
    error_log("Database Connection Error: " . $e->getMessage());
    
    die("Could not connect to the database. Please try again later.");
}

function closeDBConnection() {
    global $pdo;
    $pdo = null;
}

function dbQuery($sql, $params = []) {
    global $pdo;
    try {
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    } catch (PDOException $e) {
        error_log("Database Query Error: " . $e->getMessage() . " - Query: " . $sql);
        return false;
    }
}

function debugPDOPrepare($sql, $params) {
    global $pdo;
    $stmt = $pdo->prepare($sql);
    $stmt->execute($params);
    $stmt->debugDumpParams();
}
?>