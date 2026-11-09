-- =====================================================================
-- Миграция: журнал действий (audit_log)
--   mysql -u <user> -p <db> < database/migrations/005_audit_log.sql
-- =====================================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS audit_log (
    id          INT UNSIGNED NOT NULL AUTO_INCREMENT,
    actor_id    INT UNSIGNED NULL,
    action      VARCHAR(64)  NOT NULL,
    entity_type VARCHAR(32)  NULL,
    entity_id   INT UNSIGNED NULL,
    meta        VARCHAR(500) NULL,
    ip          VARCHAR(45)  NOT NULL,
    created_at  DATETIME     NOT NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (id),
    KEY idx_audit_log_created (created_at),
    KEY idx_audit_log_actor (actor_id),
    KEY idx_audit_log_entity (entity_type, entity_id),
    CONSTRAINT fk_audit_log_actor FOREIGN KEY (actor_id) REFERENCES users (id) ON DELETE SET NULL
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Журнал действий (аудит)';
