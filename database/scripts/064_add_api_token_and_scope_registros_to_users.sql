-- 064: Agregar api_token y scope_registros a la tabla users
-- Fecha: 2026-11-28
-- Propósito: Token único por usuario para autenticación de API externa
--            y campo de visibilidad de registros (empresa/equipo/personal)

ALTER TABLE `users`
    ADD COLUMN `api_token` VARCHAR(64) NULL UNIQUE
        COMMENT 'Token único por usuario para autenticación de API externa' AFTER `marcacion_global`,
    ADD COLUMN `scope_registros` ENUM('empresa','equipo','personal') NOT NULL DEFAULT 'personal'
        COMMENT 'Visibilidad de registros: empresa=todos, equipo=lider+subordinados, personal=solo el propio'
        AFTER `api_token`;

-- Migrar valor actual de ver_todos al nuevo scope
UPDATE `users` SET `scope_registros` = 'empresa' WHERE `ver_todos` = 1;
