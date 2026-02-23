<style>
	.footer-wrapper {
		background-color: #000000;
		color: #ffffff;
		font-family: 'Inter', sans-serif;
		padding-top: 4rem;
		padding-bottom: 2rem;
		border-top: 1px solid rgba(255, 255, 255, 0.1);
	}
	.footer-logo-text {
		font-weight: 800;
		font-size: 1.25rem;
		letter-spacing: -0.5px;
		color: #fff;
	}
	.footer-logo-text span {
		color: #ffc107;
	}
	.footer-logo-icon {
		background: #ffc107; /* Gold background */
		color: #000; /* Black text */
		font-weight: 800;
		padding: 4px 8px;
		border-radius: 4px;
		margin-right: 8px;
		font-size: 0.9rem;
	}
	.footer-heading {
		color: #ffffff;
		font-weight: 700;
		text-transform: uppercase;
		font-size: 0.9rem;
		margin-bottom: 1.25rem;
		letter-spacing: 0.5px;
	}
	.footer-links {
		list-style: none;
		padding: 0;
		margin: 0;
	}
	.footer-links li {
		margin-bottom: 0.75rem;
	}
	.footer-links a {
		color: #9ca3af; /* Gray-400 */
		text-decoration: none;
		font-size: 0.9rem;
		transition: color 0.2s;
	}
	.footer-links a:hover {
		color: #ffc107;
	}
	.footer-desc {
		color: #6b7280; /* Gray-500 */
		font-size: 0.85rem;
		line-height: 1.6;
		margin-top: 1rem;
		max-width: 300px;
	}
	.subscribe-input {
		background: #131720;
		border: 1px solid #374151;
		color: #fff;
		padding: 10px 15px;
		border-radius: 6px 0 0 6px;
		font-size: 0.9rem;
	}
	.subscribe-input:focus {
		background: #131720;
		border-color: #ffc107;
		box-shadow: none;
		color: #fff;
	}
	.subscribe-btn {
		background: #ffc107;
		color: #000;
		font-weight: 800;
		border: none;
		border-radius: 0 6px 6px 0;
		padding: 10px 20px;
		text-transform: uppercase;
		font-size: 0.9rem;
	}
	.subscribe-btn:hover {
		background: #e0a800;
		color: #000;
	}
	.footer-bottom {
		border-top: 1px solid rgba(255, 255, 255, 0.05);
		margin-top: 3rem;
		padding-top: 1.5rem;
		text-align: center;
		font-size: 0.75rem;
		color: #4b5563; /* Gray-600 */
	}
</style>

<footer class="footer-wrapper">
	<div class="container">
		<div class="row gy-5">
			<!-- Brand Column -->
			<div class="col-lg-4 col-md-12">
				<div class="d-flex align-items-center mb-3">
					<span class="footer-logo-icon">SP</span>
					<span class="footer-logo-text">STORAGE<span>PUNTERS</span></span>
				</div>
				<p class="footer-desc">
					The premier destination for storage auction enthusiasts. Licensed giveaways and real auctions.
				</p>
			</div>

			<!-- Platform Links -->
			<div class="col-lg-2 col-6">
				<h5 class="footer-heading">Platform</h5>
				<ul class="footer-links">
					<li><a href="<?= site_url('auctions') ?>">Auctions</a></li>
					<li><a href="<?= site_url('vault') ?>">Live Draws</a></li>
					<li><a href="<?= site_url('vault') ?>">Winners Wall</a></li>
					<li><a href="#">Merch Store</a></li>
				</ul>
			</div>

			<!-- Support Links -->
			<div class="col-lg-2 col-6">
				<h5 class="footer-heading">Support</h5>
				<ul class="footer-links">
					<li><a href="<?= site_url('faqs') ?>">FAQ</a></li>
					<li><a href="<?= site_url('contact-us') ?>">Contact Us</a></li>
					<li><a href="<?= site_url('terms-and-conditions') ?>">Terms & Conditions</a></li>
					<li><a href="<?= site_url('privacy-policy') ?>">Privacy Policy</a></li>
				</ul>
			</div>

			<!-- Subscribe -->
			<div class="col-lg-4 col-md-12">
				<h5 class="footer-heading">Subscribe</h5>
				<p class="text-secondary small mb-3">Get the latest drop alerts.</p>
				<form class="d-flex">
					<input type="email" class="form-control subscribe-input" placeholder="Email address">
					<button class="btn subscribe-btn" type="button">GO</button>
				</form>
			</div>
		</div>

		<!-- Copyright -->
		<div class="footer-bottom">
			<div class="d-flex flex-wrap justify-content-between align-items-center gap-2">
				<span>&copy; 2025 Storage Punters. All rights reserved.</span>
				<span class="small text-secondary">
					<?php
					$CI =& get_instance();
					if ($CI->db->table_exists('tbl_vault_settings')) {
						$CI->db->where_in('setting_key', ['nsw_permit_number','sa_permit_number','act_permit_number']);
						$permits = $CI->db->get('tbl_vault_settings')->result();
						$parts = [];
						foreach($permits as $p) {
							if(!empty($p->setting_value)) {
								$label = strtoupper(str_replace('_permit_number', '', $p->setting_key));
								$parts[] = $label . ' Permit #' . htmlspecialchars($p->setting_value);
							}
						}
						echo implode(' | ', $parts);
					}
					?>
				</span>
			</div>
		</div>
	</div>
</footer>


<!-- Modal -->
<!-- Unified Auth Modal -->
<?php $this->load->view('auth/unified_auth_modal'); ?>


<div class="modal fade" id="auctionAgreementModal" tabindex="-1" aria-labelledby="auctionAgreementModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-lg">
		<div class="modal-content">
			<div class="modal-header">
				<h5 class="modal-title">Auction Agreement</h5>
				<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
			</div>
			<div class="modal-body">
				<p>
					By confirming, both buyer and seller agree to the terms of the auction and commit to fulfill their obligations.
				</p>
				<!-- Add more legal content if needed -->
			</div>
			<div class="modal-footer">
				<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
				<button type="button" class="btn btn-success" id="confirmAuctionAgreementBtn">I Agree</button>
			</div>
		</div>
	</div>
</div>

<?php $this->load->view('layout/vault_modal'); ?>

<!-- Spinner overlay -->
<div id="spinner-overlay" style="display:none; position:fixed; top:0; left:0; right:0; bottom:0; background:rgba(0,0,0,0.5); z-index:9999;">
	<div style="position:absolute; top:50%; left:50%; transform:translate(-50%,-50%);">
		<div class="spinner-border text-light" role="status"></div>
	</div>
</div>
