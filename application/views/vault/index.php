<?php
$bg_image = base_url('assets/images/vault_bg.png'); // Ensure this image exists
?>

<div class="vault-container" style="background-color: #0b0f19; color: #fff; font-family: 'Inter', sans-serif;">
    
   
    <section class="vault-hero">
        <div class="container position-relative z-2">
            <div class="row align-items-center">
                
                <div class="col-lg-6 mb-5 mb-lg-0">
                    <div class="vault-hero-badge">
                        <i class="bi bi-star-fill me-1"></i> THE #1 COMMUNITY FOR STORAGE SHED PUNTERS
                    </div>
                    <h1 class="vault-hero-title">
                        STORAGE PUNTERS <span>REWARDS CLUB.</span>
                    </h1>
                    <p class="vault-hero-copy">
                        The world's first rewards club for storage auction addicts. Bid on auctions, crack The Vault, and go in the draw to win monthly prizes.
                    </p>
                    <p class="vault-hero-prize">
                        Next prize: <span><?php echo $active_draw ? '$' . number_format($active_draw->prize_value_cents / 100) : '$20,000'; ?> CASH.</span>
                    </p>

                    <div class="vault-hero-cta">
                        <p class="mb-0">ENTER THIS MONTH'S DRAW FROM JUST <span>$9 MONTH</span>, CANCEL ANYTIME.</p>
                        <a href="<?= $has_active_subscription ? site_url('show-dashboard/membership') : site_url('join') ?>">
                            <?= $has_active_subscription ? 'EXPLORE UPGRADE OPTIONS' : 'GET STARTED NOW' ?> <i class="bi bi-chevron-right"></i>
                        </a>
                    </div>

                    <div class="d-flex flex-wrap gap-3">
                        <a href="<?= $has_active_subscription ? site_url('show-dashboard/membership') : site_url('join') ?>" class="vault-btn-primary">
                            <?= $has_active_subscription ? 'UPGRADE AND GET MORE ENTRIES' : 'JOIN AS A PUNTER' ?> <i class="bi bi-chevron-right ms-2"></i>
                        </a>
                        <a href="<?= site_url('auctions') ?>" class="vault-btn-secondary">
                            <i class="bi bi-hammer me-2"></i> GO TO LIVE AUCTIONS
                        </a>
                    </div>

                    <div class="vault-hero-members">
                        <div class="d-flex me-3">
                            <i class="bi bi-person-circle"></i>
                            <i class="bi bi-person-circle"></i>
                            <i class="bi bi-person-circle"></i>
                        </div>
                        <small>Join 15,000+ punters active today</small>
                    </div>
                </div>

               
                <div class="col-lg-5 offset-lg-1">
                    <div class="vault-card">
                        <div class="text-end">
                            <span class="vault-card-badge">THE VAULT GIVEAWAY</span>
                        </div>
                        <div class="vault-card-icon">
                            <i class="bi bi-lock-fill"></i>
                        </div>
                        <div class="vault-card-label">THE VAULT GIVEAWAY</div>
                        <div class="vault-card-amount" id="sp-home-vault-amount">
                            <?php echo $active_draw ? '$' . number_format($active_draw->prize_value_cents / 100) . ' CASH' : '$20,000 CASH'; ?>
                        </div>
                        <div class="vault-card-sub">MONTHLY DRAW</div>

                       
                        <div class="vault-card-countdown" id="vault-countdown">
                            <div class="item">
                                <span id="days">00</span>
                                <small>DAYS</small>
                            </div>
                            <div class="item">
                                <span id="hours">00</span>
                                <small>HRS</small>
                            </div>
                            <div class="item">
                                <span id="minutes">00</span>
                                <small>MINS</small>
                            </div>
                            <div class="item">
                                <span id="seconds">00</span>
                                <small>SECS</small>
                            </div>
                        </div>

                        <a href="<?= $has_active_subscription ? site_url('show-dashboard/membership') : site_url('join') ?>" class="vault-card-button">
                            <?= $has_active_subscription ? 'UPGRADE AND GET MORE ENTRIES' : 'JOIN AS A PUNTER' ?>
                        </a>

                        <?php
                        $second_prize_amount = ($active_draw && isset($active_draw->prize_2nd_cents) && (int)$active_draw->prize_2nd_cents > 0)
                            ? '$' . number_format($active_draw->prize_2nd_cents / 100)
                            : 'TBA';
                        $third_prize_amount = ($active_draw && isset($active_draw->prize_3rd_cents) && (int)$active_draw->prize_3rd_cents > 0)
                            ? '$' . number_format($active_draw->prize_3rd_cents / 100)
                            : 'TBA';
                    ?>
                    <div class="vault-card-footer">
                        <div>UPCOMING DRAW</div>
                        <div><?php echo $active_draw ? date('g:i A d M Y', strtotime($active_draw->draw_at)) : 'COMING SOON'; ?></div>
                        <div class="text-warning">
                            2nd: <?php echo $second_prize_amount; ?> • 3rd: <?php echo $third_prize_amount; ?>
                        </div>
                    </div>
                        <div id="draw-status-text" class="mt-2 small text-muted text-uppercase fw-bold"></div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <div class="bg-warning text-dark fw-bold text-uppercase py-3 overflow-hidden">
        <div class="container d-flex justify-content-between align-items-center" style="font-family: 'Inter', sans-serif; font-style: italic;">
            <a href="<?= site_url('join') ?>" style="color: #000; text-decoration: none;"><i class="bi bi-trophy me-2"></i> Join as a Punter</a>
            <span class="opacity-50">/</span>
            <span><i class="bi bi-people me-2"></i> Get Draw Entries</span>
            <span class="opacity-50">/</span>
            <span><i class="bi bi-hammer me-2"></i> Bid on Storage Auctions</span>
            <span class="opacity-50">/</span>
            <span><i class="bi bi-unlock me-2"></i> CRACK THE VAULT</span>
        </div>
    </div>

   
    <section class="py-4 bg-dark">
        <div class="container">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="p-4 rounded-4 bg-white bg-opacity-5 border border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-secondary mb-1">Your Total Entries</h6>
                            <h2 class="display-6 fw-bold mb-0 text-info" id="user-entries-display"><?php echo number_format($user_entries); ?></h2>
                        </div>
                        <i class="bi bi-ticket-perforated fs-1 text-info opacity-50"></i>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="p-4 rounded-4 bg-white bg-opacity-5 border border-secondary border-opacity-25 d-flex align-items-center justify-content-between">
                        <div>
                            <h6 class="text-uppercase text-secondary mb-1">Coin Balance</h6>
                            <h2 class="display-6 fw-bold mb-0 text-warning" id="user-coins-display"><?php echo number_format($coin_balance); ?></h2>
                        </div>
                        <i class="bi bi-coin fs-1 text-warning opacity-50"></i>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="py-5 bg-darker" style="background-color: #05070a;">
        <div class="container my-5">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h6 class="text-warning text-uppercase mb-2"><i class="bi bi-tv me-2"></i>Watch Now</h6>
                    <h2 class="display-5 fw-bold fst-italic text-white">STORAGE PUNTERS TV</h2>
                </div>
                <a href="<?= site_url('videos') ?>" class="text-white text-decoration-none fw-bold">View All Episodes <i class="bi bi-arrow-right ms-1"></i></a>
            </div>

            <div class="row g-4">
                <?php if(!empty($seasons) && !empty($seasons[0]['episodes'])): ?>
                    <?php 
                    $latest_episodes = array_slice($seasons[0]['episodes'], 0, 3);
                    foreach($latest_episodes as $ep): 
                    ?>
                    <div class="col-md-4">
                        <div class="card border-0 bg-transparent text-white group-hover-zoom">
                            <div class="position-relative overflow-hidden rounded-3 mb-3">
                                <img src="https://img.youtube.com/vi/<?= get_youtube_id($ep['youtube_url']) ?>/maxresdefault.jpg" class="img-fluid w-100 transition-transform" alt="Episode Thumbnail" style="filter: brightness(0.8);">
                                <a href="<?= $ep['youtube_url'] ?>" target="_blank" class="position-absolute top-50 start-50 translate-middle btn btn-light rounded-circle p-3 shadow-lg playback-btn">
                                    <i class="bi bi-play-fill fs-2 text-dark"></i>
                                </a>
                                <span class="badge bg-danger position-absolute bottom-0 start-0 m-3">NEW</span>
                            </div>
                            <h5 class="fw-bold mb-1"><?= $ep['episode_name'] ?></h5>
                            <small class="text-secondary"><?= $seasons[0]['season_name'] ?> • <?= date('M d, Y', strtotime($ep['created_at'])) ?></small>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div class="col-12 text-center text-muted py-5">
                        <p>No episodes available yet. Stay tuned!</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </section>

    
    <section class="py-5 bg-white text-dark">
        <div class="container my-5 text-center">
            <h2 class="fw-black text-uppercase mb-5 display-5" style="color: #0b0f19;">HOW THE CLUB WORKS</h2>
            <p class="lead text-muted mb-5 mw-700 mx-auto">Unlike standard auctions, being a Storage Punter gives you multiple ways to win. It's not just about buying junk—it's about the thrill of the find.</p>

            <div class="row g-4 text-start">
                <div class="col-md-4">
                    <div class="p-4 h-100 rounded-4 border bg-white shadow-sm hover-lift">
                        <div class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded p-3 mb-4">
                            <i class="bi bi-crown fs-3"></i>
                        </div>
                        <h4 class="fw-bold mb-3">1. Join as a Punter</h4>
                        <p class="text-muted small mb-3">Join the exclusive community. Get <span class="fw-bold text-success">Bonus entries into The Vault</span>, access to tools, merch discounts, and our private community.</p>
                        <ul class="list-unstyled small text-secondary">
                            <li class="mb-1"><i class="bi bi-dot text-success me-1"></i> Bonus Vault Entries</li>
                            <li><i class="bi bi-dot text-success me-1"></i> 0% Bidding Fees Available</li>
                        </ul>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-4 h-100 rounded-4 bg-dark text-white shadow-lg transform-scale-sm">
                        <div class="d-inline-flex align-items-center justify-content-center bg-warning text-dark rounded p-3 mb-4">
                            <i class="bi bi-hammer fs-3"></i>
                        </div>
                        <h4 class="fw-bold mb-3">2. Bid on Storage Auctions</h4>
                        <p class="opacity-75 small mb-4">We curate "Staged Sheds" packed with visible value. Punters bid on these unique lots.</p>
                        
                        <div class="p-3 rounded bg-white bg-opacity-10 border border-secondary border-opacity-25">
                            <small class="text-warning fw-bold text-uppercase d-block mb-1">THE MULTIPLIER EFFECT</small>
                            <p class="small mb-0 opacity-75">Every bid you place grants you <span class="fw-bold text-white">COINS to buy BONUS ENTRIES</span> into The Vault draw.</p>
                        </div>
                    </div>
                </div>

                <div class="col-md-4">
                    <div class="p-4 h-100 rounded-4 border bg-white shadow-sm hover-lift">
                        <div class="d-inline-flex align-items-center justify-content-center bg-dark text-white rounded p-3 mb-4">
                            <i class="bi bi-trophy fs-3"></i>
                        </div>
                        <h4 class="fw-bold mb-3">3. CRACK THE VAULT</h4>
                        <p class="text-muted small mb-3">The ultimate prize. A sealed unit containing massive prizes. The first drop is a <span class="fw-bold text-success">$20k value</span> haul.</p>
                        <h2 class="fw-black mb-0 display-4">$20k+</h2>
                        <small class="text-uppercase fw-bold text-muted">FIRST PRIZE</small>
                    </div>
                </div>
            </div>
        </div>
    </section>

    
    <section class="entries-accumulation">
        <div class="container text-center">
            <p class="text-warning text-uppercase fw-bold mb-2">THE LONGER YOU'RE WITH US, THE MORE YOU STACK</p>
            <h2 class="display-4 fw-black mb-1">ENTRIES THAT NEVER STOP</h2>
            <h2 class="display-4 fw-black text-warning mb-5">ACCUMULATING</h2>
            <p class="text-secondary mb-5">The longer you're with us, the more monthly entries you'll collect, and they never expire!</p>

            <div class="entries-toggle">
                <button class="active" data-tier="players">Players</button>
                <button data-tier="sharks">Sharks</button>
                <button data-tier="whales">Whales</button>
            </div>

            <div class="entries-table" id="entries-table"></div>
        </div>
    </section>

    <!-- Current Prizes -->
    <section class="py-5" style="background-color: #0b0f19;">
        <div class="container">
            <div class="d-flex justify-content-between align-items-end mb-4">
                <div>
                    <h2 class="fw-bold mb-0 text-white">CURRENT PRIZES</h2>
                    <p class="text-secondary mb-0">Items currently locked in the vault</p>
                </div>
            </div>
            <div class="row g-4" id="vault-items-container">
                <!-- Items will be loaded via JS -->
            </div>
        </div>
    </section>

    <!-- Hall of Fame -->
    <section class="py-5 bg-black bg-opacity-50">
        <div class="container">
            <h2 class="fw-bold mb-4 text-center text-white">🏆 HALL OF FAME</h2>
            <div class="table-responsive">
                <table class="table table-dark table-hover align-middle custom-table">
                    <thead>
                        <tr>
                            <th>Winner</th>
                            <th>Prize</th>
                            <th>Draw Date</th>
                            <th>Winning Ticket</th>
                            <th>Certificate</th>
                        </tr>
                    </thead>
                    <tbody id="winners-table-body">
                        <?php if(!empty($hall_of_fame)): ?>
                            <?php foreach($hall_of_fame as $winner): ?>
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center text-white">
                                        <i class="bi bi-person-circle me-2 fs-4 opacity-50"></i>
                                        <span><?php echo htmlspecialchars($winner->winner_display); ?></span>
                                    </div>
                                </td>
                                <td class="text-warning fw-bold"><?php echo htmlspecialchars($winner->draw_title); ?></td>
                                <td class="text-white opacity-75"><?php echo date('M j, Y', strtotime($winner->won_at)); ?></td>
                                <td><span class="badge bg-secondary"><?php echo htmlspecialchars($winner->punter_id); ?></span></td>
                                <td>
                                    <?php if(!empty($winner->certificate_url)): ?>
                                        <a href="<?php echo htmlspecialchars($winner->certificate_url); ?>" target="_blank" class="btn btn-sm btn-outline-warning">
                                            <i class="bi bi-file-earmark-check me-1"></i>TPAL
                                        </a>
                                    <?php else: ?>
                                        <span class="text-muted small">-</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center py-4">No winners yet. You could be the first!</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </section>
