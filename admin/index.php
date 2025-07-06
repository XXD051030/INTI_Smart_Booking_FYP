<?php
session_start();

// Check if admin is already logged in
if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

// Check for messages
$message = $_GET['message'] ?? '';

// Handle login form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Simple hardcoded admin credentials
    if ($username === 'admin' && $password === 'admin123') {
        $_SESSION['admin_logged_in'] = true;
        $_SESSION['admin_username'] = 'admin';
        header('Location: dashboard.php');
        exit;
    } else {
        $error = 'Invalid username or password';
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - Reservation System</title>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@300;400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
        body {
            background: rgb(246, 31, 31);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Roboto', sans-serif;
        }
        
        .admin-login-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
            padding: 3rem;
            width: 100%;
            max-width: 450px;
        }
        
        .admin-header {
            text-align: center;
            margin-bottom: 2rem;
        }
        
        .admin-logo {
            height: 60px;
            margin-bottom: 1rem;
        }
        
        .admin-title {
            color: #333;
            font-weight: 700;
            margin-bottom: 0.5rem;
        }
        
        .admin-subtitle {
            color: #666;
            font-size: 0.9rem;
        }
        
        .form-group {
            margin-bottom: 1.5rem;
        }
        
        .form-label {
            font-weight: 500;
            color: #333;
            margin-bottom: 0.5rem;
        }
        
        .form-control {
            border: 2px solid #e1e5e9;
            border-radius: 10px;
            padding: 0.75rem 1rem;
            font-size: 1rem;
            transition: border-color 0.3s ease;
        }
        
        .form-control:focus {
            border-color: rgb(246, 31, 31);
            box-shadow: 0 0 0 0.2rem rgba(246, 31, 31, 0.25);
        }
        
        .btn-admin {
            background: linear-gradient(135deg, rgb(246, 31, 31) 0%, rgb(212, 26, 26) 100%);
            border: none;
            border-radius: 10px;
            padding: 0.75rem 2rem;
            color: white;
            font-weight: 500;
            width: 100%;
            transition: transform 0.3s ease;
        }
        
        .btn-admin:hover {
            transform: translateY(-2px);
            color: white;
        }
        
        .alert {
            border-radius: 10px;
            margin-bottom: 1rem;
        }
        
        .back-link {
            text-align: center;
            margin-top: 1.5rem;
        }
        
        .back-link a {
            color: rgb(246, 31, 31);
            text-decoration: none;
            font-weight: 500;
        }
        
        .back-link a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="admin-login-card">
        <div class="admin-header">
            <img src="../images/logo/inti_logo.png" alt="INTI Logo" class="admin-logo">
            <h2 class="admin-title">Admin Panel</h2>
            <p class="admin-subtitle">Reservation System Administration</p>
        </div>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger" role="alert">
                <i class="fas fa-exclamation-circle me-2"></i><?php echo htmlspecialchars($error); ?>
            </div>
        <?php endif; ?>
        
        <?php if (!empty($message)): ?>
            <div class="alert alert-warning" role="alert">
                <i class="fas fa-info-circle me-2"></i><?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>
        
        <form method="POST">
            <div class="form-group">
                <label for="username" class="form-label">
                    <i class="fas fa-user me-2"></i>Username
                </label>
                <input type="text" class="form-control" id="username" name="username" required>
            </div>
            
            <div class="form-group">
                <label for="password" class="form-label">
                    <i class="fas fa-lock me-2"></i>Password
                </label>
                <input type="password" class="form-control" id="password" name="password" required>
            </div>
            
            <button type="submit" class="btn btn-admin">
                <i class="fas fa-sign-in-alt me-2"></i>Login to Admin Panel
            </button>
        </form>
        
        <div class="back-link">
            <a href="../login.php">
                <i class="fas fa-arrow-left me-2"></i>Back to User Login
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html> 