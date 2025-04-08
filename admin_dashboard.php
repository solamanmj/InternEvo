<?php
session_start();
require_once 'config.php';

// Check if user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Initialize counts
$counts = [
    'pending' => 0,
    'approved' => 0,
    'rejected' => 0
];

// Get counts for each status
try {
    $stmt = $conn->prepare("
        SELECT status, COUNT(*) as count 
        FROM company_profiles 
        GROUP BY status
    ");
    $stmt->execute();
    $statusCounts = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
    foreach ($statusCounts as $row) {
        if (isset($row['status']) && isset($counts[$row['status']])) {
            $counts[$row['status']] = $row['count'];
        }
    }
} catch(PDOException $e) {
    error_log("Error getting counts: " . $e->getMessage());
}

// Handle approval/rejection
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Debug: Print POST data
    error_log("POST Data: " . print_r($_POST, true));
    
    if (isset($_POST['company_id']) && isset($_POST['action'])) {
        $company_id = $_POST['company_id'];
        $action = $_POST['action'];
        
        try {
            // Debug: Print values
            error_log("Processing company_id: $company_id, action: $action");
            
            // Update company status
            $stmt = $conn->prepare("
                UPDATE company_profiles 
                SET status = ? 
                WHERE company_id = ?
            ");
            
            $result = $stmt->execute([$action, $company_id]);
            
            // Debug: Print result
            error_log("Update result: " . ($result ? "Success" : "Failed"));

            if ($result) {
                // Get company email for notification
                $stmt = $conn->prepare("
                    SELECT company_name, company_email 
                    FROM company_profiles 
                    WHERE company_id = ?
                ");
                $stmt->execute([$company_id]);
                $company = $stmt->fetch(PDO::FETCH_ASSOC);

                if ($company) {
                    // Send email notification
                    $to = $company['company_email'];
                    $subject = "Company Registration " . ucfirst($action);
                    $message = "Your company registration has been " . $action;
                    mail($to, $subject, $message);

                    $_SESSION['success'] = "Company status updated to " . $action;
                }
            } else {
                throw new Exception("Failed to update company status");
            }
        } catch (Exception $e) {
            error_log("Error in approval process: " . $e->getMessage());
            $_SESSION['error'] = "Error updating status: " . $e->getMessage();
        }
        
        header("Location: admin_dashboard.php");
        exit();
    }

    // Add this after the existing POST handler (around line 60)
    if (isset($_POST['delete']) && isset($_POST['company_id'])) {
        try {
            $company_id = $_POST['company_id'];
            
            // Start transaction
            $conn->beginTransaction();
            
            // Delete from internship_applications first
            $stmt = $conn->prepare("
                DELETE ia FROM internship_applications ia
                INNER JOIN internships i ON ia.internship_id = i.id
                WHERE i.company_id = ?
            ");
            $stmt->execute([$company_id]);
            
            // Delete from internships table
            $stmt = $conn->prepare("DELETE FROM internships WHERE company_id = ?");
            $stmt->execute([$company_id]);
            
            // Delete from company_profiles table
            $stmt = $conn->prepare("DELETE FROM company_profiles WHERE company_id = ?");
            $stmt->execute([$company_id]);
            
            // Commit transaction
            $conn->commit();
            
            $_SESSION['success'] = "Company and all related data successfully deleted";
        } catch(PDOException $e) {
            // Rollback transaction on error
            $conn->rollBack();
            error_log("Error deleting company: " . $e->getMessage());
            $_SESSION['error'] = "Error deleting company: " . $e->getMessage();
        }
        
        header("Location: admin_dashboard.php");
        exit();
    }
}

// Fetch pending companies
try {
    $stmt = $conn->prepare("
        SELECT * FROM company_profiles 
        ORDER BY 
            CASE status
                WHEN 'pending' THEN 1
                WHEN 'approved' THEN 2
                WHEN 'rejected' THEN 3
            END,
            created_at DESC
    ");
    $stmt->execute();
    $companies = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Error fetching companies: " . $e->getMessage());
    $_SESSION['error'] = "Error fetching companies";
    $companies = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .company-card {
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .company-logo {
            max-width: 100px;
            height: auto;
        }
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
            transform: translateY(-2px);
        }
        
        .d-flex.gap-2 {
            gap: 0.5rem !important;
        }
        
        .flex-column {
            flex-direction: column !important;
        }
        
        .w-100 {
            width: 100% !important;
        }

        /* Add these new styles to your existing style section */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 250px;
            background: #2c3e50;
            color: white;
            padding-top: 60px;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .main-content {
            margin-left: 250px;
            transition: all 0.3s ease;
        }

        .user-profile {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #4e73df;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            padding: 12px 20px !important;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white !important;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: #4e73df;
            color: white !important;
        }

        .navbar {
            margin-left: 250px;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            .sidebar.active {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .navbar {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="bg-light">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="user-profile">
            <div class="user-avatar">
                <i class="fas fa-user-shield fa-2x"></i>
            </div>
            <h6 class="mb-1">Administrator</h6>
            <p class="small mb-0">System Admin</p>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link active" href="admin_dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="admin_students.php">
                    <i class="fas fa-user-graduate"></i>
                    Students
                </a>
            </li>
            <li class="nav-item mt-auto">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Top Navbar -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <button class="btn btn-link text-white d-md-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand">InternEvo Admin</a>
            <div>
                <span class="text-white me-3">
                    Pending: <?php echo $counts['pending']; ?> |
                    Approved: <?php echo $counts['approved']; ?> |
                    Rejected: <?php echo $counts['rejected']; ?>
                </span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid py-4">
            <h2 class="mb-4">Company Management</h2>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <?php foreach ($companies as $company): ?>
                <div class="card company-card mb-3">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-2">
                                <?php if ($company['logo_url']): ?>
                                    <img src="<?php echo htmlspecialchars($company['logo_url']); ?>" 
                                         class="img-fluid company-logo" alt="Company Logo">
                                <?php else: ?>
                                    <i class="fas fa-building fa-4x text-secondary"></i>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-7">
                                <h4><?php echo htmlspecialchars($company['company_name']); ?></h4>
                                <p class="mb-1"><strong>Status:</strong> 
                                    <span class="badge bg-<?php 
                                        echo $company['status'] === 'approved' ? 'success' : 
                                            ($company['status'] === 'pending' ? 'warning' : 'danger'); 
                                    ?>">
                                        <?php echo ucfirst($company['status']); ?>
                                    </span>
                                </p>
                                <p class="mb-1"><strong>Industry:</strong> 
                                    <?php echo htmlspecialchars($company['category_name'] ?? 'N/A'); ?>
                                </p>
                                <p class="mb-1"><strong>Email:</strong> 
                                    <?php echo htmlspecialchars($company['company_email']); ?>
                                </p>
                                <p class="mb-0"><small class="text-muted">
                                    Registered: <?php echo date('F j, Y', strtotime($company['created_at'])); ?>
                                </small></p>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex gap-2 flex-column">
                                    <?php if ($company['status'] === 'pending'): ?>
                                        <form method="POST" class="d-flex gap-2">
                                            <input type="hidden" name="company_id" value="<?php echo $company['company_id']; ?>">
                                            <button type="submit" name="action" value="approved" class="btn btn-success">
                                                <i class="fas fa-check"></i> Approve
                                            </button>
                                            <button type="submit" name="action" value="rejected" class="btn btn-danger">
                                                <i class="fas fa-times"></i> Reject
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                    <!-- Add delete form -->
                                    <form method="POST" onsubmit="return confirmDelete()">
                                        <input type="hidden" name="company_id" value="<?php echo $company['company_id']; ?>">
                                        <button type="submit" name="delete" class="btn btn-danger w-100">
                                            <i class="fas fa-trash"></i> Delete Company
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>

            <?php if (empty($companies)): ?>
                <div class="alert alert-info">
                    No companies registered at this time.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmDelete() {
        return confirm('Warning: This will permanently delete the company and all associated data including internships and applications. This action cannot be undone. Are you sure you want to proceed?');
    }

    // Add this to your existing scripts
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Active link highlighting
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.href === window.location.href) {
            link.classList.add('active');
        }
    });
    </script>
</body>
</html> 