</div>

<!-- Coin Shop Modal -->
<div class="modal fade" id="coinShopModal" tabindex="-1">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content bg-dark text-white border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title">ENTRY SHOP</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="d-flex justify-content-between align-items-center mb-4 p-3 bg-white bg-opacity-5 rounded-4 border border-secondary border-opacity-25">
                    <div>
                        <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.65rem; letter-spacing: 1px;">Your Current Balance</small>
                        <div class="d-flex align-items-center">
                            <i class="bi bi-coin text-warning fs-4 me-2"></i>
                            <span class="fs-4 fw-bold text-warning" id="modal-user-coins"><?php echo number_format($coin_balance); ?></span>
                        </div>
                    </div>
                    <div class="text-end">
                        <small class="text-muted text-uppercase fw-bold d-block mb-1" style="font-size: 0.65rem; letter-spacing: 1px;">Entries for this Draw</small>
                        <div class="d-flex align-items-center justify-content-end">
                            <i class="bi bi-ticket-perforated text-info fs-4 me-2"></i>
                            <span class="fs-4 fw-bold text-info" id="modal-user-entries"><?php echo number_format($user_entries); ?></span>
                        </div>
                    </div>
                </div>

                <ul class="nav nav-pills mb-4 justify-content-center custom-pills" id="shopTabs">
                    <li class="nav-item">
                        <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#coin-packages">
                            <i class="bi bi-coin me-2"></i>Spend Coins
                        </button>
                    </li>
                    <li class="nav-item">
                        <button class="nav-link" data-bs-toggle="pill" data-bs-target="#cash-packages">
                            <i class="bi bi-credit-card me-2"></i>Cash Top-up
                        </button>
                    </li>
                </ul>
                <div class="tab-content" id="shopTabsContent">
                    <div class="tab-pane fade show active" id="coin-packages">
                        <div class="row g-3" id="coin-shop-container" style="min-height: 200px;">
                            <!-- Shop items loaded via JS -->
                        </div>
                    </div>
                    <div class="tab-pane fade" id="cash-packages">
                        <div class="row g-3" id="cash-shop-container" style="min-height: 200px;">
                            <!-- Cash items loaded via JS -->
                        </div>
                        <div class="mt-4 text-center p-3 bg-white bg-opacity-5 rounded border border-warning border-opacity-25">
                            <p class="mb-0 small text-warning"><i class="bi bi-info-circle me-1"></i> Membership Discount Applied! Shark (50%) Whale (75%)</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Matrix Reveal Overlay -->
