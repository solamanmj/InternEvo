<?php
session_start();
require_once 'config.php';

// Check if company is logged in
if (!isset($_SESSION['company_id']) || !isset($_SESSION['is_company'])) {
    header("Location: company_login.php");
    exit();
}

// Fetch company details
try {
    $stmt = $conn->prepare("
        SELECT cp.*, ic.category_name 
        FROM company_profiles cp 
        JOIN industry_categories ic ON cp.industry_id = ic.id 
        WHERE cp.company_id = ?
    ");
    $stmt->execute([$_SESSION['company_id']]);
    $company = $stmt->fetch();

    // Fetch industries for dropdown
    $stmt = $conn->query("SELECT * FROM industry_categories ORDER BY category_name");
    $industries = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching company details: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    try {
        // Handle logo upload if provided
        $logo_url = $company['logo_url']; // Keep existing logo by default
        if (isset($_FILES['logo']) && $_FILES['logo']['size'] > 0) {
            $target_dir = "uploads/logos/";
            if (!file_exists($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            $file_extension = strtolower(pathinfo($_FILES["logo"]["name"], PATHINFO_EXTENSION));
            $new_filename = "company_" . $_SESSION['company_id'] . "_" . time() . "." . $file_extension;
            $target_file = $target_dir . $new_filename;

            if (move_uploaded_file($_FILES["logo"]["tmp_name"], $target_file)) {
                $logo_url = $target_file;
            }
        }

        // Update company profile
        $stmt = $conn->prepare("
            UPDATE company_profiles SET 
                company_name = ?,
                industry_id = ?,
                company_phone = ?,
                company_address = ?,
                company_website = ?,
                company_description = ?,
                company_size = ?,
                logo_url = ?
            WHERE company_id = ?
        ");

        $stmt->execute([
            $_POST['company_name'],
            $_POST['industry_id'],
            $_POST['company_phone'],
            $_POST['company_address'],
            $_POST['company_website'],
            $_POST['company_description'],
            $_POST['company_size'],
            $logo_url,
            $_SESSION['company_id']
        ]);

        $_SESSION['success'] = "Profile updated successfully!";
        header("Location: company_profile.php");
        exit();
    } catch(PDOException $e) {
        $_SESSION['error'] = "Error updating profile: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Profile - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background: #f8f9fc;
        }
        .profile-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
        }
        .logo-preview {
            width: 150px;
            height: 150px;
            object-fit: contain;
            border: 2px dashed #ddd;
            border-radius: 10px;
        }
        .btn-save {
            background: linear-gradient(135deg, #4e73df 0%, #2e59d9 100%);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 10px 30px;
        }
    </style>
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card profile-card">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Company Profile</h2>

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

                        <form method="POST" enctype="multipart/form-data">
                            <div class="text-center mb-4">
                                <img src="<?php echo $company['logo_url'] ?: 'assets/default-logo.png'; ?>" 
                                     alt="Company Logo" class="logo-preview mb-3" id="logoPreview">
                                <div>
                                    <input type="file" name="logo" id="logoInput" class="d-none" accept="image/*">
                                    <button type="button" class="btn btn-outline-primary btn-sm" onclick="document.getElementById('logoInput').click()">
                                        <i class="fas fa-upload me-2"></i>Change Logo
                                    </button>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Company Name</label>
                                    <input type="text" name="company_name" class="form-control" 
                                           value="<?php echo htmlspecialchars($company['company_name']); ?>" required>
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Industry</label>
                                    <select name="industry_id" class="form-control" required>
                                        <?php foreach ($industries as $industry): ?>
                                            <option value="<?php echo $industry['id']; ?>" 
                                                <?php echo $company['industry_id'] == $industry['id'] ? 'selected' : ''; ?>>
                                                <?php echo htmlspecialchars($industry['category_name']); ?>
                                            </option>
                                        <?php endforeach; ?>
                                    </select>
                                </div>
                            </div>

                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Phone</label>
                                    <input type="tel" name="company_phone" class="form-control" 
                                           value="<?php echo htmlspecialchars($company['company_phone']); ?>">
                                </div>

                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Website</label>
                                    <input type="url" name="company_website" class="form-control" 
                                           value="<?php echo htmlspecialchars($company['company_website']); ?>">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Address</label>
                                <textarea name="company_address" class="form-control" rows="2"><?php echo htmlspecialchars($company['company_address']); ?></textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Company Description</label>
                                <textarea name="company_description" class="form-control" rows="4"><?php echo htmlspecialchars($company['company_description']); ?></textarea>
                            </div>

                            <div class="mb-4">
                                <label class="form-label">Company Size</label>
                                <select name="company_size" class="form-control">
                                    <option value="1-10" <?php echo $company['company_size'] == '1-10' ? 'selected' : ''; ?>>1-10 employees</option>
                                    <option value="11-50" <?php echo $company['company_size'] == '11-50' ? 'selected' : ''; ?>>11-50 employees</option>
                                    <option value="51-200" <?php echo $company['company_size'] == '51-200' ? 'selected' : ''; ?>>51-200 employees</option>
                                    <option value="201-500" <?php echo $company['company_size'] == '201-500' ? 'selected' : ''; ?>>201-500 employees</option>
                                    <option value="501+" <?php echo $company['company_size'] == '501+' ? 'selected' : ''; ?>>501+ employees</option>
                                </select>
                            </div>

                            <div class="d-grid gap-2">
                                <button type="submit" class="btn btn-save">
                                    <i class="fas fa-save me-2"></i>Save Changes
                                </button>
                                <a href="company_dashboard.php" class="btn btn-light">Back to Dashboard</a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Logo preview
        document.getElementById('logoInput').onchange = function(e) {
            if (e.target.files && e.target.files[0]) {
                var reader = new FileReader();
                reader.onload = function(e) {
                    document.getElementById('logoPreview').src = e.target.result;
                }
                reader.readAsDataURL(e.target.files[0]);
            }
        };
    </script>
</body>
</html> 