<nav class="navbar navbar-expand-lg navbar-light bg-white shadow-sm">
    <div class="container">
        <!-- Logo -->
        <a class="navbar-brand" href="home.php">
            <span class="text-primary fw-bold">Intern</span><span class="text-dark fw-bold">Evo</span>
        </a>

        <!-- Mobile Toggle Button -->
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>

        <!-- Navigation Items -->
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav ms-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="home.php">Home</a>
                </li>
                
                <?php if(isset($_SESSION['user_id'])): ?>
                    <!-- Student is logged in -->
                    <li class="nav-item">
                        <a class="nav-link" href="student_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="my_applications.php">My Applications</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php elseif(isset($_SESSION['company_id'])): ?>
                    <!-- Company is logged in -->
                    <li class="nav-item">
                        <a class="nav-link" href="company_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="post_internship.php">Post Internship</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                <?php else: ?>
                    <!-- No one is logged in -->
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="loginDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            Login
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="loginDropdown">
                            <li><a class="dropdown-item" href="login.php">Student Login</a></li>
                            <li><a class="dropdown-item" href="company_login.php">Company Login</a></li>
                        </ul>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="registerDropdown" role="button" 
                           data-bs-toggle="dropdown" aria-expanded="false">
                            Register
                        </a>
                        <ul class="dropdown-menu" aria-labelledby="registerDropdown">
                            <li><a class="dropdown-item" href="register_student.php">Student Register</a></li>
                            <li><a class="dropdown-item" href="register_company.php">Company Register</a></li>
                        </ul>
                    </li>
                <?php endif; ?>
            </ul>
        </div>
    </div>
</nav>

<style>
    .navbar {
        padding: 15px 0;
    }
    
    .navbar-brand {
        font-size: 1.5rem;
    }
    
    .nav-link {
        color: #333 !important;
        font-weight: 500;
        padding: 8px 16px !important;
        transition: all 0.3s ease;
    }
    
    .nav-link:hover {
        color: #4e73df !important;
    }
    
    .dropdown-item {
        padding: 8px 20px;
        transition: all 0.3s ease;
    }
    
    .dropdown-item:hover {
        background-color: #4e73df;
        color: white;
    }
    
    .navbar-toggler {
        border: none;
        padding: 0;
    }
    
    .navbar-toggler:focus {
        box-shadow: none;
    }
</style> 