<div class="matrix-overlay" id="matrix-reveal" style="display: none;">
    <canvas id="matrix-canvas" class="matrix-canvas"></canvas>
    <div class="winner-reveal-content text-center">
        <div id="reveal-countdown" class="display-1 fw-bold text-warning mb-4" style="font-family: monospace;">10</div>
        <div id="matrix-spinner" class="display-4 fw-bold mb-4" style="font-family: monospace; color: #0f0; text-shadow: 0 0 10px #0f0;">
            ********
        </div>
        <div id="winner-info" style="display: none; animation: fadeIn 1s;">
            <h1 class="display-1 fw-bold text-success mb-2" style="text-shadow: 0 0 20px rgba(0,255,0,0.5);">WINNER</h1>
            <div id="winner-name-reveal" class="display-3 fw-bold mb-2">TOBIAS C</div>
            <div id="winner-id-reveal" class="h2 text-warning mb-4">SP 1729</div>
            <div id="winner-prize-reveal" class="h4 text-white opacity-75">WON FORD RANGER</div>
            <button class="btn btn-lg btn-outline-success mt-4 px-5 rounded-pill" onclick="location.reload()">COLLECT PRIZE</button>
        </div>
    </div>
</div>

<style>
@keyframes fadeIn { from { opacity: 0; transform: scale(0.9); } to { opacity: 1; transform: scale(1); } }
.vault-container { max-width: 100%; overflow-x: clip; }
.vault-container * { box-sizing: border-box; }
.vault-hero {
    padding: 5rem 0;
    background: linear-gradient(135deg, #05070a 0%, #121822 50%, #05070a 100%);
}
.vault-hero-badge {
    display: inline-flex;
    align-items: center;
    background: #f6c945;
    color: #000;
    font-weight: 700;
    padding: 0.45rem 1rem;
    border-radius: 999px;
    letter-spacing: 1px;
    font-size: 0.85rem;
}
.vault-hero-title {
    font-size: clamp(2.5rem, 5vw, 4.5rem);
    font-weight: 900;
    text-transform: uppercase;
    line-height: 1;
}
.vault-hero-title span {
    color: #f6c945;
}
.vault-hero-copy {
    max-width: 520px;
    color: rgba(255,255,255,0.7);
    font-size: 1.1rem;
}
.vault-hero-prize span {
    color: #f6c945;
    font-weight: 800;
}
.vault-hero-cta {
    background: rgba(255,255,255,0.05);
    border: 1px solid rgba(255,255,255,0.1);
    border-radius: 16px;
    padding: 1rem 1.5rem;
    margin-bottom: 1.5rem;
    text-align: left;
}
.vault-hero-cta span { color: #f6c945; font-weight: 800; }
.vault-hero-cta a {
    display: inline-flex;
    align-items: center;
    color: #fff;
    font-weight: 700;
    text-decoration: none;
}
.vault-btn-primary,
.vault-btn-secondary {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    padding: 0.8rem 1.8rem;
    border-radius: 999px;
    font-weight: 800;
    text-transform: uppercase;
    text-decoration: none;
}
.vault-btn-primary {
    background: #f6c945;
    color: #000;
    box-shadow: 0 10px 30px rgba(246,201,69,0.3);
}
.vault-btn-secondary {
    border: 1px solid rgba(255,255,255,0.2);
    color: #fff;
}
.vault-hero-members i { margin-right: -8px; font-size: 1.5rem; opacity: 0.5; }

.vault-card {
    border-radius: 24px;
    padding: 2.5rem;
    background: radial-gradient(circle at top, rgba(246,201,69,0.15), rgba(11,15,25,0.95));
    border: 1px solid rgba(255,255,255,0.05);
    box-shadow: 0 30px 60px rgba(0,0,0,0.4);
    text-align: center;
}
.vault-card-badge {
    background: #ff375f;
    color: #fff;
    border-radius: 999px;
    padding: 0.35rem 1rem;
    font-weight: 700;
}
.vault-card-icon i { font-size: 4rem; color: #f6c945; }
.vault-card-amount {
    font-size: 2.8rem;
    font-weight: 900;
    color: #f6c945;
}
.vault-card-countdown {
    display: grid;
    grid-template-columns: repeat(4, minmax(70px, 1fr));
    gap: 1rem;
    margin: 1.5rem 0;
}
.vault-card-countdown .item {
    background: rgba(0,0,0,0.25);
    padding: 0.8rem 0.5rem;
    border-radius: 12px;
    border: 1px solid rgba(255,255,255,0.05);
}
.vault-card-countdown span { font-size: 1.6rem; font-weight: 900; }
.vault-card-button {
    display: block;
    background: #38b000;
    color: #fff;
    padding: 0.9rem;
    border-radius: 12px;
    text-decoration: none;
    font-weight: 800;
    text-transform: uppercase;
    letter-spacing: 1px;
}
.vault-card-footer {
    margin-top: 1.5rem;
    font-size: 0.9rem;
    opacity: 0.8;
}

.entries-accumulation {
    background: linear-gradient(180deg, #05070a 0%, #0b0f19 100%);
    padding: 6rem 0;
}
.entries-toggle {
    display: inline-flex;
    background: rgba(255,255,255,0.05);
    border-radius: 999px;
    padding: 0.3rem;
    margin-bottom: 2.5rem;
}
.entries-toggle button {
    border: none;
    background: transparent;
    color: rgba(255,255,255,0.6);
    font-weight: 700;
    text-transform: uppercase;
    padding: 0.6rem 2.2rem;
    border-radius: 999px;
}
.entries-toggle button.active {
    background: #f6c945;
    color: #000;
    box-shadow: 0 10px 30px rgba(246,201,69,0.4);
}
.entries-table-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    color: rgba(255,255,255,0.8);
    padding: 0.75rem 0;
    border-bottom: 1px solid rgba(255,255,255,0.05);
}
.entries-table-row .stars {
    flex: 1;
    margin: 0 1.5rem;
    display: flex;
    gap: 0.35rem;
    justify-content: space-between;
    min-width: 0;
}
.entries-table-row .stars span {
    flex: 1;
    text-align: center;
    font-size: 1rem;
    color: rgba(255,255,255,0.08);
    text-shadow: none;
    transition: color 0.2s ease, text-shadow 0.2s ease;
    line-height: 1;
}
.entries-table-row .stars span.active {
    color: #f6c945;
    text-shadow: 0 0 10px rgba(246,201,69,0.7);
}
.entries-table-row strong {
    min-width: 90px;
    text-align: right;
    font-weight: 800;
}

@media (max-width: 991.98px) {
    .vault-hero { padding: 3rem 0; }
    .vault-hero-copy { max-width: 100%; }
    .vault-card { padding: 1.5rem; }
    .vault-card-amount { font-size: 2.2rem; line-height: 1.1; word-break: break-word; }
    .vault-card-countdown { grid-template-columns: repeat(2, minmax(0, 1fr)); gap: 0.75rem; }
}

@media (max-width: 575.98px) {
    .bg-warning.text-dark.fw-bold.text-uppercase.py-3.overflow-hidden .container { flex-wrap: wrap; justify-content: center; gap: 0.75rem; text-align: center; }
    .bg-warning.text-dark.fw-bold.text-uppercase.py-3.overflow-hidden .container > span.opacity-50 { display: none; }
    .entries-toggle { width: 100%; display: flex; }
    .entries-toggle button { flex: 1; padding: 0.6rem 0.75rem; }
    .entries-table-row { flex-direction: column; align-items: flex-start; gap: 0.35rem; }
    .entries-table-row .stars { width: 100%; margin: 0; justify-content: space-between; }
    .entries-table-row .stars span { flex: 0 0 auto; width: calc(100% / 24); text-align: center; }
    .entries-table-row strong { min-width: 0; text-align: left; }
}

.matrix-overlay {
    position: fixed; top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.95); z-index: 9999;
    display: flex; align-items: center; justify-content: center;
}
.matrix-canvas { position: absolute; top: 0; left: 0; pointer-events: none; opacity: 0.3; }
.winner-reveal-content { position: relative; z-index: 1; }

.hover-lift { transition: transform 0.2s; }
.hover-lift:hover { transform: translateY(-5px); }

.group-hover-zoom:hover .transition-transform { transform: scale(1.05); }
.transition-transform { transition: transform 0.3s ease; }

.playback-btn { transition: transform 0.2s; opacity: 0.9; }
.group-hover-zoom:hover .playback-btn { transform: translate(-50%, -50%) scale(1.1); opacity: 1; }

.pulse-button {
    animation: pulse 2s infinite;
}
@keyframes pulse {
    0% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 193, 7, 0.7); }
    70% { transform: scale(1.05); box-shadow: 0 0 0 10px rgba(255, 193, 7, 0); }
    100% { transform: scale(1); box-shadow: 0 0 0 0 rgba(255, 193, 7, 0); }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    loadVaultData();
    loadPackages();
    initEntriesTable();
    
    const countdownEls = {
        days: document.getElementById("days"),
        hours: document.getElementById("hours"),
        minutes: document.getElementById("minutes"),
        seconds: document.getElementById("seconds")
    };

    let countdownInterval = null;
    let countdownResyncInterval = null;

    function initCountdownFromServer() {
        fetch('<?= site_url("vault/status") ?>')
            .then(res => res.json())
            .then(data => {
                const drawTimestamp = data && Number(data.draw_timestamp) ? Number(data.draw_timestamp) * 1000 : null;
                if (!drawTimestamp) return;
                const serverTime = data && Number(data.server_time) ? Number(data.server_time) * 1000 : Date.now();
                const clientOffset = Date.now() - serverTime;

                function getRemaining() {
                    return Math.floor((drawTimestamp - (Date.now() - clientOffset)) / 1000);
                }

                function renderCountdown(secs) {
                    const d = Math.floor(secs / 86400);
                    const h = Math.floor((secs % 86400) / 3600);
                    const m = Math.floor((secs % 3600) / 60);
                    const s = Math.max(0, secs % 60);
                    if(countdownEls.days) countdownEls.days.innerHTML = String(d).padStart(2, '0');
                    if(countdownEls.hours) countdownEls.hours.innerHTML = String(h).padStart(2, '0');
                    if(countdownEls.minutes) countdownEls.minutes.innerHTML = String(m).padStart(2, '0');
                    if(countdownEls.seconds) countdownEls.seconds.innerHTML = String(s).padStart(2, '0');
                }

                if (countdownInterval) clearInterval(countdownInterval);
                if (countdownResyncInterval) clearInterval(countdownResyncInterval);

                const tick = function() {
                    const remaining = getRemaining();
                    if (remaining <= 0) {
                        renderCountdown(0);
                        const statusEl = document.getElementById("draw-status-text");
                        if (statusEl) {
                            statusEl.innerHTML = "DRAW PENDING";
                            statusEl.className = "mt-3 small text-info text-uppercase fw-bold";
                        }
                        setTimeout(function() { loadVaultData(); }, 5000);
                        clearInterval(countdownInterval);
                        countdownInterval = null;
                        return;
                    }
                    renderCountdown(remaining);
                };

                tick();
                countdownInterval = setInterval(tick, 1000);
                countdownResyncInterval = setInterval(initCountdownFromServer, 60000);
            });
    }

    initCountdownFromServer();

    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('success')) {
        Swal.fire({
            title: 'Purchase Successful!',
            text: 'Your entries have been added to the vault draw.',
            icon: 'success',
            confirmButtonColor: '#ffc107'
        });
        window.history.replaceState({}, document.title, window.location.pathname);
    }
});

