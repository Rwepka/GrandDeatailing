// GRAND DETAILING — базовый скрипт.
// Здесь позже: квиз записи, отправка форм через fetch, слайдер портфолио.

document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.getElementById('nav-toggle');
    const menu = document.getElementById('header-menu');

    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            const open = menu.classList.toggle('open');
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
            toggle.setAttribute('aria-label', open ? 'Закрыть меню' : 'Открыть меню');
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

        const show = (index, focusStep = false) => {
            steps.forEach((step, i) => { step.hidden = i !== index; });
            indicators.forEach((li, i) => {
                li.classList.toggle('active', i === index);
                li.classList.toggle('done', i < index);
                if (i === index) {
                    li.setAttribute('aria-current', 'step');
                } else {
                    li.removeAttribute('aria-current');
                }
            });
            current = index;
            updateNextButton();

            if (focusStep) {
                const heading = steps[index].querySelector('h2');
                if (heading) {
                    heading.setAttribute('tabindex', '-1');
                    heading.focus();
                }
            }
        };

        form.addEventListener('click', (e) => {
            const btn = e.target.closest('[data-next], [data-prev]');
            if (!btn) return;
            e.preventDefault();
            const next = current + (btn.hasAttribute('data-next') ? 1 : -1);
            if (next >= 0 && next < steps.length) show(next, true);
        });

        form.addEventListener('change', updateNextButton);

        const initialStep = form.hasAttribute('data-skip-to')
            ? parseInt(form.getAttribute('data-skip-to'), 10) - 1
            : 0;
        show(Math.max(0, Math.min(initialStep, steps.length - 1)));

        // Имя не мутируем: серверная валидация есть, а апостроф/цифры могут быть
        // частью реального имени (например, «O'Neil»).
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

    // Карта: iframe грузим только по клику — без сторонних cookies и TBT заранее.
    const mapLoad = document.getElementById('map-load');
    if (mapLoad) {
        mapLoad.addEventListener('click', () => {
            const wrap = document.getElementById('map-wrap');
            const src = mapLoad.getAttribute('data-src');
            if (!wrap || !src) {
                return;
            }

            const iframe = document.createElement('iframe');
            iframe.src = src;
            iframe.title = 'Карта: GRAND DETAILING, Туапсе';
            iframe.style.width = '100%';
            iframe.style.height = '400px';
            iframe.style.border = '0';
            iframe.setAttribute('allowfullscreen', '');
            iframe.loading = 'lazy';

            wrap.innerHTML = '';
            wrap.appendChild(iframe);
        });
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

    // Цена под выбранный класс автомобиля: сначала из data-prices, затем базовый data-price.
    const priceForClass = (baseRaw, pricesJson, carClass) => {
        if (pricesJson) {
            try {
                const map = JSON.parse(pricesJson);
                const val = map && carClass ? map[carClass] : undefined;
                if (val !== undefined && val !== null && val !== '') {
                    const p = parseFloat(val);
                    if (Number.isFinite(p)) return p;
                }
            } catch (e) { /* повреждённый JSON — игнорируем */ }
        }
        const raw = (baseRaw === undefined || baseRaw === null) ? '' : String(baseRaw).trim();
        if (raw !== '' && raw !== '0') {
            const p = parseFloat(raw);
            if (Number.isFinite(p)) return p;
        }
        return null;
    };

    const formatRoubles = (value) => 'от ' + value.toLocaleString('ru-RU') + ' ₽';

    // Калькулятор стоимости на главной странице.
    const calcTotal = document.getElementById('calc-total');
    if (calcTotal) {
        const calcServices = Array.from(document.querySelectorAll('input[name="service_ids[]"]'));
        const calcCarClasses = Array.from(document.querySelectorAll('input[name="car_class"]'));

        const recalc = () => {
            const carClassRadio = document.querySelector('input[name="car_class"]:checked');
            const carClass = carClassRadio ? carClassRadio.value : '';

            const sum = calcServices.reduce((acc, cb) => {
                if (!cb.checked) return acc;
                const price = priceForClass(cb.dataset.price, cb.dataset.prices, carClass);
                return price === null ? acc : acc + price;
            }, 0);

            calcTotal.textContent = sum === 0 ? 'от 0 ₽' : formatRoubles(sum);
        };

        calcServices.forEach((cb) => cb.addEventListener('change', recalc));
        calcCarClasses.forEach((radio) => radio.addEventListener('change', recalc));
        recalc();
    }

    // Цены по кузову и итог в шаге 2 квиза записи.
    const bookingForm = document.getElementById('booking-form');
    const quizTotal = document.getElementById('quiz-total');
    if (bookingForm && quizTotal) {
        const quizCards = Array.from(bookingForm.querySelectorAll('.service-card'));
        const quizCarClasses = Array.from(bookingForm.querySelectorAll('input[name="car_class"]'));
        const quizServices = Array.from(bookingForm.querySelectorAll('input[name="service_ids[]"]'));

        const updateQuizPrices = () => {
            const carClassRadio = bookingForm.querySelector('input[name="car_class"]:checked');
            const carClass = carClassRadio ? carClassRadio.value : '';

            let sum = 0;
            quizCards.forEach((card) => {
                const cb = card.querySelector('input[name="service_ids[]"]');
                const priceEl = card.querySelector('.price');
                const price = priceForClass(card.dataset.price, card.dataset.prices, carClass);
                if (priceEl) {
                    priceEl.textContent = price === null ? 'цена по запросу' : formatRoubles(price);
                }
                if (cb && cb.checked && price !== null) {
                    sum += price;
                }
            });

            quizTotal.textContent = sum === 0 ? 'от 0 ₽' : formatRoubles(sum);
        };

        quizCarClasses.forEach((radio) => radio.addEventListener('change', updateQuizPrices));
        quizServices.forEach((cb) => cb.addEventListener('change', updateQuizPrices));
        updateQuizPrices();
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

    // Фокус на первое невалидное поле (a11y).
    const firstInvalid = Array.from(document.querySelectorAll('[aria-invalid="true"]'))
        .find((el) => el.offsetParent !== null);
    if (firstInvalid) {
        firstInvalid.focus();
    }
});
