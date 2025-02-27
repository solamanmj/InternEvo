<?php
session_start();
include 'conn.php';  // Assuming conn.php handles your PDO connection.

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $firstname = filter_var($_POST['firstname'], FILTER_SANITIZE_STRING);
    $lastname = filter_var($_POST['lastname'], FILTER_SANITIZE_STRING);
    $email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'];

    try {
        // Assuming $conn is your PDO connection
        // Check if email already exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);

        // Correct way to get the row count with PDO
        if ($stmt->rowCount() > 0) {
            $_SESSION['error'] = "Email already exists";
            header("Location: /#contact");
            exit();
        }

        // Hash the password
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        // Insert the new user into the database
        $stmt = $conn->prepare("INSERT INTO users (firstname, lastname, email, password) VALUES (?, ?, ?, ?)");
        $stmt->execute([$firstname, $lastname, $email, $password_hash]);

        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: /#contact");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Registration failed. Please try again.";
        header("Location: /#contact");
        exit();
    }
}
?>
