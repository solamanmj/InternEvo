<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Fetch current student details
try {
    $stmt = $conn->prepare("SELECT * FROM student_profiles WHERE user_id = ? AND email = ?");
    $stmt->execute([
        $_SESSION['user_id'],
        $_SESSION['email']
    ]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if (!$student) {
        header("Location: login.php");
        exit();
    }
} catch(PDOException $e) {
    header("Location: error.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    try {
        $stmt = $conn->prepare("
            UPDATE student_profiles 
            SET 
                first_name = ?,
                last_name = ?,
                date_of_birth = ?,
                gender = ?,
                address = ?,
                college = ?,
                course = ?,
                graduation_year = ?,
                about_me = ?,
                skills = ?
            WHERE user_id = ? AND email = ?
        ");

        $stmt->execute([
            $_POST['first_name'],
            $_POST['last_name'],
            $_POST['date_of_birth'],
            $_POST['gender'],
            $_POST['address'],
            $_POST['college'],
            $_POST['course'],
            $_POST['graduation_year'],
            $_POST['about_me'],
            $_POST['skills'],
            $_SESSION['user_id'],
            $_SESSION['email']
        ]);

        header("Location: student_profile.php");
        exit();
    } catch(PDOException $e) {
        $error = "Error updating profile";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        :root {
            --primary: #4e73df;
            --secondary: #2e59d9;
            --accent: #00ff88;
            --dark-bg: #0f172a;
            --card-bg: #1e293b;
            --text-primary: #ffffff;
            --text-secondary: #94a3b8;
            --input-bg: #2a3441;
            --border-color: #334155;
        }

        body {
            background: linear-gradient(135deg, var(--dark-bg) 0%, #1a1a2e 100%);
            color: var(--text-primary);
            min-height: 100vh;
            font-family: 'Segoe UI', system-ui, -apple-system, sans-serif;
            padding: 2rem;
            position: relative;
            overflow-x: hidden;
        }

        /* Animated background effect */
        body::before {
            content: '';
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 20% 30%, rgba(78, 115, 223, 0.1) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(0, 255, 136, 0.05) 0%, transparent 50%);
            animation: backgroundShift 15s ease-in-out infinite alternate;
            z-index: -1;
        }

        @keyframes backgroundShift {
            0% { transform: scale(1); }
            100% { transform: scale(1.1); }
        }

        .profile-container {
            max-width: 1200px;
            margin: 0 auto;
            background: var(--card-bg);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.2);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            overflow: hidden;
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.6s ease forwards;
        }

        @keyframes fadeInUp {
            to {
                transform: translateY(0);
                opacity: 1;
            }
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 2rem;
            position: relative;
            overflow: hidden;
        }

        .profile-header::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, transparent 0%, rgba(255,255,255,0.1) 100%);
            animation: shimmer 2s infinite;
        }

        @keyframes shimmer {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .section {
            padding: 2rem;
            border-bottom: 1px solid var(--border-color);
            animation: fadeIn 0.6s ease forwards;
        }

        .section-title {
            color: var(--accent);
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.5rem;
        }

        .form-control {
            background: var(--input-bg);
            border: 1px solid var(--border-color);
            color: var(--text-primary);
            border-radius: 10px;
            padding: 0.75rem 1rem;
            transition: all 0.3s ease;
        }

        .form-control:focus {
            background: var(--input-bg);
            border-color: var(--accent);
            box-shadow: 0 0 0 2px rgba(0, 255, 136, 0.2);
            color: var(--text-primary);
        }

        .form-control::placeholder {
            color: var(--text-secondary);
        }

        .btn-primary {
            background: var(--accent);
            border: none;
            color: var(--dark-bg);
            padding: 0.75rem 2rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(0, 255, 136, 0.4);
            background: var(--accent);
        }

        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 1.5rem;
        }

        label {
            color: var(--text-secondary);
            margin-bottom: 0.5rem;
            display: block;
        }

        /* 4K optimization */
        @media (min-width: 2560px) {
            body {
                font-size: 18px;
            }

            .profile-container {
                max-width: 2000px;
                padding: 3rem;
            }

            .form-control {
                padding: 1rem 1.5rem;
                font-size: 1.1rem;
            }

            .btn-primary {
                padding: 1rem 3rem;
                font-size: 1.1rem;
            }
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Custom scrollbar */
        ::-webkit-scrollbar {
            width: 10px;
        }

        ::-webkit-scrollbar-track {
            background: var(--dark-bg);
        }

        ::-webkit-scrollbar-thumb {
            background: var(--border-color);
            border-radius: 5px;
        }

        ::-webkit-scrollbar-thumb:hover {
            background: var(--accent);
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <a href="student_profile.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h2 class="mb-2">Edit Profile</h2>
        </div>

        <form method="POST" class="section">
            <!-- Personal Information -->
            <h3 class="section-title">
                <i class="fas fa-user"></i> Personal Information
            </h3>
            <div class="info-grid">
                <div class="mb-3">
                    <label>First Name</label>
                    <input type="text" name="first_name" class="form-control" 
                           value="<?php echo htmlspecialchars($student['first_name'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label>Last Name</label>
                    <input type="text" name="last_name" class="form-control" 
                           value="<?php echo htmlspecialchars($student['last_name'] ?? ''); ?>" required>
                </div>
                <div class="mb-3">
                    <label>Date of Birth</label>
                    <input type="date" name="date_of_birth" class="form-control" 
                           value="<?php echo htmlspecialchars($student['date_of_birth'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label>Gender</label>
                    <select name="gender" class="form-control">
                        <option value="">Select Gender</option>
                        <option value="Male" <?php echo ($student['gender'] ?? '') == 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo ($student['gender'] ?? '') == 'Female' ? 'selected' : ''; ?>>Female</option>
                        <option value="Other" <?php echo ($student['gender'] ?? '') == 'Other' ? 'selected' : ''; ?>>Other</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label>Address</label>
                    <textarea name="address" class="form-control" rows="3"><?php echo htmlspecialchars($student['address'] ?? ''); ?></textarea>
                </div>
            </div>

            <!-- Academic Information -->
            <h3 class="section-title mt-4">
                <i class="fas fa-graduation-cap"></i> Academic Information
            </h3>
            <div class="info-grid">
                <div class="mb-3">
                    <label>College</label>
                    <input type="text" name="college" class="form-control" 
                           value="<?php echo htmlspecialchars($student['college'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label>Course</label>
                    <input type="text" name="course" class="form-control" 
                           value="<?php echo htmlspecialchars($student['course'] ?? ''); ?>">
                </div>
                <div class="mb-3">
                    <label>Graduation Year</label>
                    <input type="number" name="graduation_year" class="form-control" 
                           value="<?php echo htmlspecialchars($student['graduation_year'] ?? ''); ?>">
                </div>
            </div>

            <!-- About Me -->
            <h3 class="section-title mt-4">
                <i class="fas fa-user-circle"></i> About Me
            </h3>
            <div class="mb-3">
                <textarea name="about_me" class="form-control" rows="4"><?php echo htmlspecialchars($student['about_me'] ?? ''); ?></textarea>
            </div>

            <!-- Skills -->
            <h3 class="section-title mt-4">
                <i class="fas fa-laptop-code"></i> Skills
            </h3>
            <div class="mb-3">
                <input type="text" name="skills" class="form-control" 
                       value="<?php echo htmlspecialchars($student['skills'] ?? ''); ?>" 
                       placeholder="Enter skills separated by commas">
            </div>

            <button type="submit" class="btn btn-primary mt-4">
                <i class="fas fa-save"></i> Save Changes
            </button>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 