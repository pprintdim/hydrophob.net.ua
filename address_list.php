<?php
require_once __DIR__ . '/helper/general.php';

$pageTitle = 'Мої адреси';

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
<main class="main" id="content">
        <section class="account" id="account-address">
            <div class="container">
                <nav class="account__crumbs">
                    <a href="index.php">Головна</a><span>/</span>
                    <a href="account.php">Особистий кабінет</a><span>/</span>
                    <span>Адресна книга</span>
                </nav>

                <div class="row">
                    <div id="content" class="col-sm-12">

                        <h1 class="account__title">Адресна книга</h1>

                        <div class="account__addresses">
                            <div class="account__address">
                                <span class="account__address-badge">За замовчуванням</span>
                                <div class="account__address-text">
                                    Іван Петренко<br>
                                    вул. Хрещатик, 22, кв. 15<br>
                                    Київ, 01001<br>
                                    Україна<br>
                                    +380 (67) 123-45-67
                                </div>
                                <div class="account__address-actions">
                                    <a href="address_form.php" class="account__link">Редагувати</a>
                                    <a href="#" onclick="return confirm('Видалити цю адресу?')" class="account__link">Видалити</a>
                                </div>
                            </div>
                            <div class="account__address">
                                <div class="account__address-text">
                                    Іван Петренко<br>
                                    просп. Перемоги, 45, офіс 8<br>
                                    Київ, 03057<br>
                                    Україна<br>
                                    +380 (67) 123-45-67
                                </div>
                                <div class="account__address-actions">
                                    <a href="address_form.php" class="account__link">Редагувати</a>
                                    <a href="#" onclick="return confirm('Видалити цю адресу?')" class="account__link">Видалити</a>
                                </div>
                            </div>
                        </div>

                        <div class="account__actions">
                            <a href="account.php" class="account__link">Назад до кабінету</a>
                            <a href="address_form.php" class="btn-2">Нова адреса
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                            </a>
                        </div>

                    </div>
                </div>
            </div>
        </section>
	</main>
<?php
require __DIR__ . '/sections/footer.php';
require __DIR__ . '/sections/document-end.php';
