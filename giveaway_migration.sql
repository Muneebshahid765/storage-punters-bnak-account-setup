-- ============================================================
-- Storage Punters: Giveaway & Subscription System Migration
-- ============================================================

-- 1. Subscription Tiers Lookup Table
CREATE TABLE IF NOT EXISTS `tbl_subscription_tiers` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `tier_key` VARCHAR(20) NOT NULL UNIQUE,
    `tier_name` VARCHAR(50) NOT NULL,
    `price_monthly_cents` INT NOT NULL DEFAULT 0,
    `base_entries` INT NOT NULL DEFAULT 0,
    `fee_rate` DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    `coin_multiplier` DECIMAL(3,1) NOT NULL DEFAULT 1.0,
    `stripe_product_id` VARCHAR(100) DEFAULT NULL,
    `stripe_price_id` VARCHAR(100) DEFAULT NULL,
    `icon_class` VARCHAR(50) DEFAULT 'bi-trophy',
    `sort_order` INT NOT NULL DEFAULT 0,
    `is_active` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert default tiers
INSERT INTO `tbl_subscription_tiers` (`tier_key`, `tier_name`, `price_monthly_cents`, `base_entries`, `fee_rate`, `coin_multiplier`, `icon_class`, `sort_order`) VALUES
('player', 'Players', 900, 1, 10.00, 0.5, 'bi-trophy', 1),
('shark', 'Sharks', 3900, 5, 0.00, 1.0, 'bi-award', 2),
('whale', 'Whales', 9900, 20, 0.00, 2.0, 'bi-gem', 3);

