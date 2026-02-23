-- Add cancel/pause flags to facility subscriptions
ALTER TABLE `tbl_facility_subscriptions`
ADD COLUMN `cancel_at_period_end` TINYINT(1) NOT NULL DEFAULT 0 AFTER `status`,
ADD COLUMN `pause_resumes_at` DATETIME NULL AFTER `current_period_end`;
