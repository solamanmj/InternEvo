<?php
session_start();
require_once 'config.php';

// Debug line to check session
error_log("Session data: " . print_r($_SESSION, true));

// Check if company is logged in - modified check
if (!isset($_SESSION['company_id'])) {
    error_log("Company not logged in, redirecting to login");
    header("Location: company_login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Debug line to check POST data
        error_log("POST data received: " . print_r($_POST, true));

        // Validate stipend amount
        $stipend = filter_var($_POST['stipend'], FILTER_VALIDATE_INT);
        if ($stipend === false || $stipend < 1000 || $stipend > 100000) {
            throw new Exception("Stipend must be between ₹1,000 and ₹100,000");
        }

        $stmt = $conn->prepare("
            INSERT INTO internships (
                company_id, title, description, requirements, 
                location, duration, stipend, positions, 
                application_deadline, status
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 'open')
        ");

        $result = $stmt->execute([
            $_SESSION['company_id'],
            $_POST['title'],
            $_POST['description'],
            $_POST['requirements'],
            $_POST['location'],
            $_POST['duration'],
            $stipend,
            $_POST['positions'],
            $_POST['application_deadline']
        ]);

        if ($result) {
            $_SESSION['success'] = "Internship posted successfully!";
            header("Location: company_dashboard.php");
            exit();
        } else {
            throw new Exception("Failed to insert data");
        }
    } catch(PDOException $e) {
        error_log("Database error: " . $e->getMessage());
        $_SESSION['error'] = "Error posting internship. Please try again.";
    } catch(Exception $e) {
        error_log("General error: " . $e->getMessage());
        $_SESSION['error'] = "Error posting internship. Please try again.";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Internship - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Include Rich Text Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f8f9fa 0%, #e3f2fd 100%);
            color: #2c3e50;
            min-height: 100vh;
        }

        /* Main Container Styling */
        .post-internship-container {
            padding: 40px 0;
            min-height: 100vh;
        }

        /* Form Card Styling */
        .form-card {
            background: #ffffff;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            border: 1px solid rgba(0, 0, 0, 0.08);
            margin-bottom: 30px;
        }

        /* Section Headers */
        .section-header {
            color: #1565c0;
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
        }

        /* Form Group Styling */
        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            color: #2c3e50;
            font-weight: 500;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        /* Input Fields */
        .form-control, .form-select {
            background: #ffffff;
            border: 2px solid #e3f2fd;
            border-radius: 10px;
            color: #2c3e50;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: #ffffff;
            border-color: #1565c0;
            box-shadow: 0 0 15px rgba(21, 101, 192, 0.1);
            color: #2c3e50;
        }

        /* Placeholder Styling */
        ::placeholder {
            color: #95a5a6;
            opacity: 0.7;
        }

        /* Rich Text Editor */
        .editor {
            background: #ffffff;
            border: 2px solid #e3f2fd;
            border-radius: 10px;
            margin-bottom: 20px;
        }

        .ql-toolbar {
            border-top-left-radius: 10px;
            border-top-right-radius: 10px;
            border-bottom: 1px solid #e3f2fd;
            background: #f8f9fa;
        }

        .ql-container {
            border-bottom-left-radius: 10px;
            border-bottom-right-radius: 10px;
            background: #ffffff;
        }

        /* Submit Button */
        .btn-post {
            background: linear-gradient(135deg, #1565c0 0%, #0d47a1 100%);
            color: white;
            border: none;
            border-radius: 10px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-post:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(21, 101, 192, 0.3);
            color: white;
        }

        /* Card Styling */
        .card {
            background: #ffffff;
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
        }

        .card-body {
            padding: 2rem;
        }

        /* Alert Styling */
        .alert {
            border-radius: 10px;
            border: none;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.1);
        }

        .alert-success {
            background: #e3f2fd;
            color: #1565c0;
            border-left: 4px solid #1565c0;
        }

        .alert-danger {
            background: #ffebee;
            color: #c62828;
            border-left: 4px solid #c62828;
        }

        /* Responsive Adjustments */
        @media (max-width: 768px) {
            .form-card {
                padding: 20px;
            }

            .section-header {
                font-size: 1.8rem;
            }

            .btn-post {
                padding: 12px 25px;
                font-size: 1rem;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #f8f9fa;
        }

        ::-webkit-scrollbar-thumb {
            background: #1565c0;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #0d47a1;
        }

        /* Hover Effects */
        .hover-effect {
            transition: transform 0.3s ease;
        }

        .hover-effect:hover {
            transform: translateY(-2px);
        }

        .expired-internship {
            opacity: 0.8;
        }

        .expired-internship .card {
            position: relative;
            overflow: hidden;
            border: 1px solid #dc3545;
        }

        .expired-banner {
            position: absolute;
            top: 0;
            right: 0;
            background-color: #dc3545;
            color: white;
            padding: 5px 15px;
            font-size: 0.8rem;
            transform: rotate(45deg) translate(15px, -15px);
            transform-origin: top right;
            z-index: 10;
            font-weight: bold;
            box-shadow: 0 2px 5px rgba(0,0,0,0.2);
        }

        .expired-internship .btn-apply {
            background: #6c757d;
        }

        .expired-internship .btn-secondary {
            background: #6c757d;
            border-color: #6c757d;
        }

        .text-danger {
            color: #dc3545!important;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Post New Internship</h2>

                        <?php if (isset($_SESSION['error'])): ?>
                            <div class="alert alert-danger">
                                <?php 
                                echo $_SESSION['error'];
                                unset($_SESSION['error']);
                                ?>
                            </div>
                        <?php endif; ?>

                        <form method="POST" id="internshipForm">
                            <div class="mb-3">
                                <label class="form-label">Internship Title</label>
                                <input type="text" name="title" class="form-control" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <div id="description-editor" class="editor"></div>
                                <input type="hidden" name="description" id="description-input">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Requirements</label>
                                <div id="requirements-editor" class="editor"></div>
                                <input type="hidden" name="requirements" id="requirements-input">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-control" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Duration</label>
                                    <select name="duration" class="form-control" required>
                                        <option value="">Select Duration</option>
                                        <option value="1 Month">1 Month</option>
                                        <option value="2 Months">2 Months</option>
                                        <option value="3 Months">3 Months</option>
                                        <option value="6 Months">6 Months</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Stipend (₹)*</label>
                                    <input type="number" 
                                           name="stipend" 
                                           class="form-control" 
                                           required 
                                           min="1000" 
                                           max="100000" 
                                           placeholder="Enter amount between ₹1,000 - ₹100,000"
                                           oninput="validateStipend(this)">
                                    <div class="invalid-feedback">
                                        Please enter a valid stipend amount between ₹1,000 and ₹100,000
                                    </div>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Number of Positions</label>
                                    <input type="number" name="positions" class="form-control" min="1" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Application Deadline</label>
                                <input type="date" name="application_deadline" class="form-control" required>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-post">
                                    <i class="fas fa-plus me-2"></i>Post Internship
                                </button>
                                <a href="company_dashboard.php" class="btn btn-light">Cancel</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.js"></script>
    <script>
        // Initialize Rich Text Editors
        var descriptionEditor = new Quill('#description-editor', {
            theme: 'snow'
        });
        var requirementsEditor = new Quill('#requirements-editor', {
            theme: 'snow'
        });

        // Handle form submission
        document.getElementById('internshipForm').onsubmit = function() {
            // Get editor contents
            document.getElementById('description-input').value = descriptionEditor.root.innerHTML;
            document.getElementById('requirements-input').value = requirementsEditor.root.innerHTML;
            return true;
        };

        // Set minimum date for deadline
        var today = new Date().toISOString().split('T')[0];
        document.querySelector('input[type="date"]').min = today;

        function validateStipend(input) {
            const min = 1000;
            const max = 100000;
            const value = parseInt(input.value);
            
            if (value < min) {
                input.setCustomValidity(`Stipend must be at least ₹${min}`);
            } else if (value > max) {
                input.setCustomValidity(`Stipend cannot exceed ₹${max}`);
            } else {
                input.setCustomValidity('');
            }
            
            input.reportValidity();
        }

        // Format the stipend with commas while typing
        document.querySelector('input[name="stipend"]').addEventListener('input', function(e) {
            let value = this.value.replace(/,/g, '');
            if (value.length > 0) {
                value = parseInt(value).toLocaleString('en-IN');
                this.value = value.replace(/,/g, ''); // Remove commas for form submission
            }
        });
    </script>
</body>
</html> 