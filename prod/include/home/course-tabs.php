<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Главная: вкладки курсов (IBLOCK_ID = 6).
 *
 * Свойства: SHORT_TITLE, PRICE, DURATION, TEACH, TOOLS, INCLUDED (таблица: файл | название).
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
            'PROPERTY_TEACH',
            'PROPERTY_TOOLS',
            'PROPERTY_INCLUDED',
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

        // INCLUDED — таблица: колонка 0 = файл (ID), колонка 1 = название
        $includedRaw = $f['PROPERTY_INCLUDED_VALUE'] ?? [];
        $included    = is_array($includedRaw) ? $includedRaw : [];

        $includedItems = [];
        foreach ($included as $row) {
            if (!is_array($row)) {
                continue;
            }
            $fileId = (int) ($row[0] ?? 0);
            $name   = trim((string) ($row[1] ?? ''));
            if ($name === '') {
                continue;
            }
            $imgUrl = '';
            if ($fileId > 0) {
                $fileInfo = CFile::GetFileArray($fileId);
                if ($fileInfo) {
                    $imgUrl = (string) CFile::GetFileSRC($fileInfo);
                }
            }
            $includedItems[] = ['img' => $imgUrl, 'name' => $name];
        }

        $rows[] = [
            'input_id'      => 'course-tab-' . (int) $f['ID'],
            'tab_label'     => $tabLabel,
            'title'         => $title,
            'price'         => $price,
            'duration'      => $propScalar($f, $p, 'DURATION'),
            'intro_html'    => $intro,
            'teach_html'    => $propRawHtml($f, $p, 'TEACH'),
            'tools_html'    => $propRawHtml($f, $p, 'TOOLS'),
            'included'      => $includedItems,
            'img'           => $imgSrc,
        ];
    }

    return $rows;
})();

$hasCourses = !empty($courseRows);
?>

<section id="course-tabs" class="section soft course-tabs-section" aria-label="Курсы">
    <div class="container course-tabs">
        <?php if ($hasCourses): ?>
            <div class="course-tabs__tablist flex flex-wrap gap-1 mb-3 md:gap-2" role="tablist" aria-label="Курсы">
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
                    <div class="course-panel flex flex-col gap-5" data-panel="<?= (int) $i ?>"<?= $i !== 0 ? ' hidden' : '' ?>>

                        <!-- Строка 1: картинка слева, заголовок + описание + цена + кнопка справа -->
                        <div class="grid gap-5 md:grid-cols-[minmax(280px,2fr)_3fr] md:items-start md:gap-10">
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

                            <div class="flex flex-col gap-4">
                                <h2 class="title course-panel__title font-bold text-zinc-900 leading-tight"><?= htmlspecialcharsbx($c['title']) ?></h2>
                                <?php if ($c['duration'] !== ''): ?>
                                    <div class="course-duration-chip w-fit"><?= htmlspecialcharsbx($c['duration']) ?></div>
                                <?php endif; ?>
                                <?php if ($c['intro_html'] !== ''): ?>
                                    <div class="content-editor course-panel__desc my-4"><?= $c['intro_html'] ?></div>
                                <?php endif; ?>
                                <div class="course-panel__price-cta">
                                    <?php if ($c['price'] !== ''): ?>
                                        <span class="inline-flex items-baseline gap-1.5 shrink-0">
                                            <span class="course-panel__price-value font-extrabold leading-none tracking-[-0.03em]"><?= htmlspecialcharsbx($c['price']) ?></span>
                                            <span class="price-gradient text-sm font-semibold leading-none">рублей</span>
                                        </span>
                                    <?php endif; ?>
                                    <a href="#zapis" class="btn btn--v2 course-tabs__cta font-semibold smooth-link">Мне это подходит, записываюсь</a>
                                </div>
                            </div>
                        </div>

                        <!-- Строка 2: Вы научитесь + В стоимость обучения входит -->
                        <div class="grid grid-cols-1 gap-4 md:gap-6 md:grid-cols-2 md:items-stretch">
                            <?php if ($c['teach_html'] !== ''): ?>
                                <div class="course-inventory-card h-full">
                                    <p class="course-inventory-card__title">Вы научитесь</p>
                                    <div class="content-editor mt-4"><?= $c['teach_html'] ?></div>
                                </div>
                            <?php endif; ?>

                            <?php if (!empty($c['included'])): ?>
                                <div class="course-inventory-card h-full">
                                    <p class="course-inventory-card__title">В стоимость обучения входит</p>
                                    <ul class="course-included__list list-none m-0 p-0 mt-4 flex flex-col gap-3">
                                        <?php foreach ($c['included'] as $item): ?>
                                            <li class="course-included__item flex items-center gap-3">
                                                <?php if ($item['img'] !== ''): ?>
                                                    <img
                                                        class="course-included__img shrink-0 w-[60px] h-[60px] object-contain"
                                                        src="<?= htmlspecialcharsbx($item['img']) ?>"
                                                        alt="<?= htmlspecialcharsbx($item['name']) ?>"
                                                        loading="lazy"
                                                        aria-hidden="true"
                                                    />
                                                <?php else: ?>
                                                    <div class="course-included__img-placeholder shrink-0 w-[60px] h-[60px]" aria-hidden="true"></div>
                                                <?php endif; ?>
                                                <span class="text-[15px] leading-[1.45] text-zinc-800"><?= htmlspecialcharsbx($item['name']) ?></span>
                                            </li>
                                        <?php endforeach; ?>
                                    </ul>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Строка 3: Инструменты -->
                        <?php if ($c['tools_html'] !== ''): ?>
                            <div class="flex flex-col gap-3">
                                <div class="course-inventory-card">
                                    <p class="course-inventory-card__title">Инструменты</p>
                                    <div class="content-editor mt-4"><?= $c['tools_html'] ?></div>
                                </div>
                            </div>
                        <?php endif; ?>

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