function initEntriesTable() {
    const schedules = {
        players: [
            { month: 'Month 1', entries: 1 },
            { month: 'Month 3', entries: 3 },
            { month: 'Month 6', entries: 6 },
            { month: 'Month 12', entries: 12 },
            { month: 'Month 18', entries: 18 },
            { month: 'Month 24', entries: 24 }
        ],
        sharks: [
            { month: 'Month 1', entries: 5 },
            { month: 'Month 3', entries: 15 },
            { month: 'Month 6', entries: 30 },
            { month: 'Month 12', entries: 60 },
            { month: 'Month 18', entries: 90 },
            { month: 'Month 24', entries: 120 }
        ],
        whales: [
            { month: 'Month 1', entries: 20 },
            { month: 'Month 3', entries: 60 },
            { month: 'Month 6', entries: 120 },
            { month: 'Month 12', entries: 240 },
            { month: 'Month 18', entries: 360 },
            { month: 'Month 24', entries: 480 }
        ]
    };

    const maxEntries = {
        players: Math.max(...schedules.players.map(item => item.entries)),
        sharks: Math.max(...schedules.sharks.map(item => item.entries)),
        whales: Math.max(...schedules.whales.map(item => item.entries))
    };

    const tableEl = document.getElementById('entries-table');
    if (!tableEl) return;

    const buttons = document.querySelectorAll('.entries-toggle button');
    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            renderEntries(btn.dataset.tier);
        });
    });

    renderEntries('players');

    function renderEntries(tier) {
        const data = schedules[tier];
        const cap = maxEntries[tier] || 1;
        const slots = 24;
        tableEl.innerHTML = data.map(item => {
            const ratio = cap > 0 ? (item.entries / cap) : 0;
            const activeStars = Math.max(1, Math.min(slots, Math.ceil(ratio * slots)));
            const stars = Array.from({ length: slots }, (_, i) => `<span class="${i < activeStars ? 'active' : ''}">★</span>`).join('');
            return `
                <div class="entries-table-row">
                    <div class="text-uppercase small text-secondary fw-bold">${item.month}</div>
                    <div class="stars">${stars}</div>
                    <strong>${item.entries} ENTRIES</strong>
                </div>
            `;
        }).join('');
    }
}

