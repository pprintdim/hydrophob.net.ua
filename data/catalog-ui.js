/* Редизайн каталогу знімка:
   - фільтри (категорія/сортування/ціна/пошук) переїжджають у висувну панель (drawer) по кліку на іконку;
   - сайдбар праворуч: список категорій + популярні товари з data/*.json. */
(function () {
    var PROD = 'https://hydrophob.com.ua/';
    var body = document.querySelector('.catalog__body');
    var filters = document.querySelector('.catalog__filters');
    if (!body || !filters) return;

    document.body.classList.add('cui');

    /* --- drawer із фільтрами --- */
    var overlay = document.createElement('div');
    overlay.className = 'cui-overlay';
    document.body.appendChild(overlay);

    var drawer = document.createElement('aside');
    drawer.className = 'cui-drawer';
    drawer.innerHTML = '<div class="cui-drawer__head"><p>Фільтри</p>' +
        '<button type="button" class="cui-drawer__close" aria-label="Закрити">' +
        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"/></svg>' +
        '</button></div>' +
        '<div class="cui-drawer__body"></div>';
    document.body.appendChild(drawer);

    // переносимо весь блок фільтрів у drawer
    drawer.querySelector('.cui-drawer__body').appendChild(filters);

    // кнопка-іконка фільтрів над сіткою
    var trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'cui-filter-btn';
    trigger.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5h10M11 9h7M11 13h4M3 17l3 3 3-3M6 18V4"/></svg> Фільтри';
    body.parentNode.insertBefore(trigger, body);

    function open() { document.body.classList.add('cui-drawer-open'); }
    function close() { document.body.classList.remove('cui-drawer-open'); }
    trigger.addEventListener('click', open);
    overlay.addEventListener('click', close);
    drawer.querySelector('.cui-drawer__close').addEventListener('click', close);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

    /* --- сайдбар праворуч --- */
    body.classList.add('cui-body');
    var aside = body.querySelector('.catalog__aside');
    if (!aside) {
        aside = document.createElement('aside');
        aside.className = 'catalog__aside';
        body.appendChild(aside);
    }
    aside.classList.add('cui-aside');

    // стара мобільна кнопка розкриття aside більше не потрібна
    var oldToggle = document.querySelector('.catalog__aside-toggle');
    if (oldToggle) oldToggle.remove();

    function cacheImg(path, size) {
        if (!path) return PROD + 'image/placeholder.png';
        var dot = path.lastIndexOf('.');
        return PROD + 'image/cache/' + encodeURI(path.substring(0, dot)) + '-' + size + 'x' + size + '.' + path.substring(dot + 1);
    }
    function fmt(n) {
        return Math.round(n).toString().replace(/\B(?=(\d{3})+(?!\d))/g, ' ') + ' грн';
    }

    Promise.all([
        fetch('data/categories.json').then(function (r) { return r.json(); }),
        fetch('data/products.json').then(function (r) { return r.json(); }),
        fetch('data/home.json').then(function (r) { return r.json(); })
    ]).then(function (res) {
        var cats = res[0], products = res[1], home = res[2];
        var byId = {};
        products.forEach(function (p) { byId[p.product_id] = p; });

        var current = (location.pathname.match(/category-(\d+)\.html/) || [])[1];

        var catList = cats
            .filter(function (c) { return String(c.category_id) !== '33'; })
            .map(function (c) {
                var name = (c.translations['uk-ua'] || {}).name || '';
                var active = String(c.category_id) === current ? ' is-active' : '';
                return '<a class="catalog__filter-item' + active + '" href="category-' + c.category_id + '.html">' + name + '</a>';
            }).join('');

        var popular = (home.popular || []).map(function (id) {
            var p = byId[id];
            if (!p) return '';
            var name = (p.translations['uk-ua'] || {}).name || '';
            return '<a class="cui-pop" href="product-' + p.product_id + '.html">' +
                '<img class="cui-pop__img" src="' + cacheImg(p.image, 200) + '" alt="' + name.replace(/"/g, '&quot;') + '" loading="lazy" onerror="this.src=\'' + PROD + 'image/placeholder.png\'">' +
                '<span class="cui-pop__meta"><span class="cui-pop__name">' + name + '</span>' +
                '<span class="cui-pop__price">' + fmt(p.price) + '</span></span>' +
                '</a>';
        }).join('');

        aside.innerHTML =
            '<div class="catalog__filter"><p class="catalog__filter-title">Категорії</p>' +
            '<div class="catalog__filter-list"><a class="catalog__filter-item' + (current === '33' ? ' is-active' : '') + '" href="category-33.html">Всі товари</a>' + catList + '</div></div>' +
            (popular ? '<div class="catalog__filter"><p class="catalog__filter-title">Популярні товари</p>' +
                '<div class="cui-pop-list">' + popular + '</div></div>' : '');
    }).catch(function () {});
})();
