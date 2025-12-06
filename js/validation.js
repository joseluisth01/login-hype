document.addEventListener('DOMContentLoaded', function() {
    const signupForm = document.getElementById('signupForm');
    
    if (signupForm) {
        const passwordField = document.getElementById('password');
        const passwordConfirmField = document.getElementById('password_confirm');
        const usernameField = document.getElementById('username');
        const emailField = document.getElementById('email');
        
        signupForm.addEventListener('submit', function(e) {
            let isValid = true;
            let errorMessage = '';
            
            const username = usernameField.value.trim();
            if (username.length < 3 || username.length > 50) {
                isValid = false;
                errorMessage += 'Username must be between 3 and 50 characters.\n';
            }
            
            if (!/^[a-zA-Z0-9_]+$/.test(username)) {
                isValid = false;
                errorMessage += 'Username can only contain letters, numbers, and underscores.\n';
            }
            
            const email = emailField.value.trim();
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                isValid = false;
                errorMessage += 'Please enter a valid email address.\n';
            }
            
            const password = passwordField.value;
            if (password.length < 8) {
                isValid = false;
                errorMessage += 'Password must be at least 8 characters long.\n';
            }
            
            if (!/[A-Z]/.test(password)) {
                isValid = false;
                errorMessage += 'Password must contain at least one uppercase letter.\n';
            }
            
            if (!/[a-z]/.test(password)) {
                isValid = false;
                errorMessage += 'Password must contain at least one lowercase letter.\n';
            }
            
            if (!/[0-9]/.test(password)) {
                isValid = false;
                errorMessage += 'Password must contain at least one number.\n';
            }
            
            const passwordConfirm = passwordConfirmField.value;
            if (password !== passwordConfirm) {
                isValid = false;
                errorMessage += 'Passwords do not match.\n';
            }
            
            if (!isValid) {
                e.preventDefault();
                alert(errorMessage);
            }
        });
        
        passwordField.addEventListener('input', function() {
            const password = this.value;
            const strength = calculatePasswordStrength(password);
            updatePasswordStrength(strength);
        });
        
        passwordConfirmField.addEventListener('input', function() {
            const password = passwordField.value;
            const passwordConfirm = this.value;
            
            if (passwordConfirm && password !== passwordConfirm) {
                this.style.borderColor = '#ff3333';
            } else {
                this.style.borderColor = '';
            }
        });
    }
    
    function calculatePasswordStrength(password) {
        let strength = 0;
        
        if (password.length >= 8) strength++;
        if (password.length >= 12) strength++;
        if (/[a-z]/.test(password)) strength++;
        if (/[A-Z]/.test(password)) strength++;
        if (/[0-9]/.test(password)) strength++;
        if (/[^a-zA-Z0-9]/.test(password)) strength++;
        
        return strength;
    }
    
    function updatePasswordStrength(strength) {
        const passwordField = document.getElementById('password');
        
        if (strength <= 2) {
            passwordField.style.borderColor = '#ff3333';
        } else if (strength <= 4) {
            passwordField.style.borderColor = '#ffaa00';
        } else {
            passwordField.style.borderColor = '#39ff14';
        }
    }
});