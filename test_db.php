<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'config.php';

try {
    // Test database connection
    echo "<h3>Testing Database Connection</h3>";
    $test = $conn->query("SELECT 1");
    echo "✓ Database connection successful<br><br>";

    // Check if students table exists
    echo "<h3>Checking Tables</h3>";
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "Found tables: " . implode(', ', $tables) . "<br><br>";

    // Check students table structure
    if (in_array('students', $tables)) {
        echo "<h3>Students Table Structure</h3>";
        $columns = $conn->query("DESCRIBE students")->fetchAll(PDO::FETCH_ASSOC);
        echo "<pre>";
        print_r($columns);
        echo "</pre><br>";

        // Check if any students exist
        echo "<h3>Existing Students</h3>";
        $students = $conn->query("SELECT id, first_name, last_name, email FROM students")->fetchAll(PDO::FETCH_ASSOC);
        if ($students) {
            echo "<pre>";
            print_r($students);
            echo "</pre>";
        } else {
            echo "No students found in the database.<br>";
        }
    } else {
        echo "Students table does not exist!<br>";
        
        // Create the table
        echo "<h3>Creating Students Table</h3>";
        $sql = "CREATE TABLE IF NOT EXISTS students (
            id INT PRIMARY KEY AUTO_INCREMENT,
            first_name VARCHAR(50) NOT NULL,
            last_name VARCHAR(50) NOT NULL,
            email VARCHAR(100) NOT NULL UNIQUE,
            phone VARCHAR(20),
            password VARCHAR(255) NOT NULL,
            education TEXT,
            skills TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
        
        $conn->exec($sql);
        echo "✓ Students table created successfully<br>";
        
        // Insert test student
        $hash = password_hash('password', PASSWORD_DEFAULT);
        $sql = "INSERT INTO students (first_name, last_name, email, phone, password, education, skills) 
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->execute([
            'Test',
            'Student',
            'test@example.com',
            '1234567890',
            $hash,
            'Bachelor of Technology in Computer Science',
            'Programming, Web Development'
        ]);
        echo "✓ Test student account created<br>";
    }

} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
