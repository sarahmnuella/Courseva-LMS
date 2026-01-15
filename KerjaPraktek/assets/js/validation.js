// Validation JavaScript untuk Courseva

// Register form validation
document.addEventListener('DOMContentLoaded', function() {
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const password = document.getElementById('password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            if (password !== confirmPassword) {
                e.preventDefault();
                alert('Konfirmasi password tidak sesuai!');
                return false;
            }
            
            if (password.length < 8) {
                e.preventDefault();
                alert('Password minimal 8 karakter!');
                return false;
            }
            
            const username = document.getElementById('username').value;
            if (username.length < 3) {
                e.preventDefault();
                alert('Username minimal 3 karakter!');
                return false;
            }
        });
    }
});

// Email validation
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Phone number validation (Indonesia)
function validatePhone(phone) {
    const re = /^(\+62|62|0)[0-9]{9,12}$/;
    return re.test(phone.replace(/\s/g, ''));
}

// Real-time validation feedback
document.addEventListener('DOMContentLoaded', function() {
    const emailInputs = document.querySelectorAll('input[type="email"]');
    emailInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            if (input.value && !validateEmail(input.value)) {
                input.classList.add('is-invalid');
                if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('invalid-feedback')) {
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    feedback.textContent = 'Format email tidak valid';
                    input.parentNode.insertBefore(feedback, input.nextSibling);
                }
            } else {
                input.classList.remove('is-invalid');
                const feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.remove();
                }
            }
        });
    });
    
    const phoneInputs = document.querySelectorAll('input[name*="nomor_hp"], input[name*="phone"]');
    phoneInputs.forEach(function(input) {
        input.addEventListener('blur', function() {
            if (input.value && !validatePhone(input.value)) {
                input.classList.add('is-invalid');
                if (!input.nextElementSibling || !input.nextElementSibling.classList.contains('invalid-feedback')) {
                    const feedback = document.createElement('div');
                    feedback.className = 'invalid-feedback';
                    feedback.textContent = 'Format nomor HP tidak valid';
                    input.parentNode.insertBefore(feedback, input.nextSibling);
                }
            } else {
                input.classList.remove('is-invalid');
                const feedback = input.nextElementSibling;
                if (feedback && feedback.classList.contains('invalid-feedback')) {
                    feedback.remove();
                }
            }
        });
    });
});

// File upload validation
document.addEventListener('DOMContentLoaded', function() {
    const fileInputs = document.querySelectorAll('input[type="file"]');
    fileInputs.forEach(function(input) {
        input.addEventListener('change', function() {
            const file = input.files[0];
            if (file) {
                // Check file size (default 5MB)
                const maxSize = input.getAttribute('data-max-size') || 5242880;
                if (file.size > maxSize) {
                    alert('Ukuran file melebihi batas maksimal!');
                    input.value = '';
                    return;
                }
                
                // Check file type
                const allowedTypes = input.getAttribute('accept');
                if (allowedTypes) {
                    const fileExtension = '.' + file.name.split('.').pop().toLowerCase();
                    const allowedExtensions = allowedTypes.split(',').map(type => type.trim());
                    let isValid = false;
                    
                    allowedExtensions.forEach(function(type) {
                        if (type.startsWith('.')) {
                            if (fileExtension === type) isValid = true;
                        } else if (type.includes('/')) {
                            if (file.type.match(type.replace('*', ''))) isValid = true;
                        }
                    });
                    
                    if (!isValid) {
                        alert('Tipe file tidak diizinkan!');
                        input.value = '';
                        return;
                    }
                }
            }
        });
    });
});

// Password strength indicator
document.addEventListener('DOMContentLoaded', function() {
    const passwordInputs = document.querySelectorAll('input[type="password"][name="password"]');
    passwordInputs.forEach(function(input) {
        input.addEventListener('input', function() {
            const password = input.value;
            const strength = checkPasswordStrength(password);
            
            // Remove existing indicators
            const existingIndicator = input.parentNode.querySelector('.password-strength');
            if (existingIndicator) {
                existingIndicator.remove();
            }
            
            if (password.length > 0) {
                const indicator = document.createElement('div');
                indicator.className = 'password-strength mt-1';
                
                let strengthText = '';
                let strengthClass = '';
                
                if (strength < 2) {
                    strengthText = 'Lemah';
                    strengthClass = 'text-danger';
                } else if (strength < 4) {
                    strengthText = 'Sedang';
                    strengthClass = 'text-warning';
                } else {
                    strengthText = 'Kuat';
                    strengthClass = 'text-success';
                }
                
                indicator.innerHTML = `<small class="${strengthClass}">Kekuatan password: ${strengthText}</small>`;
                input.parentNode.appendChild(indicator);
            }
        });
    });
});

// Helper function untuk check password strength
function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;
    return strength;
}

