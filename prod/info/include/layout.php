<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

CModule::IncludeModule("iblock");

$currentPath = rtrim($APPLICATION->GetCurPage(), '/') . '/';

$infoMenu = [
    ['href' => '/info/', 'title' => 'Основные сведения'],
];

$menuRes = CIBlockElement::GetList(
    ['SORT' => 'ASC'],
    ['IBLOCK_ID' => 1, 'ACTIVE' => 'Y', '!CODE' => 'main'],
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

?>

<section class="bg-zinc-50/60 py-8 md:py-10">
    <div class="container">

        <?php $APPLICATION->IncludeComponent('bitrix:breadcrumb', 'info', [
            'PATH'       => false,
            'START_FROM' => 0,
        ], false); ?>

        <h1 class="mb-6 text-3xl font-semibold tracking-tight text-zinc-900 md:text-4xl"><?= $APPLICATION->ShowTitle(false) ?></h1>

        <div class="grid grid-cols-1 gap-5 md:grid-cols-[300px_1fr] md:gap-6">

            <aside class="overflow-hidden rounded-2xl border border-zinc-200 bg-white shadow-sm">
                <nav aria-label="Навигация по разделам сведений">
                    <ul>
                        <?php foreach ($infoMenu as $item):
                            $isActive = (rtrim($item['href'], '/') . '/') === $currentPath;
                            $linkClass = $isActive
                                ? 'block px-4 py-3 text-sm leading-5 transition-colors border-l-2 border-violet-500 bg-violet-50 font-semibold text-zinc-900'
                                : 'block px-4 py-3 text-sm leading-5 transition-colors text-zinc-700 hover:bg-zinc-50 hover:text-zinc-900';
                        ?>
                        <li class="border-b border-zinc-200 last:border-b-0">
                            <a href="<?= htmlspecialchars($item['href']) ?>" class="<?= $linkClass ?>">
                                <?= htmlspecialchars($item['title']) ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                </nav>
            </aside>

            <article class="min-h-[380px] rounded-2xl border border-zinc-200 bg-white px-4 py-6 shadow-sm md:px-8">
                <div class="content-editor">
                    <?= $infoPageContent ?? '' ?>
                </div>
            </article>

        </div>

    </div>
</section>
