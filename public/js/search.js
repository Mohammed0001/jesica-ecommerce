/**
 * Search functionality for Jesica E-commerce
 */

document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const searchSuggestions = document.getElementById('searchSuggestions');
    let searchTimeout;

    if (searchInput && searchSuggestions) {
        // Handle search input
        searchInput.addEventListener('input', function() {
            const query = this.value.trim();

            // Clear previous timeout
            clearTimeout(searchTimeout);

            if (query.length < 2) {
                searchSuggestions.style.display = 'none';
                return;
            }

            // Debounce search requests
            searchTimeout = setTimeout(() => {
                fetchSuggestions(query);
            }, 300);
        });

        // Hide suggestions when clicking outside
        document.addEventListener('click', function(e) {
            if (!searchInput.contains(e.target) && !searchSuggestions.contains(e.target)) {
                searchSuggestions.style.display = 'none';
            }
        });

        // Handle escape key
        searchInput.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                searchSuggestions.style.display = 'none';
                this.blur();
            }
        });
    }

    /**
     * Fetch search suggestions from the server
     */
    function fetchSuggestions(query) {
        fetch(`/search/suggestions?q=${encodeURIComponent(query)}`, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest',
                'Accept': 'application/json',
            }
        })
        .then(response => response.json())
        .then(data => {
            displaySuggestions(data);
        })
        .catch(error => {
            console.error('Search suggestions error:', error);
            searchSuggestions.style.display = 'none';
        });
    }

    /**
     * Display search suggestions
     */
    function displaySuggestions(data) {
        if (!data.suggestions || data.suggestions.length === 0) {
            searchSuggestions.style.display = 'none';
            return;
        }

        let html = '<div class="suggestions-header">Suggestions</div>';

        data.suggestions.forEach(suggestion => {
            html += `
                <div class="suggestion-item" onclick="selectSuggestion('${suggestion.name}')">
                    <div class="suggestion-icon">
                        ${suggestion.type === 'product' ? '📦' : '📂'}
                    </div>
                    <div class="suggestion-content">
                        <div class="suggestion-name">${suggestion.name}</div>
                        <div class="suggestion-type">${suggestion.type}</div>
                    </div>
                </div>
            `;
        });

        searchSuggestions.innerHTML = html;
        searchSuggestions.style.display = 'block';
    }

    /**
     * Select a suggestion and perform search
     */
    window.selectSuggestion = function(name) {
        searchInput.value = name;
        searchSuggestions.style.display = 'none';

        // Submit search form or redirect
        const searchForm = searchInput.closest('form');
        if (searchForm) {
            searchForm.submit();
        } else {
            window.location.href = `/search?q=${encodeURIComponent(name)}`;
        }
    };
});

/**
 * Search form enhancement
 */
function enhanceSearchForm() {
    const searchForms = document.querySelectorAll('.search-form');

    searchForms.forEach(form => {
        form.addEventListener('submit', function(e) {
            const input = this.querySelector('input[name="q"]');
            if (input && input.value.trim() === '') {
                e.preventDefault();
                input.focus();
                return false;
            }
        });
    });
}

// Initialize search form enhancements
document.addEventListener('DOMContentLoaded', enhanceSearchForm);
