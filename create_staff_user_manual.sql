-- Alternative: Create Staff User with Manual ID Specification
-- Use this if LAST_INSERT_ID() doesn't work in your SQL client

-- First, check the next available user ID
SELECT MAX(id) + 1 as next_user_id FROM tbl_users;
-- Let's assume the next ID is 4 (replace with actual value from above query)

-- Step 1: Insert into tbl_users
INSERT INTO tbl_users (id, first_name, last_name, created_at, updated_at) 
VALUES (4, 'Staff', 'User', NOW(), NOW());

-- Step 2: Insert user credentials
INSERT INTO tbl_user_credentials (email, password, user_id, created_at, updated_at) 
VALUES ('staff@storagepunters.com', 'StaffPassword123!', 4, NOW(), NOW());

-- Step 3: Insert user status (1 = Active)
INSERT INTO tbl_user_status_relationship (user_id, status_type_id, created_at, updated_at) 
VALUES (4, 1, NOW(), NOW());

-- Step 4: Insert user role (3 = Staff)
INSERT INTO tbl_user_role_relationship (user_id, role_id, created_at, updated_at) 
VALUES (4, 3, NOW(), NOW());

-- Step 5: Insert user location
-- First get next location ID
SELECT MAX(id) + 1 as next_location_id FROM tbl_locations;
-- Let's assume the next ID is 9 (replace with actual value)

INSERT INTO tbl_locations (id, street_address, apt_suite, city, postal_code, state, country, phone, created_at, updated_at) 
VALUES (9, '123 Staff Street', 'Suite 1', 'Brisbane', '4000', 'Queensland', 'Australia', '0400000000', NOW(), NOW());

INSERT INTO tbl_user_location_relationship (user_id, location_id, created_at, updated_at) 
VALUES (4, 9, NOW(), NOW());

-- Verification Query
SELECT 
    u.id as user_id,
    u.first_name,
    u.last_name,
    uc.email,
    uc.password,
    usr.status_type_id,
    st.name as status_name,
    urr.role_id,
    rt.name as role_name,
    l.street_address,
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
WHERE u.id = 4;
