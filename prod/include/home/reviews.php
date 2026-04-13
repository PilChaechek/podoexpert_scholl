<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if (!function_exists('home_reviews_video_embed_url')) {
    function home_reviews_video_embed_url(string $raw): string
    {
        $raw = trim($raw);
        if ($raw === '') {
            return '';
        }
        if (str_contains($raw, 'youtube.com/embed/')) {
            return $raw;
        }
        if (preg_match('#youtube\.com/watch\?v=([a-zA-Z0-9_-]{11})#', $raw, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        if (preg_match('#youtu\.be/([a-zA-Z0-9_-]{11})#', $raw, $m)) {
            return 'https://www.youtube.com/embed/' . $m[1];
        }
        if (preg_match('#rutube\.ru/video(?:/private)?/([a-zA-Z0-9]+)/?#', $raw, $m)) {
            return 'https://rutube.ru/play/embed/' . $m[1];
        }
        return $raw;
    }
}

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

CModule::IncludeModule('iblock');

$reviewsRes = CIBlockElement::GetList(
    ['SORT' => 'ASC', 'ID' => 'ASC'],
    ['IBLOCK_ID' => 5, 'ACTIVE' => 'Y'],
    false,
    false,
    ['ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'DETAIL_TEXT_TYPE', 'PREVIEW_TEXT_TYPE', 'PREVIEW_PICTURE', 'PROPERTY_FAVORITE']
);

$reviewRows = [];
while ($ob = $reviewsRes->GetNextElement()) {
    $f = $ob->GetFields();
    $p = $ob->GetProperties();

    if (($f['PROPERTY_FAVORITE_VALUE'] ?? '') === 'да') {
        continue;
    }

    $name = trim((string) $f['NAME']);
    if ($name === '') {
        continue;
    }

    $city = trim((string) ($p['CITY']['VALUE'] ?? ''));
    $videoRaw = trim((string) ($p['VIDEO']['VALUE'] ?? ''));
    $videoEmbed = home_reviews_video_embed_url($videoRaw);
    $hasVideo = $videoEmbed !== '';

    $previewText = (string) $f['PREVIEW_TEXT'];
    $detailText = (string) $f['DETAIL_TEXT'];
    $previewType = (string) $f['PREVIEW_TEXT_TYPE'];
    $detailType = (string) $f['DETAIL_TEXT_TYPE'];

    $quote = home_reviews_body_plain($previewText, $previewType);
    if ($quote === '') {
        $quote = home_reviews_body_plain($detailText, $detailType);
    }
    if ($quote === '') {
        continue;
    }

    $fullPlain = home_reviews_body_plain($detailText, $detailType);
    if ($fullPlain === '') {
        $fullPlain = $quote;
    }

    $imgSrc = '';
    if (!empty($f['PREVIEW_PICTURE'])) {
        $imgSrc = (string) CFile::GetPath($f['PREVIEW_PICTURE']);
    }
    if ($imgSrc === '') {
        continue;
    }

    $reviewRows[] = [
        'name' => $name,
        'city' => $city,
        'quote' => $quote,
        'full_plain' => $fullPlain,
        'detail_is_html' => ($detailType === 'html' && $detailText !== ''),
        'detail_html' => $detailText,
        'img' => $imgSrc,
        'has_video' => $hasVideo,
        'video_embed' => $videoEmbed,
    ];
}

$GLOBALS['HOME_REVIEWS_SCRIPT_LOADED'] = $GLOBALS['HOME_REVIEWS_SCRIPT_LOADED'] ?? false;
if (!empty($reviewRows) && !$GLOBALS['HOME_REVIEWS_SCRIPT_LOADED']) {
    $GLOBALS['HOME_REVIEWS_SCRIPT_LOADED'] = true;
    ?>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/micromodal@0.4.10/dist/micromodal.min.js"></script>
    <?php
}
?>

<section class="section landing-reviews" aria-label="Отзывы">
    <div class="container">
        <h2 class="h2 mb-6 font-bold text-zinc-900 md:mb-8">Отзывы</h2>
    </div>
    <?php if (!empty($reviewRows)): ?>
        <div class="container--fullwidth">
            <div class="landing-reviews__swiper ">
                <div class="swiper js-landing-reviews-swiper">
                    <div class="swiper-wrapper landing-reviews__wrapper">
                        <?php foreach ($reviewRows as $item): ?>
                            <div class="swiper-slide landing-reviews__slide">
                                <article
                                    class="landing-review-card"
                                    data-video-embed="<?= htmlspecialcharsbx($item['video_embed']) ?>"
                                >
                                    <div
                                        class="landing-review-card__media"
                                        role="button"
                                        tabindex="0"
                                        aria-label="Открыть полный отзыв: <?= htmlspecialcharsbx($item['name']) ?>"
                                        data-micromodal-trigger="landing-review-modal"
                                    >
                                        <img
                                            class="landing-review-card__photo"
                                            src="<?= htmlspecialcharsbx($item['img']) ?>"
                                            alt="Фото: <?= htmlspecialcharsbx($item['name']) ?>"
                                            loading="lazy"
                                            width="400"
                                            height="400"
                                        />
                                        <?php if ($item['has_video']): ?>
                                            <span class="landing-review-card__play" aria-hidden="true">
                                                <svg
                                                    class="landing-review-card__play-icon"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    width="22"
                                                    height="26"
                                                    viewBox="0 0 22 26"
                                                    fill="none"
                                                    aria-hidden="true"
                                                >
                                                    <path d="M22 13L0.25 25.1244V0.875644L22 13Z" fill="currentColor" />
                                                </svg>
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                    <div class="landing-review-card__body">
                                        <p class="landing-review-card__category">Отзыв мастера</p>
                                        <p class="landing-review-card__author"><?= htmlspecialcharsbx($item['name']) ?></p>
                                        <?php if ($item['city'] !== ''): ?>
                                            <p class="landing-review-card__city"><?= htmlspecialcharsbx($item['city']) ?></p>
                                        <?php endif; ?>
                                        <p class="landing-review-card__quote"><?= nl2br(htmlspecialcharsbx($item['quote'])) ?></p>
                                        <button
                                            type="button"
                                            class="landing-review-card__link"
                                            data-micromodal-trigger="landing-review-modal"
                                        >
                                            <?= $item['has_video'] ? 'Смотреть отзыв' : 'Читать отзыв' ?>
                                        </button>
                                    </div>
                                    <div
                                        class="landing-review-card__fulltext"
                                        hidden
                                        <?php if ($item['detail_is_html']): ?>data-review-body-html="1"<?php endif ?>
                                    >
                                        <?php if ($item['detail_is_html']): ?>
                                            <?= $item['detail_html'] ?>
                                        <?php else: ?>
                                            <?= htmlspecialcharsbx($item['full_plain']) ?>
                                        <?php endif ?>
                                    </div>
                                </article>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <button
                    class="landing-reviews__nav landing-reviews__nav--prev"
                    type="button"
                    aria-label="Предыдущий отзыв"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="14" viewBox="0 0 9 14" fill="none">
                        <path d="M1 13L7 7L1 0.999999" stroke="currentColor" stroke-width="2" />
                    </svg>
                </button>
                <button
                    class="landing-reviews__nav landing-reviews__nav--next"
                    type="button"
                    aria-label="Следующий отзыв"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" width="9" height="14" viewBox="0 0 9 14" fill="none">
                        <path d="M1 13L7 7L1 0.999999" stroke="currentColor" stroke-width="2" />
                    </svg>
                </button>
            </div>
        </div>

        <div class="review-modal-mm" id="landing-review-modal" aria-hidden="true">
            <div class="review-modal-mm__overlay" tabindex="-1" data-micromodal-close>
                <div
                    class="review-modal-mm__container"
                    role="dialog"
                    aria-modal="true"
                    aria-labelledby="landing-review-modal-title"
                >
                    <button
                        type="button"
                        class="review-modal-mm__close"
                        data-micromodal-close
                        aria-label="Закрыть"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M3 3L17 17M17 3L3 17" stroke="currentColor" stroke-width="2" stroke-linecap="round" />
                        </svg>
                    </button>
                    <div class="review-modal-mm__grid">
                        <div class="review-modal-mm__media-col">
                            <img class="review-modal-mm__img" alt="" width="640" height="640" decoding="async" />
                            <div class="review-modal-mm__video" data-review-modal-video-wrap hidden>
                                <iframe
                                    class="review-modal-mm__iframe"
                                    title="Видеоотзыв"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
                                    allowfullscreen
                                    loading="lazy"
                                ></iframe>
                            </div>
                        </div>
                        <div class="review-modal-mm__body">
                            <h2 id="landing-review-modal-title" class="review-modal-mm__name"></h2>
                            <p class="review-modal-mm__city"></p>
                            <div class="review-modal-mm__text content-editor"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        (function () {
            function initReviewsSwiper() {
                const SwiperConstructor = window.Swiper;
                if (!SwiperConstructor) return;
                document.querySelectorAll('.js-landing-reviews-swiper').forEach(function (slider) {
                    if (slider.dataset.initialized === 'true') return;
                    const root = slider.closest('.landing-reviews__swiper');
                    if (!root) return;
                    const swiper = new SwiperConstructor(slider, {
                        slidesPerView: 1.12,
                        slidesOffsetBefore: 12,
                        slidesOffsetAfter: 12,
                        spaceBetween: 12,
                        speed: 600,
                        navigation: {
                            nextEl: root.querySelector('.landing-reviews__nav--next'),
                            prevEl: root.querySelector('.landing-reviews__nav--prev'),
                        },
                        breakpoints: {
                            576: { slidesPerView: 2, spaceBetween: 16 },
                            992: {
                                slidesPerView: 3,
                                spaceBetween: 20,
                                slidesOffsetBefore: 0,
                                slidesOffsetAfter: 0,
                            },
                            1280: { slidesPerView: 4, spaceBetween: 16 },
                        },
                    });
                    if (swiper) slider.dataset.initialized = 'true';
                });
            }

            function initReviewsMicroModal() {
                const MM = window.MicroModal;
                const section = document.querySelector('.landing-reviews');
                const modal = document.getElementById('landing-review-modal');
                if (!MM || !section || !modal || section.getAttribute('data-mm-reviews') === '1') return;

                const videoWrap = modal.querySelector('[data-review-modal-video-wrap]');
                const iframe = modal.querySelector('.review-modal-mm__iframe');
                const img = modal.querySelector('.review-modal-mm__img');
                const titleEl = modal.querySelector('.review-modal-mm__name');
                const cityEl = modal.querySelector('.review-modal-mm__city');
                const textEl = modal.querySelector('.review-modal-mm__text');
                if (!titleEl || !cityEl || !textEl || !img) return;

                function fillFromCard(card) {
                    const fullEl = card.querySelector('.landing-review-card__fulltext');
                    const name = (card.querySelector('.landing-review-card__author')?.textContent ?? '').trim();
                    const city = (card.querySelector('.landing-review-card__city')?.textContent ?? '').trim();
                    const photo = card.querySelector('.landing-review-card__photo');
                    const videoUrl = (card.getAttribute('data-video-embed') || '').trim();

                    titleEl.textContent = name;
                    cityEl.textContent = city;
                    if (fullEl && fullEl.hasAttribute('data-review-body-html')) {
                        textEl.innerHTML = fullEl.innerHTML;
                    } else {
                        textEl.textContent = fullEl ? fullEl.textContent.trim() : '';
                    }

                    if (photo) {
                        img.src = photo.currentSrc || photo.src;
                        img.alt = photo.alt || ('Фото: ' + name);
                        img.hidden = false;
                    } else {
                        img.hidden = true;
                    }

                    if (videoUrl && iframe && videoWrap) {
                        videoWrap.hidden = false;
                        iframe.src = videoUrl;
                    } else {
                        if (iframe) iframe.removeAttribute('src');
                        if (videoWrap) videoWrap.hidden = true;
                    }
                }

                MM.init({
                    openTrigger: 'data-micromodal-trigger',
                    closeTrigger: 'data-micromodal-close',
                    openClass: 'is-open',
                    disableScroll: true,
                    disableFocus: false,
                    awaitOpenAnimation: false,
                    awaitCloseAnimation: false,
                    debugMode: false,
                    onShow: function (modalEl, _active, ev) {
                        const t = ev?.currentTarget;
                        const card = t?.closest?.('.landing-review-card') ?? null;
                        if (card) fillFromCard(card);
                    },
                    onClose: function () {
                        if (iframe) iframe.removeAttribute('src');
                        if (videoWrap) videoWrap.hidden = true;
                    },
                });

                section.addEventListener('keydown', function (e) {
                    if (e.key !== 'Enter' && e.key !== ' ') return;
                    const t = e.target;
                    if (!t?.classList?.contains('landing-review-card__media')) return;
                    e.preventDefault();
                    t.click();
                });

                section.setAttribute('data-mm-reviews', '1');
            }

            function bootReviews() {
                initReviewsSwiper();
                initReviewsMicroModal();
                if (!window.MicroModal) {
                    window.addEventListener('load', function () {
                        initReviewsMicroModal();
                    }, { once: true });
                }
            }

            if (document.readyState === 'loading') {
                document.addEventListener('DOMContentLoaded', bootReviews, { once: true });
            } else {
                bootReviews();
            }
        }());
        </script>
    <?php else: ?>
        <div class="container">
            <p class="text-zinc-500">Пока нет опубликованных отзывов.</p>
        </div>
    <?php endif; ?>
</section>
