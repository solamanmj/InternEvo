<?php
session_start();
require_once 'config.php';

// Get search query
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Prepare the search query
try {
    if (!empty($search)) {
        $stmt = $conn->prepare("
            SELECT 
                i.id,
                i.title,
                i.description,
                i.requirements,
                i.location,
                i.stipend,
                i.duration,
                i.deadline,
                i.posted_date,
                c.company_name,
                c.logo_url
            FROM internship_postings i
            JOIN company_profiles c ON i.company_id = c.company_id
            WHERE 
                i.title LIKE :search OR
                i.description LIKE :search OR
                i.location LIKE :search OR
                i.requirements LIKE :search OR
                c.company_name LIKE :search
            ORDER BY i.posted_date DESC
        ");
        
        $searchParam = "%{$search}%";
        $stmt->bindParam(':search', $searchParam);
        $stmt->execute();
        $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Debug: Print the query and results
        echo "<!-- Search Query: " . $search . " -->";
        echo "<!-- Number of results: " . count($results) . " -->";
    }
} catch(PDOException $e) {
    $_SESSION['error'] = "Search error: " . $e->getMessage();
    // Debug: Print the error
    echo "<!-- Database Error: " . $e->getMessage() . " -->";
}

// Debug: Print the table structure
try {
    $tables = $conn->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    echo "<!-- Available tables: " . implode(", ", $tables) . " -->";
    
    $columns = $conn->query("SHOW COLUMNS FROM internship_postings")->fetchAll(PDO::FETCH_COLUMN);
    echo "<!-- internship_postings columns: " . implode(", ", $columns) . " -->";
} catch(PDOException $e) {
    echo "<!-- Table structure error: " . $e->getMessage() . " -->";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Search Results - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background: linear-gradient(135deg, #f5f7fa, #c3cfe2);
            min-height: 100vh;
            padding: 20px;
        }

        .search-results {
            max-width: 800px;
            margin: 0 auto;
        }

        .internship-card {
            background: white;
            border-radius: 10px;
            padding: 20px;
            margin-bottom: 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            transition: transform 0.3s ease;
        }

        .internship-card:hover {
            transform: translateY(-5px);
        }

        .company-logo {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 5px;
        }

        .internship-title {
            color: #2c3e50;
            font-size: 1.2rem;
            margin-bottom: 10px;
        }

        .company-name {
            color: #4e73df;
            font-weight: 500;
        }

        .location, .stipend, .duration {
            display: inline-block;
            padding: 5px 10px;
            background: rgba(78, 115, 223, 0.1);
            border-radius: 15px;
            margin-right: 10px;
            font-size: 0.9rem;
            color: #4e73df;
        }

        .apply-btn {
            background: linear-gradient(135deg, #4e73df, #224abe);
            color: white;
            padding: 8px 20px;
            border-radius: 5px;
            text-decoration: none;
            display: inline-block;
            margin-top: 10px;
            transition: all 0.3s ease;
        }

        .apply-btn:hover {
            background: linear-gradient(135deg, #224abe, #4e73df);
            color: white;
            transform: translateY(-2px);
        }

        .no-results {
            text-align: center;
            padding: 50px;
            background: white;
            border-radius: 10px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="search-results">
            <h2 class="mb-4">Search Results for "<?php echo htmlspecialchars($search); ?>"</h2>

            <?php if (isset($results)): ?>
                <?php if (empty($results)): ?>
                    <div class="no-results">
                        <h3>No internships found</h3>
                        <p>Try different search terms or browse all internships</p>
                        <a href="index.php" class="btn btn-primary mt-3">Back to Home</a>
                    </div>
                <?php else: ?>
                    <?php foreach ($results as $internship): ?>
                        <div class="internship-card">
                            <div class="d-flex align-items-center mb-3">
                                <img src="<?php echo htmlspecialchars($internship['logo_url'] ?: 'uploads/company_logos/default-logo.png'); ?>" 
                                     alt="Company Logo" 
                                     class="company-logo me-3">
                                <div>
                                    <h3 class="internship-title mb-1">
                                        <?php echo htmlspecialchars($internship['title']); ?>
                                    </h3>
                                    <div class="company-name">
                                        <?php echo htmlspecialchars($internship['company_name']); ?>
                                    </div>
                                </div>
                            </div>

                            <div class="mb-3">
                                <span class="location">
                                    📍 <?php echo htmlspecialchars($internship['location']); ?>
                                </span>
                                <span class="stipend">
                                    💰 ₹<?php echo htmlspecialchars($internship['stipend']); ?>
                                </span>
                                <span class="duration">
                                    ⏱️ <?php echo htmlspecialchars($internship['duration']); ?> months
                                </span>
                            </div>

                            <div class="mb-3">
                                <strong>Description:</strong><br>
                                <?php echo nl2br(htmlspecialchars($internship['description'])); ?>
                            </div>

                            <div class="mb-3">
                                <strong>Requirements:</strong><br>
                                <?php echo nl2br(htmlspecialchars($internship['requirements'])); ?>
                            </div>

                            <div class="mb-3">
                                <strong>Application Deadline:</strong>
                                <?php echo date('d M Y', strtotime($internship['deadline'])); ?>
                            </div>

                            <a href="apply_internship.php?id=<?php echo $internship['id']; ?>" 
                               class="apply-btn">Apply Now</a>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Debug information -->
    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger">
            <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
            ?>
        </div>
    <?php endif; ?>
</body>
</html>