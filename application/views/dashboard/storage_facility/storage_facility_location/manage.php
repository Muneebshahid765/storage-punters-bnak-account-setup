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
                <h1 class="dash-title-modern mb-0">Settings Hub</h1>
            </div>
            <div class="d-flex gap-2">
<!--                <button type="button" class="btn btn-outline-success" id="btn-discover-locations">-->
<!--                    <i class="bi bi-robot me-2"></i>Discover Locations with AI-->
<!--                </button>-->
                <button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#importStorageFacilityModal">
                    <i class="bi bi-file-earmark-spreadsheet me-2"></i>Import via CSV
                </button>
                <button type="button" class="btn-export-modern __btn__add__new__storage__facility" data-bs-toggle="modal" data-bs-target="#addStorageFacilityModal">
                    <i class="bi bi-plus-circle"></i> Add New Location
                </button>
            </div>
        </header>

        <!-- Settings Tabs -->
        <nav class="settings-hub-nav mb-4">
            <a href="<?php echo site_url('company/global-auction-rules'); ?>" class="settings-tab-link">Global Auction Rules</a>
            <a href="<?php echo site_url('company'); ?>" class="settings-tab-link">Company Setup</a>
            <a href="<?php echo site_url('storage-facility/edit-account'); ?>" class="settings-tab-link">Account Holder</a>
            <a href="<?php echo site_url('bank/setup/new'); ?>" class="settings-tab-link">Bank Account Setup</a>
            <a href="<?php echo site_url('storage-facility/manage'); ?>" class="settings-tab-link active">Manage Storage Locations</a>
            <a href="<?php echo site_url('storage-facility/notifications'); ?>" class="settings-tab-link">Notifications</a>
            <a href="<?php echo site_url('storage-facility/subscription'); ?>" class="settings-tab-link">Subscription</a>
        </nav>

        <div class="settings-card-modern">
            <h2 class="settings-form-title">Storage Facility Locations</h2>
            
            <div class="table-responsive">
                <table id="storage-facility-table" class="table-modern">
                    <thead>
                        <tr>
                            <th>Facility #</th>
                            <th>Facility Name</th>
                            <th>Company Name</th>
                            <th>Timezone</th>
                            <th>Last Modified</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Loaded via JS -->
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

<script>
    // Set company ID for location discovery
    const COMPANY_ID = <?php echo isset($company_id) ? $company_id : 'null'; ?>;
    
    // Trigger location discovery
    document.getElementById('btn-discover-locations')?.addEventListener('click', function() {
        if (COMPANY_ID) {
            triggerLocationDiscovery(COMPANY_ID);
        } else {
            alert('Company ID not found. Please ensure company is set up.');
        }
    });
</script>

