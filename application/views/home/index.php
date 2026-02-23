<div class="p-0 container-fluid">
    <main>
        <!-- Hero Section -->
        <section class="hero-modern">
            <div class="hero-overlay"></div>
            <div class="container">
                <div class="hero-content">
                    <h1 class="hero-title">Discover Hidden Treasures</h1>
                    <p class="hero-subtitle">Bid on storage auctions near you</p>
                    <div class="hero-actions">
                        <button class="btn-primary-modern" data-bs-toggle="modal" data-bs-target="#unifiedAuthModal" onclick="showBidderRegister()">
                            <i class="bi bi-hammer me-2"></i>Start Bidding
                        </button>
                        <button class="btn-secondary-modern" data-bs-toggle="modal" data-bs-target="#unifiedAuthModal" onclick="showFacilityRegister()">
                            <i class="bi bi-building me-2"></i>List Auction
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <!-- Search Section -->
        <div class="search-section">
            <div class="container">
                <div class="search-card">
                    <form action="<?php echo site_url('auctions'); ?>" method="GET" class="search-form">
                        <div class="search-input-wrapper">
                            <i class="bi bi-search"></i>
                            <input type="text" name="q" placeholder="Enter postcode" class="search-input">
                        </div>
                        <select name="distance" class="search-select">
                            <option value="50">50 km</option>
                            <option value="100">100 km</option>
                            <option value="200">200 km</option>
                        </select>
                        <button type="submit" class="search-btn">
                            <i class="bi bi-arrow-right"></i>
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <script>
        (function() {
            if (window.location.hash) return;
            if ('scrollRestoration' in history) {
                history.scrollRestoration = 'manual';
            }
            window.addEventListener('load', function() {
                try {
                    if (document.activeElement && typeof document.activeElement.blur === 'function') {
                        document.activeElement.blur();
                    }
                } catch (e) {}

                window.scrollTo(0, 0);
                requestAnimationFrame(function() {
                    window.scrollTo(0, 0);
                });
                setTimeout(function() {
                    window.scrollTo(0, 0);
                }, 50);
                setTimeout(function() {
                    window.scrollTo(0, 0);
                }, 250);
            });
        })();
        </script>

        <!-- Featured Auctions Section -->
        <section class="auctions-section feature_live_storage_auction">
            <div class="container">
                <div class="section-header">
                    <div>
                        <h2 class="section-title-modern">Live Auctions</h2>
                        <p class="section-desc">Ending soon</p>
                    </div>
                    <a href="<?php echo site_url('auctions'); ?>" class="view-all-link d-none d-md-flex">
                        View All <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>

                <div class="feature__live__section__container__grid row">
                    <!-- Populated by JS -->
                </div>

                <div class="text-center mt-4 d-md-none">
                    <a href="<?php echo site_url('auctions'); ?>" class="btn-view-all-mobile">
                        View All Auctions
                    </a>
                </div>
            </div>
        </section>

		<!-- How It Works Section -->
		<section class="steps-section how-it-works-modern">
			<div class="container">
				<div class="hiw-layout">
					<div class="hiw-copy">
						<span class="hiw-tag">Storage Punters Club</span>
						<h2>How It Works</h2>
						<p>Sign up for a FREE account. Then you can bid on real storage units all over Australia!</p>
						<ul class="hiw-stats">
							<li>
								<strong>BID</strong>
								<span>On real storage units.</span>
							</li>
							<li>
								<strong>WIN</strong>
								<span>Find cool hidden treasures.</span>
							</li>
							<li>
								<strong>MEMBERSHIP</strong>
								<span>Get rewards and enter to win prizes!</span>
							</li>
						</ul>
					</div>
					<div class="hiw-steps">
						<div class="hiw-card">
							<div class="hiw-card-head">
								<span class="hiw-step-index">01</span>
								<div class="hiw-icon">
									<i class="bi bi-person-check"></i>
								</div>
							</div>
							<div class="hiw-card-body">
								<h3>Register</h3>
								<p>Create your account in seconds and verify your bidder profile.</p>
							</div>
						</div>
						<div class="hiw-card">
							<div class="hiw-card-head">
								<span class="hiw-step-index">02</span>
								<div class="hiw-icon">
									<i class="bi bi-trophy"></i>
								</div>
							</div>
							<div class="hiw-card-body">
								<h3>Win</h3>
								<p>Download your winning invoice and collect the unit within the 72-hour pickup window.</p>
							</div>
						</div>
					</div>
				</div>
			</div>
		</section>

        <!-- The Vault Giveaway Section -->
        <section class="sp-home-vault">
            <div class="container">
                <div class="sp-home-vault-content">
                    <div>
                        <span class="sp-home-vault-badge">THE VAULT GIVEAWAY</span>
                        <h2>WIN <span class="highlight">MONTHLY CASH</span> PRIZES</h2>
                        <p class="sp-home-vault-desc">
                            Join the Storage Punters Rewards Club and go in the draw to win monthly cash giveaways. 
                            The longer you stay, the more entries you earn — they compound every month.
                        </p>
                        <p class="sp-home-vault-prize">
                            Current prize pool: <span id="home-vault-prize">Loading...</span>
                        </p>
                        <div class="sp-home-vault-cta-box">
                            <p>Plans start from just <span class="price">$9/month</span>. Cancel anytime.</p>
                            <a href="<?= site_url('join') ?>">View all plans <i class="bi bi-chevron-right"></i></a>
                        </div>
                        <div class="sp-home-vault-buttons">
                            <a href="<?= site_url('join') ?>" class="sp-home-vault-btn primary sp-pulse">
                                <i class="bi bi-trophy-fill"></i> JOIN AS A PUNTER
                            </a>
                            <a href="<?= site_url('vault') ?>" class="sp-home-vault-btn secondary">
                                <i class="bi bi-lock-fill"></i> VIEW THE VAULT
                            </a>
                        </div>
                        <div class="sp-home-vault-members">
                            <i class="bi bi-people-fill"></i>
                            <small>Join thousands of punters already in the draw</small>
                        </div>
                    </div>
                    <div>
                        <div class="sp-vault-card">
                            <span class="sp-vault-card-badge">LIVE DRAW</span>
                            <div class="sp-vault-card-body">
                                <div class="sp-vault-card-icon"><i class="bi bi-lock-fill"></i></div>
                                <div class="sp-vault-card-label">THE VAULT GIVEAWAY</div>
                                <div class="sp-vault-card-amount" id="home-vault-amount">$0 CASH</div>
                                <div class="sp-vault-card-draw-label">MONTHLY DRAW</div>
                                <div class="sp-vault-countdown" id="home-vault-countdown">
                                    <div class="sp-vault-countdown-item"><span class="number" id="hv-days">00</span><span class="unit">Days</span></div>
                                    <div class="sp-vault-countdown-item"><span class="number" id="hv-hours">00</span><span class="unit">Hrs</span></div>
                                    <div class="sp-vault-countdown-item"><span class="number" id="hv-mins">00</span><span class="unit">Mins</span></div>
                                    <div class="sp-vault-countdown-item"><span class="number" id="hv-secs">00</span><span class="unit">Secs</span></div>
                                </div>
                                <a href="<?= site_url('join') ?>" class="sp-vault-join-btn sp-pulse">JOIN AS A PUNTER</a>
                            </div>
                            <div class="sp-vault-card-footer">
                                <div class="upcoming">DRAW DATE</div>
                                <div class="draw-date" id="home-vault-draw-date">Loading...</div>
                                <div class="prizes" id="home-vault-extra-prizes"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <script>
        (function() {
            const countdownIds = {
                days: document.getElementById('hv-days'),
                hours: document.getElementById('hv-hours'),
                mins: document.getElementById('hv-mins'),
                secs: document.getElementById('hv-secs')
            };

            function renderCountdown(seconds) {
                var dd = Math.floor(seconds / 86400);
                var hh = Math.floor((seconds % 86400) / 3600);
                var mm = Math.floor((seconds % 3600) / 60);
                var ss = Math.max(0, seconds % 60);
                if (countdownIds.days) countdownIds.days.textContent = String(dd).padStart(2, '0');
                if (countdownIds.hours) countdownIds.hours.textContent = String(hh).padStart(2, '0');
                if (countdownIds.mins) countdownIds.mins.textContent = String(mm).padStart(2, '0');
                if (countdownIds.secs) countdownIds.secs.textContent = String(ss).padStart(2, '0');
            }

            function showPendingMessage() {
                var container = document.getElementById('home-vault-countdown');
                if (container && !container.querySelector('.sp-vault-countdown-message')) {
                    container.insertAdjacentHTML('beforeend', '<div class="sp-vault-countdown-message">Draw pending… awaiting winner</div>');
                }
            }

            fetch('<?= site_url("vault/status") ?>')
                .then(function(r) { return r.json(); })
                .then(function(data) {
                    if (!data || !data.draw_details) return;
                    var draw = data.draw_details;
                    var amountText = draw.prize_formatted ? draw.prize_formatted + ' CASH' : '$0 CASH';

                    var amountEl = document.getElementById('home-vault-amount');
                    if (amountEl) amountEl.textContent = amountText;
                    var prizeEl = document.getElementById('home-vault-prize');
                    if (prizeEl) prizeEl.textContent = amountText;
                    var dateEl = document.getElementById('home-vault-draw-date');
                    if (dateEl) dateEl.textContent = draw.draw_date_formatted || '';

                    var extraEl = document.getElementById('home-vault-extra-prizes');
                    if (extraEl) {
                        var extras = [];
                        if (draw.prize_2nd) extras.push('<span>2nd: ' + draw.prize_2nd + '</span>');
                        if (draw.prize_3rd) extras.push('<span>3rd: ' + draw.prize_3rd + '</span>');
                        extraEl.innerHTML = extras.join(' • ');
                    }

                    // Use server timestamp for accurate countdown sync
                    var serverTime = data.server_time ? data.server_time * 1000 : Date.now();
                    var drawTimestamp = data.draw_timestamp ? data.draw_timestamp * 1000 : 0;
                    var clientOffset = Date.now() - serverTime;
                    var status = data.draw_status;

                    function getRemaining() {
                        return Math.floor((drawTimestamp - (Date.now() - clientOffset)) / 1000);
                    }

                    var remaining = getRemaining();

                    if (remaining <= 0) {
                        renderCountdown(0);
                        if (status === 'locked' || status === 'awaiting_draw') {
                            showPendingMessage();
                        }
                        return;
                    }

                    renderCountdown(remaining);
                    var timer = setInterval(function() {
                        remaining = getRemaining();
                        if (remaining < 0) {
                            clearInterval(timer);
                            showPendingMessage();
                            return;
                        }
                        renderCountdown(remaining);
                    }, 1000);
                });
        })();
        </script>

        <!-- Features Section -->
        <section class="features-section">
            <div class="container">
                <div class="features-grid">
                    <div class="feature-card-modern">
                        <i class="bi bi-shield-check"></i>
                        <h3>Secure</h3>
                        <p>Verified facilities</p>
                    </div>
                    <div class="feature-card-modern">
                        <i class="bi bi-bell"></i>
                        <h3>Alerts</h3>
                        <p>Real-time notifications</p>
                    </div>
                    <div class="feature-card-modern">
                        <i class="bi bi-phone"></i>
                        <h3>Mobile</h3>
                        <p>Bid anywhere</p>
                    </div>
                </div>
            </div>
        </section>


        <!-- Story Section -->
        <section class="story-section">
            <div class="container">
                <div class="story-grid">
                    <div class="story-copy">
                        <span class="eyebrow">Why Storage Punters</span>
                        <h2>Trusted marketplace for storage treasure hunters</h2>
                        <p>We make buying and selling storage units safe and easy. Every facility is checked by our team, and we use a secure system to protect your money from start to finish.</p>
                        <ul>
                            <li><i class="bi bi-check-circle"></i>Use AI tools to list storage auctions simply</li>
                            <li><i class="bi bi-check-circle"></i>100% manually verified storage facilities</li>
                            <li><i class="bi bi-check-circle"></i>Secure escrow payments keep your money safe</li>
                        </ul>
                    </div>
                    <div class="story-highlight">
                        <div class="stats-tile">
                            <span class="stat">100%</span>
                            <small>Secure escrow payments</small>
                        </div>
                        <div class="stats-tile">
                            <span class="stat">100%</span>
                            <small>Australian Owned and Operated</small>
                        </div>
                        <div class="stats-note">
                            <p>“We are building the safest, easiest way to clear out lockers and find hidden treasures across Australia. Get ready to bid.”</p>
                            <span>— The Storage Punters Team</span>
                        </div>
                    </div>
                </div>
            </div>
        </section>


        <!-- Testimonials -->
        <section class="testimonial-section">
            <div class="container">
                <div class="testimonial-header">
                    <span class="eyebrow">Community Voices</span>
                    <h2>Real punters. Real wins.</h2>
                </div>
                <div class="testimonial-grid">
                    <article class="testimonial-card">
                        <div class="quote-icon"><i class="bi bi-quote"></i></div>
                        <p>“Vintage guitars, comics and designer wear from a single haul. Listings are crystal clear and payouts are instant.”</p>
                        <h5>Sarah J. — Melbourne</h5>
                    </article>
                    <article class="testimonial-card">
                        <div class="quote-icon"><i class="bi bi-quote"></i></div>
                        <p>“Storage Punters is the only platform that actually markets our units. Debt recovery has never looked this premium.”</p>
                        <h5>David R. — Facility Partner</h5>
                    </article>
                    <article class="testimonial-card">
                        <div class="quote-icon"><i class="bi bi-quote"></i></div>
                        <p>“Mobile notifications mean I never miss a counter bid. The green & gold theme feels truly Australian.”</p>
                        <h5>Mike T. — Sydney</h5>
                    </article>
                </div>
            </div>
        </section>


        <!-- CTA Section -->
        <?php if (!isset($_SESSION['auction_user_session_data'])) { ?>
        <section class="cta-section">
            <div class="container">
                <div class="cta-card">
                    <h2>Start Bidding Today</h2>
                    <p>Join thousands of treasure hunters</p>
                    <button class="cta-btn" data-bs-toggle="modal" data-bs-target="#unifiedAuthModal" onclick="showBidderRegister()">
                        Get Started Free <i class="bi bi-arrow-right ms-2"></i>
                    </button>
                </div>
            </div>
        </section>
        <?php } ?>

    </main>
</div>
