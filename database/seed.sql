-- =====================================================================
-- Начальные (демонстрационные) данные детейлинг-студии «GRAND DETAILING»
-- Используется INSERT IGNORE — перезапуск не вызывает ошибок дубликатов.
-- =====================================================================

SET NAMES utf8mb4;

-- Роли пользователей
INSERT IGNORE INTO roles (code, title) VALUES
    ('client', 'Клиент'),
    ('admin', 'Администратор'),
    ('master', 'Мастер');

-- Категории услуг
INSERT IGNORE INTO categories (title, slug) VALUES
    ('Детейлинг кузова', 'deteyling-kuzova'),
    ('Детейлинг салона', 'deteyling-salona'),
    ('Комплексные программы', 'kompleks');

-- Услуги (category_id подставляется через подзапрос по slug)
INSERT IGNORE INTO services (title, slug, price_from, duration_min, category_id) VALUES
    ('Мойка', 'moyka', 1000.00, 60, (SELECT id FROM categories WHERE slug = 'deteyling-kuzova')),
    ('Полировка кузова', 'polirovka-kuzova', 8000.00, 360, (SELECT id FROM categories WHERE slug = 'deteyling-kuzova')),
    ('Керамика / кварц', 'keramika-kvarc', 18000.00, 480, (SELECT id FROM categories WHERE slug = 'deteyling-kuzova')),
    ('Оклейка PPF', 'okleyka-ppf', 85000.00, 720, (SELECT id FROM categories WHERE slug = 'deteyling-kuzova')),
    ('Химчистка салона', 'himchistka-salona', 9000.00, 240, (SELECT id FROM categories WHERE slug = 'deteyling-salona')),
    ('Комплекс «под ключ»', 'kompleks-pod-klyuch', 45000.00, 720, (SELECT id FROM categories WHERE slug = 'kompleks'));

-- =====================================================================
-- Отзывы (демонстрационные, можно заменить на реальные с Яндекс.Карт)
-- =====================================================================
INSERT INTO reviews (user_id, booking_id, rating, text, is_published) VALUES
    (NULL, NULL, 5, 'Делал полировку кузова и керамику. Результат превзошёл ожидания — машина как из салона. Всё объяснили, уложились в срок. Рекомендую!', 1),
    (NULL, NULL, 5, 'Химчистка салона — просто супер. Вывели все пятна, убрали запах, всё аккуратно. Приятный сервис, обязательно вернусь.', 1),
    (NULL, NULL, 5, 'Оклеивали плёнкой PPF капот и бампер. Сделали идеально, без пузырей. Цена полностью соответствует качеству.', 1);

-- =====================================================================
-- Администратор создаётся отдельным PHP-скриптом через password_hash() —
-- НЕ хардкодить пароль в SQL.
-- =====================================================================