function loadVaultData() {
    fetch('<?= site_url("api/vault/current") ?>')
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                const draw = res.data.draw;
                const items = res.data.items;
                const user = res.data.user;

                if (user) {
                    document.getElementById('modal-user-coins').innerText = Number(user.coins).toLocaleString();
                    document.getElementById('modal-user-entries').innerText = Number(user.entries).toLocaleString();
                    document.getElementById('user-entries-display').innerText = Number(user.entries).toLocaleString();
                    document.getElementById('user-coins-display').innerText = Number(user.coins).toLocaleString();
                }

                if (draw) {
                    renderItems(items);
                    if (draw.status === 'active') {
                        document.getElementById("draw-status-text").innerHTML = "LIVE GIVEAWAY ENDING SOON";
                        document.getElementById("draw-status-text").className = "mt-3 small text-success text-uppercase fw-bold";
                    } else if (draw.status === 'locked' || draw.status === 'awaiting_draw') {
                        document.getElementById("draw-status-text").innerHTML = "DRAW PENDING - WINNER SELECTION IN PROGRESS";
                        document.getElementById("draw-status-text").className = "mt-3 small text-info text-uppercase fw-bold";
                    } else if (draw.status === 'completed') {
                        document.getElementById("draw-status-text").innerHTML = "DRAW COMPLETED";
                        document.getElementById("draw-status-text").className = "mt-3 small text-muted text-uppercase fw-bold";
                    } else {
                        document.getElementById("draw-status-text").innerHTML = "UPCOMING DRAW";
                        document.getElementById("draw-status-text").className = "mt-3 small text-warning text-uppercase fw-bold";
                    }
                }
            }

            if (res.data.last_winner) {
                const winner = res.data.last_winner;
                const wonAt = new Date(winner.won_at).getTime();
                const now = new Date().getTime();
                // If draw was completed in the last 2 minutes, show animation
                if (now - wonAt < 120000) { 
                    if (!sessionStorage.getItem('shown_winner_' + winner.id)) {
                        startReveal(winner);
                        sessionStorage.setItem('shown_winner_' + winner.id, 'true');
                    }
                }
            }
        });
}

