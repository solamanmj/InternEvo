<?php
session_start();
require_once 'config.php';
require('razorpay-php/Razorpay.php');

// Check if user is logged in
if (!isset($_SESSION['logged_in']) || $_SESSION['user_type'] !== 'student') {
    header("Location: login.php");
    exit();
}

// Get internship details
if (!isset($_GET['internship_id'])) {
    header("Location: browse_internships.php");
    exit();
}

try {
    // Fetch internship details
    $stmt = $conn->prepare("
        SELECT i.*, cp.company_name 
        FROM internships i 
        JOIN company_profiles cp ON i.company_id = cp.company_id 
        WHERE i.id = ?
    ");
    $stmt->execute([$_GET['internship_id']]);
    $internship = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$internship) {
        throw new Exception("Internship not found");
    }

    // Initialize Razorpay
    $api_key = 'rzp_test_YOUR_KEY_HERE';
    $api_secret = 'YOUR_SECRET_HERE';

    $api = new Razorpay\Api\Api($api_key, $api_secret);

    // Create order
    $order = $api->order->create([
        'receipt' => 'intern_' . time(),
        'amount' => 50000, // Amount in paise (₹500)
        'currency' => 'INR',
        'payment_capture' => 1
    ]);

} catch(Exception $e) {
    $_SESSION['error'] = $e->getMessage();
    header("Location: browse_internships.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payment - InternEvo</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .payment-container {
            max-width: 600px;
            margin: 50px auto;
        }
        .internship-details {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
        }
        .payment-button {
            background: #2c3e50;
            color: white;
            padding: 15px 30px;
            border-radius: 5px;
            border: none;
            width: 100%;
            font-size: 18px;
        }
        .payment-button:hover {
            background: #34495e;
        }
    </style>
</head>
<body>
    <?php include 'navbar.php'; ?>

    <div class="container payment-container">
        <h2 class="mb-4">Complete Your Application</h2>

        <div class="internship-details">
            <h4><?php echo htmlspecialchars($internship['title']); ?></h4>
            <p class="text-muted"><?php echo htmlspecialchars($internship['company_name']); ?></p>
            <hr>
            <div class="row">
                <div class="col-6">
                    <p><strong>Duration:</strong><br><?php echo htmlspecialchars($internship['duration']); ?></p>
                </div>
                <div class="col-6">
                    <p><strong>Stipend:</strong><br>₹<?php echo htmlspecialchars($internship['stipend']); ?></p>
                </div>
            </div>
        </div>

        <div class="card mb-4">
            <div class="card-body">
                <h5 class="card-title">Payment Details</h5>
                <p class="card-text">Application Fee: ₹500</p>
                <p class="card-text small text-muted">This is a one-time fee for processing your application</p>
            </div>
        </div>

        <button id="rzp-button" class="payment-button">Pay Now</button>
    </div>

    <script src="https://checkout.razorpay.com/v1/checkout.js"></script>
    <script>
        var options = {
            "key": "<?php echo $api_key; ?>",
            "amount": "50000",
            "currency": "INR",
            "name": "InternEvo",
            "description": "Application Fee",
            "image": "your-logo-url.png",
            "order_id": "<?php echo $order['id']; ?>",
            "handler": function (response){
                // Send payment details to server
                document.location = 'process_payment.php?payment_id=' + response.razorpay_payment_id + 
                    '&order_id=' + response.razorpay_order_id + 
                    '&signature=' + response.razorpay_signature + 
                    '&internship_id=<?php echo $_GET['internship_id']; ?>';
            },
            "prefill": {
                "name": "<?php echo $_SESSION['name']; ?>",
                "email": "<?php echo $_SESSION['email']; ?>"
            },
            "theme": {
                "color": "#2c3e50"
            }
        };
        var rzp = new Razorpay(options);
        document.getElementById('rzp-button').onclick = function(e){
            rzp.open();
            e.preventDefault();
        }
    </script>
</body>
</html> 