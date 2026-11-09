-- =====================================================================
-- Миграция: throttle неудачных попыток входа по аккаунту (login_attempts)
--   mysql -u <user> -p <db> < database/migrations/004_login_attempts.sql
-- =====================================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS login_attempts (
    id         INT UNSIGNED NOT NULL AUTO_INCREMENT,
    login_hash CHAR(64)     NOT NULL COMMENT 'sha256 от нормализованного логина',
    ip         VARCHAR(45)  NOT NULL,
    created_at DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_login_attempts_login (login_hash, created_at),
    KEY idx_login_attempts_ip (ip, created_at)
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Неудачные попытки входа по аккаунту/IP';
