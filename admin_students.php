<?php
session_start();
require_once 'config.php';

// Check if user is admin
if (!isset($_SESSION['is_admin']) || $_SESSION['is_admin'] !== true) {
    header("Location: admin_login.php");
    exit();
}

// Fetch all students
try {
    $stmt = $conn->prepare("
        SELECT * FROM student_profiles
        ORDER BY created_at DESC
    ");
    $stmt->execute();
    $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    error_log("Error fetching students: " . $e->getMessage());
    $_SESSION['error'] = "Error fetching students";
    $students = [];
}

// Handle student deletion if needed
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete']) && isset($_POST['student_id'])) {
    try {
        $student_id = $_POST['student_id'];
        
        // Start transaction
        $conn->beginTransaction();
        
        // Delete applications first
        $stmt = $conn->prepare("DELETE FROM internship_applications WHERE student_id = ?");
        $stmt->execute([$student_id]);
        
        // Delete student profile
        $stmt = $conn->prepare("DELETE FROM student_profiles WHERE user_id = ?");
        $stmt->execute([$student_id]);
        
        // Commit transaction
        $conn->commit();
        
        $_SESSION['success'] = "Student and related data successfully deleted";
        
        // Redirect to refresh the page
        header("Location: admin_students.php");
        exit();
    } catch(PDOException $e) {
        // Rollback transaction on error
        $conn->rollBack();
        error_log("Error deleting student: " . $e->getMessage());
        $_SESSION['error'] = "Error deleting student: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Management - InternEvo Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        .student-card {
            border-radius: 15px;
            box-shadow: 0 0 15px rgba(0,0,0,0.1);
            margin-bottom: 20px;
            transition: transform 0.3s ease;
        }
        
        .student-card:hover {
            transform: translateY(-5px);
        }
        
        .btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
            transition: all 0.3s ease;
        }
        
        .btn-danger:hover {
            background-color: #bb2d3b;
            border-color: #b02a37;
            transform: translateY(-2px);
        }
        
        .d-flex.gap-2 {
            gap: 0.5rem !important;
        }
        
        .w-100 {
            width: 100% !important;
        }

        /* Sidebar styles */
        .sidebar {
            position: fixed;
            left: 0;
            top: 0;
            height: 100vh;
            width: 250px;
            background: #2c3e50;
            color: white;
            padding-top: 60px;
            transition: all 0.3s ease;
            z-index: 1000;
        }

        .main-content {
            margin-left: 250px;
            transition: all 0.3s ease;
        }

        .user-profile {
            padding: 20px;
            text-align: center;
            border-bottom: 1px solid rgba(255,255,255,0.1);
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            background: #4e73df;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 15px;
        }

        .nav-item {
            margin-bottom: 5px;
        }

        .nav-link {
            color: rgba(255,255,255,0.8) !important;
            padding: 12px 20px !important;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .nav-link:hover {
            background: rgba(255,255,255,0.1);
            color: white !important;
            transform: translateX(5px);
        }

        .nav-link.active {
            background: #4e73df;
            color: white !important;
        }

        .navbar {
            margin-left: 250px;
        }

        @media (max-width: 768px) {
            .sidebar {
                left: -250px;
            }
            .sidebar.active {
                left: 0;
            }
            .main-content {
                margin-left: 0;
            }
            .navbar {
                margin-left: 0;
            }
        }
    </style>
</head>
<body class="bg-light">
    <!-- Sidebar -->
    <div class="sidebar">
        <div class="user-profile">
            <div class="user-avatar">
                <i class="fas fa-user-shield fa-2x"></i>
            </div>
            <h6 class="mb-1">Administrator</h6>
            <p class="small mb-0">System Admin</p>
        </div>

        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link" href="admin_dashboard.php">
                    <i class="fas fa-tachometer-alt"></i>
                    Dashboard
                </a>
            </li>
            
            </li>
            <li class="nav-item">
                <a class="nav-link active" href="admin_students.php">
                    <i class="fas fa-user-graduate"></i>
                    Students
                </a>
            </li>
            
            <li class="nav-item mt-auto">
                <a class="nav-link text-danger" href="logout.php">
                    <i class="fas fa-sign-out-alt"></i>
                    Logout
                </a>
            </li>
        </ul>
    </div>

    <!-- Top Navbar -->
    <nav class="navbar navbar-dark bg-primary">
        <div class="container-fluid">
            <button class="btn btn-link text-white d-md-none" id="sidebarToggle">
                <i class="fas fa-bars"></i>
            </button>
            <a class="navbar-brand">InternEvo Admin</a>
            <div>
                <span class="text-white">Student Management</span>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="main-content">
        <div class="container-fluid py-4">
            <h2 class="mb-4">Registered Students</h2>
            
            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    echo $_SESSION['success'];
                    unset($_SESSION['success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger">
                    <?php 
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                    ?>
                </div>
            <?php endif; ?>

            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="card shadow-sm">
                        <div class="card-body">
                            <h5><i class="fas fa-users me-2"></i>Total Students</h5>
                            <h2 class="display-4"><?php echo count($students); ?></h2>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Students Table -->
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Name</th>
                                    <th>Email</th>
                                    <th>College</th>
                                    <th>Course</th>
                                    <th>Registered On</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($students)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center py-4">No students registered yet</td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach ($students as $student): ?>
                                        <tr>
                                            <td><?php echo htmlspecialchars($student['user_id']); ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($student['first_name'] . ' ' . $student['last_name']); ?>
                                            </td>
                                            <td><?php echo htmlspecialchars($student['email']); ?></td>
                                            <td><?php echo htmlspecialchars($student['college'] ?? 'N/A'); ?></td>
                                            <td><?php echo htmlspecialchars($student['course'] ?? 'N/A'); ?></td>
                                            <td>
                                                <?php echo isset($student['created_at']) ? date('F j, Y', strtotime($student['created_at'])) : 'N/A'; ?>
                                            </td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="view_student.php?id=<?php echo $student['user_id']; ?>" class="btn btn-sm btn-primary">
                                                        <i class="fas fa-eye"></i>
                                                    </a>
                                                    <form method="POST" onsubmit="return confirmDelete()">
                                                        <input type="hidden" name="student_id" value="<?php echo $student['user_id']; ?>">
                                                        <button type="submit" name="delete" class="btn btn-sm btn-danger">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
    function confirmDelete() {
        return confirm('Warning: This will permanently delete the student and all associated data including applications. This action cannot be undone. Are you sure you want to proceed?');
    }

    // Sidebar toggle for mobile
    document.getElementById('sidebarToggle').addEventListener('click', function() {
        document.querySelector('.sidebar').classList.toggle('active');
    });

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(event) {
        const sidebar = document.querySelector('.sidebar');
        const sidebarToggle = document.getElementById('sidebarToggle');
        
        if (window.innerWidth <= 768) {
            if (!sidebar.contains(event.target) && !sidebarToggle.contains(event.target)) {
                sidebar.classList.remove('active');
            }
        }
    });

    // Active link highlighting
    document.querySelectorAll('.nav-link').forEach(link => {
        if (link.href === window.location.href) {
            link.classList.add('active');
        }
    });
    </script>
</body>
</html>