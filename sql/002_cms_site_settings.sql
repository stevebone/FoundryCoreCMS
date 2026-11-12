-- Site-wide settings editable from the admin panel. Rows are overrides:
-- a key absent here falls back to the value in config.php.
CREATE TABLE IF NOT EXISTS `cms`.`site_settings` (
    `key`        VARCHAR(64)  NOT NULL,
    `value`      VARCHAR(255) NULL,
    `updated_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
