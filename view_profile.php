<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header('Location: login.php');
    exit();
}

// Get student profile data
try {
    $stmt = $conn->prepare("
        SELECT sp.*, u.email 
        FROM student_profiles sp 
        JOIN users u ON sp.user_id = u.id 
        WHERE sp.id = ?
    ");
    $stmt->execute([$_SESSION['user_id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    $_SESSION['error'] = "Error fetching profile: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile View - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary-color: #6366f1;
            --secondary-color: #4f46e5;
            --text-primary: #1e293b;
            --text-secondary: #475569;
        }

        body {
            background: linear-gradient(135deg, #f8fafc 0%, #ffffff 100%);
            color: var(--text-primary);
            font-family: 'SF Pro Display', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        }

        .profile-container {
            max-width: 800px;
            margin: 2rem auto;
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            padding: 2rem;
        }

        .profile-header {
            text-align: center;
            padding-bottom: 2rem;
            border-bottom: 2px solid rgba(99, 102, 241, 0.1);
            margin-bottom: 2rem;
        }

        .profile-name {
            font-size: 2.5rem;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 0.5rem;
        }

        .profile-title {
            font-size: 1.2rem;
            color: var(--text-secondary);
            margin-bottom: 1rem;
        }

        .profile-section {
            margin-bottom: 2rem;
        }

        .section-title {
            font-size: 1.5rem;
            color: var(--primary-color);
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 2px solid rgba(99, 102, 241, 0.1);
        }

        .info-item {
            margin-bottom: 1rem;
        }

        .info-label {
            font-weight: 600;
            color: var(--text-secondary);
            margin-bottom: 0.25rem;
        }

        .info-value {
            color: var(--text-primary);
            line-height: 1.6;
        }

        .skills-list {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .skill-tag {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            padding: 0.5rem 1rem;
            border-radius: 20px;
            font-size: 0.9rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            margin-top: 2rem;
        }

        .btn-action {
            padding: 0.75rem 1.5rem;
            border-radius: 10px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-edit {
            background: linear-gradient(135deg, var(--primary-color), var(--secondary-color));
            color: white;
            border: none;
        }

        .btn-edit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.2);
        }

        @media print {
            .no-print {
                display: none;
            }
            
            body {
                background: white;
            }

            .profile-container {
                box-shadow: none;
                margin: 0;
                padding: 1rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="profile-container">
            <div class="profile-header">
                <div class="profile-name">
                    <?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?>
                </div>
                <div class="profile-title">
                    <?php echo htmlspecialchars($profile['course']); ?> Student
                </div>
                <div class="profile-contact">
                    <i class="fas fa-envelope me-2"></i><?php echo htmlspecialchars($profile['email']); ?>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-title">
                    <i class="fas fa-user-graduate me-2"></i>Education
                </div>
                <div class="info-item">
                    <div class="info-label">College/University</div>
                    <div class="info-value"><?php echo htmlspecialchars($profile['college']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Course</div>
                    <div class="info-value"><?php echo htmlspecialchars($profile['course']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Expected Graduation</div>
                    <div class="info-value"><?php echo htmlspecialchars($profile['graduation_year']); ?></div>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-title">
                    <i class="fas fa-user me-2"></i>About Me
                </div>
                <div class="info-value">
                    <?php echo nl2br(htmlspecialchars($profile['about_me'])); ?>
                </div>
            </div>

            <div class="profile-section">
                <div class="section-title">
                    <i class="fas fa-code me-2"></i>Skills
                </div>
                <div class="skills-list">
                    <?php
                    $skills = explode(',', $profile['skills']);
                    foreach ($skills as $skill):
                        $skill = trim($skill);
                        if (!empty($skill)):
                    ?>
                        <span class="skill-tag"><?php echo htmlspecialchars($skill); ?></span>
                    <?php
                        endif;
                    endforeach;
                    ?>
                </div>
            </div>

            <div class="action-buttons no-print">
                <a href="student_profile.php" class="btn btn-action btn-edit">
                    <i class="fas fa-edit me-2"></i>Edit Profile
                </a>
                <button onclick="window.print()" class="btn btn-action btn-secondary">
                    <i class="fas fa-print me-2"></i>Print Profile
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 