<?php
session_start();
$error_message = '';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'PHPMailer-master/src/Exception.php';
require 'PHPMailer-master/src/PHPMailer.php';
require 'PHPMailer-master/src/SMTP.php';

function generateVerificationCode($length = 6) {
    return str_pad(random_int(0, pow(10, $length) - 1), $length, '0', STR_PAD_LEFT);
}

function sendVerificationEmail($recipientEmail, $verificationCode) {
    $mail = new PHPMailer(true);
    try {
        $mail->isSMTP();
        $mail->Host       = 'smtp.gmail.com';
        $mail->SMTPAuth   = true;
        $mail->Username   = 'taskmate0369@gmail.com';
        $mail->Password   = 'olal qtcm wdhl cyyx';
        $mail->SMTPSecure = PHPMailer::ENCRYPTION_STARTTLS;
        $mail->Port       = 587;

        $mail->setFrom('taskmate0369@gmail.com', 'Taskmate');
        $mail->addAddress($recipientEmail);
        $mail->Subject = 'Your Verification Code';
        $mail->Body    = "Your verification code is: $verificationCode\n\nThis code will expire in 10 minutes.";

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("Email sending failed: " . $mail->ErrorInfo);
        return false;
    }
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['email'])) {
        $email = $_POST['email'];
        $verificationCode = generateVerificationCode();
        $_SESSION['verification_code'] = $verificationCode;
        $_SESSION['email'] = $email;

        if (sendVerificationEmail($email, $verificationCode)) {
            echo"";
        } else {
            $error_message= "Failed to send verification code.";
        }
    } elseif (isset($_POST['verify'])) {
        $enteredOTP = $_POST['otp1'] . $_POST['otp2'] . $_POST['otp3'] . $_POST['otp4'] . $_POST['otp5'] . $_POST['otp6'];
        if ($enteredOTP == $_SESSION['verification_code']) {
            header('Location: resetpassword.php');
            unset($_SESSION['verification_code']);  
        } else {
            $error_message="Incorrect OTP.";
        }
    }
}
?>
3)

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskMate - OTP Verification</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .login-container {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            animation: slideUp 0.5s ease-out;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            font-weight: 700;
        }

        .task { color: #2563eb; }
        .mate { color: #3b82f6; }

        h2 {
            text-align: center;
            color: #1e293b;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #64748b;
            font-size: 0.9rem;
        }

        .otp-inputs {
            display: flex;
            justify-content: space-between;
        }

        .otp-inputs input {
            width: 3rem;
            height: 3rem;
            text-align: center;
            font-size: 1.5rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            transition: all 0.3s ease;
        }

        .otp-inputs input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 1rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .resend-text {
            text-align: center;
            margin-top: 1.5rem;
            color: #64748b;
        }

        .resend-text a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.3s ease;
        }

        .resend-text a:hover {
            color: #1d4ed8;
        }
        .error-message {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }

        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <span class="task">Task</span><span class="mate">Mate</span>
        </div>
        <h2>OTP Verification</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>
        <p style="text-align: center; color: #64748b; margin-bottom: 1.5rem;">Enter the 6-digit code sent to your email.</p>
        <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group otp-inputs">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp2')" id="otp1" name="otp1">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp3')" id="otp2" name="otp2">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp4')" id="otp3" name="otp3">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp5')" id="otp4" name="otp4">
                <input type="text" maxlength="1" required oninput="moveToNext(this, 'otp6')" id="otp5" name="otp5">
                <input type="text" maxlength="1" required id="otp6" name="otp6">
            </div>
            <button type="submit" class="login-btn" name="verify">Verify OTP</button>
            <p class="resend-text">Didn't receive the code? <a href="#">Resend OTP</a></p>
        </form>
    </div>

    <script>
        function moveToNext(current, nextFieldID) {
            if (current.value.length === 1) {
                document.getElementById(nextFieldID)?.focus();
            }
        }
    </script>
</body>
</html>
 <?php
$error_message = '';

