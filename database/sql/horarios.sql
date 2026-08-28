  -- Consulta de verificación
  SELECT ra.id, ra.user_id, ra.horario_id, ra.tipo, ra.fecha_hora, u.horario_id AS user_horario_id
  FROM biometricip_2.tbl_registros_asistencia ra
  JOIN biometricip.users u ON u.id = ra.user_id
  WHERE ra.fecha_hora >= '2026-08-26'
  ORDER BY ra.fecha_hora DESC LIMIT 20;

  -- Actualización con validación contra tbl_horarios
  UPDATE biometricip_2.tbl_registros_asistencia ra
  JOIN biometricip.users u ON u.id = ra.user_id
  JOIN biometricip_2.tbl_horarios h ON h.id = u.horario_id
  SET ra.horario_id = u.horario_id
  WHERE ra.horario_id IS NULL
    AND u.horario_id IS NOT NULL
    AND DATE(ra.fecha_hora) = '2026-08-26';


 SELECT u.id, u.name, u.horario_id, h.id AS horario_existe
  FROM biometricip.users u
  LEFT JOIN biometricip_1.tbl_horarios h ON h.id = u.horario_id
  WHERE u.horario_id IS NOT NULL;

  SELECT COUNT(*)
  FROM biometricip_1.tbl_registros_asistencia
  WHERE horario_id IS NULL;


-- este funciona para la base de datos biometricip_1, que es la que se usa en produccion
  UPDATE biometricip_1.tbl_registros_asistencia ra
  JOIN biometricip.users u ON u.id = ra.user_id
  JOIN biometricip_1.tbl_horarios h ON h.id = u.horario_id
  SET ra.horario_id = u.horario_id
  WHERE ra.horario_id IS NULL
    AND u.horario_id IS NOT NULL;
