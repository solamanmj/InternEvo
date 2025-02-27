<?php
session_start();
require_once 'config.php';

// Check if we have company email in session
if (!isset($_SESSION['pending_email'])) {
    header("Location: company_login.php");
    exit();
}

// Check current status
try {
    $stmt = $conn->prepare("SELECT status FROM company_profiles WHERE company_email = ?");
    $stmt->execute([$_SESSION['pending_email']]);
    $result = $stmt->fetch();
    $current_status = $result ? $result['status'] : 'unknown';
} catch(PDOException $e) {
    $current_status = 'error';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pending Approval - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }
        .pending-container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
        }
        .card-header {
            background: linear-gradient(135deg, #4e73df 0%, #2e59d9 100%);
            color: white;
            padding: 20px;
            text-align: center;
        }
        .status-icon {
            font-size: 4rem;
            color: #f6c23e;
            margin: 20px 0;
        }
        .progress {
            height: 10px;
            border-radius: 5px;
        }
        .progress-bar {
            background: linear-gradient(135deg, #4e73df 0%, #2e59d9 100%);
            animation: progress-animation 2s infinite;
        }
        @keyframes progress-animation {
            0% { width: 10%; }
            50% { width: 50%; }
            100% { width: 10%; }
        }
        .refresh-btn {
            background: linear-gradient(135deg, #4e73df 0%, #2e59d9 100%);
            border: none;
            padding: 10px 30px;
            border-radius: 25px;
            color: white;
            transition: transform 0.3s ease;
        }
        .refresh-btn:hover {
            transform: scale(1.05);
            color: white;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="pending-container">
            <div class="card">
                <div class="card-header">
                    <h4 class="mb-0">Application Status</h4>
                </div>
                <div class="card-body text-center">
                    <i class="fas fa-clock status-icon"></i>
                    <h3 class="mb-4">Pending Approval</h3>
                    
                    <div class="progress mb-4">
                        <div class="progress-bar" role="progressbar"></div>
                    </div>

                    <p class="lead mb-4">
                        Your application is currently under review. Our team will verify your information and update your status soon.
                    </p>

                    <div class="alert alert-info">
                        <i class="fas fa-info-circle me-2"></i>
                        Current Status: <?php echo ucfirst($current_status); ?>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="check_status.php" class="refresh-btn btn">
                            <i class="fas fa-sync-alt me-2"></i>Check Current Status
                        </a>
                        <a href="company_login.php" class="btn btn-outline-secondary">
                            <i class="fas fa-arrow-left me-2"></i>Back to Login
                        </a>
                    </div>

                    <div class="mt-4">
                        <small class="text-muted">
                            <i class="fas fa-envelope me-2"></i>
                            We'll notify you at: <?php echo htmlspecialchars($_SESSION['pending_email']); ?>
                        </small>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 