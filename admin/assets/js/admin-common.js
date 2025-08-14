/**
 * Admin Common JavaScript Functions
 * Provides shared functionality across all admin pages
 */

// Admin namespace to avoid conflicts
window.AdminPanel = window.AdminPanel || {};

// Admin utility functions
AdminPanel.Utils = {
    showNotification: function(message, type = 'info', duration = 5000) {
        const notification = document.createElement('div');
        notification.className = `admin-alert admin-alert-${type}`;
        notification.innerHTML = `
            <div class="d-flex align-items-center">
                <i class="bi bi-${this.getNotificationIcon(type)} me-2"></i>
                <span>${message}</span>
                <button type="button" class="btn-close ms-auto" onclick="this.parentElement.parentElement.remove()"></button>
            </div>
        `;
        
        const container = document.querySelector('.admin-main-content') || document.body;
        container.insertBefore(notification, container.firstChild);
        
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, duration);
        
        return notification;
    },
    
    getNotificationIcon: function(type) {
        const icons = {
            success: 'check-circle-fill',
            error: 'exclamation-triangle-fill',
            warning: 'exclamation-triangle-fill',
            info: 'info-circle-fill'
        };
        return icons[type] || 'info-circle-fill';
    },
    
    confirmAction: function(message, callback) {
        if (confirm(message)) {
            if (typeof callback === 'function') {
                callback();
            }
        }
    },
    
    formatCurrency: function(amount, currency = '₫') {
        return new Intl.NumberFormat('vi-VN').format(amount) + currency;
    },
    
    formatDate: function(date) {
        if (typeof date === 'string') {
            date = new Date(date);
        }
        return date.toLocaleDateString('vi-VN', {
            year: 'numeric',
            month: 'long',
            day: 'numeric',
            hour: '2-digit',
            minute: '2-digit'
        });
    }
};

// Admin form handling
AdminPanel.Forms = {
    initValidation: function(formSelector) {
        const forms = document.querySelectorAll(formSelector);
        forms.forEach(form => {
            form.addEventListener('submit', this.handleSubmit.bind(this));
        });
    },
    
    handleSubmit: function(e) {
        if (!this.validateForm(e.target)) {
            e.preventDefault();
            AdminPanel.Utils.showNotification('Vui lòng kiểm tra lại thông tin nhập vào', 'error');
        }
    },
    
    validateForm: function(form) {
        let isValid = true;
        const inputs = form.querySelectorAll('input[required], select[required], textarea[required]');
        
        inputs.forEach(input => {
            if (!input.value.trim()) {
                this.showInputError(input, 'Trường này là bắt buộc');
                isValid = false;
            } else {
                this.clearInputError(input);
            }
        });
        
        return isValid;
    },
    
    showInputError: function(input, message) {
        this.clearInputError(input);
        input.classList.add('is-invalid');
        
        const errorDiv = document.createElement('div');
        errorDiv.className = 'invalid-feedback';
        errorDiv.textContent = message;
        input.parentNode.appendChild(errorDiv);
    },
    
    clearInputError: function(input) {
        input.classList.remove('is-invalid');
        const errorDiv = input.parentNode.querySelector('.invalid-feedback');
        if (errorDiv) {
            errorDiv.remove();
        }
    }
};

// Admin sidebar handling
AdminPanel.Sidebar = {
    init: function() {
        this.highlightCurrentPage();
    },
    
    highlightCurrentPage: function() {
        const currentPage = window.location.search.match(/page=([^&]+)/)?.[1] || 'dashboard';
        const navLinks = document.querySelectorAll('.admin-sidebar .nav-link');
        
        navLinks.forEach(link => {
            link.classList.remove('active');
            if (link.getAttribute('href').includes(currentPage) || 
                (currentPage === 'dashboard' && link.getAttribute('href') === 'index.php')) {
                link.classList.add('active');
            }
        });
    }
};

// Initialize admin panel when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    AdminPanel.Forms.initValidation('form');
    AdminPanel.Sidebar.init();
}); 