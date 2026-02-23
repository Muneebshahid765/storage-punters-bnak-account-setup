-- Check User Company and Storage Facility Relationships
-- This query shows if the user has the necessary relationships to see storage facilities

SELECT 
    u.id as user_id,
    u.first_name,
    u.last_name,
    uc.email,
    ucr.company_id,
    c.name as company_name,
    sfcr.storage_facility_id,
    sf.name as storage_facility_name
FROM tbl_users u
LEFT JOIN tbl_user_credentials uc ON uc.user_id = u.id
LEFT JOIN tbl_user_company_relationship ucr ON ucr.user_id = u.id AND ucr.status_type_id = 1
LEFT JOIN tbl_companies c ON c.id = ucr.company_id
LEFT JOIN tbl_storage_facility_company_relationship sfcr ON sfcr.company_id = c.id
LEFT JOIN tbl_storage_facilities sf ON sf.id = sfcr.storage_facility_id
WHERE u.id = 1;  -- User 1 (Tobias)
