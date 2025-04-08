<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

try {
    // Check if student_profiles table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'student_profiles'");
    $tableExists = $stmt->rowCount() > 0;
    echo "student_profiles table exists: " . ($tableExists ? "Yes" : "No") . "<br>";

    if ($tableExists) {
        // Show table structure
        $stmt = $conn->query("DESCRIBE student_profiles");
        echo "<h3>Table Structure:</h3>";
        echo "<pre>";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";

        // Show all records
        $stmt = $conn->query("SELECT * FROM student_profiles");
        echo "<h3>All Records:</h3>";
        echo "<pre>";
        print_r($stmt->fetchAll(PDO::FETCH_ASSOC));
        echo "</pre>";
    } else {
        // Create the table
        $sql = "CREATE TABLE IF NOT EXISTS student_profiles (
            id INT PRIMARY KEY AUTO_INCREMENT,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            password VARCHAR(255) NOT NULL,
            phone VARCHAR(20),
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        )";
        $conn->exec($sql);
        echo "Created student_profiles table<br>";

        // Insert test user
        $sql = "INSERT INTO student_profiles (first_name, last_name, email, password, phone) VALUES (?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'Test',
            'Student',
            'test@example.com',
            password_hash('password', PASSWORD_DEFAULT),
            '1234567890'
        ]);
        echo "Added test user<br>";
    }

} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
