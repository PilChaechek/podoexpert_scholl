<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();

/**
 * Кастомный тип свойства инфоблока — «Таблица».
 *
 * Регистрация в init.php:
 *   require_once __DIR__ . '/classes/CustomTableProperty.php';
 *   AddEventHandler('iblock', 'OnIBlockPropertyBuildList', ['CustomTableProperty', 'GetUserTypeDescription']);
 *
 * Возможности:
 *   - Колонки настраиваются в свойстве (GetSettingsHTML): название + тип (string / textarea / file)
 *   - В карточке элемента — таблица с кнопками «Добавить строку» / «Удалить»
 *   - В списке элементов — счётчик строк
 *   - Данные хранятся как JSON в одном свойстве
 */
class CustomTableProperty
{
    const USER_TYPE = 'custom_table';

    // -------------------------------------------------------------------------
    // Регистрация типа
    // -------------------------------------------------------------------------

    public static function GetUserTypeDescription(): array
    {
        return [
            'PROPERTY_TYPE'        => 'S',
            'USER_TYPE'            => self::USER_TYPE,
            'DESCRIPTION'          => 'Таблица',
            'GetSettingsHTML'      => [__CLASS__, 'GetSettingsHTML'],
            'PrepareSettings'      => [__CLASS__, 'PrepareSettings'],
            'GetPropertyFieldHtml' => [__CLASS__, 'GetPropertyFieldHtml'],
            'GetAdminListViewHTML' => [__CLASS__, 'GetAdminListViewHTML'],
            'ConvertToDB'          => [__CLASS__, 'ConvertToDB'],
            'ConvertFromDB'        => [__CLASS__, 'ConvertFromDB'],
        ];
    }

    // -------------------------------------------------------------------------
    // Настройки свойства: задаём колонки
    // -------------------------------------------------------------------------

    public static function PrepareSettings(array $arProperty): array
    {
        $cols = [];
        if (!empty($arProperty['USER_TYPE_SETTINGS']['COLUMNS']) && is_array($arProperty['USER_TYPE_SETTINGS']['COLUMNS'])) {
            foreach ($arProperty['USER_TYPE_SETTINGS']['COLUMNS'] as $col) {
                $name = trim((string)($col['NAME'] ?? ''));
                $type = in_array($col['TYPE'] ?? '', ['string', 'textarea', 'file'], true) ? $col['TYPE'] : 'string';
                if ($name !== '') {
                    $cols[] = ['NAME' => $name, 'TYPE' => $type];
                }
            }
        }
        return ['COLUMNS' => $cols];
    }

