<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InternEvo - Your Gateway to Internships</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Import Fonts */
        @import url('https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap');
        @import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@700&display=swap');

        /* Reset and Base Styles */
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        :root {
            --primary: #4e73df;
            --primary-dark: #2e59d9;
            --secondary: #1cc88a;
            --secondary-dark: #13855c;
            --dark: #2c3e50;
            --light: #f8f9fc;
        }

        body {
            font-family: 'Poppins', sans-serif;
            line-height: 1.6;
            color: #333;
            overflow-x: hidden;
            background: var(--light);
        }

        /* Enchanted Navigation */
        .navbar {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
            border-bottom: 1px solid rgba(255, 255, 255, 0.3);
            padding: 1rem 0;
            position: fixed;
            width: 100%;
            top: 0;
            z-index: 1000;
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .navbar-brand {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            position: relative;
        }

        .navbar-brand::after {
            content: '';
            position: absolute;
            bottom: -5px;
            left: 0;
            width: 100%;
            height: 2px;
            background: linear-gradient(90deg, var(--primary), transparent);
        }

        .nav-link {
            font-weight: 500;
            color: var(--dark) !important;
            padding: 0.7rem 1.5rem !important;
            border-radius: 25px;
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .nav-link::before {
            content: '';
            position: absolute;
            top: 50%;
            left: 50%;
            width: 0;
            height: 0;
            background: rgba(78, 115, 223, 0.1);
            border-radius: 50%;
            transform: translate(-50%, -50%);
            transition: width 0.6s ease, height 0.6s ease;
        }

        .nav-link:hover::before {
            width: 300px;
            height: 300px;
        }

        /* Spectacular Hero Section */
        .hero {
            background: linear-gradient(135deg, rgba(0, 0, 0, 0.8), rgba(0, 0, 0, 0.6)),
                        url('https://images.unsplash.com/photo-1497366216548-37526070297c?auto=format&fit=crop&q=80&w=3840');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 100vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: 
                linear-gradient(135deg, rgba(46, 89, 217, 0.3), rgba(28, 200, 138, 0.3)),
                radial-gradient(circle at 20% 30%, rgba(46, 89, 217, 0.4) 0%, transparent 50%),
                radial-gradient(circle at 80% 70%, rgba(28, 200, 138, 0.4) 0%, transparent 50%);
            animation: gradientShift 15s ease infinite;
        }

        @keyframes gradientShift {
            0%, 100% { transform: scale(1); }
            50% { transform: scale(1.1); }
        }

        .hero-content {
            position: relative;
            z-index: 2;
            padding: 2rem;
            border-radius: 20px;
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            box-shadow: 0 25px 45px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            animation: contentFade 1s ease-out forwards;
        }

        @keyframes contentFade {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .hero h1 {
            font-size: 4.5rem;
            font-weight: 700;
            margin-bottom: 1.5rem;
            color: #ffffff;
            text-shadow: 2px 4px 8px rgba(0, 0, 0, 0.3);
            letter-spacing: -1px;
        }

        .hero .lead {
            font-size: 1.5rem;
            color: rgba(255, 255, 255, 0.9);
            text-shadow: 1px 2px 4px rgba(0, 0, 0, 0.2);
            margin-bottom: 2.5rem;
        }

        /* Mesmerizing Registration Buttons */
        .register-btn {
            padding: 1rem 2.5rem;
            border-radius: 50px;
            font-weight: 600;
            font-size: 1.1rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
            z-index: 1;
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .register-btn::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(45deg, rgba(255,255,255,0.1), rgba(255,255,255,0.2));
            transform: translateX(-100%) rotate(45deg);
            transition: transform 0.6s cubic-bezier(0.4, 0, 0.2, 1);
            z-index: -1;
        }

        .register-btn:hover::before {
            transform: translateX(100%) rotate(45deg);
        }

        .company-btn {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            box-shadow: 0 10px 30px rgba(78, 115, 223, 0.3),
                        inset 0 -3px 0 rgba(0, 0, 0, 0.1);
        }

        .student-btn {
            background: linear-gradient(135deg, var(--secondary), var(--secondary-dark));
            box-shadow: 0 10px 30px rgba(28, 200, 138, 0.3),
                        inset 0 -3px 0 rgba(0, 0, 0, 0.1);
        }

        /* Captivating Feature Cards */
        .feature-card {
            padding: 3rem 2rem;
            border-radius: 20px;
            background: white;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.05);
            transition: all 0.5s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            overflow: hidden;
        }

        .feature-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 4px;
            height: 100%;
            background: linear-gradient(to bottom, var(--primary), var(--secondary));
            transition: all 0.5s ease;
        }

        .feature-card:hover::before {
            width: 100%;
            opacity: 0.1;
        }

        .feature-card:hover {
            transform: translateY(-20px) scale(1.02);
            box-shadow: 0 30px 60px rgba(0, 0, 0, 0.1);
        }

        .feature-icon {
            font-size: 3.5rem;
            background: linear-gradient(120deg, var(--primary), var(--secondary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 1.5rem;
            transition: all 0.5s ease;
        }

        .feature-card:hover .feature-icon {
            transform: scale(1.2) rotate(10deg);
        }

        /* Elegant Footer */
        .footer {
            background: linear-gradient(135deg, var(--dark), #1a2639);
            color: white;
            position: relative;
            overflow: hidden;
        }

        .footer::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: 
                radial-gradient(circle at 10% 90%, rgba(78, 115, 223, 0.1) 0%, transparent 40%),
                radial-gradient(circle at 90% 10%, rgba(28, 200, 138, 0.1) 0%, transparent 40%);
        }

        .footer a {
            position: relative;
            padding: 0.5rem 0;
            transition: all 0.3s ease;
        }

        .footer a::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 0;
            height: 2px;
            background: var(--primary);
            transition: width 0.3s ease;
        }

        .footer a:hover::after {
            width: 100%;
        }

        /* Responsive Enhancements */
        @media (max-width: 768px) {
            .hero h1 {
                font-size: 3rem;
            }

            .register-buttons {
                flex-direction: column;
                gap: 20px;
            }

            .register-btn {
                width: 100%;
                text-align: center;
            }

            .feature-card {
                margin-bottom: 30px;
            }
        }

        /* Custom Animations */
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.05); }
            100% { transform: scale(1); }
        }

        .floating {
            animation: float 3s ease-in-out infinite;
        }

        .pulsing {
            animation: pulse 2s ease-in-out infinite;
        }

        /* Add floating particles effect */
        .particles {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            overflow: hidden;
            z-index: 1;
        }

        .particle {
            position: absolute;
            width: 6px;
            height: 6px;
            background: rgba(255, 255, 255, 0.3);
            border-radius: 50%;
            animation: particleFloat 20s infinite linear;
        }

        @keyframes particleFloat {
            0% { transform: translateY(0) rotate(0deg); opacity: 0; }
            50% { opacity: 0.5; }
            100% { transform: translateY(-100vh) rotate(360deg); opacity: 0; }
        }

        /* Search Bar Styles */
        .search-container {
            width: 100%;
            max-width: 600px;
            margin: 0 auto;
        }

        .search-wrapper {
            position: relative;
            display: flex;
            align-items: center;
            background: rgba(255, 255, 255, 0.15);
            backdrop-filter: blur(10px);
            border-radius: 50px;
            padding: 5px;
            border: 1px solid rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.2);
            transition: all 0.3s ease;
        }

        .search-wrapper:hover {
            background: rgba(255, 255, 255, 0.2);
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.3);
        }

        .search-input {
            width: 100%;
            padding: 15px 25px;
            background: transparent;
            border: none;
            font-size: 1.1rem;
            color: #ffffff;
            outline: none;
        }

        .search-input::placeholder {
            color: rgba(255, 255, 255, 0.8);
        }

        .search-button {
            background: linear-gradient(135deg, var(--primary), var(--secondary));
            border: none;
            padding: 15px 30px;
            border-radius: 50px;
            color: white;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .search-button:hover {
            transform: translateX(3px);
            box-shadow: -5px 0 20px rgba(0, 0, 0, 0.2);
        }

        /* Responsive adjustments */
        @media (max-width: 768px) {
            .search-container {
                padding: 0 20px;
            }
            
            .search-input {
                font-size: 1rem;
                padding: 12px 20px;
            }
            
            .search-button {
                padding: 12px 25px;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">InternEvo</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav ms-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="login.php">Student Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="company_login.php">Company Login</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register_student.php">Student Registration</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="register_company.php">Company Registration</a>
                    </li>
                </ul>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <!-- Add particles -->
        <div class="particles">
            <?php for($i = 0; $i < 50; $i++): ?>
                <div class="particle" style="
                    left: <?php echo rand(0, 100); ?>%;
                    top: <?php echo rand(0, 100); ?>%;
                    animation-delay: <?php echo rand(0, 5000)/1000; ?>s;
                    animation-duration: <?php echo rand(10000, 20000)/1000; ?>s;
                "></div>
            <?php endfor; ?>
        </div>

        <div class="container text-center">
            <div class="hero-content">
                <h1 class="display-4 mb-4">Welcome to InternEvo</h1>
                <p class="lead mb-5">Your Gateway to Exciting Internship Opportunities</p>
                
                <!-- Add this new search section -->
                <div class="search-container mb-5">
                    <form action="search_internships.php" method="GET" class="search-form">
                        <div class="search-wrapper">
                            <input type="text" name="search" placeholder="Search for internships..." class="search-input">
                            <button type="submit" class="search-button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </div>

                <div class="register-buttons">
                    <a href="register_company.php" class="register-btn company-btn">
                        <i class="fas fa-building"></i>
                        Register as Company
                    </a>
                    <a href="register_student.php" class="register-btn student-btn">
                        <i class="fas fa-user-graduate"></i>
                        Register as Student
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-search feature-icon"></i>
                        <h3>Find Opportunities</h3>
                        <p>Discover internship opportunities that match your skills and interests.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-handshake feature-icon"></i>
                        <h3>Connect with Companies</h3>
                        <p>Build relationships with leading companies in your field.</p>
                    </div>
                </div>
                <div class="col-md-4 mb-4">
                    <div class="feature-card">
                        <i class="fas fa-rocket feature-icon"></i>
                        <h3>Launch Your Career</h3>
                        <p>Take the first step towards your dream career.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="row">
                <div class="col-md-4 mb-4">
                    <h5>About InternEvo</h5>
                    <p>Connecting talented students with innovative companies for meaningful internship experiences.</p>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Quick Links</h5>
                    <ul class="list-unstyled">
                        <li><a href="register_student.php">Student Registration</a></li>
                        <li><a href="register_company.php">Company Registration</a></li>
                        <li><a href="login.php">Student Login</a></li>
                        <li><a href="company_login.php">Company Login</a></li>
                    </ul>
                </div>
                <div class="col-md-4 mb-4">
                    <h5>Contact Us</h5>
                    <ul class="list-unstyled">
                        <li><i class="fas fa-envelope me-2"></i> info@internevo.com</li>
                        <li><i class="fas fa-phone me-2"></i> +1 234 567 8900</li>
                    </ul>
                </div>
            </div>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 