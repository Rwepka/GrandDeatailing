# Деплой и индексация GRAND DETAILING

Пошаговая инструкция для выкладки правок и попадания сайта в Google и Яндекс.

Короткий чек-лист релиза и сборка архива — в [RELEASE-CHECKLIST.md](RELEASE-CHECKLIST.md).

## 1. Перед выкладкой

1. Убедитесь, что в `config/config.php`:
   - `app.url` = `https://grand-detailing.ru` (без слэша на конце);
   - `app.debug` = `false`;
   - корректны доступы к боевой БД.
   На сервере можно взять за основу `config/config.production.example.php`.
2. **Сначала примените миграции** `007_service_content.sql` и `008_faqs.sql` (иначе новые поля/таблица ещё не существуют). Для чистой БД — `database/schema.sql` и миграции `001`–`008`.
3. Сделайте бэкап файлов и базы (phpMyAdmin → экспорт).
4. Не перезаписывайте на сервере:
   - `config/config.php`;
   - `storage/sessions/`, `storage/uploads/`;
   - загруженные пользователями файлы.
5. Соберите и проверьте релиз:

   ```bash
   php scripts/preflight.php --local
   php scripts/build-release.php /path/to/release
   ```

   Архив не содержит `config/config.php`, demo-seed и пользовательские файлы.

## 2. Что залить

Изменённые и новые файлы:

```
TZ.md
docs/SEO-DEPLOY.md
public/index.php
public/139cc868f917b8ceb8beb05e7f239773.txt
public/assets/css/style.css
public/assets/css/admin.css
public/assets/fonts/inter-cyrillic.woff2
public/assets/fonts/inter-latin.woff2
public/assets/js/main.js
database/migrations/007_service_content.sql
database/migrations/008_faqs.sql
src/Controller/AdminController.php
src/Controller/AdminFaqController.php
src/Controller/AdminSettingsController.php
src/Controller/BookingController.php
src/Controller/ContactController.php
src/Controller/HomeController.php
src/Controller/NewsController.php
src/Controller/ReviewController.php
src/Controller/ServiceController.php
src/Controller/SitemapController.php
src/Model/Faq.php
src/Model/Service.php
src/bootstrap.php
src/helpers.php
scripts/indexnow.php
views/booking.php
views/contacts.php
views/home.php
views/news.php
views/reviews.php
views/service.php
views/services.php
views/layout/header.php
views/layout/footer.php
views/partials/breadcrumbs.php
views/admin/booking_form.php
views/admin/faqs.php
views/admin/faq_form.php
views/admin/layout/header.php
views/admin/service_form.php
```

Режим передачи — binary. Сборки нет: `asset()` сам обновит версии CSS/JS по mtime.

Проще залить готовый архив из `scripts/build-release.php`: он содержит рабочие файлы и `config/config.production.example.php`, но не содержит `config/config.php`, demo-seed и пользовательские загрузки.

## 3. Проверка после выкладки

После применения миграций заполните в админке:

- «Услуги» → у каждой услуги: краткое описание, полное описание, «Что входит», «Как проходит работа», цены по кузовам. Эти данные сразу попадут в карточки и на страницу услуги.
- «Вопросы» → добавьте 4–6 вопросов для блока на главной; порядок и видимость настраиваются там же.

```bash
DOMAIN="https://grand-detailing.ru"

curl -sSI "$DOMAIN/" | sed -n '1,30p'
curl -sS "$DOMAIN/robots.txt"
curl -sS "$DOMAIN/sitemap.xml" | head -40
```

Чек-лист:

