<?php
session_start();
require_once 'config.php';

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['logged_in'] !== true) {
    header("Location: login.php");
    exit();
}

// Fetch student details
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

// Get student data
try {
    $stmt = $conn->prepare("SELECT * FROM student_profiles WHERE id = ?");
    $stmt->execute([$_SESSION['user_id']]);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        throw new Exception("Student profile not found");
    }

    // If profile is incomplete, redirect to profile page
    if (empty($student['college']) || empty($student['course'])) {
        header('Location: student_profile.php');
        exit();
    }
} catch (Exception $e) {
    $_SESSION['error'] = "Error: " . $e->getMessage();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Dashboard - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #2563eb;
            --secondary: #3b82f6;
            --dark-blue: #1e3a8a;
            --darker-blue: #172554;
            --light-blue: #60a5fa;
            --white: #ffffff;
            --light-gray: #f1f5f9;
            --text-primary: #ffffff;
            --text-secondary: #cbd5e1;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: var(--darker-blue);
            min-height: 100vh;
            overflow-x: hidden;
            color: var(--text-primary);
        }

        /* Enhanced Background Animation */
        .dashboard-bg {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: -1;
            background: 
                radial-gradient(circle at 0% 0%, rgba(37, 99, 235, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 100% 0%, rgba(59, 130, 246, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 100% 100%, rgba(96, 165, 250, 0.2) 0%, transparent 50%),
                radial-gradient(circle at 0% 100%, rgba(37, 99, 235, 0.2) 0%, transparent 50%);
            animation: gradientAnimation 20s ease infinite;
            background-size: 200% 200%;
        }

        @keyframes gradientAnimation {
            0% { background-position: 0% 0%; }
            25% { background-position: 100% 0%; }
            50% { background-position: 100% 100%; }
            75% { background-position: 0% 100%; }
            100% { background-position: 0% 0%; }
        }

        /* Navbar Styling */
        .dashboard-nav {
            background: rgba(30, 58, 138, 0.95);
            backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        .nav-brand {
            font-size: 1.8rem;
            font-weight: 800;
            background: linear-gradient(135deg, var(--white), var(--light-blue));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            text-shadow: 0 0 20px rgba(96, 165, 250, 0.5);
        }

        .nav-link {
            color: var(--white) !important;
            transition: all 0.3s ease;
        }

        .nav-link:hover {
            color: var(--light-blue) !important;
            transform: translateY(-2px);
        }

        /* Cards Styling */
        .dashboard-card {
            background: rgba(30, 58, 138, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.2);
        }

        .card-header {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        }

        /* Stats Cards */
        .stats-card {
            background: rgba(30, 58, 138, 0.8);
            backdrop-filter: blur(10px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            transition: all 0.3s ease;
        }

        .stats-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(37, 99, 235, 0.3);
            border: 1px solid rgba(96, 165, 250, 0.5);
        }

        .stats-icon {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            box-shadow: 0 5px 15px rgba(37, 99, 235, 0.4);
        }

        /* Progress Bars */
        .progress {
            background: rgba(255, 255, 255, 0.1);
        }

        .progress-bar {
            background: linear-gradient(135deg, var(--white), var(--light-blue));
            box-shadow: 0 0 10px rgba(96, 165, 250, 0.5);
        }

        /* Timeline */
        .timeline::before {
            background: linear-gradient(to bottom, var(--white), var(--light-blue));
            opacity: 0.3;
        }

        .timeline-item::before {
            background: var(--darker-blue);
            border: 2px solid var(--light-blue);
            box-shadow: 0 0 10px rgba(96, 165, 250, 0.5);
        }

        /* Animations */
        @keyframes glow {
            0%, 100% { box-shadow: 0 0 20px rgba(37, 99, 235, 0.3); }
            50% { box-shadow: 0 0 30px rgba(37, 99, 235, 0.5); }
        }

        .dashboard-card:hover, .stats-card:hover {
            animation: glow 2s infinite;
        }

        /* Scrollbar */
        ::-webkit-scrollbar {
            width: 8px;
            background: var(--darker-blue);
        }

        ::-webkit-scrollbar-thumb {
            background: linear-gradient(var(--primary), var(--secondary));
            border-radius: 4px;
        }

        /* Text Colors */
        h5, h3, h4, p {
            color: var(--white);
        }

        .text-muted {
            color: var(--text-secondary) !important;
        }

        /* Hover Effects */
        .timeline-item:hover {
            background: rgba(37, 99, 235, 0.1);
            border-radius: 8px;
        }

        /* Card Animations */
        @keyframes shimmer {
            0% { background-position: -100% 0; }
            100% { background-position: 100% 0; }
        }

        .stats-card::after {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            bottom: 0;
            left: 0;
            background: linear-gradient(
                to right,
                transparent 0%,
                rgba(255, 255, 255, 0.1) 50%,
                transparent 100%
            );
            animation: shimmer 2s infinite;
            background-size: 200% 100%;
        }

        /* Enhanced Glassmorphism */
        .dashboard-card, .stats-card {
            position: relative;
            overflow: hidden;
            border-radius: 15px;
        }

        /* White Accent Elements */
        .card-header h4, .stats-card h5 {
            position: relative;
            display: inline-block;
        }

        .card-header h4::after, .stats-card h5::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 50%;
            height: 2px;
            background: rgba(255, 255, 255, 0.3);
            transform: scaleX(0);
            transition: transform 0.3s ease;
        }

        .card-header h4:hover::after, .stats-card h5:hover::after {
            transform: scaleX(1);
        }

        /* 4K Optimization Remains Unchanged */
        @media (min-width: 3840px) {
            .container {
                max-width: 3000px;
            }
            
            body {
                font-size: 18px;
            }

            .nav-brand {
                font-size: 2.5rem;
            }

            .card-header {
                padding: 2rem;
            }

            .stats-icon {
                width: 80px;
                height: 80px;
                font-size: 2rem;
            }

            .progress {
                height: 15px;
            }

            .timeline-item {
                padding-left: 3rem;
            }

            .timeline-item::before {
                width: 15px;
                height: 15px;
            }
        }

        /* Smooth Scrolling */
        html {
            scroll-behavior: smooth;
        }
    </style>
</head>
<body>
    <!-- Animated Background -->
    <div class="dashboard-bg"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg dashboard-nav sticky-top">
        <div class="container">
            <a class="navbar-brand nav-brand" href="#">InternEvo</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="home.php">Browse Internships</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="student_profile.php">Profile</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container py-5">
        <!-- Welcome Section -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="card-header">
                        <h4 class="mb-0">Welcome back, <?php echo htmlspecialchars($_SESSION['name']); ?>!</h4>
                    </div>
                    <div class="card-body p-4">
                        <p class="mb-0">Your journey to success starts here. Let's find your perfect internship!</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Row -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-briefcase"></i>
                    </div>
                    <h5>Applications</h5>
                    <h3 class="mb-0">12</h3>
                    <div class="progress">
                        <div class="progress-bar" style="width: 75%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <h5>Shortlisted</h5>
                    <h3 class="mb-0">5</h3>
                    <div class="progress">
                        <div class="progress-bar" style="width: 50%"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <h5>Profile Views</h5>
                    <h3 class="mb-0">28</h3>
                    <div class="progress">
                        <div class="progress-bar" style="width: 90%"></div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Activity (removed Quick Actions and made Recent Activity full width) -->
        <div class="row mb-4">
            <div class="col-12">
                <div class="dashboard-card">
                    <div class="card-body p-4">
                        <h5 class="card-title">Recent Activity</h5>
                        <div class="timeline">
                            <div class="timeline-item">
                                <p class="mb-0">Profile updated</p>
                                <small class="text-muted">2 days ago</small>
                            </div>
                            <div class="timeline-item">
                                <p class="mb-0">Applied for Web Developer Internship</p>
                                <small class="text-muted">5 days ago</small>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Add animation to progress bars
        window.onload = function() {
            document.querySelectorAll('.progress-bar').forEach(bar => {
                const width = bar.style.width;
                bar.style.width = '0';
                setTimeout(() => {
                    bar.style.width = width;
                }, 500);
            });
        }
    </script>
</body>
</html> 