<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Основные сведения");
$APPLICATION->AddChainItem("Сведения об образовательной организации", "/info/");

CModule::IncludeModule("iblock");

$element = CIBlockElement::GetList(
    [],
    ['IBLOCK_ID' => 1, 'CODE' => 'main', 'ACTIVE' => 'Y'],
    false,
    ['nTopCount' => 1],
    ['ID', 'NAME', 'DETAIL_TEXT', 'DETAIL_TEXT_TYPE']
)->GetNextElement();

ob_start();

if ($element) {
    $fields = $element->GetFields();
    if (!empty($fields['DETAIL_TEXT'])) {
        echo $fields['DETAIL_TEXT_TYPE'] === 'html'
            ? $fields['DETAIL_TEXT']
            : nl2br(htmlspecialchars($fields['DETAIL_TEXT']));
    } else {
        echo '<p>Контент не заполнен.</p>';
    }
} else {
    echo '<p>Страница не найдена. Создайте элемент с кодом <code>main</code> в инфоблоке.</p>';
}

$infoPageContent = ob_get_clean();
include $_SERVER["DOCUMENT_ROOT"] . "/info/include/layout.php";
?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
