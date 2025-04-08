<?php
session_start();
require_once 'config.php';

// Pagination settings
$results_per_page = 10;
$current_page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($current_page - 1) * $results_per_page;

$search = isset($_GET['search']) ? trim($_GET['search']) : '';
$industry_id = isset($_GET['industry_id']) ? $_GET['industry_id'] : '';
$location = isset($_GET['location']) ? $_GET['location'] : '';
$duration = isset($_GET['duration']) ? $_GET['duration'] : '';

// Get industry categories from registered companies
try {
    $stmt = $conn->query("
        SELECT DISTINCT ic.id, ic.name 
        FROM industry_categories ic
        INNER JOIN company_profiles cp ON ic.id = cp.industry_id
        WHERE cp.status = 'approved'
        ORDER BY ic.name
    ");
    $industries = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    $industries = [];
}

// Search query with pagination
if ($search || $industry_id || $location || $duration) {
    try {
        // Get total count for pagination
        $countQuery = "
            SELECT COUNT(*) as total
            FROM internships i
            INNER JOIN company_profiles cp ON i.company_id = cp.company_id
            WHERE 1=1 
        ";
        
        $params = [];
        $conditions = [];

        if ($search) {
            $conditions[] = "(cp.company_name LIKE ? OR i.title LIKE ?)";
            $searchTerm = "%$search%";
            $params = array_merge($params, [$searchTerm, $searchTerm]);
        }

        if ($industry_id) {
            $conditions[] = "cp.industry_id = ?";
            $params[] = $industry_id;
        }

        if ($location) {
            $conditions[] = "i.location LIKE ?";
            $params[] = "%$location%";
        }

        if ($duration) {
            $conditions[] = "i.duration = ?";
            $params[] = $duration;
        }

        if (!empty($conditions)) {
            $countQuery .= " AND (" . implode(" OR ", $conditions) . ")";
        }

        $countStmt = $conn->prepare($countQuery);
        $countStmt->execute($params);
        $total_results = $countStmt->fetch(PDO::FETCH_ASSOC)['total'];
        $total_pages = ceil($total_results / $results_per_page);

        // Main query with pagination
        $query = "
            SELECT 
                i.*,
                cp.company_name,
                cp.logo_url,
                cp.industry_id
            FROM internships i
            INNER JOIN company_profiles cp ON i.company_id = cp.company_id
            WHERE 1=1 
        ";

        if (!empty($conditions)) {
            $query .= " AND (" . implode(" OR ", $conditions) . ")";
        }

        $query .= " ORDER BY i.created_at DESC LIMIT $results_per_page OFFSET $offset";
        
        $stmt = $conn->prepare($query);
        $stmt->execute($params);
        $internships = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch(PDOException $e) {
        $internships = [];
        $total_pages = 0;
    }
} else {
    // Show all recent internships with pagination
    try {
        // Get total count
        $countStmt = $conn->query("SELECT COUNT(*) FROM internships");
        $total_results = $countStmt->fetchColumn();
        $total_pages = ceil($total_results / $results_per_page);

        $query = "
            SELECT 
                i.*,
                cp.company_name,
                cp.logo_url,
                cp.industry_id
            FROM internships i
            INNER JOIN company_profiles cp ON i.company_id = cp.company_id
            ORDER BY i.created_at DESC
            LIMIT $results_per_page OFFSET $offset
        ";
        
        $stmt = $conn->prepare($query);
        $stmt->execute();
        $internships = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch(PDOException $e) {
        $internships = [];
        $total_pages = 0;
    }
}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>InternEvo - Find Your Perfect Internship</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
</head>
<body>
    <?php include 'navbar.php'; ?>

    <!-- Hero Section -->
    <div class="hero-section text-center py-5 bg-primary text-white">
        <div class="container">
            <h1 class="display-4 mb-3">Find Your Dream Internship</h1>
            <p class="lead mb-4">Discover opportunities that match your interests and skills</p>
        </div>
    </div>

    <!-- Search Section -->
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="search-box">
                    <form class="row g-3" method="GET" action="home.php">
                        <!-- Search input -->
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fas fa-search text-primary"></i>
                                </span>
                                <input type="text" 
                                       name="search" 
                                       class="form-control border-start-0" 
                                       placeholder="Search title or company"
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                        </div>

                        <!-- Industry Dropdown with Categories -->
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fas fa-briefcase text-primary"></i>
                                </span>
                                <select name="industry_id" class="form-select border-start-0">
                                    <option value="">All Industries</option>
                                    <!-- Technology & IT -->
                                    <optgroup label="Technology & IT">
                                        <option value="1" <?php echo ($industry_id == '1') ? 'selected' : ''; ?>>Web Development</option>
                                        <option value="2" <?php echo ($industry_id == '2') ? 'selected' : ''; ?>>Mobile App Development</option>
                                        <option value="3" <?php echo ($industry_id == '3') ? 'selected' : ''; ?>>Software Development</option>
                                        <option value="4" <?php echo ($industry_id == '4') ? 'selected' : ''; ?>>Data Science & Analytics</option>
                                        <option value="5" <?php echo ($industry_id == '5') ? 'selected' : ''; ?>>Cloud Computing</option>
                                        <option value="6" <?php echo ($industry_id == '6') ? 'selected' : ''; ?>>Cybersecurity</option>
                                        <option value="7" <?php echo ($industry_id == '7') ? 'selected' : ''; ?>>AI/Machine Learning</option>
                                    </optgroup>
                                    
                                    <!-- Automotive -->
                                    <optgroup label="Automotive">
                                        <option value="31" <?php echo ($industry_id == '31') ? 'selected' : ''; ?>>Automotive Design</option>
                                        <option value="32" <?php echo ($industry_id == '32') ? 'selected' : ''; ?>>Automotive Engineering</option>
                                        <option value="33" <?php echo ($industry_id == '33') ? 'selected' : ''; ?>>Electric Vehicles</option>
                                        <option value="34" <?php echo ($industry_id == '34') ? 'selected' : ''; ?>>Automotive Manufacturing</option>
                                        <option value="35" <?php echo ($industry_id == '35') ? 'selected' : ''; ?>>Automotive R&D</option>
                                    </optgroup>
                                    
                                    <!-- Design & Creative -->
                                    <optgroup label="Design & Creative">
                                        <option value="8" <?php echo ($industry_id == '8') ? 'selected' : ''; ?>>UI/UX Design</option>
                                        <option value="9" <?php echo ($industry_id == '9') ? 'selected' : ''; ?>>Graphic Design</option>
                                        <option value="10" <?php echo ($industry_id == '10') ? 'selected' : ''; ?>>Content Writing</option>
                                        <option value="11" <?php echo ($industry_id == '11') ? 'selected' : ''; ?>>Video Production</option>
                                        <option value="12" <?php echo ($industry_id == '12') ? 'selected' : ''; ?>>Animation</option>
                                    </optgroup>

                                    <!-- Marketing -->
                                    <optgroup label="Marketing">
                                        <option value="13" <?php echo ($industry_id == '13') ? 'selected' : ''; ?>>Digital Marketing</option>
                                        <option value="14" <?php echo ($industry_id == '14') ? 'selected' : ''; ?>>Social Media Marketing</option>
                                        <option value="15" <?php echo ($industry_id == '15') ? 'selected' : ''; ?>>Content Marketing</option>
                                        <option value="16" <?php echo ($industry_id == '16') ? 'selected' : ''; ?>>SEO/SEM</option>
                                        <option value="17" <?php echo ($industry_id == '17') ? 'selected' : ''; ?>>Email Marketing</option>
                                    </optgroup>

                                    <!-- Business -->
                                    <optgroup label="Business">
                                        <option value="18" <?php echo ($industry_id == '18') ? 'selected' : ''; ?>>Business Development</option>
                                        <option value="19" <?php echo ($industry_id == '19') ? 'selected' : ''; ?>>Sales</option>
                                        <option value="20" <?php echo ($industry_id == '20') ? 'selected' : ''; ?>>Finance</option>
                                        <option value="21" <?php echo ($industry_id == '21') ? 'selected' : ''; ?>>Accounting</option>
                                        <option value="22" <?php echo ($industry_id == '22') ? 'selected' : ''; ?>>Human Resources</option>
                                    </optgroup>

                                    <!-- Engineering -->
                                    <optgroup label="Engineering">
                                        <option value="23" <?php echo ($industry_id == '23') ? 'selected' : ''; ?>>Mechanical Engineering</option>
                                        <option value="24" <?php echo ($industry_id == '24') ? 'selected' : ''; ?>>Civil Engineering</option>
                                        <option value="25" <?php echo ($industry_id == '25') ? 'selected' : ''; ?>>Electrical Engineering</option>
                                        <option value="26" <?php echo ($industry_id == '26') ? 'selected' : ''; ?>>Chemical Engineering</option>
                                    </optgroup>

                                    <!-- Others -->
                                    <optgroup label="Others">
                                        <option value="27" <?php echo ($industry_id == '27') ? 'selected' : ''; ?>>Education</option>
                                        <option value="28" <?php echo ($industry_id == '28') ? 'selected' : ''; ?>>Healthcare</option>
                                        <option value="29" <?php echo ($industry_id == '29') ? 'selected' : ''; ?>>Architecture</option>
                                        <option value="30" <?php echo ($industry_id == '30') ? 'selected' : ''; ?>>Research</option>
                                    </optgroup>
                                </select>
                            </div>
                        </div>

                        <!-- Duration Filter -->
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fas fa-clock text-primary"></i>
                                </span>
                                <select name="duration" class="form-select border-start-0">
                                    <option value="">All Durations</option>
                                    <option value="1 Month" <?php echo ($duration == '1 Month') ? 'selected' : ''; ?>>1 Month</option>
                                    <option value="2 Months" <?php echo ($duration == '2 Months') ? 'selected' : ''; ?>>2 Months</option>
                                    <option value="3 Months" <?php echo ($duration == '3 Months') ? 'selected' : ''; ?>>3 Months</option>
                                    <option value="4 Months" <?php echo ($duration == '4 Months') ? 'selected' : ''; ?>>4 Months</option>
                                    <option value="5 Months" <?php echo ($duration == '5 Months') ? 'selected' : ''; ?>>5 Months</option>
                                    <option value="6 Months" <?php echo ($duration == '6 Months') ? 'selected' : ''; ?>>6 Months</option>
                                </select>
                            </div>
                        </div>

                        <!-- Location input -->
                        <div class="col-md-3">
                            <div class="input-group">
                                <span class="input-group-text bg-transparent border-end-0">
                                    <i class="fas fa-map-marker-alt text-primary"></i>
                                </span>
                                <input type="text" 
                                       name="location" 
                                       class="form-control border-start-0" 
                                       placeholder="Enter location"
                                       value="<?php echo htmlspecialchars($location); ?>">
                            </div>
                        </div>

                        <!-- Search button -->
                        <div class="col-md-12 text-center mt-4">
                            <button type="submit" class="btn btn-apply px-5">
                                <i class="fas fa-search me-2"></i>Search Internships
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Display internships -->
    <div class="container mt-4">
        <?php if (!empty($internships)): ?>
            <?php foreach ($internships as $internship): ?>
                <div class="internship-card mb-4" data-aos="fade-up">
                    <div class="card">
                        <div class="card-body">
                            <div class="row align-items-center">
                                <!-- Company Logo -->
                                <div class="col-md-2 text-center">
                                    <?php if (!empty($internship['logo_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($internship['logo_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($internship['company_name']); ?>" 
                                             class="company-logo mb-2">
                                    <?php else: ?>
                                        <div class="default-logo">
                                            <i class="fas fa-building fa-2x"></i>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Internship Details -->
                                <div class="col-md-7">
                                    <h5 class="card-title mb-1">
                                        <?php echo htmlspecialchars($internship['title']); ?>
                                    </h5>
                                    <h6 class="company-name mb-2">
                                        <?php echo htmlspecialchars($internship['company_name']); ?>
                                    </h6>
                                    <div class="internship-highlights">
                                        <span class="badge bg-light text-dark me-2">
                                            <i class="fas fa-map-marker-alt me-1"></i>
                                            <?php echo htmlspecialchars($internship['location']); ?>
                                        </span>
                                        <span class="badge bg-light text-dark me-2">
                                            <i class="fas fa-clock me-1"></i>
                                            <?php echo htmlspecialchars($internship['duration']); ?>
                                        </span>
                                        <span class="badge bg-light text-dark me-2">
                                            <i class="fas fa-wallet me-1"></i>
                                            ₹<?php echo htmlspecialchars($internship['stipend']); ?>/month
                                        </span>
                                        <span class="badge bg-light text-dark me-2">
                                            <i class="fas fa-users me-1"></i>
                                            <?php echo htmlspecialchars($internship['positions']); ?> positions
                                        </span>
                                    </div>

                                    <!-- Description Preview -->
                                    <div class="description-preview mt-2">
                                        <?php 
                                        $desc = strip_tags($internship['description']);
                                        echo htmlspecialchars(substr($desc, 0, 150)) . (strlen($desc) > 150 ? '...' : ''); 
                                        ?>
                                    </div>
                                </div>

                                <!-- Action Button -->
                                <div class="col-md-3 text-end">
                                    <a href="view_internship.php?id=<?php echo $internship['id']; ?>" 
                                       class="btn btn-apply w-100 mb-2">
                                        <i class="fas fa-eye me-2"></i>View Details
                                    </a>
                                    <?php if(isset($_SESSION['user_id'])): ?>
                                        <a href="apply_internship.php?id=<?php echo $internship['id']; ?>" 
                                           class="btn btn-apply w-100 mb-2">
                                            <i class="fas fa-paper-plane me-2"></i>Apply Now
                                        </a>
                                    <?php endif; ?>
                                    <p class="deadline-text small">
                                        <i class="fas fa-hourglass-half me-1"></i>
                                        Apply by: <?php echo date('d M Y', strtotime($internship['application_deadline'])); ?>
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="no-results text-center py-5">
                <i class="fas fa-search fa-3x mb-3 text-muted"></i>
                <h3>No internships found</h3>
                <p class="text-muted">Try adjusting your search criteria</p>
                <a href="home.php" class="btn btn-outline-primary mt-3">
                    <i class="fas fa-redo me-2"></i>Reset Search
                </a>
            </div>
        <?php endif; ?>
    </div>


    <style>
        .hero-section {
            background: linear-gradient(135deg, #4e73df 0%, #2e59d9 100%);
            padding: 80px 0;
            margin-bottom: 30px;
        }

        .search-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
        }

        .internship-card {
            transition: transform 0.3s ease;
        }
        
        .internship-card:hover {
            transform: translateY(-5px);
        }
        
        .company-logo {
            max-width: 80px;
            max-height: 80px;
            object-fit: contain;
        }
        
        .default-logo {
            width: 80px;
            height: 80px;
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
            border-radius: 50%;
            color: #6c757d;
        }
        
        .badge {
            font-weight: normal;
            padding: 0.5em 1em;
        }
        
        .btn-apply {
            background: linear-gradient(135deg, #4e73df 0%, #2e59d9 100%);
            color: white;
            border: none;
            border-radius: 25px;
            padding: 8px 20px;
            transition: all 0.3s ease;
        }
        
        .btn-apply:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(46, 89, 217, 0.2);
            color: white;
        }
        
        .deadline-text {
            color: #6c757d;
            font-size: 0.9rem;
        }
        
        .description-preview {
            color: #6c757d;
            font-size: 0.9rem;
            line-height: 1.5;
        }

        .form-select optgroup {
            color: #4e73df;
            font-weight: 600;
            padding: 5px 0;
        }
        
        .form-select option {
            color: #333;
            font-weight: normal;
            padding: 5px 15px;
        }
        
        .input-group:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }

        .btn-success {
            background-color: #28a745;
            border-color: #28a745;
            color: white;
            transition: all 0.3s ease;
        }

        .btn-success:hover {
            background-color: #218838;
            border-color: #1e7e34;
            transform: translateY(-2px);
        }

        .btn-outline-primary {
            transition: all 0.3s ease;
        }

        .btn-outline-primary:hover {
            transform: translateY(-2px);
        }

        .status-card {
            border-radius: 15px;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .status-card .display-4 {
            font-size: 2.5rem;
            font-weight: 600;
            margin-bottom: 0.5rem;
        }

        .status-card p {
            font-size: 1rem;
            font-weight: 500;
        }

        /* Status-specific colors */
        .status-card.border-primary { border-left: 4px solid #4e73df; }
        .status-card.border-warning { border-left: 4px solid #f6c23e; }
        .status-card.border-success { border-left: 4px solid #1cc88a; }
        .status-card.border-danger { border-left: 4px solid #e74a3b; }
    </style>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script>
        AOS.init();
    </script>
</body>
</html> 