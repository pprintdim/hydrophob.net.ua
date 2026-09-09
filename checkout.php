<?php
require_once __DIR__ . '/helper/general.php';

$pageTitle = 'Оформлення замовлення';
$pageLangRedirect = 'https://hydrophob.net.ua/index.php?route=checkout/checkout';

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
<main class="main main--light" id="content">
    <section class="checkout">
        <div class="container">
            <nav class="catalog__crumbs" aria-label="Хлібні крихти">
                <a href="index.php" class="catalog__crumbs-link">Головна</a><span class="catalog__crumbs-sep" aria-hidden="true">/</span><a class="catalog__crumbs-link is-current">Оформлення замовлення</a>
            </nav>
            <h1 class="checkout__title">Оформлення замовлення</h1>

            <div class="checkout__inner">
                <div class="checkout__content">
                    <form class="checkout__box" id="checkout-form" action="#" method="post" onsubmit="return false;">

                        <div class="checkout__block">
                            <h2 class="checkout__block-name">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/></svg>
                                Контактні дані
                            </h2>
                            <div class="checkout__block-inputs">
                                <div class="account__field">
                                    <label for="co-firstname">Ім'я</label>
                                    <input type="text" id="co-firstname" name="firstname" placeholder="Ім'я" autocomplete="given-name">
                                </div>
                                <div class="account__field">
                                    <label for="co-lastname">Прізвище</label>
                                    <input type="text" id="co-lastname" name="lastname" placeholder="Прізвище" autocomplete="family-name">
                                </div>
                                <div class="account__field">
                                    <label for="co-phone">Телефон</label>
                                    <input type="tel" id="co-phone" name="telephone" placeholder="+380" autocomplete="tel">
                                </div>
                                <div class="account__field">
                                    <label for="co-email">E-Mail</label>
                                    <input type="email" id="co-email" name="email" placeholder="you@example.com" autocomplete="email">
                                </div>
                            </div>
                        </div>

                        <div class="checkout__block">
                            <h2 class="checkout__block-name">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M10 17h4V5H2v12h3M20 17h2v-6l-3-5h-5v11h3"/><circle cx="7.5" cy="17.5" r="2.5"/><circle cx="17.5" cy="17.5" r="2.5"/></svg>
                                Доставка
                            </h2>
                            <div class="checkout__block-delivery">
                                <div class="account__field" style="grid-column: span 3;">
                                    <label for="co-carrier">Перевізник</label>
                                    <select id="co-carrier" name="carrier">
                                        <option value="np" selected>Нова Пошта — відділення / поштомат</option>
                                        <option value="np-courier">Нова Пошта — кур'єр</option>
                                        <option value="ukr">Укрпошта</option>
                                        <option value="meest">Meest</option>
                                        <option value="pickup">Самовивіз (Київ)</option>
                                    </select>
                                </div>
                                <div class="account__field" style="grid-column: span 2;">
                                    <label for="co-city">Місто</label>
                                    <input type="text" id="co-city" name="city" placeholder="Київ">
                                </div>
                                <div class="account__field">
                                    <label for="co-branch">Відділення</label>
                                    <input type="text" id="co-branch" name="branch" placeholder="№ відділення">
                                </div>
                            </div>
                        </div>

                        <div class="checkout__block">
                            <h2 class="checkout__block-name">
                                <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><rect x="2" y="5" width="20" height="14" rx="2"/><path d="M2 10h20"/></svg>
                                Оплата
                            </h2>
                            <div class="checkout__block-payments">
                                <label class="account__radio"><input type="radio" name="payment" value="cod" checked> Післяплата (оплата при отриманні)</label>
                                <label class="account__radio"><input type="radio" name="payment" value="iban"> Оплата на рахунок (IBAN)</label>
                            </div>
                        </div>

                        <div class="checkout__block">
                            <div class="account__field" style="max-width: 828px;">
                                <label for="co-comment">Коментар до замовлення</label>
                                <textarea id="co-comment" name="comment" rows="3" placeholder="Необов'язково"></textarea>
                            </div>
                        </div>

                        <div class="account__actions" style="max-width: 828px;">
                            <a href="catalog.php" class="account__link">Продовжити покупки</a>
                            <button type="button" class="btn-2" id="checkout-confirm">Підтвердити замовлення
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            </button>
                        </div>
                    </form>

                    <div class="checkout__thanks" id="checkout-thanks" hidden>
                        <div class="account__notice account__notice--ok" style="margin-top: 32px;">
                            <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                            <span>Дякуємо! Замовлення прийнято — менеджер зв'яжеться з вами найближчим часом для підтвердження.</span>
                        </div>
                        <div class="account__actions" style="justify-content: flex-start;">
                            <a href="catalog.php" class="btn-2" style="display:inline-flex;align-items:center;">До каталогу</a>
                        </div>
                    </div>
                </div>

                <aside class="checkout__busket busket-static">
                    <div class="busket__top">
                        <p class="busket__title">Ваше замовлення</p>
                    </div>
                    <ul class="busket__inner" id="checkout-busket"></ul>
                </aside>
            </div>
        </div>
    </section>
</main>
<script>
document.addEventListener('DOMContentLoaded', function () {
    var btn = document.getElementById('checkout-confirm');
    if (!btn) return;
    btn.addEventListener('click', function () {
        /* косметика знімка: ховаємо форму, показуємо подяку, чистимо кошик */
        document.getElementById('checkout-form').hidden = true;
        document.getElementById('checkout-thanks').hidden = false;
        try { localStorage.setItem('hydro_cart_items', '[]'); } catch (e) {}
        var badge = document.querySelector('.header__busket .hc-badge');
        if (badge) badge.remove();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    });
});
</script>
<?php
require __DIR__ . '/sections/footer.php';
require __DIR__ . '/sections/document-end.php';
