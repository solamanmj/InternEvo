<?php
// Ensure session is started if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>

<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container-fluid">
        <!-- Logo/Brand -->
        <a class="navbar-brand" href="company_dashboard.php">
            <img src="assets/images/logo.png" alt="InternEvo" height="40" class="d-inline-block align-text-top">
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
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'company_dashboard.php' ? 'active' : ''; ?>" 
                       href="company_dashboard.php">
                        <i class="fas fa-home me-2"></i>Dashboard
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'company_applications.php' ? 'active' : ''; ?>" 
                       href="company_applications.php">
                        <i class="fas fa-users me-2"></i>Applications
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link <?php echo basename($_SERVER['PHP_SELF']) == 'post_internship.php' ? 'active' : ''; ?>" 
                       href="post_internship.php">
                        <i class="fas fa-plus-circle me-2"></i>Post Internship
                    </a>
                </li>
            </ul>

            <!-- User Profile Dropdown -->
            <ul class="navbar-nav">
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" 
                       data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fas fa-user-circle me-2"></i>
                        <?php echo htmlspecialchars($_SESSION['company_name'] ?? 'Company'); ?>
                    </a>
                    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                        <li>
                            <a class="dropdown-item" href="company_profile.php">
                                <i class="fas fa-cog me-2"></i>Profile Settings
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
            </ul>
        </div>
    </div>
</nav>

<style>
.navbar {
    padding: 1rem 2rem;
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