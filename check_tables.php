<?php
require_once 'config.php';

// Enable error reporting
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    // Check if students table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'students'");
    $hasStudentsTable = $stmt->rowCount() > 0;
    
    echo "Students table exists: " . ($hasStudentsTable ? "Yes" : "No") . "<br>";
    
    if ($hasStudentsTable) {
        $stmt = $conn->query("SELECT * FROM students");
        $students = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Number of students: " . count($students) . "<br>";
        echo "Students data:<br>";
        echo "<pre>";
        print_r($students);
        echo "</pre>";
    }
    
    // Check if student_profiles table exists
    $stmt = $conn->query("SHOW TABLES LIKE 'student_profiles'");
    $hasProfilesTable = $stmt->rowCount() > 0;
    
    echo "Student_profiles table exists: " . ($hasProfilesTable ? "Yes" : "No") . "<br>";
    
    if ($hasProfilesTable) {
        $stmt = $conn->query("SELECT * FROM student_profiles");
        $profiles = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "Number of profiles: " . count($profiles) . "<br>";
        echo "Profiles data:<br>";
        echo "<pre>";
        print_r($profiles);
        echo "</pre>";
    }
    
} catch(PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>
