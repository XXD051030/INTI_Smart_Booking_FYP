<?php

  session_start();

  include("db.php");
  require_once("function.php");
  require_once("password-validation.php");
  require_once("notification_functions.php");

  // Set header to return JSON response
  header('Content-Type: application/json');

  // Initialize response array
  $response = array(
    'success' => false,
    'message' => ''
  );

  // Debug database connection
  try {
    if (!isset($pdo)) {
      throw new PDOException("Database connection not established");
    }
  } catch (PDOException $e) {
    $response['message'] = "Database connection error: " . $e->getMessage();
    echo json_encode($response);
    exit;
  }

  // Initialize a variable to check if the form is valid
  $isValid = true;
  $username = $email = $password = $confirmPassword = "";

  $username = $_POST['username'] ?? '';
  $email = $_POST['email'] ?? '';
  $password = $_POST['password'] ?? '';
  $confirmPassword = $_POST['password_confirmation'] ?? '';


  // Check if the username is empty
  if(empty($username)){
    $isValid = false;
    $response['message'] = "Username is required";
  }

  //Check if the email is empty
  if (empty($email)) {
    $isValid = false;
    $response['message'] = "Email is required";
  }

  // validate the password again
  $isValid = passwordValidate($password, $confirmPassword);
  if (!$isValid) {
    $response['message'] = "Password validation failed";
  }

  // If any validation fails, return error response
  if(!$isValid){
    echo json_encode($response);
    exit;
  }

  try {
    // Prepare an SQL query to check if the email already exists in the database
    $stmt = $pdo->prepare('SELECT * FROM users WHERE email = ?');
    $stmt->execute([$email]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);
    $password_hash = password_hash($password, PASSWORD_DEFAULT);

    // If the email already exists
    if ($user) {
        // If the email is already verified
        if ($user['is_verified'] == 1) {
          $response['message'] = "Your email is already registered in our system. Kindly log in to proceed.";
          echo json_encode($response);
          exit;
        } else {
          // If the user is registered but has not verified their account, update the user details
          $update_stmt = $pdo->prepare("UPDATE users SET username = ?, password = ? WHERE email = ?");
          
          if ($update_stmt->execute([$username, $password_hash, $email])) {
            $_SESSION['email_reg'] = $email;
            $response['success'] = true;
            $response['message'] = "Registration successful. You can now log in.";
            echo json_encode($response);
            exit;
          }
        }
    } else {
      // new account register
      $stmt = $pdo->prepare("INSERT INTO users (username, email, password, created_at, is_verified) VALUES (?, ?, ?, NOW(), 0)");
      
      if ($stmt->execute([$username, $email, $password_hash])) {
        $_SESSION['email_reg'] = $email;
        $response['success'] = true;
        $response['message'] = "Registration successful. You can now log in.";
        echo json_encode($response);
        exit;
      }
    }
  } catch (PDOException $e) {
    // Log the error
    error_log("Registration Error: " . $e->getMessage() . "\n", 3, "var/log/register_error.log");
    $response['message'] = "Database error: " . $e->getMessage();
    echo json_encode($response);
    exit;
  }