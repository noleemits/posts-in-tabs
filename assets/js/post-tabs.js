document.addEventListener('DOMContentLoaded', function () {
    console.log("Yes sir");
    const tabs = document.querySelectorAll('.tab-title');
    const contents = document.querySelectorAll('.tab-content');

    // Handle tab clicks
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
                c.innerHTML = ''; // Fully clear content for all tabs
                c.removeAttribute('data-paged'); // Reset pagination state
            });

            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');
            targetContent.classList.add('active');

            // Reset pagination state for the active tab
            targetContent.setAttribute('data-paged', 1);

            // AJAX load content for the active tab
            const categorySlug = this.getAttribute('data-slug');
            const postsPerPage = this.getAttribute('data-posts-per-page');
            loadTabContent(targetContent, categorySlug, postsPerPage, 1); // Load the first page
        });
    });

    // Handle pagination clicks
    document.addEventListener('click', function (e) {
        if (e.target.matches('.load-older') || e.target.matches('.load-newer')) {
            const button = e.target;
            const tabContent = button.closest('.tab-content');
            const categorySlug = button.getAttribute('data-slug');
            const postsPerPage = button.getAttribute('data-posts-per-page');
            const currentPaged = parseInt(tabContent.getAttribute('data-paged')) || 1;
            const nextPaged = button.classList.contains('load-older') ? currentPaged + 1 : currentPaged - 1;

            loadTabContent(tabContent, categorySlug, postsPerPage, nextPaged);
        }
    });

    // Load content for a specific tab
    function loadTabContent(tabContent, categorySlug, postsPerPage, paged) {
        // Add a loading spinner
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
                console.log('AJAX Response:', data); // Log the response for debugging
                if (data.success) {
                    // Update the content of the tab
                    tabContent.innerHTML = data.data.content;

                    // Add pagination buttons
                    const pagination = `
                        <div class="pagination">
                            ${data.data.has_prev_page ? `<button class="load-newer" data-slug="${categorySlug}" data-posts-per-page="${postsPerPage}">« Entradas más recientes</button>` : ''}
                            ${data.data.has_next_page ? `<button class="load-older" data-slug="${categorySlug}" data-posts-per-page="${postsPerPage}">Entradas más antiguas »</button>` : ''}
                        </div>
                    `;
                    tabContent.innerHTML += pagination;
                } else {
                    tabContent.innerHTML = `<p>${data.data.message}</p>`;
                }
            })
            .catch(error => {
                console.error('Error loading tab content:', error);
                tabContent.innerHTML = `<p>Error loading content: ${error.message}</p>`;
            });
    }

});
