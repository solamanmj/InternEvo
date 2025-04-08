<?php
session_start();
require_once 'config.php';

if (!isset($_GET['id'])) {
    header('Location: browse_internships.php');
    exit();
}

$internship_id = filter_var($_GET['id'], FILTER_SANITIZE_NUMBER_INT);

try {
    // Fetch internship details with company information
    $stmt = $conn->prepare("
        SELECT 
            i.*,
            c.company_name,
            c.company_logo,
            c.company_description,
            c.website,
            c.location as company_location
        FROM internships i
        JOIN company_profiles c ON i.company_id = c.company_id
        WHERE i.id = ? AND i.status = 'open'
        AND i.application_deadline >= CURRENT_DATE
    ");
    
    $stmt->execute([$internship_id]);
    $internship = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$internship) {
        $_SESSION['error'] = "Internship not found or no longer available.";
        header('Location: browse_internships.php');
        exit();
    }

    // Check if user has already applied
    $already_applied = false;
    if (isset($_SESSION['student_id'])) {
        $stmt = $conn->prepare("
            SELECT id FROM applications 
            WHERE student_id = ? AND internship_id = ?
        ");
        $stmt->execute([$_SESSION['student_id'], $internship_id]);
        $already_applied = $stmt->fetch() !== false;
    }

} catch(PDOException $e) {
    error_log("Error: " . $e->getMessage());
    $_SESSION['error'] = "Error fetching internship details.";
    header('Location: browse_internships.php');
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($internship['title']); ?> - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
            min-height: 100vh;
        }
        .internship-card {
            background: #ffffff;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }
        .company-logo {
            width: 120px;
            height: 120px;
            object-fit: contain;
            border-radius: 10px;
            padding: 10px;
            background: #fff;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        .detail-section {
            border-left: 4px solid #1565c0;
            padding-left: 1rem;
            margin: 1.5rem 0;
        }
        .apply-btn {
            background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
            color: white;
            border: none;
            padding: 15px 40px;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }
        .apply-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(21, 101, 192, 0.3);
            color: white;
        }
        .key-detail {
            padding: 10px 15px;
            background: #f8f9fa;
            border-radius: 8px;
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php include 'student_navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="card internship-card">
                    <div class="card-body p-4">
                        <!-- Company Info -->
                        <div class="d-flex align-items-center mb-4">
                            <?php if ($internship['company_logo']): ?>
                                <img src="<?php echo htmlspecialchars($internship['company_logo']); ?>" 
                                     alt="Company Logo" class="company-logo me-4">
                            <?php endif; ?>
                            <div>
                                <h2 class="mb-2"><?php echo htmlspecialchars($internship['title']); ?></h2>
                                <h4 class="text-primary mb-2"><?php echo htmlspecialchars($internship['company_name']); ?></h4>
                                <p class="text-muted mb-0">
                                    <i class="fas fa-map-marker-alt me-2"></i>
                                    <?php echo htmlspecialchars($internship['location']); ?>
                                </p>
                            </div>
                        </div>

                        <!-- Key Details -->
                        <div class="row mb-4">
                            <div class="col-md-4">
                                <div class="key-detail">
                                    <i class="fas fa-clock text-primary me-2"></i>
                                    <strong>Duration:</strong><br>
                                    <?php echo htmlspecialchars($internship['duration']); ?>
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="key-detail">
                                    <i class="fas fa-money-bill-wave text-primary me-2"></i>
                                    <strong>Stipend:</strong><br>
                                    ₹<?php echo number_format($internship['stipend']); ?>/month
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="key-detail">
                                    <i class="fas fa-calendar-alt text-primary me-2"></i>
                                    <strong>Apply by:</strong><br>
                                    <?php echo date('F d, Y', strtotime($internship['application_deadline'])); ?>
                                </div>
                            </div>
                        </div>

                        <!-- Description -->
                        <div class="detail-section">
                            <h5 class="mb-3">About the Internship</h5>
                            <div class="mb-4">
                                <?php echo nl2br(htmlspecialchars($internship['description'])); ?>
                            </div>
                        </div>

                        <!-- Requirements -->
                        <div class="detail-section">
                            <h5 class="mb-3">Requirements</h5>
                            <div class="mb-4">
                                <?php echo nl2br(htmlspecialchars($internship['requirements'])); ?>
                            </div>
                        </div>

                        <!-- Company Description -->
                        <div class="detail-section">
                            <h5 class="mb-3">About <?php echo htmlspecialchars($internship['company_name']); ?></h5>
                            <div class="mb-4">
                                <?php echo nl2br(htmlspecialchars($internship['company_description'])); ?>
                                <?php if ($internship['website']): ?>
                                    <p class="mt-3">
                                        <a href="<?php echo htmlspecialchars($internship['website']); ?>" 
                                           target="_blank" class="text-primary">
                                            <i class="fas fa-external-link-alt me-2"></i>Visit Company Website
                                        </a>
                                    </p>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Apply Button Section -->
                        <div class="text-center mt-5">
                            <?php if (!isset($_SESSION['student_id'])): ?>
                                <a href="student_login.php?redirect=apply_internship.php?id=<?php echo $internship_id; ?>" 
                                   class="btn btn-primary btn-lg apply-btn">
                                    <i class="fas fa-sign-in-alt me-2"></i>Login to Apply
                                </a>
                            <?php elseif ($already_applied): ?>
                                <button class="btn btn-secondary btn-lg" disabled>
                                    <i class="fas fa-check me-2"></i>Already Applied
                                </button>
                            <?php else: ?>
                                <a href="apply_internship.php?id=<?php echo $internship_id; ?>" 
                                   class="btn btn-primary btn-lg apply-btn">
                                    <i class="fas fa-paper-plane me-2"></i>Apply Now
                                </a>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 