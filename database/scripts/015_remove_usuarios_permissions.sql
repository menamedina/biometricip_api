-- ============================================================
-- Script: 015_remove_usuarios_permissions.sql
-- Descripción: Elimina permisos del módulo 'usuarios' (vista no implementada)
-- BD: biometricip (central)
-- ============================================================

USE `biometricip`;

-- Quitar asignaciones de roles
DELETE FROM `role_has_permissions`
WHERE `permission_id` IN (
    SELECT `id` FROM `permissions`
    WHERE `name` IN ('usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar')
);

-- Eliminar los permisos
DELETE FROM `permissions`
WHERE `name` IN ('usuarios.ver', 'usuarios.crear', 'usuarios.editar', 'usuarios.eliminar');
