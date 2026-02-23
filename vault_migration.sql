-- Phase 1: tbl_users extensions
ALTER TABLE tbl_users ADD COLUMN punter_id VARCHAR(50) UNIQUE AFTER id;
ALTER TABLE tbl_users ADD COLUMN subscription_tier ENUM('none', 'player', 'shark', 'whale') DEFAULT 'none' AFTER punter_id;
ALTER TABLE tbl_users ADD COLUMN coin_balance INT DEFAULT 0 AFTER subscription_tier;
ALTER TABLE tbl_users ADD COLUMN notification_prefs JSON AFTER coin_balance;

-- Phase 2: Vault & Giveaway architecture
CREATE TABLE IF NOT EXISTS tbl_vault_draws (
    id INT AUTO_INCREMENT PRIMARY KEY,
    draw_code VARCHAR(20) UNIQUE NOT NULL,
    title VARCHAR(255) NOT NULL,
    prize_value_cents INT NOT NULL,
    cover_image_url TEXT,
    description TEXT,
    draw_at DATETIME NOT NULL,
    status ENUM('scheduled', 'active', 'locked', 'completed') DEFAULT 'scheduled',
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tbl_vault_draw_items (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vault_draw_id INT NOT NULL,
    item_name VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vault_draw_id) REFERENCES tbl_vault_draws(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tbl_vault_entries (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vault_draw_id INT NOT NULL,
    user_id INT NOT NULL,
    entries INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE (vault_draw_id, user_id),
    FOREIGN KEY (vault_draw_id) REFERENCES tbl_vault_draws(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES tbl_users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tbl_vault_entry_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vault_draw_id INT NOT NULL,
    user_id INT NOT NULL,
    type ENUM('subscription_credit', 'coin_purchase', 'cash_purchase', 'retention_bonus', 'admin_adjustment') NOT NULL,
    entries_delta INT NOT NULL,
    meta JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vault_draw_id) REFERENCES tbl_vault_draws(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES tbl_users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tbl_vault_winners (
    id INT AUTO_INCREMENT PRIMARY KEY,
    vault_draw_id INT NOT NULL,
    user_id INT NOT NULL,
    winner_display VARCHAR(255) NOT NULL,
    punter_id VARCHAR(50) NOT NULL,
    won_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    prize_value_cents INT NOT NULL,
    items_snapshot JSON,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (vault_draw_id) REFERENCES tbl_vault_draws(id),
    FOREIGN KEY (user_id) REFERENCES tbl_users(id)
);

-- Phase 3: Coin Economy
CREATE TABLE IF NOT EXISTS tbl_coin_rules (
    id INT AUTO_INCREMENT PRIMARY KEY,
    coins_per_bid INT DEFAULT 10,
    coins_per_dollar_bidded INT DEFAULT 0,
    coins_on_auction_win INT DEFAULT 150,
    coins_per_dollar_won INT DEFAULT 0,
    coins_on_subscription_renewal INT DEFAULT 500,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

INSERT IGNORE INTO tbl_coin_rules (id, coins_per_bid, coins_on_auction_win, coins_on_subscription_renewal) VALUES (1, 10, 150, 500);

CREATE TABLE IF NOT EXISTS tbl_coin_transactions (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    type ENUM('bid_placed', 'auction_won', 'subscription_renewal', 'coin_spent_entries', 'admin_adjustment') NOT NULL,
    coins_delta INT NOT NULL,
    reference_type VARCHAR(50),
    reference_id INT,
    description TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES tbl_users(id) ON DELETE CASCADE
);

CREATE TABLE IF NOT EXISTS tbl_entry_shop_coin_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    entry_count INT NOT NULL,
    bonus_entries INT DEFAULT 0,
    coin_price INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tbl_entry_shop_cash_packages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    entry_count INT NOT NULL,
    price_cents INT NOT NULL,
    is_active BOOLEAN DEFAULT TRUE,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

-- Phase 4: Auction Enhancements
ALTER TABLE tbl_auctions ADD COLUMN is_staged BOOLEAN DEFAULT FALSE;
ALTER TABLE tbl_auctions ADD COLUMN master_data_profile_id INT NULLABLE;

CREATE TABLE IF NOT EXISTS tbl_auction_master_profiles (
    id INT AUTO_INCREMENT PRIMARY KEY,
    profile_name VARCHAR(255) NOT NULL,
    tab1_data JSON NOT NULL,
    created_by_admin_id INT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tbl_auction_locations (
    auction_id INT NOT NULL,
    location_id INT NOT NULL,
    PRIMARY KEY (auction_id, location_id),
    FOREIGN KEY (auction_id) REFERENCES tbl_auctions(id) ON DELETE CASCADE,
    FOREIGN KEY (location_id) REFERENCES tbl_locations(id) ON DELETE CASCADE
);

-- Phase 5: Media Content
CREATE TABLE IF NOT EXISTS tbl_video_seasons (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season_name VARCHAR(255) NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
);

CREATE TABLE IF NOT EXISTS tbl_video_episodes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    season_id INT NOT NULL,
    episode_name VARCHAR(255) NOT NULL,
    youtube_url TEXT NOT NULL,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (season_id) REFERENCES tbl_video_seasons(id) ON DELETE CASCADE
);
