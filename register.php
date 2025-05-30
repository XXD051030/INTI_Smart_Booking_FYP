<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Email Registration</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            padding: 20px;
        }

        .page-container {
            display: flex;
            width: 100%;
            max-width: 1200px;
            min-height: 600px;
            background: white;
            border-radius: 20px;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .left-section {
            flex: 1;
            background: linear-gradient(135deg, #1a73e8 0%, #0d47a1 100%);
            padding: 40px;
            color: white;
            display: flex;
            flex-direction: column;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .left-section::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="50" cy="50" r="40" fill="none" stroke="rgba(255,255,255,0.1)" stroke-width="2"/></svg>') center/cover;
            opacity: 0.1;
        }

        .welcome-text {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            position: relative;
        }

        .welcome-subtext {
            font-size: 1.1rem;
            opacity: 0.9;
            line-height: 1.6;
            margin-bottom: 30px;
        }

        .features-list {
            list-style: none;
            margin-top: 30px;
        }

        .features-list li {
            display: flex;
            align-items: center;
            margin-bottom: 15px;
            font-size: 1rem;
        }

        .features-list i {
            margin-right: 10px;
            color: #4CAF50;
        }

        .right-section {
            flex: 1;
            padding: 40px;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .form-title {
            text-align: left;
            margin-bottom: 2rem;
            color: #1a73e8;
            font-size: 28px;
            font-weight: 600;
        }

        .input-container {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .input {
            width: 100%;
            padding: 15px;
            border: 2px solid #e0e0e0;
            border-radius: 10px;
            outline: none;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .input:focus {
            border-color: #1a73e8;
            background: white;
            box-shadow: 0 0 0 4px rgba(26, 115, 232, 0.1);
        }

        .input-container label {
            position: absolute;
            left: 15px;
            top: 50%;
            transform: translateY(-50%);
            color: #666;
            pointer-events: none;
            transition: 0.3s;
            background: transparent;
            padding: 0 5px;
        }

        .input:focus + label,
        .input:not(:placeholder-shown) + label {
            top: 0;
            font-size: 12px;
            color: #1a73e8;
            background: white;
        }

        .content, .content-2, .content-3 {
            margin-top: -1rem;
            margin-bottom: 1rem;
            font-size: 14px;
            background: #f8f9fa;
            padding: 15px;
            border-radius: 8px;
        }

        .requirement-list, .requirement-list-2, .requirement-list-3 {
            list-style: none;
            padding: 0;
        }

        .requirement-list li, .requirement-list-2 li, .requirement-list-3 li {
            display: flex;
            align-items: center;
            margin-bottom: 0.5rem;
            color: #666;
        }

        .requirement-list i, .requirement-list-2 i, .requirement-list-3 i {
            margin-right: 8px;
        }

        .terms-container {
            margin: 1.5rem 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .terms-container input[type="checkbox"] {
            width: 20px;
            height: 20px;
            cursor: pointer;
            accent-color: #1a73e8;
        }

        .terms-container label {
            color: #666;
            font-size: 14px;
            cursor: pointer;
        }

        .terms-container a {
            color: #1a73e8;
            text-decoration: none;
        }

        .terms-container a:hover {
            text-decoration: underline;
        }

        #submit_btn {
            width: 100%;
            padding: 15px;
            background: #1a73e8;
            color: white;
            border: none;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            margin-top: 1rem;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        #submit_btn:hover {
            background: #1557b0;
            transform: translateY(-1px);
            box-shadow: 0 4px 12px rgba(26, 115, 232, 0.2);
        }

        #submit_btn:disabled {
            background: #ccc;
            cursor: not-allowed;
            transform: none;
            box-shadow: none;
        }

        .login-link {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .login-link a {
            color: #1a73e8;
            text-decoration: none;
            font-weight: 500;
        }

        .login-link a:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .page-container {
                flex-direction: column;
            }
            
            .left-section {
                padding: 30px;
            }

            .welcome-text {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="page-container">
        <div class="left-section">
          <div style="text-align: center; margin-bottom: 30px;">
            <img src="images/logo/inti_logo.png" alt="INTI Logo" style="height: 60px; width: auto;">
          </div>
          <h1 class="welcome-text">Welcome to INTI Reservation System</h1>
          <p class="welcome-subtext">
            Ready to make your reservations effortlessly? Join INTI Reservation System and book your facilities with ease.
          </p>
          <ul class="features-list">
            <li><i class="fas fa-check-circle"></i> Easy facility booking and management</li>
            <li><i class="fas fa-check-circle"></i> Real-time availability checking</li>
            <li><i class="fas fa-check-circle"></i> Join the INTI campus community</li>
          </ul>

        </div>
        <div class="right-section">
            <h2 class="form-title">Create your account</h2>
                <div class="input-container">
                    <input type="text" placeholder=" " name="username" id="username" class="input" required>
                    <label for="username">Username</label>
                </div>

                <div class="input-container">
                    <input type="email" placeholder=" " name="email" id="email" class="input" required>
                    <label for="email">INTI Email</label>
                </div>

                <div class="content-3" id="email-requirements" style="display: none;">
                    <ul class="requirement-list-3">
                        <li>
                            <i class="fa-solid fa-circle"></i>
                            <span id="email-error">Please use your INTI student email (@student.newinti.edu.my)</span>
                        </li>
                    </ul>
                </div>

                <div class="input-container">
                    <input type="password" placeholder=" " name="password" id="password" class="input" required>
                    <label for="password">Password</label>
                </div>

                <div class="content" id="password-requirements" style="display: none;">
                    <ul class="requirement-list">
                        <li>
                            <i class="fa-solid fa-circle"></i>
                            <span>At least 6 characters length</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-circle"></i>
                            <span>At least 1 number (0...9)</span>
                        </li>
                        <!-- <li>
                            <i class="fa-solid fa-circle"></i>
                            <span>At least 1 lowercase letter (a...z)</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-circle"></i>
                            <span>At least 1 special symbol (!...$)</span>
                        </li>
                        <li>
                            <i class="fa-solid fa-circle"></i>
                            <span>At least 1 uppercase letter (A...Z)</span>
                        </li> -->
                    </ul>
                </div>

                <div class="input-container">
                    <input type="password" placeholder=" " name="password_confirmation" id="password_confirmation" class="input" required>
                    <label for="password_confirmation">Confirm Password</label>
                </div>

                <div class="content-2" id="password-requirements-2" style="display: none;">
                    <ul class="requirement-list-2">
                        <li>
                            <i class="fa-solid fa-circle"></i>
                            <span id="error">Passwords do not match</span>
                        </li>
                    </ul>
                </div>

                <div class="terms-container">
                    <input type="checkbox" id="terms" name="terms" required>
                    <label for="terms">I agree to the <a href="terms.html" target="_blank">Terms and Conditions</a> and <a href="privacy.html" target="_blank">Privacy Policy</a></label>
                </div>

                <button type="submit" id="submit_btn" disabled>Create Account</button>

                <div class="login-link">
                    Already have an account? <a href="login.php">Sign in</a>
                </div>
        </div>
    </div>

    <script src="js/validations.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            // Add response message container after the form
            $('.right-section').append('<div id="response-message" style="margin-top: 20px; padding: 15px; border-radius: 8px; display: none;"></div>');

            $('#submit_btn').click(function(e) {
                e.preventDefault();
                
                // Get form data
                var formData = {
                    username: $('#username').val(),
                    email: $('#email').val(),
                    password: $('#password').val(),
                    password_confirmation: $('#password_confirmation').val()
                };

                // Send AJAX request
                $.ajax({
                    type: 'POST',
                    url: 'process_register.php',
                    data: formData,
                    dataType: 'json',
                    success: function(response) {
                        var messageDiv = $('#response-message');
                        messageDiv.show();
                        
                        if (response.success) {
                            window.location.href = 'otp-verify.php';
                        } else {
                            messageDiv.css({
                                'background-color': '#f8d7da',
                                'color': '#721c24',
                                'border': '1px solid #f5c6cb'
                            });
                            messageDiv.html(response.message);
                        }
                    },
                    error: function(xhr, status, error) {
                        var messageDiv = $('#response-message');
                        messageDiv.show();
                        messageDiv.css({
                            'background-color': '#f8d7da',
                            'color': '#721c24',
                            'border': '1px solid #f5c6cb'
                        });
                        messageDiv.html('Error: ' + xhr.responseText);
                    }
                });
            });
        });
    </script>
   
</body>
</html>
