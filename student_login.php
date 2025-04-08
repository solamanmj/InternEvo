<?php
session_start();
require_once 'config.php';

// Enable error reporting for debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $email = trim($_POST['email']);
        $password = $_POST['password'];
        
        error_log("Login attempt - Email: " . $email);

        // Find user in student_profiles table
        $stmt = $conn->prepare("SELECT * FROM student_profiles WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();
        
        error_log("User found: " . ($user ? "Yes" : "No"));
        if ($user) {
            error_log("Password verification: " . (password_verify($password, $user['password']) ? "Success" : "Failed"));
        }

        if ($user && password_verify($password, $user['password'])) {
            // Set all necessary session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_type'] = 'student';
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['email'] = $user['email'];
            
            error_log("Login successful for user: " . $user['email']);
            error_log("Session variables: " . print_r($_SESSION, true));
            
            // Redirect to student dashboard
            header("Location: student_dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password";
            error_log("Login failed for email: " . $email);
        }
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred during login. Please try again.";
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Login - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .login-container {
            background: white;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2rem;
            max-width: 400px;
            width: 100%;
        }
        .login-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        .login-header h1 {
            color: #0d6efd;
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        .form-floating {
            margin-bottom: 1rem;
        }
        .error-message {
            color: #dc3545;
            margin-bottom: 1rem;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="login-container">
                    <div class="login-header">
                        <h1>InternEvo</h1>
                        <p class="text-muted">Student Login</p>
                    </div>
                    
                    <?php if (isset($_SESSION['error'])): ?>
                        <div class="error-message">
                            <?php 
                            echo htmlspecialchars($_SESSION['error']);
                            unset($_SESSION['error']);
                            ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="student_login.php">
                        <div class="form-floating mb-3">
                            <input type="email" class="form-control" id="email" name="email" placeholder="name@example.com" required>
                            <label for="email">Email address</label>
                        </div>
                        <div class="form-floating mb-3">
                            <input type="password" class="form-control" id="password" name="password" placeholder="Password" required>
                            <label for="password">Password</label>
                        </div>
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Login</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
