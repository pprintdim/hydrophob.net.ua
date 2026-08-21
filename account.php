<?php
require_once __DIR__ . '/helper/general.php';

$pageTitle = 'Особистий кабінет';
$pageExtraHead = '<link rel="stylesheet" href="data/search-ui.css">';

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
<main class="main" id="content">
        <section class="account" id="account-account">
            <div class="container">
                <nav class="account__crumbs">
                    <a href="index.php">Головна</a><span>/</span>
                    <span>Особистий кабінет</span>
                </nav>

                <div class="account__notice account__notice--ok">
                    <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 6 9 17l-5-5"/></svg>
                    <span>Ви увійшли як Іван Петренко</span>
                </div>

                <div class="row">
                    <div id="content" class="col-sm-12">

                        <h1 class="account__title">Особистий кабінет</h1>
                        <p class="account__lead">Вітаємо, Іван Петренко! Тут можна редагувати особисті дані, керувати адресами доставки, переглядати замовлення та список обраного.</p>

                        <div class="account__group">
                            <h2 class="account__group-title">Мій кабінет</h2>
                            <div class="account__menu">
                                <div class="account__menu-item">
                                    <a href="edit.php">Редагування профілю
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m18 2 4 4-14 14H4v-4L18 2z"/></svg>
                                    </a>
                                </div>
                                <div class="account__menu-item">
                                    <a href="address_list.php">Адресна книга
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20 10c0 6-8 12-8 12s-8-6-8-12a8 8 0 0 1 16 0Z"/><circle cx="12" cy="10" r="3"/></svg>
                                    </a>
                                </div>
                                <div class="account__menu-item">
                                    <a href="wishlist.php">Список обраного
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 5.6a5.2 5.2 0 0 0-7.4 0L12 7l-1.4-1.4a5.2 5.2 0 1 0-7.4 7.4L12 21l8.8-8.8a5.2 5.2 0 0 0 0-7.4z"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="account__group">
                            <h2 class="account__group-title">Мої замовлення</h2>
                            <div class="account__menu">
                                <div class="account__menu-item">
                                    <a href="order_list.php">Історія замовлень
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h11"/></svg>
                                    </a>
                                </div>
                            </div>
                        </div>

                        <div class="account__actions" style="justify-content: flex-end;">
                            <a href="index.php" class="account__link">Вийти з кабінету</a>
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
