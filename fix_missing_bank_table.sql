-- Create the missing storage facility bank relationship table
CREATE TABLE IF NOT EXISTS `tbl_storage_facility_bank_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) NOT NULL,
  `storage_facility_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Note: Ensure that tbl_bank and tbl_storage_facilities already exist before running this.
