// Search functionality enhancement
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('search');
    const searchForm = document.querySelector('.search-form');
    
    if (!searchInput) return;

    // Search suggestions functionality
    let searchTimeout;
    const suggestionsContainer = document.createElement('div');
    suggestionsContainer.className = 'search-suggestions';
    suggestionsContainer.style.cssText = `
        position: absolute;
        top: 100%;
        left: 0;
        background: white;
        border: 1px solid #ddd;
        border-top: none;
        border-radius: 0 0 10px 10px;
        max-height: 200px;
        overflow-y: auto;
        z-index: 50;
        display: none;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    `;

    const searchContainer = searchInput.closest('.search-input-container');
    function sizeSuggestionToInput() {
        if (!searchInput || !suggestionsContainer) return;
        const inputRect = searchInput.getBoundingClientRect();
        // Ensure the dropdown matches the input width to avoid covering menu on the right
        suggestionsContainer.style.width = `${Math.ceil(inputRect.width)}px`;
    }

    if (searchContainer) {
        searchContainer.style.position = 'relative';
        searchContainer.appendChild(suggestionsContainer);
        sizeSuggestionToInput();
        window.addEventListener('resize', sizeSuggestionToInput);
    }

    // Debounced search function
    function debounceSearch(query) {
        clearTimeout(searchTimeout);
        searchTimeout = setTimeout(() => {
            if (query.length >= 2) {
                fetchSearchSuggestions(query);
            } else {
                hideSuggestions();
            }
        }, 300);
    }

    // Fetch search suggestions
    function fetchSearchSuggestions(query) {
        fetch(`${BASE_URL}/ajax_handler.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `action=search_suggestions&query=${encodeURIComponent(query)}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success && data.suggestions.length > 0) {
                showSuggestions(data.suggestions);
            } else {
                hideSuggestions();
            }
        })
        .catch(error => {
            console.error('Error fetching suggestions:', error);
            hideSuggestions();
        });
    }

    // Show search suggestions
    function showSuggestions(suggestions) {
        suggestionsContainer.innerHTML = '';
        
        suggestions.forEach(suggestion => {
            const suggestionItem = document.createElement('div');
            suggestionItem.className = 'suggestion-item';
            suggestionItem.style.cssText = `
                padding: 10px 15px;
                cursor: pointer;
                border-bottom: 1px solid #eee;
                display: flex;
                align-items: center;
                gap: 10px;
            `;
            
            suggestionItem.innerHTML = `
                <i class="bi bi-search" style="color: #666;"></i>
                <div style="flex: 1;">
                    <div style="font-weight: 500;">${suggestion.product_name}</div>
                    <div style="font-size: 12px; color: #666;">${suggestion.category_name}</div>
                </div>
                <div style="font-size: 12px; color: var(--secondary-color); font-weight: 500;">
                    ${suggestion.price ? formatPrice(suggestion.price) : ''}
                </div>
            `;
            
            // Store product ID in dataset for easy access
            suggestionItem.dataset.productId = suggestion.product_id;
            
            // Click to go directly to product detail page
            suggestionItem.addEventListener('click', () => {
                hideSuggestions();
                window.location.href = `${BASE_URL}/?page=product-detail&id=${suggestion.product_id}`;
            });
            
            suggestionItem.addEventListener('mouseenter', () => {
                suggestionItem.style.backgroundColor = '#f8f9fa';
            });
            
            suggestionItem.addEventListener('mouseleave', () => {
                suggestionItem.style.backgroundColor = 'white';
            });
            
            suggestionsContainer.appendChild(suggestionItem);
        });
        
        sizeSuggestionToInput();
        suggestionsContainer.style.display = 'block';
    }

    // Format price for display
    function formatPrice(price) {
        return new Intl.NumberFormat('vi-VN', {
            style: 'currency',
            currency: 'VND'
        }).format(price);
    }

    // Hide search suggestions
    function hideSuggestions() {
        suggestionsContainer.style.display = 'none';
    }

    // Event listeners
    searchInput.addEventListener('input', function() {
        const query = this.value.trim();
        debounceSearch(query);
    });

    searchInput.addEventListener('focus', function() {
        const query = this.value.trim();
        if (query.length >= 2) {
            debounceSearch(query);
        }
    });

    // Hide suggestions when clicking outside
    document.addEventListener('click', function(e) {
        if (!searchInput.contains(e.target) && !suggestionsContainer.contains(e.target)) {
            hideSuggestions();
        }
    });

    // Keyboard navigation for suggestions
    searchInput.addEventListener('keydown', function(e) {
        const visibleSuggestions = suggestionsContainer.querySelectorAll('.suggestion-item');
        const currentIndex = Array.from(visibleSuggestions).findIndex(item => 
            item.style.backgroundColor === 'rgb(248, 249, 250)'
        );

        if (e.key === 'ArrowDown') {
            e.preventDefault();
            const nextIndex = (currentIndex + 1) % visibleSuggestions.length;
            visibleSuggestions.forEach(item => item.style.backgroundColor = 'white');
            if (visibleSuggestions[nextIndex]) {
                visibleSuggestions[nextIndex].style.backgroundColor = '#f8f9fa';
            }
        } else if (e.key === 'ArrowUp') {
            e.preventDefault();
            const prevIndex = currentIndex <= 0 ? visibleSuggestions.length - 1 : currentIndex - 1;
            visibleSuggestions.forEach(item => item.style.backgroundColor = 'white');
            if (visibleSuggestions[prevIndex]) {
                visibleSuggestions[prevIndex].style.backgroundColor = '#f8f9fa';
            }
        } else if (e.key === 'Enter') {
            e.preventDefault();
            const selectedSuggestion = suggestionsContainer.querySelector('.suggestion-item[style*="background-color: rgb(248, 249, 250)"]');
            if (selectedSuggestion) {
                const productId = selectedSuggestion.dataset.productId;
                if (productId) {
                    hideSuggestions();
                    window.location.href = `${BASE_URL}/?page=product-detail&id=${productId}`;
                }
            } else {
                const searchForm = document.querySelector('.search-form');
                if (searchForm) {
                    searchForm.submit();
                }
            }
        } else if (e.key === 'Escape') {
            hideSuggestions();
        }
    });
});

// Add CSS for search suggestions
const searchStyles = document.createElement('style');
searchStyles.textContent = `
    .search-suggestions::-webkit-scrollbar {
        width: 6px;
    }
    
    .search-suggestions::-webkit-scrollbar-track {
        background: #f1f1f1;
    }
    
    .search-suggestions::-webkit-scrollbar-thumb {
        background: #c1c1c1;
        border-radius: 3px;
    }
    
    .search-suggestions::-webkit-scrollbar-thumb:hover {
        background: #a8a8a8;
    }
    
    @media (max-width: 768px) {
        .search-suggestions {
            max-height: 150px !important;
        }
        
        .suggestion-item {
            padding: 12px 15px !important;
        }
    }
`;
document.head.appendChild(searchStyles);
