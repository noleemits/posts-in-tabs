document.addEventListener('DOMContentLoaded', function () {
    const tabs = document.querySelectorAll('.tab-title');
    const contents = document.querySelectorAll('.tab-content');

    tabs.forEach(tab => {
        tab.addEventListener('click', function () {
            const targetTab = this.getAttribute('data-tab');
            const targetContent = document.getElementById(targetTab);

            // Remove active class from all tabs and contents
            tabs.forEach(t => {
                t.classList.remove('active');
                t.setAttribute('aria-selected', 'false');
            });
            contents.forEach(c => {
                c.classList.remove('active');
                c.innerHTML = ''; // Clear content for non-active tabs
            });

            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');
            targetContent.classList.add('active');

            // Reset pagination state
            targetContent.setAttribute('data-paged', 1);

            // Clear existing content to avoid duplication
            targetContent.innerHTML = '';

            // Load new content for the selected tab
            const categorySlug = this.getAttribute('data-slug');
            const postsPerPage = this.getAttribute('data-posts-per-page');
            loadTabContent(targetContent, categorySlug, postsPerPage, 1);
        });

    });

    // Pagination click handler
    document.addEventListener('click', function (e) {
        if (e.target.matches('.load-older') || e.target.matches('.load-newer')) {
            e.preventDefault(); // Prevent default action
            const button = e.target;
            const tabContent = button.closest('.tab-content');
            const categorySlug = button.getAttribute('data-slug');
            const postsPerPage = button.getAttribute('data-posts-per-page');
            const currentPaged = parseInt(tabContent.getAttribute('data-paged')) || 1;
            const nextPaged = button.classList.contains('load-older') ? currentPaged + 1 : currentPaged - 1;

            loadTabContent(tabContent, categorySlug, postsPerPage, nextPaged);

            // Smooth scroll back to the top of the tab content
            tabContent.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });

    function loadTabContent(tabContent, categorySlug, postsPerPage, paged) {
        tabContent.innerHTML = `<div class="loading-spinner">Loading...</div>`;

        fetch(sp_ajax.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=sp_load_tab&category_slug=${categorySlug}&posts_per_page=${postsPerPage}&paged=${paged}&security=${sp_ajax.nonce}`,
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Replace tab content with the loaded posts
                    tabContent.innerHTML = data.data.content;

                    // Remove any existing pagination
                    const existingPagination = tabContent.querySelector('.pagination');
                    if (existingPagination) {
                        existingPagination.remove();
                    }

                    // Add the pagination container dynamically
                    if (data.data.pagination) {
                        const paginationContainer = document.createElement('div');
                        paginationContainer.className = 'pagination';
                        paginationContainer.innerHTML = data.data.pagination;
                        tabContent.appendChild(paginationContainer);
                    }

                    // Update `data-paged`
                    tabContent.setAttribute('data-paged', paged);

                    // Disable buttons if necessary
                    const newerButton = tabContent.querySelector('.load-newer');
                    const olderButton = tabContent.querySelector('.load-older');

                    if (newerButton) {
                        newerButton.disabled = paged === 1; // Disable "Entradas más recientes" on first page
                        newerButton.classList.toggle('disabled', paged === 1);
                    }

                    if (olderButton) {
                        olderButton.disabled = paged >= data.data.max_pages; // Disable "Entradas más antiguas" on last page
                        olderButton.classList.toggle('disabled', paged >= data.data.max_pages);
                    }
                } else {
                    tabContent.innerHTML = `<p>${data.data.message}</p>`;
                }
            })
            .catch(error => {
                tabContent.innerHTML = `<p>Error loading content: ${error.message}</p>`;
            });
    }

});
