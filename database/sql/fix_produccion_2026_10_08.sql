-- ============================================================================
--  RIC_2026 · Cuentas fondeadoras por usuario · 2026-10-08
--  Ejecutar en phpMyAdmin sobre la base de datos correspondiente.
--  Haz respaldo antes de correr esto.
-- ============================================================================
--
-- El SUPERUSUARIO asigna a cada usuario qué cuentas fondeadoras puede ver
-- (igual que la asignación de empresas, pero filtrado por empresa).
-- Regla: un usuario SIN fondeadoras asignadas en una empresa ve todas las de
-- esa empresa; con al menos una asignada, sólo ve las asignadas.

CREATE TABLE IF NOT EXISTS `cuentas_fondeadoras_usuarios` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `id_usuario` BIGINT UNSIGNED NOT NULL,
    `id_cuenta` BIGINT UNSIGNED NOT NULL,
    `created_at` TIMESTAMP NULL DEFAULT NULL,
    `updated_at` TIMESTAMP NULL DEFAULT NULL,
    PRIMARY KEY (`id`),
    UNIQUE KEY `cfu_usuario_cuenta_unique` (`id_usuario`, `id_cuenta`),
    KEY `cfu_cuenta_index` (`id_cuenta`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
