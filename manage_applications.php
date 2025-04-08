<?php
session_start();
require_once 'config.php';

// Check if company is logged in
if (!isset($_SESSION['company_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header('Location: company_login.php');
    exit();
}

// Handle application status updates
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['application_id']) && isset($_POST['status'])) {
    try {
        $stmt = $conn->prepare("
            UPDATE applications 
            SET status = ?, 
                status_updated_at = NOW(),
                feedback = ?
            WHERE id = ? AND internship_id IN (
                SELECT id FROM internships WHERE company_id = ?
            )
        ");
        
        $stmt->execute([
            $_POST['status'],
            $_POST['feedback'] ?? null,
            $_POST['application_id'],
            $_SESSION['company_id']
        ]);

        if ($stmt->rowCount() > 0) {
            // Send email notification to student
            $email_stmt = $conn->prepare("
                SELECT a.*, s.email as student_email, i.title 
                FROM applications a
                JOIN students s ON a.student_id = s.id
                JOIN internships i ON a.internship_id = i.id
                WHERE a.id = ?
            ");
            $email_stmt->execute([$_POST['application_id']]);
            $application = $email_stmt->fetch(PDO::FETCH_ASSOC);

            // You would implement email sending here
            // mail($application['student_email'], "Application Status Update", ...);

            $_SESSION['success'] = "Application status updated successfully!";
        }
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating application status.";
    }
    
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit();
}

// Fetch applications for company's internships
try {
    $stmt = $conn->prepare("
        SELECT 
            a.*,
            i.title as internship_title,
            s.first_name,
            s.last_name,
            s.email as student_email
        FROM applications a
        JOIN internships i ON a.internship_id = i.id
        JOIN students s ON a.student_id = s.id
        WHERE i.company_id = ?
        ORDER BY a.applied_date DESC
    ");
    
    $stmt->execute([$_SESSION['company_id']]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching applications.";
    $applications = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Applications - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .application-card {
            transition: transform 0.3s ease;
            margin-bottom: 20px;
        }
        
        .application-card:hover {
            transform: translateY(-5px);
        }
        
        .status-badge {
            font-size: 0.9rem;
            padding: 8px 12px;
        }
        
        .status-pending { background-color: #ffd700; color: #000; }
        .status-approved { background-color: #28a745; color: #fff; }
        .status-rejected { background-color: #dc3545; color: #fff; }
        
        .feedback-text {
            font-style: italic;
            color: #6c757d;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'company_navbar.php'; ?>

    <div class="container py-5">
        <h2 class="mb-4">Manage Applications</h2>
        
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

        <!-- Filter Controls -->
        <div class="row mb-4">
            <div class="col-md-6">
                <select class="form-select" id="statusFilter">
                    <option value="">All Status</option>
                    <option value="Pending">Pending</option>
                    <option value="Approved">Approved</option>
                    <option value="Rejected">Rejected</option>
                </select>
            </div>
            <div class="col-md-6">
                <input type="text" class="form-control" id="searchInput" placeholder="Search by name or internship...">
            </div>
        </div>

        <!-- Applications List -->
        <div class="applications-container">
            <?php if (!empty($applications)): ?>
                <?php foreach ($applications as $application): ?>
                    <div class="application-card card" 
                         data-status="<?php echo htmlspecialchars($application['status']); ?>"
                         data-search="<?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name'] . ' ' . $application['internship_title']); ?>">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <div class="col-md-8">
                                    <h5 class="card-title mb-1">
                                        <?php echo htmlspecialchars($application['first_name'] . ' ' . $application['last_name']); ?>
                                    </h5>
                                    <p class="text-muted mb-2">
                                        Applied for: <?php echo htmlspecialchars($application['internship_title']); ?>
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-envelope me-2"></i>
                                        <?php echo htmlspecialchars($application['student_email']); ?>
                                    </p>
                                    <p class="mb-2">
                                        <i class="fas fa-clock me-2"></i>
                                        Applied on: <?php echo date('d M Y', strtotime($application['applied_date'])); ?>
                                    </p>
                                    <?php if ($application['feedback']): ?>
                                        <p class="feedback-text mb-0">
                                            <i class="fas fa-comment me-2"></i>
                                            Feedback: <?php echo htmlspecialchars($application['feedback']); ?>
                                        </p>
                                    <?php endif; ?>
                                </div>
                                <div class="col-md-4 text-end">
                                    <span class="badge status-badge status-<?php echo strtolower($application['status']); ?> mb-3">
                                        <?php echo htmlspecialchars($application['status']); ?>
                                    </span>
                                    
                                    <?php if ($application['status'] === 'Pending'): ?>
                                        <div class="btn-group d-block">
                                            <button type="button" 
                                                    class="btn btn-success me-2"
                                                    onclick="updateStatus(<?php echo $application['id']; ?>, 'Approved')">
                                                <i class="fas fa-check me-1"></i>Approve
                                            </button>
                                            <button type="button" 
                                                    class="btn btn-danger"
                                                    onclick="updateStatus(<?php echo $application['id']; ?>, 'Rejected')">
                                                <i class="fas fa-times me-1"></i>Reject
                                            </button>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <a href="view_application.php?id=<?php echo $application['id']; ?>" 
                                       class="btn btn-outline-primary mt-2">
                                        <i class="fas fa-eye me-1"></i>View Details
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div class="text-center py-5">
                    <i class="fas fa-inbox fa-3x mb-3 text-muted"></i>
                    <h4>No applications yet</h4>
                    <p class="text-muted">When students apply for your internships, they will appear here.</p>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Status Update Modal -->
    <div class="modal fade" id="statusModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="statusForm" method="POST">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Application Status</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <input type="hidden" name="application_id" id="applicationId">
                        <input type="hidden" name="status" id="applicationStatus">
                        
                        <div class="mb-3">
                            <label for="feedback" class="form-label">Feedback (Optional)</label>
                            <textarea class="form-control" name="feedback" id="feedback" rows="3"
                                    placeholder="Provide feedback to the applicant..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary">Update Status</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Status update modal handling
        function updateStatus(applicationId, status) {
            document.getElementById('applicationId').value = applicationId;
            document.getElementById('applicationStatus').value = status;
            
            const modal = new bootstrap.Modal(document.getElementById('statusModal'));
            modal.show();
        }

        // Filtering functionality
        const statusFilter = document.getElementById('statusFilter');
        const searchInput = document.getElementById('searchInput');
        const applications = document.querySelectorAll('.application-card');

        function filterApplications() {
            const statusValue = statusFilter.value.toLowerCase();
            const searchValue = searchInput.value.toLowerCase();

            applications.forEach(card => {
                const cardStatus = card.dataset.status.toLowerCase();
                const cardSearch = card.dataset.search.toLowerCase();
                
                const statusMatch = !statusValue || cardStatus === statusValue;
                const searchMatch = !searchValue || cardSearch.includes(searchValue);
                
                card.style.display = statusMatch && searchMatch ? 'block' : 'none';
            });
        }

        statusFilter.addEventListener('change', filterApplications);
        searchInput.addEventListener('input', filterApplications);
    </script>
</body>
</html>
