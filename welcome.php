<?php
require_once __DIR__ . '/helper/general.php';

$pageTitle = 'Ласкаво просимо';
$pageExtraHead = '<link rel="stylesheet" href="data/search-ui.css">' . "\n" . '<link rel="stylesheet" href="data/home-sections.css">';
$pageLangRedirect = 'https://hydrophob.com.ua/index.php?route=information/contact';

require __DIR__ . '/sections/document-start.php';
require __DIR__ . '/sections/header.php';
require __DIR__ . '/sections/variants.php';
require __DIR__ . '/sections/footer.php';

$pageExtraFoot = '<script src="data/search-ui.js"></script>' . "\n" .
    '<script>try{localStorage.setItem("hydro_visited","1");}catch(e){}</script>' . "\n" .
    '<script src="data/crosslinks.js"></script>';
require __DIR__ . '/sections/document-end.php';
