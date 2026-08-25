-- Agrega 'whatsapp' al ENUM metodo en tbl_registros_asistencia
-- Ejecutar en CADA base de datos tenant (no en la central biometricip)
-- Ejemplo: USE tenant1; luego ejecutar el ALTER.

ALTER TABLE tbl_registros_asistencia
    MODIFY COLUMN metodo
        ENUM('qr','biometrico','reconocimiento_facial','foto','qr_web','manual','dispositivo','whatsapp')
        NOT NULL;
