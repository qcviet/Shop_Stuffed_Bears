// Category Page JavaScript
document.addEventListener('DOMContentLoaded', function() {
    const filterForm = document.getElementById('filterForm');
    const applyBtn = document.querySelector('.apply-filters-btn');
    const btnText = document.querySelector('.btn-text');
    const loadingSpinner = document.querySelector('.loading-spinner');
    const sizeChips = document.querySelectorAll('.js-size-chip');
    const priceElements = document.querySelectorAll('.js-price');

    // Price input formatting
    const priceInputs = document.querySelectorAll('input[name="min"], input[name="max"]');
    priceInputs.forEach(input => {
        input.addEventListener('input', function(e) {
            // Remove non-numeric characters except commas
            let value = e.target.value.replace(/[^\d,]/g, '');
            
            // Remove commas for processing
            let numericValue = value.replace(/,/g, '');
            
            // Format with commas
            if (numericValue) {
                value = parseInt(numericValue).toLocaleString('vi-VN');
            }
            
            e.target.value = value;
        });

        // Format on focus
        input.addEventListener('focus', function(e) {
            let value = e.target.value.replace(/,/g, '');
            e.target.value = value;
        });

        // Format on blur
        input.addEventListener('blur', function(e) {
            let value = e.target.value.replace(/,/g, '');
            if (value) {
                e.target.value = parseInt(value).toLocaleString('vi-VN');
            }
        });
    });

    // Form submission with loading state
    if (filterForm) {
        filterForm.addEventListener('submit', function(e) {
            // Show loading state
            if (applyBtn && btnText && loadingSpinner) {
                btnText.style.display = 'none';
                loadingSpinner.style.display = 'inline-block';
                applyBtn.disabled = true;
            }

            // Reset to page 1 when applying filters
            const pageNumInput = filterForm.querySelector('input[name="page_num"]');
            if (pageNumInput) {
                pageNumInput.value = '1';
            }

            // Add a small delay to show the loading animation
            setTimeout(() => {
                // Form will submit normally
            }, 300);
        });
    }

    // Size chip interactions
    sizeChips.forEach(chip => {
        chip.addEventListener('click', function(e) {
            e.preventDefault();
            
            // Remove active class from all chips in the same product
            const productCard = this.closest('.product-card');
            const allChipsInProduct = productCard.querySelectorAll('.js-size-chip');
            allChipsInProduct.forEach(c => c.classList.remove('active'));
            
            // Add active class to clicked chip
            this.classList.add('active');
            
            // Update price display
            const price = this.getAttribute('data-price');
            const priceElement = productCard.querySelector('.js-price');
            if (priceElement && price) {
                const formattedPrice = parseInt(price).toLocaleString('vi-VN') + ' ₫';
                priceElement.textContent = formattedPrice;
                priceElement.setAttribute('data-price', price);
            }
        });
    });

    // Smooth scroll to top when filters are applied
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('min') || urlParams.has('max') || urlParams.has('size') || urlParams.has('color')) {
        setTimeout(() => {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        }, 100);
    }

    // Add hover effects to product cards
    const productCards = document.querySelectorAll('.product-card');
    productCards.forEach(card => {
        card.addEventListener('mouseenter', function() {
            this.style.transform = 'translateY(-8px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = 'translateY(0)';
        });
    });

    // Animate category links on hover
    const categoryLinks = document.querySelectorAll('.category-link');
    categoryLinks.forEach(link => {
        link.addEventListener('mouseenter', function() {
            this.style.transform = 'translateX(5px)';
        });
        
        link.addEventListener('mouseleave', function() {
            this.style.transform = 'translateX(0)';
        });
    });

    // Add ripple effect to buttons
    const buttons = document.querySelectorAll('.apply-filters-btn, .clear-filters-btn');
    buttons.forEach(button => {
        button.addEventListener('click', function(e) {
            const ripple = document.createElement('span');
            const rect = this.getBoundingClientRect();
            const size = Math.max(rect.width, rect.height);
            const x = e.clientX - rect.left - size / 2;
            const y = e.clientY - rect.top - size / 2;
            
            ripple.style.width = ripple.style.height = size + 'px';
            ripple.style.left = x + 'px';
            ripple.style.top = y + 'px';
            ripple.classList.add('ripple');
            
            this.appendChild(ripple);
            
            setTimeout(() => {
                ripple.remove();
            }, 600);
        });
    });

    // Add CSS for ripple effect
    const style = document.createElement('style');
    style.textContent = `
        .apply-filters-btn, .clear-filters-btn {
            position: relative;
            overflow: hidden;
        }
        
        .ripple {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.3);
            transform: scale(0);
            animation: ripple-animation 0.6s linear;
            pointer-events: none;
        }
        
        @keyframes ripple-animation {
            to {
                transform: scale(4);
                opacity: 0;
            }
        }
    `;
    document.head.appendChild(style);

    // Auto-submit form when dropdowns change (optional)
    const dropdowns = document.querySelectorAll('select[name="size"], select[name="color"]');
    dropdowns.forEach(dropdown => {
        dropdown.addEventListener('change', function() {
            // Uncomment the line below if you want auto-submit on dropdown change
            // filterForm.submit();
        });
    });

    // Add keyboard navigation for accessibility
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Enter' && document.activeElement.classList.contains('size-chip')) {
            e.preventDefault();
            document.activeElement.click();
        }
    });

    // Lazy loading for images (if needed)
    const images = document.querySelectorAll('.product-image img');
    if ('IntersectionObserver' in window) {
        const imageObserver = new IntersectionObserver((entries, observer) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    const img = entry.target;
                    img.style.opacity = '1';
                    img.style.transform = 'scale(1)';
                    observer.unobserve(img);
                }
            });
        });

        images.forEach(img => {
            img.style.opacity = '0';
            img.style.transform = 'scale(1.1)';
            img.style.transition = 'opacity 0.3s ease, transform 0.3s ease';
            imageObserver.observe(img);
        });
    }

    // Add smooth transitions for filter changes
    const filterElements = document.querySelectorAll('.filter-dropdown select, .price-input input');
    filterElements.forEach(element => {
        element.addEventListener('change', function() {
            this.style.transform = 'scale(1.02)';
            setTimeout(() => {
                this.style.transform = 'scale(1)';
            }, 150);
        });
    });

    // Highlight active filters
    const activeFilters = [];
    if (urlParams.get('min')) activeFilters.push('Giá từ: ' + urlParams.get('min'));
    if (urlParams.get('max')) activeFilters.push('Giá đến: ' + urlParams.get('max'));
    if (urlParams.get('size')) activeFilters.push('Kích thước: ' + urlParams.get('size'));
    if (urlParams.get('color')) activeFilters.push('Màu sắc: ' + urlParams.get('color'));

    if (activeFilters.length > 0) {
        const filterSection = document.querySelector('.filter-section');
        if (filterSection) {
            const activeFiltersDiv = document.createElement('div');
            activeFiltersDiv.className = 'active-filters';
            activeFiltersDiv.innerHTML = `
                <div style="margin-top: 1rem; padding: 0.75rem; background: #f0f9ff; border-radius: 8px; border-left: 3px solid #0369a1;">
                    <strong style="color: #0369a1; font-size: 0.9rem;">Bộ lọc đang áp dụng:</strong>
                    <div style="margin-top: 0.5rem;">
                        ${activeFilters.map(filter => `<span style="display: inline-block; background: #0369a1; color: white; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.8rem; margin: 0.25rem;">${filter}</span>`).join('')}
                    </div>
                </div>
            `;
            filterSection.appendChild(activeFiltersDiv);
        }
    }

    // Add tooltips for better UX
    const tooltipElements = document.querySelectorAll('[data-tooltip]');
    tooltipElements.forEach(element => {
        element.addEventListener('mouseenter', function() {
            const tooltip = document.createElement('div');
            tooltip.className = 'tooltip';
            tooltip.textContent = this.getAttribute('data-tooltip');
            tooltip.style.cssText = `
                position: absolute;
                background: #333;
                color: white;
                padding: 0.5rem;
                border-radius: 4px;
                font-size: 0.8rem;
                z-index: 1000;
                pointer-events: none;
                white-space: nowrap;
            `;
            
            document.body.appendChild(tooltip);
            
            const rect = this.getBoundingClientRect();
            tooltip.style.left = rect.left + (rect.width / 2) - (tooltip.offsetWidth / 2) + 'px';
            tooltip.style.top = rect.top - tooltip.offsetHeight - 5 + 'px';
            
            this.tooltip = tooltip;
        });
        
        element.addEventListener('mouseleave', function() {
            if (this.tooltip) {
                this.tooltip.remove();
                this.tooltip = null;
            }
        });
    });

    console.log('Category page enhanced with smooth interactions!');
});
