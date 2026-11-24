-- =====================================================================
-- Миграция: FAQ для главной страницы
--   mysql -u <user> -p <db> < database/migrations/008_faqs.sql
-- =====================================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS faqs (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    question   VARCHAR(255) NOT NULL,
    answer     TEXT         NOT NULL,
    sort_order INT          NOT NULL DEFAULT 0,
    is_active  TINYINT(1)   NOT NULL DEFAULT 1,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_faqs_active (is_active, sort_order)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Вопросы и ответы для блока на главной';
