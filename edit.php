<?php
require_once __DIR__ . '/helper/general.php';

$pageTitle = 'Редагування профілю';

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
<main class="main" id="content">
        <section class="account" id="account-edit">
            <div class="container">
                <nav class="account__crumbs">
                    <a href="index.php">Головна</a><span>/</span>
                    <a href="account.php">Особистий кабінет</a><span>/</span>
                    <span>Редагування профілю</span>
                </nav>

                <div class="row">
                    <div id="content" class="col-sm-12">

                        <h1 class="account__title">Редагування профілю</h1>

                        <form action="#" method="post" class="account__form" onsubmit="return false;">
                            <div class="account__panel account__fieldset">
                                <h2 class="account__legend">Ваші дані</h2>
                                <div class="account__form-grid">
                                    <div class="account__field">
                                        <label for="input-firstname">Ім'я</label>
                                        <input type="text" name="firstname" value="Іван" placeholder="Ім'я" id="input-firstname" />
                                    </div>
                                    <div class="account__field">
                                        <label for="input-lastname">Прізвище</label>
                                        <input type="text" name="lastname" value="Петренко" placeholder="Прізвище" id="input-lastname" />
                                    </div>
                                    <div class="account__field">
                                        <label for="input-email">E-Mail</label>
                                        <div class="account__field-with-btn">
                                            <input type="email" name="email" value="ivan.petrenko@example.com" placeholder="E-Mail" id="input-email" data-code-email data-code-update="#input-email" data-email-original="ivan.petrenko@example.com" />
                                            <button type="button" class="btn-2" data-code-trigger data-code-type="email" data-code-title="Підтвердження нового email" data-code-inline="1" data-code-update="#input-email" data-email-send-btn disabled>Надіслати код
                                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="account__field">
                                        <label for="input-telephone">Телефон</label>
                                        <input type="tel" name="telephone" value="+380 (67) 123-45-67" placeholder="Телефон" id="input-telephone" />
                                    </div>
                                </div>
                            </div>

                            <div class="account__actions">
                                <a href="account.php" class="account__link">Назад до кабінету</a>
                                <button type="submit" class="btn-2">Зберегти
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M5 12h14"/><path d="m12 5 7 7-7 7"/></svg>
                                </button>
                            </div>
                        </form>

                    </div>
                </div>
            </div>
        </section>
	</main>
<?php
require __DIR__ . '/sections/footer.php';
require __DIR__ . '/sections/document-end.php';
