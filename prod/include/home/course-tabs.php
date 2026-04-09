<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Главная: вкладки курсов (IBLOCK_ID = 6).
 *
 * Свойства: SHORT_TITLE, PRICE, DURATION, PROFIT, TEACH, TOOLS.
 * Поля элемента: NAME, PREVIEW_TEXT / DETAIL_TEXT (HTML под заголовком), PREVIEW_PICTURE / DETAIL_PICTURE.
 */

CModule::IncludeModule('iblock');

$courseRows = (static function (): array {
    $scalar = static function (?array $pr): string {
        if ($pr === null || $pr === []) {
            return '';
        }
        $v = $pr['VALUE'] ?? '';
        if (is_array($v)) {
            $v = $v[0] ?? '';
        }

        return trim((string) $v);
    };

    $fieldStr = static function (array $f, string $code): string {
        $v = $f['PROPERTY_' . $code . '_VALUE'] ?? '';
        if (is_array($v)) {
            $v = $v[0] ?? '';
        }

        return trim((string) $v);
    };

    $fmtPrice = static function (string $raw): string {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }
        $digits = preg_replace('/\D+/', '', $raw);
        if ($digits === '' || !ctype_digit($digits)) {
            return $raw;
        }

        return number_format((int) $digits, 0, ',', ' ');
    };

    $looksLikeMarkup = static function (string $s): bool {
        return $s !== '' && (bool) preg_match('/<[a-z!][a-z0-9:-]*[\s>\/]/i', $s);
    };

    $normHtml = null;
    $normHtml = static function ($val, array $meta = []) use (&$normHtml, $looksLikeMarkup): string {
        if ($val === null || $val === '' || $val === false) {
            return '';
        }
        if (is_array($val)) {
            if (isset($val['HTML']) && trim((string) $val['HTML']) !== '') {
                return (string) $val['HTML'];
            }
            if (isset($val['TEXT'])) {
                $text = (string) ($val['TEXT'] ?? '');
                if (trim($text) === '') {
                    return '';
                }
                $type = strtoupper((string) ($val['TYPE'] ?? 'HTML'));
                // В админке часто стоит «текст», хотя в поле лежит разметка списков — иначе htmlspecialcharsbx превращает теги в видимый текст.
                if ($type === 'HTML' || $type === '' || $looksLikeMarkup($text)) {
                    return $text;
                }

                return nl2br(htmlspecialcharsbx($text));
            }
            $out = '';
            foreach ($val as $item) {
                if (is_array($item) || (is_string($item) && trim($item) !== '')) {
                    $out .= $normHtml($item, $meta);
                }
            }

            return $out;
        }
        $s = trim((string) $val);
        if ($s === '') {
            return '';
        }
        if (($meta['USER_TYPE'] ?? '') === 'HTML' || $looksLikeMarkup($s)) {
            return $s;
        }
        // Иногда в GetList приходит уже экранированное HTML (&lt;ul&gt;…).
        if (strpos($s, '&lt;') !== false && strpos($s, '<') === false) {
            $charset = defined('LANG_CHARSET') ? LANG_CHARSET : 'UTF-8';
            $s = html_entity_decode($s, ENT_QUOTES | ENT_HTML5, $charset);
            if ($looksLikeMarkup($s)) {
                return $s;
            }
        }

        return nl2br(htmlspecialcharsbx($s));
    };

    $htmlProp = static function (array $f, array $p, string $code) use ($normHtml): string {
        $prop = $p[$code] ?? $p[strtolower($code)] ?? [];
        // Сначала «~» — как в шаблонах Битрикс: неэкранированное значение.
        foreach (['~PROPERTY_' . $code . '_VALUE', 'PROPERTY_' . $code . '_VALUE'] as $key) {
            if (!array_key_exists($key, $f)) {
                continue;
            }
            $frag = $normHtml($f[$key], is_array($prop) ? $prop : []);
            if ($frag !== '') {
                return $frag;
            }
        }
        if (is_array($prop) && $prop !== []) {
            foreach (['~VALUE', 'VALUE'] as $k) {
                if (!array_key_exists($k, $prop)) {
                    continue;
                }
                $frag = $normHtml($prop[$k], $prop);
                if ($frag !== '') {
                    return $frag;
                }
            }
        }

        return '';
    };

    $rows = [];
    $coursesRes = CIBlockElement::GetList(
        ['SORT' => 'ASC', 'ID' => 'ASC'],
        ['IBLOCK_ID' => 6, 'ACTIVE' => 'Y'],
        false,
        false,
        [
            'ID',
            'NAME',
            'PREVIEW_TEXT',
            'PREVIEW_TEXT_TYPE',
            'DETAIL_TEXT',
            'DETAIL_TEXT_TYPE',
            'PREVIEW_PICTURE',
            'DETAIL_PICTURE',
            'PROPERTY_SHORT_TITLE',
            'PROPERTY_PRICE',
            'PROPERTY_DURATION',
            'PROPERTY_PROFIT',
            'PROPERTY_TEACH',
            'PROPERTY_TOOLS',
        ]
    );

    while ($ob = $coursesRes->GetNextElement()) {
        $f = $ob->GetFields();
        $p = $ob->GetProperties();

        $stVal = $f['PROPERTY_SHORT_TITLE_VALUE'] ?? '';
        if (is_array($stVal)) {
            $stVal = $stVal[0] ?? '';
        }
        $tabLabel = trim((string) $stVal);
        if ($tabLabel === '') {
            $tabLabel = $scalar($p['SHORT_TITLE'] ?? $p['short_title'] ?? null);
        }
        if ($tabLabel === '') {
            $tabLabel = trim((string) $f['NAME']);
        }
        $title = trim((string) $f['NAME']);
        if ($tabLabel === '' || $title === '') {
            continue;
        }

        $picId = (int) ($f['PREVIEW_PICTURE'] ?: $f['DETAIL_PICTURE']);
        if ($picId <= 0) {
            continue;
        }
        $imgSrc = (string) CFile::GetPath($picId);
        if ($imgSrc === '') {
            continue;
        }

        $introRaw = trim((string) ($f['~PREVIEW_TEXT'] ?? $f['PREVIEW_TEXT'] ?? ''));
        if ($introRaw === '') {
            $introRaw = trim((string) ($f['~DETAIL_TEXT'] ?? $f['DETAIL_TEXT'] ?? ''));
        }
        $introHtml = $introRaw === '' ? '' : $normHtml($introRaw, []);

        $price = $fieldStr($f, 'PRICE');
        if ($price === '') {
            $price = $scalar($p['PRICE'] ?? null);
        }
        if ($price !== '') {
            $price = $fmtPrice($price);
        }

        $duration = $fieldStr($f, 'DURATION');
        if ($duration === '') {
            $duration = $scalar($p['DURATION'] ?? null);
        }

        $promo = $fieldStr($f, 'PROFIT');
        if ($promo === '') {
            $promo = $scalar($p['PROFIT'] ?? null);
        }

        $rows[] = [
            'input_id' => 'course-tab-' . (int) $f['ID'],
            'tab_label' => $tabLabel,
            'title' => $title,
            'price' => $price,
            'duration' => $duration,
            'promo' => $promo,
            'intro_html' => $introHtml,
            'teach_html' => $htmlProp($f, $p, 'TEACH'),
            'tools_html' => $htmlProp($f, $p, 'TOOLS'),
            'img' => $imgSrc,
        ];
    }

    return $rows;
})();

