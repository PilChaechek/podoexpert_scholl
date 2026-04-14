<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

CModule::IncludeModule('iblock');

$faqRes = CIBlockElement::GetList(
    ['SORT' => 'ASC', 'ID' => 'ASC'],
    ['IBLOCK_ID' => 4, 'ACTIVE' => 'Y'],
    false,
    false,
    ['ID', 'NAME', 'PREVIEW_TEXT', 'DETAIL_TEXT', 'DETAIL_TEXT_TYPE', 'PREVIEW_TEXT_TYPE']
);
?>

<section class="section faq">
    <div class="container">
        <div class="mx-auto max-w-5xl">
            <h2 class="mb-6 text-center text-3xl font-bold text-zinc-900 md:text-5xl">Ответы на частые вопросы</h2>

            <div class="space-y-3 grid gap-2" data-faq-root>
                <?php
                $faqCount = 0;
                while ($row = $faqRes->Fetch()):
                    $question = trim((string) $row['NAME']);
                    if ($question === '') {
                        continue;
                    }

                    $detailRaw = (string) $row['DETAIL_TEXT'];
                    $previewRaw = (string) $row['PREVIEW_TEXT'];
                    $bodyHtml = '';
                    if ($detailRaw !== '') {
                        $bodyHtml = $row['DETAIL_TEXT_TYPE'] === 'html'
                            ? $detailRaw
                            : nl2br(htmlspecialcharsbx($detailRaw));
                    } elseif ($previewRaw !== '') {
                        $bodyHtml = $row['PREVIEW_TEXT_TYPE'] === 'html'
                            ? $previewRaw
                            : nl2br(htmlspecialcharsbx($previewRaw));
                    }
                    if ($bodyHtml === '') {
                        continue;
                    }
                    $faqCount++;
                ?>
                    <article class="faq__item overflow-hidden rounded-xl border border-zinc-200 bg-zinc-50 m-0">
                        <button
                            type="button"
                            class="flex w-full items-center justify-between gap-4 px-5 py-5 text-left md:px-7 bvi-no-styles"
                            data-faq-trigger
                            aria-expanded="false"
                        >
                            <span class="text-xl font-semibold text-zinc-900 bvi-no-styles"><?= htmlspecialcharsbx($question) ?></span>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                width="16"
                                height="10"
                                viewBox="0 0 16 10"
                                fill="none"
                                class="transition-transform"
                                data-faq-icon
                                aria-hidden="true"
                            >
                                <path
                                    d="M2 2l6 6 6-6"
                                    stroke="var(--purple)"
                                    stroke-width="3"
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                />
                            </svg>
                        </button>

                        <div class="hidden px-5 pb-6 md:px-7 md:pb-7" data-faq-content>
                            <div class="grid grid-cols-[60px_1fr] gap-5 pt-1 md:grid-cols-[140px_1fr] md:gap-7">
                                <img
                                    src="<?= SITE_TEMPLATE_PATH ?>/images/director.webp"
                                    alt="Иллюстрация к ответу"
                                    class="faq__item-image img"
                                    loading="lazy"
                                    width="180"
                                    height="208"
                                />

                                <div class="faq__item-answer space-y-4 content-editor">
                                    <?= $bodyHtml ?>
                                </div>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>

                <?php if ($faqCount === 0): ?>
                    <p class="text-center text-zinc-500">Пока нет опубликованных вопросов.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</section>

<script>
(function () {
    const roots = document.querySelectorAll('[data-faq-root]');
    roots.forEach(function (root) {
        const items = [].slice.call(root.querySelectorAll('article'));
        items.forEach(function (item) {
            const trigger = item.querySelector('[data-faq-trigger]');
            const content = item.querySelector('[data-faq-content]');
            const icon = item.querySelector('[data-faq-icon]');
            if (!trigger || !content || !icon) return;

            trigger.addEventListener('click', function () {
                const isOpen = trigger.getAttribute('aria-expanded') === 'true';

                items.forEach(function (currentItem) {
                    const currentTrigger = currentItem.querySelector('[data-faq-trigger]');
                    const currentContent = currentItem.querySelector('[data-faq-content]');
                    const currentIcon = currentItem.querySelector('[data-faq-icon]');
                    if (!currentTrigger || !currentContent || !currentIcon) return;
                    currentTrigger.setAttribute('aria-expanded', 'false');
                    currentContent.classList.add('hidden');
                    currentIcon.classList.remove('rotate-180');
                });

                if (!isOpen) {
                    trigger.setAttribute('aria-expanded', 'true');
                    content.classList.remove('hidden');
                    icon.classList.add('rotate-180');
                }
            });
        });
    });
}());
</script>
