-- SQL Script to Fix Missing Storage Facility Location Links
-- Run this in phpMyAdmin or MySQL client

-- Step 1: Create location entries for facilities that don't have them
-- First, let's create proper Australian locations for Mammoth Storage facilities

-- Mammoth Storage Forest Glen (ID 9)
INSERT INTO tbl_locations (street_address, city, state, country, postal_code, phone, created_at, updated_at)
VALUES ('Forest Glen', 'Forest Glen', 'QLD', 'Australia', '4556', '', NOW(), NOW());
SET @location_id_9 = LAST_INSERT_ID();

INSERT INTO tbl_storage_facility_location_relationship (storage_facility_id, location_id, created_at, updated_at)
VALUES (9, @location_id_9, NOW(), NOW());

-- Mammoth Storage Caloundra (ID 10)
INSERT INTO tbl_locations (street_address, city, state, country, postal_code, phone, created_at, updated_at)
VALUES ('Caloundra', 'Caloundra', 'QLD', 'Australia', '4551', '', NOW(), NOW());
SET @location_id_10 = LAST_INSERT_ID();

INSERT INTO tbl_storage_facility_location_relationship (storage_facility_id, location_id, created_at, updated_at)
VALUES (10, @location_id_10, NOW(), NOW());

-- Mammoth Storage Kunda Park (ID 11)
INSERT INTO tbl_locations (street_address, city, state, country, postal_code, phone, created_at, updated_at)
VALUES ('Kunda Park', 'Kunda Park', 'QLD', 'Australia', '4556', '', NOW(), NOW());
SET @location_id_11 = LAST_INSERT_ID();

INSERT INTO tbl_storage_facility_location_relationship (storage_facility_id, location_id, created_at, updated_at)
VALUES (11, @location_id_11, NOW(), NOW());

-- Mammoth Storage Gympie (ID 12)
INSERT INTO tbl_locations (street_address, city, state, country, postal_code, phone, created_at, updated_at)
VALUES ('Gympie', 'Gympie', 'QLD', 'Australia', '4570', '', NOW(), NOW());
SET @location_id_12 = LAST_INSERT_ID();

INSERT INTO tbl_storage_facility_location_relationship (storage_facility_id, location_id, created_at, updated_at)
VALUES (12, @location_id_12, NOW(), NOW());

-- Mammoth Storage Hervey Bay (ID 13)
INSERT INTO tbl_locations (street_address, city, state, country, postal_code, phone, created_at, updated_at)
VALUES ('Hervey Bay', 'Hervey Bay', 'QLD', 'Australia', '4655', '', NOW(), NOW());
SET @location_id_13 = LAST_INSERT_ID();

INSERT INTO tbl_storage_facility_location_relationship (storage_facility_id, location_id, created_at, updated_at)
VALUES (13, @location_id_13, NOW(), NOW());

-- Mammoth Storage Bundaberg (ID 14)
INSERT INTO tbl_locations (street_address, city, state, country, postal_code, phone, created_at, updated_at)
VALUES ('Bundaberg', 'Bundaberg', 'QLD', 'Australia', '4670', '', NOW(), NOW());
SET @location_id_14 = LAST_INSERT_ID();

INSERT INTO tbl_storage_facility_location_relationship (storage_facility_id, location_id, created_at, updated_at)
VALUES (14, @location_id_14, NOW(), NOW());

-- Mammoth Storage Nambour (ID 15)
INSERT INTO tbl_locations (street_address, city, state, country, postal_code, phone, created_at, updated_at)
VALUES ('Nambour', 'Nambour', 'QLD', 'Australia', '4560', '', NOW(), NOW());
SET @location_id_15 = LAST_INSERT_ID();

INSERT INTO tbl_storage_facility_location_relationship (storage_facility_id, location_id, created_at, updated_at)
VALUES (15, @location_id_15, NOW(), NOW());

-- Mammoth Storage Maroochydore (ID 16)
INSERT INTO tbl_locations (street_address, city, state, country, postal_code, phone, created_at, updated_at)
VALUES ('Maroochydore', 'Maroochydore', 'QLD', 'Australia', '4558', '', NOW(), NOW());
SET @location_id_16 = LAST_INSERT_ID();

INSERT INTO tbl_storage_facility_location_relationship (storage_facility_id, location_id, created_at, updated_at)
VALUES (16, @location_id_16, NOW(), NOW());

-- forest gelen (ID 7) - seems like duplicate of Forest Glen
INSERT INTO tbl_locations (street_address, city, state, country, postal_code, phone, created_at, updated_at)
VALUES ('Forest Glen', 'Forest Glen', 'QLD', 'Australia', '4556', '', NOW(), NOW());
SET @location_id_7 = LAST_INSERT_ID();

INSERT INTO tbl_storage_facility_location_relationship (storage_facility_id, location_id, created_at, updated_at)
VALUES (7, @location_id_7, NOW(), NOW());

-- My Storage Facility (ID 2) - generic test facility
INSERT INTO tbl_locations (street_address, city, state, country, postal_code, phone, created_at, updated_at)
VALUES ('Test Street', 'Brisbane', 'QLD', 'Australia', '4000', '', NOW(), NOW());
SET @location_id_2 = LAST_INSERT_ID();

INSERT INTO tbl_storage_facility_location_relationship (storage_facility_id, location_id, created_at, updated_at)
VALUES (2, @location_id_2, NOW(), NOW());

-- Step 2: Fix orphan locations with missing state (Lahore/Islamabad test data)
-- Update all Lahore locations to have proper state
UPDATE tbl_locations 
SET state = 'Punjab', country = 'Pakistan'
WHERE city = 'Lahore' AND (state IS NULL OR state = '');

-- Update all Islamabad locations to have proper state
UPDATE tbl_locations 
SET state = 'Islamabad Capital Territory', country = 'Pakistan'
WHERE city = 'Islamabad' AND (state IS NULL OR state = '');

-- Step 3: Delete completely empty orphan locations (no facility link)
DELETE FROM tbl_locations 
WHERE (city IS NULL OR city = '') 
AND (state IS NULL OR state = '')
AND id NOT IN (SELECT location_id FROM tbl_storage_facility_location_relationship);

-- Verification queries (run these after the above to confirm)
-- SELECT 'Facilities without locations:' as check_type, COUNT(*) as count
-- FROM tbl_storage_facilities sf
-- LEFT JOIN tbl_storage_facility_location_relationship sflr ON sf.id = sflr.storage_facility_id
-- WHERE sflr.location_id IS NULL;

-- SELECT 'Locations with missing city/state:' as check_type, COUNT(*) as count
-- FROM tbl_locations
-- WHERE city IS NULL OR state IS NULL OR city = '' OR state = '';

-- SELECT 'Orphan locations (no facility):' as check_type, COUNT(*) as count
-- FROM tbl_locations l
-- LEFT JOIN tbl_storage_facility_location_relationship sflr ON l.id = sflr.location_id
-- WHERE sflr.storage_facility_id IS NULL;