if (!isset($_SESSION['email'])) {
    header('Location:forgetpassword.php');
    exit();
}
$conn= new mysqli('localhost','root','','internevo');
if ($conn->connect_error) {
    error_log("Connection failed: " . $conn->connect_error);
    $error_message = "An error occurred during login. Please try again later.";
} else {
    if($_SERVER["REQUEST_METHOD"]=="POST"){
        $password= $_POST['new_password'];
        $confirm_password= $_POST['confirm_password'];
        if($password==$confirm_password){
            $hashed_password = password_hash($password, PASSWORD_BCRYPT);
            $sql = "UPDATE users SET password = '$hashed_password' WHERE email = '" . $_SESSION['email'] . "'";
           
            if ($conn->query($sql) === TRUE) {
                $_SESSION['success_message'] = "Your password has been successfully updated!";
                header('Location: signin.php');
                unset($_SESSION['email']);  
                exit();
            } else {
                $error_message = "Error updating password: " . $conn->error;
            }
           
    }
   
}
}  

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaskMate - Reset Password</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Poppins', sans-serif;
        }

        body {
            background: linear-gradient(135deg, #f0f7ff 0%, #ffffff 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 2rem;
        }

        .login-container {
            background: white;
            padding: 2.5rem;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
            animation: slideUp 0.5s ease-out;
        }

        .logo {
            text-align: center;
            margin-bottom: 2rem;
            font-size: 2rem;
            font-weight: 700;
        }

        .task { color: #2563eb; }
        .mate { color: #3b82f6; }

        h2 {
            text-align: center;
            color: #1e293b;
            margin-bottom: 2rem;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            color: #64748b;
            font-size: 0.9rem;
        }

        .form-group input {
            width: 100%;
            padding: 0.9rem;
            border: 2px solid #e2e8f0;
            border-radius: 10px;
            font-size: 1rem;
            transition: all 0.3s ease;
        }

        .form-group input:focus {
            outline: none;
            border-color: #2563eb;
            box-shadow: 0 0 0 3px rgba(37, 99, 235, 0.1);
        }

        .login-btn {
            width: 100%;
            padding: 1rem;
            background: #2563eb;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .login-btn:hover {
            background: #1d4ed8;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(37, 99, 235, 0.2);
        }

        .error-message {
            color: #dc2626;
            font-size: 0.85rem;
            margin-top: 0.5rem;
            font-weight: 400;
        }

        .error-message1 {
            background-color: #fee2e2;
            color: #dc2626;
            padding: 0.75rem;
            border-radius: 8px;
            margin-bottom: 1rem;
            text-align: center;
        }
        @keyframes slideUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo">
            <span class="task">Task</span><span class="mate">Mate</span>
        </div>
        <h2>Reset Password</h2>
        <?php if (!empty($error_message)): ?>
            <div class="error-message1">
                <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <form id="resetForm" method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
            <div class="form-group">
                <label for="new_password">New Password</label>
                <input type="password" id="new_password" name="new_password" required minlength="6">
                <div id="password-error" class="error-message"></div> <!-- Error message for password -->
            </div>
            <div class="form-group">
                <label for="confirm_password">Confirm Password</label>
                <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                <div id="cpassword-error" class="error-message"></div> <!-- Error message for confirm password -->
            </div>
            <button type="submit" class="login-btn">Reset Password</button>
        </form>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const passwordError = document.getElementById('password-error');
            const cpasswordError = document.getElementById('cpassword-error');

            const passwordInput = document.getElementById('new_password');
            const cpasswordInput = document.getElementById('confirm_password');

            function checkPassword() {
                const password = passwordInput.value.trim();
                const passwordPattern = /^(?=.[a-z])(?=.[A-Z])(?=.\d)[A-Za-z\d@$!%?&]{8,}$/;

                if (password === '') {
                    passwordError.innerHTML = "Password is required";
                    passwordInput.style.border = "2px solid red";
                    return false;
                } else if (!passwordPattern.test(password)) {
                    passwordError.innerHTML = "Password must be 8+ characters with uppercase, lowercase, and a number.";
                    passwordInput.style.border = "2px solid red";
                    return false;
                } else {
                    passwordError.innerHTML = "";
                    passwordInput.style.border = "2px solid green";
                    return true;
                }
            }

            function checkConfirmPassword() {
                const password = passwordInput.value.trim();
                const confirmPassword = cpasswordInput.value.trim();
               
                // Confirm password should only be checked if both fields are filled
                if (confirmPassword === '') {
                    cpasswordError.innerHTML = "Please confirm your password";
                    cpasswordInput.style.border = "2px solid red";
                    return false;
                } else if (confirmPassword !== password) {
                    cpasswordError.innerHTML = "Passwords do not match";
                    cpasswordInput.style.border = "2px solid red";
                    return false;
                } else {
                    cpasswordError.innerHTML = "";
                    cpasswordInput.style.border = "2px solid green";
                    return true;
                }
            }

            passwordInput.addEventListener('input', function() {
            checkPassword();
            if (cpasswordInput.value !== '') {
                checkConfirmPassword();
            }
        });
        cpasswordInput.addEventListener('input', checkConfirmPassword);

            document.getElementById('resetForm').addEventListener('submit', function(e) {
                e.preventDefault();
               
                let isValid = true;
                if (!checkPassword()) isValid = false;
                if (!checkConfirmPassword()) isValid = false;
               
                if (isValid) {
                    console.log('Form is valid, submitting...');
                    this.submit();  
                } else {
                    console.log('Form has errors, not submitting.');
                }
            });
        });
    </script>
</body>
</html>