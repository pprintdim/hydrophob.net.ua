<?php
require_once __DIR__ . '/helper/general.php';

$pageTitle = 'Вхід';

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
?>
<main class="main" id="content">
        <section class="account" id="account-login">
            <div class="container">
                <nav class="account__crumbs">
                    <a href="index.php">Головна</a><span>/</span>
                    <span>Вхід в особистий кабінет</span>
                </nav>

                <div class="row">
                    <div id="content" class="col-sm-12">

                        <h1 class="account__title">Вхід або реєстрація</h1>
                        <p class="account__lead">Пароль не потрібен — вкажіть email, і ми надішлемо одноразовий код. Той самий email підійде і новому, і постійному клієнту.</p>

                        <div class="account__otp-steps">
                            <span class="account__otp-step-badge is-active">
                                <span class="account__otp-step-num">1</span> Вкажіть email
                            </span>
                            <span class="account__otp-step-badge is-active">
                                <span class="account__otp-step-num">2</span> Отримайте код листом
                            </span>
                            <span class="account__otp-step-badge is-active">
                                <span class="account__otp-step-num">3</span> Введіть код — і ви в кабінеті
                            </span>
                        </div>

                        <div class="account__panel account__fieldset">
                            <div class="account__form-grid">
                                <div class="account__field account__field-full">
                                    <label for="input-email">E-Mail</label>
                                    <input type="email" placeholder="you@example.com" id="input-email" autocomplete="email" data-code-email />
                                </div>
                            </div>
                            <div class="account__actions" style="justify-content: flex-end;">
                                <button type="button" class="btn-2" data-code-trigger data-code-type="login" data-code-title="Код для входу" data-code-redirect="account.php">Отримати код
                                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg>
                                </button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </section>
	</main>
<?php
require __DIR__ . '/sections/footer.php';
require __DIR__ . '/sections/document-end.php';
