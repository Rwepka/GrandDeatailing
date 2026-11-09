-- =====================================================================
-- Миграция: назначение мастера на заявку (bookings.master_id)
--   mysql -u <user> -p <db> < database/migrations/006_booking_master.sql
-- =====================================================================

SET NAMES utf8mb4;

SET @has_col := (
    SELECT COUNT(*)
    FROM information_schema.columns
    WHERE table_schema = DATABASE()
      AND table_name = 'bookings'
      AND column_name = 'master_id'
);

SET @ddl := IF(
    @has_col = 0,
    'ALTER TABLE bookings
        ADD COLUMN master_id INT UNSIGNED NULL AFTER user_id,
        ADD KEY idx_bookings_master (master_id),
        ADD CONSTRAINT fk_bookings_master FOREIGN KEY (master_id) REFERENCES users (id) ON DELETE SET NULL',
    'DO 0'
);

PREPARE stmt FROM @ddl;
EXECUTE stmt;
DEALLOCATE PREPARE stmt;
