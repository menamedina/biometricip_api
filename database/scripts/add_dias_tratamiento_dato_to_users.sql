ALTER TABLE `users`
    ADD COLUMN `dias_tratamiento_dato` INT NOT NULL DEFAULT 365
    AFTER `tratamiento_datos_at`;
