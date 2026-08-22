<?php
require_once __DIR__ . '/helper/general.php';

$pageTitle = 'Реєстрація';

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
<main class="main" id="content">
        <section class="account" id="account-register">
            <div class="container">
                <nav class="account__crumbs">
                    <a href="index.php">Головна</a><span>/</span>
                    <span>Реєстрація</span>
                </nav>

                <div class="row">
                    <div id="content" class="col-sm-12">

                        <h1 class="account__title">Реєстрація нового клієнта</h1>
                        <p class="account__lead">Вже маєте акаунт? <a href="login.php" class="account__link">Увійти в особистий кабінет</a></p>

                        <form action="#" method="post" class="account__form" onsubmit="return false;">
                            <div class="account__panel account__fieldset">
                                <h2 class="account__legend">Ваші дані</h2>
                                <div class="account__form-grid">
                                    <div class="account__field">
                                        <label for="input-firstname">Ім'я</label>
                                        <input type="text" name="firstname" value="" placeholder="Ім'я" id="input-firstname" />
                                    </div>
                                    <div class="account__field">
                                        <label for="input-lastname">Прізвище</label>
                                        <input type="text" name="lastname" value="" placeholder="Прізвище" id="input-lastname" />
                                    </div>
                                    <div class="account__field">
                                        <label for="input-email">E-Mail</label>
                                        <input type="email" name="email" value="" placeholder="E-Mail" id="input-email" />
                                    </div>
                                    <div class="account__field">
                                        <label for="input-telephone">Телефон</label>
                                        <input type="tel" name="telephone" value="" placeholder="Телефон" id="input-telephone" />
                                    </div>
                                </div>
                            </div>

                            <div class="account__panel account__fieldset">
                                <h2 class="account__legend">Пароль</h2>
                                <div class="account__form-grid">
                                    <div class="account__field">
                                        <label for="input-password">Пароль</label>
                                        <input type="password" name="password" value="" placeholder="Пароль" id="input-password" />
                                    </div>
                                    <div class="account__field">
                                        <label for="input-confirm">Підтвердження паролю</label>
                                        <input type="password" name="confirm" value="" placeholder="Підтвердження паролю" id="input-confirm" />
                                    </div>
                                </div>
                            </div>

                            <div class="account__panel account__fieldset">
                                <h2 class="account__legend">Розсилка новин</h2>
                                <div class="account__field">
                                    <label>Підписатися на розсилку?</label>
                                    <div class="account__radio-group">
                                        <label class="account__radio"><input type="radio" name="newsletter" value="1" />Так</label>
                                        <label class="account__radio"><input type="radio" name="newsletter" value="0" checked="checked" />Ні</label>
                                    </div>
                                </div>
                            </div>

                            <div class="account__actions">
                                <label class="account__checkbox">
                                    <input type="checkbox" name="agree" value="1" />
                                    Я прочитав(-ла) і погоджуюсь з <a href="privacy.php" class="account__link">Умовами використання</a>
                                </label>
                                <button type="submit" class="btn-2">Продовжити
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
