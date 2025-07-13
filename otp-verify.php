<?php

  session_start();
  include("db.php");
  require_once("Mailer.php");
  require_once("function.php");
  require_once("notification_functions.php");

  // Set timezone to Malaysia (Kuala Lumpur)
  date_default_timezone_set('Asia/Kuala_Lumpur');

  // Retrieve the email from the session (Take from 'process-register.php')
  $email = $_SESSION['email_reg'];
  
  // If the email is not set in the session, log an error and redirect to the registration page
  if(!isset($email)){
    $_SESSION['error'] = "Something went wrong. Please try again.";
    error_log("Connection failed: " ."Unable to catch Email Address" . "\n", 3, "../var/log/register_error.log");
    redirect("register.php");
  }

  // Check if the request method is POST
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Check if the action is to send an OTP (Get from countdown.js)
    if (isset($_POST['action']) && $_POST['action'] === 'sended') {
      
      // Generate a 6-digit OTP
      $otp = rand(100000, 999999);
      $expires_at = date('Y-m-d H:i:s', strtotime('+15 minutes'));

      // Get user_id from users table
      $user_query = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
      $user_query->execute([$email]);
      $user_data = $user_query->fetch(PDO::FETCH_ASSOC);
      $user_id = $user_data['user_id'];

      // Check if user_id exists in user_otp table
      $check_stmt = $pdo->prepare("SELECT user_id FROM user_otp WHERE user_id = ?");
      $check_stmt->execute([$user_id]);
      $exists = $check_stmt->fetch();

      if ($exists) {
          // Update existing OTP
          $otp_stmt = $pdo->prepare("UPDATE user_otp SET 
                                    otp_code = ?, 
                                    expires_at = ?,
                                    created_at = CURRENT_TIMESTAMP 
                                    WHERE user_id = ?");
          $otp_stmt->execute([$otp, $expires_at, $user_id]);
      } else {
          // Insert new OTP
          $otp_stmt = $pdo->prepare("INSERT INTO user_otp (user_id, otp_code, expires_at) 
                                    VALUES (?, ?, ?)");
          $otp_stmt->execute([$user_id, $otp, $expires_at]);
      }

      // Send the OTP to the user via email using new PHPMailer
      try {
        $mailer = new Mailer();
        $success = $mailer->sendVerificationCode($email, $otp, 'User');
        
        if (!$success) {
          error_log("Failed to send OTP email to: " . $email);
          $_SESSION['msg'] = "Failed to send OTP email. Please try again.";
        }
      } catch (Exception $e) {
        error_log("OTP Email error: " . $e->getMessage());
        $_SESSION['msg'] = "Failed to send OTP email. Please try again.";
      }
      
      redirect("otp-verify.php");
    }

    // verify otp 
    if(isset($_POST['action']) && $_POST['action'] === 'verify_otp'){
      $entered_otp = $_POST['otp'] ?? '';

      // Get user_id from users table
      $user_query = $pdo->prepare("SELECT user_id FROM users WHERE email = ?");
      $user_query->execute([$email]);
      $user_data = $user_query->fetch(PDO::FETCH_ASSOC);
      $user_id = $user_data['user_id'];

      // Retrieve OTP from user_otp table
      $sql = "SELECT otp_code, expires_at FROM user_otp WHERE user_id = ? ORDER BY created_at DESC LIMIT 1";
      $stmt = $pdo->prepare($sql);
      $stmt->execute([$user_id]);
      $row = $stmt->fetch(PDO::FETCH_ASSOC);

      if ($row) {
        $stored_otp = $row['otp_code'];
        $expires_at = strtotime($row['expires_at']);
        $current_time = time();

        // Check if OTP is valid (within 15 minutes)
        if ($current_time > $expires_at) {
            $_SESSION['msg'] = "OTP has expired. Please request a new one.";

        } elseif ($entered_otp == $stored_otp) {
          // OTP is correct, verify the user
          $sql = "UPDATE users SET is_verified = 1 WHERE email = ?";
          $stmt = $pdo->prepare($sql);
          $stmt->execute([$email]);

          // Delete used OTP
          $delete_otp = $pdo->prepare("DELETE FROM user_otp WHERE user_id = ?");
          $delete_otp->execute([$user_id]);

          // Create welcome notification for new user
          try {
              createWelcomeNotification($user_id);
          } catch (Exception $e) {
              error_log("Error creating welcome notification: " . $e->getMessage());
          }

          unset($_SESSION['email_reg']);
          $_SESSION['message'] = "Your account has been successfully created and registered.";
          redirect("login.php");
        } else {
          $_SESSION['msg'] = "The OTP you entered is invalid. Kindly try again.";
        }
      } else {
          $_SESSION['msg'] = "Please send the OTP first before attempting to verify.";
      }
    }
  }

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>OTP Verification</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.3.0/css/all.min.css">
    
    <style>
        body {
            background-color: #f8f9fa;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0;
            padding: 20px;
        }
        .otp-container {
            background: white;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.1);
            padding: 2.5rem;
            max-width: 450px;
            width: 100%;
            margin: 0 auto;
        }
        .icon-illustration {
            position: relative;
            width: 110px;
            height: 90px;
            margin: 0 auto 1.5rem auto;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .icon-envelope-bg {
            background: #ffb84d;
            border-radius: 18px 18px 18px 18px;
            width: 90px;
            height: 65px;
            display: flex;
            align-items: center;
            justify-content: center;
            position: absolute;
            left: 10px;
            top: 15px;
            z-index: 1;
        }
        .icon-envelope {
            color: #fff;
            font-size: 2.8rem;
            z-index: 2;
        }
        .icon-key {
            position: absolute;
            left: -5px;
            top: 35px;
            background: #ff6f3c;
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 3;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
        }
        .icon-key i {
            color: #fff;
            font-size: 1.1rem;
        }
        .icon-password {
            position: absolute;
            right: -12px;
            top: 41px;
            background: #fff;
            border-radius: 8px;
            padding: 1px 8px;
            font-size: 0.9rem;
            color: #222;
            font-weight: 600;
            letter-spacing: 0.2em;
            box-shadow: 0 2px 8px rgba(0,0,0,0.08);
            z-index: 4;
        }
        .logo-container {
            display: none;
        }
        .otp-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #222;
            margin-bottom: 0.5rem;
            text-align: center;
        }
        .otp-subtitle {
            color: #6c757d;
            text-align: center;
            margin-bottom: 2.5rem;
            font-size: 0.95rem;
        }
        .otp-inputs {
            display: flex;
            gap: 0.75rem;
            justify-content: center;
            margin-bottom: 2rem;
            padding: 0 1rem;
        }
        .otp-inputs input {
            width: 45px;
            height: 45px;
            text-align: center;
            font-size: 1.5rem;
            border: 2px solid #dee2e6;
            border-radius: 8px;
            background: #f8f9fa;
            transition: all 0.3s ease;
            padding: 0;
        }
        .otp-inputs input:focus {
            border-color: #0d6efd;
            background: white;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
            outline: none;
        }
        .otp-inputs input.filled {
            border-color: #198754;
            background: white;
        }
        .btn-verify {
            background-color: #0d6efd;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 500;
            width: 100%;
            margin-top: 1rem;
            max-width: 300px;
            margin-left: auto;
            margin-right: auto;
            display: block;
        }
        .btn-resend {
            background-color: transparent;
            color: #0d6efd;
            border: none;
            padding: 0.5rem;
            font-weight: 500;
            margin-top: 1.5rem;
            display: block;
            margin-left: auto;
            margin-right: auto;
        }
        .btn-resend:disabled {
            color: #6c757d;
        }
        .alert {
            border-radius: 8px;
            margin: 1rem auto;
            max-width: 300px;
        }
        .back-button {
            position: fixed;
            top: 1rem;
            left: 1rem;
            background: white;
            border: none;
            border-radius: 50%;
            width: 40px;
            height: 40px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: #2c3e50;
            text-decoration: none;
            z-index: 1000;
        }
        @media (max-width: 480px) {
            .otp-container {
                padding: 1.5rem;
            }
            .otp-inputs {
                gap: 0.5rem;
            }
            .otp-inputs input {
                width: 40px;
                height: 40px;
                font-size: 1.25rem;
            }
            .icon-illustration {
                width: 80px;
                height: 65px;
            }
            .icon-envelope-bg {
                width: 65px;
                height: 45px;
                left: 7px;
                top: 10px;
            }
            .icon-envelope {
                font-size: 2rem;
            }
            .icon-key {
                width: 22px;
                height: 22px;
                left: -7px;
                top: 25px;
            }
            .icon-key i {
                font-size: 0.8rem;
            }
            .icon-password {
                right: -22px;
                top: 25px;
                font-size: 0.8rem;
                padding: 1px 10px;
            }
        }
    </style>
