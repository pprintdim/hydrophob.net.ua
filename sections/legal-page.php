<?php
/* Спільний шаблон юридичних сторінок. Очікує $legalSlug. Дані: helper/legal.php */
require_once __DIR__ . '/../helper/legal.php';

$doc = hp_legal_doc($legalSlug ?? '');
if (!$doc) {
    $doc = hp_legal_docs()[0];
}
$pageTitle = $doc['meta_title'];
$pageDescription = $doc['meta_description'];
$pageExtraHead = '<link rel="stylesheet" href="data/legal.css">';

require __DIR__ . '/document-start.php';
require __DIR__ . '/header.php';
?>
<main class="main">
    <section class="legal">
        <div class="container">
            <nav class="catalog__crumbs" aria-label="Хлібні крихти">
                <a href="index.php" class="catalog__crumbs-link">Головна</a><span class="catalog__crumbs-sep" aria-hidden="true">/</span><a class="catalog__crumbs-link is-current"><?= htmlspecialchars($doc['crumb']) ?></a>
            </nav>
            <div class="legal__grid">
                <aside class="legal__aside">
                    <p class="legal__aside-title">Документи</p>
                    <nav class="legal__menu">
                        <?php foreach (hp_legal_docs() as $item): ?>
                        <a href="<?= $item['slug'] ?>.php" class="legal__menu-link<?= $item['slug'] === $doc['slug'] ? ' is-active' : '' ?>"><?= htmlspecialchars($item['crumb']) ?></a>
                        <?php endforeach; ?>
                        <a href="delivery.php" class="legal__menu-link">Доставка та оплата</a>
                    </nav>
                </aside>
                <article class="legal__body">
                    <p class="legal__updated"><?= htmlspecialchars($doc['updated']) ?></p>
                    <h1 class="legal__title"><?= htmlspecialchars($doc['title']) ?></h1>
                    <p class="legal__lead"><?= htmlspecialchars($doc['lead']) ?></p>
                    <?php foreach ($doc['blocks'] as $block): ?>
                        <?php if ($block['kind'] === 'subtitle'): ?>
                            <h2 class="legal__subtitle"><?= htmlspecialchars($block['text']) ?></h2>
                        <?php elseif ($block['kind'] === 'list'): ?>
                            <ul class="legal__list">
                                <?php foreach ($block['items'] as $it): ?>
                                <li><?= htmlspecialchars($it) ?></li>
                                <?php endforeach; ?>
                            </ul>
                        <?php else: ?>
                            <p class="legal__p"><?= htmlspecialchars($block['text']) ?></p>
                        <?php endif; ?>
                    <?php endforeach; ?>
                </article>
            </div>
        </div>
    </section>
</main>
<?php
require __DIR__ . '/footer.php';
require __DIR__ . '/document-end.php';
