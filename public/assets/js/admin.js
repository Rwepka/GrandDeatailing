// GRAND DETAILING — скрипты админ-панели.
document.addEventListener('DOMContentLoaded', () => {
    // Живой поиск по таблицам: скрываем несовпадающие строки по мере ввода.
    document.querySelectorAll('[data-table-search]').forEach((input) => {
        const table = document.querySelector(input.getAttribute('data-table-search'));
        if (!table) return;

        input.addEventListener('input', () => {
            const q = input.value.trim().toLowerCase();
            table.querySelectorAll('tbody tr').forEach((row) => {
                const match = q === '' || row.textContent.toLowerCase().includes(q);
                row.style.display = match ? '' : 'none';
            });
        });
    });

    // Маска телефона: +7 (XXX) XXX-XX-XX для всех полей type="tel".
    document.querySelectorAll('input[type="tel"]').forEach((input) => {
        const formatPhone = (v) => {
            let d = v.replace(/\D/g, '');
            if (d.startsWith('8')) d = '7' + d.slice(1);
            if (d.startsWith('7')) d = d.slice(1);
            d = d.slice(0, 10);
            let out = '+7';
            if (d.length) out += ' (' + d.slice(0, 3);
            if (d.length >= 3) out += ')';
            if (d.length > 3) out += ' ' + d.slice(3, 6);
            if (d.length > 6) out += '-' + d.slice(6, 8);
            if (d.length > 8) out += '-' + d.slice(8, 10);
            return out;
        };
        input.addEventListener('input', () => { input.value = formatPhone(input.value); });
        if (input.value) input.value = formatPhone(input.value);
    });
});
