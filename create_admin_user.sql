-- Create Admin User SQL Script
-- Use this if you need to create an Admin user (Role ID = 4)

-- Step 1: Ensure Role ID 4 exists in tbl_role_types (if not already there)
INSERT IGNORE INTO tbl_role_types (id, name, descriptions, created_at, updated_at)
VALUES (4, 'Admin', 'Platform Administrator', NOW(), NOW());

-- Step 2: Insert into tbl_users
INSERT INTO tbl_users (
    first_name,
    last_name,
    created_at,
    updated_at
) VALUES (
    'Admin',
    'User',
    NOW(),
    NOW()
);

SET @user_id = LAST_INSERT_ID();

-- Step 3: Insert user credentials
INSERT INTO tbl_user_credentials (
    email,
    password,
    user_id,
    created_at,
    updated_at
) VALUES (
    'admin@storagepunters.com',
    'AdminPassword123!',
    @user_id,
    NOW(),
    NOW()
);

-- Step 4: Insert user status (1 = Active)
INSERT INTO tbl_user_status_relationship (
    user_id,
    status_type_id,
    created_at,
    updated_at
) VALUES (
    @user_id,
    1,
    NOW(),
    NOW()
);

-- Step 5: Insert user role (4 = Admin)
INSERT INTO tbl_user_role_relationship (
    user_id,
    role_id,
    created_at,
    updated_at
) VALUES (
    @user_id,
    4,
    NOW(),
    NOW()
);

-- Verification Query
SELECT 
    u.id as user_id,
    u.first_name,
    u.last_name,
    uc.email,
    urr.role_id,
    rt.name as role_name
FROM tbl_users u
JOIN tbl_user_credentials uc ON uc.user_id = u.id
JOIN tbl_user_role_relationship urr ON urr.user_id = u.id
JOIN tbl_role_types rt ON rt.id = urr.role_id
WHERE u.id = @user_id;
