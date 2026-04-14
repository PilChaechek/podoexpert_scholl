<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Главная: вкладки курсов (IBLOCK_ID = 6).
 *
 * Свойства: SHORT_TITLE, PRICE, DURATION, PROFIT, TEACH, TOOLS.
 * Поля: NAME, PREVIEW_TEXT / DETAIL_TEXT, PREVIEW_PICTURE / DETAIL_PICTURE.
 */

CModule::IncludeModule('iblock');

$courseRows = (static function (): array {
    $asString = static function ($val): string {
        if ($val === null || $val === false) {
            return '';
        }
        if (is_string($val)) {
            return $val;
        }
        if (is_array($val)) {
            foreach (['~VALUE', 'VALUE', 'HTML', 'TEXT'] as $k) {
                if (!array_key_exists($k, $val)) {
                    continue;
                }
                $x = $val[$k];
                if (is_array($x)) {
                    if (array_key_exists('TEXT', $x)) {
                        $t = (string) $x['TEXT'];
                        if ($t !== '') {
                            return $t;
                        }
                    }
                    if (array_key_exists('HTML', $x)) {
                        $h = (string) $x['HTML'];
                        if ($h !== '') {
                            return $h;
                        }
                    }
                    $x = $x[0] ?? '';
                }
                $s = (string) $x;
                if ($s !== '') {
                    return $s;
                }
            }

            return '';
        }

        return (string) $val;
    };

    $propScalar = static function (array $f, array $p, string $code) use ($asString): string {
        $v = $f['PROPERTY_' . $code . '_VALUE'] ?? '';
        if (is_array($v)) {
            $v = $v[0] ?? '';
        }
        $s = trim((string) $v);
        if ($s !== '') {
            return $s;
        }

        $pr = $p[$code] ?? $p[strtolower($code)] ?? null;
        if ($pr === null || $pr === []) {
            return '';
        }

        return trim($asString($pr));
    };

    $propRawHtml = static function (array $f, array $p, string $code) use ($asString): string {
        foreach (['~PROPERTY_' . $code . '_VALUE', 'PROPERTY_' . $code . '_VALUE'] as $key) {
            if (!array_key_exists($key, $f)) {
                continue;
            }
            $s = $asString($f[$key]);
            if ($s !== '') {
                return $s;
            }
        }
        $pr = $p[$code] ?? $p[strtolower($code)] ?? null;
        if (is_array($pr) && $pr !== []) {
            foreach (['~VALUE', 'VALUE'] as $k) {
                if (!array_key_exists($k, $pr)) {
                    continue;
                }
                $s = $asString($pr[$k]);
                if ($s !== '') {
                    return $s;
                }
            }
        }

        return '';
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
            'DETAIL_TEXT',
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

        $tabLabel = trim($propScalar($f, $p, 'SHORT_TITLE'));
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

        $intro = $asString($f['~PREVIEW_TEXT'] ?? $f['PREVIEW_TEXT'] ?? null);
        if ($intro === '') {
            $intro = $asString($f['~DETAIL_TEXT'] ?? $f['DETAIL_TEXT'] ?? null);
        }

        $price = $propScalar($f, $p, 'PRICE');
        if ($price !== '') {
            $price = $fmtPrice($price);
        }

        $rows[] = [
            'input_id' => 'course-tab-' . (int) $f['ID'],
            'tab_label' => $tabLabel,
            'title' => $title,
            'price' => $price,
            'duration' => $propScalar($f, $p, 'DURATION'),
            'promo' => $propScalar($f, $p, 'PROFIT'),
            'intro_html' => $intro,
            'teach_html' => $propRawHtml($f, $p, 'TEACH'),
            'tools_html' => $propRawHtml($f, $p, 'TOOLS'),
            'img' => $imgSrc,
        ];
    }

    return $rows;
})();

$hasCourses = !empty($courseRows);
?>

<section id="course-tabs" class="section soft course-tabs-section" aria-label="Курсы">
    <div class="container course-tabs">
        <?php if ($hasCourses): ?>
            <div class="course-tabs__tablist flex flex-wrap gap-3 mb-3" role="tablist" aria-label="Курсы">
                <?php foreach ($courseRows as $i => $c): ?>
                    <div class="course-tabs__tab-pair" role="presentation">
                        <input
                            class="course-tabs__input"
                            type="radio"
                            name="landing-course-tab"
                            id="<?= htmlspecialcharsbx($c['input_id']) ?>"
                            <?= $i === 0 ? ' checked' : '' ?>
                        />
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
                    </div>
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
                                    class="btn btn--v2 course-tabs__cta w-full font-semibold"
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

<?php if ($hasCourses): ?>
<script>
(function () {
    function initCourseTabs() {
        const root = document.querySelector('.course-tabs');
        if (!root) return;
        const inputs = root.querySelectorAll('.course-tabs__input');
        const panels = root.querySelectorAll('.course-panel');

        function sync() {
            let active = 0;
            inputs.forEach((inp, i) => {
                if (inp instanceof HTMLInputElement && inp.checked) {
                    active = i;
                }
            });
            panels.forEach((panel, i) => {
                panel.toggleAttribute('hidden', i !== active);
            });
        }

        inputs.forEach((inp) => inp.addEventListener('change', sync));
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
