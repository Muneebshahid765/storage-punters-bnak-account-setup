-- Fix Login Issue: Activate User 1 (Tobias Constantinides)
-- This updates the user status from Inactive (2) to Active (1)

UPDATE tbl_user_status_relationship 
SET status_type_id = 1, 
    updated_at = NOW() 
WHERE user_id = 1;

-- Verify the update
SELECT 
    usr.id,
    usr.user_id,
    u.first_name,
    u.last_name,
    usr.status_type_id,
    st.name as status_name,
    usr.updated_at
FROM tbl_user_status_relationship usr
JOIN tbl_users u ON u.id = usr.user_id
JOIN tbl_status_types st ON st.id = usr.status_type_id
WHERE usr.user_id = 1;
