/**
 * Countdown timer for OTP resend functionality
 */

function startCountdown(initialText, resendText, targetPage) {
    const button = document.getElementById('send-email');
    let countdown = 60; // 60 seconds countdown
    
    // Disable button and start countdown
    button.disabled = true;
    button.textContent = `${initialText} (${countdown}s)`;
    
    // Send OTP request
    sendOTP(targetPage);
    
    const timer = setInterval(() => {
        countdown--;
        button.textContent = `${resendText} (${countdown}s)`;
        
        if (countdown <= 0) {
            clearInterval(timer);
            button.disabled = false;
            button.textContent = resendText;
        }
    }, 1000);
}

function sendOTP(targetPage) {
    // Create form data
    const formData = new FormData();
    formData.append('action', 'sended');
    
    // Send AJAX request
    fetch(targetPage, {
        method: 'POST',
        body: formData
    })
    .then(response => {
        if (!response.ok) {
            throw new Error('Network response was not ok');
        }
        return response.text();
    })
    .then(data => {
        console.log('OTP sent successfully');
        // You can add success notification here if needed
    })
    .catch(error => {
        console.error('Error sending OTP:', error);
        // Re-enable button on error
        const button = document.getElementById('send-email');
        button.disabled = false;
        button.textContent = 'Send OTP';
        
        // Show error message
        showMessage('Failed to send OTP. Please try again.', 'error');
    });
}

function showMessage(message, type) {
    // Create or update message element
    let messageElement = document.getElementById('otp-message');
    if (!messageElement) {
        messageElement = document.createElement('div');
        messageElement.id = 'otp-message';
        messageElement.style.cssText = `
            margin: 1rem auto;
            padding: 10px;
            border-radius: 5px;
            text-align: center;
            max-width: 300px;
        `;
        
        // Insert after OTP inputs
        const otpInputs = document.querySelector('.otp-inputs');
        otpInputs.parentNode.insertBefore(messageElement, otpInputs.nextSibling);
    }
    
    // Set message and style based on type
    messageElement.textContent = message;
    if (type === 'error') {
        messageElement.style.backgroundColor = '#f8d7da';
        messageElement.style.color = '#721c24';
        messageElement.style.border = '1px solid #f5c6cb';
    } else {
        messageElement.style.backgroundColor = '#d4edda';
        messageElement.style.color = '#155724';
        messageElement.style.border = '1px solid #c3e6cb';
    }
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        messageElement.style.display = 'none';
    }, 5000);
} 