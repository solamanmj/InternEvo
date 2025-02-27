<?php
session_start();
require_once 'config.php';

// If accessed directly without login, redirect to login page
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// If coming directly after login, redirect to dashboard
if (isset($_SERVER['HTTP_REFERER']) && strpos($_SERVER['HTTP_REFERER'], 'login.php') !== false) {
    header("Location: student_dashboard.php");
    exit();
}

// Fetch student details
try {
    $stmt = $conn->prepare("
        SELECT * FROM student_profiles 
        WHERE user_id = ? 
        AND email = ?");
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
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Profile</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: #fff;
            min-height: 100vh;
        }
        .profile-container {
            max-width: 1200px;
            margin: 40px auto;
            background: rgba(45, 45, 45, 0.95);
            border-radius: 20px;
            box-shadow: 0 8px 32px rgba(0, 0, 0, 0.3);
            overflow: hidden;
            backdrop-filter: blur(10px);
            transform: translateY(20px);
            opacity: 0;
            animation: fadeInUp 0.6s ease-out forwards;
        }
        .profile-header {
            background: linear-gradient(135deg, #1a365d 0%, #0f2442 100%);
            color: white;
            padding: 50px 30px;
            text-align: center;
            position: relative;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.2);
        }
        .back-btn {
            position: absolute;
            top: 25px;
            left: 25px;
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            gap: 8px;
            opacity: 0.8;
            transition: all 0.3s ease;
            padding: 10px 15px;
            border-radius: 12px;
            background: rgba(255, 255, 255, 0.1);
        }
        .back-btn:hover {
            opacity: 1;
            color: #60a5fa;
            transform: translateX(-5px);
            background: rgba(255, 255, 255, 0.15);
        }
        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid rgba(255,255,255,0.3);
            margin: 0 auto 20px;
            object-fit: cover;
            background-color: #fff;
        }
        .section {
            padding: 40px;
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
            opacity: 0;
            transform: translateY(20px);
            animation: fadeInUp 0.6s ease-out forwards;
            animation-delay: calc(var(--delay) * 0.2s);
        }
        .section:last-child {
            border-bottom: none;
        }
        .section-title {
            color: #60a5fa;
            margin-bottom: 30px;
            font-size: 1.4rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 12px;
            text-shadow: 0 2px 4px rgba(0, 0, 0, 0.2);
        }
        .section-title i {
            font-size: 1.2em;
            opacity: 0.9;
        }
        .info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
        }
        .info-item {
            background: rgba(54, 54, 54, 0.7);
            padding: 20px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        .info-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            background: rgba(64, 64, 64, 0.8);
        }
        .info-label {
            font-size: 1rem;
            color: #60a5fa;
            margin-bottom: 8px;
            font-weight: 500;
            letter-spacing: 0.5px;
        }
        .info-value {
            color: #ffffff;
            font-weight: 500;
            font-size: 1.1rem;
            line-height: 1.6;
        }
        .skills-container {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }
        .skill-tag {
            background: rgba(54, 54, 54, 0.7);
            color: #60a5fa;
            padding: 8px 20px;
            border-radius: 25px;
            font-size: 0.95rem;
            font-weight: 500;
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        .skill-tag:hover {
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            background: rgba(64, 64, 64, 0.8);
        }
        .about-me {
            background: rgba(54, 54, 54, 0.7);
            padding: 25px;
            border-radius: 15px;
            border: 1px solid rgba(255, 255, 255, 0.1);
            line-height: 1.8;
            color: #ffffff;
            transition: all 0.3s ease;
            backdrop-filter: blur(5px);
        }
        .about-me:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 20px rgba(0, 0, 0, 0.2);
            background: rgba(64, 64, 64, 0.8);
        }
        .btn-primary {
            background-color: #60a5fa;
            border: none;
            padding: 12px 25px;
            border-radius: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }
        .btn-primary:hover {
            background-color: #3b82f6;
            transform: translateY(-3px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

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

        /* Add animation delays for sections */
        .section:nth-child(1) { --delay: 1; }
        .section:nth-child(2) { --delay: 2; }
        .section:nth-child(3) { --delay: 3; }
        .section:nth-child(4) { --delay: 4; }
        .section:nth-child(5) { --delay: 5; }

        /* 4K optimization */
        @media (min-width: 2160px) {
            .profile-container {
                max-width: 1800px;
            }
            .section {
                padding: 60px;
            }
            .info-grid {
                grid-template-columns: repeat(auto-fit, minmax(400px, 1fr));
            }
            .section-title {
                font-size: 2rem;
            }
            .info-label {
                font-size: 1.4rem;
            }
            .info-value {
                font-size: 1.6rem;
            }
            .skill-tag {
                font-size: 1.3rem;
                padding: 12px 30px;
            }
        }
    </style>
</head>
<body>
    <div class="profile-container">
        <div class="profile-header">
            <a href="student_dashboard.php" class="back-btn">
                <i class="fas fa-arrow-left"></i> Back
            </a>
            <h2 class="mb-2"><?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?></h2>
        </div>

        <div class="section">
            <h3 class="section-title">
                <i class="fas fa-user"></i> Personal Information
            </h3>
            <div class="info-grid">
                <div class="info-item">
                    <div class="info-label">Email</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['email']); ?></div>
                </div>
                <?php if (!empty($student['contact_number'])): ?>
                <div class="info-item">
                    <div class="info-label">Contact Number</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['contact_number']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($student['date_of_birth'])): ?>
                <div class="info-item">
                    <div class="info-label">Date of Birth</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['date_of_birth']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($student['gender'])): ?>
                <div class="info-item">
                    <div class="info-label">Gender</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['gender']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($student['address'])): ?>
                <div class="info-item">
                    <div class="info-label">Address</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['address']); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="section">
            <h3 class="section-title">
                <i class="fas fa-graduation-cap"></i> Academic Information
            </h3>
            <div class="info-grid">
                <?php if (!empty($student['college'])): ?>
                <div class="info-item">
                    <div class="info-label">College</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['college']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($student['course'])): ?>
                <div class="info-item">
                    <div class="info-label">Course</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['course']); ?></div>
                </div>
                <?php endif; ?>
                <?php if (!empty($student['graduation_year'])): ?>
                <div class="info-item">
                    <div class="info-label">Graduation Year</div>
                    <div class="info-value"><?php echo htmlspecialchars($student['graduation_year']); ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!empty($student['about_me'])): ?>
        <div class="section">
            <h3 class="section-title">
                <i class="fas fa-user-circle"></i> About Me
            </h3>
            <div class="about-me">
                <?php echo nl2br(htmlspecialchars($student['about_me'])); ?>
            </div>
        </div>
        <?php endif; ?>

        <?php if (!empty($student['skills'])): ?>
        <div class="section">
            <h3 class="section-title">
                <i class="fas fa-laptop-code"></i> Skills
            </h3>
            <div class="skills-container">
                <?php 
                    $skills = explode(',', $student['skills']);
                    foreach($skills as $skill): 
                        if(trim($skill) != ''):
                ?>
                    <span class="skill-tag"><?php echo htmlspecialchars(trim($skill)); ?></span>
                <?php 
                        endif;
                    endforeach; 
                ?>
            </div>
        </div>
        <?php endif; ?>

        <div class="section">
            <a href="edit_profile.php" class="btn btn-primary">
                <i class="fas fa-edit"></i> Complete/Edit Profile
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 