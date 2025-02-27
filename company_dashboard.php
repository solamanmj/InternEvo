<?php
session_start();
error_log("Dashboard access - Session data: " . print_r($_SESSION, true));

if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'company') {
    error_log("Access denied - Not logged in or not company");
    header("Location: company_login.php");
    exit();
}

require_once 'config.php';

// Fetch company's posted internships
try {
    $stmt = $conn->prepare("
        SELECT * FROM internships 
        WHERE company_id = ? 
        ORDER BY created_at DESC
    ");
    $stmt->execute([$_SESSION['company_id']]);
    $internships = $stmt->fetchAll();
} catch(PDOException $e) {
    $_SESSION['error'] = "Error fetching internships: " . $e->getMessage();
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
        :root {
            --primary: #2c3e50;
            --secondary: #34495e;
            --accent: #3498db;
            --success: #2ecc71;
            --warning: #f1c40f;
            --danger: #e74c3c;
            --light: #ecf0f1;
            --dark: #2c3e50;
        }

        body {
            background: #f8f9fc;
            min-height: 100vh;
            font-family: 'Poppins', sans-serif;
        }

        /* Navbar Styling */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0,0,0,0.1);
            padding: 1rem 2rem;
        }

        .navbar-brand {
            font-size: 1.5rem;
            font-weight: 600;
            color: var(--primary);
        }

        .dropdown-toggle {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            color: white;
            border: none;
            padding: 0.5rem 1.5rem;
            border-radius: 25px;
            transition: all 0.3s ease;
        }

        .dropdown-toggle:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(44,62,80,0.3);
            color: white;
        }

        /* Sidebar Styling */
        .sidebar {
            background: white;
            box-shadow: 4px 0 20px rgba(0,0,0,0.1);
            padding: 2rem 1rem;
            min-height: calc(100vh - 76px);
            transition: all 0.3s ease;
        }

        .list-group-item {
            border: none;
            border-radius: 10px !important;
            margin-bottom: 0.5rem;
            padding: 1rem 1.5rem;
            transition: all 0.3s ease;
        }

        .list-group-item:hover {
            background: rgba(52,152,219,0.1);
            transform: translateX(5px);
        }

        .list-group-item.active {
            background: linear-gradient(135deg, var(--accent), #2980b9);
            border: none;
        }

        /* Main Content Styling */
        .main-content {
            padding: 2rem;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .btn-post {
            background: linear-gradient(135deg, var(--accent), #2980b9);
            color: white;
            border: none;
            padding: 1rem 2rem;
            border-radius: 25px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-post:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(52,152,219,0.3);
            color: white;
        }

        /* Internship Cards */
        .internship-card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
            transition: all 0.3s ease;
            background: white;
            overflow: hidden;
        }

        .internship-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }

        .card-body {
            padding: 1.5rem;
        }

        .card-title {
            color: var(--primary);
            font-weight: 600;
            margin-bottom: 1rem;
        }

        .card-text {
            color: var(--secondary);
            margin-bottom: 0.5rem;
        }

        .card-stats {
            display: flex;
            justify-content: space-between;
            padding: 1rem;
            background: rgba(52,152,219,0.05);
            border-radius: 10px;
            margin: 1rem 0;
        }

        .stat-item {
            text-align: center;
        }

        .stat-value {
            font-size: 1.2rem;
            font-weight: 600;
            color: var(--accent);
        }

        .stat-label {
            font-size: 0.8rem;
            color: var(--secondary);
        }

        /* Action Buttons */
        .btn-action {
            padding: 0.5rem 1rem;
            border-radius: 20px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .btn-edit {
            background: rgba(52,152,219,0.1);
            color: var(--accent);
        }

        .btn-view {
            background: rgba(46,204,113,0.1);
            color: var(--success);
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        /* Alerts */
        .alert {
            border: none;
            border-radius: 15px;
            padding: 1rem 1.5rem;
            margin-bottom: 2rem;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from { transform: translateX(-100%); opacity: 0; }
            to { transform: translateX(0); opacity: 1; }
        }

        /* Empty State */
        .empty-state {
            text-align: center;
            padding: 3rem;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.05);
        }

        .empty-state i {
            font-size: 4rem;
            color: var(--accent);
            margin-bottom: 1.5rem;
        }

        /* Responsive Design */
        @media (max-width: 768px) {
            .sidebar {
                position: fixed;
                left: -100%;
                z-index: 1000;
            }

            .sidebar.active {
                left: 0;
            }
        }
    </style>
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-light">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">
                <img src="logo.png" alt="InternEvo" height="40">
            </a>
            <div class="d-flex">
                <div class="dropdown">
                    <button class="btn btn-light dropdown-toggle" type="button" id="dropdownMenuButton" data-bs-toggle="dropdown">
                        <i class="fas fa-user-circle me-2"></i><?php echo htmlspecialchars($_SESSION['company_name']); ?>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="company_profile.php"><i class="fas fa-cog me-2"></i>Settings</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
                    </ul>
                </div>
            </div>
        </div>
    </nav>

    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 sidebar p-3">
                <div class="list-group">
                    <a href="#" class="list-group-item list-group-item-action active">
                        <i class="fas fa-clipboard-list me-2"></i>Internships
                    </a>
                    <a href="applications.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-users me-2"></i>Applications
                    </a>
                    <a href="company_profile.php" class="list-group-item list-group-item-action">
                        <i class="fas fa-building me-2"></i>Company Profile
                    </a>
                </div>
            </div>

            <!-- Main Content -->
            <div class="col-md-9 col-lg-10 p-4">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h2>Manage Internships</h2>
                    <a href="post_internship.php" class="btn btn-post">
                        <i class="fas fa-plus me-2"></i>Post New Internship
                    </a>
                </div>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success">
                        <?php 
                        echo $_SESSION['success'];
                        unset($_SESSION['success']);
                        ?>
                    </div>
                <?php endif; ?>

                <!-- Internships Grid -->
                <div class="row g-4">
                    <?php if (!empty($internships)): ?>
                        <?php foreach ($internships as $internship): ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card internship-card h-100">
                                    <div class="card-body">
                                        <h5 class="card-title"><?php echo htmlspecialchars($internship['title']); ?></h5>
                                        <p class="card-text text-muted">
                                            <i class="fas fa-map-marker-alt me-2"></i><?php echo htmlspecialchars($internship['location']); ?>
                                        </p>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="fas fa-clock me-2"></i>Duration: <?php echo htmlspecialchars($internship['duration']); ?>
                                            </small>
                                        </p>
                                        <p class="card-text">
                                            <small class="text-muted">
                                                <i class="fas fa-users me-2"></i>Applications: 
                                                <?php echo $internship['application_count'] ?? 0; ?>
                                            </small>
                                        </p>
                                        <div class="mt-3">
                                            <a href="edit_internship.php?id=<?php echo $internship['id']; ?>" class="btn btn-sm btn-outline-primary me-2">
                                                <i class="fas fa-edit"></i> Edit
                                            </a>
                                            <a href="view_applications.php?internship_id=<?php echo $internship['id']; ?>" class="btn btn-sm btn-outline-success">
                                                <i class="fas fa-eye"></i> View Applications
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <div class="col-12">
                            <div class="alert alert-info">
                                <i class="fas fa-info-circle me-2"></i>No internships posted yet. 
                                <a href="post_internship.php">Post your first internship</a>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 