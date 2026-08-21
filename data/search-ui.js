/* Пошук знімка: оверлей із затемненням по центру + живий пошук по data/products.json.
   На search.html — повні результати з query-параметра q. */
(function () {
    var PROD = 'https://hydrophob.com.ua/';

    function cacheImg(path, size) {
        if (!path) return PROD + 'image/placeholder.png';
        var dot = path.lastIndexOf('.');
        return PROD + 'image/cache/' + encodeURI(path.substring(0, dot)) + '-' + size + 'x' + size + '.' + path.substring(dot + 1);
    }
    function fmt(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' грн';
    }
    function norm(s) {
        return (s || '').toLowerCase().replace(/\s+/g, ' ').trim();
    }
    var productsPromise = null;
    function loadProducts() {
        if (!productsPromise) productsPromise = fetch('data/products.json').then(function (r) { return r.json(); });
        return productsPromise;
    }
    function search(products, q) {
        q = norm(q);
        if (!q) return [];
        var words = q.split(' ');
        return products.filter(function (p) {
            var hay = norm((p.translations['uk-ua'] || {}).name + ' ' + (p.model || ''));
            return words.every(function (w) { return hay.indexOf(w) !== -1; });
        });
    }

    /* --- оверлей --- */
    var openBtn = document.querySelector('.header__search-open');
    if (openBtn) {
        var ov = document.createElement('div');
        ov.className = 'su-overlay';
        ov.innerHTML = '<div class="su-box">' +
            '<button type="button" class="su-close" aria-label="Закрити">' +
            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"/></svg></button>' +
            '<form class="su-form" action="search.html" method="get">' +
            '<input class="su-input" type="text" name="q" placeholder="Пошук по каталогу..." autocomplete="off">' +
            '<button type="submit" class="su-submit" aria-label="Шукати">' +
            '<svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg></button>' +
            '</form>' +
            '<div class="su-results"></div>' +
            '<a class="su-all" href="#" hidden>Всі результати</a>' +
            '</div>';
        document.body.appendChild(ov);

        var input = ov.querySelector('.su-input');
        var results = ov.querySelector('.su-results');
        var allLink = ov.querySelector('.su-all');

        function openOv(e) {
            if (e) e.preventDefault();
            ov.classList.add('is-open');
            document.body.style.overflow = 'hidden';
            setTimeout(function () { input.focus(); }, 50);
        }
        function closeOv() {
            ov.classList.remove('is-open');
            document.body.style.overflow = '';
        }
        openBtn.addEventListener('click', openOv);
        ov.querySelector('.su-close').addEventListener('click', closeOv);
        ov.addEventListener('click', function (e) { if (e.target === ov) closeOv(); });
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') closeOv(); });

        var t = null;
        input.addEventListener('input', function () {
            clearTimeout(t);
            t = setTimeout(function () {
                var q = input.value;
                loadProducts().then(function (products) {
                    var found = search(products, q);
                    if (!norm(q)) { results.innerHTML = ''; allLink.hidden = true; return; }
                    results.innerHTML = found.slice(0, 6).map(function (p) {
                        var name = (p.translations['uk-ua'] || {}).name || '';
                        return '<a class="su-item" href="product-' + p.product_id + '.html">' +
                            '<img src="' + cacheImg(p.image, 200) + '" alt="" loading="lazy">' +
                            '<span class="su-item__name">' + name + '</span>' +
                            '<span class="su-item__price">' + fmt(p.price) + '</span></a>';
                    }).join('') || '<p class="su-empty">Нічого не знайдено</p>';
                    allLink.hidden = found.length <= 6;
                    allLink.textContent = 'Всі результати (' + found.length + ')';
                    allLink.href = 'search.html?q=' + encodeURIComponent(q);
                });
            }, 200);
        });
    }

    /* --- сторінка search.html --- */
    var page = document.getElementById('search-page-results');
    if (page) {
        var params = new URLSearchParams(location.search);
        var q = params.get('q') || '';
        var qInput = document.getElementById('search-page-input');
        if (qInput) qInput.value = q;
        var title = document.getElementById('search-page-count');

        loadProducts().then(function (products) {
            var found = q ? search(products, q) : [];
            if (title) title.textContent = q ? 'Знайдено: ' + found.length + ' за запитом «' + q + '»' : 'Введіть пошуковий запит';
            page.innerHTML = found.map(function (p) {
                var name = (p.translations['uk-ua'] || {}).name || '';
                var href = 'product-' + p.product_id + '.html';
                return '<div class="product__item">' +
                    '<div class="product__item-media"><a class="product__item-image" href="' + href + '">' +
                    '<img src="' + cacheImg(p.image, 450) + '" alt="' + name.replace(/"/g, '&quot;') + '" loading="lazy"></a></div>' +
                    '<div class="product__item-content">' +
                    '<a class="product__item-title" href="' + href + '"><h3>' + name + '</h3></a>' +
                    '<p class="product__item-price">' + fmt(p.price) + '</p>' +
                    '</div></div>';
            }).join('');
        });
    }
})();
