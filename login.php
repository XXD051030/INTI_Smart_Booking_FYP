<?php
session_start();

// Check if user is already logged in, redirect to dashboard
if (isset($_SESSION['user_id'])) {
    header('Location: general.php');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Reservation System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">
    <link rel="stylesheet" href="css/login.css">
</head>
<body class="login-body">
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <img src="images/logo/inti_logo.png" alt="INTI Logo" class="login-logo">
                <h2>Welcome Back</h2>
                <p class="text-muted">Sign in to your account</p>
            </div>
            
            <form class="login-form" id="loginForm">
                <div class="form-group">
                    <label for="email" class="form-label">
                        <i class="fas fa-envelope"></i> Email Address
                    </label>
                    <input type="email" class="form-control" id="email" name="email" placeholder="Enter your email" required>
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">
                        <i class="fas fa-lock"></i> Password
                    </label>
                    <div class="password-wrapper">
                        <input type="password" class="form-control" id="password" name="password" placeholder="Enter your password" required>
                        <button type="button" class="password-toggle" onclick="togglePassword()">
                            <i class="fas fa-eye" id="toggleIcon"></i>
                        </button>
                    </div>
                </div>
                
                <div id="errorMessage" class="alert alert-danger d-none mb-3"></div>
                
                <div class="form-options">
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" id="remember" name="remember">
                        <label class="form-check-label" for="remember">
                            Remember me
                        </label>
                    </div>
                    <a href="#" class="forgot-password">Forgot password?</a>
                </div>
                
                <button type="submit" class="btn-login" id="loginButton">
                    <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="loginSpinner"></span>
                    <span id="loginButtonText"><i class="fas fa-sign-in-alt"></i> Sign In</span>
                </button>
                
                <div class="signup-link">
                    <p>Don't have an account? <a href="register.php">Sign up here</a></p>
                </div>
            </form>
        </div>
        
        <div class="login-info">
            <div class="info-card">
                <i class="fas fa-calendar-check"></i>
                <h4>Easy Booking</h4>
                <p>Book rooms and facilities with just a few clicks</p>
            </div>
            <div class="info-card">
                <i class="fas fa-clock"></i>
                <h4>Real-time Updates</h4>
                <p>Get instant notifications about your reservations</p>
            </div>
            <div class="info-card">
                <i class="fas fa-shield-alt"></i>
                <h4>Secure Platform</h4>
                <p>Your data is protected with enterprise-grade security</p>
            </div>
        </div>
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }

        // Handle form submission with AJAX
        document.getElementById('loginForm').addEventListener('submit', function (e) {
            e.preventDefault();
            
            // Get form elements
            const form = this;
            const formData = new FormData(form);
            const errorMessage = document.getElementById('errorMessage');
            const loginButton = document.getElementById('loginButton');
            const loginSpinner = document.getElementById('loginSpinner');
            const loginButtonText = document.getElementById('loginButtonText');
            
            // Show loading state
            loginButton.disabled = true;
            loginSpinner.classList.remove('d-none');
            loginButtonText.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Signing In...';
            errorMessage.classList.add('d-none');

            // Send AJAX request
            fetch('login_handler.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {   
                if (data.success) {
                    // Login successful - redirect to general page
                    window.location.href = 'general.php';
                } else {
                    // Show error message
                    errorMessage.textContent = data.message;
                    errorMessage.classList.remove('d-none');
                    
                    // Reset button state
                    loginButton.disabled = false;
                    loginSpinner.classList.add('d-none');
                    loginButtonText.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
                }
            })
            .catch(error => {
                // Show error message
                errorMessage.textContent = 'An error occurred. Please try again.';
                errorMessage.classList.remove('d-none');
                
                // Reset button state
                loginButton.disabled = false;
                loginSpinner.classList.add('d-none');
                loginButtonText.innerHTML = '<i class="fas fa-sign-in-alt"></i> Sign In';
            });
        });

        // Add fade-in animation
        window.addEventListener('load', function() {
            document.querySelector('.login-card').classList.add('fade-in');
            document.querySelector('.login-info').classList.add('fade-in');
        });
    </script>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 