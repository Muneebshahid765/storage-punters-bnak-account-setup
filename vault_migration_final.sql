-- Phase 4: Auction Enhancements (Partial)
ALTER TABLE tbl_auctions ADD COLUMN master_data_profile_id INT NULL;

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