- [ ] `/` — 200, корректный `<title>` с «Туапсе».
- [ ] `/services`, `/booking`, `/contacts`, `/reviews`, `/news` — 200.
- [ ] `/admin` без входа — редирект на `/login`; после входа — 200.
- [ ] `robots.txt` содержит `Sitemap: https://grand-detailing.ru/sitemap.xml`.
- [ ] `sitemap.xml` содержит все услуги из БД, домен не `localhost`.
- [ ] В HTML есть `canonical`, `og:image`, JSON-LD `AutoWash`, `Service`, `FAQPage`, `BreadcrumbList`.
- [ ] Шрифт Inter отдаётся с домена (`/assets/fonts/...`), без запросов к Google Fonts.
- [ ] В админке: «Настройки» → «Главная» отсутствует, коды подтверждения доступны.
- [ ] Создание заявки мастером: при выборе «Универсал» цена в заявке соответствует универсалу.

## 4. Google Search Console

1. Откройте https://search.google.com/search-console.
2. Добавьте ресурс `https://grand-detailing.ru/` (префикс URL).
3. Подтвердите владение одним из способов:
   - HTML-тег: скопируйте значение из `content` и вставьте в админке в поле «Google Search Console (код подтверждения)»;
   - или DNS-запись (если есть доступ к DNS).
4. После подтверждения: «Файлы Sitemap» → добавить `sitemap.xml`.
5. «Проверка URL» → главная → «Запросить индексирование».
6. Через 1–2 недели проверьте «Индексирование страниц» и «Эффективность».

## 5. Яндекс.Вебмастер

1. Откройте https://webmaster.yandex.ru.
2. Добавьте сайт `https://grand-detailing.ru`.
3. Подтвердите владение:
   - мета-тег: вставьте код в админке в поле «Яндекс.Вебмастер (код подтверждения)»;
   - или DNS/HTML-файл.
4. «Индексирование» → «Файлы Sitemap» → добавить `https://grand-detailing.ru/sitemap.xml`.
5. «Индексирование» → «Переобход страниц» → отправить главную и ключевые услуги.
6. Проверьте «Диагностика» и «Поисковые запросы» через несколько дней.

## 6. IndexNow (Яндекс и Bing)

В репозитории есть ключ и скрипт:

```
public/139cc868f917b8ceb8beb05e7f239773.txt
scripts/indexnow.php
```

После деплоя (когда ключ доступен по адресу `https://grand-detailing.ru/139cc868f917b8ceb8beb05e7f239773.txt`):

```bash
php scripts/indexnow.php --dry-run   # проверить payload
php scripts/indexnow.php             # отправить в Яндекс и Bing
```

Успех — HTTP 200/202. Google IndexNow не поддерживает: для него нужен Search Console.

## 7. Локальное SEO и NAP

Для карточки в Яндекс.Картах и локальной выдачи приведите к единому виду:

- название: `GRAND DETAILING` / `Grand Detailing`;
- адрес: `г. Туапсе, ул. Богдана Хмельницкого, 16`;
- телефон: **уточнить актуальный** — на сайте `+7 (988) 413-57-77`, в справочниках встречается `+7 (988) 413-07-77`;
- режим работы: на сайте «ежедневно 10:00–19:00», в справочниках — «Пн–сб, перерыв»;
- категории: детейлинг, полировка, химчистка, тонировка, оклейка плёнкой;
- ссылка на сайт и онлайн-запись.

Обновите Яндекс.Бизнес, 2ГИС, Zoon, Авито и соцсети. Расхождения в телефоне/часах снижают доверие поисковиков к локальным данным.

## 8. Что ещё влияет на позиции

- Регулярные реальные отзывы (Яндекс.Карты, 2ГИС) и ответы на них.
- Фотографии работ с привязкой к услугам и городу.
- Новости/статьи по темам: «как ухаживать за керамикой», «чем отличается полировка от детейлинга», «тонировка по ГОСТ».
- Внутренние ссылки: услуги ↔ запись ↔ контакты.
- Скорость: hero уже в WebP, шрифт локальный, JS без библиотек.
- Ссылки с местных площадок: автосервисы Туапсе, городские каталоги, партнёры.

## 9. Откат

- Вернуть предыдущую версию файлов из бэкапа/git.
- Восстановить БД при необходимости.
- Примените миграции `007_service_content.sql` и `008_faqs.sql` до или сразу после выкладки файлов. После этого заполните описания услуг и FAQ в админке.
