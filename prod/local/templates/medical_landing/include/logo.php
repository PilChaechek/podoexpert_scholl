<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die(); ?>
<?php $logoClass = isset($logoClass) ? ' ' . $logoClass : ''; ?>
<?php if ($isHome): ?>
    <div class="b-logo-company<?= $logoClass ?>">
        <span class="logo-company__img"><?= $logoSvg ?></span>
        <div class="b-logo-company__text">
            <span class="b-logo-company__title">Школа подологии Эксперт</span>
            <span class="b-logo-company__sub-title">Обучение авторским методам ARKADA</span>
        </div>
    </div>
<?php else: ?>
    <a href="/" class="b-logo-company<?= $logoClass ?>">
        <span class="logo-company__img"><?= $logoSvg ?></span>
        <div class="b-logo-company__text">
            <span class="b-logo-company__title">Школа подологии Эксперт</span>
            <span class="b-logo-company__sub-title">Обучение авторским методам ARKADA</span>
        </div>
    </a>
<?php endif; ?>
<?php unset($logoClass); ?>
