// GRAND DETAILING — базовый скрипт.
// Здесь позже: квиз записи, отправка форм через fetch, слайдер портфолио.

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('nav-toggle');
    const menu = document.getElementById('header-menu');

    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            menu.classList.toggle('open');
        });
    }

    // Пошаговый квиз формы записи.
    const form = document.getElementById('booking-form');
    if (form) {
        const steps = Array.from(form.querySelectorAll('.booking-step'));
        const indicators = Array.from(form.querySelectorAll('.booking-steps li'));
        let current = 0;

        const show = (index) => {
            steps.forEach((step, i) => { step.hidden = i !== index; });
            indicators.forEach((li, i) => {
                li.classList.toggle('active', i === index);
                li.classList.toggle('done', i < index);
            });
            current = index;
        };

        form.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-next], [data-prev]');
            if (!btn) return;
            e.preventDefault();
            const next = current + (btn.hasAttribute('data-next') ? 1 : -1);
            if (next >= 0 && next < steps.length) show(next);
        });

        show(0);
    }

    // Плашка согласия на использование cookie.
    const banner = document.getElementById('cookie-banner');
    if (banner) {
        const setConsent = (value) => {
            document.cookie = 'cookie_consent=' + value + '; max-age=31536000; path=/; samesite=Lax';
            banner.remove();
        };
        document.getElementById('cookie-accept')?.addEventListener('click', () => setConsent('1'));
        document.getElementById('cookie-decline')?.addEventListener('click', () => setConsent('0'));
    }

    // Черновик формы записи (cookie booking_draft).
    if (form) {
        const DRAFT_COOKIE = 'booking_draft';
        const DRAFT_MAX_AGE = 604800; // 7 дней

        const readCookie = (name) => {
            const prefix = name + '=';
            const parts = document.cookie.split(';');
            for (let part of parts) {
                part = part.trim();
                if (part.startsWith(prefix)) {
                    return part.slice(prefix.length);
                }
            }
            return null;
        };

        const saveDraft = () => {
            const carClass = form.querySelector('input[name="car_class"]:checked');
            const draft = {
                car_class: carClass ? carClass.value : '',
                brand_model: form.querySelector('input[name="brand_model"]')?.value ?? '',
                service_ids: Array.from(form.querySelectorAll('input[name="service_ids[]"]'))
                    .filter((cb) => cb.checked)
                    .map((cb) => cb.value),
                name: form.querySelector('input[name="name"]')?.value ?? '',
                phone: form.querySelector('input[name="phone"]')?.value ?? '',
                pref_date: form.querySelector('input[name="pref_date"]')?.value ?? '',
                pref_time: form.querySelector('input[name="pref_time"]')?.value ?? '',
                comment: form.querySelector('textarea[name="comment"]')?.value ?? '',
            };
            document.cookie = DRAFT_COOKIE + '=' + encodeURIComponent(JSON.stringify(draft))
                + '; max-age=' + DRAFT_MAX_AGE + '; path=/; samesite=Lax';
        };

        const loadDraft = () => {
            const raw = readCookie(DRAFT_COOKIE);
            if (!raw) return;
            try {
                const draft = JSON.parse(decodeURIComponent(raw));
                if (!draft || typeof draft !== 'object') return;

                if (draft.car_class !== undefined && draft.car_class !== null) {
                    const carClass = String(draft.car_class);
                    form.querySelectorAll('input[name="car_class"]').forEach((radio) => {
                        radio.checked = radio.value === carClass;
                    });
                }

                if (Array.isArray(draft.service_ids)) {
                    const ids = draft.service_ids.map((id) => String(id));
                    form.querySelectorAll('input[name="service_ids[]"]').forEach((cb) => {
                        cb.checked = ids.includes(cb.value);
                    });
                }

                const setValue = (selector, value) => {
                    if (value !== undefined && value !== null) {
                        const el = form.querySelector(selector);
                        if (el) el.value = String(value);
                    }
                };
                setValue('input[name="brand_model"]', draft.brand_model);
                setValue('input[name="name"]', draft.name);
                setValue('input[name="phone"]', draft.phone);
                setValue('input[name="pref_date"]', draft.pref_date);
                setValue('input[name="pref_time"]', draft.pref_time);
                setValue('textarea[name="comment"]', draft.comment);
            } catch (e) {
                // Повреждённая cookie — игнорируем.
            }
        };

        loadDraft();

        form.addEventListener('input', saveDraft);
        form.addEventListener('change', saveDraft);

        form.addEventListener('submit', () => {
            document.cookie = DRAFT_COOKIE + '=; max-age=0; path=/; samesite=Lax';
        });
    }
});
