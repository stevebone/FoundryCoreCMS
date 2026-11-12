-- CMS-owned database: site features keep their data here, separate from
-- the TrinityCore databases (auth/world/characters).
CREATE DATABASE IF NOT EXISTS `cms` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

-- Per-account CMS preferences. NULL = "use site default"; a row exists only
-- after the user saves settings. FK cascades with the battlenet account.
CREATE TABLE IF NOT EXISTS `cms`.`account_settings` (
    `battlenet_account` INT UNSIGNED NOT NULL,
    `lang`            VARCHAR(8)  NULL DEFAULT NULL,
    `theme`           VARCHAR(32) NULL DEFAULT NULL,
    `bg`              VARCHAR(64) NULL DEFAULT NULL,
    `timezone`        VARCHAR(64) NULL DEFAULT NULL,
    `updated_at`      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    PRIMARY KEY (`battlenet_account`),
    CONSTRAINT `fk_acct_settings_bnet`
        FOREIGN KEY (`battlenet_account`)
        REFERENCES `auth`.`battlenet_accounts` (`id`)
        ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
