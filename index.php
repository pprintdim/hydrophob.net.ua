<?php
require_once __DIR__ . '/helper/general.php';

$pageTitle = 'Hydrophob';
$pageDescription = 'Hydrophob';
$pageKeywords = 'Hydrophob';
$pageWelcomeRedirect = true;
$pageExtraHead = '<link rel="stylesheet" href="data/home-sections.css">' . "\n" . '<link rel="stylesheet" href="data/search-ui.css">';
$pageLangRedirect = 'https://hydrophob.com.ua/index.php?route=common/home';

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
require __DIR__ . '/sections/hero-slider.php';
require __DIR__ . '/sections/seo-text.php';
?>
    </main>
<?php
require __DIR__ . '/sections/footer.php';
$pageExtraFoot = '<script src="data/home-sections.js"></script>' . "\n" .
    '<script src="data/search-ui.js"></script>' . "\n" .
    '<script src="data/crosslinks.js"></script>' . "\n" .
    '<script src="data/hero-slider.js"></script>';
require __DIR__ . '/sections/document-end.php';
