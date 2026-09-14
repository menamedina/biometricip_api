 SELECT
      r1.id        AS id_salida,
      r2.id        AS id_entrada,
      r1.user_id,
      s1.nombre    AS sede_salida,
      s2.nombre    AS sede_entrada,
      r1.fecha_hora,
      r1.metodo
  FROM tbl_registros_asistencia r1
  JOIN tbl_registros_asistencia r2
      ON  r2.user_id    = r1.user_id
      AND r2.tipo       = 'entrada'
      AND r2.fecha_hora = r1.fecha_hora
  JOIN tbl_sedes s1 ON s1.id = r1.sede_id
  JOIN tbl_sedes s2 ON s2.id = r2.sede_id
  WHERE r1.tipo   = 'salida'
    AND r1.user_id = 2
  ORDER BY r1.fecha_hora DESC
  LIMIT 20;


  SELECT
      id,
      user_id,
      sede_id,
      (SELECT nombre FROM tbl_sedes WHERE id = r.sede_id) AS sede,
      tipo,
      metodo,
      fecha_hora
  FROM tbl_registros_asistencia r
  WHERE user_id = 2 AND DATE(fecha_hora) = '2026-09-14';
  ORDER BY fecha_hora DESC
  LIMIT 20;

  DELETE FROM tbl_registros_asistencia
  WHERE user_id = 2
    AND DATE(fecha_hora) = '2026-09-14';

DELETE FROM tbl_registros_asistencia
  WHERE id in (2100,2101,2102,2103,2104,2105,2106);

DELETE FROM tbl_registros_asistencia_log
  WHERE registro_id in (2100,2101,2102,2103,2104,2105,2106);

