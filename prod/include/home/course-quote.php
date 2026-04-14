<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

CModule::IncludeModule('iblock');

if (!function_exists('home_reviews_body_plain')) {
    function home_reviews_body_plain(string $raw, string $type): string
    {
        if ($raw === '') {
            return '';
        }
        if ($type === 'html') {
            $withBreaks = preg_replace('#<br\s*/?>#i', "\n", $raw);
            $charset = defined('LANG_CHARSET') ? LANG_CHARSET : 'UTF-8';
            return trim(html_entity_decode(strip_tags((string) $withBreaks), ENT_QUOTES | ENT_HTML5, $charset));
        }
        return trim($raw);
    }
}

$favoriteReview = null;

$res = CIBlockElement::GetList(
    ['SORT' => 'ASC', 'ID' => 'ASC'],
    ['IBLOCK_ID' => 5, 'ACTIVE' => 'Y', 'PROPERTY_FAVORITE_VALUE' => 'да'],
    false,
    ['nTopCount' => 1],
    ['ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'DETAIL_TEXT_TYPE', 'PREVIEW_TEXT_TYPE', 'PREVIEW_PICTURE', 'PROPERTY_FAVORITE_VALUE', 'PROPERTY_CITY']
);

while ($ob = $res->GetNextElement()) {
    $f = $ob->GetFields();

    $name = trim((string) $f['NAME']);
    if ($name === '') {
        continue;
    }

    $city     = trim((string) ($f['PROPERTY_CITY_VALUE'] ?? ''));
    $quote    = home_reviews_body_plain((string) $f['PREVIEW_TEXT'], (string) $f['PREVIEW_TEXT_TYPE']);
    if ($quote === '') {
        $quote = home_reviews_body_plain((string) $f['DETAIL_TEXT'], (string) $f['DETAIL_TEXT_TYPE']);
    }
    if ($quote === '') {
        continue;
    }

    $imgSrc = '';
    if (!empty($f['PREVIEW_PICTURE'])) {
        $imgSrc = (string) CFile::GetPath($f['PREVIEW_PICTURE']);
    }
    if ($imgSrc === '') {
        continue;
    }

    $favoriteReview = [
        'name'  => $name,
        'city'  => $city,
        'quote' => $quote,
        'img'   => $imgSrc,
    ];
    break;
}

if (!$favoriteReview) {
    return;
}
?>

<section class="section">
    <div class="container">
        <div class="mx-auto max-w-[1000px] rounded-2xl border border-zinc-300 bg-white overflow-hidden">
            <div class="grid grid-cols-1 md:grid-cols-[360px_1fr] items-stretch">
                <img
                    class="w-full h-full object-cover md:rounded-none"
                    src="<?= htmlspecialcharsbx($favoriteReview['img']) ?>"
                    alt="<?= htmlspecialcharsbx($favoriteReview['name']) ?>"
                    loading="lazy"
                    width="360"
                    height="480"
                />
                <div class="space-y-3 p-5 md:p-8">
                    <h3 class="text-2xl font-bold leading-tight text-zinc-900 mb-1 md:text-4xl">
                        <?= htmlspecialcharsbx($favoriteReview['name']) ?>
                    </h3>
                    <?php if ($favoriteReview['city'] !== ''): ?>
                        <p class="text-sm text-zinc-500 md:text-base"><?= htmlspecialcharsbx($favoriteReview['city']) ?></p>
                    <?php endif; ?>
                    <p class="text-base leading-relaxed text-zinc-700 md:text-lg">
                        <?= nl2br(htmlspecialcharsbx($favoriteReview['quote'])) ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
</section>
