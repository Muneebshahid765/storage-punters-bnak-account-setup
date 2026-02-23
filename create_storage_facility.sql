-- Create Storage Facility SQL Script
-- This script creates a complete storage facility with all necessary relationships

-- Step 1: Insert location for the storage facility
INSERT INTO tbl_locations (
    street_address,
    apt_suite,
    city,
    postal_code,
    state,
    country,
    phone,
    created_at,
    updated_at
) VALUES (
    '123 Storage Street',
    'Unit 1',
    'Brisbane',
    '4000',
    'Queensland',
    'Australia',
    '1300 123 456',
    NOW(),
    NOW()
);

SET @location_id = LAST_INSERT_ID();

-- Step 2: Insert the storage facility
INSERT INTO tbl_storage_facilities (
    name,
    abn,
    website,
    description,
    timezone,
    contact_email,
    additional_email,
    office_hours,
    payout_details,
    youtube_video_url,
    branding_logo,
    created_at,
    updated_at
) VALUES (
    'My Storage Facility',
    '12 345 678 901',
    'https://mystorageplace.com',
    'Premium self-storage facility offering secure, climate-controlled units in various sizes. We provide 24/7 access, moving supplies, and excellent customer service.',
    'Australia/Brisbane',
    'contact@mystorageplace.com',
    'admin@mystorageplace.com',
    'Access Hours: 24/7\nReception Office Hours:\nMondays to Fridays: 9am to 5pm\nSaturdays: 9am to 1pm\nSunday: Closed\nClosed on Public Holidays',
    'Bank',
    '',
    '',
    NOW(),
    NOW()
);

SET @storage_facility_id = LAST_INSERT_ID();

-- Step 3: Link storage facility to location
INSERT INTO tbl_storage_facility_location_relationship (
    storage_facility_id,
    location_id,
    created_at,
    updated_at
) VALUES (
    @storage_facility_id,
    @location_id,
    NOW(),
    NOW()
);

-- Step 4: Set storage facility status (1 = Active)
INSERT INTO tbl_storage_facility_status_relationship (
    storage_facility_id,
    status_type_id,
    created_at,
    updated_at
) VALUES (
    @storage_facility_id,
    1,  -- Active
    NOW(),
    NOW()
);

-- Step 5: Link to company (optional - requires company_id)
-- First check available companies:
-- SELECT id, name FROM tbl_companies;
-- Then uncomment and update the company_id below:
/*
INSERT INTO tbl_storage_facility_company_relationship (
    storage_facility_id,
    company_id,
    created_at,
    updated_at
) VALUES (
    @storage_facility_id,
    1,  -- Replace with actual company_id
    NOW(),
    NOW()
);
*/

-- Verification Query
SELECT 
    sf.id as storage_facility_id,
    sf.name,
    sf.abn,
    sf.website,
    sf.contact_email,
    sf.timezone,
    l.street_address,
    l.city,
    l.state,
    l.postal_code,
    l.phone,
    st.name as status_name
FROM tbl_storage_facilities sf
LEFT JOIN tbl_storage_facility_location_relationship sflr ON sflr.storage_facility_id = sf.id
LEFT JOIN tbl_locations l ON l.id = sflr.location_id
LEFT JOIN tbl_storage_facility_status_relationship sfsr ON sfsr.storage_facility_id = sf.id
LEFT JOIN tbl_status_types st ON st.id = sfsr.status_type_id
WHERE sf.id = @storage_facility_id;
