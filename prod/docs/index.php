<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

CModule::IncludeModule("iblock");

$code = trim($_GET['CODE'] ?? '');

if (empty($code)) {
    LocalRedirect('/');
}

$element = CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => 3, 'CODE' => $code, 'ACTIVE' => 'Y'],
    false,
    ['nTopCount' => 1],
    ['ID', 'NAME', 'DETAIL_TEXT', 'DETAIL_TEXT_TYPE']
)->GetNextElement();

if (!$element) {
    LocalRedirect('/404/');
}

$fields = $element->GetFields();

$APPLICATION->SetTitle($fields['NAME']);
$APPLICATION->AddChainItem($fields['NAME']);

?>

<section class="bg-zinc-50/60 py-8 md:py-10">
    <div class="container">

        <?php $APPLICATION->IncludeComponent('bitrix:breadcrumb', 'info', [
            'PATH'       => false,
            'START_FROM' => 0,
        ], false); ?>

        <h1 class="mb-6 text-3xl font-semibold tracking-tight text-zinc-900 md:text-4xl">
            <?= htmlspecialchars($fields['NAME']) ?>
        </h1>

        <div class="rounded-2xl border border-zinc-200 bg-white px-4 py-6 shadow-sm md:px-8">
            <div class="content-editor">
                <?php if (!empty($fields['DETAIL_TEXT'])): ?>
                    <?= $fields['DETAIL_TEXT_TYPE'] === 'html'
                        ? $fields['DETAIL_TEXT']
                        : nl2br(htmlspecialchars($fields['DETAIL_TEXT'])) ?>
                <?php else: ?>
                    <p>Контент не заполнен.</p>
                <?php endif; ?>
            </div>
        </div>

    </div>
</section>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
