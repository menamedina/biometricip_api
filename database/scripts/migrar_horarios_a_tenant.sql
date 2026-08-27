-- Migra los horarios y sus días desde la BD principal (biometricip)
-- hacia el tenant (biometricip_1).
-- Ejecutar en el contexto de la BD del tenant (biometricip_1) o ajustar prefijos.

-- 1. Insertar horarios (respetando los IDs originales)
INSERT INTO `biometricip_1`.`tbl_horarios` (`id`, `nombre`, `is_active`, `created_at`, `updated_at`)
SELECT `id`, `nombre`, `is_active`, `created_at`, `updated_at`
FROM `biometricip`.`tbl_horarios`
ON DUPLICATE KEY UPDATE
    `nombre`     = VALUES(`nombre`),
    `is_active`  = VALUES(`is_active`),
    `updated_at` = VALUES(`updated_at`);

-- 2. Insertar días de horario
INSERT INTO `biometricip_1`.`tbl_horario_dias`
    (`id`, `horario_id`, `dia_semana`, `hora_entrada`, `hora_salida`, `duracion_almuerzo_min`, `retardo_min`)
SELECT `id`, `horario_id`, `dia_semana`, `hora_entrada`, `hora_salida`, `duracion_almuerzo_min`, `retardo_min`
FROM `biometricip`.`tbl_horario_dias`
ON DUPLICATE KEY UPDATE
    `hora_entrada`          = VALUES(`hora_entrada`),
    `hora_salida`           = VALUES(`hora_salida`),
    `duracion_almuerzo_min` = VALUES(`duracion_almuerzo_min`),
    `retardo_min`           = VALUES(`retardo_min`);
