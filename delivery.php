<?php
require_once __DIR__ . '/helper/general.php';

$pageTitle = 'Доставка та оплата';

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
<main class="main" id="content">
        <section class="delivery">
            <div class="container">
                <div class="delivery__inner">
                    <h1 class="delivery__title page-title">Доставка та оплата</h1>
                    <h2 class="delivery__name page-name">
																			Умови доставки та оплати
														</h2>
                    <div class="delivery__content">
						<div class="dlv">
    <p class="dlv__lead">Відправляємо замовлення по всій Україні у день оплати або наступного робочого дня. Термін доставки — 1–3 робочі дні. Вартість розраховується за тарифами перевізника; кілька товарів обʼєднуємо в одну посилку.</p>

    <h3 class="dlv__heading">Способи доставки</h3>
    <div class="dlv__grid">
        <div class="dlv__card">
            <span class="dlv__card-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M21 8a2 2 0 0 0-1-1.73l-7-4a2 2 0 0 0-2 0l-7 4A2 2 0 0 0 3 8v8a2 2 0 0 0 1 1.73l7 4a2 2 0 0 0 2 0l7-4A2 2 0 0 0 21 16z"/><path d="m3.3 7 8.7 5 8.7-5M12 22V12"/></svg></span>
            <p class="dlv__card-name">Нова Пошта</p>
            <p class="dlv__card-text">У відділення, поштомат або курʼєром до дверей. 1–2 робочі дні. Можлива оплата при отриманні (накладний платіж).</p>
        </div>
        <div class="dlv__card">
            <span class="dlv__card-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="m22 7-10 6L2 7"/></svg></span>
            <p class="dlv__card-name">Укрпошта</p>
            <p class="dlv__card-text">У відділення по всій Україні. 2–3 робочі дні. Найвигідніший тариф для невеликих посилок.</p>
        </div>
        <div class="dlv__card">
            <span class="dlv__card-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17h4V5H2v12h3M20 17h2v-6l-3-5h-5v11h3"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg></span>
            <p class="dlv__card-name">Meest</p>
            <p class="dlv__card-text">Відділення та поштомати Meest. 2–3 робочі дні.</p>
        </div>
        <div class="dlv__card">
            <span class="dlv__card-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0z"/><circle cx="12" cy="10" r="3"/></svg></span>
            <p class="dlv__card-name">Самовивіз</p>
            <p class="dlv__card-text">Безкоштовно, за попередньою домовленістю з менеджером. Тел.: <a href="tel:+380731081212">+38 073 108 12 12</a></p>
        </div>
        <div class="dlv__card">
            <span class="dlv__card-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg></span>
            <p class="dlv__card-name">Курʼєрська доставка</p>
            <p class="dlv__card-text">Адресна доставка курʼєром перевізника до дверей у вашому місті.</p>
        </div>
        <div class="dlv__card">
            <span class="dlv__card-icon"><svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="5" cy="12" r="1.6"/><circle cx="12" cy="12" r="1.6"/><circle cx="19" cy="12" r="1.6"/></svg></span>
            <p class="dlv__card-name">Інші перевізники</p>
            <p class="dlv__card-text">Відправимо зручним для вас перевізником — узгодьте з менеджером.</p>
        </div>
    </div>

    <h3 class="dlv__heading">Оплата</h3>
    <div class="dlv__grid dlv__grid--pay">
        <div class="dlv__card">
            <p class="dlv__card-name">Післяплата</p>
            <p class="dlv__card-text">Оплата при отриманні на «Нова Пошта» накладним платежем. Перевізник додатково бере комісію за переказ коштів.</p>
        </div>
        <div class="dlv__card">
            <p class="dlv__card-name">Оплата на рахунок (IBAN)</p>
            <p class="dlv__card-text">Передоплата на рахунок ФОП. Після оплати обовʼязково звʼяжіться з менеджером.</p>
        </div>
    </div>

    <div class="dlv__req">
        <p class="dlv__req-title">Реквізити для оплати</p>
        <dl class="dlv__req-list">
            <div><dt>Установа банку</dt><dd>АТ КБ «ПРИВАТБАНК»</dd></div>
            <div><dt>МФО банку</dt><dd>305299</dd></div>
            <div><dt>IBAN</dt><dd>UA213052990000026002016705486</dd></div>
            <div><dt>РНОКПП отримувача</dt><dd>3251306276</dd></div>
            <div><dt>Валюта</dt><dd>UAH</dd></div>
            <div><dt>Призначення платежу</dt><dd>Поповнення рахунку</dd></div>
        </dl>
        <p class="dlv__req-note">Питання щодо доставки та оплати: <a href="tel:+380731081212">+38 073 108 12 12</a></p>
    </div>
</div>
                    </div>
                </div>
            </div>
        </section>

<section class="dx-steps"><div class="container">
    <h2 class="dx-steps__title page-name">Як відбувається доставка</h2>
    <div class="dx-steps__grid">
        <div class="dx-step"><span class="dx-step__num">1</span>
            <p class="dx-step__name">Замовлення</p>
            <p class="dx-step__text">Оформлюєте замовлення на сайті або телефоном — як зручніше.</p></div>
        <div class="dx-step"><span class="dx-step__num">2</span>
            <p class="dx-step__name">Підтвердження</p>
            <p class="dx-step__text">Менеджер зв'язується, уточнює спосіб доставки та оплати.</p></div>
        <div class="dx-step"><span class="dx-step__num">3</span>
            <p class="dx-step__name">Відправка</p>
            <p class="dx-step__text">Відправляємо в день оплати або наступного робочого дня, надсилаємо ТТН.</p></div>
        <div class="dx-step"><span class="dx-step__num">4</span>
            <p class="dx-step__name">Отримання</p>
            <p class="dx-step__text">Забираєте у відділенні чи від кур'єра. Перевіряйте товар при отриманні.</p></div>
    </div>
    <div class="dx-perks">
        <div class="dx-perk"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12l5 5L20 7"/></svg><p class="dx-perk__text">Відправка в день замовлення</p></div>
        <div class="dx-perk"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg><p class="dx-perk__text">Безкоштовний самовивіз</p></div>
        <div class="dx-perk"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg><p class="dx-perk__text">Оплата при отриманні</p></div>
        <div class="dx-perk"><svg width="28" height="28" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M22 16.92v3a2 2 0 0 1-2.18 2 19.79 19.79 0 0 1-8.63-3.07 19.5 19.5 0 0 1-6-6 19.79 19.79 0 0 1-3.07-8.67A2 2 0 0 1 4.11 2h3a2 2 0 0 1 2 1.72c.127.96.361 1.903.7 2.81a2 2 0 0 1-.45 2.11L8.09 9.91a16 16 0 0 0 6 6l1.27-1.27a2 2 0 0 1 2.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0 1 22 16.92z"/></svg><p class="dx-perk__text">Підтримка: +38 073 108 12 12</p></div>
    </div>
    <div class="dx-cta">
        <p class="dx-cta__name">Залишились питання?</p>
        <p class="dx-cta__text">Зателефонуйте — підкажемо зі способом доставки, оплатою та підбором засобу.</p>
        <div class="dx-cta__row">
            <a class="dx-cta__phone" href="tel:+380731081212">+38 073 108 12 12</a>
            <a class="btn-2" style="display:inline-flex;align-items:center;" href="catalog.php">Перейти в каталог</a>
        </div>
    </div>
</div></section>

</main>
<?php
require __DIR__ . '/sections/footer.php';
require __DIR__ . '/sections/document-end.php';
