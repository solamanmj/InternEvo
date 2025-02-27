<?php
session_start();
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

// TEMPORARY DATABASE CHECK - REMOVE AFTER FIXING
try {
    $email = 'test@example.com'; // Replace with an email you know exists
    
    // Check student table
    $stmt = $conn->prepare("SELECT * FROM student_profiles WHERE email = ?");
    $stmt->execute([$email]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($student) {
        error_log("Found student: " . print_r($student, true));
    }
    
    // Check company table
    $stmt = $conn->prepare("SELECT * FROM company_profiles WHERE company_email = ?");
    $stmt->execute([$email]);
    $company = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($company) {
        error_log("Found company: " . print_r($company, true));
    }
} catch (Exception $e) {
    error_log("Database check error: " . $e->getMessage());
}

// TEMPORARY DEBUGGING - REMOVE AFTER FIXING
try {
    // Check student_profiles
    $debug_stmt = $conn->query("SELECT email, password FROM student_profiles");
    error_log("=== Student Profiles ===");
    while($row = $debug_stmt->fetch()) {
        error_log("Email: " . $row['email'] . " | Password: " . substr($row['password'], 0, 20) . "...");
    }
    
    // Check company_profiles
    $debug_stmt = $conn->query("SELECT company_email, password FROM company_profiles");
    error_log("=== Company Profiles ===");
    while($row = $debug_stmt->fetch()) {
        error_log("Email: " . $row['company_email'] . " | Password: " . substr($row['password'], 0, 20) . "...");
    }
} catch (Exception $e) {
    error_log("Debug query error: " . $e->getMessage());
}
// END TEMPORARY DEBUGGING

// Debug: Log request method and POST data
error_log("Request Method: " . $_SERVER['REQUEST_METHOD']);
if ($_POST) {
    error_log("POST Data: " . print_r($_POST, true));
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $email = trim($_POST['email']);
        $password = $_POST['password'];

        // Find user in student_profiles table
        $stmt = $conn->prepare("SELECT * FROM student_profiles WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user && password_verify($password, $user['password'])) {
            // Set all necessary session variables
            $_SESSION['logged_in'] = true;
            $_SESSION['user_id'] = $user['user_id'];
            $_SESSION['user_type'] = 'student';
            $_SESSION['name'] = $user['first_name'] . ' ' . $user['last_name'];
            $_SESSION['email'] = $user['email'];
            
            // Always redirect to student_dashboard.php after successful login
            header("Location: student_dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password";
            header("Location: login.php");
            exit();
        }
        
    } catch (Exception $e) {
        $_SESSION['error'] = "An error occurred";
        header("Location: login.php");
        exit();
    }
}

// Debug: Show current database state
try {
    error_log("=== Current Database State ===");
    
    // Check student_profiles table
    $stmt = $conn->query("SELECT * FROM student_profiles");
    error_log("Student profiles table:");
    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
        error_log(print_r($row, true));
    }
    
} catch (Exception $e) {
    error_log("Debug query error: " . $e->getMessage());
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InternEvo - Login</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4e73df;
            --secondary: #2e59d9;
            --accent: #00ff88;
            --dark: #1a1a1a;
            --light: #ffffff;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', sans-serif;
        }

        body {
            background: var(--dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }

        .background {
            position: fixed;
            width: 200%;
            height: 200%;
            top: -50%;
            left: -50%;
            z-index: 0;
            animation: background-rotate 15s linear infinite;
            background: radial-gradient(circle, 
                rgba(78,115,223,0.1) 0%, 
                rgba(46,89,217,0.1) 50%, 
                rgba(0,255,136,0.1) 100%
            );
        }

        @keyframes background-rotate {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        .login-container {
            position: relative;
            z-index: 1;
            width: 400px;
            backdrop-filter: blur(20px);
            background: rgba(255, 255, 255, 0.05);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 0 40px rgba(0,0,0,0.3);
            border: 1px solid rgba(255,255,255,0.1);
            transform: translateY(20px);
            opacity: 0;
            animation: container-appear 0.6s ease forwards;
        }

        @keyframes container-appear {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .logo {
            text-align: center;
            margin-bottom: 30px;
            color: var(--light);
            font-size: 2em;
            text-transform: uppercase;
            letter-spacing: 2px;
        }

        .logo i {
            color: var(--accent);
            font-size: 1.2em;
            animation: rocket-shake 2s ease infinite;
        }

        @keyframes rocket-shake {
            0%, 100% { transform: rotate(-10deg); }
            50% { transform: rotate(10deg); }
        }

        .input-group {
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
        }

        .input-group input {
            width: 100%;
            padding: 15px 15px 15px 45px;
            background: rgba(255,255,255,0.05);
            border: none;
            border-radius: 10px;
            color: var(--light);
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .input-group input:focus {
            background: rgba(255,255,255,0.1);
            outline: none;
            box-shadow: 0 0 20px rgba(0,255,136,0.3);
        }

        .input-group i {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--accent);
            font-size: 20px;
        }

        .login-btn {
            width: 100%;
            padding: 15px;
            background: var(--accent);
            border: none;
            border-radius: 10px;
            color: var(--dark);
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .login-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(0,255,136,0.4);
        }

        .login-btn i {
            margin-right: 10px;
        }

        .forgot-password {
            text-align: center;
            margin-bottom: 20px;
            color: var(--light);
        }

        .forgot-password a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .forgot-password a:hover {
            text-shadow: 0 0 10px var(--accent);
        }

        .register-link {
            text-align: center;
            margin-top: 20px;
            color: var(--light);
        }

        .register-link a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .register-link a:hover {
            text-shadow: 0 0 10px var(--accent);
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 10px;
            color: var(--light);
            background: rgba(255,0,0,0.2);
            border: 1px solid rgba(255,0,0,0.3);
            animation: alert-appear 0.3s ease forwards;
        }

        @keyframes alert-appear {
            from {
                transform: translateY(-10px);
                opacity: 0;
            }
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .floating-particles {
            position: fixed;
            width: 100%;
            height: 100%;
            top: 0;
            left: 0;
            pointer-events: none;
            z-index: 0;
        }

        .particle {
            position: absolute;
            width: 4px;
            height: 4px;
            background: var(--accent);
            border-radius: 50%;
            animation: float-up 10s linear infinite;
            opacity: 0.3;
        }

        @keyframes float-up {
            0% {
                transform: translateY(100vh) scale(0);
                opacity: 0;
            }
            50% {
                opacity: 0.5;
            }
            100% {
                transform: translateY(-100vh) scale(1);
                opacity: 0;
            }
        }

        input:-webkit-autofill,
        input:-webkit-autofill:hover,
        input:-webkit-autofill:focus,
        input:-webkit-autofill:active {
            -webkit-box-shadow: 0 0 0 30px var(--dark) inset !important;
            -webkit-text-fill-color: var(--light) !important;
            transition: background-color 5000s ease-in-out 0s;
        }

        .form-control {
            background-color: rgba(255, 255, 255, 0.05) !important;
            border: 1px solid rgba(255, 255, 255, 0.1);
            color: var(--light) !important;
        }

        .form-control:focus {
            background-color: rgba(255, 255, 255, 0.08) !important;
            border-color: var(--accent);
            box-shadow: 0 0 0 0.2rem rgba(52, 152, 219, 0.25);
        }

        .form-control::placeholder {
            color: rgba(255, 255, 255, 0.5);
        }

        .form-control:focus::placeholder {
            color: rgba(255, 255, 255, 0.7);
        }
    </style>
</head>
<body>
    <div class="background"></div>
    <div class="floating-particles">
        <?php for($i = 0; $i < 50; $i++): ?>
            <div class="particle" style="
                left: <?php echo rand(0, 100); ?>%;
                animation-delay: <?php echo rand(0, 5000)/1000; ?>s;
                animation-duration: <?php echo rand(5000, 15000)/1000; ?>s;
            "></div>
        <?php endfor; ?>
    </div>

    <div class="login-container">
        <div class="logo">
            <i class="fas fa-rocket"></i>
            <span>InternEvo</span>
        </div>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="input-group">
                <i class="fas fa-envelope"></i>
                <input type="email" name="email" placeholder="Email Address" required>
            </div>

            <div class="input-group">
                <i class="fas fa-lock"></i>
                <input type="password" name="password" placeholder="Password" required>
            </div>

            <button type="submit" class="login-btn">
                <i class="fas fa-sign-in-alt"></i>
                Login
            </button>

            <div class="forgot-password">
                <a href="forgot_password.php">Forgot Password?</a>
            </div>

            <div class="register-link">
                <p>Don't have an account? <a href="register_student.php">Register here</a></p>
            </div>
        </form>
    </div>

    <script>
        // Add hover effect to particles near cursor
        document.addEventListener('mousemove', (e) => {
            const particles = document.querySelectorAll('.particle');
            particles.forEach(particle => {
                const rect = particle.getBoundingClientRect();
                const x = rect.left + rect.width/2;
                const y = rect.top + rect.height/2;
                const distance = Math.sqrt(
                    Math.pow(e.clientX - x, 2) + 
                    Math.pow(e.clientY - y, 2)
                );
                
                if (distance < 100) {
                    const scale = 1 + (100 - distance)/100;
                    particle.style.transform = `scale(${scale})`;
                    particle.style.opacity = '0.8';
                } else {
                    particle.style.transform = '';
                    particle.style.opacity = '0.3';
                }
            });
        });

        // Add form submission debugging
        document.querySelector('form').addEventListener('submit', function(e) {
            console.log('Form submitted');
        });
    </script>
</body>
</html>