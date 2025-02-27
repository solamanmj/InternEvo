<?php
session_start();
require_once 'config.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'company') {
    die('Unauthorized access');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Validate required fields
        $required_fields = ['title', 'category', 'description', 'type', 'duration', 
                          'stipend_amount', 'positions', 'skills', 'deadline', 'start_date'];
        
        foreach ($required_fields as $field) {
            if (empty($_POST[$field])) {
                throw new Exception("All required fields must be filled out");
            }
        }

        // Validate dates
        $deadline = new DateTime($_POST['deadline']);
        $start_date = new DateTime($_POST['start_date']);
        $today = new DateTime();

        if ($deadline < $today) {
            throw new Exception("Deadline cannot be in the past");
        }

        if ($start_date < $deadline) {
            throw new Exception("Start date must be after the application deadline");
        }

        // Insert internship posting
        $stmt = $conn->prepare("INSERT INTO internship_postings (
            company_id, title, category, description, type,
            duration, stipend_amount, positions_available,
            skills_required, application_deadline, start_date,
            status, created_at
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())");

        $stmt->bind_param("isssssdisss",
            $_SESSION['user_id'],
            $_POST['title'],
            $_POST['category'],
            $_POST['description'],
            $_POST['type'],
            $_POST['duration'],
            $_POST['stipend_amount'],
            $_POST['positions'],
            $_POST['skills'],
            $_POST['deadline'],
            $_POST['start_date']
        );

        if (!$stmt->execute()) {
            throw new Exception("Error posting internship: " . $conn->error);
        }

        $_SESSION['success'] = "Internship posted successfully!";
        header("Location: company-dashboard.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error'] = $e->getMessage();
        header("Location: post-internship.php");
        exit();
    }
}