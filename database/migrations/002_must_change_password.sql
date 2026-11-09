-- =====================================================================
-- Миграция: принудительная смена пароля при первом входе
-- Применяется к уже развёрнутой базе:
--   mysql -u <user> -p <db> < database/migrations/002_must_change_password.sql
-- =====================================================================

SET NAMES utf8mb4;

SET @has_col := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'users'
      AND column_name = 'must_change_password'
);

SET @ddl := IF(
    @has_col = 0,
    'ALTER TABLE users ADD COLUMN must_change_password TINYINT(1) NOT NULL DEFAULT 0 AFTER password_hash',
    'DO 0'
);

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
