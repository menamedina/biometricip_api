-- Agrega índice único compuesto (empresa_id, cedula) en la tabla users
-- Evita que el mismo empleado se registre dos veces en la misma empresa
-- Ejecutar en la base de datos principal (biometricip)

ALTER TABLE users
    ADD UNIQUE INDEX uq_empresa_cedula (empresa_id, cedula);
