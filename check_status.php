<?php
session_start();
require_once 'config.php';

$status = '';
$message = '';
$company_details = null;

// Auto-fill email if just registered
$registered_email = isset($_SESSION['registered_email']) ? $_SESSION['registered_email'] : '';
unset($_SESSION['registered_email']); // Clear it after use

// Automatically check status if just registered
if (isset($_SESSION['registration_success'])) {
    $_POST['email'] = $registered_email;
    $_SERVER["REQUEST_METHOD"] = "POST";
}

if ($_SERVER["REQUEST_METHOD"] == "POST" || isset($_SESSION['registration_success'])) {
    $email = filter_var($_POST['email'] ?? $registered_email, FILTER_SANITIZE_EMAIL);

    try {
        $stmt = $conn->prepare("SELECT * FROM company_profiles WHERE company_email = ?");
        $stmt->execute([$email]);
        $company = $stmt->fetch();

        if ($company) {
            $status = $company['status'];
            $company_details = $company;
            
            switch($status) {
                case 'pending':
                    $message = isset($_SESSION['registration_success']) ? 
                        "Thank you for registering! Your application is under review. We will notify you once it's approved." :
                        "Your application is still under review. Please check back later.";
                    break;
                case 'approved':
                    $message = "Congratulations! Your company has been approved. You can now login to your account.";
                    break;
                case 'rejected':
                    $message = "We regret to inform you that your application has been rejected. Please contact support for more information.";
                    break;
            }
        } else {
            $message = "No application found with this email address.";
        }
    } catch(PDOException $e) {
        $message = "Error checking status: " . $e->getMessage();
    }
}

// Clear the registration success message after displaying
$registration_success = isset($_SESSION['registration_success']) ? $_SESSION['registration_success'] : '';
unset($_SESSION['registration_success']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Check Application Status - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .status-container {
            max-width: 500px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .card-header {
            background: linear-gradient(135deg, #4e73df 0%, #2e59d9 100%);
            color: white;
            border-radius: 15px 15px 0 0 !important;
            padding: 20px;
        }
        .status-badge {
            font-size: 1.2rem;
            padding: 10px 20px;
            border-radius: 25px;
            text-transform: uppercase;
        }
        .status-pending {
            background-color: #f6c23e;
            color: #fff;
        }
        .status-approved {
            background-color: #1cc88a;
            color: #fff;
        }
        .status-rejected {
            background-color: #e74a3b;
            color: #fff;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="status-container">
            <div class="card">
                <div class="card-header text-center">
                    <h4 class="mb-0">
                        <i class="fas fa-clipboard-check me-2"></i>
                        <?php echo $registration_success ? 'Registration Status' : 'Check Application Status'; ?>
                    </h4>
                </div>
                <div class="card-body">
                    <?php if ($registration_success): ?>
                        <div class="alert alert-success mb-4">
                            <?php echo $registration_success; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (!$registration_success): ?>
                        <form method="POST" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>" class="mb-4">
                            <div class="mb-3">
                                <label for="email" class="form-label">Company Email</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?php echo htmlspecialchars($registered_email); ?>" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Check Status</button>
                        </form>
                    <?php endif; ?>

                    <?php if ($message): ?>
                        <div class="text-center mb-4">
                            <?php if ($status): ?>
                                <span class="status-badge status-<?php echo $status; ?>">
                                    <?php echo ucfirst($status); ?>
                                </span>
                            <?php endif; ?>
                            
                            <div class="alert <?php 
                                echo $status === 'approved' ? 'alert-success' : 
                                    ($status === 'rejected' ? 'alert-danger' : 'alert-warning'); 
                            ?> mt-3">
                                <?php echo $message; ?>
                            </div>

                            <?php if ($company_details && $status === 'approved'): ?>
                                <a href="company_login.php" class="btn btn-success mt-3">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login Now
                                </a>
                            <?php endif; ?>
                        </div>
                    <?php endif; ?>

                    <div class="text-center mt-3">
                        <p>Back to <a href="company_login.php">Company Login</a></p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 