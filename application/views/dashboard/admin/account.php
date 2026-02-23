<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>
<div class="dashboard-container-modern admin-dark-theme">
    <!-- Sidebar Overlay -->
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <!-- Sidebar -->
    <?php $this->load->view('dashboard/admin/sidebar'); ?>

    <!-- Main Content -->
    <main class="main-content-modern">
        <header class="dash-header-modern">
            <div class="d-flex align-items-center">
                <!-- Mobile Toggle Button -->
                <button type="button" class="btn btn-link text-white d-lg-none me-3 p-0" id="sidebarToggle">
                    <i class="bi bi-list fs-2"></i>
                </button>
                <h1 class="dash-title-modern mb-0">Edit Your Profile</h1>
            </div>
        </header>

        <section class="settings-card-modern">
            <form id="user-profile-form">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="firstname" class="form-label small fw-bold text-muted">First Name *</label>
                        <input id="firstname" type="text" placeholder="Enter your first name" 
                            name="firstname" class="modern-form-control" value="" disabled>
                    </div>

                    <div class="col-md-4">
                        <label for="lastname" class="form-label small fw-bold text-muted">Last Name *</label>
                        <input id="lastname" type="text" placeholder="Enter last name" 
                            name="lastname" class="modern-form-control" value="" disabled>
                    </div>

                    <div class="col-md-4">
                        <label for="dob" class="form-label small fw-bold text-muted">Date of Birth *</label>
                        <input id="dob" type="date" name="dob" class="modern-form-control" value="">
                    </div>

                    <div class="col-md-6">
                        <label for="standard_email_address" class="form-label small fw-bold text-muted">Email *</label>
                        <input id="standard_email_address" type="email" placeholder="Enter Email Address" 
                            name="standard_email_address" class="modern-form-control" value="" disabled>
                    </div>

                    <div class="col-md-6">
                        <label for="cell_phone" class="form-label small fw-bold text-muted">Cell Phone *</label>
                        <input id="cell_phone" type="tel" placeholder="Enter cell number" 
                            name="cell_phone" class="modern-form-control" value="" disabled>
                    </div>

                    <div class="col-md-6">
                        <label for="current_password" class="form-label small fw-bold text-muted">Password *</label>
                        <div class="input-field-container">
                            <input id="current_password" type="password" placeholder="Enter password" 
                                name="current_password" class="modern-form-control" value="">
                            <div class="input-svg toggle-password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="23.401" height="auto" viewBox="0 0 23.401 15.601">
                                    <path d="M23.26,11.707A13.03,13.03,0,0,0,11.7,4.5,13.032,13.032,0,0,0,.141,11.708a1.314,1.314,0,0,0,0,1.186A13.03,13.03,0,0,0,11.7,20.1,13.032,13.032,0,0,0,23.26,12.893,1.314,1.314,0,0,0,23.26,11.707ZM11.7,18.151a5.85,5.85,0,1,1,5.85-5.85A5.85,5.85,0,0,1,11.7,18.151Zm0-9.75a3.872,3.872,0,0,0-1.028.154,1.944,1.944,0,0,1-2.718,2.718A3.891,3.891,0,1,0,11.7,8.4Z" transform="translate(0 -4.5)" fill="var(--btn-bg-yellow)"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="password_confirmation" class="form-label small fw-bold text-muted">Repeat Password *</label>
                        <div class="input-field-container">
                            <input id="password_confirmation" type="password" placeholder="Enter password again" 
                                name="password_confirmation" class="modern-form-control" value="">
                            <div class="input-svg toggle-password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="23.401" height="auto" viewBox="0 0 23.401 15.601">
                                    <path d="M23.26,11.707A13.03,13.03,0,0,0,11.7,4.5,13.032,13.032,0,0,0,.141,11.708a1.314,1.314,0,0,0,0,1.186A13.03,13.03,0,0,0,11.7,20.1,13.032,13.032,0,0,0,23.26,12.893,1.314,1.314,0,0,0,23.26,11.707ZM11.7,18.151a5.85,5.85,0,1,1,5.85-5.85A5.85,5.85,0,0,1,11.7,18.151Zm0-9.75a3.872,3.872,0,0,0-1.028.154,1.944,1.944,0,0,1-2.718,2.718A3.891,3.891,0,1,0,11.7,8.4Z" transform="translate(0 -4.5)" fill="var(--btn-bg-yellow)"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="col-md-9">
                        <label for="address" class="form-label small fw-bold text-muted">Address *</label>
                        <input id="address" type="text" placeholder="Enter street address" 
                            name="address" class="modern-form-control" value="">
                    </div>

                    <div class="col-md-3">
                        <label for="apt_suite" class="form-label small fw-bold text-muted">Apt/Suite</label>
                        <input id="apt_suite" type="text" placeholder="Enter apartment" 
                            name="apt_suite" class="modern-form-control" value="">
                    </div>

                    <div class="col-md-3">
                        <label for="country" class="form-label small fw-bold text-muted">Country *</label>
                        <select id="country" name="country" class="modern-form-control">
                            <option value="Australia" selected>Australia</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="state" class="form-label small fw-bold text-muted">State *</label>
                        <select id="state" name="state" class="modern-form-control">
                            <option value="" style="display:none">Select a state</option>
                            <option value="New South Wales">New South Wales</option>
                            <option value="Victoria">Victoria</option>
                            <option value="Queensland">Queensland</option>
                            <option value="Western Australia">Western Australia</option>
                            <option value="South Australia">South Australia</option>
                            <option value="Tasmania">Tasmania</option>
                            <option value="Australian Capital Territory">Australian Capital Territory</option>
                            <option value="Northern Territory">Northern Territory</option>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="zip" class="form-label small fw-bold text-muted">ZIP *</label>
                        <input id="zip" type="text" placeholder="Enter ZIP" 
                            name="zip" class="modern-form-control" value="">
                    </div>

                    <div class="col-md-3">
                        <label for="city" class="form-label small fw-bold text-muted">City *</label>
                        <input id="city" type="text" placeholder="Enter City" 
                            name="city" class="modern-form-control" value="">
                    </div>

                    <div class="col-md-6">
                        <label for="timezone" class="form-label small fw-bold text-muted">Time Zone *</label>
                        <select id="timezone" name="timezone" class="modern-form-control">
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
                        <label for="hear_us" class="form-label small fw-bold text-muted">How did you hear about us?</label>
                        <select id="hear_us" name="hear_us" class="modern-form-control">
                            <option value="" selected>Select...</option>
                            <option value="google">Google Search</option>
                            <option value="social_media">Social Media (Facebook, Instagram, Twitter, etc.)</option>
                            <option value="word_of_mouth">Word of Mouth</option>
                            <option value="referral">Referral from a Friend</option>
                            <option value="email_campaign">Email Campaign</option>
                            <option value="tv_advertisement">TV Advertisement</option>
                            <option value="radio_advertisement">Radio Advertisement</option>
                            <option value="newspaper">Newspaper / Magazine</option>
                            <option value="online_advertisement">Online Advertisement</option>
                            <option value="youtube">YouTube</option>
                            <option value="blog">Blog / Article</option>
                            <option value="linkedin">LinkedIn</option>
                            <option value="company_website">Company Website</option>
                            <option value="event_or_webinar">Event or Webinar</option>
                            <option value="other">Other</option>
                        </select>
                    </div>

                    <div class="col-12">
                        <button type="submit" id="save-updates-btn" class="btn-modern-save">
                            Save Updates
                        </button>
                    </div>
                </div>
            </form>
        </section>
    </main>
</div>