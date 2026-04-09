<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

CModule::IncludeModule("iblock");

$currentPath = rtrim($APPLICATION->GetCurPage(), '/') . '/';

$infoMenu = [];
$menuRes = CIBlockElement::GetList(
    ['SORT' => 'ASC'],
    ['IBLOCK_ID' => 1, 'ACTIVE' => 'Y'],
    false,
    false,
    ['ID', 'NAME', 'CODE']
);
while ($el = $menuRes->Fetch()) {
    $infoMenu[] = [
        'href'  => '/info/' . $el['CODE'] . '/',
        'title' => $el['NAME'],
    ];
}

$navChain = $APPLICATION->GetNavChain();
?>

<section class="info-section">
    <div class="container">

        <?php if (!empty($navChain)): ?>
        <nav class="info-breadcrumbs" aria-label="Хлебные крошки">
            <?php foreach ($navChain as $i => $crumb): ?>
                <?php if ($i > 0): ?><span class="info-breadcrumbs__sep">/</span><?php endif; ?>
                <?php if (!empty($crumb['LINK'])): ?>
                    <a href="<?= htmlspecialchars($crumb['LINK']) ?>" class="info-breadcrumbs__link"><?= htmlspecialchars($crumb['TITLE']) ?></a>
                <?php else: ?>
                    <span class="info-breadcrumbs__current"><?= htmlspecialchars($crumb['TITLE']) ?></span>
                <?php endif; ?>
            <?php endforeach; ?>
        </nav>
        <?php endif; ?>

        <h1 class="info-section__title"><?= $APPLICATION->ShowTitle(false) ?></h1>

        <div class="info-layout">
            <aside class="info-layout__sidebar">
                <nav aria-label="Навигация по разделам сведений">
                    <ul class="info-nav">
                        <?php foreach ($infoMenu as $item):
                            $isActive = (rtrim($item['href'], '/') . '/') === $currentPath;
                        ?>
                        <li class="info-nav__item">
                            <a href="<?= htmlspecialchars($item['href']) ?>"
                               class="info-nav__link<?= $isActive ? ' info-nav__link--active' : '' ?>">
                                <?= htmlspecialchars($item['title']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </aside>

            <article class="info-layout__content">
                <?= $infoPageContent ?? '' ?>
            </article>
        </div>

    </div>
</section>
