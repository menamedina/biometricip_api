-- Agregar campos OTP para recuperación de contraseña desde la app móvil
-- OTP de 6 dígitos con expiración de 6 minutos

ALTER TABLE users
    ADD COLUMN otp_code VARCHAR(6) NULL AFTER remember_token,
    ADD COLUMN otp_expires_at TIMESTAMP NULL AFTER otp_code;
