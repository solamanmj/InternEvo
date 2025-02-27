<?php
$host = '127.0.0.1';
$user = 'root';  // your database username
$pass = '';      // your database password

try {
    // First connect without specifying a database
    $conn = new PDO("mysql:host=$host", $user, $pass);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    // Create database if it doesn't exist
    $conn->exec("CREATE DATABASE IF NOT EXISTS internevo");
    $conn->exec("USE internevo");
    
    // Create tables using PHP to execute SQL
    $sql = "CREATE TABLE IF NOT EXISTS users (
        id INT PRIMARY KEY AUTO_INCREMENT,
        firstname VARCHAR(50),
        lastname VARCHAR(50),
        email VARCHAR(100) UNIQUE,
        password VARCHAR(255),
        user_type ENUM('user', 'admin') DEFAULT 'user',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);

    $sql = "CREATE TABLE IF NOT EXISTS courses (
        id INT PRIMARY KEY AUTO_INCREMENT,
        title VARCHAR(100),
        description TEXT,
        price DECIMAL(10,2),
        instructor_id INT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (instructor_id) REFERENCES users(id)
    )";
    $conn->exec($sql);

    $sql = "CREATE TABLE IF NOT EXISTS enrollments (
        id INT PRIMARY KEY AUTO_INCREMENT,
        user_id INT,
        course_id INT,
        enrollment_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id),
        FOREIGN KEY (course_id) REFERENCES courses(id)
    )";
    $conn->exec($sql);

    $sql = "CREATE TABLE IF NOT EXISTS companies (
        id INT PRIMARY KEY AUTO_INCREMENT,
        company_name VARCHAR(100),
        company_email VARCHAR(100) UNIQUE,
        company_address TEXT,
        contact_person VARCHAR(100),
        contact_number VARCHAR(20),
        business_type VARCHAR(50),
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    )";
    $conn->exec($sql);

    return $conn;

} catch (PDOException $e) {
    $_SESSION['error'] = "Connection error. Please try again later.";
    header("Location: login.php");
    exit();
}

// Add this at the top of admin-dashboard.php
require_once 'db_connect.php';

// Fetch total users
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE user_type = 'user'");
$totalUsers = $stmt->fetch()['total'];

// Fetch active courses
$stmt = $pdo->query("SELECT COUNT(*) as total FROM courses");
$totalCourses = $stmt->fetch()['total'];

// Fetch total revenue
$stmt = $pdo->query("SELECT SUM(price) as total FROM courses JOIN enrollments ON courses.id = enrollments.course_id");
$totalRevenue = $stmt->fetch()['total'];

// Fetch new enrollments (last 30 days)
$stmt = $pdo->query("SELECT COUNT(*) as total FROM enrollments WHERE enrollment_date >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
$newEnrollments = $stmt->fetch()['total'];

// Fetch recent activity
$stmt = $pdo->query("SELECT * FROM (
    SELECT 'user' as type, firstname, lastname, created_at 
    FROM users 
    UNION ALL 
    SELECT 'enrollment' as type, courses.title, users.firstname, enrollments.enrollment_date
    FROM enrollments 
    JOIN users ON users.id = enrollments.user_id
    JOIN courses ON courses.id = enrollments.course_id
) as activity 
ORDER BY created_at DESC 
LIMIT 5");
$recentActivity = $stmt->fetchAll();
?>