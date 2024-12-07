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
            contents.forEach(c => c.classList.remove('active'));

            // Add active class to clicked tab and corresponding content
            this.classList.add('active');
            this.setAttribute('aria-selected', 'true');
            targetContent.classList.add('active');

            // Reset pagination state
            targetContent.setAttribute('data-paged', 1);

            // AJAX load for non-loaded tabs
            const categorySlug = this.getAttribute('data-slug');
            const postsPerPage = this.getAttribute('data-posts-per-page');
            loadTabContent(targetContent, categorySlug, postsPerPage, 1); // Load first page
        });
    });

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

    function loadTabContent(tabContent, categorySlug, postsPerPage, paged) {
        tabContent.innerHTML = `<div class="loading-spinner">Loading...</div>`;

        console.log("Sending categorySlug:", categorySlug);
        console.log("Sending postsPerPage:", postsPerPage);
        console.log("Sending paged:", paged);

        fetch(sp_ajax.ajax_url, {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: `action=sp_load_tab&category_slug=${categorySlug}&posts_per_page=${postsPerPage}&paged=${paged}&security=${sp_ajax.nonce}`,
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    tabContent.innerHTML = data.data.content;

                    const pagination = `
                        <div class="pagination">
                            ${data.data.has_prev_page ? `<button class="load-newer" data-slug="${categorySlug}" data-posts-per-page="${postsPerPage}">« Entradas más recientes</button>` : ''}
                            ${data.data.has_next_page ? `<button class="load-older" data-slug="${categorySlug}" data-posts-per-page="${postsPerPage}">Entradas más antiguas »</button>` : ''}
                        </div>
                    `;
                    tabContent.innerHTML += pagination;
                } else {
                    tabContent.innerHTML = `<p>${data.data?.message || 'Error loading content.'}</p>`;
                }
            })
            .catch(error => {
                console.error('Error loading content:', error);
                tabContent.innerHTML = `<p>Error loading content: ${error.message}</p>`;
            });
    }


});