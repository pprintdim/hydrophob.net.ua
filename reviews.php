<?php
require_once __DIR__ . '/helper/general.php';
require_once __DIR__ . '/helper/reviews.php';

$reviews = hp_reviews();
$avg = hp_reviews_avg();

/* пагінація — та сама, що в каталозі */
$perPage = 6;
$total = count($reviews);
$pages = max(1, (int) ceil($total / $perPage));
$page = min(max(1, (int) ($_GET['page'] ?? 1)), $pages);
$pageReviews = array_slice($reviews, ($page - 1) * $perPage, $perPage);

$pageTitle = 'Відгуки покупців';
$pageDescription = 'Відгуки покупців про засоби Hydrophob: нанокераміка для авто, захист скла, шкіри та гуми.';
$pageLangRedirect = 'https://hydrophob.net.ua/index.php?route=common/home';

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
<main class="main main--light" id="content">
    <section class="rv-page">
        <div class="container">
            <nav class="catalog__crumbs" aria-label="Хлібні крихти">
                <a href="index.php" class="catalog__crumbs-link">Головна</a><span class="catalog__crumbs-sep" aria-hidden="true">/</span><a class="catalog__crumbs-link is-current">Відгуки</a>
            </nav>
            <script type="application/ld+json"><?= hp_breadcrumb_ld([
                ['name' => 'Головна', 'url' => 'index.php'],
                ['name' => 'Відгуки', 'url' => 'reviews.php'],
            ]) ?></script>

            <h1 class="page-name" style="color:#161616;">Відгуки</h1>

            <div class="rv-rating">
                <div class="rv-rating__score">
                    <span class="rv-rating__value"><?= number_format($avg, 1) ?></span>
                    <?= hp_stars_html((int) round($avg), 16) ?>
                    <span class="rv-rating__count"><?= count($reviews) ?> відгуків</span>
                </div>
                <button type="button" class="btn-2" data-review-open>Залишити відгук
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 2 4 4-14 14H4v-4L18 2z"/></svg>
                </button>
            </div>

            <div class="rv-list">
                <?php foreach ($pageReviews as $r): ?>
                <?= hp_review_card($r) ?>
                <?php endforeach; ?>
            </div>

            <div id="json-pagination">
<?= hp_pagination($page, $pages, $total, $perPage, fn($p) => 'reviews.php?page=' . $p) ?>
            </div>
        </div>
    </section>
</main>
<?php
require __DIR__ . '/sections/footer.php';
require __DIR__ . '/sections/document-end.php';
