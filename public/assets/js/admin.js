// GRAND DETAILING — скрипты админ-панели.
document.addEventListener('DOMContentLoaded', () => {
    // Гамбургер-меню на мобильном.
    const adminToggle = document.getElementById('admin-nav-toggle');
    const adminSidebar = document.getElementById('admin-sidebar');
    const adminOverlay = document.getElementById('admin-overlay');
    if (adminToggle && adminSidebar) {
        const closeMenu = () => {
            adminSidebar.classList.remove('open');
            if (adminOverlay) adminOverlay.hidden = true;
        };
        adminToggle.addEventListener('click', () => {
            const open = !adminSidebar.classList.contains('open');
            adminSidebar.classList.toggle('open', open);
            if (adminOverlay) adminOverlay.hidden = !open;
        });
        if (adminOverlay) adminOverlay.addEventListener('click', closeMenu);
        adminSidebar.querySelectorAll('a').forEach((a) => a.addEventListener('click', closeMenu));
    }

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

    // Подтверждение сохранения настроек (сводка изменений).
    const settingsForm = document.getElementById('settings-form');
    const settingsModal = document.getElementById('settings-modal');
    if (settingsForm && settingsModal) {
        const changesList = document.getElementById('settings-changes');
        const cancelBtn = document.getElementById('settings-cancel');
        const confirmBtn = document.getElementById('settings-confirm');
        let allowSubmit = false;

        settingsForm.addEventListener('submit', (e) => {
            if (allowSubmit) return;

            const changes = [];
            settingsForm.querySelectorAll('input[data-old]').forEach((input) => {
                if (input.value !== input.dataset.old) {
                    changes.push({
                        label: input.dataset.label || input.name,
                        old: input.dataset.old,
                        neu: input.value,
                    });
                }
            });

            e.preventDefault();
            if (changes.length === 0) {
                alert('Нет изменений.');
                return;
            }

            changesList.innerHTML = '';
            changes.forEach((c) => {
                const li = document.createElement('li');
                li.textContent = c.label + ': ' + (c.old || '—') + ' → ' + (c.neu || '—');
                changesList.appendChild(li);
            });
            settingsModal.hidden = false;
        });

        cancelBtn.addEventListener('click', () => { settingsModal.hidden = true; });
        confirmBtn.addEventListener('click', () => {
            settingsModal.hidden = true;
            allowSubmit = true;
            settingsForm.submit();
        });
    }
});
