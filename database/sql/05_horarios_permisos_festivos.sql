-- ============================================================
-- 05_horarios_permisos_festivos.sql
-- Ejecutar estando dentro de la BD del tenant (ej: biometricip_1)
-- El ALTER de users se ejecuta en biometricip
-- ============================================================

-- ── 1. Snapshot del horario en cada marcación ────────────────
ALTER TABLE tbl_registros_asistencia
    ADD COLUMN horario_id BIGINT UNSIGNED NULL DEFAULT NULL AFTER sede_id;

-- ── 2. Horario asignado al empleado (BD central) ─────────────
ALTER TABLE users
    ADD COLUMN horario_id BIGINT UNSIGNED NULL DEFAULT NULL AFTER cargo_id;

-- ── 3. tbl_horarios ──────────────────────────────────────────
CREATE TABLE tbl_horarios (
    id                    BIGINT UNSIGNED   NOT NULL AUTO_INCREMENT,
    nombre                VARCHAR(100)      NOT NULL,
    hora_entrada          TIME              NOT NULL,
    hora_salida           TIME              NOT NULL,
    duracion_almuerzo_min SMALLINT UNSIGNED NULL DEFAULT NULL
        COMMENT 'Minutos a descontar del total de horas. NULL = no descuenta.',
    is_active             TINYINT(1)        NOT NULL DEFAULT 1,
    created_at            TIMESTAMP         NULL,
    updated_at            TIMESTAMP         NULL,
    PRIMARY KEY (id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 4. tbl_permisos ──────────────────────────────────────────
CREATE TABLE tbl_permisos (
    id            BIGINT UNSIGNED  NOT NULL AUTO_INCREMENT,
    user_id       BIGINT UNSIGNED  NOT NULL
        COMMENT 'Referencia a biometricip.users (sin FK cross-DB)',
    fecha         DATE             NOT NULL,
    tipo          ENUM(
                      'salida_temprana',
                      'llegada_tarde',
                      'dia_completo',
                      'horas'
                  )                NOT NULL,
    horas_permiso DECIMAL(4,2)     NOT NULL DEFAULT 0.00,
    motivo        TEXT             NULL,
    estado        ENUM('pendiente','aprobado','rechazado') NOT NULL DEFAULT 'pendiente',
    aprobado_por  BIGINT UNSIGNED  NULL DEFAULT NULL
        COMMENT 'user_id del admin que aprobó/rechazó',
    created_at    TIMESTAMP        NULL,
    updated_at    TIMESTAMP        NULL,
    PRIMARY KEY (id),
    INDEX idx_permisos_user_fecha (user_id, fecha),
    INDEX idx_permisos_estado     (estado)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 5. tbl_festivos ──────────────────────────────────────────
CREATE TABLE tbl_festivos (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    fecha      DATE            NOT NULL,
    nombre     VARCHAR(255)    NOT NULL,
    is_active  TINYINT(1)      NOT NULL DEFAULT 1,
    created_at TIMESTAMP       NULL,
    updated_at TIMESTAMP       NULL,
    PRIMARY KEY (id),
    UNIQUE KEY uq_festivos_fecha (fecha)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ── 6. Datos de ejemplo — Horarios ───────────────────────────
INSERT INTO tbl_horarios (nombre, hora_entrada, hora_salida, duracion_almuerzo_min, is_active) VALUES
    ('Turno Completo (con descuento almuerzo)', '07:00:00', '17:00:00', 60,   1),
    ('Turno Completo (sin descuento almuerzo)', '07:00:00', '17:00:00', NULL, 1),
    ('Medio Tiempo Mañana',                     '07:00:00', '12:00:00', NULL, 1),
    ('Turno Tarde',                             '14:00:00', '22:00:00', 30,   1),
    ('Turno Nocturno',                          '22:00:00', '06:00:00', NULL, 1);

-- ── 7. Datos de ejemplo — Festivos Colombia 2025/2026 ────────
INSERT IGNORE INTO tbl_festivos (fecha, nombre, is_active) VALUES
    ('2025-01-01', 'Año Nuevo',                        1),
    ('2025-01-06', 'Día de Reyes',                     1),
    ('2025-03-24', 'Día de San José',                  1),
    ('2025-04-17', 'Jueves Santo',                     1),
    ('2025-04-18', 'Viernes Santo',                    1),
    ('2025-05-01', 'Día del Trabajo',                  1),
    ('2025-06-02', 'Día de la Ascensión',              1),
    ('2025-06-23', 'Corpus Christi',                   1),
    ('2025-06-30', 'Sagrado Corazón de Jesús',         1),
    ('2025-07-07', 'San Pedro y San Pablo',            1),
    ('2025-07-20', 'Día de la Independencia',          1),
    ('2025-08-07', 'Batalla de Boyacá',                1),
    ('2025-08-18', 'La Asunción de la Virgen',         1),
    ('2025-10-13', 'Día de la Raza',                   1),
    ('2025-11-03', 'Todos los Santos',                 1),
    ('2025-11-17', 'Independencia de Cartagena',       1),
    ('2025-12-08', 'Día de la Inmaculada Concepción',  1),
    ('2025-12-25', 'Navidad',                          1),
    ('2026-01-01', 'Año Nuevo',                        1),
    ('2026-01-12', 'Día de Reyes',                     1),
    ('2026-03-23', 'Día de San José',                  1),
    ('2026-04-02', 'Jueves Santo',                     1),
    ('2026-04-03', 'Viernes Santo',                    1),
    ('2026-05-01', 'Día del Trabajo',                  1),
    ('2026-07-20', 'Día de la Independencia',          1),
    ('2026-08-07', 'Batalla de Boyacá',                1),
    ('2026-12-25', 'Navidad',                          1);
