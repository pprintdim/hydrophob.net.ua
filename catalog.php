<?php
require_once __DIR__ . '/helper/general.php';

$categoryId = isset($_GET['id']) && $_GET['id'] !== '' ? (int) $_GET['id'] : null;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$sort = $_GET['sort'] ?? '';
$order = strtoupper($_GET['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
$perPage = 14; /* 14 карток + банер (2 слоти) = 4 повних ряди; без банера було б 12 */

$categories = hp_categories();
$allProducts = hp_products();

$category = $categoryId !== null ? hp_category_by_id($categoryId) : null;
$categoryName = $category ? hp_t($category, 'name') : 'Каталог';
$categoryDescription = $category ? hp_html($category, 'description') : '';

/* коренева "Каталог" (33, порожньо або невідома категорія) — всі товари */
if ($categoryId === null || $categoryId === 33 || !$category) {
    $products = $allProducts;
} else {
    $products = array_values(array_filter($allProducts, function (array $p) use ($categoryId) {
        return in_array($categoryId, array_map('intval', $p['categories'] ?? []), true);
    }));
    if (!$products) {
        $products = $allProducts; // категорія без прив'язаних товарів — як у знімку
    }
}

if ($sort === 'price') {
    usort($products, function ($a, $b) use ($order) {
        $cmp = ((float) $a['price']) <=> ((float) $b['price']);
        return $order === 'DESC' ? -$cmp : $cmp;
    });
} elseif ($sort === 'name') {
    usort($products, function ($a, $b) use ($order) {
        $cmp = strnatcasecmp(hp_t($a, 'name'), hp_t($b, 'name'));
        return $order === 'DESC' ? -$cmp : $cmp;
    });
}

$total = count($products);
$pages = max(1, (int) ceil($total / $perPage));
$page = min($page, $pages);
$slice = array_slice($products, ($page - 1) * $perPage, $perPage);

$canonicalId = $categoryId ?? 33;
$pageCanonical = 'catalog.php' . ($canonicalId !== null ? '?id=' . $canonicalId : '');

$pageTitle = $categoryName;
$pageLangRedirect = 'https://hydrophob.net.ua/index.php?route=product/category' . ($category ? '&path=' . $categoryId : '');

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
<main class="main">
        <section class="catalog" id="product-category">
            <div class="container" id="content">
                <div class="catalog__inner">

                    <nav class="catalog__crumbs" aria-label="Хлібні крихти">
                        <a href="index.php" class="catalog__crumbs-link">Головна</a><span class="catalog__crumbs-sep" aria-hidden="true">/</span><a href="<?= hp_e($pageCanonical) ?>" class="catalog__crumbs-link is-current"><?= hp_e($categoryName) ?></a>
                    </nav>
                    <script type="application/ld+json"><?= hp_breadcrumb_ld([
                        ['name' => 'Головна', 'url' => 'https://hydrophob.net.ua/'],
                        ['name' => $categoryName, 'url' => 'https://hydrophob.net.ua/' . $pageCanonical],
                    ]) ?></script>

                    <h1 class="catalog__title page-title">Каталог</h1>
                    <h2 class="catalog__name page-name"><?= hp_e($categoryName) ?></h2>

                    <div class="catalog__filters">
                        <select name="categories" id="category-select">
                            <option value="" selected hidden>Категорії</option>
                            <option value="catalog.php">Каталог</option>
                            <?php foreach ($categories as $c): ?>
                            <option value="catalog.php?id=<?= (int) $c['category_id'] ?>"><?= hp_e(hp_t($c, 'name')) ?></option>
                            <?php endforeach; ?>
                        </select>
                        <select id="input-sort" class="form-control" onchange="location = this.value;">
                            <option value="<?= hp_e($pageCanonical) ?>" <?= $sort === '' ? 'selected' : '' ?>>За замовчуванням</option>
                            <option value="<?= hp_e($pageCanonical) ?>&amp;sort=name&amp;order=ASC" <?= $sort === 'name' && $order === 'ASC' ? 'selected' : '' ?>>Назва (А - Я)</option>
                            <option value="<?= hp_e($pageCanonical) ?>&amp;sort=name&amp;order=DESC" <?= $sort === 'name' && $order === 'DESC' ? 'selected' : '' ?>>Назва (Я - А)</option>
                            <option value="<?= hp_e($pageCanonical) ?>&amp;sort=price&amp;order=ASC" <?= $sort === 'price' && $order === 'ASC' ? 'selected' : '' ?>>Ціна (низька &gt; висока)</option>
                            <option value="<?= hp_e($pageCanonical) ?>&amp;sort=price&amp;order=DESC" <?= $sort === 'price' && $order === 'DESC' ? 'selected' : '' ?>>Ціна (висока &gt; низька)</option>
                        </select>
                        <form class="catalog__search" method="get" action="search.php">
                            <input class="catalog__search-input" type="text" name="q" placeholder="Пошук по каталогу" aria-label="Пошук по каталогу" autocomplete="off">
                            <button type="submit" class="catalog__search-btn" aria-label="Шукати">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                            </button>
                        </form>
                    </div>

                    <button type="button" class="catalog__aside-toggle" data-aside-toggle>
                        Фільтри
                        <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                    </button>

                    <div class="catalog__body">
                        <aside class="catalog__aside" data-aside>
                            <div class="catalog__filter">
                                <p class="catalog__filter-title">Розділи каталогу</p>
                                <div class="catalog__filter-list">
                                    <?php foreach ($categories as $c): if ((int) $c['category_id'] === 33) continue; ?>
                                    <a href="catalog.php?id=<?= (int) $c['category_id'] ?>" class="catalog__filter-item<?= $categoryId === (int) $c['category_id'] ? ' is-active' : '' ?>"><?= hp_e(hp_t($c, 'name')) ?></a>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                            <?php if ($categoryDescription !== ''): ?>
                            <div class="catalog__filter cat-desc-side" data-cat-desc>
                                <p class="catalog__filter-title">Про категорію</p>
                                <div class="cat-desc-side__text"><?= $categoryDescription ?></div>
                                <button type="button" class="cat-desc-side__more" data-cat-desc-open>Розгорнути
                                    <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="M15 3h6v6"/><path d="M10 14 21 3"/><path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/></svg>
                                </button>
                            </div>
                            <?php endif; ?>
                        </aside>

                        <div class="catalog__main">
<div class="catalog__content" id="json-catalog" data-category="<?= (int) $canonicalId ?>">
<?php
$catalogBanners = [
    [
        'title' => 'Набір для нанесення покриття',
        'text' => 'Все для першого нанесення: засіб, аплікатор і мікрофібра — вигідніше, ніж окремо.',
        'img' => 'https://hydrophob.net.ua/image/catalog/hydrophob/p2523929316.webp',
        'href' => 'product.php?id=70',
        'btn' => 'До набору',
    ],
    [
        'title' => 'Оптовим клієнтам',
        'text' => 'Автомийки, детейлінг-студії, магазини — окремі ціни від об\'єму.',
        'img' => 'https://hydrophob.net.ua/image/catalog/hydrophob/p2524531368.webp',
        'href' => 'contact.php',
        'btn' => 'Зв\'язатися',
    ],
];
$bi = 0;
foreach ($slice as $i => $p):
    echo hp_product_card($p);
    if (($i + 1) % 8 === 0 && isset($catalogBanners[$bi])):
        $b = $catalogBanners[$bi++];
?>
<a class="catalog__banner" href="<?= hp_e($b['href']) ?>">
    <div class="catalog__banner-media"><img src="<?= hp_e($b['img']) ?>" alt="" loading="lazy"></div>
    <div class="catalog__banner-body">
        <p class="catalog__banner-title"><?= hp_e($b['title']) ?></p>
        <p class="catalog__banner-text"><?= hp_e($b['text']) ?></p>
        <span class="catalog__banner-btn btn-2"><?= hp_e($b['btn']) ?>
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
        </span>
    </div>
</a>
<?php endif; endforeach; ?>
</div>
<div id="json-pagination">
<?= hp_pagination($page, $pages, $total, $perPage, fn($p) => $pageCanonical . (strpos($pageCanonical, '?') !== false ? '&' : '?') . 'page=' . $p) ?>
</div>
</div>
                    </div>
                </div>
            </div>
        </section>

<?php $galleryItems = hp_home()['gallery'] ?? []; ?>
<?php if ($galleryItems): ?>
<section class="hm-sec" id="gallery" style="background:#fff;">
    <div class="container">
        <h2 class="hm-sec__title page-name" style="color:#161616;">Hydrophob у дії</h2>
        <div class="hm-gallery">
            <?php foreach ($galleryItems as $gi => $g): ?>
            <button type="button" class="hm-gallery__item<?= $gi % 4 === 0 ? ' hm-gallery__item--wide' : '' ?>" data-lightbox-type="<?= hp_e($g['type']) ?>" data-lightbox-src="<?= hp_e($g['src']) ?>"<?= !empty($g['poster']) ? ' data-lightbox-poster="' . hp_e($g['poster']) . '"' : '' ?>>
                <?php if ($g['type'] === 'video'): ?>
                <img src="<?= hp_e($g['poster'] ?? '') ?>" alt="Hydrophob у дії" loading="lazy">
                <span class="hm-gallery__play" aria-hidden="true"><svg width="20" height="20" viewBox="0 0 24 24" fill="currentColor"><path d="M8 5v14l11-7z"/></svg></span>
                <?php else: ?>
                <img src="<?= hp_e($g['src']) ?>" alt="Hydrophob у дії" loading="lazy">
                <?php endif; ?>
            </button>
            <?php endforeach; ?>
        </div>
    </div>
</section>
<?php endif; ?>

<?php $reviewsTeaserLight = true; require __DIR__ . '/sections/reviews-teaser.php'; ?>

<?php
/* Топ продажів — товари з бейджем "top" (остання секція) */
$topIds = [];
foreach (hp_badges_map() as $pid => $badges) {
    if (in_array('top', (array) $badges, true)) { $topIds[] = (int) $pid; }
}
$topProducts = array_values(array_filter(hp_products(), function ($p) use ($topIds) {
    return in_array((int) $p['product_id'], $topIds, true);
}));
$topProducts = array_slice($topProducts, 0, 8);
?>
<?php if ($topProducts): ?>
<section class="hm-sec" id="top-sales" style="background:#fff; padding-bottom:72px;">
    <div class="container">
        <h2 class="hm-sec__title page-name" style="color:#161616;">Топ продажів</h2>
        <div class="hm-slider-wrap">
            <button type="button" class="hm-arrow hm-arrow--prev" aria-label="Назад"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m15 18-6-6 6-6"/></svg></button>
            <div class="hm-slider">
                <?php foreach ($topProducts as $tp): ?>
                <?= hp_product_card($tp, 'hm-slide') ?>
                <?php endforeach; ?>
            </div>
            <button type="button" class="hm-arrow hm-arrow--next" aria-label="Вперед"><svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m9 18 6-6-6-6"/></svg></button>
        </div>
    </div>
</section>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var wrap = document.querySelector('#top-sales .hm-slider-wrap');
    if (!wrap) return;
    var slider = wrap.querySelector('.hm-slider');
    function step() {
        var el = slider.querySelector('.hm-slide');
        return el ? el.getBoundingClientRect().width + 24 : 300;
    }
    wrap.querySelector('.hm-arrow--prev').addEventListener('click', function () { slider.scrollBy({ left: -step(), behavior: 'smooth' }); });
    wrap.querySelector('.hm-arrow--next').addEventListener('click', function () { slider.scrollBy({ left: step(), behavior: 'smooth' }); });
});
</script>
<?php endif; ?>

