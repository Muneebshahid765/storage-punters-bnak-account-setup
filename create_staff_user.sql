-- Create Staff User SQL Script
-- This script creates a complete staff user with all necessary relationships

-- Step 1: Insert into tbl_users
INSERT INTO tbl_users (first_name, last_name, created_at, updated_at) 
VALUES ('Staff', 'User', NOW(), NOW());

-- Get the last inserted user_id (you'll need to replace @user_id with the actual ID after running the above)
SET @user_id = LAST_INSERT_ID();

-- Step 2: Insert user credentials (email and password)
INSERT INTO tbl_user_credentials (email, password, user_id, created_at, updated_at) 
VALUES ('staff@storagepunters.com', 'StaffPassword123!', @user_id, NOW(), NOW());

-- Step 3: Insert user status (1 = Active)
INSERT INTO tbl_user_status_relationship (user_id, status_type_id, created_at, updated_at) 
VALUES (@user_id, 1, NOW(), NOW());

-- Step 4: Insert user role (3 = Staff)
INSERT INTO tbl_user_role_relationship (user_id, role_id, created_at, updated_at) 
VALUES (@user_id, 3, NOW(), NOW());

-- Step 5: Insert user location (optional - you can modify the address details)
INSERT INTO tbl_locations (street_address, apt_suite, city, postal_code, state, country, phone, created_at, updated_at) 
VALUES ('123 Staff Street', 'Suite 1', 'Brisbane', '4000', 'Queensland', 'Australia', '0400000000', NOW(), NOW());

SET @location_id = LAST_INSERT_ID();

INSERT INTO tbl_user_location_relationship (user_id, location_id, created_at, updated_at) 
VALUES (@user_id, @location_id, NOW(), NOW());

-- Verification Query
SELECT 
    u.id as user_id,
    u.first_name,
    u.last_name,
    uc.email,
    usr.status_type_id,
    st.name as status_name,
    urr.role_id,
    rt.name as role_name,
    l.city,
    l.phone
FROM tbl_users u
LEFT JOIN tbl_user_credentials uc ON uc.user_id = u.id
LEFT JOIN tbl_user_status_relationship usr ON usr.user_id = u.id
LEFT JOIN tbl_status_types st ON st.id = usr.status_type_id
LEFT JOIN tbl_user_role_relationship urr ON urr.user_id = u.id
LEFT JOIN tbl_role_types rt ON rt.id = urr.role_id
LEFT JOIN tbl_user_location_relationship ulr ON ulr.user_id = u.id
LEFT JOIN tbl_locations l ON l.id = ulr.location_id
WHERE u.id = @user_id;