function renderItems(items) {
    const container = document.getElementById('vault-items-container');
    if (!container) return;
    if (!items || items.length === 0) {
        container.innerHTML = '<div class="col-12 text-center text-muted py-4">No items listed in this draw yet.</div>';
        return;
    }
    container.innerHTML = items.map(item => `
        <div class="col-md-4">
            <div class="prize-card h-100 bg-dark text-white rounded overflow-hidden shadow-sm border border-secondary">
                <div class="prize-image-container position-relative" style="height: 200px; overflow: hidden;">
                    <img src="${item.image_url || 'https://via.placeholder.com/400x250'}" class="w-100 h-100 object-fit-cover" alt="${item.title}">
                </div>
                <div class="p-3">
                    <span class="badge bg-warning text-dark mb-2">Value: $${Number(item.estimated_value || 0).toLocaleString()}</span>
                    <h5 class="fw-bold mb-2">${item.title}</h5>
                    <p class="text-secondary small mb-0">${item.description || ''}</p>
                </div>
            </div>
        </div>
    `).join('');
}

function loadPackages() {
    fetch('<?= site_url("api/vault/packages") ?>')
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                renderPackages(res.data.coin_packages, 'coin-shop-container', true);
                renderPackages(res.data.cash_packages, 'cash-shop-container', false);
            }
        });
}

