<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Главная: блок «Как проходит обучение» + «Бонусы» (IBLOCK_ID = 10, ID = 25).
 *
 * Поля: NAME (заголовок блока).
 * Свойства:
 *   ITEMS        — Таблица: [0] Заголовок, [1] Описание
 *   BONUSES      — Таблица: [0] Заголовок, [1] Подзаголовок, [2] Описание, [3] Изображение (file)
 *   BONUSES_TITLE    — строка
 *   BONUSES_SUBTITLE — строка
 *   BONUSES_BTN_TEXT — строка
 */

CModule::IncludeModule('iblock');

$courseHowData = (static function (): array {
    $res = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => 10, 'ID' => 25, 'ACTIVE' => 'Y'],
        false,
        ['nTopCount' => 1],
        [
            'ID', 'NAME',
            'PROPERTY_ITEMS',
            'PROPERTY_BONUSES',
            'PROPERTY_BONUSES_TITLE',
            'PROPERTY_BONUSES_SUBTITLE',
            'PROPERTY_BONUSES_BTN_TEXT',
        ]
    );

    $el = $res->GetNext();
    if (!$el) {
        return [];
    }

    $itemsRaw = $el['PROPERTY_ITEMS_VALUE'] ?? [];
    $items = [];
    if (is_array($itemsRaw)) {
        foreach ($itemsRaw as $row) {
            $title = trim((string)($row[0] ?? ''));
            $desc  = trim((string)($row[1] ?? ''));
            if ($title !== '') {
                $items[] = ['title' => $title, 'desc' => $desc];
            }
        }
    }

    $bonusesRaw = $el['PROPERTY_BONUSES_VALUE'] ?? [];
    $bonuses = [];
    if (is_array($bonusesRaw)) {
        foreach ($bonusesRaw as $row) {
            $title    = trim((string)($row[0] ?? ''));
            $subtitle = trim((string)($row[1] ?? ''));
            $desc     = trim((string)($row[2] ?? ''));
            $fileId   = (int)($row[3] ?? 0);
            $imgSrc   = '';
            if ($fileId > 0) {
                $fileInfo = CFile::GetFileArray($fileId);
                if ($fileInfo) {
                    $imgSrc = CFile::GetFileSRC($fileInfo);
                }
            }
            if ($title !== '') {
                $bonuses[] = [
                    'title'    => $title,
                    'subtitle' => $subtitle,
                    'desc'     => $desc,
                    'img'      => $imgSrc,
                ];
            }
        }
    }

    return [
        'title'            => (string)($el['NAME'] ?? ''),
        'items'            => $items,
        'bonuses'          => $bonuses,
        'bonuses_title'    => (string)($el['PROPERTY_BONUSES_TITLE_VALUE'] ?? ''),
        'bonuses_subtitle' => (string)($el['PROPERTY_BONUSES_SUBTITLE_VALUE'] ?? ''),
        'bonuses_btn'      => (string)($el['PROPERTY_BONUSES_BTN_TEXT_VALUE'] ?? ''),
    ];
})();

if (empty($courseHowData)) {
    return;
}
?>

<section class="section course-registration">
    <div class="container">
        <div class="course-registration__shell rounded-[18px] border border-t-0 border-zinc-200 p-4 md:p-8 relative overflow-hidden">
            <div class="absolute inset-x-0 top-0 h-[4px] bg-gradient-to-r from-teal-500 to-purple-700"></div>

            <?php if ($courseHowData['title'] !== ''): ?>
            <header class="mb-[clamp(22px,3vw,32px)]">
                <h2 class="course-registration__title"><?= htmlspecialcharsbx($courseHowData['title']) ?></h2>
            </header>
            <?php endif; ?>

            <?php if (!empty($courseHowData['items'])): ?>
            <div class="grid gap-2 md:grid-cols-3 md:gap-6 md:items-stretch">
                <?php foreach ($courseHowData['items'] as $item): ?>
                <article class="course-registration__module-card flex flex-col gap-3 min-h-full p-[clamp(18px,2.2vw,22px)] rounded-[14px] border border-zinc-200 bg-white shadow-[0_4px_18px_rgba(24,24,27,0.04)]">
                    <h3 class="course-registration__module-title"><?= htmlspecialcharsbx($item['title']) ?></h3>
                    <?php if ($item['desc'] !== ''): ?>
                    <p class="course-registration__module-text"><?= nl2br(htmlspecialcharsbx($item['desc'])) ?></p>
                    <?php endif; ?>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($courseHowData['bonuses']) || $courseHowData['bonuses_title'] !== ''): ?>
            <header class="flex flex-wrap items-center gap-x-4 mt-[clamp(36px,5vw,52px)] mb-[clamp(20px,2.5vw,28px)]">
                <?php if ($courseHowData['bonuses_subtitle'] !== ''): ?>
                <p class="course-registration__eyebrow basis-full"><?= htmlspecialcharsbx($courseHowData['bonuses_subtitle']) ?></p>
                <?php endif; ?>
                <?php if ($courseHowData['bonuses_title'] !== ''): ?>
                <h2 class="course-registration__bonus-title mb-2 basis-full md:flex-1"><?= htmlspecialcharsbx($courseHowData['bonuses_title']) ?></h2>
                <?php endif; ?>
                <?php if ($courseHowData['bonuses_btn'] !== ''): ?>
                <a href="#zapis" class="btn btn--v2 w-full md:w-auto smooth-link"><?= htmlspecialcharsbx($courseHowData['bonuses_btn']) ?></a>
                <?php endif; ?>
            </header>

            <div class="grid gap-4 min-[576px]:grid-cols-2 min-[992px]:grid-cols-4 min-[992px]:gap-[18px]">
                <?php foreach ($courseHowData['bonuses'] as $bonus): ?>
                <article class="course-registration__bonus-card flex flex-col min-h-full rounded-[14px] border border-dashed border-zinc-300/85 overflow-hidden">
                    <?php if ($bonus['img'] !== ''): ?>
                    <div class="aspect-square overflow-hidden rounded-[10px] border border-zinc-200">
                        <img
                            src="<?= htmlspecialcharsbx($bonus['img']) ?>"
                            alt="<?= htmlspecialcharsbx($bonus['title']) ?>"
                            class="w-full h-full object-cover"
                            loading="lazy"
                        >
                    </div>
                    <?php else: ?>
                    <div class="course-registration__bonus-ph aspect-square flex items-center justify-center rounded-[10px] border border-zinc-200" aria-hidden="true">Фото</div>
                    <?php endif; ?>
                    <div class="flex flex-col gap-[10px] flex-1 p-[clamp(14px,2vw,20px)]">
                        <h3 class="course-registration__bonus-card-title"><?= htmlspecialcharsbx($bonus['title']) ?></h3>
                        <?php if ($bonus['subtitle'] !== ''): ?>
                        <p class="course-registration__bonus-card-text"><?= htmlspecialcharsbx($bonus['subtitle']) ?></p>
                        <?php endif; ?>
                        <?php if ($bonus['desc'] !== ''): ?>
                        <p class="course-registration__bonus-price"><?= nl2br(htmlspecialcharsbx($bonus['desc'])) ?></p>
                        <?php endif; ?>
                    </div>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>
