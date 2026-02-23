-- Add Stripe integration columns to tbl_facility_subscription_plans
ALTER TABLE `tbl_facility_subscription_plans` 
ADD COLUMN `stripe_product_id` VARCHAR(191) NULL AFTER `annual_price`,
ADD COLUMN `stripe_price_id_monthly` VARCHAR(191) NULL AFTER `stripe_product_id`,
ADD COLUMN `stripe_price_id_yearly` VARCHAR(191) NULL AFTER `stripe_price_id_monthly`;
