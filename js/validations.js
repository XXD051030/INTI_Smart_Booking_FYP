const passwordInput = document.getElementById('password');
const confirmPasswordInput = document.getElementById('password_confirmation');
const content = document.getElementById('password-requirements');
const content2 = document.getElementById('password-requirements-2');
const content3 = document.getElementById('email-requirements');
const requirementList = document.querySelectorAll(".requirement-list li");
const submitButton = document.getElementById("submit_btn");
const usernameInput = document.getElementById('username');
const emailInput = document.getElementById('email');
const termsCheckbox = document.getElementById('terms');

// Debug check for elements
console.log('Elements found:', {
    passwordInput: !!passwordInput,
    confirmPasswordInput: !!confirmPasswordInput,
    content: !!content,
    content2: !!content2,
    content3: !!content3,
    requirementList: requirementList.length,
    submitButton: !!submitButton,
    usernameInput: !!usernameInput,
    emailInput: !!emailInput,
    termsCheckbox: !!termsCheckbox
});

// Show or hide password requirement list
passwordInput.addEventListener('input', function() {
    content.style.display = passwordInput.value.length > 0 ? 'block' : 'none';
    validateForm();
});

// Show or hide password confirmation check
confirmPasswordInput.addEventListener('input', function() {
    content2.style.display = confirmPasswordInput.value.length > 0 ? 'block' : 'none';
    checkPasswordMatch();
    validateForm();
});

// Show or hide email validation check
emailInput.addEventListener('input', function() {
    content3.style.display = emailInput.value.length > 0 ? 'block' : 'none';
    checkEmailFormat();
    validateForm();
});

// Password validation requirements
const requirements = [
    { req: /.{6,}/, index: 0 },  // Minimum of 6 characters
    { req: /[0-9]/, index: 1 },  // At least one number
//    { req: /[a-z]/, index: 2 },  // At least one lowercase letter
//    { req: /[^A-Za-z0-9]/, index: 3 },  // At least one special character
//    { req: /[A-Z]/, index: 4 },  // At least one uppercase letter
];

passwordInput.addEventListener('keyup', (e) => {
    requirements.forEach(item => {
        const isValid = item.req.test(e.target.value);
        const requirementItem = requirementList[item.index];
        requirementItem.firstElementChild.className = isValid ? "fa-solid fa-check" : "fa-solid fa-circle";
        requirementItem.firstElementChild.style.color = isValid ? "#007bff" : "#777";
    });
    validateForm();
});

// Check if passwords match
function checkPasswordMatch() {
    const errorMessageElement = document.getElementById("error");
    const iconElement = document.querySelector('.requirement-list-2 i.fa-solid');

    if ((passwordInput.value === confirmPasswordInput.value) && passwordInput.value !== "") {
        errorMessageElement.innerHTML = "Passwords match.";
        iconElement.className = 'fa-solid fa-check';
        iconElement.style.color = "#007bff";
    } else {
        errorMessageElement.innerHTML = "Passwords do not match.";
        iconElement.className = 'fa-solid fa-circle';
        iconElement.style.color = "#777";
    }
    validateForm();
}

// Check if email format is valid
function checkEmailFormat() {
    const emailErrorElement = document.getElementById("email-error");
    const iconElement = document.querySelector('.requirement-list-3 i.fa-solid');

    if (isValidEmail(emailInput.value) && emailInput.value !== "") {
        emailErrorElement.innerHTML = "Valid INTI student email.";
        iconElement.className = 'fa-solid fa-check';
        iconElement.style.color = "#007bff";
    } else {
        emailErrorElement.innerHTML = "Please use your INTI student email (@student.newinti.edu.my)";
        iconElement.className = 'fa-solid fa-circle';
        iconElement.style.color = "#777";
    }
    validateForm();
}

// Email validation - Only accept INTI student emails
function isValidEmail(email) {
    const emailRegex = /^[^\s@]+@student\.newinti\.edu\.my$/;
    return emailRegex.test(email);
}

// Username validation
function isValidUsername(username) {
    return username.length >= 3;
}

// Enable or disable the submit button based on form validation
function validateForm() {
    const isPasswordValid = requirements.every(item => item.req.test(passwordInput.value));
    const isPasswordMatching = passwordInput.value === confirmPasswordInput.value && passwordInput.value !== "";
    const isEmailValid = isValidEmail(emailInput.value);
    const isUsernameValid = isValidUsername(usernameInput.value);
    const isTermsAccepted = termsCheckbox.checked;

    submitButton.disabled = !(isPasswordValid && isPasswordMatching && isEmailValid && isUsernameValid && isTermsAccepted);
}

// Add event listeners for username and email validation
usernameInput.addEventListener('input', validateForm);
emailInput.addEventListener('input', validateForm);
termsCheckbox.addEventListener('change', validateForm);

// Initial validation
validateForm();