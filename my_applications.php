<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Fetch user's applications
try {
    $stmt = $conn->prepare("
        SELECT 
            ia.*,
            i.title as internship_title,
            i.duration,
            i.stipend,
            i.location,
            cp.company_name,
            cp.logo_url
        FROM internship_applications ia
        INNER JOIN internships i ON ia.internship_id = i.id
        INNER JOIN company_profiles cp ON i.company_id = cp.company_id
        WHERE ia.student_id = ?
        ORDER BY ia.created_at DESC
    ");
    
    $stmt->execute([$_SESSION['user_id']]);
    $applications = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $applications = [];
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Applications - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-white: #ffffff;
            --secondary-white: #f8fafc;
            --accent-blue: #3b82f6;
            --light-blue: #60a5fa;
            --text-dark: #1e293b;
            --text-gray: #64748b;
        }

        .page-header {
            background: linear-gradient(135deg, var(--accent-blue) 0%, var(--light-blue) 100%);
            padding: 2rem 0;
            color: white;
            margin-bottom: 2rem;
        }

        .application-card {
            background: var(--primary-white);
            border-radius: 15px;
            border: 1px solid rgba(0, 0, 0, 0.1);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.05);
            transition: all 0.3s ease;
            margin-bottom: 1.5rem;
        }

        .application-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(59, 130, 246, 0.1);
        }

        .company-logo {
            width: 60px;
            height: 60px;
            object-fit: contain;
            border-radius: 10px;
            padding: 5px;
            background: var(--secondary-white);
        }

        .status-badge {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.875rem;
        }

        .status-pending {
            background: #fef3c7;
            color: #92400e;
        }

        .status-accepted {
            background: #dcfce7;
            color: #166534;
        }

        .status-rejected {
            background: #fee2e2;
            color: #991b1b;
        }

        .skills-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .skill-tag {
            background: rgba(59, 130, 246, 0.1);
            color: var(--accent-blue);
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.875rem;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="page-header">
        <div class="container">
            <h1 class="h3 mb-0">My Applications</h1>
        </div>
    </div>

    <div class="container">
        <?php if (!empty($applications)): ?>
            <?php foreach ($applications as $application): ?>
                <div class="application-card p-4">
                    <div class="row align-items-center">
                        <div class="col-md-1">
                            <?php if (!empty($application['logo_url'])): ?>
                                <img src="<?php echo htmlspecialchars($application['logo_url']); ?>" 
                                     alt="<?php echo htmlspecialchars($application['company_name']); ?>" 
                                     class="company-logo">
                            <?php else: ?>
                                <div class="company-logo d-flex align-items-center justify-content-center bg-light">
                                    <i class="fas fa-building text-gray-400"></i>
                                </div>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-6">
                            <h5 class="mb-1"><?php echo htmlspecialchars($application['internship_title']); ?></h5>
                            <p class="mb-1 text-primary"><?php echo htmlspecialchars($application['company_name']); ?></p>
                            <div class="text-muted small mb-2">
                                <span class="me-3">
                                    <i class="fas fa-map-marker-alt me-1"></i>
                                    <?php echo htmlspecialchars($application['location']); ?>
                                </span>
                                <span class="me-3">
                                    <i class="fas fa-clock me-1"></i>
                                    <?php echo htmlspecialchars($application['duration']); ?>
                                </span>
                                <span>
                                    <i class="fas fa-wallet me-1"></i>
                                    Expected: ₹<?php echo htmlspecialchars($application['expected_salary']); ?>/month
                                </span>
                            </div>
                            <div class="skills-tags">
                                <?php 
                                $skills = explode(',', $application['skills']);
                                foreach($skills as $skill): ?>
                                    <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                                <?php endforeach; ?>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="mb-2">
                                <small class="text-muted">Availability Date:</small><br>
                                <?php echo date('d M Y', strtotime($application['availability_date'])); ?>
                            </div>
                            <?php if($application['portfolio_url']): ?>
                                <div class="mb-2">
                                    <a href="<?php echo htmlspecialchars($application['portfolio_url']); ?>" 
                                       target="_blank" 
                                       class="btn btn-sm btn-outline-primary">
                                        <i class="fas fa-external-link-alt me-1"></i>Portfolio
                                    </a>
                                </div>
                            <?php endif; ?>
                            <?php if($application['resume_path']): ?>
                                <a href="<?php echo htmlspecialchars($application['resume_path']); ?>" 
                                   target="_blank" 
                                   class="btn btn-sm btn-outline-secondary">
                                    <i class="fas fa-file-pdf me-1"></i>View Resume
                                </a>
                            <?php endif; ?>
                        </div>
                        <div class="col-md-2 text-end">
                            <span class="status-badge <?php 
                                echo match($application['status']) {
                                    'pending' => 'status-pending',
                                    'accepted' => 'status-accepted',
                                    'rejected' => 'status-rejected',
                                    default => 'status-pending'
                                };
                            ?>">
                                <?php echo ucfirst(htmlspecialchars($application['status'])); ?>
                            </span>
                            <small class="text-muted d-block mt-2">
                                Applied: <?php echo date('d M Y', strtotime($application['created_at'])); ?>
                            </small>
                            <?php if($application['feedback']): ?>
                                <div class="mt-2">
                                    <button class="btn btn-sm btn-link" 
                                            data-bs-toggle="tooltip" 
                                            title="<?php echo htmlspecialchars($application['feedback']); ?>">
                                        <i class="fas fa-comment-alt me-1"></i>Feedback
                                    </button>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="text-center py-5">
                <i class="fas fa-clipboard-list fa-3x text-muted mb-3"></i>
                <h3>No Applications Yet</h3>
                <p class="text-muted">Start applying for internships to see them here</p>
                <a href="home.php" class="btn btn-primary mt-3">
                    <i class="fas fa-search me-2"></i>Browse Internships
                </a>
            </div>
        <?php endif; ?>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Initialize tooltips
        var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
        var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
            return new bootstrap.Tooltip(tooltipTriggerEl)
        })
    </script>
</body>
</html>
