<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

if (empty($arResult)) return '';

$itemSize = count($arResult);
$strReturn = '<nav class="mb-5 flex flex-wrap items-center gap-2 text-sm text-zinc-500" aria-label="Хлебные крошки">';

for ($i = 0; $i < $itemSize; $i++) {
    $title = htmlspecialcharsex($arResult[$i]['TITLE']);
    $link  = $arResult[$i]['LINK'];
    $isLast = $i === $itemSize - 1;

    if ($i > 0) {
        $strReturn .= '<span>/</span>';
    }

    if ($link !== '' && !$isLast) {
        $strReturn .= '<a href="' . $link . '" class="transition-colors hover:text-zinc-700">' . $title . '</a>';
    } else {
        $strReturn .= '<span class="text-zinc-600">' . $title . '</span>';
    }
}

$strReturn .= '</nav>';

return $strReturn;
