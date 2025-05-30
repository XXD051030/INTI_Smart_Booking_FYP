<?php
/**
 * Password validation functions
 */

/**
 * Validate password according to security requirements
 * @param string $password The password to validate
 * @param string $confirmPassword The password confirmation
 * @return bool True if password is valid, false otherwise
 */
function passwordValidate($password, $confirmPassword) {
    // Check if passwords match
    if ($password !== $confirmPassword) {
        return false;
    }
    
    // Check minimum length (8 characters)
    if (strlen($password) < 6) {
        return false;
    }
    
    // // Check for at least one number
    if (!preg_match('/[0-9]/', $password)) {
        return false;
    }
    
    // // Check for at least one lowercase letter
    // if (!preg_match('/[a-z]/', $password)) {
    //     return false;
    // }
    
    // // Check for at least one uppercase letter
    // if (!preg_match('/[A-Z]/', $password)) {
    //     return false;
    // }
    
    // // Check for at least one special character
    // if (!preg_match('/[!@#$%^&*(),.?":{}|<>]/', $password)) {
    //     return false;
    // }
    
    return true;
}

/**
 * Get password validation requirements as array
 * @return array List of password requirements
 */
function getPasswordRequirements() {
    return [
        'At least 6 characters length',
        'At least 1 number (0...9)',
        //'At least 1 lowercase letter (a...z)',
        //'At least 1 uppercase letter (A...Z)',
        //'At least 1 special symbol (!@#$%^&*(),.?":{}|<>)'
    ];
}
?> 