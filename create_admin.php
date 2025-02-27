<?php
require_once 'config.php';

try {
    $username = 'admin';
    $password = 'admin123'; // You can change this password
    
    // First check if admin already exists
    $stmt = $conn->prepare("SELECT id FROM admins WHERE username = ?");
    $stmt->execute([$username]);
    
    if ($stmt->rowCount() > 0) {
        echo "Admin user already exists!";
        echo "<br>Please use the existing admin account to login.";
        echo "<br><br>If you forgot the password, contact the system administrator.";
    } else {
        // Create new admin if doesn't exist
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
        $stmt->execute([$username, $hashed_password]);
        
        echo "Admin created successfully!";
        echo "<br>Username: " . $username;
        echo "<br>Password: " . $password;
        echo "<br><br>Please delete this file after creating the admin account.";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 