<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

$isHome = ($APPLICATION->GetCurPage() === '/');
$logoSvg = file_get_contents(__DIR__ . '/images/logo.svg');

$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/styles.css');
$APPLICATION->SetAdditionalCSS(SITE_TEMPLATE_PATH . '/scripts/bvi.min.css');
$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH . '/scripts/bvi.min.js');
$APPLICATION->AddHeadScript(SITE_TEMPLATE_PATH . '/scripts/header.js');
?><!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="<?= LANG_CHARSET ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?$APPLICATION->ShowHead()?>
    <title><?$APPLICATION->ShowTitle()?></title>

    <link rel="icon" href="/favicons/favicon.ico" sizes="any" />
    <link rel="icon" href="/favicons/icon.svg" type="image/svg+xml" />
    <link rel="apple-touch-icon" href="/favicons/apple-touch-icon.png" />
    <link rel="manifest" href="/favicons/manifest.webmanifest" />
</head>
<div id="panel"><?$APPLICATION->ShowPanel();?></div>
<body>
<div class="site-wrapper">

<header class="site-header">
    <div class="container">
        <div class="site-header__inner flex justify-between items-center py-4">
            <?php $logoClass = 'site-header__logo'; include __DIR__ . '/include/logo.php'; ?>
            <div class="site-header__right">
                <button class="btn--bvi site-header__btn-bvi bvi-no-styles" aria-label="Версия для слабовидящих" type="button">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><circle cx="6" cy="12" r="3.5"/><circle cx="18" cy="12" r="3.5"/><path d="M2.5 12c1-4 3.5-6 6.5-6"/><path d="M21.5 12c-1-4-3.5-6-6.5-6"/><path d="M9.5 12h5"/></svg>
                </button>
                <a href="/info/" class="btn btn--info site-header__link-info bvi-no-styles">
                    <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><rect width="24" height="24" fill="none"/><g fill="none" stroke="currentColor" stroke-width="1.5"><path d="M3 10c0-3.771 0-5.657 1.172-6.828S7.229 2 11 2h2c3.771 0 5.657 0 6.828 1.172S21 6.229 21 10v4c0 3.771 0 5.657-1.172 6.828S16.771 22 13 22h-2c-3.771 0-5.657 0-6.828-1.172S3 17.771 3 14z"/><path stroke-linecap="round" d="M8 10h8m-8 4h5"/></g></svg>
                    <span class="btn--info__text">Сведения об образовательной организации</span>
                </a>
            </div>
        </div>
    </div>
</header>
<main class="page-content">