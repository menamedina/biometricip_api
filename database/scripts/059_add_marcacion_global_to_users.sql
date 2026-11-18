-- Agrega el campo marcacion_global a la tabla users
-- Permite autorizar a usuarios para marcar desde cualquier ubicación (sin geocerca)

ALTER TABLE `users`
    ADD COLUMN `marcacion_global` TINYINT(1) NOT NULL DEFAULT 0 AFTER `ver_todos`;