</head>

<body>
    <a href="register.php" class="back-button">
        <i class="fas fa-arrow-left"></i>
    </a>

    <div class="container">
        <div class="otp-container">
            <!-- INTI Logo -->
            <div style="text-align: center; margin-bottom: 20px;">
                <img src="images/logo/inti_logo.png" alt="INTI Logo" style="height: 50px; width: auto;">
            </div>
            
            <!-- Email Illustration -->
            <div class="icon-illustration">
                <div class="icon-envelope-bg"></div>
                <i class="fa-solid fa-envelope icon-envelope"></i>
                <div class="icon-key"><i class="fa-solid fa-key"></i></div>
                <div class="icon-password">******</div>
            </div>
            <!-- End Email Illustration -->
            
            <h1 class="otp-title">Verify Your Email Address</h1>
            <p class="otp-subtitle">Please click the "Send OTP" button below to receive your 6-digit verification code. If you don't see the email in your inbox, please check your spam folder.</p>

            <form action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>" method="post" id="otpForm">
                <input type="hidden" name="action" value="verify_otp">
                <input type="hidden" name="otp" id="otpInput">
                
                <div class="otp-inputs">
                    <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" class="otp-input">
                    <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" class="otp-input">
                    <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" class="otp-input">
                    <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" class="otp-input">
                    <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" class="otp-input">
                    <input type="text" maxlength="1" pattern="[0-9]" inputmode="numeric" autocomplete="off" class="otp-input">
                </div>

                <?php if (!empty($_SESSION['msg'])): ?>
                    <div class="alert alert-danger" role="alert" id="alertMessage">
                        <?php 
                            echo htmlspecialchars($_SESSION['msg']);
                            unset($_SESSION['msg']);
                        ?>
                    </div>
                <?php endif; ?>

                <button type="submit" class="btn btn-verify">
                    Verify Account
                </button>
            </form>

            <div class="text-center">
                <button type="button" 
                        id="send-email" 
                        class="btn-resend" 
                        onclick="startCountdown('Send OTP', 'Resend OTP','otp-verify.php')">
                    Send OTP
                </button>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS and Popper.js -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.min.js"></script>
    <!-- Your existing countdown script -->
    <script src="js/countdown.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const otpInputs = document.querySelectorAll('.otp-input');
            const otpForm = document.getElementById('otpForm');
            const hiddenOtpInput = document.getElementById('otpInput');

            // Function to handle input
            function handleInput(e) {
                const input = e.target;
                
                // Only allow numbers
                input.value = input.value.replace(/[^0-9]/g, '');
                
                // Add filled class if there's a value
                if (input.value) {
                    input.classList.add('filled');
                } else {
                    input.classList.remove('filled');
                }

                // Move to next input if current input is filled
                if (input.value && input.nextElementSibling) {
                    input.nextElementSibling.focus();
                }

                // Combine all inputs for the hidden field
                const otp = Array.from(otpInputs).map(input => input.value).join('');
                hiddenOtpInput.value = otp;
            }

            // Function to handle backspace
            function handleBackspace(e) {
                const input = e.target;
                
                if (e.key === 'Backspace' && !input.value && input.previousElementSibling) {
                    input.previousElementSibling.focus();
                }
            }

            // Add event listeners to all inputs
            otpInputs.forEach(input => {
                input.addEventListener('input', handleInput);
                input.addEventListener('keydown', handleBackspace);
            });

            // Handle paste event
            document.addEventListener('paste', function(e) {
                e.preventDefault();
                const pastedData = e.clipboardData.getData('text').slice(0, 6);
                if (/^\d+$/.test(pastedData)) {
                    otpInputs.forEach((input, index) => {
                        input.value = pastedData[index] || '';
                        if (input.value) {
                            input.classList.add('filled');
                        }
                    });
                    hiddenOtpInput.value = pastedData;
                }
            });
        });

        // Auto-hide alert message after 8 seconds
        document.addEventListener('DOMContentLoaded', function() {
            const alertMessage = document.getElementById('alertMessage');
            if (alertMessage) {
                setTimeout(function() {
                    alertMessage.style.transition = 'opacity 0.5s ease';
                    alertMessage.style.opacity = '0';
                    setTimeout(function() {
                        alertMessage.remove();
                    }, 500);
                }, 5000);
            }
        });
    </script>
</body>
</html>