<?php if ($categoryDescription !== ''): ?>
<div class="fb-modal" data-fb-modal="catdesc" hidden role="dialog" aria-modal="true" aria-label="Опис категорії">
    <div class="fb-modal__dialog fb-modal__dialog--wide">
        <button type="button" class="otp-modal__close" data-fb-close aria-label="Закрити">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
        <h2 class="otp-modal__title" style="text-align:left;"><?= hp_e($categoryName) ?></h2>
        <div class="cat-desc-full"><?= $categoryDescription ?></div>
    </div>
</div>
<?php endif; ?>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var categorySelect = document.getElementById('category-select');
    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            if (this.value) {
                window.location.href = this.value;
            }
        });
    }

    document.querySelectorAll('[data-desc]').forEach(function (box) {
        var scroll = box.querySelector('[data-desc-scroll]');
        var toggle = box.querySelector('[data-desc-toggle]');
        var label = toggle ? toggle.querySelector('[data-desc-text]') : null;
        if (!scroll || !toggle) {
            return;
        }
        if (scroll.scrollHeight > scroll.clientHeight + 4) {
            toggle.hidden = false;
        }
        toggle.addEventListener('click', function () {
            var expanded = box.classList.toggle('is-expanded');
            if (label) {
                label.textContent = expanded ? 'Згорнути' : 'Показати більше';
            }
        });
    });

    var asideToggle = document.querySelector('[data-aside-toggle]');
    var aside = document.querySelector('[data-aside]');
    if (asideToggle && aside) {
        asideToggle.addEventListener('click', function () {
            aside.classList.toggle('is-open');
        });
    }
});
</script>
<?php
require __DIR__ . '/sections/footer.php';
require __DIR__ . '/sections/document-end.php';
