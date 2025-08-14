/**
 * User Profile JavaScript
 * Handles profile management, tab switching, and form validation
 */

// User Profile namespace
window.UserProfile = window.UserProfile || {};

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    UserProfile.init();
});

UserProfile = {
    /**
     * Initialize user profile functionality
     */
    init: function() {
        this.initTabSwitching();
        this.initFormValidation();
        this.initAutoHideAlerts();
        this.initPasswordChange();
        this.initOrderActions();
        this.initResponsiveNav();
    },

    /**
     * Initialize tab switching functionality
     */
    initTabSwitching: function() {
        const navLinks = document.querySelectorAll('.user-profile__nav-link[data-tab]');
        const tabContents = document.querySelectorAll('.user-profile__tab-content');
        
        navLinks.forEach(link => {
            link.addEventListener('click', (e) => {
                e.preventDefault();
                
                // Remove active class from all nav links
                navLinks.forEach(navLink => {
                    navLink.classList.remove('user-profile__nav-link--active');
                });
                
                // Hide all tab contents
                tabContents.forEach(content => {
                    content.style.display = 'none';
                });
                
                // Add active class to clicked link
                link.classList.add('user-profile__nav-link--active');
                
                // Show corresponding tab content
                const targetTab = link.getAttribute('data-tab');
                const targetContent = document.getElementById(targetTab + '-tab');
                if (targetContent) {
                    targetContent.style.display = 'block';
                }
                
                // Update URL hash
                window.location.hash = targetTab;
            });
        });
        
        // Handle initial tab based on URL hash
        this.handleInitialTab();
    },

    /**
     * Handle initial tab based on URL hash
     */
    handleInitialTab: function() {
        const hash = window.location.hash.replace('#', '');
        if (hash) {
            const targetLink = document.querySelector(`[data-tab="${hash}"]`);
            if (targetLink) {
                targetLink.click();
            }
        }
    },

    /**
     * Initialize form validation
     */
    initFormValidation: function() {
        const forms = document.querySelectorAll('.user-profile__form');
        
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
        const inputs = form.querySelectorAll('.user-profile__input[required]');
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

        // Special validation for password change form
        if (form.id === 'passwordForm') {
            isValid = this.validatePasswordForm(form) && isValid;
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
     * Validate password change form
     */
    validatePasswordForm: function(form) {
        let isValid = true;
        
        const newPassword = form.querySelector('#new_password');
        const confirmPassword = form.querySelector('#confirm_new_password');
        
        if (newPassword && newPassword.value.length < 6) {
            newPassword.classList.remove('is-valid');
            newPassword.classList.add('is-invalid');
            isValid = false;
        }

        if (confirmPassword && newPassword && confirmPassword.value !== newPassword.value) {
            confirmPassword.classList.remove('is-valid');
            confirmPassword.classList.add('is-invalid');
            isValid = false;
        }

        return isValid;
    },

    /**
     * Add input validation to form
     */
    addInputValidation: function(form) {
        const inputs = form.querySelectorAll('.user-profile__input');
        
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
            case 'phone':
                if (value && !this.isValidPhone(value)) {
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
     * Validate phone format
     */
    isValidPhone: function(phone) {
        const phoneRegex = /^[0-9+\-\s()]+$/;
        return phoneRegex.test(phone);
    },

    /**
     * Show loading state on form
     */
    showLoadingState: function(form) {
        const submitBtn = form.querySelector('.user-profile__button--primary');
        if (submitBtn) {
            const originalText = submitBtn.innerHTML;
            submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang xử lý...';
            submitBtn.disabled = true;
            
            // Re-enable after 5 seconds if form doesn't submit
            setTimeout(() => {
                submitBtn.innerHTML = originalText;
                submitBtn.disabled = false;
            }, 5000);
        }
    },

    /**
     * Initialize auto-hide alerts
     */
    initAutoHideAlerts: function() {
        setTimeout(() => {
            const alerts = document.querySelectorAll('.user-profile__alert');
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
        alertDiv.className = `user-profile__alert user-profile__alert--${type}`;
        alertDiv.innerHTML = `
            <i class="bi bi-${type === 'error' ? 'exclamation-triangle-fill' : 'info-circle-fill'}"></i> 
            ${message}
        `;
        
        const cardBody = document.querySelector('.user-profile__card-body');
        if (cardBody) {
            cardBody.insertBefore(alertDiv, cardBody.firstChild);
            
            // Auto-hide after 5 seconds
            setTimeout(() => {
                this.fadeOutAlert(alertDiv);
            }, 5000);
        }
    },

    /**
     * Initialize password change functionality
     */
    initPasswordChange: function() {
        const passwordForm = document.getElementById('passwordForm');
        if (passwordForm) {
            // Add password strength indicator
            const newPasswordInput = passwordForm.querySelector('#new_password');
            if (newPasswordInput) {
                newPasswordInput.addEventListener('input', (e) => {
                    this.updatePasswordStrength(e.target.value);
                });
            }
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
     * Initialize order actions
     */
    initOrderActions: function() {
        const orderItems = document.querySelectorAll('.user-profile__order-item');
        
        orderItems.forEach(item => {
            const viewButton = item.querySelector('.user-profile__button--secondary');
            if (viewButton) {
                viewButton.addEventListener('click', (e) => {
                    // Handle order detail view
                    const orderId = e.target.closest('.user-profile__order-item').querySelector('.user-profile__order-id').textContent.split('#')[1];
                    this.viewOrderDetail(orderId);
                });
            }
        });
    },

    /**
     * View order detail
     */
    viewOrderDetail: function(orderId) {
        // Navigate to order detail page
        window.location.href = `${window.location.origin}/order-detail/${orderId}`;
    },

    /**
     * Initialize responsive navigation
     */
    initResponsiveNav: function() {
        // Add mobile menu toggle if needed
        const sidebar = document.querySelector('.user-profile__sidebar');
        const navLinks = document.querySelectorAll('.user-profile__nav-link');
        
        // Add click outside to close functionality for mobile
        document.addEventListener('click', (e) => {
            if (window.innerWidth <= 768) {
                if (!sidebar.contains(e.target) && !e.target.closest('.user-profile__nav-link')) {
                    navLinks.forEach(link => {
                        link.classList.remove('user-profile__nav-link--active');
                    });
                }
            }
        });
    },

    /**
     * Format currency
     */
    formatCurrency: function(amount) {
        return new Intl.NumberFormat('vi-VN').format(amount) + '₫';
    },

    /**
     * Format date
     */
    formatDate: function(dateString) {
        const date = new Date(dateString);
        return date.toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    },

    /**
     * Get order status color
     */
    getOrderStatusColor: function(status) {
        const statusColors = {
            'pending': 'warning',
            'processing': 'info',
            'shipped': 'primary',
            'delivered': 'success',
            'cancelled': 'danger'
        };
        return statusColors[status.toLowerCase()] || 'secondary';
    },

    /**
     * Refresh user data
     */
    refreshUserData: function() {
        // Reload page to refresh user data
        window.location.reload();
    },

    /**
     * Clear all validation states
     */
    clearAllValidation: function() {
        const inputs = document.querySelectorAll('.user-profile__input');
        inputs.forEach(input => {
            input.classList.remove('is-valid', 'is-invalid');
        });
    }
};

// Export for module systems
if (typeof module !== 'undefined' && module.exports) {
    module.exports = UserProfile;
} 