if (!empty($courseRows)) {
    ?>
    <style>
        <?php foreach ($courseRows as $row) {
            $id = preg_replace('/[^a-zA-Z0-9_-]/', '', $row['input_id']);
            if ($id === '') {
                continue;
            }
            ?>
        .course-tabs:has(#<?= $id ?>:checked) label[for="<?= htmlspecialcharsbx($id) ?>"] {
            border-color: transparent;
            color: #fff;
            background: linear-gradient(135deg, var(--teal-dark) 0%, var(--purple) 100%);
            box-shadow: 0 12px 32px rgba(92, 70, 192, 0.22);
        }
        .course-tabs:has(#<?= $id ?>:checked) label[for="<?= htmlspecialcharsbx($id) ?>"] .course-tab__price-hint {
            color: rgba(255, 255, 255, 0.8);
            background: none;
            -webkit-background-clip: unset;
            -webkit-text-fill-color: rgba(255, 255, 255, 0.8);
            background-clip: unset;
        }
            <?php
        } ?>
    </style>
    <?php
}
?>

<section id="course-tabs" class="section soft course-tabs-section" aria-label="Курсы">
    <div class="container course-tabs">
        <?php if (!empty($courseRows)): ?>
            <div class="course-tabs__inputs" aria-hidden="true">
                <?php foreach ($courseRows as $i => $c): ?>
                    <input
                        class="course-tabs__input"
                        type="radio"
                        name="landing-course-tab"
                        id="<?= htmlspecialcharsbx($c['input_id']) ?>"
                        <?= $i === 0 ? ' checked' : '' ?>
                    />
                <?php endforeach; ?>
            </div>

            <div class="flex flex-wrap gap-3 mb-3" role="tablist" aria-label="Курсы">
                <?php foreach ($courseRows as $c): ?>
                    <label
                        class="course-tab inline-flex flex-col items-center justify-center text-center font-semibold"
                        for="<?= htmlspecialcharsbx($c['input_id']) ?>"
                        role="tab"
                    >
                        <span class="course-tab__name text-base md:text-lg leading-snug"><?= htmlspecialcharsbx($c['tab_label']) ?></span>
                        <?php if ($c['price'] !== ''): ?>
                            <span class="course-tab__price-hint"><?= htmlspecialcharsbx($c['price']) ?> ₽</span>
                        <?php endif; ?>
                    </label>
                <?php endforeach; ?>
            </div>

            <div class="course-tabs__surface">
                <?php foreach ($courseRows as $i => $c): ?>
                    <div class="course-panel flex flex-col" data-panel="<?= (int) $i ?>"<?= $i !== 0 ? ' hidden' : '' ?>>

                        <div class="flex flex-col gap-2 md:flex-row md:items-start md:justify-between md:gap-x-8">
                            <div class="min-w-0">
                                <h2 class="title course-panel__title m-0 font-bold text-zinc-900 leading-tight"><?= htmlspecialcharsbx($c['title']) ?></h2>
                                <?php if ($c['intro_html'] !== ''): ?>
                                    <div class="content-editor course-panel__desc"><?= $c['intro_html'] ?></div>
                                <?php endif; ?>
                            </div>
                            <div class="flex flex-row flex-wrap items-baseline gap-x-2 gap-y-0.5 shrink-0 pt-1 md:flex-col md:items-end md:gap-1.5 md:pt-0">
                                <?php if ($c['price'] !== ''): ?>
                                    <span class="inline-flex items-baseline gap-1.5 shrink-0">
                                        <span class="course-panel__price-value font-extrabold leading-none tracking-[-0.03em]"><?= htmlspecialcharsbx($c['price']) ?></span>
                                        <span class="price-gradient text-sm font-semibold leading-none">рублей</span>
                                    </span>
                                <?php endif; ?>
                                <?php if ($c['duration'] !== ''): ?>
                                    <div class="course-duration-chip"><?= htmlspecialcharsbx($c['duration']) ?></div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <div class="grid gap-5 mt-5 md:grid-cols-[1fr_minmax(300px,0.92fr)] md:items-stretch md:gap-6">
                            <div class="flex flex-col min-w-0">
                                <div class="course-image-card">
                                    <div class="course-image-card__media">
                                        <img
                                            class="block w-full h-full object-cover"
                                            src="<?= htmlspecialcharsbx($c['img']) ?>"
                                            alt="<?= htmlspecialcharsbx($c['title']) ?>"
                                            loading="<?= $i === 0 ? 'eager' : 'lazy' ?>"
                                            width="800"
                                            height="600"
                                            decoding="async"
                                        />
                                    </div>
                                </div>
                                <?php if ($c['promo'] !== ''): ?>
                                    <div class="course-promo-card">
                                        <div class="flex items-start gap-3.5">
                                            <div class="promo-icon shrink-0 w-11 h-11 rounded-full flex items-center justify-center font-extrabold text-base text-white" aria-hidden="true">%</div>
                                            <p class="m-0 text-[15px] leading-[1.5] font-bold text-zinc-900"><?= htmlspecialcharsbx($c['promo']) ?></p>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="course-inventory flex flex-col gap-3 min-h-full">
                                <?php if ($c['teach_html'] !== ''): ?>
                                    <div class="course-inventory-card flex-1">
                                        <p class="course-inventory-card__title">Вы научитесь</p>
                                        <div class="content-editor mt-4"><?= $c['teach_html'] ?></div>
                                    </div>
                                <?php endif; ?>

                                <?php if ($c['tools_html'] !== ''): ?>
                                    <div class="course-inventory-card">
                                        <p class="course-inventory-card__title">Инструменты</p>
                                        <div class="content-editor mt-4"><?= $c['tools_html'] ?></div>
                                    </div>
                                <?php endif; ?>

                                <a
                                    href="#"
                                    class="btn btn--v2 course-tabs__cta mt-2 w-full font-semibold"
                                >Мне это подходит, записываюсь</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php else: ?>
            <p class="text-zinc-500 m-0">Пока нет опубликованных курсов.</p>
        <?php endif; ?>
    </div>
</section>

<?php if (!empty($courseRows)): ?>
<script>
(function () {
    function initCourseTabs() {
        var root = document.querySelector('.course-tabs');
        if (!root) return;
        var inputs = root.querySelectorAll('.course-tabs__input');
        var panels = root.querySelectorAll('.course-panel');

        function sync() {
            var active = 0;
            inputs.forEach(function (inp, i) {
                if (inp.checked) active = i;
            });
            panels.forEach(function (panel, i) {
                if (i !== active) {
                    panel.setAttribute('hidden', '');
                } else {
                    panel.removeAttribute('hidden');
                }
            });
        }

        inputs.forEach(function (inp) {
            inp.addEventListener('change', sync);
        });
        sync();
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', initCourseTabs);
    } else {
        initCourseTabs();
    }
})();
</script>
<?php endif; ?>