    public static function GetSettingsHTML(array $arProperty, array $strHTMLControlName, array &$arPropertyFields): string
    {
        $arPropertyFields = ['HIDE' => ['ROW_COUNT', 'COL_COUNT', 'MULTIPLE', 'WITH_DESCRIPTION', 'SEARCHABLE', 'FILTRABLE', 'IS_REQUIRED']];

        $cols = $arProperty['USER_TYPE_SETTINGS']['COLUMNS'] ?? [];

        // Гарантируем минимум 1 пустую строку для добавления
        if (empty($cols)) {
            $cols = [['NAME' => '', 'TYPE' => 'string']];
        }

        $baseName = htmlspecialchars($strHTMLControlName['NAME']);
        $typeOptions = ['string' => 'Строка', 'textarea' => 'Текст (textarea)', 'file' => 'Файл'];

        ob_start(); ?>
        <tr>
            <td colspan="2">
                <b>Колонки таблицы:</b>
                <table id="ctp-settings-table" style="border-collapse:collapse;margin-top:8px;">
                    <thead>
                        <tr>
                            <th style="padding:4px 8px;text-align:left;border:1px solid #ccc;background:#f5f5f5;">Название</th>
                            <th style="padding:4px 8px;text-align:left;border:1px solid #ccc;background:#f5f5f5;">Тип</th>
                            <th style="padding:4px 8px;border:1px solid #ccc;background:#f5f5f5;"></th>
                        </tr>
                    </thead>
                    <tbody id="ctp-settings-body">
                        <?php foreach ($cols as $i => $col):
                            $colName = htmlspecialchars($col['NAME'] ?? '');
                            $colType = $col['TYPE'] ?? 'string';
                        ?>
                        <tr class="ctp-settings-row">
                            <td style="padding:4px;border:1px solid #ccc;">
                                <input type="text"
                                    name="<?= $baseName ?>[COLUMNS][<?= $i ?>][NAME]"
                                    value="<?= $colName ?>"
                                    style="width:200px;"
                                />
                            </td>
                            <td style="padding:4px;border:1px solid #ccc;">
                                <select name="<?= $baseName ?>[COLUMNS][<?= $i ?>][TYPE]">
                                    <?php foreach ($typeOptions as $val => $label): ?>
                                    <option value="<?= $val ?>" <?= $colType === $val ? 'selected' : '' ?>><?= $label ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </td>
                            <td style="padding:4px;border:1px solid #ccc;">
                                <input type="button" value="✕" onclick="ctpRemoveSettingsRow(this)" />
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                <br>
                <input type="button" value="+ Добавить колонку"
                    onclick="ctpAddSettingsRow('<?= $baseName ?>')"
                    style="margin-top:4px;"
                />
            </td>
        </tr>
        <script>
        var ctpSettingsColIndex = <?= count($cols) ?>;
        function ctpAddSettingsRow(baseName) {
            const tbody = document.getElementById('ctp-settings-body');
            const i = ctpSettingsColIndex++;
            const typeOptions = `
                <option value="string">Строка</option>
                <option value="textarea">Текст (textarea)</option>
                <option value="file">Файл</option>
            `;
            const tr = document.createElement('tr');
            tr.className = 'ctp-settings-row';
            tr.innerHTML = `
                <td style="padding:4px;border:1px solid #ccc;">
                    <input type="text" name="${baseName}[COLUMNS][${i}][NAME]" style="width:200px;" />
                </td>
                <td style="padding:4px;border:1px solid #ccc;">
                    <select name="${baseName}[COLUMNS][${i}][TYPE]">${typeOptions}</select>
                </td>
                <td style="padding:4px;border:1px solid #ccc;">
                    <input type="button" value="✕" onclick="ctpRemoveSettingsRow(this)" />
                </td>
            `;
            tbody.appendChild(tr);
        }
        function ctpRemoveSettingsRow(btn) {
            btn.closest('tr').remove();
        }
        </script>
        <?php
        return ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // Хранение в БД
    // -------------------------------------------------------------------------

    public static function ConvertToDB(array $arProperty, array $value): array
    {
        $rows = [];
        if (!empty($value['VALUE']) && is_array($value['VALUE'])) {
            $rows = $value['VALUE'];
        }
        return ['VALUE' => json_encode($rows, JSON_UNESCAPED_UNICODE)];
    }

    public static function ConvertFromDB(array $arProperty, array $value): array
    {
        $raw  = $value['VALUE'] ?? '';
        $rows = [];
        if (is_string($raw) && $raw !== '') {
            $decoded = json_decode($raw, true);
            if (is_array($decoded)) {
                $rows = $decoded;
            }
        }
        return ['VALUE' => $rows];
    }

    // -------------------------------------------------------------------------
    // Счётчик в списке элементов админки
    // -------------------------------------------------------------------------

    public static function GetAdminListViewHTML(array $arProperty, array $value, array $strHTMLControlName): string
    {
        $rows = $value['VALUE'] ?? [];
        if (!is_array($rows) || empty($rows)) {
            return '<span style="color:#aaa;">—</span>';
        }
        $count = count($rows);
        return '<span style="color:#555;">' . $count . ' ' . self::pluralRows($count) . '</span>';
    }

    // -------------------------------------------------------------------------
    // Форма редактирования в карточке элемента
    // -------------------------------------------------------------------------

    public static function GetPropertyFieldHtml(array $arProperty, array $value, array $strHTMLControlName): string
    {
        $cols = $arProperty['USER_TYPE_SETTINGS']['COLUMNS'] ?? [];
        if (empty($cols)) {
            return '<span style="color:red;">Не настроены колонки свойства. Зайдите в настройки инфоблока.</span>';
        }

        $rows = $value['VALUE'] ?? [];
        if (!is_array($rows)) {
            $rows = [];
        }
        // Минимум 1 пустая строка
        if (empty($rows)) {
            $rows[] = array_fill_keys(array_keys($cols), '');
        }

        $controlName = htmlspecialchars($strHTMLControlName['VALUE']);
        $propId      = (int)$arProperty['ID'];

        ob_start();
        ?>
        <div class="ctp-wrap" id="ctp-wrap-<?= $propId ?>">
            <table class="ctp-table" style="border-collapse:collapse;width:100%;">
                <thead>
                    <tr>
                        <?php foreach ($cols as $col): ?>
                        <th style="padding:4px 8px;text-align:left;border:1px solid #ccc;background:#f5f5f5;white-space:nowrap;">
                            <?= htmlspecialchars($col['NAME']) ?>
                        </th>
                        <?php endforeach; ?>
                        <th style="padding:4px 8px;border:1px solid #ccc;background:#f5f5f5;width:40px;"></th>
                    </tr>
                </thead>
                <tbody id="ctp-body-<?= $propId ?>">
                    <?php foreach ($rows as $rIdx => $row): ?>
                    <?= self::renderRow($cols, $row, $controlName, $propId, $rIdx) ?>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <br>
            <input type="button" value="+ Добавить строку"
                onclick="ctpAddRow(<?= $propId ?>, <?= htmlspecialchars(json_encode($cols, JSON_UNESCAPED_UNICODE)) ?>, '<?= $controlName ?>')"
            />
        </div>
        <script src="/local/js/custom_table_property.js"></script>
        <?php
        return ob_get_clean();
    }

    // -------------------------------------------------------------------------
    // Вспомогательные методы
    // -------------------------------------------------------------------------

    private static function renderRow(array $cols, array $row, string $controlName, int $propId, int $rIdx): string
    {
        ob_start();
        ?>
        <tr class="ctp-row">
            <?php foreach ($cols as $cIdx => $col):
                $fieldName = "{$controlName}[{$rIdx}][{$cIdx}]";
                $val       = htmlspecialchars((string)($row[$cIdx] ?? ''));
                $type      = $col['TYPE'] ?? 'string';
            ?>
            <td style="padding:4px;border:1px solid #ccc;vertical-align:top;">
                <?php if ($type === 'textarea'): ?>
                    <textarea name="<?= $fieldName ?>" rows="3" style="width:100%;box-sizing:border-box;"><?= $val ?></textarea>
                <?php elseif ($type === 'file'): ?>
                    <?php if ($val !== ''): ?>
                        <div style="margin-bottom:4px;">
                            <img src="<?= $val ?>" alt="" style="max-height:60px;max-width:120px;display:block;margin-bottom:4px;" />
                            <label><input type="checkbox" name="<?= $fieldName ?>[delete]" value="Y"> удалить</label>
                        </div>
                    <?php endif; ?>
                    <input type="file" name="<?= $fieldName ?>[file]" style="width:100%;" />
                    <input type="hidden" name="<?= $fieldName ?>[current]" value="<?= $val ?>" />
                <?php else: ?>
                    <input type="text" name="<?= $fieldName ?>" value="<?= $val ?>" style="width:100%;box-sizing:border-box;" />
                <?php endif; ?>
            </td>
            <?php endforeach; ?>
            <td style="padding:4px;border:1px solid #ccc;text-align:center;vertical-align:middle;">
                <input type="button" value="✕" onclick="this.closest('tr').remove()" style="cursor:pointer;" />
            </td>
        </tr>
        <?php
        return ob_get_clean();
    }

    private static function pluralRows(int $n): string
    {
        $n = abs($n) % 100;
        $n1 = $n % 10;
        if ($n > 10 && $n < 20) return 'строк';
        if ($n1 > 1 && $n1 < 5) return 'строки';
        if ($n1 === 1)           return 'строка';
        return 'строк';
    }
}
