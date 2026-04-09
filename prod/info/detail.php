<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

CModule::IncludeModule("iblock");

$code = trim($_GET['CODE'] ?? '');

if (empty($code)) {
    LocalRedirect('/info/');
}

$element = CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => 1, 'CODE' => $code, 'ACTIVE' => 'Y'],
    false,
    ['nTopCount' => 1],
    ['ID', 'NAME', 'DETAIL_TEXT', 'DETAIL_TEXT_TYPE']
)->GetNextElement();

if (!$element) {
    LocalRedirect('/info/');
}

$fields = $element->GetFields();

$APPLICATION->SetTitle($fields['NAME']);
$APPLICATION->AddChainItem("Сведения об образовательной организации", "/info/");
$APPLICATION->AddChainItem($fields['NAME']);

ob_start();
?>

<?php if (!empty($fields['DETAIL_TEXT'])): ?>
    <?= $fields['DETAIL_TEXT_TYPE'] === 'html' ? $fields['DETAIL_TEXT'] : nl2br(htmlspecialchars($fields['DETAIL_TEXT'])) ?>
<?php else: ?>
    <p>Контент не заполнен.</p>
<?php endif; ?>

<?php
$infoPageContent = ob_get_clean();
include $_SERVER["DOCUMENT_ROOT"] . "/info/include/layout.php";
?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
