-- Check if an Admin user exists (Role ID = 4)
SELECT 
    u.id as user_id,
    u.first_name,
    u.last_name,
    uc.email,
    uc.password,
    urr.role_id,
    usr.status_type_id
FROM tbl_users u
JOIN tbl_user_credentials uc ON uc.user_id = u.id
JOIN tbl_user_role_relationship urr ON urr.user_id = u.id
JOIN tbl_user_status_relationship usr ON usr.user_id = u.id
WHERE urr.role_id = 4;
