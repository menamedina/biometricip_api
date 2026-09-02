-- ============================================================
-- DIAGNÓSTICO: Filtro de visitantes por fecha
-- Ejecutar en la BD TENANT (ej: biometricip_1)
-- ============================================================

-- 1. Estructura de hora_entrada
SHOW COLUMNS FROM tbl_visitantes LIKE 'hora_entrada';

-- 2. Zona horaria del servidor MySQL
SELECT @@global.time_zone, @@session.time_zone;

-- 3. Muestra de valores reales almacenados (últimos 5)
SELECT id, hora_entrada, DATE(hora_entrada) AS solo_fecha
FROM tbl_visitantes
ORDER BY id DESC
LIMIT 5;

-- 4. Prueba del WHERE que usa Laravel (ajusta la fecha)
SELECT COUNT(*) AS total_en_rango
FROM tbl_visitantes
WHERE DATE(hora_entrada) >= '2025-09-01'
  AND DATE(hora_entrada) <= '2026-09-02';

-- 5. Total de registros sin filtro
SELECT COUNT(*) AS total_sin_filtro FROM tbl_visitantes;

-- 6. Distribución por fecha
SELECT DATE(hora_entrada) AS fecha, COUNT(*) AS cantidad
FROM tbl_visitantes
GROUP BY DATE(hora_entrada)
ORDER BY fecha DESC
LIMIT 10;
