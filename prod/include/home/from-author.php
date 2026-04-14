<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Главная: блок «От автора» (IBLOCK_ID = 8).
 *
 * Поля:     NAME (заголовок), PREVIEW_TEXT (текст-абзацы), PREVIEW_PICTURE (фото).
 * Свойства: SUBTITLE (строка — подзаголовок/лид),
 *           ACHIEVEMENTS (Таблица: Заголовок | Описание).
 */

CModule::IncludeModule('iblock');

$fromAuthorData = (static function (): array {
    $res = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => 8, 'ACTIVE' => 'Y'],
        false,
        ['nTopCount' => 1],
        ['ID', 'NAME', 'PREVIEW_TEXT', '~PREVIEW_TEXT', 'PREVIEW_PICTURE',
         'PROPERTY_SUBTITLE', 'PROPERTY_ACHIEVEMENTS']
    );

    $el = $res->GetNext();
    if (!$el) {
        return [];
    }

    $achievementsRaw = $el['PROPERTY_ACHIEVEMENTS_VALUE'] ?? [];
    $achievements    = is_array($achievementsRaw) ? $achievementsRaw : [];

    $photoSrc = '';
    if (!empty($el['PREVIEW_PICTURE'])) {
        $fileInfo = CFile::GetFileArray($el['PREVIEW_PICTURE']);
        if ($fileInfo) {
            $photoSrc = CFile::GetFileSRC($fileInfo);
        }
    }

    return [
        'title'        => (string)($el['NAME'] ?? ''),
        'subtitle'     => (string)($el['PROPERTY_SUBTITLE_VALUE'] ?? ''),
        'text'         => (string)($el['~PREVIEW_TEXT'] ?? ''),
        'photo'        => $photoSrc,
        'achievements' => $achievements,
    ];
})();

if (empty($fromAuthorData)) {
    return;
}
?>
<svg width="0" height="0" aria-hidden="true" style="position:absolute">
    <defs>
        <linearGradient id="diamond-grad" x1="0%" y1="0%" x2="100%" y2="100%">
            <stop offset="0%" stop-color="#14b8ae"/>
            <stop offset="100%" stop-color="#6b22d4"/>
        </linearGradient>
    </defs>
</svg>

<section class="section from-author">
    <div class="container">
        <div class="from-author__layout grid grid-cols-1 gap-2 items-stretch md:grid-cols-[1fr_38%] md:gap-6">

            <div class="from-author__card">
                <h2 class="from-author__title text-[clamp(26px,3vw,36px)]">
                    <?= htmlspecialchars($fromAuthorData['title']) ?>
                </h2>

                <?php if ($fromAuthorData['subtitle'] !== ''): ?>
                <p class="from-author__lead text-base">
                    <?= htmlspecialchars($fromAuthorData['subtitle']) ?>
                </p>
                <?php endif; ?>

                <?php if ($fromAuthorData['text'] !== ''): ?>
                <div class="from-author__text">
                    <?= $fromAuthorData['text'] ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($fromAuthorData['achievements'])): ?>
                <ul class="from-author__stats grid grid-cols-1 gap-2.5 sm:grid-cols-2">
                    <?php foreach ($fromAuthorData['achievements'] as $item):
                        $statTitle = (string)($item[0] ?? '');
                        $statValue = (string)($item[1] ?? '');
                        if ($statTitle === '') continue;
                    ?>
                    <li class="from-author__stat">
                        <svg class="from-author__stat-icon" xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" aria-hidden="true">
                            <path fill="url(#diamond-grad)" d="m19.467 8.694l.246-.566a4.36 4.36 0 0 1 2.22-2.25l.759-.339a.53.53 0 0 0 0-.963l-.717-.319a4.37 4.37 0 0 1-2.251-2.326l-.253-.611a.506.506 0 0 0-.942 0l-.253.61a4.37 4.37 0 0 1-2.25 2.327l-.718.32a.53.53 0 0 0 0 .962l.76.338a4.36 4.36 0 0 1 2.219 2.251l.246.566c.18.414.753.414.934 0M5 6a1 1 0 0 0-.8.4l-3 4a1 1 0 0 0 .057 1.269l9 10a1 1 0 0 0 1.486 0l9-10l-1.486-1.338L11 19.505l-7.707-8.563L5.5 8H14V6z"/>
                        </svg>
                        <span class="from-author__stat-body">
                            <strong class="from-author__stat-title text-sm font-semibold">
                                <?= htmlspecialchars($statTitle) ?>
                            </strong>
                            <?php if ($statValue !== ''): ?>
                            <span class="from-author__stat-value text-xs">
                                <?= $statValue ?>
                            </span>
                            <?php endif; ?>
                        </span>
                    </li>
                    <?php endforeach; ?>
                </ul>
                <?php endif; ?>

                <a class="from-author__cta btn btn--v2 btn--big" href="#lead-form">
                    Хочу обучаться у Юлии Буровой
                </a>
            </div>

            <?php if ($fromAuthorData['photo'] !== ''): ?>
            <div class="from-author__image-wrap min-h-[280px] md:min-h-[520px]">
                <img
                    class="from-author__img"
                    src="<?= htmlspecialchars($fromAuthorData['photo']) ?>"
                    alt="<?= htmlspecialchars($fromAuthorData['title']) ?>"
                    loading="lazy"
                >
            </div>
            <?php endif; ?>

        </div>
    </div>
</section>
