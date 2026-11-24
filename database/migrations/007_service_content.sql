-- =====================================================================
-- Миграция: контент услуги для карточек и страницы услуги
--   mysql -u <user> -p <db> < database/migrations/007_service_content.sql
-- =====================================================================

SET NAMES utf8mb4;

SET @has_col := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'services' AND column_name = 'short_description'
);
SET @ddl := IF(@has_col = 0,
    'ALTER TABLE services ADD COLUMN short_description VARCHAR(255) NULL AFTER slug',
    'DO 0');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_col := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'services' AND column_name = 'description'
);
SET @ddl := IF(@has_col = 0,
    'ALTER TABLE services ADD COLUMN description TEXT NULL AFTER short_description',
    'DO 0');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_col := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'services' AND column_name = 'includes'
);
SET @ddl := IF(@has_col = 0,
    'ALTER TABLE services ADD COLUMN includes TEXT NULL AFTER description',
    'DO 0');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;

SET @has_col := (
    SELECT COUNT(*) FROM information_schema.columns
    WHERE table_schema = DATABASE() AND table_name = 'services' AND column_name = 'process'
);
SET @ddl := IF(@has_col = 0,
    'ALTER TABLE services ADD COLUMN process TEXT NULL AFTER includes',
    'DO 0');
PREPARE stmt FROM @ddl; EXECUTE stmt; DEALLOCATE PREPARE stmt;
