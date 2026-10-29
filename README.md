# GRAND DETAILING — сайт детейлинг-студии

Веб-сайт студии премиум-детейлинга «GRAND DETAILING»: каталог услуг, портфолио, запись онлайн. Построен на лёгком самописном MVC без сторонних фреймворков и без сборщиков фронтенда.

**Стек:** PHP 8.3 · MySQL 8.0 · PDO · Apache (mod_rewrite) · HTML/CSS/JS (vanilla).

**Архитектура:** единая точка входа `public/index.php` (фронт-контроллер) → `src/Core/Router` → контроллер (`src/Controller`) → модель (`src/Model`) → представление (`views`).

---

## 1. Требования

| Компонент | Версия |
|-----------|--------|
| PHP       | 8.3 или выше |
| MySQL     | 8.0 или выше |
| Веб-сервер| Apache с mod_rewrite **или** встроенный сервер PHP |
| Расширения PHP | `pdo_mysql` |

---

## 2. Развёртывание локально

### Вариант А — OpenServer / XAMPP

1. Скопируйте папку `site/` в веб-каталог локального сервера
   (например, `C:\OSPanel\domains\grand-detail\` или `C:\xampp\htdocs\grand-detail\`).
2. В панели управления укажите **корень домена = `public/`**
   (домен должен указывать на папку `public`, а не на корень `site/`).
3. Создайте базу данных и импортируйте схему (см. раздел «Создание и импорт базы данных»).
4. Отредактируйте `config/config.php` — укажите данные доступа к БД.
5. Откройте сайт по адресу домена, например `http://grand-detail`.

### Вариант Б — встроенный сервер PHP

Из папки `site/` выполните:

```bash
php -S localhost:8000 -t public router.php
```

Затем откройте <http://localhost:8000>.

> Удобнее использовать готовые скрипты запуска: `./start.sh` (Linux/macOS) или `start.bat` (Windows). Порт можно переопределить: `PORT=8080 ./start.sh`.

> Скрипт `router.php` нужен, чтобы «чистые» URL (например `/services`) попадали во фронт-контроллер `index.php`: он отдаёт существующие статические файлы (css/js/img) напрямую, а остальное перенаправляет в `index.php`. На Apache ту же роль выполняет `.htaccess`.

---

## 3. Развёртывание на shared-хостинге (REG.RU)

1. По FTP загрузите **содержимое папки `site/`** в корневую директорию хостинга
   (обычно `public_html/` или `www/`).
2. В панели хостинга укажите **корень сайта = `public/`**
   (в REG.RU это настройка «Корневая директория сайта» → `domains/ваш-домен/public`).
3. В разделе «Базы данных MySQL» создайте базу данных и пользователя, запомните имя БД, логин и пароль.
4. Через **phpMyAdmin** (ссылка есть в панели хостинга) импортируйте `database/schema.sql` и при необходимости `database/seed.sql` (вкладка «Импорт»).
5. Заполните `config/config.php` данными, выданными хостингом (см. раздел «Настройка config.php»).
6. Проверьте сайт по домену.

---

## 4. Создание и импорт базы данных

### Через консоль (mysql CLI)

```bash
# 1. Импортировать схему — она сама создаёт БД grand_detailing и все таблицы
mysql -u root -p < database/schema.sql

# 2. Импортировать тестовые данные (опционально)
mysql -u root -p < database/seed.sql
```

Команды выполняются из папки `site/`.

### Через phpMyAdmin

1. Откройте phpMyAdmin и создайте БД `grand_detailing` (кодировка `utf8mb4_unicode_ci`).
2. Выберите БД → вкладка **«Импорт»** → выберите файл `database/schema.sql` → «Вперёд».
3. Повторите для `database/seed.sql`, если нужны демо-данные.

---

## 5. Настройка `config/config.php`

Файл `config/config.php` возвращает массив с настройками приложения и БД. Заполните блок `db`:

> Если файла `config/config.php` нет (свежий клон репозитория — он в `.gitignore`), скопируйте шаблон: `cp config/config.example.php config/config.php`, затем заполните реальные значения.

```php
'db' => [
    'host'    => 'localhost',        // на shared-хостинге обычно "localhost" или адрес, выданный хостером
    'name'    => 'grand_detailing',  // имя базы данных
    'user'    => 'root',             // пользователь MySQL
    'pass'    => 'секретный_пароль', // пароль MySQL
    'charset' => 'utf8mb4',
],
```

Также проверьте блок `app`:

```php
'app' => [
    'name'  => 'GRAND DETAILING',
    'url'   => 'http://localhost', // на проде — https://ваш-домен.ru (без слэша на конце)
    'debug' => true,               // на проде поставьте false
],
```

Значения читаются хелпером `config('db.host')`, `config('app.url')` и т.д.

---

## 6. Структура проекта

```
site/
├── public/                 # ВЕБ-КОРЕНЬ — домен должен указывать сюда
│   ├── index.php           # фронт-контроллер (единая точка входа) + маршруты
│   ├── .htaccess           # rewrite всех запросов на index.php
│   ├── robots.txt
│   └── assets/             # css/ js/ img/ + uploads/
├── src/                    # PHP-код приложения (ВНЕ веб-корня)
│   ├── bootstrap.php       # автозагрузчик классов (namespace App\)
│   ├── helpers.php         # config(), e(), url(), asset()
│   ├── Core/
│   │   ├── Router.php      # маршрутизация (get/post, параметры {id})
│   │   ├── Database.php    # PDO-соединение (singleton)
│   │   └── View.php        # рендер: layout/header + view + layout/footer
│   ├── Controller/
│   │   └── HomeController.php
│   └── Model/
│       ├── Model.php       # базовый класс (all(), find())
│       └── Service.php     # модель услуги (таблица services)
├── views/                  # шаблоны
│   ├── layout/header.php   # общий «шапка» (открывает <html>…<main>)
│   ├── layout/footer.php   # общий «подвал» (закрывает </main>…</html>)
│   ├── home.php
│   └── 404.php
├── config/config.php       # настройки БД и приложения (ВНЕ веб-корня)
├── database/               # schema.sql, seed.sql
├── storage/uploads/        # загрузки (вне веб-корня)
├── router.php              # router для встроенного сервера PHP (php -S)
└── .htaccess               # защитный: закрывает src/config/views/database/storage
```

**Ключевое правило:** браузер должен видеть только содержимое `public/`. Весь PHP-код, конфигурация, шаблоны и загрузки лежат вне веб-корня и закрыты защитным `.htaccess`.

---

## 7. Как добавить новую страницу

Допустим, нужна страница со списком услуг по адресу `/services`.

### Шаг 1. Добавить маршрут в `public/index.php`

```php
use App\Controller\ServiceController;

// ...
$router->get('/services', [ServiceController::class, 'index']);
```

Для маршрута с параметром:

```php
$router->get('/services/{slug}', [ServiceController::class, 'show']);
```

### Шаг 2. Создать контроллер `src/Controller/ServiceController.php`

```php
<?php
declare(strict_types=1);

namespace App\Controller;

use App\Core\View;
use App\Model\Service;

class ServiceController
{
    public function index(): void
    {
        $services = Service::all();

        View::render('services', [
            'title'    => 'Услуги — GRAND DETAILING',
            'services' => $services,
        ]);
    }
}
```

### Шаг 3. Создать представление `views/services.php`

```php
<section class="section">
    <div class="container">
        <h1>Наши услуги</h1>
        <?php foreach ($services as $service): ?>
            <h3><?= e($service['title']) ?></h3>
        <?php endforeach; ?>
    </div>
</section>
```

`View::render()` автоматически оборачивает представление в общий `layout/header.php` и `layout/footer.php`, а переменные из массива `$data` становятся доступны в шаблоне по имени.

---

## 8. Заметки по безопасности

- **Конфигурация вне веб-корня.** `config/config.php` лежит выше `public/`, поэтому недоступен из браузера. Никогда не кладите файлы с паролями в `public/`.
- **Защита от SQL-инъекций.** Все запросы с пользовательским вводом выполняются через **PDO prepared statements** (`prepare()` + `execute([$value])`), см. `src/Model/Model.php`. Не конкатенируйте значения в SQL-строку. Режим `PDO::ATTR_EMULATE_PREPARES => false` гарантирует настоящие prepared statements на стороне MySQL.
- **Защита от XSS.** Любой вывод данных в HTML оборачивается в хелпер `e()` (`htmlspecialchars(..., ENT_QUOTES, 'UTF-8')`), например `<?= e($service['title']) ?>`.
- **Защитный `.htaccess`.** Корневой `.htaccess` закрывает прямые обращения к `src/`, `config/`, `views/`, `database/`, `storage/` и запрещает листинг каталогов. Правильный корень сайта — всегда `public/`.
- **Скрытые файлы.** `public/.htaccess` блокирует доступ к файлам, начинающимся с точки (`.env`, `.git` и т.п.).
