// Main JavaScript untuk Courseva

// Auto-hide alerts setelah 5 detik
document.addEventListener('DOMContentLoaded', function() {
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        setTimeout(function() {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});

// Form validation
function validateForm(formId) {
    const form = document.getElementById(formId);
    if (!form) return false;
    
    const inputs = form.querySelectorAll('input[required], textarea[required], select[required]');
    let isValid = true;
    
    inputs.forEach(function(input) {
        if (!input.value.trim()) {
            input.classList.add('is-invalid');
            isValid = false;
        } else {
            input.classList.remove('is-invalid');
        }
    });
    
    return isValid;
}

// Confirm delete
function confirmDelete(message) {
    return confirm(message || 'Yakin ingin menghapus?');
}

// Format rupiah input
function formatRupiahInput(input) {
    input.addEventListener('input', function(e) {
        let value = e.target.value.replace(/\D/g, '');
        if (value) {
            value = parseInt(value).toLocaleString('id-ID');
            e.target.value = value;
        }
    });
}

// Initialize rupiah formatters
document.addEventListener('DOMContentLoaded', function() {
    const rupiahInputs = document.querySelectorAll('input[type="number"][name*="harga"], input[type="number"][name*="amount"]');
    rupiahInputs.forEach(function(input) {
        formatRupiahInput(input);
    });
});

// Image preview untuk file upload
function previewImage(input, previewId) {
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            document.getElementById(previewId).src = e.target.result;
            document.getElementById(previewId).style.display = 'block';
        };
        reader.readAsDataURL(input.files[0]);
    }
}

// File size validation
function validateFileSize(input, maxSizeMB) {
    if (input.files && input.files[0]) {
        const fileSize = input.files[0].size / 1024 / 1024; // Convert to MB
        if (fileSize > maxSizeMB) {
            alert(`Ukuran file melebihi ${maxSizeMB}MB`);
            input.value = '';
            return false;
        }
    }
    return true;
}

// Password strength checker
function checkPasswordStrength(password) {
    let strength = 0;
    if (password.length >= 8) strength++;
    if (password.match(/[a-z]+/)) strength++;
    if (password.match(/[A-Z]+/)) strength++;
    if (password.match(/[0-9]+/)) strength++;
    if (password.match(/[$@#&!]+/)) strength++;
    
    return strength;
}

// Smooth scroll
function smoothScrollTo(elementId) {
    const element = document.getElementById(elementId);
    if (element) {
        element.scrollIntoView({ behavior: 'smooth' });
    }
}

// Copy to clipboard
function copyToClipboard(text) {
    navigator.clipboard.writeText(text).then(function() {
        alert('Berhasil disalin!');
    });
}

// Debounce function
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Search dengan debounce
const searchInputs = document.querySelectorAll('input[type="search"], input[name*="search"]');
searchInputs.forEach(function(input) {
    input.addEventListener('input', debounce(function() {
        // Trigger search jika ada form parent
        const form = input.closest('form');
        if (form) {
            form.submit();
        }
    }, 500));
});

