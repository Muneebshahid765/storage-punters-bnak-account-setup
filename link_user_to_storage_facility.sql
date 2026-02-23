-- Link User 1 (Tobias) to Company and Storage Facility
-- This will enable storage facilities to appear on the dashboard

-- Step 1: Check if storage facility exists
SELECT id, name FROM tbl_storage_facilities;
-- If you see storage facility ID 1 (Forest Glan), we'll use that
-- Otherwise, run create_storage_facility.sql first

-- Step 2: Check if company exists
SELECT id, name FROM tbl_companies;
-- If no company exists, we need to create one first

-- Step 3: Create a company for User 1 (if needed)
INSERT INTO tbl_companies (
    name,
    website,
    email,
    created_at,
    updated_at
) VALUES (
    'Mammoth Storage Company',
    'https://mammothstorage.com.au',
    'contact@mammothstorage.com.au',
    NOW(),
    NOW()
);

SET @company_id = LAST_INSERT_ID();

-- Step 4: Link User 1 to the company
INSERT INTO tbl_user_company_relationship (
    company_id,
    user_id,
    status_type_id,
    created_at,
    updated_at
) VALUES (
    @company_id,
    1,  -- User 1 (Tobias)
    1,  -- Active status
    NOW(),
    NOW()
);

-- Step 5: Link the company to storage facility ID 1 (Forest Glan)
-- Change storage_facility_id if you want to use a different facility
INSERT INTO tbl_storage_facility_company_relationship (
    storage_facility_id,
    company_id,
    created_at,
    updated_at
) VALUES (
    1,  -- Storage Facility ID 1 (Forest Glan) - change if needed
    @company_id,
    NOW(),
    NOW()
);

-- Verification Query - Check if relationships are set up correctly
SELECT 
    u.id as user_id,
    u.first_name,
    u.last_name,
    uc.email,
    c.id as company_id,
    c.name as company_name,
    sf.id as storage_facility_id,
    sf.name as storage_facility_name
FROM tbl_users u
JOIN tbl_user_credentials uc ON uc.user_id = u.id
JOIN tbl_user_company_relationship ucr ON ucr.user_id = u.id AND ucr.status_type_id = 1
JOIN tbl_companies c ON c.id = ucr.company_id
JOIN tbl_storage_facility_company_relationship sfcr ON sfcr.company_id = c.id
JOIN tbl_storage_facilities sf ON sf.id = sfcr.storage_facility_id
WHERE u.id = 1;
