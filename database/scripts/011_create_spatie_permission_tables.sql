-- ============================================================
-- Script: 011_create_spatie_permission_tables.sql
-- Descripción: Tablas de Spatie Laravel Permission para BiometricIP
-- BD: biometricip (central, NO tenant)
-- Ejecutar en: biometricip
-- ============================================================

USE `biometricip`;

-- ── 1. permissions ───────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `permissions` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(125)    NOT NULL,
    `guard_name` VARCHAR(125)    NOT NULL,
    `created_at` TIMESTAMP       NULL DEFAULT NULL,
    `updated_at` TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `permissions_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 2. roles ─────────────────────────────────────────────────
CREATE TABLE IF NOT EXISTS `roles` (
    `id`         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`       VARCHAR(125)    NOT NULL,
    `guard_name` VARCHAR(125)    NOT NULL,
    `created_at` TIMESTAMP       NULL DEFAULT NULL,
    `updated_at` TIMESTAMP       NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `roles_name_guard_name_unique` (`name`, `guard_name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 3. model_has_permissions ─────────────────────────────────
CREATE TABLE IF NOT EXISTS `model_has_permissions` (
    `permission_id` BIGINT UNSIGNED NOT NULL,
    `model_type`    VARCHAR(125)    NOT NULL,
    `model_id`      BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`permission_id`, `model_id`, `model_type`),
    KEY `model_has_permissions_model_id_model_type_index` (`model_id`, `model_type`),
    CONSTRAINT `fk_mhp_permission`
        FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 4. model_has_roles ───────────────────────────────────────
CREATE TABLE IF NOT EXISTS `model_has_roles` (
    `role_id`    BIGINT UNSIGNED NOT NULL,
    `model_type` VARCHAR(125)    NOT NULL,
    `model_id`   BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`role_id`, `model_id`, `model_type`),
    KEY `model_has_roles_model_id_model_type_index` (`model_id`, `model_type`),
    CONSTRAINT `fk_mhr_role`
        FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 5. role_has_permissions ──────────────────────────────────
CREATE TABLE IF NOT EXISTS `role_has_permissions` (
    `permission_id` BIGINT UNSIGNED NOT NULL,
    `role_id`       BIGINT UNSIGNED NOT NULL,
    PRIMARY KEY (`permission_id`, `role_id`),
    CONSTRAINT `fk_rhp_permission`
        FOREIGN KEY (`permission_id`) REFERENCES `permissions` (`id`) ON DELETE CASCADE,
    CONSTRAINT `fk_rhp_role`
        FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── Registrar en tbl_admin_tenant ────────────────────────────
INSERT INTO `tbl_admin_tenant`
    (`nombre_tabla`, `descripcion`, `es_bd_central`, `copiar_estructura`, `copiar_datos`, `activo`, `orden`, `created_at`, `updated_at`)
VALUES
    ('permissions',           'Permisos del sistema (Spatie)',                   1, 0, 0, 1, 200, NOW(), NOW()),
    ('roles',                 'Roles del sistema (Spatie)',                      1, 0, 0, 1, 201, NOW(), NOW()),
    ('model_has_permissions', 'Relación modelo-permiso (Spatie)',                1, 0, 0, 1, 202, NOW(), NOW()),
    ('model_has_roles',       'Relación modelo-rol (Spatie)',                    1, 0, 0, 1, 203, NOW(), NOW()),
    ('role_has_permissions',  'Relación rol-permiso (Spatie)',                   1, 0, 0, 1, 204, NOW(), NOW());
