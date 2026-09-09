<!-- Попапи: залишити відгук / поставити питання (спільні для сторінок) -->
<div class="fb-modal" data-fb-modal="review" hidden role="dialog" aria-modal="true" aria-label="Залишити відгук">
    <div class="fb-modal__dialog">
        <button type="button" class="otp-modal__close" data-fb-close aria-label="Закрити">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
        <div data-fb-form>
            <h2 class="otp-modal__title">Залишити відгук</h2>
            <p class="otp-modal__text">Поділіться враженням — це допомагає іншим покупцям</p>
            <div class="fb-stars" data-fb-stars>
                <?php for ($i = 1; $i <= 5; $i++): ?>
                <button type="button" class="fb-star<?= $i <= 5 ? ' is-on' : '' ?>" data-star="<?= $i ?>" aria-label="Оцінка <?= $i ?>">
                    <svg width="26" height="26" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M11.525 2.295a.53.53 0 0 1 .95 0l2.31 4.679a2.123 2.123 0 0 0 1.595 1.16l5.166.756a.53.53 0 0 1 .294.904l-3.736 3.638a2.123 2.123 0 0 0-.611 1.878l.882 5.14a.53.53 0 0 1-.771.56l-4.618-2.428a2.122 2.122 0 0 0-1.973 0L6.396 21.01a.53.53 0 0 1-.77-.56l.881-5.139a2.122 2.122 0 0 0-.611-1.879L2.16 9.795a.53.53 0 0 1 .294-.906l5.165-.755a2.122 2.122 0 0 0 1.597-1.16z"/></svg>
                </button>
                <?php endfor; ?>
            </div>
            <div class="fb-fields">
                <div class="account__field">
                    <label for="fb-review-name">Ваше ім'я</label>
                    <input type="text" id="fb-review-name" placeholder="Ім'я">
                </div>
                <div class="account__field">
                    <label for="fb-review-text">Відгук</label>
                    <textarea id="fb-review-text" rows="4" placeholder="Що сподобалось, який товар використовували"></textarea>
                </div>
            </div>
            <div class="otp-modal__actions">
                <button type="button" class="btn-2" data-fb-submit>Опублікувати відгук
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg>
                </button>
            </div>
        </div>
        <div data-fb-done hidden>
            <h2 class="otp-modal__title">Дякуємо!</h2>
            <p class="otp-modal__text">Відгук з'явиться на сторінці після модерації.</p>
        </div>
    </div>
</div>

<div class="fb-modal" data-fb-modal="question" hidden role="dialog" aria-modal="true" aria-label="Поставити питання">
    <div class="fb-modal__dialog">
        <button type="button" class="otp-modal__close" data-fb-close aria-label="Закрити">
            <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="m6 6 12 12M18 6 6 18"/></svg>
        </button>
        <div data-fb-form>
            <h2 class="otp-modal__title">Поставити питання</h2>
            <p class="otp-modal__text">Відповімо на email протягом робочого дня</p>
            <div class="fb-fields">
                <div class="account__field">
                    <label for="fb-q-name">Ваше ім'я</label>
                    <input type="text" id="fb-q-name" placeholder="Ім'я">
                </div>
                <div class="account__field">
                    <label for="fb-q-email">E-Mail</label>
                    <input type="email" id="fb-q-email" placeholder="you@example.com">
                </div>
                <div class="account__field">
                    <label for="fb-q-text">Питання</label>
                    <textarea id="fb-q-text" rows="4" placeholder="Ваше питання"></textarea>
                </div>
            </div>
            <div class="otp-modal__actions">
                <button type="button" class="btn-2" data-fb-submit>Надіслати питання
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M22 2 11 13"/><path d="M22 2 15 22l-4-9-9-4 20-7z"/></svg>
                </button>
            </div>
        </div>
        <div data-fb-done hidden>
            <h2 class="otp-modal__title">Питання надіслано</h2>
            <p class="otp-modal__text">Дякуємо! Відповідь прийде на вказаний email.</p>
        </div>
    </div>
</div>
