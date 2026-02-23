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
                    <h1 class="dash-title-modern">List New Auction</h1>
                    <p class="text-muted mb-0">Complete the steps below to list your storage unit for auction.</p>
                </div>
            </div>
        </header>

        <!-- Wizard Steps Indicator -->
        <div class="d-flex gap-4 mb-5">
            <div class="d-flex align-items-center gap-2" id="step-indicator-1">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold" style="width: 32px; height: 32px; background-color: var(--dash-emerald); color: white;">1</div>
                <div class="fw-bold" style="color: var(--dash-emerald);">Location</div>
            </div>
            <div class="d-flex align-items-center gap-2 opacity-50" id="step-indicator-2">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold border" style="width: 32px; height: 32px;">2</div>
                <div class="fw-bold">Media</div>
            </div>
            <div class="d-flex align-items-center gap-2 opacity-50" id="step-indicator-3">
                <div class="rounded-circle d-flex align-items-center justify-content-center fw-bold border" style="width: 32px; height: 32px;">3</div>
                <div class="fw-bold">Details</div>
            </div>
        </div>

        <div class="content-section-modern p-4 p-md-5">
            <form id="auction-add-form">
                <!-- STEP 1: LOCATION -->
                <div class="wizard-step-content" id="step-content-1">
                    <div class="row g-4">
                        <div class="col-md-6">
                            <label class="form-label fw-600">Storage Facility Listing(s) *</label>
                            <select id="storageFacilityDropdown" name="storage_facility[]" class="form-select border rounded-3 p-2" multiple style="height: 120px;">
                                <!-- Options will be loaded via JS -->
                            </select>
                            <small class="text-muted">Hold Ctrl/Cmd to select multiple for bulk listing.</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Master Data Profile (Tab 1)</label>
                            <select id="masterProfileDropdown" class="form-select border rounded-3 p-2">
                                <option value="">Draft from scratch...</option>
                            </select>
                            <small class="text-muted">Apply pre-filled details & terms</small>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Auction Type *</label>
                            <select id="auctionTypeDropdown" name="auction_type" class="form-select border rounded-3 p-2">
                                <option value="">Select Type...</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Unit Category *</label>
                            <select id="auctionCategoryDropdown" name="auction_category" class="form-select border rounded-3 p-2">
                                <option value="">Select Category...</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Unit Number *</label>
                            <input id="auction_unit_number" type="text" class="form-control border rounded-3 p-2" placeholder="e.g. A-104">
                        </div>
                        <div class="col-12 mt-5 text-end border-top pt-4">
                            <button type="button" class="btn btn-emerald px-5" onclick="goToStep(2)">Next Step <i class="bi bi-arrow-right ms-2"></i></button>
                        </div>
                    </div>
                </div>

                <!-- STEP 2: PHOTOS -->
                <div class="wizard-step-content d-none" id="step-content-2">
                    <div class="mb-4">
                        <h4 class="fw-bold">Upload Unit Photos</h4>
                        <p class="text-muted">High-quality photos increase engagement and final sale price.</p>
                    </div>
                    
                    <div id="auction-upload-section">
                        <!-- JS injects its own upload area here -->
                    </div>

                    <div class="text-center mt-3">
                        <button type="button" id="ai-analyze-btn" class="btn btn-outline-primary btn-sm rounded-pill px-4">
                            <i class="bi bi-cpu me-2"></i> AI Analyze Contents
                        </button>
                        <p class="small text-muted mt-2">Our AI will detect items and suggest tags/description.</p>
                    </div>

                    <div class="row mt-4">
                         <div class="col-md-12">
                            <label class="form-label fw-600">YouTube Video URL (Optional)</label>
                            <input id="auction_youtube_video_url" type="url" class="form-control border rounded-3 p-2" placeholder="https://youtube.com/watch?v=...">
                        </div>
                    </div>

                    <div class="d-flex justify-content-between mt-5 border-top pt-4">
                        <button type="button" class="btn btn-outline-secondary px-4" onclick="goToStep(1)"><i class="bi bi-arrow-left me-2"></i> Back</button>
                        <button type="button" class="btn btn-emerald px-5" onclick="goToStep(3)">Next Step <i class="bi bi-arrow-right ms-2"></i></button>
                    </div>
                </div>

                <!-- STEP 3: DETAILS -->
                <div class="wizard-step-content d-none" id="step-content-3">
                    <div class="row g-4">
                        <div class="col-12">
                            <label class="form-label fw-600">Description *</label>
                            <textarea id="auction_description" name="auction_description" class="form-control border rounded-3 p-2" rows="5" placeholder="Describe the contents of the unit..."></textarea>
                        </div>
                        
                        <div class="col-md-4">
                            <label class="form-label fw-600">Start Bid ($)</label>
                            <input id="auction_start_bid_amount" type="text" class="form-control border rounded-3 p-2" placeholder="0.00">
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Unit Size</label>
                            <select id="auction_unit_size" class="form-select border rounded-3 p-2">
                                <option value="">Select Size...</option>
                                <option value="1m x 1m (Locker)">1m x 1m (Locker)</option>
                                <option value="1.5m x 1.5m">1.5m x 1.5m</option>
                                <option value="3m x 1.5m">3m x 1.5m</option>
                                <option value="3m x 3m">3m x 3m</option>
                                <option value="3m x 4m">3m x 4m</option>
                                <option value="3m x 5m">3m x 5m</option>
                                <option value="3m x 6m">3m x 6m</option>
                                <option value="3m x 9m">3m x 9m</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label fw-600">Auction Duration</label>
                            <select id="auction_set_days" class="form-select border rounded-3 p-2">
                                <option value="3" data-type="day" <?= (isset($global_rules['default_auction_duration']) && $global_rules['default_auction_duration'] == 3) ? 'selected' : '' ?>>3 Days</option>
                                <option value="5" data-type="day" <?= (isset($global_rules['default_auction_duration']) && $global_rules['default_auction_duration'] == 5) ? 'selected' : '' ?>>5 Days</option>
                                <option value="7" data-type="day" <?= (isset($global_rules['default_auction_duration']) && $global_rules['default_auction_duration'] == 7) ? 'selected' : '' ?>>7 Days</option>
                                <?php if (isset($global_rules['default_auction_duration']) && !in_array($global_rules['default_auction_duration'], [3, 5, 7])): ?>
                                    <option value="<?= $global_rules['default_auction_duration'] ?>" data-type="day" selected><?= $global_rules['default_auction_duration'] ?> Days</option>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label fw-600">Cleaning Deposit ($)</label>
                            <select id="auction_cleaning_deposit" class="form-select border rounded-3 p-2">
                                <option value="50.00" <?= (isset($global_rules['cleaning_deposit_amount']) && $global_rules['cleaning_deposit_amount'] == 50) ? 'selected' : '' ?>>$50.00 AUD</option>
                                <option value="100.00" <?= (isset($global_rules['cleaning_deposit_amount']) && $global_rules['cleaning_deposit_amount'] == 100) ? 'selected' : '' ?>>$100.00 AUD</option>
                                <option value="200.00" <?= (isset($global_rules['cleaning_deposit_amount']) && $global_rules['cleaning_deposit_amount'] == 200) ? 'selected' : '' ?>>$200.00 AUD</option>
                                <?php if (isset($global_rules['cleaning_deposit_amount']) && !in_array($global_rules['cleaning_deposit_amount'], [50, 100, 200])): ?>
                                    <option value="<?= number_format($global_rules['cleaning_deposit_amount'], 2) ?>" selected>$<?= number_format($global_rules['cleaning_deposit_amount'], 2) ?> AUD</option>
                                <?php endif; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label fw-600">Payment Method</label>
                            <div class="d-flex align-items-center gap-3 mt-2">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="auction_payment_method" id="auction_payment_cash" value="Cash">
                                    <label class="form-check-label" for="auction_payment_cash">
                                        Cash
                                    </label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="auction_payment_method" id="auction_payment_card" value="Card">
                                    <label class="form-check-label" for="auction_payment_card">
                                        Card
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Hidden but required fields for JS -->
                        <div class="d-none">
                            <input id="auction_start_date" type="date">
                            <select id="auction_start_time"></select> 
                            <input id="auction_end_date" type="date">
                            <select id="auction_end_time"></select>
                            <select id="auction_disclose_unit_number_on_website"><option value="1" selected>Yes</option></select>
                            <input id="auction_outstanding_dept" value="0">
                            <input id="auction_tags" value="storage, auction">
                            <input id="auction_additional_features" value="Secure Unit">
                        </div>

                        <div class="d-flex justify-content-between mt-5 border-top pt-4">
                            <button type="button" class="btn btn-outline-secondary px-4" onclick="goToStep(2)"><i class="bi bi-arrow-left me-2"></i> Back</button>
                            <button type="submit" id="save-auction-add-btn" class="btn btn-gold shadow-sm px-5">
                                <i class="bi bi-check2-circle me-2"></i> COMPLETE & LIST AUCTION
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </main>
</div>

