<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $email = trim($_POST['email']);
        
        // First check student_profiles with correct column names
        $stmt = $conn->prepare("
            SELECT 
                id,
                user_id,
                CONCAT(first_name, ' ', last_name) as user_name,
                email,
                'student' as user_type 
            FROM student_profiles 
            WHERE email = ?
        ");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        // If not found in student_profiles, check company_profiles
        if (!$user) {
            $stmt = $conn->prepare("
                SELECT 
                    company_id as id,
                    company_id as user_id,
                    company_name as user_name,
                    company_email as email,
                    'company' as user_type
                FROM company_profiles 
                WHERE company_email = ?
            ");
            $stmt->execute([$email]);
            $user = $stmt->fetch();
        }

        if ($user) {
            // Generate unique token
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));

            // Store reset token
            $stmt = $conn->prepare("
                INSERT INTO password_resets (user_id, token, expiry)
                VALUES (?, ?, ?)
            ");
            $stmt->execute([$user['user_id'], $token, $expiry]);

            // Send reset email
            $reset_link = "http://" . $_SERVER['HTTP_HOST'] . 
                         "/reset_password.php?token=" . $token;

            $to = $email;
            $subject = "Password Reset - InternEvo";
            $message = "Hi " . htmlspecialchars($user['user_name']) . ",\n\n";
            $message .= "Click the link below to reset your password:\n";
            $message .= $reset_link . "\n\n";
            $message .= "This link will expire in 1 hour.\n\n";
            $message .= "If you didn't request this, please ignore this email.\n";
            
            mail($to, $subject, $message);

            $_SESSION['success'] = "Password reset instructions have been sent to your email.";
        } else {
            // Generic message for security
            $_SESSION['success'] = "If this email exists in our system, you will receive reset instructions.";
        }
    } catch (Exception $e) {
        $_SESSION['error'] = "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - InternEvo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Use same styling as login.php for consistency */
        :root {
            --primary: #4e73df;
            --secondary: #2e59d9;
            --accent: #00ff88;
            --dark: #1a1a1a;
            --light: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: var(--dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
        }

        .reset-container {
            width: 400px;
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(20px);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 0 40px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
            color: var(--light);
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
        }

        .input-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: rgba(255,255,255,0.05);
            border: none;
            border-radius: 10px;
            color: var(--light);
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent);
            font-size: 20px;
        }

        .reset-btn {
            width: 100%;
            padding: 15px;
            background: var(--accent);
            border: none;
            border-radius: 10px;
            color: var(--dark);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .reset-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,255,136,0.4);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            color: var(--light);
        }

        .alert-success {
            background: rgba(46,204,113,0.2);
            border: 1px solid rgba(46,204,113,0.3);
        }

        .alert-error {
            background: rgba(231,76,60,0.2);
            border: 1px solid rgba(231,76,60,0.3);
        }

        .back-to-login {
            text-align: center;
            margin-top: 20px;
        }

        .back-to-login a {
            color: var(--accent);
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="reset-container">
        <div class="logo">
            <i class="fas fa-lock fa-3x"></i>
            <h2>Reset Password</h2>
        </div>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-error">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Enter your email" required>
            </div>

            <button type="submit" class="reset-btn">
                <i class="fas fa-paper-plane"></i> Send Reset Link
            </button>
        </form>

        <div class="back-to-login">
            <a href="login.php">Back to Login</a>
        </div>
    </div>
</body>
</html> 