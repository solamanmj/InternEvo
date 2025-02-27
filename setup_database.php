<?php
require_once 'config.php';

try {
    // Drop existing tables to ensure clean setup
    $conn->exec("DROP TABLE IF EXISTS student_profiles");
    $conn->exec("DROP TABLE IF EXISTS degrees");
    
    echo "Dropped existing tables...<br>";
    
    // Create database if not exists
    $conn->exec("CREATE DATABASE IF NOT EXISTS internevo");
    $conn->exec("USE internevo");
    
    echo "Selected database...<br>";
    
    // Create degrees table
    $sql = "CREATE TABLE IF NOT EXISTS degrees (
        id INT AUTO_INCREMENT PRIMARY KEY,
        degree_name VARCHAR(100) NOT NULL UNIQUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);
    
    echo "Created degrees table...<br>";
    
    // Insert some sample degrees
    $degrees = [
        'Bachelor of Technology',
        'Bachelor of Engineering',
        'Bachelor of Science',
        'Master of Technology',
        'Master of Science'
    ];
    
    $stmt = $conn->prepare("INSERT INTO degrees (degree_name) VALUES (?)");
    foreach ($degrees as $degree) {
        $stmt->execute([$degree]);
    }
    
    echo "Inserted sample degrees...<br>";
    
    // Create student_profiles table with updated structure
    $sql = "CREATE TABLE IF NOT EXISTS student_profiles (
        id INT AUTO_INCREMENT PRIMARY KEY,
        first_name VARCHAR(50) NOT NULL,
        last_name VARCHAR(50) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL,
        degree_id INT,
        institution_name VARCHAR(100),
        phone_number VARCHAR(20),
        date_of_birth DATE,
        gender VARCHAR(10),
        profile_picture VARCHAR(255),
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (degree_id) REFERENCES degrees(id)
    )";
    
    $conn->exec($sql);
    
    echo "Created student_profiles table...<br>";
    
    // Verify table structure
    $stmt = $conn->query("DESCRIBE student_profiles");
    echo "<br>Student Profiles Table Structure:<br>";
    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        echo $row['Field'] . " - " . $row['Type'] . "<br>";
    }
    
    // Insert a test student account
    $password_hash = password_hash('test123', PASSWORD_DEFAULT);
    
    $stmt = $conn->prepare("INSERT INTO student_profiles (first_name, last_name, email, password, degree_id, institution_name) 
                           VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Test', 'Student', 'test@example.com', $password_hash, 1, 'Test University']);
    
    echo "<br>Test account created successfully!<br>";
    echo "Email: test@example.com<br>";
    echo "Password: test123<br>";
    
} catch(PDOException $e) {
    echo "<br>Error: " . $e->getMessage() . "<br>";
    echo "Error Code: " . $e->getCode() . "<br>";
}
?>