<script>
    function goToStep(stepNumber) {
        // Basic validation for Step 1
        if (stepNumber === 2) {
            if (!$('#storageFacilityDropdown').val() || !$('#auction_unit_number').val()) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Incomplete Details',
                    text: 'Please select a facility and enter a unit number.'
                });
                return;
            }
        }

        // Hide all steps
        document.querySelectorAll('.wizard-step-content').forEach(step => step.classList.add('d-none'));
        // Show target step
        document.getElementById('step-content-' + stepNumber).classList.remove('d-none');
        
        // Update indicators
        document.querySelectorAll('[id^="step-indicator-"]').forEach((item, index) => {
            const currentStep = index + 1;
            if (currentStep === stepNumber) {
                item.classList.remove('opacity-50');
                item.querySelector('.rounded-circle').style.backgroundColor = 'var(--dash-emerald)';
                item.querySelector('.rounded-circle').style.color = 'white';
                item.querySelector('.rounded-circle').style.borderColor = 'var(--dash-emerald)';
                item.querySelector('.fw-bold:last-child').style.color = 'var(--dash-emerald)';
            } else if (currentStep < stepNumber) {
                item.classList.remove('opacity-50');
                item.querySelector('.rounded-circle').style.backgroundColor = 'var(--dash-emerald)';
                item.querySelector('.rounded-circle').style.color = 'white';
                item.querySelector('.rounded-circle').style.borderColor = 'var(--dash-emerald)';
                item.querySelector('.fw-bold:last-child').style.color = 'var(--dash-text-dark)';
            } else {
                item.classList.add('opacity-50');
                item.querySelector('.rounded-circle').style.backgroundColor = 'transparent';
                item.querySelector('.rounded-circle').style.color = 'inherit';
                item.querySelector('.rounded-circle').style.borderColor = 'var(--dash-border)';
                item.querySelector('.fw-bold:last-child').style.color = 'inherit';
            }
        });

        // Initialize Start Date/Time to "Now" if we are on Step 3
        if (stepNumber === 3) {
            const startDateInput = document.getElementById('auction_start_date');
            if (!startDateInput.value) {
                const now = new Date();
                const brisbaneOffset = 10 * 60 * 60000;
                const brisbaneTime = new Date(now.getTime() + now.getTimezoneOffset() * 60000 + brisbaneOffset);
                const yyyy = brisbaneTime.getFullYear();
                const mm = String(brisbaneTime.getMonth() + 1).padStart(2, '0');
                const dd = String(brisbaneTime.getDate()).padStart(2, '0');
                startDateInput.value = `${yyyy}-${mm}-${dd}`;
                $(startDateInput).trigger('change');
                setTimeout(() => {
                    const timeSelect = $('#auction_start_time');
                    if (timeSelect.find('option').length > 1) {
                        timeSelect.find('option:eq(1)').prop('selected', true).trigger('change');
                    }
                }, 100);
            }
        }
    }

    // Handle injected drop-area styling to match light theme
    const observer = new MutationObserver((mutations) => {
        mutations.forEach((mutation) => {
            if (mutation.addedNodes.length) {
                const dropArea = document.getElementById('image-upload-area');
                if (dropArea) {
                    dropArea.style.border = '2px dashed #D1D5DB';
                    dropArea.style.backgroundColor = '#F9FAFB';
                    dropArea.style.borderRadius = '12px';
                    dropArea.style.padding = '48px';
                    
                    const browseBtn = document.getElementById('browse-btn');
                    if (browseBtn) {
                        browseBtn.className = 'btn btn-emerald btn-sm ms-2';
                        browseBtn.style.borderRadius = '6px';
                    }
                    observer.disconnect();
                }
            }
        });
    });
    observer.observe(document.getElementById('auction-upload-section'), { childList: true });
</script>