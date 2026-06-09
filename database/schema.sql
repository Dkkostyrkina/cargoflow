CREATE DATABASE IF NOT EXISTS `cargoflow` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `cargoflow`;

-- ═══ Заявки с сайта (лиды) ═══

CREATE TABLE IF NOT EXISTS `leads` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` DATETIME NOT NULL,
  `name` VARCHAR(190) DEFAULT NULL,
  `email` VARCHAR(190) NOT NULL,
  `direction` VARCHAR(255) NOT NULL,
  `transport_type` VARCHAR(32) DEFAULT NULL,
  `cargo_description` TEXT DEFAULT NULL,
  `comment` TEXT DEFAULT NULL,
  `source` VARCHAR(32) DEFAULT 'web',
  PRIMARY KEY (`id`),
  KEY `idx_created_at` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══ Пользователи ═══

CREATE TABLE IF NOT EXISTS `users` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `email` VARCHAR(190) NOT NULL,
  `password_hash` VARCHAR(255) NOT NULL,
  `full_name` VARCHAR(255) DEFAULT NULL,
  `company` VARCHAR(255) DEFAULT NULL,
  `phone` VARCHAR(64) DEFAULT NULL,
  `role` ENUM('client', 'admin') NOT NULL DEFAULT 'client',
  `email_verified` TINYINT(1) NOT NULL DEFAULT 0,
  `confirm_token` VARCHAR(64) DEFAULT NULL,
  `confirm_token_at` DATETIME DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_email` (`email`),
  KEY `idx_confirm_token` (`confirm_token`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══ Заявки на перевозку (личный кабинет) ═══

CREATE TABLE IF NOT EXISTS `applications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `user_id` INT UNSIGNED NOT NULL,
  `country_from` VARCHAR(120) NOT NULL,
  `city_from` VARCHAR(120) NOT NULL,
  `country_to` VARCHAR(120) NOT NULL,
  `city_to` VARCHAR(120) NOT NULL,
  `transport_type` ENUM('air','sea','road','rail','multi') NOT NULL DEFAULT 'sea',
  `cargo_type` VARCHAR(190) DEFAULT NULL,
  `weight_kg` DECIMAL(12,2) DEFAULT NULL,
  `volume_cbm` DECIMAL(12,3) DEFAULT NULL,
  `comment` TEXT DEFAULT NULL,
  `status` TINYINT UNSIGNED NOT NULL DEFAULT 1
    COMMENT '1=Новая, 2=В обработке, 3=Документы проверены, 4=В пути, 5=Завершена',
  PRIMARY KEY (`id`),
  KEY `idx_user` (`user_id`),
  KEY `idx_status` (`status`),
  KEY `idx_created` (`created_at`),
  CONSTRAINT `fk_app_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══ Документы ═══

CREATE TABLE IF NOT EXISTS `documents` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `application_id` INT UNSIGNED NOT NULL,
  `file_name` VARCHAR(255) NOT NULL,
  `file_path` VARCHAR(500) NOT NULL,
  `file_size` INT UNSIGNED DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_app` (`application_id`),
  CONSTRAINT `fk_doc_app` FOREIGN KEY (`application_id`) REFERENCES `applications` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══ Старая таблица orders (обратная совместимость) ═══

CREATE TABLE IF NOT EXISTS `orders` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created_at` DATETIME NOT NULL,
  `client_id` INT UNSIGNED NOT NULL,
  `lead_id` INT UNSIGNED DEFAULT NULL,
  `direction` VARCHAR(255) NOT NULL,
  `transport_type` VARCHAR(32) NOT NULL,
  `status` ENUM('new', 'in_progress', 'completed', 'cancelled') NOT NULL DEFAULT 'new',
  PRIMARY KEY (`id`),
  KEY `idx_client` (`client_id`),
  CONSTRAINT `fk_orders_client` FOREIGN KEY (`client_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_orders_lead` FOREIGN KEY (`lead_id`) REFERENCES `leads` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ═══ Триггер: автообновление updated_at ═══

DROP TRIGGER IF EXISTS `trg_app_status_change`;
DELIMITER $$
CREATE TRIGGER `trg_app_status_change`
BEFORE UPDATE ON `applications`
FOR EACH ROW
BEGIN
  IF OLD.status <> NEW.status THEN
    SET NEW.updated_at = NOW();
  END IF;
END$$
DELIMITER ;

-- ═══ Демо-данные ═══
-- Пароли:
--   demo@cargoflow.ru  -> demo
--   admin@cargoflow.ru -> admin123

INSERT IGNORE INTO `users` (`id`, `created_at`, `email`, `password_hash`, `full_name`, `company`, `phone`, `role`)
VALUES (1, NOW(), 'demo@cargoflow.ru', '$2y$12$rLUrDPZkJbtVMWjKIW31keIl9V2RwLGJUPmLWH4i3yD9Ws/RTB6Eq', 'Иванов Алексей Петрович', 'ООО «ТрансЛогистик»', '+7 (495) 123-45-67', 'client');

INSERT IGNORE INTO `users` (`id`, `created_at`, `email`, `password_hash`, `full_name`, `company`, `phone`, `role`)
VALUES (2, NOW(), 'admin@cargoflow.ru', '$2y$12$WId3e/Ei5oNN3r/COFslbOPg0xo8oEniEKdrp3bytEM2OL3pryXzS', 'Администратор', 'CargoFlow', '+7 (495) 000-00-00', 'admin');

-- ═══ Демонстрационные заявки ═══

INSERT IGNORE INTO `applications` (`id`, `created_at`, `updated_at`, `user_id`, `country_from`, `city_from`, `country_to`, `city_to`, `transport_type`, `cargo_type`, `weight_kg`, `volume_cbm`, `comment`, `status`)
VALUES
(1, '2026-03-15 10:30:00', '2026-03-15 10:30:00', 1, 'Китай', 'Шанхай', 'Россия', 'Москва', 'sea', 'Электроника', 12500.00, 28.500, 'FCL 40-футовый контейнер', 4),
(2, '2026-02-20 14:15:00', '2026-02-20 14:15:00', 1, 'Турция', 'Стамбул', 'Россия', 'Новороссийск', 'sea', 'Текстиль', 8200.00, 18.200, 'LCL сборный груз', 5),
(3, '2026-04-01 09:45:00', '2026-04-01 09:45:00', 1, 'Германия', 'Гамбург', 'Россия', 'Санкт-Петербург', 'road', 'Автозапчасти', 3400.00, 12.000, 'FTL еврофура', 3),
(4, '2026-04-10 16:20:00', '2026-04-10 16:20:00', 1, 'Китай', 'Гуанчжоу', 'Россия', 'Владивосток', 'air', 'Образцы продукции', 450.00, 2.100, 'Срочная авиадоставка', 2),
(5, '2026-04-25 11:00:00', '2026-04-25 11:00:00', 1, 'Италия', 'Милан', 'Россия', 'Москва', 'road', 'Мебель', 6800.00, 45.000, 'Тентованный полуприцеп', 1);
