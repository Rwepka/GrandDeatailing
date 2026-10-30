-- =====================================================================
-- Схема базы данных детейлинг-студии «GRAND DETAILING»
-- СУБД: MySQL 8+ / MariaDB, кодировка utf8mb4, движок InnoDB
-- =====================================================================

SET NAMES utf8mb4;

SET FOREIGN_KEY_CHECKS = 0;

-- ---------------------------------------------------------------------
-- Справочник ролей пользователей (клиент, администратор, мастер)
-- ---------------------------------------------------------------------
CREATE TABLE roles (
    id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    code  VARCHAR(20)  NOT NULL,
    title VARCHAR(60)  NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_roles_code (code)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Справочник ролей пользователей';

-- ---------------------------------------------------------------------
-- Справочник категорий услуг
-- ---------------------------------------------------------------------
CREATE TABLE categories (
    id    INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    title VARCHAR(100)  NOT NULL,
    slug  VARCHAR(120)  NOT NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_categories_slug (slug)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Категории услуг (детейлинг кузова, салона, комплексы)';

-- ---------------------------------------------------------------------
-- Пользователи системы (клиенты и сотрудники)
-- ---------------------------------------------------------------------
CREATE TABLE users (
    id            INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    role_id       INT UNSIGNED     NOT NULL,
    name          VARCHAR(100)     NOT NULL,
    email         VARCHAR(120)     NULL,
    phone         VARCHAR(20)      NOT NULL,
    password_hash VARCHAR(255)     NOT NULL,
    discount      TINYINT UNSIGNED NOT NULL DEFAULT 0,
    created_at    DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_users_email (email),
    UNIQUE KEY uq_users_phone (phone),
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles (id)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Пользователи: клиенты, администраторы, мастера';

-- ---------------------------------------------------------------------
-- Автомобили клиентов
-- ---------------------------------------------------------------------
CREATE TABLE cars (
    id     INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    user_id INT UNSIGNED     NOT NULL,
    brand  VARCHAR(60)       NOT NULL,
    model  VARCHAR(60)       NOT NULL,
    year   SMALLINT UNSIGNED NULL,
    vin    VARCHAR(17)       NULL,
    plate  VARCHAR(12)       NULL,
    PRIMARY KEY (id),
    KEY idx_cars_user (user_id),
    CONSTRAINT fk_cars_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Автомобили клиентов';

-- ---------------------------------------------------------------------
-- Услуги (прайс-лист)
-- ---------------------------------------------------------------------
CREATE TABLE services (
    id            INT UNSIGNED      NOT NULL AUTO_INCREMENT,
    category_id   INT UNSIGNED      NOT NULL,
    title         VARCHAR(140)      NOT NULL,
    slug          VARCHAR(160)      NOT NULL,
    price_from    DECIMAL(10, 2)    NULL,
    duration_min  INT UNSIGNED      NULL,
    image         VARCHAR(255)      NULL,
    is_active     TINYINT(1)        NOT NULL DEFAULT 1,
    PRIMARY KEY (id),
    UNIQUE KEY uq_services_slug (slug),
    KEY idx_services_category (category_id),
    CONSTRAINT fk_services_category FOREIGN KEY (category_id) REFERENCES categories (id)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Услуги детейлинг-студии';

-- ---------------------------------------------------------------------
-- Заявки (бронирования)
-- ---------------------------------------------------------------------
CREATE TABLE bookings (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NULL,
    car_id     INT UNSIGNED NULL,
    status     ENUM('new', 'confirmed', 'in_work', 'done', 'ready', 'cancelled') NOT NULL DEFAULT 'new',
    pref_date  DATE         NULL,
    pref_time  TIME         NULL,
    comment      TEXT         NULL,
    client_name  VARCHAR(100) NULL,
    client_phone VARCHAR(20)  NULL,
    car_info     VARCHAR(255) NULL,
    agreed_cost  DECIMAL(10, 2) NULL,
    final_cost   DECIMAL(10, 2) NULL,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_bookings_status (status),
    KEY idx_bookings_user (user_id),
    KEY idx_bookings_car (car_id),
    CONSTRAINT fk_bookings_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT fk_bookings_car FOREIGN KEY (car_id) REFERENCES cars (id) ON DELETE SET NULL
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Бронирования (заявки на услуги)';

-- ---------------------------------------------------------------------
-- Состав заявки: выбранные услуги с ценой на момент заказа
-- ---------------------------------------------------------------------
CREATE TABLE booking_items (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    booking_id INT UNSIGNED  NOT NULL,
    service_id INT UNSIGNED  NOT NULL,
    price      DECIMAL(10, 2) NULL,
    PRIMARY KEY (id),
    KEY idx_booking_items_booking (booking_id),
    KEY idx_booking_items_service (service_id),
    CONSTRAINT fk_booking_items_booking FOREIGN KEY (booking_id) REFERENCES bookings (id) ON DELETE CASCADE,
    CONSTRAINT fk_booking_items_service FOREIGN KEY (service_id) REFERENCES services (id)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Позиции (услуги) внутри бронирования';

-- ---------------------------------------------------------------------
-- Отзывы клиентов
-- ---------------------------------------------------------------------
CREATE TABLE reviews (
    id           INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    user_id      INT UNSIGNED     NULL,
    booking_id   INT UNSIGNED     NULL,
    rating       TINYINT UNSIGNED NOT NULL DEFAULT 5,
    text         TEXT             NULL,
    is_published TINYINT(1)       NOT NULL DEFAULT 0,
    created_at   DATETIME         NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_reviews_published (is_published),
    KEY idx_reviews_user (user_id),
    KEY idx_reviews_booking (booking_id),
    CONSTRAINT fk_reviews_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE SET NULL,
    CONSTRAINT fk_reviews_booking FOREIGN KEY (booking_id) REFERENCES bookings (id) ON DELETE SET NULL
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Отзывы клиентов о выполненных работах';

-- ---------------------------------------------------------------------
-- Портфолио выполненных работ
-- ---------------------------------------------------------------------
CREATE TABLE works (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title       VARCHAR(140) NOT NULL,
    slug        VARCHAR(160) NOT NULL,
    category    VARCHAR(80)  NULL,
    before_img  VARCHAR(255) NULL,
    after_img   VARCHAR(255) NULL,
    description TEXT         NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_works_slug (slug)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Портфолио выполненных работ (до/после)';

-- ---------------------------------------------------------------------
-- Статьи блога
-- ---------------------------------------------------------------------
CREATE TABLE posts (
    id           INT UNSIGNED NOT NULL AUTO_INCREMENT,
    title        VARCHAR(160) NOT NULL,
    slug         VARCHAR(160) NOT NULL,
    content      LONGTEXT     NULL,
    image        VARCHAR(255) NULL,
    is_published TINYINT(1)   NOT NULL DEFAULT 0,
    created_at   DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_posts_slug (slug)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Статьи блога';

-- ---------------------------------------------------------------------
-- Сообщения обратной связи
-- ---------------------------------------------------------------------
CREATE TABLE messages (
    id         INT UNSIGNED  NOT NULL AUTO_INCREMENT,
    name       VARCHAR(100)  NOT NULL,
    phone      VARCHAR(20)   NOT NULL,
    email      VARCHAR(120)  NULL,
    text       TEXT          NULL,
    is_read    TINYINT(1)    NOT NULL DEFAULT 0,
    created_at DATETIME      NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_messages_read (is_read)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Сообщения обратной связи с сайта';

-- ---------------------------------------------------------------------
-- Уведомления пользователей
-- ---------------------------------------------------------------------
CREATE TABLE notifications (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NOT NULL,
    text       VARCHAR(255) NOT NULL,
    is_read    TINYINT(1)   NOT NULL DEFAULT 0,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_notifications_user (user_id),
    CONSTRAINT fk_notifications_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Уведомления пользователей';

-- ---------------------------------------------------------------------
-- Токены «запомнить меня» (хранится только хэш токена)
-- ---------------------------------------------------------------------
CREATE TABLE remember_tokens (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    user_id    INT UNSIGNED NOT NULL,
    token_hash VARCHAR(64)  NOT NULL,
    expires_at DATETIME     NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    UNIQUE KEY uq_remember_tokens_hash (token_hash),
    KEY idx_remember_tokens_user (user_id),
    CONSTRAINT fk_remember_tokens_user FOREIGN KEY (user_id) REFERENCES users (id) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Токены «запомнить меня»';

-- ---------------------------------------------------------------------
-- Защита от спама: лимит отправок форм по IP
-- ---------------------------------------------------------------------
CREATE TABLE rate_limits (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    ip         VARCHAR(45)  NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_rate_limits_ip_time (ip, created_at)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Лимит отправок форм по IP';

SET FOREIGN_KEY_CHECKS = 1;

-- ---------------------------------------------------------------------
-- Настройки сайта (ключ → значение)
-- ---------------------------------------------------------------------
CREATE TABLE settings (
    id    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `key` VARCHAR(100) NOT NULL,
    value TEXT         NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_settings_key (`key`)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Настройки сайта (адрес, телефон и т.п.)';
