-- Add Stripe integration columns to tbl_facility_subscriptions
-- Run this migration to fix Error 1054: Unknown column

ALTER TABLE `tbl_facility_subscriptions` 
ADD COLUMN `stripe_customer_id` VARCHAR(191) NULL AFTER `company_id`,
ADD COLUMN `stripe_subscription_id` VARCHAR(191) NULL AFTER `stripe_customer_id`,
ADD COLUMN `start_date` DATETIME NULL AFTER `next_billing_date`,
ADD COLUMN `current_period_start` DATETIME NULL AFTER `start_date`,
ADD COLUMN `current_period_end` DATETIME NULL AFTER `current_period_start`,
ADD COLUMN `amount` DECIMAL(10,2) NULL DEFAULT 0.00 AFTER `current_period_end`,
ADD COLUMN `currency` VARCHAR(10) NULL DEFAULT 'AUD' AFTER `amount`;

-- Add index for faster lookups
ALTER TABLE `tbl_facility_subscriptions` 
ADD INDEX `idx_stripe_subscription_id` (`stripe_subscription_id`),
ADD INDEX `idx_stripe_customer_id` (`stripe_customer_id`);
