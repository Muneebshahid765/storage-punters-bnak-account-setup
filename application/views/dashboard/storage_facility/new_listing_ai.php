<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>
<div class="dashboard-container-modern">
    <!-- Sidebar -->
    <?php $this->load->view('dashboard/storage_facility/sidebar'); ?>

    <!-- Main Content -->
    <main class="main-content-modern">
        <header class="dash-header-modern">
            <div class="d-flex align-items-center">
                <button class="btn btn-link text-dark d-lg-none me-3 p-0 hamburger-menu-btn">
                    <i class="bi bi-list fs-1"></i>
                </button>
                <div>
                    <h1 class="dash-title-modern">AI Smart Listing</h1>
                    <p class="text-muted mb-0">Upload photos and let AI generate your auction listing instantly.</p>
                </div>
            </div>
        </header>

        <div class="content-section-modern p-4 p-md-5">
            <form id="ai-listing-form">
                
                <!-- STEP 1: UPLOAD & ANALYZE -->
                <div id="ai-step-upload">
                    <h5 class="fw-bold mb-3">Step 1: Select Storage Location</h5>
                    <select id="storageFacilityDropdown" name="storage_facility_id" class="form-select border rounded-3 p-2 mb-4">
                        <!-- Populated via JS -->
                    </select>
                    
                    

                    <h5 class="fw-bold mb-3">Step 2: Upload Unit Photos</h5>
                    <div class="upload-zone text-center p-5 border-2 border-dashed rounded-3 bg-light" id="drop-zone">
                        <p class="text-muted mb-2">Drag & drop photos here, or <span class="text-primary" style="cursor:pointer" onclick="document.getElementById('file-input').click()">browse files</span>.</p>
                        <p class="text-muted small">Upload at least 5 photos for best AI results.</p>
                        <input type="file" id="file-input" name="images[]" multiple accept="image/*" class="d-none">
                        
                        <div id="preview-container" class="d-flex flex-wrap gap-3 justify-content-center mt-4"></div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="button" id="btn-analyze" class="btn btn-lg px-5 text-white" style="background-color: #2d5f3f;" disabled>
                            Generate Listing Draft
                        </button>
                    </div>
                </div>

                <!-- LOADING STATE -->
                <div id="ai-loading" class="text-center py-5 d-none">
                    <div class="spinner-border text-emerald mb-3" role="status" style="width: 3rem; height: 3rem;"></div>
                    <h4 class="fw-bold animate-pulse">Analyzing Photos...</h4>
                    <p class="text-muted" id="ai-loading-text">Detecting items and estimating value...</p>
                </div>

                <!-- STEP 2: REVIEW & EDIT -->
                <div id="ai-step-review" class="d-none animate-fade-in-up">
                    <h5 class="fw-bold mb-4">Step 3: Review & Edit Auction Details for <span id="facility-name-display">Mammoth Storage Maroochydore</span></h5>

                    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4" style="background-color: #f8f9fa;">
                        <h6 class="fw-bold mb-3">AI-Generated Content:</h6>
                        
                        <label class="form-label fw-600 small">Listing Title</label>
                        <input type="text" id="auction_title" name="auction_title" class="form-control border rounded-3 p-2 mb-3">
                        
                        <label class="form-label fw-600 small">Unit Number (Required)</label>
                        <input type="text" id="unit_number" name="unit_number" class="form-control border rounded-3 p-2 mb-3" placeholder="e.g. A123">

                        <label class="form-label fw-600 small">Unit Size</label>
                        <select id="ai_unit_size" name="unit_size" class="form-select border rounded-3 p-2 mb-3">
                            <option value="">Select Size...</option>
                            <?php for ($i = 1; $i <= 50; $i++): ?>
                                <option value="<?= $i ?>m2"><?= $i ?>m2</option>
                            <?php endfor; ?>
                        </select>
                        
                        <label class="form-label fw-600 small">Item List (Editable)</label>
                        <textarea id="auction_items" name="auction_items" class="form-control border rounded-3 p-2 mb-3" rows="4" placeholder="- Bicycle&#10;- Large Television&#10;- Box of Books"></textarea>

                        <label class="form-label fw-600 small">Item Tags (Comma separated)</label>
                        <input type="text" id="auction_tags" name="auction_tags" class="form-control border rounded-3 p-2 mb-3" placeholder="e.g. tools, electronics, furniture">
                    </div>

                    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                        <h6 class="fw-bold mb-3">Outstanding Debt ($)</h6>
                        <input type="number" id="debt_amount" name="debt_amount" class="form-control border rounded-3 p-3 mb-2" placeholder="500" step="0.01">
                        <p class="text-muted small mb-0">This is used to calculate the starting bid based on your global percentage setting.</p>
                    </div>

                    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                        <h6 class="fw-bold mb-3">Auction Settings (Adjustable for this Auction)</h6>
                        
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-600 small">Duration (Days)</label>
                                <input type="number" id="duration" name="duration" class="form-control border rounded-3 p-2" value="<?= $global_rules['default_auction_duration'] ?? 7 ?>">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600 small">Display Debt Value?</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="display_debt" name="display_debt" <?= (isset($global_rules['display_debt_value']) && $global_rules['display_debt_value'] == 1) ? 'checked' : '' ?>>
                                    <label class="form-check-label" for="display_debt">Yes</label>
                                </div>
                            </div>
                        </div>

                        <div class="row g-3">
                            <div class="col-md-6">
                                <label class="form-label fw-600 small">Cleaning Deposit ($)</label>
                                <input type="number" id="cleaning_deposit" name="cleaning_deposit" class="form-control border rounded-3 p-2" value="<?= $global_rules['cleaning_deposit_amount'] ?? 150 ?>" step="0.01">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-600 small">Starting Bid (% of Debt)</label>
                                <input type="number" id="start_bid_percent" name="start_bid_percent" class="form-control border rounded-3 p-2" value="<?= $global_rules['starting_price_percentage'] ?? 50 ?>" min="0" max="100">
                            </div>
                        </div>

                        <div class="row g-3 mb-2">
                            <div class="col-md-6">
                                <label class="form-label fw-600 small">Payment Method</label>
                                <div class="d-flex align-items-center gap-3 mt-2">
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="ai_payment_method" id="ai_payment_cash" value="Cash">
                                        <label class="form-check-label small" for="ai_payment_cash">Cash</label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="ai_payment_method" id="ai_payment_card" value="Card">
                                        <label class="form-check-label small" for="ai_payment_card">Card</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                        <h6 class="fw-bold mb-3">Auction Terms & Conditions (Editable)</h6>
                        <textarea id="auction_terms" name="auction_terms" class="form-control border rounded-3 p-2" rows="6"><?= $global_rules['default_terms_conditions'] ?? "1. Collection must occur within 48 hours of auction close.\n2. Failure to collect will result in forfeiture of payment and cleaning deposit.\n3. Buyer is responsible for the removal of all contents.\n4. Storage facility reserves the right to bid on any unit.\n5. All sales are final (As Is, Where Is)." ?></textarea>
                    </div>

                    <div class="text-center">
                        <button type="button" id="btn-publish" class="btn btn-lg px-5 text-white" style="background-color: #16a085;">
							Finalise & Post Auction
                        </button>
                    </div>
                </div>

            </form>
        </div>
    </main>
</div>
