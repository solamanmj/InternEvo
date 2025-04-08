<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

echo "<h2>Database Connection Test</h2>";

try {
    // Test database connection
    echo "Database connection: OK<br>";
    
    // Check if students table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'students'");
    $hasStudentsTable = $stmt->rowCount() > 0;
    echo "Students table exists: " . ($hasStudentsTable ? "Yes" : "No") . "<br>";
    
    if ($hasStudentsTable) {
        // Show table structure
        $stmt = $conn->query("DESCRIBE students");
        echo "<h3>Table Structure:</h3>";
        echo "<pre>";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
        
        // Count records
        $stmt = $conn->query("SELECT COUNT(*) FROM students");
        echo "Number of students: " . $stmt->fetchColumn() . "<br>";
        
        // Show all records
        $stmt = $conn->query("SELECT * FROM students");
        echo "<h3>Student Records:</h3>";
        echo "<pre>";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
        
        // Test login with test@example.com
        $email = 'test@example.com';
        $password = 'password';
        
        $stmt = $conn->prepare("SELECT * FROM students WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        echo "<h3>Login Test:</h3>";
        echo "Found user with email $email: " . ($user ? "Yes" : "No") . "<br>";
        if ($user) {
            echo "Password verification: " . (password_verify($password, $user['password']) ? "Success" : "Failed") . "<br>";
            echo "User data:<br>";
            echo "<pre>";
            print_r($user);
            echo "</pre>";
        }
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
