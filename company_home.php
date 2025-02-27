<?php
session_start();
if (!isset($_SESSION['company_id'])) {
    header("Location: company_login.php");
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Company Dashboard - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Add your existing styles */
        
        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .post-internship-card {
            background: linear-gradient(135deg, #4e73df 0%, #2e59d9 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-gradient">
        <div class="container">
            <a class="navbar-brand" href="company_home.php">InternEvo</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link active" href="company_home.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="post_internship.php">Post Internship</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="manage_applications.php">Applications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="company_profile.php">Profile</a>
                    </li>
                </ul>
                <div class="d-flex">
                    <span class="navbar-text me-3">
                        Welcome, <?php echo htmlspecialchars($_SESSION['company_name']); ?>
                    </span>
                    <a href="logout.php" class="btn btn-light">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container mt-4">
        <!-- Quick Stats -->
        <div class="row">
            <div class="col-md-4">
                <div class="stats-card">
                    <h4>Active Internships</h4>
                    <h2>5</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h4>Total Applications</h4>
                    <h2>25</h2>
                </div>
            </div>
            <div class="col-md-4">
                <div class="stats-card">
                    <h4>Pending Reviews</h4>
                    <h2>10</h2>
                </div>
            </div>
        </div>

        <!-- Post New Internship Card -->
        <div class="post-internship-card">
            <h3>Post New Internship</h3>
            <p>Create a new internship posting to find talented students.</p>
            <a href="post_internship.php" class="btn btn-light">Post Now</a>
        </div>

        <!-- Recent Applications -->
        <div class="recent-applications mt-4">
            <h3>Recent Applications</h3>
            <!-- Add your applications table here -->
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 