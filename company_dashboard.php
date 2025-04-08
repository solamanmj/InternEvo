<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);
require_once 'config.php';

// Use company_id from session
$company_id = $_SESSION['company_id'] ?? null;

if (!isset($_SESSION['logged_in']) || !$company_id) {
    header("Location: login.php");
    exit();
}

// Handle application status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id']) && isset($_POST['status'])) {
    try {
        // Validate status
        $allowed_statuses = ['approved', 'rejected'];
        $new_status = $_POST['status'];
        $application_id = $_POST['application_id'];

        if (in_array($new_status, $allowed_statuses)) {
            // First verify this application belongs to the company's internship
            $verify_query = "
                SELECT ia.id 
                FROM internship_applications ia
                JOIN internships i ON ia.internship_id = i.id
                WHERE ia.id = ? AND i.company_id = ?
            ";
            $verify_stmt = $conn->prepare($verify_query);
            $verify_stmt->execute([$application_id, $company_id]);
            
            if ($verify_stmt->rowCount() > 0) {
                // Update the application status
                $update_query = "
                    UPDATE internship_applications 
                    SET status = ?, 
                        updated_at = NOW()
                    WHERE id = ?
                ";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->execute([$new_status, $application_id]);

                if ($update_stmt->rowCount() > 0) {
                    $_SESSION['success'] = "Application successfully " . $new_status;
                } else {
                    $_SESSION['error'] = "Failed to update application status";
                }
            } else {
                $_SESSION['error'] = "Invalid application";
            }
        } else {
            $_SESSION['error'] = "Invalid status";
        }
    } catch(PDOException $e) {
        error_log("Error updating application: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while updating the application";
    }
    
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Get current status filter from URL
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Initialize application count variables with default values
$total_count = 0;
$pending_count = 0;
$approved_count = 0;
$rejected_count = 0;

// Only fetch application counts if a company is logged in
if (isset($_SESSION['company_id'])) {
    $company_id = $_SESSION['company_id'];
    
    try {
        $count_query = "
            SELECT ia.status, COUNT(*) as count
            FROM internship_applications ia
            JOIN internships i ON ia.internship_id = i.id
            WHERE i.company_id = :company_id
            GROUP BY ia.status
        ";
        $count_stmt = $conn->prepare($count_query);
        $count_stmt->execute(['company_id' => $company_id]);
        $status_counts = array_column($count_stmt->fetchAll(PDO::FETCH_ASSOC), 'count', 'status');
        
        // Set default counts if not present
        $pending_count = $status_counts['pending'] ?? 0;
        $approved_count = $status_counts['approved'] ?? 0;
        $rejected_count = $status_counts['rejected'] ?? 0;
        $total_count = $pending_count + $approved_count + $rejected_count;
    } catch(PDOException $e) {
        error_log("Error fetching counts: " . $e->getMessage());
    }
}

// Fetch applications based on status filter
try {
    $query = "
        SELECT 
            ia.*,
            i.title AS internship_title,
            sp.first_name,
            sp.last_name,
            sp.email,
            sp.college,
            sp.course
        FROM internships i
        JOIN internship_applications ia ON i.id = ia.internship_id
        JOIN student_profiles sp ON ia.student_id = sp.user_id
        WHERE i.company_id = :company_id
        " . ($status_filter !== 'all' ? "AND ia.status = :status" : "") . "
        ORDER BY ia.created_at DESC
    ";
    
    $stmt = $conn->prepare($query);
    $params = ['company_id' => $company_id];
    
    if ($status_filter !== 'all') {
        $params['status'] = $status_filter;
    }
    
    $stmt->execute($params);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
    
} catch(PDOException $e) {
    error_log("Error fetching applications: " . $e->getMessage());
    $applications = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard - Applications</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #2c3e50;
            --secondary: #34495e;
            --accent: #3498db;
            --success: #2ecc71;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
        }

        body {
            background: #f8f9fc;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
        }

        /* Navbar Styling */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
        }

        .dropdown-toggle {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .dropdown-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44,62,80,0.3);
            color: white;
        }

        /* Sidebar Styling */
        .sidebar {
            background: white;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
            padding: 2rem 1rem;
            min-height: calc(100vh - 76px);
            transition: all 0.3s ease;
        }

        .list-group-item {
            border: none;
            border-radius: 10px !important;
            margin-bottom: 0.5rem;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
        }

        .list-group-item:hover {
            background: rgba(52,152,219,0.1);
            transform: translateX(5px);
        }

        .list-group-item.active {
            background: linear-gradient(135deg, var(--accent), #2980b9);
            border: none;
        }

        /* Main Content Styling */
        .main-content {
            padding: 2rem;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .btn-post {
            background: linear-gradient(135deg, var(--accent), #2980b9);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-post:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52,152,219,0.3);
            color: white;
        }

        /* Internship Cards */
        .internship-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            background: white;
            overflow: hidden;
        }

        .internship-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .card-text {
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }

        .card-stats {
            display: flex;
            justify-content: space-between;
            padding: 1rem;
            background: rgba(52,152,219,0.05);
            border-radius: 10px;
            margin: 1rem 0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--accent);
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--secondary);
        }

        /* Action Buttons */
        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-edit {
            background: rgba(52,152,219,0.1);
            color: var(--accent);
        }

        .btn-view {
            background: rgba(46,204,113,0.1);
            color: var(--success);
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 15px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--accent);
            margin-bottom: 1.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -100%;
                z-index: 1000;
            }

            .sidebar.active {
                left: 0;
            }
        }

        .table {
            font-size: 0.9rem;
        }
        
        .table td, .table th {
            padding: 1rem;
        }
        
        .badge {
            padding: 0.5rem 1rem;
            font-weight: 500;
        }
        
        .btn-sm {
            padding: 0.4rem 1rem;
            font-size: 0.85rem;
        }

        .application-card {
            transition: transform 0.2s;
            border-left: 4px solid #ddd;
        }
        .application-card:hover {
            transform: translateX(5px);
        }
        .application-card.status-pending { border-left-color: #ffc107; }
        .application-card.status-approved { border-left-color: #28a745; }
        .application-card.status-rejected { border-left-color: #dc3545; }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.5em 1em;
        }

        .status-card {
            transition: transform 0.2s;
            cursor: pointer;
        }
        .status-card:hover {
            transform: translateY(-5px);
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="logo.png" alt="InternEvo" height="40">
            </a>
            <div class="d-flex">
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($_SESSION['company_name']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="company_profile.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action active">
                        <i class="fas fa-dashboard me-2"></i>Dashboard
                    </a>
                    <a href="post_internship.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-plus-circle me-2"></i>Post Internship
                    </a>
                    <a href="manage_internships.php" class="list-group-item list-group-item-action">
    <i class="fas fa-list me-2"></i>Manage Internships
</a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="mb-4">
                    <h2>Internship Applications</h2>
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
                    <div class="alert alert-danger">
                        <?php 
                        echo $_SESSION['error'];
                        unset($_SESSION['error']);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Internships Grid -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-body">
                                <div>
                                    <h4 class="mb-0">Recent Applications</h4>
                                    <p class="text-muted mb-0">View and manage all internship applications</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Status Cards -->
                <div class="row mb-4">
                    <div class="col-md-3">
                        <a href="?status=all" class="text-decoration-none">
                            <div class="card status-card <?php echo $status_filter === 'all' ? 'border-primary' : ''; ?>">
                                <div class="card-body text-center">
                                    <h3 class="display-4"><?php echo $total_count; ?></h3>
                                    <p class="text-muted mb-0">Total Applications</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="?status=pending" class="text-decoration-none">
                            <div class="card status-card <?php echo $status_filter === 'pending' ? 'border-warning' : ''; ?>">
                                <div class="card-body text-center">
                                    <h3 class="display-4 text-warning"><?php echo $pending_count; ?></h3>
                                    <p class="text-muted mb-0">Pending</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="?status=approved" class="text-decoration-none">
                            <div class="card status-card <?php echo $status_filter === 'approved' ? 'border-success' : ''; ?>">
                                <div class="card-body text-center">
                                    <h3 class="display-4 text-success"><?php echo $approved_count; ?></h3>
                                    <p class="text-muted mb-0">Approved</p>
                                </div>
                            </div>
                        </a>
                    </div>
                    <div class="col-md-3">
                        <a href="?status=rejected" class="text-decoration-none">
                            <div class="card status-card <?php echo $status_filter === 'rejected' ? 'border-danger' : ''; ?>">
                                <div class="card-body text-center">
                                    <h3 class="display-4 text-danger"><?php echo $rejected_count; ?></h3>
                                    <p class="text-muted mb-0">Rejected</p>
                                </div>
                            </div>
                        </a>
                    </div>
                </div>

                <!-- Applications List -->
                <?php if (empty($applications)): ?>
                    <div class="alert alert-info">
                        <h4><i class="fas fa-info-circle me-2"></i>No Applications Found</h4>
                        <p>
                            <?php echo $status_filter !== 'all' ? 
                                "No " . $status_filter . " applications found." : 
                                "There are currently no applications for your internships."; ?>
                        </p>
                    </div>
                <?php else: ?>
                    <?php foreach ($applications as $app): ?>
                        <div class="card application-card status-<?php echo htmlspecialchars($app['status']); ?> mb-3">
                            <div class="card-body">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h5 class="card-title"><?php echo htmlspecialchars($app['first_name'] . ' ' . $app['last_name']); ?></h5>
                                        <p class="text-muted mb-1"><?php echo htmlspecialchars($app['email']); ?></p>
                                        <p class="mb-1">
                                            <strong>Applied for:</strong> <?php echo htmlspecialchars($app['internship_title']); ?>
                                        </p>
                                        <p class="mb-1">
                                            <strong>Expected Salary:</strong> ₹<?php echo htmlspecialchars($app['expected_salary']); ?>
                                        </p>
                                    </div>
                                    <div class="col-md-4">
                                        <p class="mb-1"><strong>College:</strong> <?php echo htmlspecialchars($app['college']); ?></p>
                                        <p class="mb-1"><strong>Course:</strong> <?php echo htmlspecialchars($app['course']); ?></p>
                                        <p class="mb-1"><strong>Skills:</strong> <?php echo htmlspecialchars($app['skills']); ?></p>
                                        
                                    </div>
                                    <div class="col-md-4 text-end">
                                        <?php if ($app['status'] === 'pending'): ?>
                                            <form method="POST" class="mb-2">
                                                <input type="hidden" name="application_id" value="<?php echo $app['id']; ?>">
                                                <button type="submit" name="status" value="approved" class="btn btn-success btn-sm">
                                                    <i class="fas fa-check me-1"></i>Approve
                                                </button>
                                                <button type="submit" name="status" value="rejected" class="btn btn-danger btn-sm">
                                                    <i class="fas fa-times me-1"></i>Reject
                                                </button>
                                            </form>
                                        <?php else: ?>
                                            <span class="badge bg-<?php echo $app['status'] === 'approved' ? 'success' : 'danger'; ?>">
                                                <?php echo ucfirst($app['status']); ?>
                                            </span>
                                        <?php endif; ?>
                                        
                                        <?php if ($app['resume_path']): ?>
                                            <a href="<?php echo htmlspecialchars($app['resume_path']); ?>" 
                                               class="btn btn-outline-primary btn-sm mt-2" 
                                               target="_blank">
                                                <i class="fas fa-file-pdf me-1"></i>View Resume
                                            </a>
                                        <?php endif; ?>
                                        
                                        <?php if ($app['portfolio_url']): ?>
                                            <a href="<?php echo htmlspecialchars($app['portfolio_url']); ?>" 
                                               class="btn btn-outline-info btn-sm mt-2"
                                               target="_blank">
                                                <i class="fas fa-globe me-1"></i>Portfolio
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 