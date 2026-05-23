
--aun no esta en uso no tener en cuenta
-- Configurar empresa con ID 1 (reemplazar empresa_id según corresponda)
INSERT INTO tbl_empresa_mail_config
    (empresa_id, mailer, host, port, encryption, username, password, from_address, from_name, activo, created_at, updated_at)
VALUES
    (
        1,                                -- <EMPRESA_ID>
        'smtp',                           -- mailer: smtp | ses | postmark | resend | log
        'smtp.ejemplo.com',               -- <SMTP_HOST>  ej: smtp.gmail.com, smtp.office365.com
        587,                              -- <SMTP_PORT>  587=TLS, 465=SSL, 25=sin cifrado
        'tls',                            -- <ENCRYPTION> tls | ssl | none
        'correo@tuempresa.com',           -- <SMTP_USERNAME>
        'REEMPLAZAR_CON_encrypt()',        -- <SMTP_PASSWORD encriptado con Laravel encrypt()>
        'no-reply@tuempresa.com',         -- <FROM_ADDRESS>
        'Nombre Empresa',                 -- <FROM_NAME>
        1,                                -- activo: 1=sí, 0=no
        NOW(),
        NOW()
    )
ON DUPLICATE KEY UPDATE
    mailer       = VALUES(mailer),
    host         = VALUES(host),
    port         = VALUES(port),
    encryption   = VALUES(encryption),
    username     = VALUES(username),
    password     = VALUES(password),
    from_address = VALUES(from_address),
    from_name    = VALUES(from_name),
    activo       = VALUES(activo),
    updated_at   = NOW();

-- ============================================================
-- Verificación: listar configuraciones activas
-- ============================================================
SELECT
    e.nombre       AS empresa,
    m.mailer,
    m.host,
    m.port,
    m.encryption,
    m.username,
    m.from_address,
    m.from_name,
    IF(m.activo, 'SÍ', 'NO') AS activo
FROM tbl_empresa_mail_config m
JOIN tbl_empresas e ON e.id = m.empresa_id
ORDER BY e.nombre;


  ALTER TABLE tbl_empresa_mail_config
      MODIFY COLUMN password TEXT NULL
      COMMENT 'Encriptado con encrypt() de Laravel — no guardar en texto plano';
