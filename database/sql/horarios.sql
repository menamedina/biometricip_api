  SELECT id, user_id, horario_id, tipo, fecha_hora
  FROM biometricip_2.tbl_registros_asistencia
  WHERE fecha_hora >= '2026-08-26'
  ORDER BY fecha_hora DESC LIMIT 20;

	 UPDATE biometricip_2.tbl_registros_asistencia ra
  JOIN biometricip.users u ON u.id = ra.user_id
  SET ra.horario_id = u.horario_id
  WHERE ra.horario_id IS NULL
    AND u.horario_id IS NOT NULL
    AND DATE(ra.fecha_hora) = '2026-08-26';
