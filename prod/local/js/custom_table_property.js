/**
 * custom_table_property.js
 * Логика кнопки "Добавить строку" для кастомного типа свойства "Таблица".
 */

const ctpRowCounters = {};

function ctpAddRow(propId, cols, controlName) {
    const tbody = document.getElementById('ctp-body-' + propId);
    if (!tbody) return;

    if (ctpRowCounters[propId] === undefined) {
        ctpRowCounters[propId] = tbody.querySelectorAll('tr.ctp-row').length;
    }
    const rIdx = ctpRowCounters[propId]++;

    const tr = document.createElement('tr');
    tr.className = 'ctp-row';

    let html = '';
    cols.forEach((col, cIdx) => {
        const fieldName = `${controlName}[${rIdx}][${cIdx}]`;
        const type = col.TYPE || 'string';

        let input = '';
        if (type === 'textarea') {
            input = `<textarea name="${fieldName}" rows="3" style="width:100%;box-sizing:border-box;"></textarea>`;
        } else if (type === 'file') {
            input = `<input type="file" name="${fieldName}[file]" style="width:100%;" />
                     <input type="hidden" name="${fieldName}[current]" value="" />`;
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
