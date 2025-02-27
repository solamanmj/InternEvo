<?php
session_start();
require_once 'config.php';

// Check if company is logged in
if (!isset($_SESSION['company_id']) || !isset($_SESSION['is_company'])) {
    header("Location: company_login.php");
    exit();
}

// Get internship ID from URL
$internship_id = isset($_GET['id']) ? $_GET['id'] : 0;

// Fetch internship details
try {
    $stmt = $conn->prepare("
        SELECT * FROM internships 
        WHERE id = ? AND company_id = ?
    ");
    $stmt->execute([$internship_id, $_SESSION['company_id']]);
    $internship = $stmt->fetch();

    if (!$internship) {
        $_SESSION['error'] = "Internship not found or access denied";
        header("Location: company_dashboard.php");
        exit();
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching internship: " . $e->getMessage();
    header("Location: company_dashboard.php");
    exit();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        $stmt = $conn->prepare("
            UPDATE internships 
            SET title = ?, description = ?, requirements = ?, 
                location = ?, duration = ?, stipend = ?, 
                positions = ?, application_deadline = ?, 
                status = ?
            WHERE id = ? AND company_id = ?
        ");

        $stmt->execute([
            $_POST['title'],
            $_POST['description'],
            $_POST['requirements'],
            $_POST['location'],
            $_POST['duration'],
            $_POST['stipend'],
            $_POST['positions'],
            $_POST['application_deadline'],
            $_POST['status'],
            $internship_id,
            $_SESSION['company_id']
        ]);

        $_SESSION['success'] = "Internship updated successfully!";
        header("Location: company_dashboard.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating internship: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Internship - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fc;
        }
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .btn-save {
            background: linear-gradient(135deg, #4e73df 0%, #2e59d9 100%);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 30px;
        }
        .editor {
            height: 200px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Edit Internship</h2>

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
                                <input type="text" name="title" class="form-control" 
                                    value="<?php echo htmlspecialchars($internship['title']); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Description</label>
                                <div id="description-editor" class="editor">
                                    <?php echo $internship['description']; ?>
                                </div>
                                <input type="hidden" name="description" id="description-input">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Requirements</label>
                                <div id="requirements-editor" class="editor">
                                    <?php echo $internship['requirements']; ?>
                                </div>
                                <input type="hidden" name="requirements" id="requirements-input">
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Location</label>
                                    <input type="text" name="location" class="form-control" 
                                        value="<?php echo htmlspecialchars($internship['location']); ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Duration</label>
                                    <select name="duration" class="form-control" required>
                                        <option value="1 Month" <?php echo $internship['duration'] == '1 Month' ? 'selected' : ''; ?>>1 Month</option>
                                        <option value="2 Months" <?php echo $internship['duration'] == '2 Months' ? 'selected' : ''; ?>>2 Months</option>
                                        <option value="3 Months" <?php echo $internship['duration'] == '3 Months' ? 'selected' : ''; ?>>3 Months</option>
                                        <option value="6 Months" <?php echo $internship['duration'] == '6 Months' ? 'selected' : ''; ?>>6 Months</option>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Stipend</label>
                                    <input type="text" name="stipend" class="form-control" 
                                        value="<?php echo htmlspecialchars($internship['stipend']); ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Number of Positions</label>
                                    <input type="number" name="positions" class="form-control" min="1" 
                                        value="<?php echo htmlspecialchars($internship['positions']); ?>" required>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Application Deadline</label>
                                    <input type="date" name="application_deadline" class="form-control" 
                                        value="<?php echo $internship['application_deadline']; ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Status</label>
                                    <select name="status" class="form-control" required>
                                        <option value="active" <?php echo $internship['status'] == 'active' ? 'selected' : ''; ?>>Active</option>
                                        <option value="closed" <?php echo $internship['status'] == 'closed' ? 'selected' : ''; ?>>Closed</option>
                                    </select>
                                </div>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-save">
                                    <i class="fas fa-save me-2"></i>Save Changes
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