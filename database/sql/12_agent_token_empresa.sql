-- ------------------------------------------------------------
-- Script 12: Agregar token de agente local a tbl_empresas
-- BD: biometricip (central)
-- ------------------------------------------------------------

USE biometricip;

ALTER TABLE `tbl_empresas`
    ADD COLUMN `agent_token`          VARCHAR(64)  NULL COMMENT 'Token de autenticación del agente local' AFTER `is_active`,
    ADD COLUMN `agent_token_vigencia` TIMESTAMP    NULL COMMENT 'Fecha de expiración del token del agente' AFTER `agent_token`;

-- Índice para búsqueda rápida por token
ALTER TABLE `tbl_empresas`
    ADD UNIQUE INDEX `idx_agent_token` (`agent_token`);
