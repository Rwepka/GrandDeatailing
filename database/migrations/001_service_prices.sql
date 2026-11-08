-- =====================================================================
-- Миграция: цены услуг по типам кузова (service_prices)
-- Применяется к уже развёрнутой базе: mysql -u root -p < database/migrations/001_service_prices.sql
-- =====================================================================

SET NAMES utf8mb4;

CREATE TABLE IF NOT EXISTS service_prices (
    id         INT UNSIGNED     NOT NULL AUTO_INCREMENT,
    service_id INT UNSIGNED     NOT NULL,
    body_type  VARCHAR(60)      NOT NULL,
    price      DECIMAL(10, 2)   NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_service_prices_service_body (service_id, body_type),
    CONSTRAINT fk_service_prices_service FOREIGN KEY (service_id) REFERENCES services (id) ON DELETE CASCADE
) ENGINE = InnoDB
  DEFAULT CHARACTER SET = utf8mb4
  COLLATE = utf8mb4_unicode_ci
  COMMENT = 'Цены услуги по типу кузова';
