<?php
require_once 'config.php';

try {
    // First, let's see what's in the admins table
    $stmt = $conn->query("SELECT * FROM admins");
    $admins = $stmt->fetchAll();
    
    echo "Current admins in database:<br>";
    echo "<pre>";
    print_r($admins);
    echo "</pre><br>";

    // Now let's create a new admin with a secure password
    $new_username = 'superadmin';
    $new_password = 'Admin@123'; // Strong password
    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO admins (username, password) VALUES (?, ?)");
    $stmt->execute([$new_username, $hashed_password]);
    
    echo "New admin created successfully!<br>";
    echo "Username: " . $new_username . "<br>";
    echo "Password: " . $new_password . "<br>";
    echo "<br>Please save these credentials and delete this file!";
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?> 