function renderPackages(packages, containerId, isCoin) {
    const container = document.getElementById(containerId);
    if (!container) return;
    
    container.innerHTML = packages.map(pkg => `
        <div class="col-md-4 mb-4">
            <div class="shop-package position-relative h-100 d-flex flex-column justify-content-between p-4 border border-secondary rounded">
                ${pkg.bonus_entries > 0 ? `<span class="badge bg-warning text-dark position-absolute top-0 end-0 m-3 shadow-sm">+${pkg.bonus_entries} BONUS</span>` : ''}
                <div class="text-center mb-4">
                    <h2 class="package-entries-count mb-0 fw-bold">${Number(pkg.entry_count) + Number(pkg.bonus_entries || 0)}</h2>
                    <p class="small text-uppercase tracking-wider opacity-75 mb-0 text-warning" style="font-weight: 700;">Entries</p>
                </div>
                <button class="btn ${isCoin ? 'btn-warning text-dark' : 'btn-outline-warning'} w-100 py-3 rounded-pill fw-bold" onclick="buyEntries(${pkg.id}, ${isCoin})">
                    ${isCoin ? `<i class="bi bi-coin me-2"></i>${pkg.coin_price}` : `<i class="bi bi-credit-card me-2"></i>$${Number(pkg.price_aud).toFixed(2)}`}
                </button>
            </div>
        </div>
    `).join('');
}