<!-- Add Storage Facility Modal -->
<div class="modal fade" id="addStorageFacilityModal" tabindex="-1" aria-labelledby="addStorageFacilityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 p-4 pb-0">
                <h5 class="modal-title fw-bold fs-4" id="addStorageFacilityModalLabel">Add New Facility Location</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                
                <!-- Company Name (Hidden as per design, but required for logic) -->
                <div class="mb-3 d-none">
                     <select id="companiesDropdown" name="company_name" class="form-select">
                          <option value="">Loading company...</option>
                     </select>
                </div>

                <form id="storage-facility-add-form">
                    <input type="hidden" id="storage_facility_id" name="storage_facility_id">
                    <div class="row g-3">
                        
                        <!-- Facility Name -->
                        <div class="col-12">
                            <input id="storage_facility_name" type="text" name="storage_facility_name" class="form-control border rounded-3 p-3" placeholder="Facility Name (Required)" required>
                        </div>

                        <!-- ABN -->

                        <!-- Street Address -->
                        <div class="col-12">
                            <input id="address" type="text" name="address" class="form-control border rounded-3 p-3" placeholder="Street Address (Required)" required>
                        </div>

                        <!-- City & Post Code -->
                        <div class="col-md-6">
                            <input id="city" type="text" name="city" class="form-control border rounded-3 p-3" placeholder="City" required>
                        </div>
                        <div class="col-md-6">
                            <input id="zip" type="text" name="zip" class="form-control border rounded-3 p-3" placeholder="Post Code" required>
                        </div>

                        <!-- State & Country -->
                        <div class="col-md-6">
                             <select id="state" name="state" class="form-select border rounded-3 p-3" required>
                                <option value="" style="display:none">State</option>
                                <option value="New South Wales">New South Wales</option>
                                <option value="Victoria">Victoria</option>
                                <option value="Queensland" selected>Queensland</option>
                                <option value="Western Australia">Western Australia</option>
                                <option value="South Australia">South Australia</option>
                                <option value="Tasmania">Tasmania</option>
                                <option value="Australian Capital Territory">Australian Capital Territory</option>
                                <option value="Northern Territory">Northern Territory</option>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <select id="country" name="country" class="form-select border rounded-3 p-3" required>
                                 <option value="Australia" selected>Country (Australia)</option>
                            </select>
                        </div>

                        <!-- Timezone -->
                        <div class="col-12">
                             <select id="storage_facility_timezone" name="storage_facility_timezone" class="form-select border rounded-3 p-3" required>
                                <option value="" style="display:none">Timezone (e.g., Australia/Sydney)</option>
                                <option value="Australia/Adelaide">Australia/Adelaide</option>
                                <option value="Australia/Brisbane" selected>Australia/Brisbane</option>
                                <option value="Australia/Broken_Hill">Australia/Broken_Hill</option>
                                <option value="Australia/Canberra">Australia/Canberra</option>
                                <option value="Australia/Currie">Australia/Currie</option>
                                <option value="Australia/Darwin">Australia/Darwin</option>
                                <option value="Australia/Eucla">Australia/Eucla</option>
                                <option value="Australia/Hobart">Australia/Hobart</option>
                                <option value="Australia/LHI">Australia/LHI</option>
                                <option value="Australia/Lindeman">Australia/Lindeman</option>
                                <option value="Australia/Lord_Howe">Australia/Lord_Howe</option>
                                <option value="Australia/Melbourne">Australia/Melbourne</option>
                                <option value="Australia/NSW">Australia/NSW</option>
                                <option value="Australia/North">Australia/North</option>
                                <option value="Australia/Perth">Australia/Perth</option>
                                <option value="Australia/Queensland">Australia/Queensland</option>
                                <option value="Australia/South">Australia/South</option>
                                <option value="Australia/Sydney">Australia/Sydney</option>
                                <option value="Australia/Tasmania">Australia/Tasmania</option>
                                <option value="Australia/Victoria">Australia/Victoria</option>
                                <option value="Australia/West">Australia/West</option>
                                <option value="Australia/Yancowinna">Australia/Yancowinna</option>
                            </select>
                        </div>

                        <!-- Email -->
                        <div class="col-12">
                            <input id="storage_facility_contact_email" type="email" name="storage_facility_contact_email" class="form-control border rounded-3 p-3" placeholder="Facility Contact Email" required>
                        </div>

                        <!-- Phone -->
                        <div class="col-12">
                            <input id="cell_phone" type="text" name="cell_phone" class="form-control border rounded-3 p-3" placeholder="Facility Contact Phone" required>
                        </div>

                        <!-- Office Hours -->
                        <div class="col-12">
                            <label for="storage_facility_office_hours" class="form-label fw-600">Operating Hours *</label>
                            <textarea id="storage_facility_office_hours" name="storage_facility_office_hours" class="form-control border rounded-3 p-3" placeholder="e.g. Mon–Fri: 9am–5pm, Sat: 10am–2pm, Sun: Closed" rows="3" required></textarea>
                            <p class="text-muted small mb-0 mt-1">Buyers will see these hours in facility details and invoices.</p>
                        </div>

                        <!-- Description -->
                        <div class="col-12">
                            <textarea id="storage_facility_description" name="storage_facility_description" class="form-control border rounded-3 p-3" placeholder="Facility Description (Editable)" rows="4"></textarea>
                        </div>

                        <!-- Notice to Buyers -->
                         <div class="col-12">
                            <textarea id="storage_facility_notice_to_buyers" name="storage_facility_notice_to_buyers" class="form-control border rounded-3 p-3" placeholder="Notice to Buyers / Auction Terms (Editable)" rows="6">🤝 Agreement and Acceptance of Terms
Submitting a bid signifies your full acceptance of the Storage Punters Terms of Sale, establishing a valid and legally enforceable contract.

