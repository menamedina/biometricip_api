-- Agrega soporte para QR estático imprimible en tbl_sedes
-- Ejecutar manualmente en cada base tenant (ej: biometricip_1)
-- qr_static_token NULL = QR estático deshabilitado para esta sede

ALTER TABLE tbl_sedes
    ADD COLUMN qr_static_token VARCHAR(64) NULL DEFAULT NULL
    COMMENT 'Token para QR estático imprimible. NULL = deshabilitado. Regenerar para revocar QRs impresos.'
    AFTER secret_key;
