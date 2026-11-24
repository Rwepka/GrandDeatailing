# Production release checklist

Короткий чек-лист выкладки GRAND DETAILING. Подробности по SEO и индексации — в [SEO-DEPLOY.md](SEO-DEPLOY.md).

## 1. Сборка

```bash
php scripts/preflight.php --local
php scripts/build-release.php /path/to/release
```

По умолчанию архив создаётся в `../release` рядом с проектом. Внутри:

- `MANIFEST.txt` — список файлов и SHA256;
- рядом с zip — файл `.sha256`.

В релиз **не попадают**: `config/config.php`, `database/seed.sql`, сессии, загруженные файлы, `.git`, локальные тесты.

## 2. До выкладки

- [ ] Рабочее дерево закоммичено, тесты из `docs/QA-REPORT.md` проходят.
- [ ] Сделан бэкап файлов и БД.
- [ ] На сервере PHP **8.3+** и расширения `pdo_mysql`, `mbstring`, `openssl`, `fileinfo`, `curl`, `zip`.
- [ ] Документ-рут домена указывает на `public/`, а не на корень репозитория.
- [ ] Создан `config/config.php` из `config/config.production.example.php`:
  - `app.url = https://grand-detailing.ru`;
  - `app.debug = false`;
  - корректные доступы к БД.
- [ ] Каталоги `storage/sessions`, `storage/uploads`, `public/uploads` доступны на запись.
- [ ] Настроен HTTPS и редирект с HTTP.

## 3. Миграции

Для **существующего** сайта:

```text
007_service_content.sql
008_faqs.sql
```

Для **чистой** БД: `database/schema.sql`, затем миграции `001`–`008` по порядку. `database/seed.sql` — только демо-данные (категории, услуги, отзывы), на существующем проде его импортировать не нужно; в релизный архив seed не входит.

## 4. Выкладка

- [ ] Загрузить архив в релизную директорию, распаковать.
- [ ] Не перезаписывать `config/config.php`, `storage/sessions`, `storage/uploads`, `public/uploads`.
- [ ] Проверить права: каталоги `755`, файлы `644`, `config.php` — минимально необходимые.
- [ ] Запустить `php scripts/preflight.php`; все пункты должны быть `OK`.
- [ ] Очистить кэш OPcache, если он включён.

## 5. Проверка после выкладки

- [ ] `/`, `/services`, `/booking`, `/contacts`, `/reviews`, `/news` — 200.
- [ ] `/admin` без входа — редирект на `/login`.
- [ ] В админке заполнены описания услуг и FAQ.
- [ ] `robots.txt` и `sitemap.xml` отдают боевой домен.
- [ ] В HTML есть canonical, OG, JSON-LD; FAQ только на главной.
- [ ] Создание заявки: цена «Универсал» соответствует цене услуги.
- [ ] Нет ошибок в PHP-логе и консоли браузера.

## 6. После релиза

- [ ] Отправить `sitemap.xml` в Google Search Console и Яндекс.Вебмастер.
- [ ] Проверить ключ IndexNow и при необходимости запустить `php scripts/indexnow.php`.
- [ ] Сверить телефон и часы с Яндекс.Бизнес/2ГИС (см. SEO-DEPLOY).
- [ ] Настроить cron очистки неподтверждённых клиентов, если используется:

```cron
0 4 * * * php /path/to/site/scripts/cleanup_unverified_clients.php
```

## 7. Откат

1. Вернуть файлы из предыдущего релиза/бэкапа.
2. При необходимости восстановить БД.
3. Миграции `007`/`008` не удалять: они добавочные и не мешают старой версии кода.
