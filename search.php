<?php
require_once __DIR__ . '/helper/general.php';

$pageTitle = 'Пошук';
$pageLangRedirect = 'https://hydrophob.com.ua/index.php?route=product/search';

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
	<main class="main">
    <section class="search-page" id="content">
        <div class="container">
            <h1 class="search-page__title page-name">Пошук</h1>
            <p class="search-page__count" id="search-page-count"></p>
            <form class="su-form" action="search.php" method="get" style="max-width:680px;margin-bottom:32px;">
                <input class="su-input" id="search-page-input" type="text" name="q" placeholder="Пошук по каталогу..." autocomplete="off">
                <button type="submit" class="su-submit" aria-label="Шукати">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>
                </button>
            </form>
            <div class="search-page__grid" id="search-page-results"></div>
        </div>
    </section>
</main>

<?php
require __DIR__ . '/sections/footer.php';
require __DIR__ . '/sections/document-end.php';
