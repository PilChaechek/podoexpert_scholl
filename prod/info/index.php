<?php
require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");
$APPLICATION->SetTitle("Основные сведения");
$APPLICATION->AddChainItem("Сведения об образовательной организации", "/info/");

ob_start();

CModule::IncludeModule("iblock");

$elements = CIBlockElement::GetList(
    ['SORT' => 'ASC'],
    ['IBLOCK_ID' => 1, 'ACTIVE' => 'Y'],
    false,
    false,
    ['ID', 'NAME', 'CODE']
);
?>

<ul>
    <?php while ($el = $elements->Fetch()): ?>
        <li><?= htmlspecialchars($el['NAME']) ?></li>
    <?php endwhile; ?>
</ul>

<?php
$infoPageContent = ob_get_clean();
include $_SERVER["DOCUMENT_ROOT"] . "/info/include/layout.php";
?>

<?php require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php"); ?>
