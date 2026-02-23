<!-- The Vault Modal -->
<div class="modal fade" id="vaultModal" tabindex="-1" aria-hidden="true" style="z-index: 10000;">
	<div class="modal-dialog modal-dialog-centered modal-xl" style="max-width: 900px;">
		<div class="modal-content border-0 bg-transparent shadow-lg">

			<!-- Custom CSS Injection for this Modal -->
			<style>

				.vault-prev-winners-container {
					background-color: #F9FAFB;
					width: 100%;
					margin: auto;
					overflow: hidden;
					box-shadow: 0 15px 35px rgba(0,0,0,0.2);
					padding: 20px 30px;
				}


				.vault-header-banner {
					background: linear-gradient(180deg, #0d2b11 0%, #051a08 100%);
					padding: 16px 00px;
					text-align: center;
					position: relative;
					border-radius: 12px 12px 0 0;
					border-bottom: 4px solid #ffcc00;
				}

				.vault-logo-lock {
					color: #EAB308;
					font-weight: 900;
					font-size: 2.2rem;
					letter-spacing: 2px;
					font-style: italic;
					margin-top: 10px;
					text-transform: uppercase;
				}

				.vault-logo-text {
					color: #fff;
					font-weight: 900;
					font-size: 2.2rem;
					letter-spacing: 2px;
					font-style: italic;
					text-transform: uppercase;
				}

				.vault-subtitle {
					color: #ffcc00;
					font-weight: 700;
					letter-spacing: 1.5px;
					font-size: 0.75rem;
					text-transform: uppercase;
				}

				.close-modal-btn {
					position: absolute;
					top: 15px;
					right: 15px;
					background: rgba(255, 255, 255, 0.1);
					border: 1px solid rgba(255, 255, 255, 0.2);
					border-radius: 50%;
					width: 32px;
					height: 32px;
					color: #fff;
					display: flex;
					align-items: center;
					justify-content: center;
					transition: 0.3s;
					cursor: pointer;
				}

				.close-modal-btn:hover {
					background: rgba(255, 255, 255, 0.3);
					text-decoration: none;
					color: white;
				}

				.vault-body {
					background: #fff;
					display: flex;
					flex-wrap: wrap;
					overflow: hidden;
				}

				.vault-left-col {
					background: #000;
					color: #39FF14;
					padding: 0;
					display: flex;
					flex-direction: column;
					justify-content: center;
					align-items: center;
					min-height: 400px;
					position: relative;
				}

				.draw-result-id {
					font-family: 'Courier New', monospace;
					font-size: 2rem;
					word-break: break-all;
					line-height: 1.2;
					text-align: center;
					padding: 20px;
					text-shadow: 0 0 10px rgba(57, 255, 20, 0.5);
				}

				.vault-right-col {
					padding: 30px;
					display: flex;
					flex-direction: column;
				}

				.vault-title-text {
					font-size: 1.8rem;
					font-weight: 900;
					font-style: italic;
					color: #0d2b11;
					margin-bottom: 5px;
					line-height: 1.1;
				}

				.vault-value-amount {
					font-size: 1.6rem;
					font-weight: 800;
					color: #1a4721;
				}

				.countdown-bar {
					background: #010101;
					border: 2px solid #ffcc00;
					border-radius: 8px;
					padding: 15px;
					text-align: center;
					margin: 20px 0;
					box-shadow: 0 4px 10px rgba(0, 0, 0, 0.1);
				}

				.countdown-timer {
					font-size: 2.2rem;
					font-weight: 800;
					color: #ffffff;
					line-height: 1;
				}

				.btn-get-entries {
					background-color: #1a4721;
					color: #ffffff;
					font-weight: 800;
					text-transform: uppercase;
					padding: 15px;
					border-radius: 6px;
					width: 100%;
					border: none;
					margin-top: auto;
					transition: background 0.2s;
					text-decoration: none;
					display: block;
					text-align: center;
				}

				.btn-get-entries:hover {
					background-color: #0d2b11;
					color: #fff;
					text-decoration: none;
				}

				.user-stats-row {
					display: flex;
					gap: 10px;
					margin-bottom: 20px;
				}

				.stat-box {
					flex: 1;
					border-radius: 8px;
					padding: 10px;
					text-align: center;
				}

				.bg-success-light {
					background-color: rgba(212, 237, 218, 0.5);
					border: 1px solid #c3e6cb;
				}

				.bg-warning-light {
					background-color: rgba(255, 243, 205, 0.5);
					border: 1px solid #ffeeba;
				}

				.stat-value {
					font-weight: 800;
					font-size: 1.2rem;
					display: block;
				}

				.stat-label {
					font-size: 0.75rem;
					text-transform: uppercase;
					color: #666;
					font-weight: 700;
				}

				/* Previous Winners Cards */
				.winner-card {
					border: 1px solid #eee;
					border-radius: 10px;
					padding: 20px;
					background: #fff;
					height: 100%;
				}

				.avatar-circle {
					width: 50px;
					height: 50px;
					border-radius: 50%;
					display: flex;
					align-items: center;
					justify-content: center;
					font-weight: bold;
					font-size: 1.1rem;
				}

				.winner-tag {
					background: #d4edda;
					color: #155724;
					font-size: 0.65rem;
					font-weight: 800;
					padding: 2px 8px;
					border-radius: 4px;
				}

				.prize-item-tag {
					background: #f1f3f5;
					border: 1px solid #dee2e6;
					border-radius: 4px;
					padding: 4px 10px;
					font-size: 0.75rem;
					color: #495057;
					margin-right: 5px;
					margin-bottom: 5px;
					display: inline-block;
				}

                /* Animation Box */
                .vault-reveal-box {
                    border: 2px solid #ffcc00;
                    box-shadow: 0 0 20px #ffcc00, inset 0 0 20px #ffcc00;
                    padding: 30px 50px;
                    border-radius: 8px;
                    background: #111;
                    min-width: 80%;
                    text-align: center;
                }
                .reveal-text {
                    font-family: 'Courier New', monospace;
                    font-size: 2rem;
                    font-weight: bold;
                    color: #4ADE80;
                    letter-spacing: 3px;
                    text-shadow: 0 0 10px rgba(255, 255, 255, 0.5);
                }
                .reveal-text.winner {
                    color: #ffcc00;
                    text-shadow: 0 0 20px #ffcc00;
                    font-size: 2.2rem;
                }

                /* Confetti Animation */
                .confetti {
                    position: absolute;
                    width: 10px;
                    height: 10px;
                    background-color: #ffd700;
                    top: -10px;
                    z-index: 1000; /* High Z-Index */
                    animation: fall linear forwards;
                }
                @keyframes fall {
                    to {
                        transform: translateY(60vh) rotate(720deg);
                        opacity: 0;
                    }
                }

			</style>

			<div class="vault-header-banner">
				<a href="#" class="close-modal-btn" data-bs-dismiss="modal" data-dismiss="modal">
					<i class="bi bi-x-lg"></i>
				</a>
				<div class="vault-logo-lock"><i class="bi bi-lock me-2"></i></div>
				<div class="vault-logo-text">THE VAULT</div>
				<p class="vault-subtitle text-uppercase">Exclusive Rewards Club</p>


				<div class="vault-body row m-0">
					<!-- Left: Static Cover Image -->
					<div class="col-lg-5 vault-left-col p-0">
                        <img id="vault-cover-image" src="" class="img-fluid w-100 h-100" style="object-fit: cover;" alt="Prize">
					</div>

					<!-- Right: Details & Stats & Animation Overlay -->
					<div class="col-lg-7 vault-right-col position-relative">

                        <!-- ANIMATION OVERLAY -->
                        <div id="vault-right-overlay" class="d-none w-100 h-100 position-absolute top-0 start-0 d-flex flex-column justify-content-center align-items-center px-5" style="background: #000; z-index: 50; overflow: hidden;">
                            <div id="vault-winner-label" class="text-warning text-uppercase fw-bold mb-4 d-none" style="letter-spacing: 2px; font-size: 1.2rem;">Winner Confirmed</div>

                            <div class="vault-reveal-box w-100" style="z-index: 51;">
                                <span id="vault-reveal-text" class="reveal-text">INITIALIZING</span>
                            </div>
                        </div>

						<div class="vault-details-box">


							<div class="d-flex justify-content-between align-items-start mb-2">
								<div class="text-start">
									<h2 class="vault-title-text" id="vault-draw-code">Loading...</h2>
									<span class="badge badge-warning text-dark font-weight-bold"
										  id="vault-draw-date">--</span>
								</div>
								<div class="text-right">
									<span class="d-block small text-muted font-weight-bold">PRIZE VALUE</span>
									<div class="vault-value-amount" id="vault-prize-value">$0</div>
								</div>
							</div>

                            <p class="text-muted small mt-2 d-none"  id="vault-description"></p>

							<div class="countdown-bar">
								<div class="small text-warning font-weight-bold mb-1" style="letter-spacing: 1px;">Live
									Draw Initiated
								</div>
								<div class="countdown-timer" id="vault-countdown-j">--:--:--:--</div>
							</div>

							<div class="locked-inside-title text-start font-medium mb-1 text-uppercase">Locked Inside:</div>
							<div class="prize-list-container mb-4" id="vault-items-list">
								<!-- Items injected via JS -->
							</div>

							<a href="/show-dashboard/coin-shop" class="btn-get-entries">
								<i class="bi bi-plus-circle-fill mr-2"></i> Get More Entries
							</a>
						</div>


						<!-- User Stats -->
						<div class="user-stats-row mt-3">
							<div class="stat-box bg-success-light">
								<span class="stat-value text-success" id="vault-user-entries">0</span>
								<span class="stat-label">Your Entries</span>
							</div>
							<div class="stat-box bg-warning-light">
								<span class="stat-value text-dark" id="vault-user-coins">0</span>
								<span class="stat-label">Coin Balance</span>
							</div>
						</div>

					</div>
				</div>


				<div class="vault-prev-winners-container">
				<div class="d-flex align-items-center mb-3">
					<div class="bg-light rounded-circle d-flex align-items-center justify-content-center me-2" style="width: 28px; height: 28px; border: 1px solid #ddd;">
						<i class="bi bi-clock-history text-secondary small"></i>
					</div>
					<h5 class="fw-bold mb-0 text-dark" style="letter-spacing: -0.5px;">PREVIOUS VAULT WINNERS</h5>
				</div>
				<!-- PREVIOUS VAULT WINNERS LIMIT 2-->
				<div class="row g-3" id="hall-of-fame-container">
                    <!-- Winners will be injected here via JS -->
                    <div class="col-12 text-center py-5 text-muted small">Loading winners...</div>
				</div>
				</div>

			</div>
		</div>
	</div>

	<script>
		document.addEventListener('DOMContentLoaded', function () {
			let vaultTimer = null;

			function renderCountdown(seconds) {
				const el = document.getElementById('vault-countdown-j');
				if (!el) return;
				if (seconds <= 0) {
					el.innerText = 'DRAW PENDING';
					return;
				}
				const days = Math.floor(seconds / 86400);
				const hours = Math.floor((seconds % 86400) / 3600);
				const minutes = Math.floor((seconds % 3600) / 60);
				const secs = seconds % 60;
				el.innerText = `${days.toString().padStart(2,'0')}:${hours.toString().padStart(2,'0')}:${minutes.toString().padStart(2,'0')}:${secs.toString().padStart(2,'0')}`;
			}

			function startTimer(seconds) {
				if (vaultTimer) clearInterval(vaultTimer);
				let remaining = seconds;
				renderCountdown(remaining);
				vaultTimer = setInterval(function () {
					remaining--;
					if (remaining <= 0) {
						clearInterval(vaultTimer);
						fetchVaultData();
					} else {
						renderCountdown(remaining);
					}
				}, 1000);
			}

			// Load data when modal opens
			$('#vaultModal').on('show.bs.modal', function (e) {
				fetchVaultData();
			});

			// Close/Stop timer when closed
			$('#vaultModal').on('hidden.bs.modal', function (e) {
				if (vaultTimer) clearInterval(vaultTimer);
			});

			function fetchVaultData() {
				fetch('<?php echo base_url("vault/status"); ?>')
					.then(res => res.json())
					.then(data => {
						updateVaultUI(data);
					})
					.catch(err => console.error("Vault Error:", err));
			}

			function updateVaultUI(data) {
				// User Stats
				document.getElementById('vault-user-entries').innerText = new Intl.NumberFormat().format(data.entries);
				document.getElementById('vault-user-coins').innerText = new Intl.NumberFormat().format(data.coins);

				// Populate draw details if available
				if (data.draw_details) {
					document.getElementById('vault-draw-code').innerText = data.draw_details.draw_code || data.draw_details.title || 'THE VAULT';
					document.getElementById('vault-draw-date').innerText = "Draws: " + data.draw_details.draw_date_formatted;
					document.getElementById('vault-prize-value').innerText = data.draw_details.prize_formatted;
					document.getElementById('vault-description').innerText = data.draw_details.description || '';

					// Cover Image Logic
					const coverImg = document.getElementById('vault-cover-image');
					if(data.draw_details.cover_image_url) {
						coverImg.src = data.draw_details.cover_image_url;
						coverImg.classList.remove('d-none');
					} else {
						coverImg.classList.add('d-none');
					}

					// Items List
					const itemsList = document.getElementById('vault-items-list');
					itemsList.innerHTML = '';
					if(data.draw_details.items && data.draw_details.items.length > 0) {
						data.draw_details.items.forEach(item => {
							const itemHtml = `
							<div class="prize-list-item text-start d-flex gap-2">
								<div class="check-icon"><i class="bi bi-check-circle" style="color: #EAB308"></i></div>
								<span>${item.item_name}</span>
							</div>`;
							itemsList.insertAdjacentHTML('beforeend', itemHtml);
						});
					}

					// Show 2nd/3rd prize if available
					if (data.draw_details.prize_2nd || data.draw_details.prize_3rd) {
						let prizeHtml = '<div class="text-center mt-2 small">';
						if (data.draw_details.prize_2nd) prizeHtml += '<span class="badge bg-secondary me-1">2nd: ' + data.draw_details.prize_2nd + '</span>';
						if (data.draw_details.prize_3rd) prizeHtml += '<span class="badge bg-secondary">3rd: ' + data.draw_details.prize_3rd + '</span>';
						prizeHtml += '</div>';
						itemsList.insertAdjacentHTML('beforeend', prizeHtml);
					}
				}

				// --- Draw Status Handling ---
				if ((data.draw_status === 'active' || data.draw_status === 'scheduled') && data.seconds_remaining > 0) {
					startTimer(data.seconds_remaining);

				} else if (data.draw_status === 'awaiting_draw' || data.draw_status === 'locked') {
					// Draw time passed but winner not yet selected - show waiting state
					document.getElementById('vault-countdown-j').innerText = "DRAW PENDING";
					// Auto-refresh every 15 seconds to check for winner
					if (vaultTimer) clearInterval(vaultTimer);
					vaultTimer = setInterval(fetchVaultData, 15000);

				} else if (data.draw_status === 'completed') {
					document.getElementById('vault-draw-code').innerText = "Draw Completed";
					document.getElementById('vault-countdown-j').innerText = "CLOSED";

					// Show Animation Overlay on Right
					document.getElementById('vault-right-overlay').classList.remove('d-none');

					if (data.last_winner_name) {
						document.getElementById('vault-reveal-text').innerText = data.last_winner_name;
						document.getElementById('vault-reveal-text').classList.add('winner');
						document.getElementById('vault-winner-label').classList.remove('d-none');
					}
				} else {
					document.getElementById('vault-draw-code').innerText = "No Active Draw";
					document.getElementById('vault-countdown-j').innerText = "--";
				}

				// --- Hall of Fame Rendering ---
				const hofContainer = document.getElementById('hall-of-fame-container');
				hofContainer.innerHTML = ''; // Clear loading/old data

                if (data.hall_of_fame && data.hall_of_fame.length > 0) {
                    data.hall_of_fame.forEach((winner, index) => {
                        const date = new Date(winner.won_at).toLocaleDateString('en-AU', { month: 'short', day: 'numeric', year: 'numeric' });
                        const initials = winner.winner_display.charAt(0) + (winner.winner_display.split(' ')[1] ? winner.winner_display.split(' ')[1].charAt(0) : '');
                        const colors = ['#fff9db','#e9ecef','#d0ebff','#f8f0fc'];
                        const bg = colors[index % 4];

                        // Items Tags
                        let itemsHtml = '<span class="prize-item-tag">Mystery Bundle</span>';
                        if (winner.items && winner.items.length > 0) {
                            itemsHtml = winner.items.map(item => `<span class="prize-item-tag">${item.item_name}</span>`).join('');
                        }

                        // Prize Value Title
                        const prizeVal = (winner.prize_value_cents/100).toLocaleString();

                        const html = `
						<div class="col-md-6">
							<div class="winner-card">
								<div class="d-flex justify-content-between align-items-start mb-3">
									<div class="d-flex align-items-center">
										<div class="avatar-circle me-3" style="background: ${bg}; color: #495057;">${initials}</div>
										<div>
											<h6 class="fw-bold mb-0">${winner.winner_display}</h6>
											<small class="text-muted">ID: ${winner.punter_id}</small>
										</div>
									</div>
									<div class="text-end">
										<span class="winner-tag">WINNER</span>
										<div class="text-muted small mt-1">${date}</div>
									</div>
								</div>
								<div class="mb-3 text-start">
									<span class="h4 fw-bold text-dark">$${prizeVal} <small class="text-muted" style="font-size: 0.4em; font-weight: lighter text-transform: uppercase;">Value</small></span>
								</div>
								<div class="bg-light p-3 rounded text-start">
									<p class="text-muted small fw-bold text-uppercase mb-2">Items Won:</p>
									${itemsHtml}
								</div>
							</div>
						</div>`;
                        hofContainer.insertAdjacentHTML('beforeend', html);
                    });
                } else {
                    hofContainer.innerHTML = '<div class="col-12 text-center py-5"><p class="text-muted">The Hall of Fame is currently empty. Be the first to win!</p></div>';
                }
			}

			function startTimer(seconds) {
				if (vaultTimer) clearInterval(vaultTimer);

				let remaining = parseInt(seconds);

                function updateDisplay() {
                    if (remaining <= 0) {
						clearInterval(vaultTimer);
						document.getElementById("vault-countdown-j").innerText = "DRAW PENDING";
						// Auto-refresh to get updated status from server
						setTimeout(fetchVaultData, 3000);
						return;
					}

					const days = Math.floor(remaining / (3600 * 24));
					const hours = Math.floor((remaining % (3600 * 24)) / 3600);
					const minutes = Math.floor((remaining % 3600) / 60);
					const secs = Math.floor(remaining % 60);

					document.getElementById("vault-countdown-j").innerText =
						days + "d " + hours + "h " + minutes + "m " + secs + "s ";

					remaining--;
                }

                updateDisplay(); // Run immediately
				vaultTimer = setInterval(updateDisplay, 1000);
			}

            // --- ANIMATION ENGINE ---

            // Expose for testing via Console
            window.testVaultReveal = function(winnerName = "TEST USER SP-0000") {
                const overlay = document.getElementById('vault-right-overlay');
                overlay.classList.remove('d-none');

                // Reset State
                document.getElementById('vault-winner-label').classList.add('d-none');
                document.getElementById('vault-reveal-text').classList.remove('winner');

                startRevealSequence(winnerName);
            }

            function startRevealSequence(winnerName) {
                const textField = document.getElementById('vault-reveal-text');

                // Matrix Shuffle
                const chars = "ABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789!@#$%^&*";
                let iterations = 0;
                const maxIterations = 50; // Approx 5 seconds at 100ms

                const interval = setInterval(() => {
                    // Generate 12 char random string
                    let tempText = "";
                    for(let i=0; i<12; i++) {
                        tempText += chars[Math.floor(Math.random() * chars.length)];
                    }

                    textField.innerText = tempText;
                    iterations++;

                    if (iterations > maxIterations) {
                        clearInterval(interval);

                        // Lock Winner
                        textField.innerText = winnerName;
                        textField.classList.add('winner');

                        // Show Title
                        document.getElementById('vault-winner-label').classList.remove('d-none');

                        // Fire Confetti
                        fireConfetti();
                    }
                }, 100);
            }

            function fireConfetti() {
                const colors = ['#ffd700', '#ffec8b', '#ffc107', '#ffffff'];
                const container = document.getElementById('vault-right-overlay');

                // Create particles
                for (let i = 0; i < 150; i++) {
                    const confetti = document.createElement('div');
                    confetti.classList.add('confetti');
                    confetti.style.left = Math.random() * 100 + '%';
                    confetti.style.backgroundColor = colors[Math.floor(Math.random() * colors.length)];
                    confetti.style.animationDuration = (Math.random() * 2 + 2) + 's';
                    confetti.style.width = (Math.random() * 8 + 5) + 'px';
                    confetti.style.height = confetti.style.width;

                    container.appendChild(confetti);

                    // Cleanup
                    setTimeout(() => {
                        confetti.remove();
                    }, 4000);
                }
            }
		});
	</script>
</div>