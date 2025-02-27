<?php
session_start();
require_once 'config.php';

// Fetch industry categories for the dropdown
try {
    $stmt = $conn->prepare("SELECT id, category_name FROM industry_categories ORDER BY category_name");
    $stmt->execute();
    $industries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Error loading industries: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Get and sanitize form data
        $company_name = trim($_POST['company_name']);
        $email = trim($_POST['company_email']);
        $password = trim($_POST['password']);
        $industry_id = $_POST['industry_id'];
        $company_address = trim($_POST['company_address']);
        $company_phone = trim($_POST['company_phone']);
        $company_website = trim($_POST['company_website']);
        $company_description = trim($_POST['company_description']);
        $company_size = trim($_POST['company_size']);

        // Validate email format
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check if email exists - do this FIRST
        $check_stmt = $conn->prepare("SELECT COUNT(*) FROM company_profiles WHERE company_email = ?");
        $check_stmt->execute([$email]);
        $email_exists = $check_stmt->fetchColumn();

        if ($email_exists > 0) {
            throw new Exception("This email is already registered. Please use a different email or login to your existing account.");
        }

        // Handle file upload if exists
        $logo_url = '';
        if (isset($_FILES['logo']) && $_FILES['logo']['error'] === UPLOAD_ERR_OK) {
            $upload_dir = 'uploads/company_logos/';
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $file_extension = strtolower(pathinfo($_FILES['logo']['name'], PATHINFO_EXTENSION));
            $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
            
            if (!in_array($file_extension, $allowed_extensions)) {
                throw new Exception("Invalid file type. Only JPG, JPEG, PNG & GIF files are allowed.");
            }

            $new_filename = uniqid() . '.' . $file_extension;
            $target_path = $upload_dir . $new_filename;
            
            if (!move_uploaded_file($_FILES['logo']['tmp_name'], $target_path)) {
                throw new Exception("Error uploading logo");
            }
            
            $logo_url = $target_path;
        }

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Set initial status
        $status = 'pending';

        // Begin transaction
        $conn->beginTransaction();

        // Insert company
        $stmt = $conn->prepare("
            INSERT INTO company_profiles (
                company_name, 
                company_email, 
                password, 
                industry_id, 
                company_address,
                company_phone,
                company_website,
                company_description,
                company_size,
                logo_url, 
                status, 
                created_at
            ) VALUES (
                :company_name, 
                :company_email, 
                :password, 
                :industry_id, 
                :company_address,
                :company_phone,
                :company_website,
                :company_description,
                :company_size,
                :logo_url, 
                :status, 
                NOW()
            )
        ");

        $stmt->bindParam(':company_name', $company_name);
        $stmt->bindParam(':company_email', $email);
        $stmt->bindParam(':password', $hashed_password);
        $stmt->bindParam(':industry_id', $industry_id);
        $stmt->bindParam(':company_address', $company_address);
        $stmt->bindParam(':company_phone', $company_phone);
        $stmt->bindParam(':company_website', $company_website);
        $stmt->bindParam(':company_description', $company_description);
        $stmt->bindParam(':company_size', $company_size);
        $stmt->bindParam(':logo_url', $logo_url);
        $stmt->bindParam(':status', $status);

        $stmt->execute();
        
        // Commit transaction
        $conn->commit();

        // Set success message and redirect
        $_SESSION['registration_success'] = "Registration successful! Check your application status below.";
        $_SESSION['registered_email'] = $email;
        header("Location: check_status.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction if active
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        
        $_SESSION['error'] = $e->getMessage();
        header("Location: register_company.php");
        exit();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Registration</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');

        body {
            font-family: 'Poppins', sans-serif;
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            background: linear-gradient(125deg, #00072D, #000C40, #001F3F);
            background-size: 400% 400%;
            animation: gradientBG 15s ease infinite;
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .registration-container {
            max-width: 600px;
            margin: 50px auto;
            background: rgba(255, 255, 255, 0.05);
            padding: 40px;
            border-radius: 24px;
            box-shadow: 
                0 8px 32px 0 rgba(31, 38, 135, 0.37),
                inset 0 0 32px rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(8px);
            -webkit-backdrop-filter: blur(8px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            overflow: hidden;
        }

        .registration-container::before {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, transparent 60%);
            animation: lightEffect 10s linear infinite;
            pointer-events: none;
        }

        @keyframes lightEffect {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        h2 {
            color: #ffffff;
            text-align: center;
            margin-bottom: 40px;
            font-size: 2.5em;
            font-weight: 600;
            text-shadow: 2px 2px 4px rgba(0, 0, 0, 0.3),
                         0 0 20px rgba(255, 255, 255, 0.2);
            letter-spacing: 2px;
        }

        .form-group {
            margin-bottom: 25px;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 10px;
            font-weight: 500;
            color: #ffffff;
            text-shadow: 1px 1px 2px rgba(0, 0, 0, 0.3);
            font-size: 0.95em;
            letter-spacing: 0.5px;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 15px;
            background: rgba(255, 255, 255, 0.07);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 12px;
            color: #ffffff;
            font-size: 16px;
            transition: all 0.4s ease;
            backdrop-filter: blur(4px);
            box-shadow: inset 0 0 10px rgba(255, 255, 255, 0.05);
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: rgba(255, 255, 255, 0.3);
            box-shadow: 
                0 0 20px rgba(255, 255, 255, 0.1),
                inset 0 0 15px rgba(255, 255, 255, 0.1);
            transform: translateY(-2px);
        }

        .form-group textarea {
            height: 120px;
            resize: vertical;
        }

        .form-group select option {
            background: #1a1f36;
            color: #ffffff;
            padding: 10px;
        }

        button {
            width: 100%;
            padding: 16px;
            background: linear-gradient(45deg, #00c6ff, #0072ff);
            color: white;
            border: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.4s ease;
            text-transform: uppercase;
            letter-spacing: 2px;
            box-shadow: 0 4px 15px rgba(0, 114, 255, 0.3);
            position: relative;
            overflow: hidden;
        }

        button::before {
            content: '';
            position: absolute;
            top: 0;
            left: -100%;
            width: 100%;
            height: 100%;
            background: linear-gradient(
                90deg,
                transparent,
                rgba(255, 255, 255, 0.2),
                transparent
            );
            transition: 0.5s;
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 25px rgba(0, 114, 255, 0.4);
        }

        button:hover::before {
            left: 100%;
        }

        .validation-message {
            font-size: 12px;
            margin-top: 8px;
            display: none;
            color: #ffffff;
            background: rgba(0, 0, 0, 0.4);
            padding: 6px 12px;
            border-radius: 6px;
            backdrop-filter: blur(4px);
            position: absolute;
            right: 0;
            top: 100%;
        }

        .validation-message.error {
            background: rgba(255, 71, 87, 0.2);
            border: 1px solid rgba(255, 71, 87, 0.3);
            color: #ff4757;
            display: block;
        }

        .validation-message.success {
            background: rgba(46, 213, 115, 0.2);
            border: 1px solid rgba(46, 213, 115, 0.3);
            color: #2ed573;
            display: block;
        }

        .password-strength {
            margin-top: 10px;
            display: flex;
            gap: 6px;
        }

        .strength-bar {
            height: 4px;
            flex: 1;
            background: rgba(255, 255, 255, 0.1);
            border-radius: 2px;
            transition: all 0.4s ease;
        }

        .strength-bar.weak {
            background: linear-gradient(90deg, #ff4757, #ff6b81);
            box-shadow: 0 0 10px rgba(255, 71, 87, 0.4);
        }

        .strength-bar.medium {
            background: linear-gradient(90deg, #ffa502, #ff7f50);
            box-shadow: 0 0 10px rgba(255, 165, 2, 0.4);
        }

        .strength-bar.strong {
            background: linear-gradient(90deg, #2ed573, #7bed9f);
            box-shadow: 0 0 10px rgba(46, 213, 115, 0.4);
        }

        input[type="file"] {
            background: rgba(255, 255, 255, 0.05);
            border: 2px dashed rgba(255, 255, 255, 0.2);
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        input[type="file"]:hover {
            border-color: rgba(255, 255, 255, 0.3);
            background: rgba(255, 255, 255, 0.07);
        }

        .login-link {
            text-align: center;
            margin-top: 25px;
            color: #ffffff;
            font-size: 0.95em;
        }

        .login-link a {
            color: #00c6ff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            position: relative;
        }

        .login-link a::after {
            content: '';
            position: absolute;
            width: 100%;
            height: 2px;
            bottom: -2px;
            left: 0;
            background: linear-gradient(90deg, #00c6ff, #0072ff);
            transform: scaleX(0);
            transform-origin: right;
            transition: transform 0.3s ease;
        }

        .login-link a:hover::after {
            transform: scaleX(1);
            transform-origin: left;
        }

        /* Animated background circles */
        .registration-container::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: radial-gradient(circle at 50% 50%, rgba(255,255,255,0.1) 0%, transparent 50%);
            z-index: -1;
            animation: pulse 4s ease infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); opacity: 0.5; }
            50% { transform: scale(1.5); opacity: 0.7; }
            100% { transform: scale(1); opacity: 0.5; }
        }
    </style>
</head>
<body>
    <div class="registration-container">
        <h2>Company Registration</h2>
        
        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" enctype="multipart/form-data">
            <div class="form-group">
                <label>Company Name</label>
                <input type="text" name="company_name" required>
            </div>

            <div class="form-group">
                <label>Industry</label>
                <select name="industry_id" required>
                    <option value="">Select Industry</option>
                    <?php foreach ($industries as $industry): ?>
                        <option value="<?php echo $industry['id']; ?>">
                            <?php echo htmlspecialchars($industry['category_name']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label>Email Address</label>
                <input type="email" name="company_email" required>
            </div>

            <div class="form-group">
                <label>Phone Number</label>
                <input type="tel" name="company_phone" required>
            </div>

            <div class="form-group">
                <label>Address</label>
                <textarea name="company_address" required></textarea>
            </div>

            <div class="form-group">
                <label>Website</label>
                <input type="url" name="company_website" required>
            </div>

            <div class="form-group">
                <label>Company Description</label>
                <textarea name="company_description" required></textarea>
            </div>

            <div class="form-group">
                <label>Company Size</label>
                <select name="company_size" required>
                    <option value="">Select Size</option>
                    <option value="1-10">1-10 employees</option>
                    <option value="11-50">11-50 employees</option>
                    <option value="51-200">51-200 employees</option>
                    <option value="201-500">201-500 employees</option>
                    <option value="501+">501+ employees</option>
                </select>
            </div>

            <div class="form-group">
                <label>Company Logo</label>
                <input type="file" name="logo" accept="image/*">
            </div>

            <div class="form-group">
                <label>Password</label>
                <input type="password" name="password" required>
            </div>

            <button type="submit">Register Company</button>
        </form>

        <div class="login-link">
            Already have an account? <a href="company_login.php">Login here</a>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Get form elements
        const companyNameInput = document.querySelector('input[name="company_name"]');
        const phoneInput = document.querySelector('input[name="company_phone"]');
        const passwordInput = document.querySelector('input[name="password"]');

        // Add validation message elements
        function addValidationMessage(input) {
            const message = document.createElement('div');
            message.className = 'validation-message';
            input.parentNode.appendChild(message);
            return message;
        }

        // Company Name Validation
        const companyNameValidation = addValidationMessage(companyNameInput);
        companyNameInput.addEventListener('input', function() {
            const value = this.value.trim();
            let message = '';

            if (value === '') {
                message = 'Company name is required';
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (/\s{2,}/.test(value)) {
                message = 'Multiple consecutive spaces are not allowed';
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                message = 'Valid company name';
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }

            companyNameValidation.textContent = message;
            companyNameValidation.className = 'validation-message ' + 
                (this.classList.contains('is-invalid') ? 'error' : 'success');
        });

        // Phone Number Validation
        const phoneValidation = addValidationMessage(phoneInput);
        phoneInput.addEventListener('input', function(e) {
            // Remove non-digits
            let value = this.value.replace(/\D/g, '');
            
            // Format number as XXX-XXX-XXXX
            if (value.length > 0) {
                if (value.length <= 3) {
                    value = value;
                } else if (value.length <= 6) {
                    value = value.slice(0,3) + '-' + value.slice(3);
                } else {
                    value = value.slice(0,3) + '-' + value.slice(3,6) + '-' + value.slice(6,10);
                }
                this.value = value;
            }

            // Validate length
            const digitsOnly = this.value.replace(/\D/g, '');
            let message = '';

            if (digitsOnly.length === 0) {
                message = 'Phone number is required';
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else if (digitsOnly.length !== 10) {
                message = 'Phone number must be 10 digits';
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                message = 'Valid phone number';
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }

            phoneValidation.textContent = message;
            phoneValidation.className = 'validation-message ' + 
                (this.classList.contains('is-invalid') ? 'error' : 'success');
        });

        // Password Validation
        const passwordValidation = addValidationMessage(passwordInput);
        
        // Add strength indicator
        const strengthIndicator = document.createElement('div');
        strengthIndicator.className = 'password-strength';
        for (let i = 0; i < 3; i++) {
            const bar = document.createElement('div');
            bar.className = 'strength-bar';
            strengthIndicator.appendChild(bar);
        }
        passwordInput.parentNode.appendChild(strengthIndicator);
        
        passwordInput.addEventListener('input', function() {
            const password = this.value;
            const strengthBars = strengthIndicator.querySelectorAll('.strength-bar');
            let strength = 0;
            let message = '';
            const requirements = [];

            // Check length
            if (password.length < 8) {
                requirements.push('at least 8 characters');
            } else {
                strength++;
            }

            // Check lowercase and uppercase
            if (!/[a-z]/.test(password) || !/[A-Z]/.test(password)) {
                requirements.push('both uppercase and lowercase letters');
            } else {
                strength++;
            }

            // Check numbers and special characters
            if (!/[0-9]/.test(password) || !/[!@#$%^&*]/.test(password)) {
                requirements.push('at least one number and one special character');
            } else {
                strength++;
            }

            // Update strength bars
            strengthBars.forEach((bar, index) => {
                bar.className = 'strength-bar';
                if (index < strength) {
                    bar.classList.add(
                        strength === 1 ? 'weak' :
                        strength === 2 ? 'medium' : 'strong'
                    );
                }
            });

            // Update validation message
            if (requirements.length > 0) {
                message = 'Password must contain: ' + requirements.join(', ');
                this.classList.add('is-invalid');
                this.classList.remove('is-valid');
            } else {
                message = 'Strong password';
                this.classList.add('is-valid');
                this.classList.remove('is-invalid');
            }

            passwordValidation.textContent = message;
            passwordValidation.className = 'validation-message ' + 
                (requirements.length > 0 ? 'error' : 'success');
        });

        // Form submission validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const companyName = companyNameInput.value.trim();
            const phone = phoneInput.value.replace(/\D/g, '');
            const password = passwordInput.value;
            
            if (companyName === '' || 
                phone.length !== 10 || 
                password.length < 8 || 
                !/[a-z]/.test(password) || 
                !/[A-Z]/.test(password) || 
                !/[0-9]/.test(password) || 
                !/[!@#$%^&*]/.test(password)) {
                e.preventDefault();
                alert('Please fix all validation errors before submitting.');
            }
        });
    });
    </script>
</body>
</html>