The Sale and Unit Condition:
Final Sale, No Guarantees: All storage unit contents are sold as a single, bulk lot and are strictly "as-is, where-is." The items are second-hand, and no warranties or condition guarantees are offered or implied.
Visuals are for Reference: Any images, photos, or item lists provided are for illustrative purposes only.
Strongly Recommend Inspection: Bidders are highly encouraged to pre-inspect the unit.
All Sales are Final: We operate a no-refund policy.
Mandatory Clean-Out: The winning bidder must clear the unit completely within 72 hours.
</textarea>
                        </div>

                        <!-- Hidden Fields (Required by JS/Backend but removed from UI) -->
                        <div class="d-none">
                             <input id="storage_facility_website" type="text" name="storage_facility_website" value="https://example.com">
                             <input id="storage_facility_additional_email" type="email" name="storage_facility_additional_email">
                             <input id="apt_suite" type="text" name="apt_suite">
                             <input id="storage_facility_branding_logo" type="text" name="storage_facility_branding_logo">
                             <textarea id="storage_facility_payout_details" name="storage_facility_payout_details"></textarea>
                             <input id="storage_facility_youtube_video_url" type="text" name="storage_facility_youtube_video_url">
                        </div>

                        <!-- Upload Section (Moved to Bottom) -->
                        <div id="storage-facility-upload-section" class="col-12">
                             <label class="form-label fw-600 mb-2">Upload/Edit Facility Images</label>
                             <!-- JS injects drop area here -->
                        </div>

                        <!-- Footer -->
                        <div class="col-12 mt-4 text-end border-top pt-4">
                            <button type="button" class="btn btn-white border px-4 me-2" data-bs-dismiss="modal">Cancel</button>
                            <button type="submit" id="save-storage-facility-add-btn" class="btn btn-emerald px-4" style="background-color: #0d3b2e; color: #ffffff;">Add/Save Facility</button>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
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
    const uploadSection = document.getElementById('storage-facility-upload-section');
    if(uploadSection){
        observer.observe(uploadSection, { childList: true });
    }
</script>

<!-- Import Storage Facility Modal -->
<div class="modal fade" id="importStorageFacilityModal" tabindex="-1" aria-labelledby="importStorageFacilityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 p-4 pb-0">
                <h5 class="modal-title fw-bold fs-4" id="importStorageFacilityModalLabel">Import Storage Locations</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
                <p class="text-muted mb-4">Upload a CSV file to bulk import storage facility locations while preserving data integrity.</p>
                
                <div id="csv-drop-zone" class="mb-4 p-3 bg-light rounded-3 border border-dashed text-center csv-drop-zone">
                    <i class="bi bi-cloud-arrow-up fs-1 text-primary mb-2 d-block"></i>
                    <h6 class="fw-bold mb-1">Upload CSV File</h6>
                    <p class="text-muted small mb-3">Drag & drop or browse from your device</p>
                    <input type="file" id="csv_file_input" class="d-none" accept=".csv">
                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="document.getElementById('csv_file_input').click()">Browse Files</button>
                    <div id="file-name-display" class="mt-2 small text-success fw-bold"></div>
                </div>

                <div class="d-flex align-items-center justify-content-between p-3 bg-light rounded-3 mb-4">
                    <div class="d-flex align-items-center">
                        <i class="bi bi-file-earmark-ruled fs-4 text-secondary me-3"></i>
                        <div>
                            <h6 class="fw-bold mb-0">Download Template</h6>
                            <p class="text-muted small mb-0">Use our standard format</p>
                        </div>
                    </div>
                    <a href="<?php echo base_url('assets/sample_storage_facility_import.csv'); ?>" download class="btn btn-sm btn-white border">Download</a>
                </div>

                <div class="text-end border-top pt-4">
                    <button type="button" class="btn btn-white border px-4 me-2" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" id="btn-import-csv" class="btn btn-primary px-4">Import Locations</button>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    .csv-drop-zone.dragover-active {
        background-color: #f8fbff;
        border-color: #0d6efd !important;
        box-shadow: 0 0 0.75rem rgba(13, 110, 253, 0.2);
    }
</style>

<!-- Include Location Discovery Modal -->
<?php $this->load->view('dashboard/storage_facility/company/location_discovery_modal'); ?>
