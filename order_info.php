<?php
require_once __DIR__ . '/helper/general.php';

$pageTitle = 'Замовлення';
$pageExtraHead = '<link rel="stylesheet" href="data/search-ui.css">';

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
<main class="main" id="content">
        <section class="account" id="account-order">
            <div class="container">
                <nav class="account__crumbs">
                    <a href="index.php">Головна</a><span>/</span>
                    <a href="account.php">Особистий кабінет</a><span>/</span>
                    <a href="order_list.php">Історія замовлень</a><span>/</span>
                    <span>Замовлення #10045</span>
                </nav>

                <div class="row">
                    <div id="content" class="col-sm-12">

                        <h1 class="account__title">Замовлення #10045</h1>

                        <div class="account__panel">
                            <h2 class="account__panel-title">Деталі замовлення</h2>
                            <div class="account__form-grid">
                                <div>
                                    <p><b>№ Замовлення:</b> #10045</p>
                                    <p><b>Дата замовлення:</b> 12.08.2026</p>
                                    <p><b>Статус:</b> <span class="account__status account__status--success">Виконано</span></p>
                                </div>
                                <div>
                                    <p><b>Спосіб оплати:</b> Оплата карткою онлайн</p>
                                    <p><b>Спосіб доставки:</b> Нова пошта (відділення)</p>
                                </div>
                            </div>
                        </div>

                        <div class="account__panel">
                            <div class="account__form-grid">
                                <div>
                                    <h2 class="account__panel-title">Адреса оплати</h2>
                                    <p class="account__address-text">
                                        Іван Петренко<br>
                                        вул. Хрещатик, 22, кв. 15<br>
                                        Київ, 01001<br>
                                        Україна<br>
                                        +380 (67) 123-45-67
                                    </p>
                                </div>
                                <div>
                                    <h2 class="account__panel-title">Адреса доставки</h2>
                                    <p class="account__address-text">
                                        Іван Петренко<br>
                                        Відділення №24, вул. Богатирська, 6<br>
                                        Київ, 04212<br>
                                        Україна<br>
                                        +380 (67) 123-45-67
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="account__table-wrap">
                            <table class="account__table">
                                <thead>
                                    <tr>
                                        <th>Назва</th>
                                        <th>Модель</th>
                                        <th>Кількість</th>
                                        <th>Ціна</th>
                                        <th>Сума</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>Керамічний гідрофобний детейл спрей-герметик 500 мл</td>
                                        <td>Спрей 500 ml</td>
                                        <td>2</td>
                                        <td>350 грн</td>
                                        <td>700 грн</td>
                                        <td style="white-space: nowrap;">
                                            <a href="#" title="Повторити замовлення" class="account__table-action">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                            </a>
                                            <a href="#" title="Оформити повернення" class="account__table-action">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"/><path d="M3 13a9 9 0 1 0 3-6.7L3 9"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Мікрофібра для поліровки</td>
                                        <td>Мікрофібра</td>
                                        <td>2</td>
                                        <td>250 грн</td>
                                        <td>500 грн</td>
                                        <td style="white-space: nowrap;">
                                            <a href="#" title="Повторити замовлення" class="account__table-action">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                            </a>
                                            <a href="#" title="Оформити повернення" class="account__table-action">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"/><path d="M3 13a9 9 0 1 0 3-6.7L3 9"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td>Знежирювач. Швидковисихаючий активатор нанокераміки</td>
                                        <td>Активатор</td>
                                        <td>1</td>
                                        <td>300 грн</td>
                                        <td>300 грн</td>
                                        <td style="white-space: nowrap;">
                                            <a href="#" title="Повторити замовлення" class="account__table-action">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="21" r="1"/><circle cx="20" cy="21" r="1"/><path d="M1 1h4l2.68 13.39a2 2 0 0 0 2 1.61h9.72a2 2 0 0 0 2-1.61L23 6H6"/></svg>
                                            </a>
                                            <a href="#" title="Оформити повернення" class="account__table-action">
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M3 7v6h6"/><path d="M3 13a9 9 0 1 0 3-6.7L3 9"/></svg>
                                            </a>
                                        </td>
                                    </tr>
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td colspan="3" style="border: none;"></td>
                                        <td><b>Товари:</b></td>
                                        <td>1 500 грн</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="border: none;"></td>
                                        <td><b>Доставка:</b></td>
                                        <td>250 грн</td>
                                        <td></td>
                                    </tr>
                                    <tr>
                                        <td colspan="3" style="border: none;"></td>
                                        <td><b>Разом:</b></td>
                                        <td><b>1 750 грн</b></td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>

                        <h2 class="account__group-title" style="margin-top: 40px;">Історія статусів</h2>
                        <div class="account__table-wrap">
                            <table class="account__table">
                                <thead>
                                    <tr>
                                        <th>Дата</th>
                                        <th>Статус</th>
                                        <th>Коментар</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>12.08.2026 10:12</td>
                                        <td><span class="account__status account__status--pending">Очікує обробки</span></td>
                                        <td>Замовлення прийнято в обробку</td>
                                    </tr>
                                    <tr>
                                        <td>12.08.2026 14:30</td>
                                        <td><span class="account__status account__status--pending">Підтверджено</span></td>
                                        <td>Оплату отримано, замовлення передано на комплектацію</td>
                                    </tr>
                                    <tr>
                                        <td>13.08.2026 09:05</td>
                                        <td><span class="account__status account__status--success">Виконано</span></td>
                                        <td>Видано у відділенні Нової пошти №24</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <div class="account__actions" style="justify-content: flex-end;">
                            <a href="order_list.php" class="btn-2">До списку замовлень
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </section>
	</main>
<?php
require __DIR__ . '/sections/footer.php';
$pageExtraFoot = '<script src="data/search-ui.js"></script>\n<script src="data/crosslinks.js"></script>';
require __DIR__ . '/sections/document-end.php';
