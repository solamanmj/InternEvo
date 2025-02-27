<?php
session_start();
header('Content-Type: application/json');

$response = [
    'logged_in' => false,
    'name' => '',
    'user_type' => ''
];

if (isset($_SESSION['logged_in']) && $_SESSION['logged_in']) {
    $response['logged_in'] = true;
    
    if ($_SESSION['user_type'] === 'student') {
        $response['name'] = $_SESSION['student_name'];
    } else if ($_SESSION['user_type'] === 'company') {
        $response['name'] = $_SESSION['company_name'];
    }
    
    $response['user_type'] = $_SESSION['user_type'];
}

echo json_encode($response); 