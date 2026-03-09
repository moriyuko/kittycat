-- База и таблицы для лендинга (лиды + админы)
-- Перед запуском: замени имя базы/пользователя при необходимости.

CREATE DATABASE IF NOT EXISTS landing_db
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_unicode_ci;

USE landing_db;

CREATE TABLE IF NOT EXISTS landing_lead (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  name VARCHAR(100) NOT NULL,
  phone VARCHAR(30) NOT NULL,
  email VARCHAR(255) NOT NULL,
  comment TEXT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  KEY idx_created_at (created_at),
  KEY idx_email (email)
) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS landing_admin (
  id INT UNSIGNED NOT NULL AUTO_INCREMENT,
  login VARCHAR(64) NOT NULL,
  password_hash CHAR(32) NOT NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (id),
  UNIQUE KEY uq_login (login)
) ENGINE=InnoDB;

-- Пример админа: login=admin password=admin
INSERT INTO landing_admin (login, password_hash)
VALUES ('admin', MD5('admin'));
