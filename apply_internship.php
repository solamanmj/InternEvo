<?php
session_start();
require_once 'config.php';

// Check if student is logged in
if (!isset($_SESSION['student_id'])) {
    $_SESSION['error'] = "Please login as a student to apply";
    header("Location: login.php");
    exit();
}

// Get internship ID
$internship_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Fetch internship details
try {
    $stmt = $conn->prepare("
        SELECT i.*, cp.company_name 
        FROM internships i
        JOIN company_profiles cp ON i.company_id = cp.company_id
        WHERE i.id = ? AND i.status = 'active'
    ");
    $stmt->execute([$internship_id]);
    $internship = $stmt->fetch();

    if (!$internship) {
        $_SESSION['error'] = "Internship not found or no longer active";
        header("Location: home.php");
        exit();
    }

    // Check if already applied
    $stmt = $conn->prepare("
        SELECT * FROM applications 
        WHERE student_id = ? AND internship_id = ?
    ");
    $stmt->execute([$_SESSION['student_id'], $internship_id]);
    if ($stmt->fetch()) {
        $_SESSION['error'] = "You have already applied for this internship";
        header("Location: view_internship.php?id=" . $internship_id);
        exit();
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
    header("Location: home.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Start transaction
        $conn->beginTransaction();

        // Handle resume upload
        $resume_path = '';
        if (isset($_FILES['resume']) && $_FILES['resume']['size'] > 0) {
            $target_dir = "uploads/resumes/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["resume"]["name"], PATHINFO_EXTENSION));
            $new_filename = "resume_" . $_SESSION['student_id'] . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["resume"]["tmp_name"], $target_file)) {
                $resume_path = $target_file;
            } else {
                throw new Exception("Failed to upload resume");
            }
        }

        // Insert application
        $stmt = $conn->prepare("
            INSERT INTO applications (
                student_id, internship_id, cover_letter, 
                resume_path, status, applied_date
            ) VALUES (?, ?, ?, ?, 'pending', CURRENT_TIMESTAMP)
        ");

        $stmt->execute([
            $_SESSION['student_id'],
            $internship_id,
            $_POST['cover_letter'],
            $resume_path
        ]);

        $conn->commit();
        $_SESSION['success'] = "Application submitted successfully!";
        header("Location: my_applications.php");
        exit();

    } catch(Exception $e) {
        $conn->rollBack();
        $_SESSION['error'] = "Error submitting application: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Internship - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fc;
        }
        .application-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .internship-details {
            background: #f8f9fa;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card application-card">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Apply for Internship</h2>

                        <div class="internship-details">
                            <h5><?php echo htmlspecialchars($internship['title']); ?></h5>
                            <p class="text-muted mb-2">
                                <i class="fas fa-building me-2"></i>
                                <?php echo htmlspecialchars($internship['company_name']); ?>
                            </p>
                            <div class="small text-muted">
                                <i class="fas fa-map-marker-alt me-2"></i>
                                <?php echo htmlspecialchars($internship['location']); ?>
                                <span class="mx-2">|</span>
                                <i class="fas fa-clock me-2"></i>
                                <?php echo htmlspecialchars($internship['duration']); ?>
                            </div>
                        </div>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">Cover Letter</label>
                                <textarea name="cover_letter" class="form-control" rows="6" required
                                    placeholder="Explain why you're interested in this internship and what makes you a good fit..."></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Resume (PDF only)</label>
                                <input type="file" name="resume" class="form-control" accept=".pdf" required>
                                <div class="form-text">Maximum file size: 5MB</div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                                </button>
                                <a href="view_internship.php?id=<?php echo $internship_id; ?>" class="btn btn-light">
                                    Cancel
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 