<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['student_id'])) {
    header("Location: login.php");
    exit();
}

// Fetch student profile data
try {
    $stmt = $conn->prepare("
        SELECT sp.*, u.* 
        FROM student_profiles sp
        JOIN users u ON sp.user_id = u.id
        WHERE sp.id = ?
    ");
    $stmt->execute([$_SESSION['student_id']]);
    $profile = $stmt->fetch(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching profile: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Profile - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #4e73df;
            --secondary: #2e59d9;
            --accent: #00ff88;
        }

        body {
            background: #f8f9fc;
        }

        .profile-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            overflow: hidden;
            margin-top: 30px;
        }

        .profile-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            padding: 30px;
            color: white;
            text-align: center;
        }

        .profile-img {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            border: 5px solid white;
            margin-bottom: 20px;
            object-fit: cover;
        }

        .profile-name {
            font-size: 24px;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .profile-info {
            padding: 30px;
        }

        .info-group {
            margin-bottom: 20px;
        }

        .info-label {
            color: #666;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .info-value {
            color: #333;
            font-size: 16px;
            font-weight: 500;
        }

        .edit-btn {
            background: var(--accent);
            color: #000;
            border: none;
            padding: 10px 30px;
            border-radius: 25px;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .edit-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0,255,136,0.3);
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="profile-card">
                    <div class="profile-header">
                        <img src="https://via.placeholder.com/150" alt="Profile" class="profile-img">
                        <div class="profile-name">
                            <?php echo htmlspecialchars($profile['first_name'] . ' ' . $profile['last_name']); ?>
                        </div>
                        <div><?php echo htmlspecialchars($profile['email']); ?></div>
                    </div>

                    <div class="profile-info">
                        <div class="row">
                            <div class="col-md-6">
                                <div class="info-group">
                                    <div class="info-label">Contact Number</div>
                                    <div class="info-value">
                                        <?php echo htmlspecialchars($profile['contact_number'] ?? 'Not specified'); ?>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="info-group">
                                    <div class="info-label">Date of Birth</div>
                                    <div class="info-value">
                                        <?php echo htmlspecialchars($profile['date_of_birth'] ?? 'Not specified'); ?>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="info-group">
                            <div class="info-label">Address</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($profile['address'] ?? 'Not specified'); ?>
                            </div>
                        </div>

                        <div class="info-group">
                            <div class="info-label">Gender</div>
                            <div class="info-value">
                                <?php echo htmlspecialchars($profile['gender'] ?? 'Not specified'); ?>
                            </div>
                        </div>

                        <div class="text-center mt-4">
                            <a href="edit_profile.php" class="btn edit-btn">
                                <i class="fas fa-edit me-2"></i>Edit Profile
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 