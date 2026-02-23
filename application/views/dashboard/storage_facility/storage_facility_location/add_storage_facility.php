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
                <h1 class="dash-title-modern mb-0">Add Storage Facility</h1>
            </div>
            <p class="text-muted mb-0 d-none d-md-block">Enter the details below to add a new storage facility location.</p>
        </header>

        <div class="content-section-modern p-4 p-md-5">
            <?php
            $userSessionData = $this->session->userdata('auction_user_session_data');
            $userRoleId = $userSessionData['role_id'];

            // Check if user is logged in
            if (in_array($userRoleId, [1])) {
                // Determine active tab/buttons?
                // The original code had a tab list but it seemed mostly navigation. 
                // We integrated navigation into the sidebar.
            }
            ?>

             <div class="mb-4">
                <div class="row">
                     <!-- Company Name -->
                    <div class="col-12 mb-3">
                        <label for="companiesDropdown" class="form-label fw-600">Company Name</label>
                        <select id="companiesDropdown" name="company_name" class="form-select border rounded-3 p-2">
                             <option value="">Loading company...</option>
                        </select>
                    </div>
                </div>
            </div>

            <form id="storage-facility-add-form">
                <div class="row g-4">

                    <div id="storage-facility-upload-section"></div>

                    <div class="col-12">
                        <label for="storage_facility_name" class="form-label fw-600">Facility Name *</label>
                        <input id="storage_facility_name" type="text" name="storage_facility_name" class="form-control border rounded-3 p-2" placeholder="e.g. Northside Storage Center" value="">
                    </div>


                    <div class="col-md-6">
                        <label for="storage_facility_website" class="form-label fw-600">Facility Website *</label>
                        <input id="storage_facility_website" type="text" placeholder="e.g. www.northsidestorage.com" name="storage_facility_website" class="form-control border rounded-3 p-2" value="">
                    </div>

                    <div class="col-12">
                        <label for="storage_facility_description" class="form-label fw-600">Facility Description</label>
                        <textarea id="storage_facility_description" name="storage_facility_description" class="form-control border rounded-3 p-2" placeholder="e.g. Northside Storage offers secure, 24/7 access self-storage units in various sizes." rows="4"></textarea>
                    </div>

                    <div class="col-md-6">
                        <label for="storage_facility_timezone" class="form-label fw-600">Timezone *</label>
                         <select id="storage_facility_timezone" name="storage_facility_timezone" class="form-select border rounded-3 p-2">
                            <option value="" style="display:none">Select a time zone</option>
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

                    <div class="col-md-6">
                        <label for="storage_facility_contact_email" class="form-label fw-600">Facility Contact Email *</label>
                        <input id="storage_facility_contact_email" type="email" name="storage_facility_contact_email" class="form-control border rounded-3 p-2" placeholder="e.g. contact@example.com" value="">
                    </div>

                    <div class="col-md-6">
                        <label for="storage_facility_additional_email" class="form-label fw-600">Facility Additional Email</label>
                        <input id="storage_facility_additional_email" type="email" name="storage_facility_additional_email" class="form-control border rounded-3 p-2" placeholder="e.g. support@example.com" value="">
                    </div>

                    <div class="col-md-6">
                        <label for="cell_phone" class="form-label fw-600">Phone Number *</label>
                        <input id="cell_phone" type="text" placeholder="e.g. (123) 456-7890" name="cell_phone" class="form-control border rounded-3 p-2" value="">
                    </div>

                    <div class="col-12">
                        <label for="storage_facility_office_hours" class="form-label fw-600">Office Hours *</label>
                        <textarea id="storage_facility_office_hours" name="storage_facility_office_hours" class="form-control border rounded-3 p-2" placeholder="e.g. Mon–Fri: 9am–5pm, Sat: 10am–2pm, Sun: Closed" rows="3"></textarea>
                    </div>
                    
                     <div class="col-12 d-none">
                        <label for="storage_facility_payout_details" class="form-label fw-600">Payout Details</label>
                         <textarea id="storage_facility_payout_details" name="storage_facility_payout_details" class="form-control border rounded-3 p-2" placeholder="e.g. Bank: Westpac, BSB: 123-456, Account: 987654321" rows="3"></textarea>
                    </div>

                    <div class="col-md-6 d-none">
                        <label for="storage_facility_youtube_video_url" class="form-label fw-600">YouTube Video URL</label>
                        <input id="storage_facility_youtube_video_url" type="text" name="storage_facility_youtube_video_url" class="form-control border rounded-3 p-2" placeholder="e.g. https://www.youtube.com/watch?v=example" value="">
                    </div>

                    <div class="col-12">
                        <label for="storage_facility_branding_logo" class="form-label fw-600">Brand Logo URL</label>
                         <input id="storage_facility_branding_logo" type="text" name="storage_facility_branding_logo" class="form-control border rounded-3 p-2" placeholder="e.g. https://yourdomain.com/logo.png" value="">
                    </div>

                     <div class="col-12">
                        <label for="storage_facility_notice_to_buyers" class="form-label fw-600">Notice to Buyers *</label>
                        <textarea id="storage_facility_notice_to_buyers" name="storage_facility_notice_to_buyers" class="form-control border rounded-3 p-2" placeholder="e.g. Winning auction buyers MUST make contact with the facility manager to make arrangements for payment and pick up won auctions with in 24hrs." rows="8">
