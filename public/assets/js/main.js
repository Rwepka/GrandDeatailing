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

        // Шаг валиден, только когда выбраны его обязательные поля:
        // шаг 1 — класс автомобиля; шаг 2 — хотя бы одна услуга.
        const isCurrentStepValid = () => {
            if (current === 0) {
                return form.querySelector('input[name="car_class"]:checked') !== null;
            }
            if (current === 1) {
                return form.querySelectorAll('input[name="service_ids[]"]:checked').length > 0;
            }
            return true;
        };

        const updateNextButton = () => {
            const nextBtn = steps[current].querySelector('[data-next]');
            if (nextBtn) {
                nextBtn.disabled = !isCurrentStepValid();
            }
        };

        const show = (index) => {
            steps.forEach((step, i) => { step.hidden = i !== index; });
            indicators.forEach((li, i) => {
                li.classList.toggle('active', i === index);
                li.classList.toggle('done', i < index);
            });
            current = index;
            updateNextButton();
        };

        form.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-next], [data-prev]');
            if (!btn) return;
            e.preventDefault();
            const next = current + (btn.hasAttribute('data-next') ? 1 : -1);
            if (next >= 0 && next < steps.length) show(next);
        });

        form.addEventListener('change', updateNextButton);

        const initialStep = form.hasAttribute('data-skip-to')
            ? parseInt(form.getAttribute('data-skip-to'), 10) - 1
            : 0;
        show(Math.max(0, Math.min(initialStep, steps.length - 1)));

        // Имя: только буквы, пробелы и дефис (без цифр и спецсимволов).
        const nameInput = form.querySelector('input[name="name"]');
        if (nameInput) {
            nameInput.addEventListener('input', () => {
                nameInput.value = nameInput.value.replace(/[^a-zA-Zа-яА-ЯёЁ\s-]/g, '');
            });
        }
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
            if (!raw) return false;
            try {
                const draft = JSON.parse(decodeURIComponent(raw));
                if (!draft || typeof draft !== 'object') return false;

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
                // Имя и телефон берём из cookie только если учётная запись их не заполнила
                // (приоритет — данные аккаунта).
                const setValueIfEmpty = (selector, value) => {
                    if (value !== undefined && value !== null) {
                        const el = form.querySelector(selector);
                        if (el && el.value === '') el.value = String(value);
                    }
                };
                setValue('input[name="brand_model"]', draft.brand_model);
                setValueIfEmpty('input[name="name"]', draft.name);
                setValueIfEmpty('input[name="phone"]', draft.phone);
                setValue('input[name="pref_date"]', draft.pref_date);
                setValue('input[name="pref_time"]', draft.pref_time);
                setValue('textarea[name="comment"]', draft.comment);
                return true;
            } catch (e) {
                // Повреждённая cookie — игнорируем.
                return false;
            }
        };

        if (loadDraft()) {
            form.dispatchEvent(new Event('change'));
        }

        form.addEventListener('input', saveDraft);
        form.addEventListener('change', saveDraft);

        form.addEventListener('submit', () => {
            document.cookie = DRAFT_COOKIE + '=; max-age=0; path=/; samesite=Lax';
        });
    }

    // Калькулятор стоимости на главной странице.
    const calcTotal = document.getElementById('calc-total');
    if (calcTotal) {
        const calcServices = Array.from(document.querySelectorAll('input[name="service_ids[]"]'));
        const calcCarClasses = Array.from(document.querySelectorAll('input[name="car_class"]'));

        const recalc = () => {
            const sum = calcServices.reduce((acc, cb) => {
                if (!cb.checked) return acc;
                const raw = (cb.dataset.price || '').trim();
                if (raw === '' || raw === '0') return acc;
                const price = parseFloat(raw);
                return Number.isFinite(price) ? acc + price : acc;
            }, 0);

            calcTotal.textContent = sum === 0
                ? 'от 0 ₽'
                : 'от ' + sum.toLocaleString('ru-RU') + ' ₽';
        };

        calcServices.forEach((cb) => cb.addEventListener('change', recalc));
        calcCarClasses.forEach((radio) => radio.addEventListener('change', recalc));
        recalc();
    }

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
