/* Каталог: тулбар (Фільтри + сортування + перемикач сітки), сайдбар праворуч,
   виїзна панель фільтрів. Дані рендерить сервер — тут лише перебудова DOM. */
(function () {
    var body = document.querySelector('.catalog__body');
    if (!body) return;

    var T = window.hpCatalogText || {};
    var sortSelect = document.getElementById('input-sort');
    var aside = body.querySelector('.catalog__aside');

    /* ---------- тулбар ---------- */
    var toolbar = document.createElement('div');
    toolbar.className = 'cui-toolbar';

    var trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'cui-filter-btn';
    trigger.innerHTML = '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11 5h10M11 9h7M11 13h4M3 17l3 3 3-3M6 18V4"/></svg> ' + (T.filters || 'Фільтри');
    toolbar.appendChild(trigger);

    if (sortSelect) {
        sortSelect.classList.add('cui-sort');
        toolbar.appendChild(sortSelect);
    }

    var gridBox = document.createElement('div');
    gridBox.className = 'cui-grid-toggle';
    gridBox.innerHTML =
        '<button type="button" class="cui-grid-btn" data-cols="4" aria-label="' + (T.grid4 || 'По чотири') + '" title="' + (T.grid4 || 'По чотири') + '">' +
        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/><rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="7" height="7"/></svg></button>' +
        '<button type="button" class="cui-grid-btn" data-cols="2" aria-label="' + (T.grid2 || 'По два') + '" title="' + (T.grid2 || 'По два') + '">' +
        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><rect x="3" y="3" width="8" height="18"/><rect x="13" y="3" width="8" height="18"/></svg></button>';
    toolbar.appendChild(gridBox);

    /* стару форму пошуку/ціни в тулбарі верстки немає — ховаємо */
    var oldFilters = document.querySelector('.catalog__filters');
    if (oldFilters) oldFilters.style.display = 'none';

    body.parentNode.insertBefore(toolbar, body);

    /* ---------- перемикач сітки 4/2 ---------- */
    var grid = document.querySelector('.catalog__content');
    var saved = null;
    try { saved = localStorage.getItem('hydro_grid'); } catch (e) {}

    function applyCols(cols) {
        if (!grid) return;
        grid.classList.toggle('cui-cols-2', cols === '2');
        gridBox.querySelectorAll('.cui-grid-btn').forEach(function (b) {
            b.classList.toggle('is-active', b.getAttribute('data-cols') === cols);
        });
        try { localStorage.setItem('hydro_grid', cols); } catch (e) {}
    }
    gridBox.querySelectorAll('.cui-grid-btn').forEach(function (b) {
        b.addEventListener('click', function () { applyCols(this.getAttribute('data-cols')); });
    });
    applyCols(saved === '2' ? '2' : '4');

    /* ---------- виїзна панель фільтрів ---------- */
    var overlay = document.createElement('div');
    overlay.className = 'cui-overlay';
    document.body.appendChild(overlay);

    var drawer = document.createElement('aside');
    drawer.className = 'cui-drawer';
    drawer.innerHTML = '<div class="cui-drawer__head"><p>' + (T.filters || 'Фільтри') + '</p>' +
        '<button type="button" class="cui-drawer__close" aria-label="' + (T.close || 'Закрити') + '">' +
        '<svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"/></svg>' +
        '</button></div>' +
        '<div class="cui-drawer__body"></div>';
    document.body.appendChild(drawer);

    var drawerBody = drawer.querySelector('.cui-drawer__body');

    function open() { document.body.classList.add('cui-drawer-open'); }
    function close() { document.body.classList.remove('cui-drawer-open'); }

    /* у виїзну панель — ТІЛЬКИ форма фільтрів (.hpf-form).
       Розділи каталогу, популярні товари й опис категорії лишаються в правому сайдбарі. */
    if (aside) {
        var filterForm = aside.querySelector('.hpf-form');
        if (filterForm) drawerBody.appendChild(filterForm);
    }

    /* кнопка «Скинути» в підвалі панелі — поруч із «Закрити» */
    var drawerFoot = document.createElement('div');
    drawerFoot.className = 'cui-drawer__foot';
    var resetLink = drawerBody.querySelector('.hpf__reset');
    if (resetLink) {
        drawerFoot.appendChild(resetLink);
        var actions = drawerBody.querySelector('.hpf__actions');
        if (actions && !actions.children.length) actions.remove();
    }
    var closeBtn = document.createElement('button');
    closeBtn.type = 'button';
    closeBtn.className = 'btn-2 cui-drawer__apply';
    closeBtn.textContent = T.close || 'Закрити';
    drawerFoot.appendChild(closeBtn);
    drawer.appendChild(drawerFoot);
    closeBtn.addEventListener('click', function () { close(); });

    trigger.addEventListener('click', open);
    overlay.addEventListener('click', close);
    drawer.querySelector('.cui-drawer__close').addEventListener('click', close);
    document.addEventListener('keydown', function (e) { if (e.key === 'Escape') close(); });

    /* ---------- сайдбар праворуч ---------- */
    body.classList.add('cui-body');
    if (aside) aside.classList.add('cui-aside');

    var oldToggle = document.querySelector('.catalog__aside-toggle');
    if (oldToggle) oldToggle.remove();

    /* блоки сайдбара — акордеони, початково згорнуті */
    if (aside) {
        aside.querySelectorAll('.catalog__filter').forEach(function (box) {
            var title = box.querySelector('.catalog__filter-title');
            if (!title) return;

            // популярні товари й опис категорії лишаються розгорнутими завжди
            // (в описі вже є власна кнопка «Показати більше»)
            if (box.querySelector('.cui-pop-list') || box.hasAttribute('data-cat-desc')) return;

            box.classList.add('cui-collapsible');

            title.setAttribute('role', 'button');
            title.setAttribute('tabindex', '0');
            title.setAttribute('aria-expanded', 'false');

            function toggle() {
                var open = box.classList.toggle('is-open');
                title.setAttribute('aria-expanded', open ? 'true' : 'false');
            }

            title.addEventListener('click', toggle);
            title.addEventListener('keydown', function (e) {
                if (e.key === 'Enter' || e.key === ' ') { e.preventDefault(); toggle(); }
            });
        });
    }
})();



/* акордеони груп фільтрів */
(function () {
    document.addEventListener('click', function (e) {
        var btn = e.target.closest('[data-hpf-toggle]');
        if (!btn) return;
        var box = btn.closest('[data-hpf]');
        if (!box) return;
        var open = box.classList.toggle('is-open');
        btn.setAttribute('aria-expanded', open ? 'true' : 'false');
    });
})();

/* Автосабміт фільтрів. Замість GET-хвоста ?filter[]=1&filter[]=2 будуємо
   ЧПУ: /katalog/<слаг-фільтра>/... — слаги віддає сервер у data-slug. */
(function () {
    document.addEventListener('change', function (e) {
        var cb = e.target.closest('input[data-autosubmit]');
        if (!cb) return;

        var form = cb.closest('form');
        if (!form) return;

        var base = form.getAttribute('data-seo-base') || form.getAttribute('action');
        var checked = [].slice.call(form.querySelectorAll('input[name="filter[]"]:checked'));
        var slugs = checked.map(function (el) { return el.getAttribute('data-slug'); });

        // усі обрані фільтри мають слаги — тоді ЧПУ, інакше звичайний сабміт
        if (base && slugs.length && slugs.every(Boolean)) {
            window.location.href = base.replace(/\/$/, '') + '/' + slugs.join('/');
            return;
        }

        if (base && !checked.length) {
            window.location.href = base;
            return;
        }

        form.submit();
    });
})();