🤝 Agreement and Acceptance of Terms
Submitting a bid signifies your full acceptance of the Storage Punters Terms of Sale, establishing a valid and legally enforceable contract.

🔑 Key Terms for Bidders
The Sale and Unit Condition
Final Sale, No Guarantees: All storage unit contents are sold as a single, bulk lot and are strictly "as-is, where-is." The items are second-hand, and no warranties or condition guarantees are offered or implied.

Visuals are for Reference: Any images, photos, or item lists provided are for illustrative purposes only and should be treated as a guide, not a guarantee of contents.

Strongly Recommend Inspection: Bidders are highly encouraged to pre-inspect the unit before placing a bid. Please coordinate directly with the storage facility for inspection appointments.

All Sales are Final: We operate a no-refund policy. If the listing details are unclear and you are unable to arrange an inspection, we advise you not to proceed with a bid.

Mandatory Clean-Out: The winning bidder must clear the unit completely and remove all contents within 72 hours of the auction ending.

Auction Adjustments
Seller's Right to Withdraw: The auction seller reserves the right to remove or withdraw a listing from the website at any time prior to the scheduled close.

Post-Sale Voids: The seller may cancel a sale after payment has been processed but before you take possession of the goods. If this happens, you will receive a full reimbursement of all funds paid.

Closing Process
Timed Finish: All auctions use a Soft Close feature.

The auction concludes when the listing status officially updates to "SOLD."

💳 Pricing and Payment
Pricing: All prices are quoted in Australian Dollars (AUD) and are inclusive of Goods and Services Tax (GST).

Buyer's Service Fee: A 17.5% Service Fee (inclusive of GST) is applied to the final winning bid price.

Calculation Example: A $100 winning bid + $17.50 Service Fee = $117.50 total due.

Punters Perk: You can eliminate this service fee entirely by subscribing to a Punters membership.

Total Cost Display: The final payable amount is clearly presented on the auction page above the bidding controls.

Automatic Transaction: Payment is automatically processed and charged to your registered credit or debit card immediately upon the close of the auction.

📦 Collection and Site Rules
Access Requirements: To collect the unit, the registered winner must present photo identification and the official winner notification email.

Security Deposit: A Cleaning Security Deposit is required and must be paid directly to the storage facility prior to being granted access.

The amount and accepted payment method for this deposit are detailed on the auction page.

This deposit will be fully returned once the facility staff confirm the unit is completely empty and clean.

Non-Compliance Penalty: Failure to pay the deposit or successfully clear the unit within the 72-hour period may result in the forfeiture of your purchase.

Entry Terms: By entering the property, you agree to abide by all operational rules and instructions set forth by the Storage Facility management while on their premises.

Handling Sensitive Items
Return Personal Items: Any personal effects found (e.g., family photos, official documents, records) must be returned to the storage facility staff.

Illegal/Dangerous Goods: Discoveries of prohibited items or firearms must be immediately reported to the storage facility and local police, and you must comply with their instructions.

Arranging Your Pickup
Please contact the seller immediately upon winning to arrange your collection time and process the security deposit.
                        </textarea>
                    </div>

                    <div class="col-md-9">
                        <label for="address" class="form-label fw-600">Address *</label>
                        <input id="address" type="text" placeholder="Enter Street Address" name="address" class="form-control border rounded-3 p-2" value="">
                    </div>

                    <div class="col-md-3">
                         <label for="apt_suite" class="form-label fw-600">Apt/Suite</label>
                         <input id="apt_suite" type="text" placeholder="Enter Apartment" name="apt_suite" class="form-control border rounded-3 p-2" value="">
                    </div>

                    <div class="col-md-3">
                        <label for="country" class="form-label fw-600">Country *</label>
                        <select id="country" name="country" class="form-select border rounded-3 p-2">
                             <option value="Australia" selected>Australia</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="state" class="form-label fw-600">State *</label>
                         <select id="state" name="state" class="form-select border rounded-3 p-2">
                            <option value="" style="display:none">Select a state</option>
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

                    <div class="col-md-3">
                        <label for="zip" class="form-label fw-600">Post Code *</label>
                        <input id="zip" type="text" placeholder="Enter Post Code" name="zip" class="form-control border rounded-3 p-2" value="">
                    </div>
                     <div class="col-md-3">
                        <label for="city" class="form-label fw-600">City *</label>
                        <input id="city" type="text" placeholder="Enter City" name="city" class="form-control border rounded-3 p-2" value="">
                    </div>

                    <div class="col-12 mt-5 text-end border-top pt-4">
                        <button type="submit" id="save-storage-facility-add-btn" class="btn btn-emerald px-5">Add Facility</button>
                    </div>
                </div>
            </form>
            <div style="position: fixed; z-index: 9999; inset: 16px; pointer-events: none;"></div>
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
    const uploadSection = document.getElementById('storage-facility-upload-section');
    if(uploadSection){
        observer.observe(uploadSection, { childList: true });
    }
</script>