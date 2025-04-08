<?php
// Ensure session is started if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <!-- Logo/Brand -->
        <a class="navbar-brand" href="index.php">
            <img src="assets/images/logo.png" alt="InternEvo" height="40" class="d-inline-block align-text-top">
            InternEvo
        </a>

        <!-- Responsive Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navbar Content -->
        <div class="collapse navbar-collapse" id="navbarContent">
            <!-- Main Navigation -->
            <ul class="navbar-nav me-auto mb-2 mb-lg-0">
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'browse_internships.php' ? 'active' : ''; ?>" 
                       href="browse_internships.php">
                        <i class="fas fa-search me-2"></i>Browse Internships
                    </a>
                </li>
                <?php if (isset($_SESSION['student_id'])): ?>
                    <li class="nav-item">
                        <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'my_applications.php' ? 'active' : ''; ?>" 
                           href="my_applications.php">
                            <i class="fas fa-file-alt me-2"></i>My Applications
                        </a>
                    </li>
                <?php endif; ?>
            </ul>

            <!-- User Menu -->
            <ul class="navbar-nav">
                <?php if (isset($_SESSION['student_id'])): ?>
                    <!-- Logged in student menu -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            <i class="fas fa-user-circle me-2"></i>
                            <?php echo htmlspecialchars($_SESSION['student_name'] ?? 'Student'); ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                            <li>
                                <a class="dropdown-item" href="student_profile.php">
                                    <i class="fas fa-user-cog me-2"></i>Profile
                                </a>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <a class="dropdown-item" href="logout.php">
                                    <i class="fas fa-sign-out-alt me-2"></i>Logout
                                </a>
                            </li>
                        </ul>
                    </li>
                <?php else: ?>
                    <!-- Login/Register buttons for non-logged in users -->
                    <li class="nav-item">
                        <a class="nav-link" href="student_login.php">
                            <i class="fas fa-sign-in-alt me-2"></i>Login
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="student_register.php">
                            <i class="fas fa-user-plus me-2"></i>Register
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
.navbar {
    padding: 1rem;
    background: rgba(255, 255, 255, 0.95) !important;
    backdrop-filter: blur(10px);
}

.navbar-brand {
    font-weight: 600;
    color: #2c3e50;
}

.nav-link {
    color: #2c3e50 !important;
    font-weight: 500;
    padding: 0.5rem 1rem;
    border-radius: 0.5rem;
    transition: all 0.3s ease;
}

.nav-link:hover {
    background: rgba(52, 152, 219, 0.1);
    color: #3498db !important;
}

.nav-link.active {
    background: rgba(52, 152, 219, 0.1);
    color: #3498db !important;
}

.dropdown-item {
    padding: 0.5rem 1.5rem;
    display: flex;
    align-items: center;
}

.dropdown-item:hover {
    background: rgba(52, 152, 219, 0.1);
    color: #3498db;
}

.navbar-toggler {
    border: none;
    padding: 0.5rem;
}

.navbar-toggler:focus {
    box-shadow: none;
    outline: none;
}

@media (max-width: 991.98px) {
    .navbar-nav {
        padding: 1rem 0;
    }
    
    .nav-link {
        padding: 0.5rem 0;
    }
}
</style> 