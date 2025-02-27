<?php
session_start();
require_once 'config.php';

// Fetch degrees for dropdown
try {
    $stmt = $conn->prepare("SELECT id, degree_name FROM degrees ORDER BY degree_name");
    $stmt->execute();
    $degrees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Error loading degrees: " . $e->getMessage();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Add this before your existing validation
        $firstname = trim($_POST['firstname']);
        if (empty($firstname) || strpos($firstname, ' ') !== false) {
            throw new Exception("First name cannot contain spaces");
        }
        $_POST['firstname'] = $firstname; // Update the value to use in database insertion

        // Validate required fields
        $required_fields = ['firstname', 'lastname', 'email', 'password', 'confirm_password'];
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("All required fields must be filled out");
            }
        }

        // Validate email format
        if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
            throw new Exception("Invalid email format");
        }

        // Check if email already exists in student_profiles
        $stmt = $conn->prepare("SELECT id FROM student_profiles WHERE email = ?");
        $stmt->execute([$_POST['email']]);
        if ($stmt->rowCount() > 0) {
            throw new Exception("This email is already registered");
        }

        // Enhanced password validation
        $password = $_POST['password'];
        if (strlen($password) < 8) {
            throw new Exception("Password must be at least 8 characters long");
        }
        if (!preg_match('/[A-Z]/', $password)) {
            throw new Exception("Password must contain at least one uppercase letter");
        }
        if (!preg_match('/[a-z]/', $password)) {
            throw new Exception("Password must contain at least one lowercase letter");
        }
        if (!preg_match('/[0-9]/', $password)) {
            throw new Exception("Password must contain at least one number");
        }
        if (!preg_match('/[@#$%^&*]/', $password)) {
            throw new Exception("Password must contain at least one special character (@#$%^&*)");
        }

        if ($_POST['password'] !== $_POST['confirm_password']) {
            throw new Exception("Passwords do not match");
        }

        // Start transaction
        $conn->beginTransaction();

        // Hash the password
        $hashed_password = password_hash($_POST['password'], PASSWORD_DEFAULT);

        // First, insert into users table with correct columns
        $stmt = $conn->prepare("
            INSERT INTO users (
                firstname,
                lastname,
                email,
                password
            ) VALUES (?, ?, ?, ?)
        ");

        $stmt->execute([
            $_POST['firstname'],
            $_POST['lastname'],
            $_POST['email'],
            $hashed_password
        ]);

        // Get the user_id from the users table
        $user_id = $conn->lastInsertId();

        // Then insert into student_profiles table
        $stmt = $conn->prepare("
            INSERT INTO student_profiles (
                user_id,
                first_name,
                last_name,
                email,
                password,
                date_of_birth,
                gender,
                contact_number,
                address
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $stmt->execute([
            $user_id,
            $_POST['firstname'],
            $_POST['lastname'],
            $_POST['email'],
            $hashed_password,
            $_POST['date_of_birth'] ?? null,
            $_POST['gender'] ?? null,
            $_POST['contact_number'] ?? null,
            $_POST['address'] ?? null
        ]);

        // Commit transaction
        $conn->commit();

        $_SESSION['success'] = "Registration successful! Please login.";
        header("Location: login.php");
        exit();

    } catch (Exception $e) {
        // Rollback transaction on error
        if ($conn->inTransaction()) {
            $conn->rollBack();
        }
        $_SESSION['error'] = "Registration error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Registration - InternEvo</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        :root {
            --primary: #4e73df;
            --secondary: #2e59d9;
            --accent: #00ff88;
            --dark: #1a1a1a;
            --light: #ffffff;
            --error: #ff4444;
            --success: #00c851;
        }

        body {
            min-height: 100vh;
            background: linear-gradient(135deg, var(--dark), #2c3e50);
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated Background */
        .background-animation {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .circle {
            position: absolute;
            border-radius: 50%;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            filter: blur(50px);
            opacity: 0.15;
            animation: floatAnimation 20s infinite linear;
        }

        @keyframes floatAnimation {
            0% { transform: rotate(0deg) translate(0, 0) scale(1); }
            50% { transform: rotate(180deg) translate(100px, 100px) scale(1.2); }
            100% { transform: rotate(360deg) translate(0, 0) scale(1); }
        }

        .registration-container {
            width: 100%;
            max-width: 1000px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            padding: 2rem;
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            position: relative;
            z-index: 1;
            animation: containerAppear 0.6s ease forwards;
        }

        @keyframes containerAppear {
            from { transform: translateY(20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        .form-header {
            text-align: center;
            margin-bottom: 2rem;
            color: var(--light);
        }

        .form-header h2 {
            font-size: 2.5rem;
            margin-bottom: 1rem;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .form-header p {
            color: rgba(255, 255, 255, 0.7);
        }

        .form-row {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .form-group {
            flex: 1;
            position: relative;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: var(--light);
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 1rem 1rem 1rem 3rem;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.2);
            border-radius: 12px;
            color: var(--light);
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group i {
            position: absolute;
            left: 1rem;
            top: 2.7rem;
            color: var(--accent);
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            background: rgba(255, 255, 255, 0.15);
            border-color: var(--accent);
            outline: none;
            box-shadow: 0 0 20px rgba(0, 255, 136, 0.2);
        }

        .form-group input:focus + i {
            color: var(--light);
            transform: scale(1.1);
        }

        .submit-btn {
            width: 100%;
            padding: 1rem;
            background: linear-gradient(45deg, var(--primary), var(--accent));
            border: none;
            border-radius: 12px;
            color: var(--light);
            font-size: 1.1rem;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
            margin-top: 1rem;
        }

        .submit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(0, 255, 136, 0.3);
        }

        .login-link {
            text-align: center;
            margin-top: 1.5rem;
            color: var(--light);
        }

        .login-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .login-link a:hover {
            text-shadow: 0 0 10px var(--accent);
        }

        .alert {
            padding: 1rem;
            border-radius: 12px;
            margin-bottom: 1.5rem;
            animation: alertAppear 0.3s ease forwards;
        }

        .alert-danger {
            background: rgba(255, 68, 68, 0.1);
            border: 1px solid rgba(255, 68, 68, 0.2);
            color: var(--error);
        }

        .alert-success {
            background: rgba(0, 200, 81, 0.1);
            border: 1px solid rgba(0, 200, 81, 0.2);
            color: var(--success);
        }

        @keyframes alertAppear {
            from { transform: translateY(-10px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .form-row {
                flex-direction: column;
                gap: 0;
            }

            .registration-container {
                padding: 1.5rem;
            }
        }

        /* Add these new styles */
        .password-requirements {
            color: #fff;
            font-size: 0.85rem;
            margin-top: 0.5rem;
        }

        .requirement {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            margin: 0.2rem 0;
        }

        .requirement i {
            position: static;
            font-size: 0.8rem;
        }

        .requirement.valid {
            color: var(--success);
        }

        .requirement.invalid {
            color: var(--error);
        }
    </style>
</head>
<body>
    <div class="background-animation">
        <?php for($i = 0; $i < 3; $i++): ?>
            <div class="circle" style="
                width: <?php echo rand(300, 600); ?>px;
                height: <?php echo rand(300, 600); ?>px;
                left: <?php echo rand(-20, 120); ?>%;
                top: <?php echo rand(-20, 120); ?>%;
                animation-duration: <?php echo rand(15, 25); ?>s;
                animation-delay: -<?php echo rand(0, 10); ?>s;
            "></div>
        <?php endfor; ?>
    </div>

    <div class="registration-container">
        <div class="form-header">
            <h2>Student Registration</h2>
            <p>Join InternEvo and start your career journey</p>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-row">
                <div class="form-group">
                    <label>First Name</label>
                    <input type="text" name="firstname" id="firstname" required>
                    <i class="fas fa-user"></i>
                    <div class="name-requirements" id="firstnameError" style="color: var(--error); font-size: 0.85rem; margin-top: 0.5rem; display: none;">
                        Name cannot contain spaces
                    </div>
                </div>
                <div class="form-group">
                    <label>Last Name</label>
                    <input type="text" name="lastname" required>
                    <i class="fas fa-user"></i>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Email Address</label>
                    <input type="email" name="email" required>
                    <i class="fas fa-envelope"></i>
                </div>
                <div class="form-group">
                    <label>Phone Number</label>
                    <input type="tel" name="phone" required>
                    <i class="fas fa-phone"></i>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" id="password" required>
                    <i class="fas fa-lock"></i>
                    <div class="password-requirements">
                        <div class="requirement" data-requirement="length">
                            <i class="fas fa-circle"></i> At least 8 characters
                        </div>
                        <div class="requirement" data-requirement="uppercase">
                            <i class="fas fa-circle"></i> At least one uppercase letter
                        </div>
                        <div class="requirement" data-requirement="lowercase">
                            <i class="fas fa-circle"></i> At least one lowercase letter
                        </div>
                        <div class="requirement" data-requirement="number">
                            <i class="fas fa-circle"></i> At least one number
                        </div>
                        <div class="requirement" data-requirement="special">
                            <i class="fas fa-circle"></i> At least one special character (@#$%^&*)
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label>Confirm Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" required>
                    <i class="fas fa-lock"></i>
                    <div class="password-requirements">
                        <div class="requirement" data-requirement="match">
                            <i class="fas fa-circle"></i> Passwords match
                        </div>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>Date of Birth</label>
                    <input type="date" name="date_of_birth" required>
                    <i class="fas fa-calendar"></i>
                </div>
                <div class="form-group">
                    <label>Gender</label>
                    <select name="gender" required>
                        <option value="">Select Gender</option>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                        <option value="Other">Other</option>
                    </select>
                    <i class="fas fa-venus-mars"></i>
                </div>
            </div>

            <button type="submit" class="submit-btn">
                <i class="fas fa-user-plus me-2"></i> Register Now
            </button>

            <div class="login-link">
                Already have an account? <a href="login.php">Login here</a>
            </div>
        </form>
    </div>

    <script>
        // Add interactive particle effect
        document.addEventListener('mousemove', (e) => {
            const circles = document.querySelectorAll('.circle');
            circles.forEach(circle => {
                const speed = 0.5;
                const x = (window.innerWidth - e.pageX * speed) / 100;
                const y = (window.innerHeight - e.pageY * speed) / 100;
                circle.style.transform = `translate(${x}px, ${y}px)`;
            });
        });

        document.addEventListener('DOMContentLoaded', function() {
            const password = document.getElementById('password');
            const confirmPassword = document.getElementById('confirm_password');
            const submitBtn = document.querySelector('.submit-btn');
            const requirements = document.querySelectorAll('.requirement');
            
            const validatePassword = () => {
                const pass = password.value;
                const validations = {
                    length: pass.length >= 8,
                    uppercase: /[A-Z]/.test(pass),
                    lowercase: /[a-z]/.test(pass),
                    number: /[0-9]/.test(pass),
                    special: /[@#$%^&*]/.test(pass)
                };

                let allValid = true;
                
                requirements.forEach(req => {
                    const type = req.dataset.requirement;
                    if (type in validations) {
                        if (validations[type]) {
                            req.classList.add('valid');
                            req.classList.remove('invalid');
                            req.querySelector('i').className = 'fas fa-check';
                        } else {
                            req.classList.add('invalid');
                            req.classList.remove('valid');
                            req.querySelector('i').className = 'fas fa-circle';
                            allValid = false;
                        }
                    }
                });

                return allValid;
            };

            const validatePasswordMatch = () => {
                const matchRequirement = document.querySelector('[data-requirement="match"]');
                const isMatch = password.value === confirmPassword.value && password.value !== '';
                
                if (isMatch) {
                    matchRequirement.classList.add('valid');
                    matchRequirement.classList.remove('invalid');
                    matchRequirement.querySelector('i').className = 'fas fa-check';
                } else {
                    matchRequirement.classList.add('invalid');
                    matchRequirement.classList.remove('valid');
                    matchRequirement.querySelector('i').className = 'fas fa-circle';
                }

                return isMatch;
            };

            const validateForm = () => {
                const isPasswordValid = validatePassword();
                const isMatchValid = validatePasswordMatch();
                submitBtn.disabled = !(isPasswordValid && isMatchValid);
            };

            password.addEventListener('input', validateForm);
            confirmPassword.addEventListener('input', validateForm);

            // Update PHP validation to match JavaScript validation
            document.querySelector('form').addEventListener('submit', function(e) {
                if (!validatePassword() || !validatePasswordMatch()) {
                    e.preventDefault();
                    alert('Please ensure all password requirements are met.');
                }
            });

            // Add first name validation
            const firstname = document.getElementById('firstname');
            const firstnameError = document.getElementById('firstnameError');
            const form = document.querySelector('form');

            firstname.addEventListener('input', function(e) {
                const value = e.target.value;
                
                // Remove spaces as they're typed
                if (value.includes(' ')) {
                    this.value = value.replace(/\s/g, '');
                    firstnameError.style.display = 'block';
                } else {
                    firstnameError.style.display = 'none';
                }
            });

            // Add form validation
            form.addEventListener('submit', function(e) {
                const firstnameValue = firstname.value.trim();

                if (firstnameValue.includes(' ') || firstnameValue.length === 0) {
                    e.preventDefault();
                    firstnameError.style.display = 'block';
                    firstname.focus();
                }
            });
        });
    </script>
</body>
</html> 