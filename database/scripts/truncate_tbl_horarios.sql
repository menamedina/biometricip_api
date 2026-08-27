-- Elimina todos los registros de tbl_horarios y reinicia el auto_increment a 1
DELETE FROM tbl_horarios;
ALTER TABLE tbl_horarios AUTO_INCREMENT = 1;
