/**
 * Admin Login Page JavaScript
 * Handles all interactive functionality for the admin login form
 */

// Wait for DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Initialize admin login functionality
    initAdminLogin();
});

function initAdminLogin() {
    // Toggle password visibility
    const togglePassword = document.getElementById('togglePassword');
    if (togglePassword) {
        togglePassword.addEventListener('click', function() {
            togglePasswordVisibility();
        });
    }

    // Form validation and enhancement
    const adminLoginForm = document.getElementById('adminLoginForm');
    if (adminLoginForm) {
        adminLoginForm.addEventListener('submit', handleFormSubmission);
    }

    // Enhanced input focus effects
    initInputFocusEffects();

    // Remember username from previous sessions
    loadSavedUsername();

    // Auto-hide alerts
    initAutoHideAlerts();
}

/**
 * Toggle password visibility between text and password
 */
function togglePasswordVisibility() {
    const password = document.getElementById('password');
    const icon = document.querySelector('#togglePassword i');
    
    if (password && icon) {
        if (password.type === 'password') {
            password.type = 'text';
            icon.classList.remove('bi-eye');
            icon.classList.add('bi-eye-slash');
        } else {
            password.type = 'password';
            icon.classList.remove('bi-eye-slash');
            icon.classList.add('bi-eye');
        }
    }
}

/**
 * Handle form submission with validation and loading states
 */
function handleFormSubmission(e) {
    const username = document.getElementById('username')?.value.trim();
    const password = document.getElementById('password')?.value;
    
    if (!username || !password) {
        e.preventDefault();
        showAlert('Vui lòng điền đầy đủ thông tin đăng nhập', 'error');
        return false;
    }
    
    // Show loading state
    showLoadingState();
    
    // Save username for future sessions
    saveUsername(username);
    
    // Re-enable after 3 seconds if form doesn't submit
    setTimeout(() => {
        hideLoadingState();
    }, 3000);
}

/**
 * Show loading state on submit button
 */
function showLoadingState() {
    const submitBtn = document.querySelector('.admin-btn-login');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang xử lý...';
        submitBtn.disabled = true;
    }
}

/**
 * Hide loading state on submit button
 */
function hideLoadingState() {
    const submitBtn = document.querySelector('.admin-btn-login');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="bi bi-box-arrow-in-right"></i> Đăng nhập Admin';
        submitBtn.disabled = false;
    }
}

/**
 * Initialize input focus effects
 */
function initInputFocusEffects() {
    const formControls = document.querySelectorAll('.admin-form-control');
    
    formControls.forEach(input => {
        input.addEventListener('focus', function() {
            this.parentElement.classList.add('focused');
        });
        
        input.addEventListener('blur', function() {
            if (!this.value) {
                this.parentElement.classList.remove('focused');
            }
        });
    });
}

/**
 * Load saved username from localStorage
 */
function loadSavedUsername() {
    const savedUsername = localStorage.getItem('admin_username');
    const usernameField = document.getElementById('username');
    
    if (savedUsername && usernameField && !usernameField.value) {
        usernameField.value = savedUsername;
    }
}

/**
 * Save username to localStorage
 */
function saveUsername(username) {
    if (username) {
        localStorage.setItem('admin_username', username);
    }
}

/**
 * Initialize auto-hide alerts
 */
function initAutoHideAlerts() {
    setTimeout(function() {
        const alerts = document.querySelectorAll('.admin-alert');
        alerts.forEach(function(alert) {
            fadeOutAlert(alert);
        });
    }, 8000);
}

/**
 * Fade out alert with animation
 */
function fadeOutAlert(alert) {
    alert.style.opacity = '0';
    alert.style.transform = 'translateY(-10px)';
    setTimeout(() => {
        if (alert.parentNode) {
            alert.remove();
        }
    }, 300);
}

/**
 * Show custom alert message
 */
function showAlert(message, type = 'info') {
    // Create alert element
    const alertDiv = document.createElement('div');
    alertDiv.className = `admin-alert alert-${type}`;
    alertDiv.innerHTML = `
        <i class="bi bi-${type === 'error' ? 'exclamation-triangle-fill' : 'info-circle-fill'}"></i> 
        ${message}
    `;
    
    // Insert before the form
    const form = document.getElementById('adminLoginForm');
    if (form && form.parentNode) {
        form.parentNode.insertBefore(alertDiv, form);
        
        // Auto-hide after 5 seconds
        setTimeout(() => {
            fadeOutAlert(alertDiv);
        }, 5000);
    }
}

/**
 * Clear all saved admin data
 */
function clearAdminData() {
    localStorage.removeItem('admin_username');
    // Clear any other admin-related data
}

/**
 * Handle keyboard shortcuts
 */
document.addEventListener('keydown', function(e) {
    // Ctrl/Cmd + Enter to submit form
    if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
        const form = document.getElementById('adminLoginForm');
        if (form) {
            form.submit();
        }
    }
    
    // Escape key to clear form
    if (e.key === 'Escape') {
        const form = document.getElementById('adminLoginForm');
        if (form) {
            form.reset();
            clearAdminData();
        }
    }
});

/**
 * Add form input enhancements
 */
function enhanceFormInputs() {
    const inputs = document.querySelectorAll('.admin-form-control');
    
    inputs.forEach(input => {
        // Add enter key navigation
        input.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                const nextInput = this.parentElement.nextElementSibling?.querySelector('.admin-form-control');
                if (nextInput) {
                    nextInput.focus();
                } else {
                    // Submit form if it's the last input
                    const form = document.getElementById('adminLoginForm');
                    if (form) {
                        form.submit();
                    }
                }
            }
        });
        
        // Add input validation feedback
        input.addEventListener('input', function() {
            validateInput(this);
        });
    });
}

/**
 * Validate individual input field
 */
function validateInput(input) {
    const value = input.value.trim();
    const fieldName = input.name;
    
    // Remove existing validation classes
    input.classList.remove('is-valid', 'is-invalid');
    
    if (fieldName === 'username') {
        if (value.length < 3) {
            input.classList.add('is-invalid');
        } else {
            input.classList.add('is-valid');
        }
    } else if (fieldName === 'password') {
        if (value.length < 6) {
            input.classList.add('is-invalid');
        } else {
            input.classList.add('is-valid');
        }
    }
}

// Initialize form enhancements when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    enhanceFormInputs();
});

// Export functions for potential external use
if (typeof module !== 'undefined' && module.exports) {
    module.exports = {
        initAdminLogin,
        togglePasswordVisibility,
        handleFormSubmission,
        showAlert,
        clearAdminData
    };
} 