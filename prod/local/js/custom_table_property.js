/**
 * custom_table_property.js
 * Логика кнопок «Добавить строку» и сортировки drag&drop
 * для кастомного типа свойства «Таблица».
 */

if (typeof window._ctpCounters === 'undefined') {
    window._ctpCounters = {};
}

/**
 * Добавить новую строку в таблицу.
 */
function ctpAddRow(propId, cols, controlName) {
    const tbody = document.getElementById('ctp-body-' + propId);
    if (!tbody) return;

    if (typeof window._ctpCounters[propId] === 'undefined') {
        window._ctpCounters[propId] = tbody.querySelectorAll('tr.ctp-row').length;
    }

    const rIdx = window._ctpCounters[propId]++;

    const tr = document.createElement('tr');
    tr.className  = 'ctp-row';
    tr.dataset.ridx = rIdx;

    // Ячейка-ручка для drag & drop
    let html = `<td style="padding:4px;border:1px solid #ccc;text-align:center;vertical-align:middle;cursor:grab;color:#aaa;width:20px;"
                    class="ctp-handle" title="Перетащить">⠿</td>`;

    cols.forEach((col, cIdx) => {
        const fieldName = `${controlName}[${rIdx}][${cIdx}]`;
        const type = col.TYPE || 'string';

        let input = '';
        if (type === 'textarea') {
            input = `<textarea name="${fieldName}" rows="3" style="width:100%;box-sizing:border-box;"></textarea>`;
        } else if (type === 'file') {
            input = `<input type="file" name="ctp_file[${propId}][${rIdx}][${cIdx}]" style="width:100%;" />
                     <input type="hidden" name="${fieldName}" value="0" />`;
        } else {
            input = `<input type="text" name="${fieldName}" value="" style="width:100%;box-sizing:border-box;" />`;
        }

        html += `<td style="padding:4px;border:1px solid #ccc;vertical-align:top;">${input}</td>`;
    });

    html += `<td style="padding:4px;border:1px solid #ccc;text-align:center;vertical-align:middle;">
        <input type="button" value="✕" onclick="this.closest('tr').remove()" style="cursor:pointer;" />
    </td>`;

    tr.innerHTML = html;
    tbody.appendChild(tr);
}

/**
 * Переиндексировать все строки после drag & drop сортировки.
 * Обновляет атрибуты name у всех input/textarea/select в строках.
 */
function ctpReindex(propId) {
    const tbody = document.getElementById('ctp-body-' + propId);
    if (!tbody) return;

    const rows = tbody.querySelectorAll('tr.ctp-row');

    rows.forEach((tr, newIdx) => {
        const oldIdx = parseInt(tr.dataset.ridx ?? newIdx, 10);
        if (oldIdx === newIdx) return;

        tr.querySelectorAll('[name]').forEach(el => {
            let name = el.name;

            // ctp_file[propId][oldIdx][cIdx] и ctp_del[propId][oldIdx][cIdx]
            name = name.replace(
                new RegExp(`^(ctp_(?:file|del)\\[${propId}\\])\\[${oldIdx}\\](\\[\\d+\\])$`),
                `$1[${newIdx}]$2`
            );

            // controlName[oldIdx][cIdx] — VALUE-поля (последние два bracket-пары)
            name = name.replace(
                new RegExp(`\\]\\[${oldIdx}\\](\\[\\d+\\])$`),
                `][${newIdx}]$1`
            );

            el.name = name;
        });

        tr.dataset.ridx = newIdx;
    });

    // Счётчик не меньше текущего числа строк
    window._ctpCounters[propId] = Math.max(
        rows.length,
        window._ctpCounters[propId] ?? 0
    );
}
