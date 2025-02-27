<?php
session_start();
require_once 'config.php';

// Check if company is logged in
if (!isset($_SESSION['company_id']) || !isset($_SESSION['is_company'])) {
    header("Location: company_login.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $stmt = $conn->prepare("
            INSERT INTO internships (
                company_id, title, description, requirements, 
                location, duration, stipend, positions, 
                application_deadline
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $_SESSION['company_id'],
            $_POST['title'],
            $_POST['description'],
            $_POST['requirements'],
            $_POST['location'],
            $_POST['duration'],
            $_POST['stipend'],
            $_POST['positions'],
            $_POST['application_deadline']
        ]);

        $_SESSION['success'] = "Internship posted successfully!";
        header("Location: company_dashboard.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error posting internship: " . $e->getMessage();
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
            background: #1a1c23;
            color: #e3e3e3;
        }

        /* Main Container Styling */
        .post-internship-container {
            padding: 40px 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #1a1c23 0%, #2c2f3a 100%);
        }

        /* Form Card Styling */
        .form-card {
            background: #2c2f3a;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.3);
            border: 1px solid rgba(255, 255, 255, 0.1);
            margin-bottom: 30px;
        }

        /* Section Headers */
        .section-header {
            color: #4e73df;
            font-size: 2.2rem;
            font-weight: 600;
            margin-bottom: 30px;
            text-align: center;
            text-shadow: 0 0 10px rgba(78, 115, 223, 0.3);
        }

        /* Form Group Styling */
        .form-group {
            margin-bottom: 25px;
        }

        .form-label {
            color: #8a8d98;
            font-weight: 500;
            margin-bottom: 10px;
            font-size: 0.95rem;
        }

        /* Input Fields */
        .form-control, .form-select {
            background: #1a1c23;
            border: 2px solid rgba(78, 115, 223, 0.2);
            border-radius: 12px;
            color: #e3e3e3;
            padding: 12px 20px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            background: #1a1c23;
            border-color: #4e73df;
            box-shadow: 0 0 15px rgba(78, 115, 223, 0.2);
            color: #ffffff;
        }

        /* Placeholder Styling */
        ::placeholder {
            color: #6c757d;
            opacity: 0.7;
        }

        /* Rich Text Editor */
        .tox-tinymce {
            border-radius: 12px !important;
            border: 2px solid rgba(78, 115, 223, 0.2) !important;
            background: #1a1c23 !important;
        }

        .tox .tox-toolbar {
            background: #2c2f3a !important;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1) !important;
        }

        /* Submit Button */
        .btn-submit {
            background: linear-gradient(135deg, #4e73df 0%, #224abe 100%);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 15px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            width: 100%;
            margin-top: 20px;
            transition: all 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 20px rgba(78, 115, 223, 0.3);
        }

        /* Card Hover Effect */
        .form-card {
            transition: all 0.3s ease;
        }

        .form-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0, 0, 0, 0.4);
        }

        /* Input Groups */
        .input-group {
            background: #1a1c23;
            border-radius: 12px;
            overflow: hidden;
        }

        .input-group-text {
            background: #2c2f3a;
            border: none;
            color: #4e73df;
            padding: 0 20px;
        }

        /* Custom Select Styling */
        .form-select {
            background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='16' height='16' fill='%234e73df' viewBox='0 0 16 16'%3E%3Cpath d='M8 10.5l4-4H4l4 4z'/%3E%3C/svg%3E");
        }

        /* Required Field Indicator */
        .required-field::after {
            content: '*';
            color: #dc3545;
            margin-left: 5px;
        }

        /* Section Dividers */
        .section-divider {
            height: 1px;
            background: linear-gradient(to right, transparent, rgba(78, 115, 223, 0.3), transparent);
            margin: 30px 0;
        }

        /* Form Sections */
        .form-section {
            padding: 20px;
            border-radius: 15px;
            background: rgba(78, 115, 223, 0.05);
            margin-bottom: 25px;
            border: 1px solid rgba(78, 115, 223, 0.1);
        }

        .form-section-title {
            color: #4e73df;
            font-size: 1.2rem;
            font-weight: 600;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 1px solid rgba(78, 115, 223, 0.2);
        }

        /* Checkbox and Radio Styling */
        .form-check-input {
            background-color: #1a1c23;
            border-color: rgba(78, 115, 223, 0.3);
        }

        .form-check-input:checked {
            background-color: #4e73df;
            border-color: #4e73df;
        }

        .form-check-label {
            color: #8a8d98;
        }

        /* Help Text */
        .form-text {
            color: #6c757d;
            font-size: 0.85rem;
            margin-top: 5px;
        }

        /* Error States */
        .is-invalid {
            border-color: #dc3545 !important;
        }

        .invalid-feedback {
            color: #dc3545;
            font-size: 0.85rem;
            margin-top: 5px;
        }

        /* Success Message */
        .alert-success {
            background: rgba(40, 167, 69, 0.1);
            border: 1px solid rgba(40, 167, 69, 0.2);
            color: #28a745;
            border-radius: 12px;
            padding: 15px 20px;
        }

        /* Animation Effects */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .form-card {
            animation: fadeInUp 0.5s ease forwards;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-card {
                padding: 20px;
            }

            .section-header {
                font-size: 1.8rem;
            }

            .btn-submit {
                padding: 12px 25px;
                font-size: 1rem;
            }
        }

        /* Custom Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
        }

        ::-webkit-scrollbar-track {
            background: #1a1c23;
        }

        ::-webkit-scrollbar-thumb {
            background: #4e73df;
            border-radius: 4px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: #224abe;
        }

        /* Neon Glow Effects */
        .neon-glow {
            position: relative;
        }

        .neon-glow::after {
            content: '';
            position: absolute;
            top: -2px;
            left: -2px;
            right: -2px;
            bottom: -2px;
            background: linear-gradient(45deg, #4e73df, #224abe);
            border-radius: 14px;
            z-index: -1;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .neon-glow:hover::after {
            opacity: 0.3;
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
                                    <label class="form-label">Stipend</label>
                                    <input type="text" name="stipend" class="form-control" required>
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
    </script>
</body>
</html> 