-- 006_add_export_columns.sql
-- Adds export_type and sa_excluded columns to tbl_draw_exports for TPAL exports

ALTER TABLE `tbl_draw_exports`
    ADD COLUMN IF NOT EXISTS `export_type` VARCHAR(20) DEFAULT 'all' AFTER `total_weight`,
    ADD COLUMN IF NOT EXISTS `sa_excluded` INT NOT NULL DEFAULT 0 AFTER `export_type`;
