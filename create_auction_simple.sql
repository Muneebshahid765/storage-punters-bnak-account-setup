-- Create Auction - Simple Version (No Manual IDs)
-- This script automatically finds the next available ID

-- Step 1: Insert auction (ID will auto-increment)
INSERT INTO tbl_auctions (
    description,
    unit_number,
    disclose_unit_number_on_website,
    outstanding_dept,
    starting_price,
    start_date,
    start_time,
    end_date,
    end_time,
    youtube_video_url,
    unit_size,
    tags,
    additional_features,
    created_at,
    updated_at
) VALUES (
    'Storage unit containing household items, furniture, and boxes. Unit has been abandoned for 90+ days.',
    'A-123',
    'Yes',
    500.00,
    100.00,
    CURDATE(),
    '09:00:00',
    DATE_ADD(CURDATE(), INTERVAL 7 DAY),
    '17:00:00',
    '',
    '10x10',
    'Furniture,Boxes,Household',
    'Climate controlled, Ground floor access',
    NOW(),
    NOW()
);

SET @auction_id = LAST_INSERT_ID();

-- Step 2: Link to storage facility (change storage_facility_id if needed)
INSERT INTO tbl_auction_storage_facility_relationship (
    auction_id,
    storage_facility_id,
    created_at,
    updated_at
) VALUES (
    @auction_id,
    1,  -- Change this to your storage_facility_id
    NOW(),
    NOW()
);

-- Step 3: Set auction type (3 = Scheduled)
INSERT INTO tbl_auction_status_relationship (
    auction_id,
    auction_type_id,
    created_at,
    updated_at
) VALUES (
    @auction_id,
    3,  -- Scheduled
    NOW(),
    NOW()
);

-- Step 4: Add category
INSERT INTO tbl_auction_category_relationship (
    auction_id,
    auction_category_id,
    created_at,
    updated_at
) VALUES (
    @auction_id,
    1,  -- General
    NOW(),
    NOW()
);

-- Verify the auction was created
SELECT 
    a.id as auction_id,
    a.description,
    a.unit_number,
    a.starting_price,
    a.start_date,
    a.end_date,
    sf.name as storage_facility_name,
    at.name as auction_type
FROM tbl_auctions a
LEFT JOIN tbl_auction_storage_facility_relationship asfr ON asfr.auction_id = a.id
LEFT JOIN tbl_storage_facilities sf ON sf.id = asfr.storage_facility_id
LEFT JOIN tbl_auction_status_relationship asr ON asr.auction_id = a.id
LEFT JOIN tbl_auction_types at ON at.id = asr.auction_type_id
WHERE a.id = @auction_id;
