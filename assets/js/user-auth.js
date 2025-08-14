/**
 * User Authentication JavaScript
 * Handles login, registration, and form validation
 */

// User Auth namespace
window.UserAuth = window.UserAuth || {};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    UserAuth.init();
});

UserAuth = {
    /**
     * Initialize user authentication functionality
     */
    init: function() {
        this.initPasswordToggles();
        this.initFormValidation();
        this.initAutoHideAlerts();
        this.initRememberMe();
        this.initKeyboardShortcuts();
        this.initPasswordStrength();
    },

    /**
     * Initialize password visibility toggles
     */
    initPasswordToggles: function() {
        const toggles = document.querySelectorAll('.user-auth__password-toggle');
        
        toggles.forEach(toggle => {
            toggle.addEventListener('click', function() {
                const input = this.parentElement.querySelector('.user-auth__input');
                const icon = this.querySelector('i');
                
                if (input.type === 'password') {
                    input.type = 'text';
                    icon.classList.remove('bi-eye');
                    icon.classList.add('bi-eye-slash');
                } else {
                    input.type = 'password';
                    icon.classList.remove('bi-eye-slash');
                    icon.classList.add('bi-eye');
                }
            });
        });
    },

    /**
     * Initialize form validation
     */
    initFormValidation: function() {
        const forms = document.querySelectorAll('.user-auth__form');
        
        forms.forEach(form => {
            form.addEventListener('submit', this.handleFormSubmit.bind(this));
            this.addInputValidation(form);
        });
    },

    /**
     * Handle form submission
     */
    handleFormSubmit: function(e) {
        const form = e.target;
        const inputs = form.querySelectorAll('.user-auth__input[required]');
        let isValid = true;

        // Clear previous validation states
        inputs.forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });

        // Validate required fields
        inputs.forEach(input => {
            if (!input.value.trim()) {
                input.classList.add('is-invalid');
                isValid = false;
            } else {
                input.classList.add('is-valid');
            }
        });

        // Special validation for registration form
        if (form.id === 'userRegisterForm') {
            isValid = this.validateRegistrationForm(form) && isValid;
        }

        if (!isValid) {
            e.preventDefault();
            this.showAlert('Vui lòng kiểm tra lại thông tin nhập vào', 'error');
            return false;
        }

        // Show loading state
        this.showLoadingState(form);
    },

    /**
     * Validate registration form specifically
     */
    validateRegistrationForm: function(form) {
        let isValid = true;
        
        // Email validation
        const email = form.querySelector('#email');
        if (email && email.value) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                email.classList.remove('is-valid');
                email.classList.add('is-invalid');
                isValid = false;
            }
        }

        // Password validation
        const password = form.querySelector('#password');
        const confirmPassword = form.querySelector('#confirm_password');
        
        if (password && password.value.length < 6) {
            password.classList.remove('is-valid');
            password.classList.add('is-invalid');
            isValid = false;
        }

        if (confirmPassword && password && confirmPassword.value !== password.value) {
            confirmPassword.classList.remove('is-valid');
            confirmPassword.classList.add('is-invalid');
            isValid = false;
        }

        // Terms agreement validation
        const agreeTerms = form.querySelector('#agree_terms');
        if (agreeTerms && !agreeTerms.checked) {
            this.showAlert('Vui lòng đồng ý với điều khoản sử dụng', 'error');
            isValid = false;
        }

        return isValid;
    },

    /**
     * Add input validation to form
     */
    addInputValidation: function(form) {
        const inputs = form.querySelectorAll('.user-auth__input');
        
        inputs.forEach(input => {
            input.addEventListener('blur', () => this.validateInput(input));
            input.addEventListener('input', () => this.clearValidation(input));
        });
    },

    /**
     * Validate individual input
     */
    validateInput: function(input) {
        const value = input.value.trim();
        const fieldName = input.name;
        
        // Remove existing validation classes
        input.classList.remove('is-valid', 'is-invalid');
        
        if (input.hasAttribute('required') && !value) {
            input.classList.add('is-invalid');
            return false;
        }

        // Field-specific validation
        switch (fieldName) {
            case 'email':
                if (value && !this.isValidEmail(value)) {
                    input.classList.add('is-invalid');
                    return false;
                }
                break;
            case 'password':
                if (value && value.length < 6) {
                    input.classList.add('is-invalid');
                    return false;
                }
                break;
            case 'confirm_password':
                const password = document.querySelector('#password');
                if (value && password && value !== password.value) {
                    input.classList.add('is-invalid');
                    return false;
                }
                break;
        }

        if (value) {
            input.classList.add('is-valid');
        }
        
        return true;
    },

    /**
     * Clear validation state
     */
    clearValidation: function(input) {
        input.classList.remove('is-valid', 'is-invalid');
    },

    /**
     * Validate email format
     */
    isValidEmail: function(email) {
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        return emailRegex.test(email);
    },

    /**
     * Show loading state on form
     */
    showLoadingState: function(form) {
        const submitBtn = form.querySelector('.user-auth__button--primary');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang xử lý...';
            submitBtn.disabled = true;
            submitBtn.classList.add('user-auth__button--loading');
            
            // Re-enable after 5 seconds if form doesn't submit
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
                submitBtn.classList.remove('user-auth__button--loading');
            }, 5000);
        }
    },

    /**
     * Initialize auto-hide alerts
     */
    initAutoHideAlerts: function() {
        setTimeout(() => {
            const alerts = document.querySelectorAll('.user-auth__alert');
            alerts.forEach(alert => {
                this.fadeOutAlert(alert);
            });
        }, 8000);
    },

    /**
     * Fade out alert with animation
     */
    fadeOutAlert: function(alert) {
        alert.style.opacity = '0';
        alert.style.transform = 'translateY(-10px)';
        setTimeout(() => {
            if (alert.parentNode) {
                alert.remove();
            }
        }, 300);
    },

    /**
     * Show custom alert message
     */
    showAlert: function(message, type = 'info') {
        const alertDiv = document.createElement('div');
        alertDiv.className = `user-auth__alert user-auth__alert--${type}`;
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'error' ? 'exclamation-triangle-fill' : 'info-circle-fill'}"></i> 
            ${message}
        `;
        
        const form = document.querySelector('.user-auth__form');
        if (form && form.parentNode) {
            form.parentNode.insertBefore(alertDiv, form);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                this.fadeOutAlert(alertDiv);
            }, 5000);
        }
    },

    /**
     * Initialize remember me functionality
     */
    initRememberMe: function() {
        const savedUsername = localStorage.getItem('user_username');
        const usernameField = document.getElementById('username');
        
        if (savedUsername && usernameField && !usernameField.value) {
            usernameField.value = savedUsername;
        }

        // Save username on successful login
        const loginForm = document.getElementById('userLoginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', () => {
                const username = usernameField?.value.trim();
                if (username) {
                    localStorage.setItem('user_username', username);
                }
            });
        }
    },

    /**
     * Initialize keyboard shortcuts
     */
    initKeyboardShortcuts: function() {
        document.addEventListener('keydown', (e) => {
            // Ctrl/Cmd + Enter to submit form
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                const form = document.querySelector('.user-auth__form');
                if (form) {
                    form.submit();
                }
            }
            
            // Escape key to clear form
            if (e.key === 'Escape') {
                const form = document.querySelector('.user-auth__form');
                if (form) {
                    form.reset();
                    this.clearAllValidation();
                }
            }
        });
    },

    /**
     * Clear all validation states
     */
    clearAllValidation: function() {
        const inputs = document.querySelectorAll('.user-auth__input');
        inputs.forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });
    },

    /**
     * Initialize password strength indicator
     */
    initPasswordStrength: function() {
        const passwordInput = document.getElementById('password');
        if (passwordInput) {
            passwordInput.addEventListener('input', (e) => {
                this.updatePasswordStrength(e.target.value);
            });
        }
    },

    /**
     * Update password strength indicator
     */
    updatePasswordStrength: function(password) {
        let strength = 0;
        let feedback = '';
        
        if (password.length >= 6) strength++;
        if (password.match(/[a-z]/)) strength++;
        if (password.match(/[A-Z]/)) strength++;
        if (password.match(/[0-9]/)) strength++;
        if (password.match(/[^a-zA-Z0-9]/)) strength++;
        
        switch (strength) {
            case 0:
            case 1:
                feedback = 'Rất yếu';
                break;
            case 2:
                feedback = 'Yếu';
                break;
            case 3:
                feedback = 'Trung bình';
                break;
            case 4:
                feedback = 'Mạnh';
                break;
            case 5:
                feedback = 'Rất mạnh';
                break;
        }
        
        // Update strength indicator if it exists
        const strengthIndicator = document.getElementById('passwordStrength');
        if (strengthIndicator) {
            strengthIndicator.textContent = feedback;
            strengthIndicator.className = `password-strength strength-${strength <= 2 ? 'weak' : strength <= 3 ? 'medium' : 'strong'}`;
        }
    },

    /**
     * Clear saved user data
     */
    clearUserData: function() {
        localStorage.removeItem('user_username');
    }
};

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UserAuth;
} 