<?php
require_once __DIR__ . '/helper/general.php';

$categoryId = isset($_GET['id']) && $_GET['id'] !== '' ? (int) $_GET['id'] : null;
$page = isset($_GET['page']) ? max(1, (int) $_GET['page']) : 1;
$sort = $_GET['sort'] ?? '';
$order = strtoupper($_GET['order'] ?? 'ASC') === 'DESC' ? 'DESC' : 'ASC';
$perPage = 12;

$categories = hp_categories();
$allProducts = hp_products();

$category = $categoryId !== null ? hp_category_by_id($categoryId) : null;
$categoryName = $category ? hp_t($category, 'name') : 'Каталог';
$categoryDescription = $category ? hp_t($category, 'description') : '';

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
$pageExtraHead = '<link rel="stylesheet" href="data/catalog-ui.css">' . "\n" . '<link rel="stylesheet" href="data/search-ui.css">';
$pageLangRedirect = 'https://hydrophob.com.ua/index.php?route=product/category' . ($category ? '&path=' . $categoryId : '');

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
<main class="main">
        <section class="catalog" id="product-category">
            <div class="container" id="content">
                <div class="catalog__inner">

                    <nav class="catalog__crumbs" aria-label="Хлібні крихти">
                        <a href="index.php" class="catalog__crumbs-link"><i class="fa fa-home"></i></a><span class="catalog__crumbs-sep" aria-hidden="true">/</span><a href="<?= hp_e($pageCanonical) ?>" class="catalog__crumbs-link is-current"><?= hp_e($categoryName) ?></a>
                    </nav>
                    <script type="application/ld+json"><?= hp_breadcrumb_ld([
                        ['name' => 'Головна', 'url' => 'https://hydrophob.com.ua/'],
                        ['name' => $categoryName, 'url' => 'https://hydrophob.com.ua/' . $pageCanonical],
                    ]) ?></script>

                    <section class="catalog__hero">
                        <span class="catalog__hero-overlay" aria-hidden="true"></span>
                        <div class="catalog__hero-content">
                            <h1 class="catalog__title page-title"><p>Каталог</p></h1>
                            <h2 class="catalog__name page-name"><?= hp_e($categoryName) ?></h2>
                        </div>
                    </section>

                    <?php if ($categoryDescription !== ''): ?>
                    <div class="catalog__desc" data-desc>
                        <div class="catalog__desc-scroll" data-desc-scroll><?= $categoryDescription ?></div>
                        <button type="button" class="catalog__desc-more" data-desc-toggle hidden>
                            <span data-desc-text>Показати більше</span>
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                    </div>
                    <?php endif; ?>

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
                        </aside>

                        <div class="catalog__main">
<div class="catalog__content" id="json-catalog" data-category="<?= (int) $canonicalId ?>">
<?php foreach ($slice as $p): echo hp_product_card($p); endforeach; ?>
</div>
<div id="json-pagination">
<?php
$from = $total ? ($page - 1) * $perPage + 1 : 0;
$to = min($page * $perPage, $total);
?>
<?php if ($pages > 1): ?>
<ul class="pagination">
<?php for ($i = 1; $i <= $pages; $i++): ?>
    <?php if ($i === $page): ?>
    <li class="active"><a><?= $i ?></a></li>
    <?php else: ?>
    <li><a href="<?= hp_e($pageCanonical . (strpos($pageCanonical, '?') !== false ? '&' : '?') . 'page=' . $i) ?>"><?= $i ?></a></li>
    <?php endif; ?>
<?php endfor; ?>
</ul>
<?php endif; ?>
<p class="cui-results">Показано з <?= $from ?> по <?= $to ?> із <?= $total ?> (сторінок: <?= $pages ?>)</p>
</div>
</div>
                    </div>
                </div>
            </div>
        </section>
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
$pageExtraFoot = '<script src="data/catalog-ui.js"></script>' . "\n" .
    '<script src="data/search-ui.js"></script>' . "\n" .
    '<script src="data/crosslinks.js"></script>';
require __DIR__ . '/sections/document-end.php';
