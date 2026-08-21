/* Кнопки-перелінковки між сторінками знімка — блок перед футером залежно від типу сторінки. */
(function () {
    var path = location.pathname.split('/').pop() || 'index.php';
    var links;

    if (/^catalog\.php/.test(path)) {
        links = [
            { href: 'delivery.php', text: 'Доставка та оплата' },
            { href: 'about.php', text: 'Про нас' },
            { href: 'contact.php', text: 'Контакти' }
        ];
    } else if (/^product\.php/.test(path)) {
        links = [
            { href: 'catalog.php', text: 'До каталогу' },
            { href: 'delivery.php', text: 'Доставка та оплата' },
            { href: 'contact.php', text: 'Контакти' }
        ];
    } else if (/^(about|privacy|terms|returns|delivery|search)\.php/.test(path)) {
        links = [
            { href: 'catalog.php', text: 'Перейти в каталог' },
            { href: 'index.php', text: 'На головну' },
            { href: 'contact.php', text: 'Контакти' }
        ];
    } else if (/^contact\.php/.test(path)) {
        links = [
            { href: 'catalog.php', text: 'Перейти в каталог' },
            { href: 'delivery.php', text: 'Доставка та оплата' }
        ];
    } else if (/^(account|login|register|edit|address|order|wishlist)/.test(path)) {
        links = [
            { href: 'catalog.php', text: 'До каталогу' },
            { href: 'index.php', text: 'На головну' }
        ];
    } else {
        return; /* головна — там своя навігація секціями */
    }

    var main = document.querySelector('main');
    if (!main) return;

    var html = '<section class="xl-nav"><div class="container"><div class="xl-nav__row">' +
        links.map(function (l) {
            return '<a class="xl-nav__btn" href="' + l.href + '">' + l.text +
                '<svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M7 17 17 7M7 7h10v10"/></svg></a>';
        }).join('') +
        '</div></div></section>';
    main.insertAdjacentHTML('beforeend', html);
})();
