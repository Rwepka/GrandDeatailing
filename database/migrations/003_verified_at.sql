-- =====================================================================
-- Миграция: ручная верификация клиента + автоудаление неподтверждённых
-- Применяется к уже развёрнутой базе:
--   mysql -u <user> -p <db> < database/migrations/003_verified_at.sql
-- =====================================================================

SET NAMES utf8mb4;

SET @has_col := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'users'
      AND column_name = 'verified_at'
);

SET @ddl := IF(
    @has_col = 0,
    'ALTER TABLE users ADD COLUMN verified_at DATETIME NULL AFTER must_change_password',
    'DO 0'
);

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