function buyEntries(id, isCoin) {
    if (!isCoin) {
        window.location.href = '<?= site_url("stripe/checkout/entries/") ?>' + id;
        return;
    }

    const formData = new FormData();
    formData.append('package_id', id);

    fetch('<?= site_url("api/vault/buy-entries") ?>', {
        method: 'POST',
        body: formData
    })
    .then(res => res.json())
    .then(res => {
        if(res.success) {
            Swal.fire({ title: 'Success!', text: res.message, icon: 'success' });
            loadVaultData(); // Refresh balances
        } else {
            Swal.fire({
                title: 'Check your balance!',
                text: res.message,
                icon: 'warning',
                confirmButtonText: 'Earn more Coins',
                showCancelButton: true
            }).then((result) => {
                if (result.isConfirmed) window.location.href = '<?= site_url("auctions") ?>';
            });
        }
    });
}

function startReveal(winnerData) {
    const overlay = document.getElementById('matrix-reveal');
    const countdownEl = document.getElementById('reveal-countdown');
    const spinnerEl = document.getElementById('matrix-spinner');
    const infoEl = document.getElementById('winner-info');
    
    overlay.style.display = 'flex';
    initMatrix(); 

    let timeLeft = 10;
    const finalString = winnerData.winner_display;
    
    const countInterval = setInterval(() => {
        timeLeft--;
        countdownEl.innerText = timeLeft;
        if (timeLeft <= 0) {
            clearInterval(countInterval);
            countdownEl.style.display = 'none';
        }
    }, 1000);

    const spinInterval = setInterval(() => {
        let randomStr = '';
        const chars = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        for (let i = 0; i < finalString.length; i++) {
            randomStr += chars.charAt(Math.floor(Math.random() * chars.length));
        }
        spinnerEl.innerText = randomStr;
    }, 50);

    setTimeout(() => {
        clearInterval(spinInterval);
        spinnerEl.innerText = finalString;
        spinnerEl.style.color = '#ffc107'; 
        
        infoEl.style.display = 'block';
        document.getElementById('winner-name-reveal').innerText = winnerData.winner_display;
        document.getElementById('winner-id-reveal').innerText = winnerData.punter_id;
        document.getElementById('winner-prize-reveal').innerText = `WON ${winnerData.prize_title || 'THE VAULT DROP'}`;
        
        if (typeof confetti === 'function') {
            confetti({ particleCount: 150, spread: 70, origin: { y: 0.6 }, colors: ['#ffc107', '#ffffff'] });
        }
    }, 10000);
}

function initMatrix() {
    const canvas = document.getElementById('matrix-canvas');
    if (!canvas) return;
    const ctx = canvas.getContext('2d');
    canvas.width = window.innerWidth;
    canvas.height = window.innerHeight;
    const letters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789$#@&%';
    const fontSize = 16;
    const columns = canvas.width / fontSize;
    const drops = [];
    for (let i = 0; i < columns; i++) drops[i] = 1;

    function draw() {
        ctx.fillStyle = 'rgba(0, 0, 0, 0.05)';
        ctx.fillRect(0, 0, canvas.width, canvas.height);
        ctx.fillStyle = '#0f0';
        ctx.font = fontSize + 'px monospace';
        for (let i = 0; i < drops.length; i++) {
            const text = letters.charAt(Math.floor(Math.random() * letters.length));
            ctx.fillText(text, i * fontSize, drops[i] * fontSize);
            if (drops[i] * fontSize > canvas.height && Math.random() > 0.975) drops[i] = 0;
            drops[i]++;
        }
    }
    const matrixInt = setInterval(draw, 33);
    window.addEventListener('resize', () => { canvas.width = window.innerWidth; canvas.height = window.innerHeight; });
}

<?php 
function get_youtube_id($url) {
    preg_match('/(?:https?:\/\/)?(?:www\.)?(?:youtube\.com\/(?:[^\/\n\s]+\/\S+\/|(?:v|e(?:mbed)?)\/|\S*?[?&]v=)|youtu\.be\/)([a-zA-Z0-9_-]{11})/', $url, $matches);
    return $matches[1] ?? '';
}
?>
</script>
