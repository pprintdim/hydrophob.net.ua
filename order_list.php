<?php
require_once __DIR__ . '/helper/general.php';

$pageTitle = 'Мої замовлення';
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
                    <span>Історія замовлень</span>
                </nav>

                <div class="row">
                    <div id="content" class="col-sm-12">

                        <h1 class="account__title">Історія замовлень</h1>

                        <div class="account__table-wrap">
                            <table class="account__table">
                                <thead>
                                    <tr>
                                        <th>№ Замовлення</th>
                                        <th>Клієнт</th>
                                        <th>Товари</th>
                                        <th>Статус</th>
                                        <th>Сума</th>
                                        <th>Дата</th>
                                        <th></th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <td>#10045</td>
                                        <td>Іван Петренко</td>
                                        <td>Керамічний гідрофобний спрей 500 мл (2), Мікрофібра для поліровки</td>
                                        <td><span class="account__status account__status--success">Виконано</span></td>
                                        <td>1 750 грн</td>
                                        <td>12.08.2026</td>
                                        <td><a href="order_info.php" title="Переглянути" class="account__table-action">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a></td>
                                    </tr>
                                    <tr>
                                        <td>#10038</td>
                                        <td>Іван Петренко</td>
                                        <td>Набір для нанесення захисного покриття</td>
                                        <td><span class="account__status account__status--pending">В обробці</span></td>
                                        <td>3 500 грн</td>
                                        <td>05.08.2026</td>
                                        <td><a href="order_info.php" title="Переглянути" class="account__table-action">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a></td>
                                    </tr>
                                    <tr>
                                        <td>#10021</td>
                                        <td>Іван Петренко</td>
                                        <td>Мікрофібра суперплюш (3)</td>
                                        <td><span class="account__status account__status--cancelled">Скасовано</span></td>
                                        <td>1 050 грн</td>
                                        <td>28.07.2026</td>
                                        <td><a href="order_info.php" title="Переглянути" class="account__table-action">
                                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8Z"/><circle cx="12" cy="12" r="3"/></svg>
                                        </a></td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                        <div class="account__meta-row">
                            <div></div>
                            <div>Показано 1-3 з 3 (1 сторінка)</div>
                        </div>

                        <div class="account__actions" style="justify-content: flex-end;">
                            <a href="account.php" class="btn-2">До кабінету
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
