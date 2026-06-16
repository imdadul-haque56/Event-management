// Bootstrap 5 client-side form validation initialization
document.addEventListener('DOMContentLoaded', () => {
    'use strict';

    // Fetch all forms we want to apply custom Bootstrap validation styles to
    const forms = document.querySelectorAll('.needs-validation');

    // Loop over them and prevent submission if invalid
    Array.from(forms).forEach(form => {
        form.addEventListener('submit', event => {
            // If the form has a password match check, perform it
            const password = form.querySelector('input[name="password"]');
            const confirmPassword = form.querySelector('input[name="confirm_password"]');

            if (password && confirmPassword) {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Passwords do not match.");
                    // Show custom matching error
                    let feedback = confirmPassword.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.textContent = "Passwords do not match!";
                    }
                } else {
                    confirmPassword.setCustomValidity("");
                }
            }

            if (!form.checkValidity()) {
                event.preventDefault();
                event.stopPropagation();
            }

            form.classList.add('was-validated');
        }, false);
    });

    // Real-time password confirmation validation as the user types
    const registerForm = document.querySelector('form[action*="register.php"]');
    if (registerForm) {
        const password = registerForm.querySelector('input[name="password"]');
        const confirmPassword = registerForm.querySelector('input[name="confirm_password"]');
        
        if (password && confirmPassword) {
            const checkPasswordMatch = () => {
                if (password.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity("Passwords do not match.");
                    let feedback = confirmPassword.nextElementSibling;
                    if (feedback && feedback.classList.contains('invalid-feedback')) {
                        feedback.textContent = "Passwords do not match!";
                    }
                } else {
                    confirmPassword.setCustomValidity("");
                }
            };
            password.addEventListener('input', checkPasswordMatch);
            confirmPassword.addEventListener('input', checkPasswordMatch);
        }
    }
});
