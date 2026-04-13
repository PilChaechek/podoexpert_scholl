<?php if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Главная: Hero-блок (IBLOCK_ID = 7, элемент ID = 21).
 *
 * Поля:  NAME, PREVIEW_TEXT, PREVIEW_PICTURE.
 * Свойства: GALLERY (кастомный тип «Таблица»: Город | Описание | Фото).
 */

CModule::IncludeModule('iblock');

$heroData = (static function (): array {
    $res = CIBlockElement::GetList(
        [],
        ['IBLOCK_ID' => 7, 'ID' => 21, 'ACTIVE' => 'Y'],
        false,
        ['nTopCount' => 1],
        ['ID', 'NAME', 'PREVIEW_TEXT', 'PREVIEW_PICTURE', 'PROPERTY_GALLERY']
    );

    $el = $res->GetNext();
    if (!$el) {
        return [];
    }

    // ConvertFromDB уже декодирует JSON → массив
    $galleryRaw = $el['PROPERTY_GALLERY_VALUE'] ?? [];
    $gallery    = is_array($galleryRaw) ? $galleryRaw : [];

    // Превью-картинка элемента
    $previewSrc = '';
    if (!empty($el['PREVIEW_PICTURE'])) {
        $fileInfo = CFile::GetFileArray($el['PREVIEW_PICTURE']);
        if ($fileInfo) {
            $previewSrc = CFile::GetFileSRC($fileInfo);
        }
    }

    // Файловые колонки галереи (индекс 2 = Фото) → URL
    foreach ($gallery as &$row) {
        $fileId = (int)($row[2] ?? 0);
        if ($fileId > 0) {
            $fileInfo          = CFile::GetFileArray($fileId);
            $row['_photo_src'] = $fileInfo ? CFile::GetFileSRC($fileInfo) : '';
        } else {
            $row['_photo_src'] = '';
        }
    }
    unset($row);

    return [
        'title'       => (string)($el['NAME'] ?? ''),
        'description' => (string)($el['PREVIEW_TEXT'] ?? ''),
        'photo'       => $previewSrc,
        'gallery'     => $gallery,
    ];
})();
