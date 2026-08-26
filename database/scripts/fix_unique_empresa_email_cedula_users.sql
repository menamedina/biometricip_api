-- Reemplaza índices únicos simples por índices compuestos con empresa_id
-- en la tabla users para permitir el mismo email/cedula en empresas distintas
-- Ejecutar en la base de datos principal (biometricip)

-- Eliminar índices existentes
ALTER TABLE users DROP INDEX users_email_unique;
ALTER TABLE users DROP INDEX uq_empresa_cedula;
ALTER TABLE users DROP INDEX idx_users_cedula;

-- Agregar índices únicos compuestos
ALTER TABLE users ADD UNIQUE INDEX uq_empresa_email  (empresa_id, email);
ALTER TABLE users ADD UNIQUE INDEX uq_empresa_cedula (empresa_id, cedula);
