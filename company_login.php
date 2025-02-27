<?php
session_start();
require_once 'config.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        // Basic validation
        if (empty($email) || empty($password)) {
            throw new Exception("Please enter both email and password");
        }

        // Get company details
        $stmt = $conn->prepare("SELECT * FROM company_profiles WHERE company_email = ?");
        $stmt->execute([$email]);
        $company = $stmt->fetch(PDO::FETCH_ASSOC);

        // Verify company exists and password is correct
        if (!$company || !password_verify($password, $company['password'])) {
            throw new Exception("Invalid email or password");
        }

        // Check company status
        if ($company['status'] === 'pending') {
            throw new Exception("Your account is pending approval. Please wait for admin verification.");
        } elseif ($company['status'] === 'rejected') {
            throw new Exception("Your account has been rejected. Please contact admin for more information.");
        } elseif ($company['status'] !== 'approved') {
            throw new Exception("Account status issue. Please contact admin.");
        }

        // Set session variables for approved companies
        $_SESSION['company_id'] = $company['company_id'];
        $_SESSION['company_name'] = $company['company_name'];
        $_SESSION['company_email'] = $company['company_email'];
        $_SESSION['company_phone'] = $company['company_phone'];
        $_SESSION['company_address'] = $company['company_address'];
        $_SESSION['company_website'] = $company['company_website'];
        $_SESSION['company_description'] = $company['company_description'];
        $_SESSION['company_size'] = $company['company_size'];
        $_SESSION['logo_url'] = $company['logo_url'];
        $_SESSION['industry_id'] = $company['industry_id'];
        $_SESSION['user_type'] = 'company';
        $_SESSION['logged_in'] = true;

        // Redirect to dashboard
        header("Location: company_dashboard.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Login - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(
                135deg, 
                #1a1c23 0%, 
                #242730 50%,
                #1a1c23 100%
            );
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        .login-container {
            background: rgba(36, 39, 48, 0.9);
            border-radius: 20px;
            padding: 35px;
            box-shadow: 0 8px 32px 0 rgba(31, 38, 135, 0.37);
            backdrop-filter: blur(4px);
            border: 1px solid rgba(255, 255, 255, 0.18);
            width: 100%;
            max-width: 400px;
            color: #fff;
        }
        .form-group {
            margin-bottom: 25px;
        }
        .form-control {
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 10px;
            color: #fff;
            padding: 12px 15px;
            transition: all 0.3s ease;
        }
        .form-control:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: #4e73df;
            box-shadow: 0 0 15px rgba(78, 115, 223, 0.3);
            color: #fff;
        }
        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }
        label {
            color: #fff;
            margin-bottom: 8px;
            font-weight: 500;
            display: block;
        }
        .alert {
            border-radius: 10px;
            margin-bottom: 20px;
            background: rgba(255, 255, 255, 0.1);
            border: none;
            color: #fff;
        }
        .alert-danger {
            background: rgba(220, 53, 69, 0.2);
            border-left: 4px solid #dc3545;
        }
        .alert-success {
            background: rgba(40, 167, 69, 0.2);
            border-left: 4px solid #28a745;
        }
        button {
            background: linear-gradient(
                135deg, 
                #4e73df 0%, 
                #224abe 100%
            );
            border: none;
            color: white;
            padding: 14px;
            border-radius: 10px;
            width: 100%;
            font-weight: 600;
            margin-top: 10px;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        button:hover {
            background: linear-gradient(
                135deg, 
                #224abe 0%, 
                #4e73df 100%
            );
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
        }
        .register-link {
            text-align: center;
            margin-top: 25px;
            color: rgba(255, 255, 255, 0.7);
        }
        .register-link a, .text-center a {
            color: #4e73df;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        .register-link a:hover, .text-center a:hover {
            color: #224abe;
            text-decoration: underline;
        }
        h2 {
            color: #fff;
            margin-bottom: 30px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            text-align: center;
            background: linear-gradient(135deg, #4e73df, #224abe);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }
        .text-center {
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h2 class="text-center mb-4">Company Login</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success">
                <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST">
            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="email" class="form-control" required>
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" class="form-control" required>
            </div>

            <button type="submit">Login</button>
        </form>

        <div class="register-link">
            Don't have an account? <a href="register_company.php">Register here</a>
        </div>

        <div class="text-center mt-3">
            <a href="check_status.php">Check Application Status</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 