(function () {
    'use strict';

    var btn = document.getElementById('load-more-btn');
    if (!btn) return;

    var grid = document.getElementById('product-grid');
    var currentPage = parseInt(btn.dataset.page, 10) || 1;
    var maxPage = parseInt(btn.dataset.maxPage, 10) || 1;
    var baseUrl = btn.dataset.url || '/products/fragment';

    var criteria = {};
    try {
        criteria = JSON.parse(btn.dataset.criteria || '{}');
    } catch (e) {}

    btn.addEventListener('click', function () {
        var nextPage = currentPage + 1;
        if (nextPage > maxPage) return;

        btn.disabled = true;
        showSkeletons(grid, 3);

        var params = new URLSearchParams(criteria);
        params.set('page', String(nextPage));

        fetch(baseUrl + '?' + params.toString(), {
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        })
            .then(function (res) {
                if (!res.ok) throw new Error('Network error');
                return res.text();
            })
            .then(function (html) {
                removeSkeletons(grid);
                var temp = document.createElement('div');
                temp.innerHTML = html;
                var items = temp.querySelectorAll('.product-card-wrapper');
                for (var i = 0; i < items.length; i++) {
                    grid.appendChild(items[i]);
                }
                currentPage = nextPage;
                btn.dataset.page = String(currentPage);
                if (currentPage >= maxPage) {
                    btn.style.display = 'none';
                }
                btn.disabled = false;
            })
            .catch(function () {
                removeSkeletons(grid);
                btn.disabled = false;
            });
    });

    function showSkeletons(container, count) {
        for (var i = 0; i < count; i++) {
            var sk = document.createElement('div');
            sk.className = 'col-12 col-sm-6 col-md-4 skeleton-item';
            sk.innerHTML = '<div class="card h-100"><div class="skeleton skeleton-card"></div><div class="card-body"><div class="skeleton skeleton-text"></div><div class="skeleton skeleton-text"></div></div></div>';
            container.appendChild(sk);
        }
    }

    function removeSkeletons(container) {
        var items = container.querySelectorAll('.skeleton-item');
        for (var i = 0; i < items.length; i++) {
            items[i].remove();
        }
    }
})();
