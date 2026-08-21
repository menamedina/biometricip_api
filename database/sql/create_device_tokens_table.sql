-- Tabla para almacenar tokens FCM de dispositivos
-- Ejecutar en la base de datos principal (biometricip) y en cada tenant (biometricip_1, etc.)

CREATE TABLE IF NOT EXISTS `device_tokens` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    `user_id` BIGINT UNSIGNED NOT NULL,
    `token` VARCHAR(500) NOT NULL,
    `device_type` ENUM('android', 'ios', 'web') NOT NULL DEFAULT 'android',
    `device_name` VARCHAR(255) NULL,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    `updated_at` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (`user_id`) REFERENCES `users`(`id`) ON DELETE CASCADE,
    UNIQUE KEY `unique_token` (`token`(255))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


biometricip-3edc9
