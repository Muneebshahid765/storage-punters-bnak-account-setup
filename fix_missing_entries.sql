-- Fix: Create Active Draw if missing
-- This will allow entries to be granted for subscriptions

-- Step 1: Check if there's an active draw
SELECT * FROM tbl_giveaway_draws WHERE status IN ('active', 'scheduled') ORDER BY draw_at DESC LIMIT 1;

-- Step 2: If no active draw exists, create one
INSERT INTO tbl_giveaway_draws (
    draw_code,
    title,
    description,
    prize_value_cents,
    prize_2nd_cents,
    prize_3rd_cents,
    draw_at,
    status,
    created_at,
    updated_at
) VALUES (
    'DRAW-001',
    'Monthly Giveaway Draw - March 2026',
    'Win amazing prizes! Major prize draw for all eligible punters.',
    10000000,  -- $100,000 major prize
    500000,    -- $5,000 second prize
    100000,    -- $1,000 third prize
    DATE_ADD(NOW(), INTERVAL 30 DAY),  -- Draw in 30 days
    'active',
    NOW(),
    NOW()
);

-- Step 3: Get the newly created draw ID
SET @draw_id = LAST_INSERT_ID();

-- Step 4: Grant entries to all users who have active subscriptions but no entries
INSERT INTO tbl_entries_ledger (user_id, draw_id, quantity, source, streak_month, meta, created_at)
SELECT 
    ps.user_id,
    @draw_id,
    st.base_entries * (
        SELECT COALESCE(MAX(multiplier), 1.0) 
        FROM tbl_streak_milestones 
        WHERE month_threshold <= ps.streak_months
    ) as calculated_entries,
    'subscription_streak',
    ps.streak_months,
    JSON_OBJECT(
        'base', st.base_entries,
        'streak', ps.streak_months,
        'tier_key', st.tier_key,
        'reason', 'Retroactive grant for missing entries'
    ),
    NOW()
FROM tbl_punter_subscriptions ps
JOIN tbl_subscription_tiers st ON st.id = ps.tier_id
LEFT JOIN tbl_entries_ledger el ON el.user_id = ps.user_id AND el.draw_id = @draw_id
WHERE ps.status = 'active'
AND el.id IS NULL;  -- Only grant if user has no entries for this draw

-- Step 5: Update user total entries count
UPDATE tbl_users u
SET u.total_giveaway_entries = (
    SELECT COALESCE(SUM(el.quantity), 0)
    FROM tbl_entries_ledger el
    WHERE el.user_id = u.id
)
WHERE u.id IN (
    SELECT DISTINCT user_id FROM tbl_punter_subscriptions WHERE status = 'active'
);

-- Step 6: Verify the fix
SELECT 
    u.id as user_id,
    u.first_name,
    u.last_name,
    ps.tier_id,
    st.tier_name,
    ps.streak_months,
    st.base_entries,
    COALESCE(SUM(el.quantity), 0) as total_entries,
    u.coin_balance
FROM tbl_users u
JOIN tbl_punter_subscriptions ps ON ps.user_id = u.id
JOIN tbl_subscription_tiers st ON st.id = ps.tier_id
LEFT JOIN tbl_entries_ledger el ON el.user_id = u.id
WHERE ps.status = 'active'
GROUP BY u.id, u.first_name, u.last_name, ps.tier_id, st.tier_name, ps.streak_months, st.base_entries, u.coin_balance;
