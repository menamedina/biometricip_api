-- Agrega el campo 'tipo' a la tabla users (BD central: biometricip)
-- Valores: 'usuario' (app personal) | 'kiosco' (dispositivo compartido con reconocimiento facial)
-- Ejecutar en la base de datos: biometricip

ALTER TABLE users
    ADD COLUMN tipo ENUM('usuario', 'kiosco') NOT NULL DEFAULT 'usuario' AFTER role;
