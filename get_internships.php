<?php
require_once 'config.php';

try {
    // Fetch active internships with company details
    $stmt = $conn->prepare("
        SELECT 
            i.id,
            i.title,
            cp.company_name,
            i.location,
            i.stipend
        FROM internships i
        INNER JOIN company_profiles cp ON i.company_id = cp.company_id
        WHERE i.status = 'open'
        AND i.application_deadline >= CURRENT_DATE
        ORDER BY i.created_at DESC
    ");
    
    $stmt->execute();
    $internships = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Generate options for the select dropdown
    foreach ($internships as $internship) {
        $id = htmlspecialchars($internship['id']);
        $title = htmlspecialchars($internship['title']);
        $company = htmlspecialchars($internship['company_name']);
        $location = htmlspecialchars($internship['location']);
        $stipend = htmlspecialchars($internship['stipend']);
        
        echo "<option value=\"{$id}\">{$title} - {$company} ({$location}) - ₹{$stipend}/month</option>";
    }

} catch (PDOException $e) {
    error_log("Error fetching internships: " . $e->getMessage());
    echo "<option value=''>Error loading internships. Please try again later.</option>";
}
?> 