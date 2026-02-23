-- First, let's verify the current status of all users
SELECT 
    u.id as user_id,
    u.first_name,
    u.last_name,
    uc.email,
    usr.status_type_id,
    st.name as status_name,
    urr.role_id,
    rt.name as role_name
FROM tbl_users u
LEFT JOIN tbl_user_credentials uc ON uc.user_id = u.id
LEFT JOIN tbl_user_status_relationship usr ON usr.user_id = u.id
LEFT JOIN tbl_status_types st ON st.id = usr.status_type_id
LEFT JOIN tbl_user_role_relationship urr ON urr.user_id = u.id
LEFT JOIN tbl_role_types rt ON rt.id = urr.role_id
ORDER BY u.id;
