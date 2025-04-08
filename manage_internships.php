<?php
session_start();
require_once 'config.php';

// Check if company is logged in
if (!isset($_SESSION['company_id'])) {
    header("Location: login.php");
    exit();
}

$company_id = $_SESSION['company_id'];

// First, automatically update status of expired internships
try {
    $update_expired = "
        UPDATE internships 
        SET status = 'closed' 
        WHERE company_id = ? 
        AND application_deadline < CURRENT_DATE 
        AND status = 'open'
    ";
    $stmt = $conn->prepare($update_expired);
    $stmt->execute([$company_id]);
    
    if ($stmt->rowCount() > 0) {
        $_SESSION['info'] = $stmt->rowCount() . " internship(s) automatically closed due to expired deadline.";
    }
} catch (PDOException $e) {
    error_log("Error updating expired internships: " . $e->getMessage());
}

// Handle delete action
if (isset($_POST['delete_internship']) && isset($_POST['internship_id'])) {
    $internship_id = filter_var($_POST['internship_id'], FILTER_SANITIZE_NUMBER_INT);
    
    try {
        // First check if there are any applications for this internship
        $check_query = "
            SELECT COUNT(*) FROM internship_applications 
            WHERE internship_id = ?
        ";
        $check_stmt = $conn->prepare($check_query);
        $check_stmt->execute([$internship_id]);
        $application_count = $check_stmt->fetchColumn();
        
        if ($application_count > 0) {
            $_SESSION['error'] = "Cannot delete internship with existing applications. Consider marking it as closed instead.";
        } else {
            // Delete the internship
            $delete_query = "
                DELETE FROM internships 
                WHERE id = ? AND company_id = ?
            ";
            $delete_stmt = $conn->prepare($delete_query);
            $delete_stmt->execute([$internship_id, $company_id]);
            
            if ($delete_stmt->rowCount() > 0) {
                $_SESSION['success'] = "Internship successfully deleted.";
            } else {
                $_SESSION['error'] = "Failed to delete internship. Internship not found or permission denied.";
            }
        }
    } catch (PDOException $e) {
        error_log("Error deleting internship: " . $e->getMessage());
        $_SESSION['error'] = "An error occurred while deleting the internship.";
    }
    
    // Redirect to prevent form resubmission
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Handle status change action (open/closed)
if (isset($_POST['toggle_status']) && isset($_POST['internship_id'])) {
    $internship_id = filter_var($_POST['internship_id'], FILTER_SANITIZE_NUMBER_INT);
    $new_status = $_POST['new_status'];
    
    if ($new_status !== 'open' && $new_status !== 'closed') {
        $_SESSION['error'] = "Invalid status value.";
    } else {
        try {
            $update_query = "
                UPDATE internships 
                SET status = ? 
                WHERE id = ? AND company_id = ?
            ";
            $update_stmt = $conn->prepare($update_query);
            $update_stmt->execute([$new_status, $internship_id, $company_id]);
            
            if ($update_stmt->rowCount() > 0) {
                $_SESSION['success'] = "Internship status updated successfully.";
            } else {
                $_SESSION['error'] = "Failed to update internship status.";
            }
        } catch (PDOException $e) {
            error_log("Error updating internship status: " . $e->getMessage());
            $_SESSION['error'] = "An error occurred while updating the internship status.";
        }
    }
    
    header("Location: " . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch all internships for the company with additional details
try {
    $query = "
        SELECT 
            i.*,
            cp.company_name,
            cp.logo_url,
            cp.industry_id,
            CASE 
                WHEN i.application_deadline < CURRENT_DATE THEN 'expired'
                WHEN i.status = 'closed' THEN 'closed'
                ELSE 'open'
            END as current_status,
            COUNT(ia.id) as application_count,
            DATEDIFF(i.application_deadline, CURRENT_DATE) as days_remaining
        FROM internships i
        INNER JOIN company_profiles cp ON i.company_id = cp.company_id
        LEFT JOIN internship_applications ia ON i.id = ia.internship_id
        WHERE i.company_id = ?
        GROUP BY i.id
        ORDER BY 
            CASE 
                WHEN i.status = 'open' AND i.application_deadline >= CURRENT_DATE THEN 1
                WHEN i.status = 'open' AND i.application_deadline < CURRENT_DATE THEN 2
                ELSE 3
            END,
            i.created_at DESC
    ";
    $stmt = $conn->prepare($query);
    $stmt->execute([$company_id]);
    $internships = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    error_log("Error fetching internships: " . $e->getMessage());
    $internships = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Internships - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .internship-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.2s;
        }
        .internship-card:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            padding: 0.5em 1em;
            border-radius: 20px;
            font-size: 0.8rem;
            display: inline-block;
            margin-right: 10px;
            margin-bottom: 10px;
        }
        .status-open {
            background-color: #e3fcef;
            color: #00875a;
        }
        .status-closed {
            background-color: #ffebe6;
            color: #de350b;
        }
        .delete-btn {
            background-color: #ffebe6;
            color: #de350b;
            border: none;
        }
        .delete-btn:hover {
            background-color: #de350b;
            color: white;
        }
        
        /* Add new status styles */
        .status-expired {
            background-color: #f4f5f7;
            color: #6b778c;
        }
        
        .deadline-warning {
            color: #de350b;
            font-size: 0.85rem;
        }
        
        .deadline-close {
            color: #ff8b00;
            font-size: 0.85rem;
        }
        
        .deadline-ok {
            color: #00875a;
            font-size: 0.85rem;
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
        <div class="container">
            <a class="navbar-brand" href="#">InternEvo</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav">
                    <li class="nav-item">
                        <a class="nav-link" href="company_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="manage_internships.php">Manage Internships</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="post_internship.php">Post New Internship</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h2>Manage Internships</h2>
            <a href="post_internship.php" class="btn btn-primary">
                <i class="fas fa-plus-circle me-2"></i>Post New Internship
            </a>
        </div>

        <?php if (isset($_SESSION['info'])): ?>
            <div class="alert alert-info alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['info'];
                unset($_SESSION['info']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($internships)): ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h4>No internships posted yet</h4>
                <p class="text-muted">Start by posting your first internship opportunity</p>
                <a href="post_internship.php" class="btn btn-primary mt-3">Post Internship</a>
            </div>
        <?php else: ?>
            <?php foreach ($internships as $internship): ?>
                <div class="card internship-card">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <h5 class="card-title mb-1"><?php echo htmlspecialchars($internship['title']); ?></h5>
                                <h6 class="company-name mb-2">
                                    <?php echo htmlspecialchars($internship['company_name']); ?>
                                </h6>
                                <span class="status-badge status-<?php echo $internship['current_status']; ?> mb-2">
                                    <?php echo ucfirst($internship['current_status']); ?>
                                </span>
                                
                                <?php if ($internship['current_status'] === 'open'): ?>
                                    <span class="ms-2 <?php 
                                        if ($internship['days_remaining'] <= 0) echo 'deadline-warning';
                                        elseif ($internship['days_remaining'] <= 7) echo 'deadline-close';
                                        else echo 'deadline-ok';
                                    ?>">
                                        <?php
                                        if ($internship['days_remaining'] <= 0) {
                                            echo '<i class="fas fa-exclamation-circle"></i> Deadline passed';
                                        } elseif ($internship['days_remaining'] == 1) {
                                            echo '<i class="fas fa-clock"></i> 1 day remaining';
                                        } else {
                                            echo '<i class="fas fa-clock"></i> ' . $internship['days_remaining'] . ' days remaining';
                                        }
                                        ?>
                                    </span>
                                <?php endif; ?>
                            </div>
                            <div class="col-md-3">
                                <p class="mb-0">
                                    <strong>Applications:</strong> 
                                    <?php echo $internship['application_count']; ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Deadline:</strong> 
                                    <?php echo date('M j, Y', strtotime($internship['application_deadline'])); ?>
                                </p>
                                <p class="mb-0">
                                    <strong>Posted:</strong> 
                                    <?php echo date('M j, Y', strtotime($internship['created_at'])); ?>
                                </p>
                            </div>
                            <div class="col-md-3 text-end">
                                <?php if ($internship['current_status'] === 'open'): ?>
                                    <form method="POST" class="d-inline-block" 
                                          onsubmit="return confirm('Are you sure you want to close this internship?');">
                                        <input type="hidden" name="internship_id" value="<?php echo $internship['id']; ?>">
                                        <input type="hidden" name="new_status" value="closed">
                                        <button type="submit" name="toggle_status" class="btn btn-outline-warning btn-sm me-2">
                                            <i class="fas fa-times-circle me-1"></i>Close
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <?php if ($internship['application_count'] == 0): ?>
                                    <form method="POST" class="d-inline-block" 
                                          onsubmit="return confirm('Are you sure you want to delete this internship? This action cannot be undone.');">
                                        <input type="hidden" name="internship_id" value="<?php echo $internship['id']; ?>">
                                        <button type="submit" name="delete_internship" class="btn delete-btn btn-sm">
                                            <i class="fas fa-trash-alt me-1"></i>Delete
                                        </button>
                                    </form>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>