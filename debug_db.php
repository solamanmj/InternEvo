<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

try {
    echo "<h2>Database Connection Test</h2>";
    
    // Test database connection
    echo "Connected to database: " . $conn->getAttribute(PDO::ATTR_CONNECTION_STATUS) . "<br>";
    
    // List all tables
    $stmt = $conn->query("SHOW TABLES");
    echo "<h3>Tables in database:</h3>";
    while ($row = $stmt->fetch()) {
        echo "- " . $row[0] . "<br>";
    }
    
    // Check student_profiles table
    $stmt = $conn->query("SELECT * FROM student_profiles");
    echo "<h3>Contents of student_profiles table:</h3>";
    echo "<pre>";
    while ($row = $stmt->fetch()) {
        print_r($row);
    }
    echo "</pre>";
    
    // Try the exact login query
    $email = 'test@example.com';
    $stmt = $conn->prepare("SELECT * FROM student_profiles WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();
    
    echo "<h3>Test login query for test@example.com:</h3>";
    if ($user) {
        echo "User found!<br>";
        echo "Password hash in database: " . $user['password'] . "<br>";
        echo "Test password verification: " . (password_verify('password', $user['password']) ? "Success" : "Failed") . "<br>";
    } else {
        echo "No user found with email test@example.com<br>";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
