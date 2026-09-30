-- create_full_schema.sql
CREATE DATABASE IF NOT EXISTS `org_chart` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `org_chart`;

-- positions
CREATE TABLE IF NOT EXISTS `positions` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(150) NOT NULL,
  `level` INT NULL,
  `description` TEXT NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- users (mínimo). Si ya tienes users en Laravel, omite/ajusta.
CREATE TABLE IF NOT EXISTS `users` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(150) NOT NULL,
  `email` VARCHAR(200) NOT NULL UNIQUE,
  `password` VARCHAR(255) NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- rooms (habitaciones)
CREATE TABLE IF NOT EXISTS `rooms` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `number` VARCHAR(50) NOT NULL,
  `type` VARCHAR(100) NULL,
  `capacity` INT DEFAULT 1,
  `price` DECIMAL(8,2) DEFAULT 0,
  `description` TEXT NULL,
  `meta` JSON NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- org_units (organigrama)
CREATE TABLE IF NOT EXISTS `org_units` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(200) NOT NULL,
  `slug` VARCHAR(200) NULL,
  `parent_id` BIGINT UNSIGNED NULL,
  `position_id` BIGINT UNSIGNED NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `order` INT NOT NULL DEFAULT 0,
  `meta` JSON NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  `deleted_at` TIMESTAMP NULL,
  INDEX (`parent_id`),
  INDEX (`parent_id`,`order`),
  CONSTRAINT `fk_org_position` FOREIGN KEY (`position_id`) REFERENCES `positions`(`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_org_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- self-referential FK (parent_id). Si falla por orden, ejecuta el ALTER después.
ALTER TABLE `org_units`
  ADD CONSTRAINT `fk_org_parent` FOREIGN KEY (`parent_id`) REFERENCES `org_units`(`id`) ON DELETE SET NULL;

-- reservations (reservas)
CREATE TABLE IF NOT EXISTS `reservations` (
  `id` BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
  `room_id` BIGINT UNSIGNED NOT NULL,
  `user_id` BIGINT UNSIGNED NULL,
  `start_date` DATE NOT NULL,
  `end_date` DATE NOT NULL,
  `status` ENUM('pending','confirmed','cancelled') DEFAULT 'pending',
  `total` DECIMAL(10,2) NULL,
  `meta` JSON NULL,
  `created_at` TIMESTAMP NULL,
  `updated_at` TIMESTAMP NULL,
  CONSTRAINT `fk_res_room` FOREIGN KEY (`room_id`) REFERENCES `rooms`(`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_res_user` FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE SET NULL,
  INDEX (`room_id`,`start_date`,`end_date`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Datos de ejemplo: posiciones mínimas
INSERT INTO `positions` (`title`,`level`,`description`,`created_at`,`updated_at`)
  VALUES ('CEO',1,'Director general',NOW(),NOW()),
         ('CTO',2,'Director técnico',NOW(),NOW()),
         ('Developer',4,'Desarrollador',NOW(),NOW())
  ON DUPLICATE KEY UPDATE title=VALUES(title);

-- Inserción de 40 habitaciones de ejemplo (si ya las insertaste antes, ignora o elimina duplicados)
INSERT INTO `rooms` (`number`,`type`,`capacity`,`price`,`description`,`created_at`,`updated_at`) VALUES
('101','Single',1,30.00,'Habitación individual económica',NOW(),NOW()),
('102','Single',1,30.00,'Habitación individual económica',NOW(),NOW()),
('103','Double',2,50.00,'Habitación doble estándar',NOW(),NOW()),
('104','Double',2,50.00,'Habitación doble estándar',NOW(),NOW()),
('105','Double',2,55.00,'Habitación doble con vista',NOW(),NOW()),
('106','Double',2,55.00,'Habitación doble con vista',NOW(),NOW()),
('107','Suite',4,120.00,'Suite con salón',NOW(),NOW()),
('108','Suite',4,130.00,'Suite premium',NOW(),NOW()),
('109','Single',1,32.00,'Individual con ventana',NOW(),NOW()),
('110','Double',2,60.00,'Doble superior',NOW(),NOW()),
('111','Family',4,140.00,'Familiar con 2 camas',NOW(),NOW()),
('112','Family',4,150.00,'Familiar deluxe',NOW(),NOW()),
('113','Single',1,28.00,'Económica sin vista',NOW(),NOW()),
('114','Double',2,48.00,'Doble económica',NOW(),NOW()),
('115','Suite',3,110.00,'Suite junior',NOW(),NOW()),
('116','Single',1,35.00,'Individual con balcón',NOW(),NOW()),
('117','Double',2,58.00,'Doble con balcón',NOW(),NOW()),
('118','Double',2,52.00,'Doble estándar',NOW(),NOW()),
('119','Single',1,29.00,'Individual económica',NOW(),NOW()),
('120','Suite',4,160.00,'Suite presidencial',NOW(),NOW()),
('201','Single',1,33.00,'Individual planta alta',NOW(),NOW()),
('202','Double',2,54.00,'Doble planta alta',NOW(),NOW()),
('203','Double',2,56.00,'Doble con vista ciudad',NOW(),NOW()),
('204','Suite',4,125.00,'Suite con vistas',NOW(),NOW()),
('205','Family',4,145.00,'Familiar planta alta',NOW(),NOW()),
('206','Single',1,31.00,'Individual estándar',NOW(),NOW()),
('207','Double',2,49.00,'Doble económica',NOW(),NOW()),
('208','Double',2,59.00,'Doble superior',NOW(),NOW()),
('209','Single',1,27.00,'Individual económica',NOW(),NOW()),
('210','Suite',3,115.00,'Suite junior',NOW(),NOW()),
('211','Single',1,30.00,'Individual',NOW(),NOW()),
('212','Double',2,50.00,'Doble',NOW(),NOW()),
('213','Double',2,52.00,'Doble',NOW(),NOW()),
('214','Family',5,155.00,'Familiar grande',NOW(),NOW()),
('215','Suite',4,135.00,'Suite premium',NOW(),NOW()),
('216','Single',1,29.00,'Individual',NOW(),NOW()),
('217','Double',2,51.00,'Doble estándar',NOW(),NOW()),
('218','Double',2,53.00,'Doble con balcón',NOW(),NOW()),
('219','Single',1,26.00,'Individual económica',NOW(),NOW()),
('220','Suite',4,170.00,'Suite ejecutiva',NOW(),NOW());