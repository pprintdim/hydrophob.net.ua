<?php $pageExtraFoot = $pageExtraFoot ?? ''; ?>
<?php require __DIR__ . '/otp-modal.php'; ?>
<?php require __DIR__ . '/feedback-modals.php'; ?>
<script src="https://hydrophob.net.ua/catalog/view/theme/default/js/script.js?v=20260824d" type="text/javascript"></script>
<script src="js/app.js?v=<?= filemtime(__DIR__ . '/../js/app.js') ?>"></script>
<?= $pageExtraFoot ?>
</body></html>
