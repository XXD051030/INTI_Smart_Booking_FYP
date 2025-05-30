<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login with Google and Apple</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
  <style>
    body {
      background-color: #f8f9fa;
      min-height: 100vh;
    }

    .login-container {
      background: #f8f9fa;
      padding: 2rem;
      width: 100%;
      max-width: 830px;
    }

    .social-login-btn {
      width: 100%;
      padding: 10px;
      margin: 10px 0;
      border: 1px solid #ddd;
      border-radius: 5px;
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 10px;
      cursor: pointer;
      transition: all 0.3s ease;
    }

    .social-login-btn:hover {
      background-color: #f8f9fa;
    }

    .google-btn {
      background-color: white;
      color: #757575;
      margin-top: 50px;
    }

    .divider {
      display: flex;
      align-items: center;
      text-align: center;
      margin: 3rem 0;
    }

    .divider::before,
    .divider::after {
      content: '';
      flex: 1;
      border-bottom: 1px solid #ddd;
    }

    .divider-text {
      padding: 0 1rem;
      color: #757575;
      font-size: 16px;  
    }

    .forgot-password-link {
      color: #000000;
      text-decoration: none;
      transition: text-decoration 0.3s ease;
    }

    .forgot-password-link:hover {
      text-decoration: underline;
    }

    @media (min-width: 968px) {
      .login-row {
        display: flex;
        gap: 2rem;
      }

      .login-column {
        flex: 1;
        padding: 1rem;
      }

      .divider {
        width: 1px;
        background-color: #ddd;
        margin: 0 1rem;
        position: relative;
      }

      .divider::before,
      .divider::after {
        display: none;
      }

      .divider-text {
        position: absolute;
        top: 50%;
        left: 50%;
        transform: translate(-50%, -50%);
        background-color: #f8f9fa;
        padding: 10px 30px;
      }

      .logo-container {
        margin-right: 60px;
      }

    }
  </style>
</head>

<body class="d-flex flex-column align-items-center justify-content-center py-5">
  <div class="container">
    <div class="row justify-content-center">
      <div class="col-12 col-md-10 col-lg-8 text-center mb-5 logo-container">
        <img src="gesturo_svg.svg" alt="Gesturo logo" class="img-fluid" style="max-width: 800px; height: 90px;">
      </div>
    </div>
    <div class="row justify-content-center">
      <div class="col-12 col-md-10 col-lg-8">
        <div class="login-container">
          <h2 class="text-center mb-5">Welcome Back</h2>

          <div class="login-row">
            <div class="login-column">
              <form id="loginForm">
                <div class="mb-3">
                  <input type="email" name="email" class="form-control" placeholder="Email Address or Username" required>
                </div>
                <div class="mb-3">
                  <input type="password" name="password" class="form-control" placeholder="Password" required>
                </div>
                <div id="errorMessage" class="alert alert-danger d-none mb-3"></div>
                <button type="submit" class="btn btn-primary w-100" id="loginButton">
                  <span class="spinner-border spinner-border-sm d-none" role="status" aria-hidden="true" id="loginSpinner"></span>
                  <span id="loginButtonText">Continue</span>
                </button>
              </form>
            </div>

            <div class="divider">
              <span class="divider-text">OR</span>
            </div>

            <div class="login-column">
              <button class="social-login-btn google-btn" onclick="handleGoogleLogin()">
                <img src="https://www.google.com/favicon.ico" alt="Google" width="20" height="20">
                Continue with Google
              </button>
            </div>
          </div>
        </div>
        
        <div class="text-center mt-5 mb-2">
          <a href="forgot-password" class="forgot-password-link">Forgot Password?</a>
        </div>
        <div class="text-center mb-5">
          <a href="register" class="forgot-password-link">Create an Account</a>
        </div>
        <div class="text-center mt-5" style="color: #757575; font-size: 14px;">
          By continuing you agree to our <a href="terms.html" class="text-decoration-none" style="color: #000000;">Terms
            of Service</a> and have read our <a href="privacy.html" class="text-decoration-none"
            style="color: #000000;">Privacy Policy</a>.
        </div>
      </div>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
  <script>
    // Google OAuth configuration
    function handleGoogleLogin() {
      // Initialize Google OAuth
      const clientId = '823488326203-mf0v62tjt1eabdp1eb20p1knngbeefsv.apps.googleusercontent.com';
      const redirectUri = 'https://gesturo.lol/check.php';
      const scope = 'email profile';

      const authUrl = `https://accounts.google.com/o/oauth2/v2/auth?client_id=${clientId}&redirect_uri=${redirectUri}&response_type=code&scope=${scope}`;
      window.location.href = authUrl;
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
      loginButtonText.textContent = 'Logging in...';
      errorMessage.classList.add('d-none');

      // Send AJAX request
      fetch('login_handler.php', {
        method: 'POST',
        body: formData
      })
      .then(response => response.json())
      .then(data => {   
        if (data.success) {
          // Login successful - redirect to homepage
          window.location.href = 'https://gesturo.lol';
        } else {
          // Show error message
          errorMessage.textContent = data.message;
          errorMessage.classList.remove('d-none');
          
          // Reset button state
          loginButton.disabled = false;
          loginSpinner.classList.add('d-none');
          loginButtonText.textContent = 'Continue';
        }
      })
      .catch(error => {
        // Show error message
        errorMessage.textContent = 'An error occurred. Please try again.';
        errorMessage.classList.remove('d-none');
        
        // Reset button state
        loginButton.disabled = false;
        loginSpinner.classList.add('d-none');
        loginButtonText.textContent = 'Continue';
      });
    });
  </script>

</body>

</html>