/**
 * Header Dropdown JavaScript
 * Click-only dropdown functionality for user menu
 */

document.addEventListener('DOMContentLoaded', function() {
    // User menu dropdown functionality
    const userMenu = document.querySelector('.user-menu');
    const userMenuToggle = document.querySelector('.user-menu__toggle');
    const userMenuDropdown = document.querySelector('.user-menu__dropdown');
    
    if (userMenu && userMenuToggle && userMenuDropdown) {
        // Click functionality for all devices
        userMenuToggle.addEventListener('click', function(e) {
            e.preventDefault();
            e.stopPropagation();
            
            // Toggle dropdown visibility
            const isVisible = userMenuDropdown.classList.contains('show');
            
            if (isVisible) {
                hideDropdown();
            } else {
                showDropdown();
            }
        });
        
        // Hide dropdown when clicking outside
        document.addEventListener('click', function(e) {
            if (!userMenu.contains(e.target)) {
                hideDropdown();
            }
        });
        
        // Hide dropdown on escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                hideDropdown();
            }
        });
        
        // Show dropdown function
        function showDropdown() {
            userMenuDropdown.classList.add('show');
            userMenuToggle.setAttribute('aria-expanded', 'true');
            
            // Ensure proper positioning
            const toggleRect = userMenuToggle.getBoundingClientRect();
            const dropdownRect = userMenuDropdown.getBoundingClientRect();
            
            // Check if dropdown would overflow to the right
            if (toggleRect.right + dropdownRect.width > window.innerWidth) {
                userMenuDropdown.style.right = 'auto';
                userMenuDropdown.style.left = '0';
            } else {
                userMenuDropdown.style.right = '0';
                userMenuDropdown.style.left = 'auto';
            }
        }
        
        // Hide dropdown function
        function hideDropdown() {
            userMenuDropdown.classList.remove('show');
            userMenuToggle.setAttribute('aria-expanded', 'false');
        }
    }
    
    // Add smooth scrolling for anchor links
    const anchorLinks = document.querySelectorAll('a[href^="#"]');
    anchorLinks.forEach(link => {
        link.addEventListener('click', function(e) {
            const href = this.getAttribute('href');
            if (href !== '#') {
                const target = document.querySelector(href);
                if (target) {
                    e.preventDefault();
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });
    });
    
    // Add loading states for form submissions
    const forms = document.querySelectorAll('form');
    forms.forEach(form => {
        form.addEventListener('submit', function() {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="bi bi-hourglass-split"></i> Đang xử lý...';
            }
        });
    });
    
    // Mobile drawer controls
    const hamburger = document.querySelector('.hamburger');
    const drawer = document.querySelector('.mobile-drawer');
    const closeBtn = document.querySelector('.drawer-close');
    const backdrop = document.querySelector('.mobile-drawer__backdrop');
    const drawerLinks = document.querySelectorAll('.mobile-drawer a');
    function openDrawer(){ if (drawer){ drawer.classList.add('open'); document.body.style.overflow='hidden'; if (hamburger) hamburger.setAttribute('aria-expanded','true'); } }
    function closeDrawer(){ if (drawer){ drawer.classList.remove('open'); document.body.style.overflow=''; if (hamburger) hamburger.setAttribute('aria-expanded','false'); } }
    if (hamburger) hamburger.addEventListener('click', openDrawer);
    if (closeBtn) closeBtn.addEventListener('click', closeDrawer);
    if (backdrop) backdrop.addEventListener('click', closeDrawer);
    // Close drawer on any link click
    drawerLinks.forEach(l => l.addEventListener('click', closeDrawer));
}); 