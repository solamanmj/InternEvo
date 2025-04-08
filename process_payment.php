<?php
session_start();
require_once 'config.php';
require('razorpay-php/Razorpay.php');

if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

try {
    $api_key = 'rzp_test_YOUR_KEY_HERE';
    $api_secret = 'YOUR_SECRET_HERE';

    // Verify payment signature
    $api = new Razorpay\Api\Api($api_key, $api_secret); 
    $attributes = array(
        'razorpay_order_id' => $_GET['order_id'],
        'razorpay_payment_id' => $_GET['payment_id'],
        'razorpay_signature' => $_GET['signature']
    );

    $api->utility->verifyPaymentSignature($attributes);

    // Payment verified, now save application
    $stmt = $conn->prepare("
        INSERT INTO internship_applications 
        (internship_id, student_id, payment_id, status, created_at) 
        VALUES (?, ?, ?, 'pending', NOW())
    ");
    $stmt->execute([
        $_GET['internship_id'],
        $_SESSION['user_id'],
        $_GET['payment_id']
    ]);

    $_SESSION['success'] = "Payment successful! Your application has been submitted.";
    header("Location: student_dashboard.php");

} catch(Exception $e) {
    error_log("Payment verification failed: " . $e->getMessage());
    $_SESSION['error'] = "Payment verification failed. Please contact support.";
    header("Location: student_dashboard.php");
}
?> 