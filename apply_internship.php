<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
require_once 'config.php';

// Test database connection
try {
    $test = $conn->query("SELECT 1");
} catch (PDOException $e) {
    die("Database connection failed: " . $e->getMessage());
}

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || !isset($_SESSION['user_id'])) {
    $_SESSION['redirect_url'] = $_SERVER['REQUEST_URI'];
    header("Location: login.php");
    exit();
}

// Get internship ID from URL
$internship_id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

// Fetch internship details
try {
    $stmt = $conn->prepare("
        SELECT i.*, cp.company_name 
        FROM internships i 
        JOIN company_profiles cp ON i.company_id = cp.company_id 
        WHERE i.id = ?
    ");
    $stmt->execute([$internship_id]);
    $internship = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$internship) {
        header("Location: home.php");
        exit();
    }
} catch(PDOException $e) {
    header("Location: home.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        // Debug: Print form data
        error_log("Form submission data: " . print_r($_POST, true));
        error_log("Session data: " . print_r($_SESSION, true));

        // Check if already applied
        $checkStmt = $conn->prepare("
            SELECT id FROM internship_applications 
            WHERE internship_id = ? AND student_id = ?
        ");
        $checkStmt->execute([$internship_id, $_SESSION['user_id']]);
        
        if ($checkStmt->rowCount() > 0) {
            $error = "You have already applied for this internship.";
            error_log("Duplicate application attempted");
        } else {
            // Process file upload for resume
            $resume = null;
            if (isset($_FILES['resume']) && $_FILES['resume']['error'] == 0) {
                // Debug: Print file upload data
                error_log("File upload data: " . print_r($_FILES['resume'], true));
                
                $allowed = ['pdf', 'doc', 'docx'];
                $filename = $_FILES['resume']['name'];
                $filetype = pathinfo($filename, PATHINFO_EXTENSION);
                
                if (in_array(strtolower($filetype), $allowed)) {
                    $newname = uniqid('resume_') . '.' . $filetype;
                    $upload_dir = 'uploads/resumes/';
                    
                    if (!is_dir($upload_dir)) {
                        mkdir($upload_dir, 0777, true);
                    }
                    
                    if (move_uploaded_file($_FILES['resume']['tmp_name'], $upload_dir . $newname)) {
                        $resume = $upload_dir . $newname;
                    }
                }
            }

            // Insert application with debug logging
            $stmt = $conn->prepare("
                INSERT INTO internship_applications (
                    internship_id, 
                    student_id, 
                    expected_salary, 
                    resume_path, 
                    skills, 
                    portfolio_url, 
                    created_at, 
                    status
                ) VALUES (?, ?, ?, ?, ?, ?, NOW(), 'pending')
            ");
            
            $params = [
                $internship_id,
                $_SESSION['user_id'],
                $_POST['expected_salary'],
                $resume,
                $_POST['skills'],
                $_POST['portfolio_url']
            ];
            
            // Debug: Print insert parameters
            error_log("Insert parameters: " . print_r($params, true));
            
            $stmt->execute($params);
            
            // Debug: Check if insert was successful
            error_log("Insert ID: " . $conn->lastInsertId());
            
            $success = "Your application has been submitted successfully!";
        }
    } catch(PDOException $e) {
        error_log("Application submission error: " . $e->getMessage());
        $error = "An error occurred while submitting your application.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Internship - <?php echo htmlspecialchars($internship['title']); ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .form-section {
            background: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }
        .required-field::after {
            content: "*";
            color: red;
            margin-left: 4px;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'navbar.php'; ?>

    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="form-section">
                    <h2 class="mb-4">Apply for <?php echo htmlspecialchars($internship['title']); ?></h2>
                    <p class="text-muted mb-4">at <?php echo htmlspecialchars($internship['company_name']); ?></p>

                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger">
                            <?php echo $error; ?>
                        </div>
                    <?php endif; ?>

                    <?php if (isset($success)): ?>
                        <div class="alert alert-success">
                            <h4><i class="fas fa-check-circle me-2"></i>Success!</h4>
                            <p class="mb-0"><?php echo $success; ?></p>
                            <div class="mt-3">
                                <a href="home.php" class="btn btn-primary">
                                    <i class="fas fa-search me-2"></i>Browse More Internships
                                </a>
                            </div>
                        </div>
                    <?php else: ?>
                        <form method="POST" enctype="multipart/form-data">
                            <!-- Skills -->
                            <div class="mb-3">
                                <label class="form-label">Relevant Skills <span class="text-danger">*</span></label>
                                <input type="text" name="skills" class="form-control" required>
                                <div class="form-text">List your relevant skills (comma separated)</div>
                            </div>

                            <!-- Resume Upload -->
                            <div class="mb-3">
                                <label class="form-label">Resume <span class="text-danger">*</span></label>
                                <input type="file" name="resume" class="form-control" accept=".pdf,.doc,.docx" required>
                                <div class="form-text">Upload your resume (PDF, DOC, or DOCX format)</div>
                            </div>

                            <!-- Portfolio URL -->
                            <div class="mb-3">
                                <label class="form-label">Portfolio URL</label>
                                <input type="url" name="portfolio_url" class="form-control">
                                <div class="form-text">Link to your portfolio or GitHub profile (optional)</div>
                            </div>

                            <!-- Expected Salary -->
                            <div class="mb-3">
                                <label class="form-label">Expected Stipend (₹/month) <span class="text-danger">*</span></label>
                                <input type="number" name="expected_salary" class="form-control" required>
                            </div>

                            

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-paper-plane me-2"></i>Submit Application
                                </button>
                                <a href="home.php" class="btn btn-light">
                                    <i class="fas fa-arrow-left me-2"></i>Back to Internships
                                </a>
                            </div>
                        </form>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>