<?php
require_once __DIR__ . '/../helper/general.php';

$pageTitle = $pageTitle ?? 'Hydrophob';
$pageDescription = $pageDescription ?? '';
$pageKeywords = $pageKeywords ?? '';
$pageCanonical = $pageCanonical ?? '';
$pageExtraHead = $pageExtraHead ?? '';
$pageWelcomeRedirect = $pageWelcomeRedirect ?? false;
$pageBodyClass = $pageBodyClass ?? 'body';
$pageIsWelcome = $pageIsWelcome ?? false;
?>
<!DOCTYPE html>
<!--[if IE]><![endif]-->
<!--[if IE 8 ]><html dir="ltr" lang="uk" class="ie8"><![endif]-->
<!--[if IE 9 ]><html dir="ltr" lang="uk" class="ie9"><![endif]-->
<!--[if (gt IE 9)|!(IE)]><!-->
<html dir="ltr" lang="uk">
<!--<![endif]-->
<head>
<?php if ($pageWelcomeRedirect): ?>
<script>try{if(!localStorage.getItem("hydro_visited")){location.replace("welcome.php");}}catch(e){}</script>
<?php endif; ?>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1">
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<title><?= hp_e($pageTitle) ?></title>
<base href="<?= hp_e($_SERVER['SCRIPT_NAME'] !== '' ? basename($_SERVER['SCRIPT_NAME']) : 'index.php') ?>" />
<?php if ($pageDescription !== ''): ?>
<meta name="description" content="<?= hp_e($pageDescription) ?>" />
<?php endif; ?>
<?php if ($pageKeywords !== ''): ?>
<meta name="keywords" content="<?= hp_e($pageKeywords) ?>" />
<?php endif; ?>
<script src="https://hydrophob.net.ua/catalog/view/javascript/jquery/jquery-3.7.1.min.js" type="text/javascript"></script>
<script src="https://hydrophob.net.ua/catalog/view/javascript/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:ital,wght@0,100..900;1,100..900&display=swap" rel="stylesheet">
<link href="css/style.css?v=<?= filemtime(__DIR__ . '/../css/style.css') ?>" rel="stylesheet">
<?php if ($pageIsWelcome): ?>
<link rel="stylesheet" href="css/welcome.css?v=<?= filemtime(__DIR__ . '/../css/welcome.css') ?>">
<?php else: ?>
<link rel="stylesheet" href="css/pages.css?v=<?= filemtime(__DIR__ . '/../css/pages.css') ?>">
<link rel="stylesheet" href="css/media.css?v=<?= filemtime(__DIR__ . '/../css/media.css') ?>">
<?php endif; ?>
<script src="https://hydrophob.net.ua/catalog/view/javascript/common.js" type="text/javascript"></script>
<script src="https://hydrophob.net.ua/catalog/view/theme/default/js/checkout.js" type="text/javascript"></script>
<?php if ($pageCanonical !== ''): ?>
<link href="<?= hp_e($pageCanonical) ?>" rel="canonical" />
<?php endif; ?>
<link rel="icon" type="image/svg+xml" href="favicon.svg">
<?= $pageExtraHead ?>
</head>
<body class="<?= hp_e($pageBodyClass) ?>">
	<div id="alert-container">
  </div>
