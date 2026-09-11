-- ============================================================
-- Script: 022_remove_asistencia_editar_permission.sql
-- Descripción: Elimina permiso asistencia.editar (sin uso en UI)
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

DELETE FROM `role_has_permissions`
WHERE `permission_id` = (SELECT id FROM `permissions` WHERE `name` = 'asistencia.editar');

DELETE FROM `model_has_permissions`
WHERE `permission_id` = (SELECT id FROM `permissions` WHERE `name` = 'asistencia.editar');

DELETE FROM `permissions` WHERE `name` = 'asistencia.editar';
