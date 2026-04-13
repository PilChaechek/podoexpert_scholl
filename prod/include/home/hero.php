<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Главная: Hero-блок (IBLOCK_ID = 7, элемент ID = 21).
 *
 * Поля:     NAME, PREVIEW_TEXT, PREVIEW_PICTURE (фото в hero).
 * Свойства: ADVANTAGES (кастомный тип «Таблица»: Заголовок | Описание).
 */

CModule::IncludeModule('iblock');

$heroData = (static function (): array {
    $res = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => 7, 'ID' => 21, 'ACTIVE' => 'Y'],
        false,
        ['nTopCount' => 1],
        ['ID', 'NAME', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'PROPERTY_ADVANTAGES']
    );

    $el = $res->GetNext();
    if (!$el) {
        return [];
    }

    $advantagesRaw = $el['PROPERTY_ADVANTAGES_VALUE'] ?? [];
    $advantages    = is_array($advantagesRaw) ? $advantagesRaw : [];

    $photoSrc = '';
    if (!empty($el['PREVIEW_PICTURE'])) {
        $fileInfo = CFile::GetFileArray($el['PREVIEW_PICTURE']);
        if ($fileInfo) {
            $photoSrc = CFile::GetFileSRC($fileInfo);
        }
    }

    return [
        'title'      => (string)($el['NAME'] ?? ''),
        'description'=> (string)($el['PREVIEW_TEXT'] ?? ''),
        'photo'      => $photoSrc,
        'advantages' => $advantages,
    ];
})();

// Первые 3 курса из инфоблока для блока услуг
$heroServices = (static function (): array {
    $gradients = [
        'from-teal-600/80 to-sky-700/80',
        'from-violet-600/80 to-purple-800/80',
        'from-slate-500/80 to-violet-700/80',
    ];

    $rows = [];
    $res  = CIBlockElement::GetList(
        ['SORT' => 'ASC', 'ID' => 'ASC'],
        ['IBLOCK_ID' => 6, 'ACTIVE' => 'Y'],
        false,
        ['nTopCount' => 3],
        ['ID', 'NAME', 'DETAIL_TEXT', 'DETAIL_PICTURE', 'PROPERTY_AVERAGE_BILL']
    );

    $i = 0;
    while ($el = $res->GetNext()) {
        $picId = (int)($el['DETAIL_PICTURE'] ?? 0);
        $img   = $picId > 0 ? (string)CFile::GetPath($picId) : '';

        $price = trim((string)($el['PROPERTY_AVERAGE_BILL_VALUE'] ?? ''));
        if ($price !== '') {
            $digits = preg_replace('/\D+/', '', $price);
            if ($digits !== '' && ctype_digit($digits)) {
                $price = number_format((int)$digits, 0, ',', ' ');
            }
        }

        $rows[] = [
            'tab_id'      => 'course-tab-' . (int)$el['ID'],
            'title'       => trim((string)$el['NAME']),
            'description' => trim(strip_tags((string)($el['~DETAIL_TEXT'] ?? $el['DETAIL_TEXT'] ?? ''))),
            'price'       => $price,
            'img'         => $img,
            'gradient'    => $gradients[$i] ?? $gradients[0],
        ];
        $i++;
    }

    return $rows;
})();

if (empty($heroData)) {
    return;
}
?>
<section class="section section--p0 hero-grid relative">
    <div class="hero-blur"></div>
    <div class="container">

        <div class="hero-main">
            <?php if ($heroData['photo']): ?>
            <div class="hero-photo">
                <img
                    src="<?= htmlspecialchars($heroData['photo']) ?>"
                    alt="<?= htmlspecialchars($heroData['title']) ?>"
                    class="hero-photo__img"
                    loading="eager"
                >
            </div>
            <?php endif; ?>

            <div class="hero-content">
                <h1 class="m-0 pb-3 text-3xl md:text-5xl font-bold leading-[1.1] tracking-[-0.02em] text-zinc-900">
                    <?= htmlspecialchars($heroData['title']) ?>
                </h1>
                <p class="mt-0 mb-5 leading-relaxed text-zinc-500">
                    <?= htmlspecialchars($heroData['description']) ?>
                </p>

                <?php if (!empty($heroServices)): ?>
                <div class="flex flex-col gap-3">
                    <?php foreach ($heroServices as $service): ?>
                    <div class="relative flex overflow-hidden rounded-[16px] border border-zinc-200 bg-white/80 backdrop-blur-md transition-all duration-200 hover:-translate-y-1 hover:shadow-xl cursor-pointer"
                         data-tab-id="<?= htmlspecialchars($service['tab_id']) ?>">
                        <div class="text-left flex-1 px-4 py-3.5 flex flex-col justify-between">
                            <p class="m-0 text-lg font-semibold text-zinc-900 leading-snug"><?= htmlspecialchars($service['title']) ?></p>
                            <p class="my-2 text-sm text-zinc-500 leading-snug"><?= htmlspecialchars($service['description']) ?></p>
                            <?php if ($service['price'] !== ''): ?>
                            <p class="m-0 mt-2.5 text-[13px] font-bold bg-gradient-to-br from-teal-500 to-violet-600 bg-clip-text text-transparent">Ср. чек <?= htmlspecialchars($service['price']) ?> ₽</p>
                            <?php endif; ?>
                        </div>
                        <?php if ($service['img'] !== ''): ?>
                        <div class="pt-2 pl-2 shrink-0 aspect-square w-[112px] flex items-center justify-center overflow-hidden bg-gradient-to-br <?= htmlspecialchars($service['gradient']) ?>">
                            <img
                                src="<?= htmlspecialchars($service['img']) ?>"
                                alt="<?= htmlspecialchars($service['title']) ?>"
                                class="block w-full h-full object-contain"
                                loading="lazy"
                            >
                        </div>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>

                <a href="#lead-form" class="btn btn--big btn--v2 hero-cta-btn mt-5 w-full">Записаться на обучение</a>
            </div>
        </div>

        <?php if (!empty($heroData['advantages'])): ?>
        <ul class="hero-benefits mt-10 list-none p-0 grid grid-cols-1 md:grid-cols-4 gap-x-6 gap-y-4 text-left">
            <?php foreach ($heroData['advantages'] as $idx => $item): ?>
            <?php
                $num   = str_pad($idx + 1, 2, '0', STR_PAD_LEFT);
                $title = (string)($item[0] ?? '');
                $desc  = (string)($item[1] ?? '');
                if ($title === '') continue;
            ?>
            <li class="flex items-start gap-2">
                <span class="shrink-0 pt-0.5 text-[11px] font-bold tracking-[0.07em] text-zinc-400"><?= $num ?></span>
                <span class="flex flex-col gap-0.5">
                    <span class="text-md font-bold leading-snug text-zinc-900"><?= htmlspecialchars($title) ?></span>
                    <?php if ($desc !== ''): ?>
                    <span class="text-sm leading-snug text-zinc-500"><?= htmlspecialchars($desc) ?></span>
                    <?php endif; ?>
                </span>
            </li>
            <?php endforeach; ?>
        </ul>
        <?php endif; ?>

    </div>
</section>

<script>
document.querySelectorAll('div[data-tab-id]').forEach((card) => {
    card.addEventListener('click', () => {
        const tabId = card.dataset.tabId;
        if (tabId) {
            const input = document.getElementById(tabId);
            if (input instanceof HTMLInputElement) {
                input.checked = true;
                input.dispatchEvent(new Event('change'));
            }
        }
        const target = document.getElementById('course-tabs');
        if (target) {
            target.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
    });
});
</script>
