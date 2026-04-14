<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Главная: блок «Программа курсов» (IBLOCK_ID = 9, элемент ID = 24).
 *
 * Поля: NAME (заголовок блока), DETAIL_TEXT (детальное описание),
 *       PREVIEW_PICTURE (картинка анонса).
 * Свойства: TITLE_BANNER (заголовок баннера), SUBTITLE_BANNER (подзаголовок баннера).
 * Курсы: IBLOCK_ID = 6, первые 3 активных элемента.
 */

CModule::IncludeModule('iblock');

$courseProgramData = (static function (): array {
    $res = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => 9, 'ID' => 24, 'ACTIVE' => 'Y'],
        false,
        ['nTopCount' => 1],
        ['ID', 'NAME', '~DETAIL_TEXT', 'DETAIL_TEXT', 'PREVIEW_TEXT', 'PREVIEW_PICTURE',
         'PROPERTY_TITLE_BANNER', 'PROPERTY_SUBTITLE_BANNER']
    );

    $el = $res->GetNext();
    if (!$el) {
        return [];
    }

    $photoSrc = '';
    if (!empty($el['PREVIEW_PICTURE'])) {
        $fileInfo = CFile::GetFileArray($el['PREVIEW_PICTURE']);
        if ($fileInfo) {
            $photoSrc = CFile::GetFileSRC($fileInfo);
        }
    }

    return [
        'title'          => (string)($el['NAME'] ?? ''),
        'description'    => (string)($el['~DETAIL_TEXT'] ?? $el['DETAIL_TEXT'] ?? ''),
        'banner_title'    => (string)($el['PROPERTY_TITLE_BANNER_VALUE'] ?? ''),
        'banner_subtitle' => (string)($el['PROPERTY_SUBTITLE_BANNER_VALUE'] ?? ''),
        'banner_text'     => (string)($el['PREVIEW_TEXT'] ?? ''),
        'photo'          => $photoSrc,
    ];
})();

$courseProgramServices = (static function (): array {
    $gradients = [
        'from-violet-600/80 to-purple-800/80',
        'from-slate-500/80 to-violet-700/80',
        'from-teal-600/80 to-sky-700/80',
    ];

    $rows = [];
    $res  = CIBlockElement::GetList(
        ['SORT' => 'ASC', 'ID' => 'ASC'],
        ['IBLOCK_ID' => 6, 'ACTIVE' => 'Y'],
        false,
        ['nTopCount' => 3],
        ['ID', 'NAME', '~DETAIL_TEXT', 'DETAIL_TEXT', 'DETAIL_PICTURE']
    );

    $i = 0;
    while ($el = $res->GetNext()) {
        $picId = (int)($el['DETAIL_PICTURE'] ?? 0);
        $img   = $picId > 0 ? (string)CFile::GetPath($picId) : '';

        $rows[] = [
            'tab_id'      => 'course-tab-' . (int)$el['ID'],
            'title'       => trim((string)$el['NAME']),
            'description' => trim(strip_tags((string)($el['~DETAIL_TEXT'] ?? $el['DETAIL_TEXT'] ?? ''))),
            'img'         => $img,
            'gradient'    => $gradients[$i] ?? $gradients[0],
        ];
        $i++;
    }

    return $rows;
})();

if (empty($courseProgramData)) {
    return;
}
?>

<section class="section">
    <div class="container">
        <div class="rounded-2xl border border-zinc-300 bg-white p-4 md:p-8">

            <h2 class="font-bold leading-tight text-[clamp(26px,3vw,36px)] mb-4">
                <?= htmlspecialchars($courseProgramData['title']) ?>
            </h2>

            <?php if ($courseProgramData['description'] !== ''): ?>
            <div class="mt-8 space-y-4 content-editor">
                <?= $courseProgramData['description'] ?>
            </div>
            <?php endif; ?>

            <?php if (!empty($courseProgramServices)): ?>
            <div class="mt-8 grid grid-cols-1 gap-2 sm:grid-cols-3 md:gap-4">
                <?php foreach ($courseProgramServices as $idx => $service):
                    $imgAlignClass = ($idx === 0 || $idx === 2) ? ' course-lesson__img-wrap--bottom' : '';
                ?>
                <article class="relative flex overflow-hidden rounded-[16px] border border-zinc-200 bg-white/80 backdrop-blur-md transition-all duration-200 hover:-translate-y-1 hover:shadow-xl cursor-pointer"
                         data-tab-id="<?= htmlspecialchars($service['tab_id']) ?>">
                    <div class="text-left flex-1 p-4 py-3.5 flex flex-col justify-between">
                        <p class="mb-2 text-base font-semibold text-zinc-900 leading-snug"><?= htmlspecialchars($service['title']) ?></p>
                        <p class="text-sm text-zinc-500 leading-snug"><?= htmlspecialchars($service['description']) ?></p>
                    </div>
                    <?php if ($service['img'] !== ''): ?>
                    <div class="course-lesson__img-wrap<?= $imgAlignClass ?> pt-2 pl-2 shrink-0 aspect-square w-[80px] sm:w-[100px] justify-center overflow-hidden bg-gradient-to-br <?= htmlspecialchars($service['gradient']) ?>">
                        <img
                            src="<?= htmlspecialchars($service['img']) ?>"
                            alt="<?= htmlspecialchars($service['title']) ?>"
                            class="block w-[80%] h-[70%] object-contain"
                            loading="lazy"
                        >
                    </div>
                    <?php endif; ?>
                </article>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <?php if ($courseProgramData['photo'] !== '' || $courseProgramData['banner_title'] !== '' || $courseProgramData['banner_subtitle'] !== '' || $courseProgramData['banner_text'] !== ''): ?>
            <div class="mt-8 grid grid-cols-1 gap-6 md:grid-cols-[320px_1fr] items-center">
                <?php if ($courseProgramData['photo'] !== ''): ?>
                <img
                    class="h-72 w-full rounded-xl object-cover"
                    src="<?= htmlspecialchars($courseProgramData['photo']) ?>"
                    alt="<?= htmlspecialchars($courseProgramData['title']) ?>"
                    loading="lazy"
                    width="320"
                    height="288"
                >
                <?php endif; ?>
                <div class="space-y-3">
                    <?php if ($courseProgramData['banner_title'] !== ''): ?>
                    <h3 class="text-2xl font-bold leading-tight text-zinc-900 mb-1">
                        <?= htmlspecialchars($courseProgramData['banner_title']) ?>
                    </h3>
                    <?php endif; ?>
                    <?php if ($courseProgramData['banner_subtitle'] !== ''): ?>
                    <p class="text-sm text-zinc-500 md:text-base">
                        <?= htmlspecialchars($courseProgramData['banner_subtitle']) ?>
                    </p>
                    <?php endif; ?>
                    <?php if ($courseProgramData['banner_text'] !== ''): ?>
                    <p class="text-base leading-relaxed text-zinc-700 md:text-lg">
                        <?= nl2br(htmlspecialchars($courseProgramData['banner_text'])) ?>
                    </p>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>

<script>
document.querySelectorAll('[data-tab-id]').forEach((card) => {
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
