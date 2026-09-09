<?php
require_once __DIR__ . '/helper/general.php';

$faqGroups = [
    'Про засоби' => [
        ['q' => 'Що таке нанокераміка HYDROPHOB і як вона працює?', 'a' => 'Це рідкий склад на основі наночастинок, який після нанесення проникає в мікротріщини лакофарбового покриття та формує гладкий захисний шар. Вода збирається в краплі й скочується, бруд і пил не налипають, а кузов зберігає блиск.'],
        ['q' => 'Скільки тримається гідрофобне покриття?', 'a' => 'Залежно від поверхні та умов експлуатації ефект зберігається від 6 до 18 місяців. На склі та лакофарбовому покритті авто — довше, на текстилі — менше.'],
        ['q' => 'Чи можна нанести покриття самостійно?', 'a' => 'Так, більшість засобів розраховані на самостійне нанесення: очистили поверхню, нанесли тонкий шар, розполірували мікрофіброю. Детальна інструкція є на кожній сторінці товару.'],
        ['q' => 'Це безпечно для лаку, пластику та гуми?', 'a' => 'Так. Склади розроблені для автомобільних поверхонь: лак, скло, пластик, гума, шкіра. Головне — дотримуватись інструкції та не наносити на розпечену поверхню.'],
        ['q' => 'Чи є засоби не для авто?', 'a' => 'Є лінійки для взуття та одягу, а також гідрофобізатори для будівельних матеріалів: бетону, каменю, дерева, фасадів.'],
    ],
    'Замовлення і доставка' => [
        ['q' => 'Як оформити замовлення і яка доставка?', 'a' => 'Додайте товар у кошик і оформіть замовлення на сайті, або зателефонуйте — оформимо за вас. Відправляємо Новою Поштою, Укрпоштою чи Meest у день оплати або наступного робочого дня.'],
        ['q' => 'Які способи оплати?', 'a' => 'Післяплата при отриманні на Новій Пошті або передоплата на рахунок (IBAN). Реквізити — на сторінці «Доставка та оплата».'],
        ['q' => 'Чи можливий обмін або повернення?', 'a' => 'Так, протягом 14 днів згідно із законом про захист прав споживачів, якщо засіб не використовувався і упаковка ціла. Деталі — на сторінці «Обмін і повернення».'],
    ],
    'Використання' => [
        ['q' => 'Що робити, якщо ефект гідрофобності зник раніше строку?', 'a' => 'Найчастіше причина — абразивне миття або хімічно агресивні мийні засоби. Повторно нанесіть засіб на очищену поверхню.'],
        ['q' => 'Яка витрата засобу?', 'a' => 'Для легкового авто на кузов достатньо 50 мл кераміки; спрею 500 мл вистачає на 8-10 повних обробок. Точна витрата вказана в характеристиках товару.'],
        ['q' => 'Як зберігати засоби?', 'a' => 'При температурі від +5 до +30 °C, у щільно закритій тарі, подалі від дітей та прямих сонячних променів.'],
    ],
];

$pageTitle = 'Питання та відповіді';
$pageDescription = 'Відповіді на часті запитання про засоби Hydrophob: нанесення, строк дії, доставка, оплата, повернення.';
$pageLangRedirect = 'https://hydrophob.net.ua/index.php?route=common/home';

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
<main class="main" id="content">
    <section class="faq-page">
        <div class="container">
            <nav class="catalog__crumbs catalog__crumbs--light" aria-label="Хлібні крихти">
                <a href="index.php" class="catalog__crumbs-link">Головна</a><span class="catalog__crumbs-sep" aria-hidden="true">/</span><a class="catalog__crumbs-link is-current">Питання та відповіді</a>
            </nav>
            <script type="application/ld+json"><?= hp_breadcrumb_ld([
                ['name' => 'Головна', 'url' => 'index.php'],
                ['name' => 'Питання та відповіді', 'url' => 'faq.php'],
            ]) ?></script>

            <h1 class="page-name">Питання та відповіді</h1>

            <?php $gi = 0; foreach ($faqGroups as $groupName => $items): ?>
            <div class="faq-group">
                <h2 class="faq-group__title"><?= hp_e($groupName) ?></h2>
                <div class="hm-faq">
                    <?php foreach ($items as $i => $item): ?>
                    <div class="hm-faq__item<?= $gi === 0 && $i === 0 ? ' is-open' : '' ?>">
                        <button type="button" class="hm-faq__btn" aria-expanded="<?= $gi === 0 && $i === 0 ? 'true' : 'false' ?>">
                            <span><?= hp_e($item['q']) ?></span>
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><path d="m6 9 6 6 6-6"/></svg>
                        </button>
                        <div class="hm-faq__answer"><p><?= hp_e($item['a']) ?></p></div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
            <?php $gi++; endforeach; ?>

            <div class="dx-cta" style="margin-top:56px;">
                <p class="dx-cta__name">Не знайшли відповідь?</p>
                <p class="dx-cta__text">Поставте питання — відповімо на email протягом робочого дня, а найкорисніші питання додамо на цю сторінку.</p>
                <div class="dx-cta__row">
                    <button type="button" class="btn-2" data-question-open style="display:inline-flex;align-items:center;">Поставити питання
                        <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 0 1 5.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                    </button>
                    <a class="dx-cta__phone" href="tel:+380731081212">+38 073 108 12 12</a>
                </div>
            </div>
        </div>
    </section>
</main>
<?php
require __DIR__ . '/sections/footer.php';
require __DIR__ . '/sections/document-end.php';