-- 2. Streak Milestone Multipliers
CREATE TABLE IF NOT EXISTS `tbl_streak_milestones` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `month_threshold` INT NOT NULL,
    `multiplier` DECIMAL(4,2) NOT NULL DEFAULT 1.00,
    `label` VARCHAR(50) DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbl_streak_milestones` (`month_threshold`, `multiplier`, `label`) VALUES
(1, 1.00, 'Month 1 - Base'),
(3, 2.00, 'Month 3 - Double'),
(6, 2.00, 'Month 6 - Double'),
(12, 2.00, 'Month 12 - Double'),
(18, 1.50, 'Month 18 - 1.5x'),
(24, 2.00, 'Month 24 - Double');

-- 3. User Punter Subscriptions
CREATE TABLE IF NOT EXISTS `tbl_punter_subscriptions` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `tier_id` INT NOT NULL,
    `stripe_customer_id` VARCHAR(100) DEFAULT NULL,
    `stripe_subscription_id` VARCHAR(100) DEFAULT NULL,
    `status` ENUM('active','cancelled','expired','past_due','trialing') NOT NULL DEFAULT 'active',
    `cancel_at_period_end` TINYINT(1) NOT NULL DEFAULT 0,
    `current_period_start` DATETIME DEFAULT NULL,
    `current_period_end` DATETIME DEFAULT NULL,
    `streak_months` INT NOT NULL DEFAULT 1,
    `total_entries_earned` INT NOT NULL DEFAULT 0,
    `residential_state` VARCHAR(10) DEFAULT NULL,
    `state_eligible` TINYINT(1) NOT NULL DEFAULT 1,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_user_id` (`user_id`),
    INDEX `idx_status` (`status`),
    INDEX `idx_stripe_sub` (`stripe_subscription_id`),
    FOREIGN KEY (`tier_id`) REFERENCES `tbl_subscription_tiers`(`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 4. Entries Ledger (immutable log of all entry grants)
CREATE TABLE IF NOT EXISTS `tbl_entries_ledger` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `draw_id` INT DEFAULT NULL,
    `quantity` INT NOT NULL DEFAULT 0,
    `source` ENUM('subscription_base','subscription_streak','coin_purchase','cash_purchase','admin_manual','retention_bonus','signup_bonus') NOT NULL,
    `streak_month` INT DEFAULT NULL,
    `meta` JSON DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user_draw` (`user_id`, `draw_id`),
    INDEX `idx_source` (`source`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 5. Giveaway Draws
CREATE TABLE IF NOT EXISTS `tbl_giveaway_draws` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `draw_code` VARCHAR(20) DEFAULT NULL,
    `title` VARCHAR(200) NOT NULL,
    `description` TEXT DEFAULT NULL,
    `prize_value_cents` INT NOT NULL DEFAULT 0,
    `prize_2nd_cents` INT NOT NULL DEFAULT 0,
    `prize_3rd_cents` INT NOT NULL DEFAULT 0,
    `draw_at` DATETIME NOT NULL,
    `status` ENUM('scheduled','active','locked','completed') NOT NULL DEFAULT 'scheduled',
    `cover_image_url` VARCHAR(500) DEFAULT NULL,
    `winner_user_id` INT DEFAULT NULL,
    `winner_display` VARCHAR(100) DEFAULT NULL,
    `certificate_url` VARCHAR(500) DEFAULT NULL,
    `total_entries_at_lock` INT DEFAULT NULL,
    `total_participants_at_lock` INT DEFAULT NULL,
    `locked_at` DATETIME DEFAULT NULL,
    `completed_at` DATETIME DEFAULT NULL,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX `idx_status` (`status`),
    INDEX `idx_draw_at` (`draw_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 6. Subscription Cancellation Log
CREATE TABLE IF NOT EXISTS `tbl_subscription_cancellations` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `user_id` INT NOT NULL,
    `subscription_id` INT NOT NULL,
    `stripe_subscription_id` VARCHAR(100) DEFAULT NULL,
    `reason` TEXT DEFAULT NULL,
    `entries_at_cancel` INT DEFAULT 0,
    `streak_at_cancel` INT DEFAULT 0,
    `undo_before` DATETIME DEFAULT NULL,
    `undone` TINYINT(1) NOT NULL DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 7. Draw CSV Exports Log
CREATE TABLE IF NOT EXISTS `tbl_draw_exports` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `draw_id` INT NOT NULL,
    `exported_by` INT NOT NULL,
    `file_path` VARCHAR(500) DEFAULT NULL,
    `total_participants` INT DEFAULT 0,
    `total_weight` INT DEFAULT 0,
    `created_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_draw` (`draw_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 8. Draw Winners (extends existing tbl_vault_winners concept)
CREATE TABLE IF NOT EXISTS `tbl_draw_winners` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `draw_id` INT NOT NULL,
    `user_id` INT NOT NULL,
    `prize_position` INT NOT NULL DEFAULT 1,
    `prize_amount_cents` INT NOT NULL DEFAULT 0,
    `winner_display` VARCHAR(100) DEFAULT NULL,
    `punter_id` VARCHAR(20) DEFAULT NULL,
    `total_entries` INT DEFAULT 0,
    `certificate_url` VARCHAR(500) DEFAULT NULL,
    `won_at` DATETIME DEFAULT CURRENT_TIMESTAMP,
    INDEX `idx_draw` (`draw_id`),
    INDEX `idx_user` (`user_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- 9. Add residential_state to tbl_users if not exists
ALTER TABLE `tbl_users` ADD COLUMN IF NOT EXISTS `residential_state` VARCHAR(10) DEFAULT NULL AFTER `last_name`;
ALTER TABLE `tbl_users` ADD COLUMN IF NOT EXISTS `punter_subscription_id` INT DEFAULT NULL AFTER `residential_state`;
ALTER TABLE `tbl_users` ADD COLUMN IF NOT EXISTS `subscription_tier_key` VARCHAR(20) DEFAULT NULL AFTER `punter_subscription_id`;
ALTER TABLE `tbl_users` ADD COLUMN IF NOT EXISTS `streak_months` INT NOT NULL DEFAULT 0 AFTER `subscription_tier_key`;
ALTER TABLE `tbl_users` ADD COLUMN IF NOT EXISTS `total_giveaway_entries` INT NOT NULL DEFAULT 0 AFTER `streak_months`;

-- 10. Admin Vault Settings (jackpot text, permit numbers)
CREATE TABLE IF NOT EXISTS `tbl_vault_settings` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `setting_key` VARCHAR(50) NOT NULL UNIQUE,
    `setting_value` TEXT DEFAULT NULL,
    `updated_at` DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbl_vault_settings` (`setting_key`, `setting_value`) VALUES
('current_jackpot_text', '$12,953 CASH'),
('next_prize_text', 'Next prize: $17,963 CASH'),
('nsw_permit_number', ''),
('sa_permit_number', ''),
('act_permit_number', ''),
('draw_timezone', 'Australia/Brisbane');

-- 11. Australian States reference
CREATE TABLE IF NOT EXISTS `tbl_australian_states` (
    `id` INT AUTO_INCREMENT PRIMARY KEY,
    `code` VARCHAR(5) NOT NULL UNIQUE,
    `name` VARCHAR(50) NOT NULL,
    `eligible` TINYINT(1) NOT NULL DEFAULT 1,
    `disclaimer` TEXT DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

INSERT INTO `tbl_australian_states` (`code`, `name`, `eligible`, `disclaimer`) VALUES
('NSW', 'New South Wales', 1, NULL),
('QLD', 'Queensland', 1, NULL),
('VIC', 'Victoria', 1, NULL),
('SA', 'South Australia', 0, 'Residents of South Australia are currently ineligible for the draw due to local permit requirements.'),
('WA', 'Western Australia', 1, NULL),
('TAS', 'Tasmania', 1, NULL),
('NT', 'Northern Territory', 1, NULL),
('ACT', 'Australian Capital Territory', 1, NULL);
