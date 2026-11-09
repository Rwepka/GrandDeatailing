// GRAND DETAILING — скрипты админ-панели.
document.addEventListener('DOMContentLoaded', () => {
    // Гамбургер-меню на мобильном.
    const adminToggle = document.getElementById('admin-nav-toggle');
    const adminSidebar = document.getElementById('admin-sidebar');
    const adminOverlay = document.getElementById('admin-overlay');
    if (adminToggle && adminSidebar) {
        const isDesktop = () => window.matchMedia('(min-width: 821px)').matches;
        let overlayHideTimer = null;
        const closeMenu = () => {
            if (isDesktop()) {
                return;
            }
            adminSidebar.classList.remove('expanded');
            if (adminOverlay) {
                // Overlay гаснет вместе с sidebar, а hidden ставим после анимации.
                adminOverlay.classList.add('closing');
                window.clearTimeout(overlayHideTimer);
                overlayHideTimer = window.setTimeout(() => {
                    adminOverlay.hidden = true;
                    adminOverlay.classList.remove('closing');
                }, 200);
            }
            adminToggle.setAttribute('aria-expanded', 'false');
            adminToggle.setAttribute('aria-label', 'Развернуть меню');
        };
        adminToggle.addEventListener('click', () => {
            const expanded = adminSidebar.classList.toggle('expanded');
            adminToggle.setAttribute('aria-expanded', expanded ? 'true' : 'false');
            adminToggle.setAttribute('aria-label', expanded ? 'Свернуть меню' : 'Развернуть меню');
            if (!isDesktop() && adminOverlay) {
                window.clearTimeout(overlayHideTimer);
                adminOverlay.classList.remove('closing');
                adminOverlay.hidden = !expanded;
            }
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
        const dialog = settingsModal.querySelector('.modal');
        let allowSubmit = false;
        let lastFocused = null;
        let modalHideTimer = null;

        const closeModal = () => {
            settingsModal.classList.add('closing');
            window.clearTimeout(modalHideTimer);
            modalHideTimer = window.setTimeout(() => {
                settingsModal.hidden = true;
                settingsModal.classList.remove('closing');
            }, 120);
            if (lastFocused && typeof lastFocused.focus === 'function') {
                lastFocused.focus();
            }
        };

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

            lastFocused = document.activeElement;
            window.clearTimeout(modalHideTimer);
            settingsModal.classList.remove('closing');
            settingsModal.hidden = false;
            if (dialog) {
                dialog.setAttribute('tabindex', '-1');
                dialog.focus();
            }
        });

        cancelBtn.addEventListener('click', closeModal);
        confirmBtn.addEventListener('click', () => {
            settingsModal.hidden = true;
            allowSubmit = true;
            settingsForm.submit();
        });

        settingsModal.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                e.preventDefault();
                closeModal();
                return;
            }
            if (e.key !== 'Tab') {
                return;
            }

            const focusable = Array.from(settingsModal.querySelectorAll(
                'button, [href], input, select, textarea, [tabindex]:not([tabindex="-1"])'
            ));
            if (focusable.length === 0) {
                return;
            }

            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        });
    }

    // Диалог назначения мастера на заявку.
    const assignModal = document.getElementById('assign-modal');
    const assignForm = document.getElementById('assign-form');
    if (assignModal && assignForm) {
        const assignDialog = assignModal.querySelector('.modal');
        const assignCancel = document.getElementById('assign-cancel');
        let assignLastFocused = null;
        let assignHideTimer = null;

        const closeAssign = () => {
            assignModal.classList.add('closing');
            window.clearTimeout(assignHideTimer);
            assignHideTimer = window.setTimeout(() => {
                assignModal.hidden = true;
                assignModal.classList.remove('closing');
            }, 120);
            if (assignLastFocused && typeof assignLastFocused.focus === 'function') {
                assignLastFocused.focus();
            }
        };

        document.querySelectorAll('.js-assign').forEach((btn) => {
            btn.addEventListener('click', () => {
                const bookingId = btn.getAttribute('data-booking-id');
                const masterId = btn.getAttribute('data-master-id') || '0';
                if (!bookingId) {
                    return;
                }

                assignForm.action = '/admin/bookings/' + bookingId + '/assign';
                assignForm.querySelectorAll('input[name="master_id"]').forEach((radio) => {
                    radio.checked = radio.value === masterId;
                });

                assignLastFocused = btn;
                window.clearTimeout(assignHideTimer);
                assignModal.classList.remove('closing');
                assignModal.hidden = false;
                if (assignDialog) {
                    assignDialog.setAttribute('tabindex', '-1');
                    assignDialog.focus();
                }
            });
        });

        assignCancel.addEventListener('click', closeAssign);

        assignModal.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                e.preventDefault();
                closeAssign();
                return;
            }
            if (e.key !== 'Tab') {
                return;
            }
            const focusable = Array.from(assignModal.querySelectorAll(
                'input, button, [href], [tabindex]:not([tabindex="-1"])'
            )).filter((el) => !el.disabled && el.offsetParent !== null);
            if (focusable.length === 0) {
                return;
            }
            const first = focusable[0];
            const last = focusable[focusable.length - 1];
            if (e.shiftKey && document.activeElement === first) {
                e.preventDefault();
                last.focus();
            } else if (!e.shiftKey && document.activeElement === last) {
                e.preventDefault();
                first.focus();
            }
        });
    }
});
