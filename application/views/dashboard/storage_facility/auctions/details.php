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
                    <h1 class="dash-title-modern">Edit Auction Details</h1>
                    <p class="text-muted mb-0">Manage and update your auction listing.</p>
                </div>
            </div>
             <a href="<?php echo site_url('storage-facility/dashboard'); ?>" class="btn btn-outline-secondary btn-sm d-flex align-items-center gap-2">
                <i class="bi bi-arrow-left"></i> Back to Dashboard
            </a>
        </header>

        <div class="content-section-modern p-4 p-md-5">
             <form id="auction-update-form">
                <div id="auction-upload-section"></div>

                <!-- Basic Information Card -->
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4" style="background-color: #f8f9fa;">
                    <h6 class="fw-bold mb-3">Basic Information</h6>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="auction_id" class="form-label fw-600 small">Auction ID</label>
                            <input id="auction_id" name="auction_id" type="text" class="form-control border rounded-3 p-2 bg-light" disabled>
                        </div>

                        <div class="col-md-4">
                            <label for="auctionTypeDropdown" class="form-label fw-600 small">Auction Type</label>
                            <select id="auctionTypeDropdown" name="auction_type" class="form-select border rounded-3 p-2">
                                <option value="">Loading types...</option>
                            </select>
                        </div>

                        <div class="col-md-4">
                            <label for="storageFacilityDropdown" class="form-label fw-600 small">Storage Facility</label>
                            <select id="storageFacilityDropdown" name="storage_facility" class="form-select border rounded-3 p-2">
                                <option value="">Loading facilities...</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="auction_unit_number" class="form-label fw-600 small">Unit Number</label>
                            <input id="auction_unit_number" type="text" class="form-control border rounded-3 p-2" placeholder="e.g. B202">
                        </div>

                        <div class="col-md-6">
                            <label for="auctionCategoryDropdown" class="form-label fw-600 small">Category</label>
                            <select id="auctionCategoryDropdown" name="auction_category" class="form-select border rounded-3 p-2">
                                <option value="">Loading categories...</option>
                            </select>
                        </div>

                        <div class="col-12">
                            <label for="auction_description" class="form-label fw-600 small">Description</label>
                            <textarea id="auction_description" name="auction_description" class="form-control border rounded-3 p-2" rows="4"></textarea>
                        </div>

                        <div class="col-12">
                            <label for="auction_tags" class="form-label fw-600 small">Tags</label>
                            <input id="auction_tags" type="text" class="form-control border rounded-3 p-2" placeholder="Enter tags (comma-separated)">
                        </div>
                    </div>
                </div>

                <!-- Outstanding Debt Card -->
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <h6 class="fw-bold mb-3">Outstanding Debt ($)</h6>
                    <input id="auction_outstanding_dept" type="number" class="form-control border rounded-3 p-3 mb-2" placeholder="500" step="0.01">
                    <p class="text-muted small mb-0">This is used to calculate the starting bid based on your global percentage setting.</p>
                </div>

                <!-- Auction Settings Card -->
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <h6 class="fw-bold mb-3">Auction Settings</h6>
                    
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="auction_set_days" class="form-label fw-600 small">Duration</label>
                            <select id="auction_set_days" class="form-select border rounded-3 p-2">
                                <option value="5" data-type="minute">5 minutes</option>
                                <option value="15" data-type="minute">15 minutes</option>
                                <option value="30" data-type="minute">30 minutes</option>
                                <option value="1" data-type="hour">1 hour</option>
                                <option value="2" data-type="hour">2 hours</option>
                                <option value="1" data-type="day">1 day</option>
                                <option value="3" data-type="day" selected>3 days</option>
                                <option value="5" data-type="day">5 days</option>
                                <option value="7" data-type="day">7 days</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="auction_disclose_unit_number_on_website" class="form-label fw-600 small">Disclose Unit Number?</label>
                            <select id="auction_disclose_unit_number_on_website" class="form-select border rounded-3 p-2">
                                <option value="0">No</option>
                                <option value="1">Yes</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="auction_cleaning_deposit" class="form-label fw-600 small">Cleaning Deposit ($)</label>
                            <select id="auction_cleaning_deposit" class="form-select border rounded-3 p-2">
                                <option value="0.00">Select Cleaning Deposit</option>
                                <option value="25.00">$25.00</option>
                                <option value="50.00">$50.00</option>
                                <option value="100.00">$100.00</option>
                                <option value="150.00">$150.00</option>
                                <option value="200.00">$200.00</option>
                                <option value="250.00">$250.00</option>
                                <option value="300.00">$300.00</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="auction_start_bid_amount" class="form-label fw-600 small">Starting Bid ($)</label>
                            <input id="auction_start_bid_amount" type="number" class="form-control border rounded-3 p-2" placeholder="Minimum bid amount" step="0.01">
                        </div>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-3">
                            <label for="auction_start_date" class="form-label fw-600 small">Start Date</label>
                            <input id="auction_start_date" type="date" class="form-control border rounded-3 p-2">
                        </div>
                        <div class="col-md-3">
                            <label for="auction_start_time" class="form-label fw-600 small">Start Time</label>
                            <select id="auction_start_time" class="form-select border rounded-3 p-2">
                                <option value="">Select time</option>
                                <option value="12:00 AM">12:00 AM</option>
                                <option value="01:00 AM">01:00 AM</option>
                                <option value="02:00 AM">02:00 AM</option>
                                <option value="03:00 AM">03:00 AM</option>
                                <option value="04:00 AM">04:00 AM</option>
                                <option value="05:00 AM">05:00 AM</option>
                                <option value="06:00 AM">06:00 AM</option>
                                <option value="07:00 AM">07:00 AM</option>
                                <option value="08:00 AM">08:00 AM</option>
                                <option value="09:00 AM">09:00 AM</option>
                                <option value="10:00 AM">10:00 AM</option>
                                <option value="11:00 AM">11:00 AM</option>
                                <option value="12:00 PM">12:00 PM</option>
                                <option value="01:00 PM">01:00 PM</option>
                                <option value="02:00 PM">02:00 PM</option>
                                <option value="03:00 PM">03:00 PM</option>
                                <option value="04:00 PM">04:00 PM</option>
                                <option value="05:00 PM">05:00 PM</option>
                                <option value="06:00 PM">06:00 PM</option>
                                <option value="07:00 PM">07:00 PM</option>
                                <option value="08:00 PM">08:00 PM</option>
                                <option value="09:00 PM">09:00 PM</option>
                                <option value="10:00 PM">10:00 PM</option>
                                <option value="11:00 PM">11:00 PM</option>
                            </select>
                        </div>

                        <div class="col-md-3">
                            <label for="auction_end_date" class="form-label fw-600 small">End Date</label>
                            <input id="auction_end_date" type="date" class="form-control border rounded-3 p-2" readonly>
                        </div>
                        <div class="col-md-3">
                            <label for="auction_end_time" class="form-label fw-600 small">End Time</label>
                            <select id="auction_end_time" class="form-select border rounded-3 p-2" readonly></select>
                        </div>
                    </div>
                </div>

                <!-- Additional Details Card -->
                <div class="card border-0 shadow-sm rounded-3 p-4 mb-4">
                    <h6 class="fw-bold mb-3">Additional Details</h6>
                    
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="auction_cleaning_deposit_payment_type" class="form-label fw-600 small">Cleaning Deposit Payment Type</label>
                            <select id="auction_cleaning_deposit_payment_type" class="form-select border rounded-3 p-2">
                                <option value="">Select Payment Type</option>
                                <option value="Cash">Cash</option>
                                <option value="Credit">Credit</option>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label for="auction_unit_size" class="form-label fw-600 small">Unit Size</label>
                            <select id="auction_unit_size" name="auction_unit_size" class="form-select border rounded-3 p-2">
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

                        <div class="col-12">
                            <label for="auction_youtube_video_url" class="form-label fw-600 small">YouTube Video URL</label>
                            <input id="auction_youtube_video_url" type="url" class="form-control border rounded-3 p-2" placeholder="Paste YouTube link (optional)">
                        </div>

                        <div class="col-12">
                            <label for="auction_additional_features" class="form-label fw-600 small">Additional Features</label>
                            <input id="auction_additional_features" type="text" class="form-control border rounded-3 p-2" placeholder="Comma-separated features">
                        </div>
                    </div>
                </div>

                <div class="text-center">
                    <button type="submit" id="save-auction-updates-btn" class="btn btn-lg px-5 text-white" style="background-color: #16a085;">
                        Save Updates
                    </button>
                </div>
            </form>
        </div>
    </main>
</div>
<script>
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
    const uploadSection = document.getElementById('auction-upload-section');
    if(uploadSection){
         observer.observe(uploadSection, { childList: true });
    }
</script>