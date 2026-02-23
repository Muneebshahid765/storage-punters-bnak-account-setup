<?php
$userSessionData = $this->session->userdata('auction_user_session_data');
?>

<!-- Load Header Partial -->
<?php $this->load->view('dashboard/bidder/layout/header_account'); ?>

<div class="container">
    <div class="dashboard-card">
        <h3 class="card-title-custom">Edit Your Profile</h3>

        <section class="settings-card-modern p-0">
            <form id="user-profile-form">
                <div class="row">
                    <div class="mt-4 col-xl-6 col-lg-3 col-md-6">
                        <label for="firstname" class="input-label-secondary">First Name *</label>
                        <div class="input-field-container">
                            <input id="firstname" type="text" placeholder="Enter First Name" name="firstname" class="input-secondary text-300" value="" disabled>
                        </div>
                    </div>

                    <div class="mt-4 col-xl-6 col-lg-3 col-md-6">
                        <label for="lastname" class="input-label-secondary">Last Name *</label>
                        <div class="input-field-container">
                            <input id="lastname" type="text" placeholder="Enter Last Name" name="lastname" class="input-secondary text-300" value="" disabled>
                        </div>
                    </div>

                    <div class="mt-4 col-xl-6 col-lg-3 col-md-6">
                        <label for="standard_email_address" class="input-label-secondary">Email *</label>
                        <div class="input-field-container">
                            <input id="standard_email_address" type="email" placeholder="Enter Email Address" name="standard_email_address" class="input-secondary text-300" value="" disabled>
                        </div>
                    </div>

                    <div class="mt-4 col-xl-6 col-lg-3 col-md-6">
                        <label for="cell_phone" class="input-label-secondary">Mobile Number *</label>
                        <div class="input-field-container">
                            <input id="cell_phone" type="tel" placeholder="Enter Mobile Number" name="cell_phone" class="input-secondary text-300" value="" disabled>
                        </div>
                    </div>

                    <div class="mt-4 col-xl-6 col-lg-3 col-md-6">
                        <label for="password" class="input-label-secondary">Password *</label>
                        <div class="input-field-container">
                            <input id="current_password" type="password" placeholder="Enter Password" name="current_password" class="input-secondary text-300" value="">
                            <div class="input-svg toggle-password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="23.401" height="auto" viewBox="0 0 23.401 15.601" class="">
                                    <path id="Icon_awesome-eye" data-name="Icon awesome-eye" d="M23.26,11.707A13.03,13.03,0,0,0,11.7,4.5,13.032,13.032,0,0,0,.141,11.708a1.314,1.314,0,0,0,0,1.186A13.03,13.03,0,0,0,11.7,20.1,13.032,13.032,0,0,0,23.26,12.893,1.314,1.314,0,0,0,23.26,11.707ZM11.7,18.151a5.85,5.85,0,1,1,5.85-5.85A5.85,5.85,0,0,1,11.7,18.151Zm0-9.75a3.872,3.872,0,0,0-1.028.154,1.944,1.944,0,0,1-2.718,2.718A3.891,3.891,0,1,0,11.7,8.4Z" transform="translate(0 -4.5)" fill="var(--btn-bg-yellow)"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="mt-4 col-xl-6 col-lg-3 col-md-6">
                        <label for="password_confirmation" class="input-label-secondary">Confirm Password *</label>
                        <div class="input-field-container">
                            <input id="password_confirmation" type="password" placeholder="Enter Confirm Password" name="password_confirmation" class="input-secondary text-300" value="">
                            <div class="input-svg toggle-password">
                                <svg xmlns="http://www.w3.org/2000/svg" width="23.401" height="auto" viewBox="0 0 23.401 15.601" class="">
                                    <path id="Icon_awesome-eye" data-name="Icon awesome-eye" d="M23.26,11.707A13.03,13.03,0,0,0,11.7,4.5,13.032,13.032,0,0,0,.141,11.708a1.314,1.314,0,0,0,0,1.186A13.03,13.03,0,0,0,11.7,20.1,13.032,13.032,0,0,0,23.26,12.893,1.314,1.314,0,0,0,23.26,11.707ZM11.7,18.151a5.85,5.85,0,1,1,5.85-5.85A5.85,5.85,0,0,1,11.7,18.151Zm0-9.75a3.872,3.872,0,0,0-1.028.154,1.944,1.944,0,0,1-2.718,2.718A3.891,3.891,0,1,0,11.7,8.4Z" transform="translate(0 -4.5)" fill="var(--btn-bg-yellow)"></path>
                                </svg>
                            </div>
                        </div>
                    </div>

                    <div class="mt-5 mx-auto col-md-12 col-12 text-center">
                        <button type="submit" id="save-updates-btn" style="border-radius:12px;border:none;font-weight:700" class="btn-yellow btn-contact text-uppercase px-5 py-3 btn btn-primary btn-md w-100 w-md-auto">Save Updates
                        </button>
                    </div>
                </div>
            </form>
            <div style="position: fixed; z-index: 9999; inset: 16px; pointer-events: none;"></div>
        </section>
    </div>
</div>
