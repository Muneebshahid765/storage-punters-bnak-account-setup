<main style="flex-grow:1">
    <div class="d-none d-md-block" style="height: 50px;"></div>
    <div class="standard__seller__signup customContainer mt-4 mb-4 container">
        <h1 class="text-xl text-grey text-center" style="font-weight: bold;">Register for Standard Seller Membership</h1>
        <form id="storage-punter-signup-form" class="mt-2 mt-md-4">
            <div class="row">
                <div class="mt-4 col-xl-6 col-lg-3 col-md-6">
                    <label for="firstname" class="input-label-secondary">First Name *</label>
                    <div class="input-field-container">
                        <input id="first_name" type="text" placeholder="Enter First name" name="first_name" class="input-secondary text-300" value="">
                    </div>
                </div>

                <div class="mt-4 col-xl-6 col-lg-3 col-md-6">
                    <label for="lastname" class="input-label-secondary">Last Name *</label>
                    <div class="input-field-container">
                        <input id="last_name" type="text" placeholder="Enter Last Name" name="last_name" class="input-secondary text-300" value="">
                    </div>
                </div>

                 <!--
                    <div class="mt-4 col-xl-2 col-lg-3 col-md-6">
                        <label for="dob" class="input-label-secondary">Date of Birth *</label>
                        <div class="input-field-container">
                            <input id="dob" type="date" name="dob" class="input-secondary text-300" value="">
                        </div>
                    </div>
                -->

                <div class="mt-4 col-xl-6 col-lg-4 col-md-6">
                    <label for="standard_email_address" class="input-label-secondary">Email *</label>
                    <div class="input-field-container">
                        <input id="standard_email_address" type="email" placeholder="Enter Email Address" name="standard_email_address" class="input-secondary text-300" value="">
                    </div>
                </div>

                <div class="mt-4 col-xl-6 col-lg-4 col-md-6">
                    <label for="cell_phone" class="input-label-secondary">Mobile Number *</label>
                    <div class="input-field-container">
                        <input id="cell_phone" type="tel" placeholder="Enter Mobile Number" name="cell_phone" class="input-secondary text-300" value="">
                    </div>
                </div>

                <div class="mt-4 col-xl-6 col-lg-4 col-md-6">
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

                <div class="mt-4 col-xl-6 col-lg-4 col-md-6">
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

                <!--
                <div class="mt-4 col-xl-9 col-md-4 col-12 d-none">
                    <label for="address" class="input-label-secondary">Address *</label>
                    <div class="input-field-container">
                        <input id="address" type="text" placeholder="Enter street address" name="address" class="input-secondary" value="Address 1">
                    </div>
                </div>

                <div class="mt-4 col-xl-12 col-md-4 col-12 d-none">
                    <label for="apt_suite" class="input-label-secondary">Apt/Suite</label>
                    <div class="input-field-container">
                        <input id="apt_suite" type="text" placeholder="Enter apartment" name="apt_suite" class="input-secondary text-300" value="0">
                    </div>
                </div>

                <div class="mt-4 col-xl-3 col-md-4 col-12 d-none">
                    <label for="country" class="input-label-secondary">Country *</label>
                    <div class="input-field-container">
                        <select id="country" name="country" class="input-secondary">
                            <option value="Australia" selected>Australia</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 col-xl-3 col-md-4 col-12 d-none">
                    <label for="state" class="input-label-secondary">State *</label>
                    <div class="input-field-container">
                        <select id="state" name="state" class="input-secondary">
                            <option value="" style="display:none">Select a state</option>
                            <option value="New South Wales" selected>New South Wales</option>
                            <option value="Victoria">Victoria</option>
                            <option value="Queensland">Queensland</option>
                            <option value="Western Australia">Western Australia</option>
                            <option value="South Australia">South Australia</option>
                            <option value="Tasmania">Tasmania</option>
                            <option value="Australian Capital Territory">Australian Capital Territory</option>
                            <option value="Northern Territory">Northern Territory</option>
                        </select>
                    </div>
                </div>

                <div class="mt-4 col-xl-3 col-md-4 col-12 d-none">
                    <label for="zip" class="input-label-secondary">Post Code *</label>
                    <div class="input-field-container">
                        <input id="zip" type="text" placeholder="Enter Post Code" name="zip" class="input-secondary text-300" value="0000">
                    </div>
                </div>

                <div class="mt-4 col-xl-3 col-md-4 col-12 d-none">
                    <label for="city" class="input-label-secondary">City *</label>
                    <div class="input-field-container">
                        <input id="city" type="text" placeholder="Enter City" name="city" class="input-secondary text-300" value="Sydney">
                    </div>
                </div>

            -->

                <div class="mt-4 col-xl-3 col-md-4 col-12">
                    <label for="timezone" class="input-label-secondary">Time Zone *</label>
                    <div class="input-field-container">
                        <select id="timezone" name="timezone" class="input-secondary">
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
                </div>

                <div class="mt-4 col-xl-9  col-md-8  col-12">
                    <label for="hear_us" class="input-label-secondary">How did you hear about us?</label>
                    <div class="input-field-container">
                        <select id="hear_us" name="hear_us" class="input-secondary">
                            <option value="">Select...</option>
                            <option value="google">Google Search</option>
                            <option value="social_media">Social Media (Facebook, Instagram, Twitter, etc.)</option>
                            <option value="referral">Referral from a Friend</option>
                            <option value="other" selected>Other</option>
                        </select>

                    </div>
                </div>

                <!-- Bank Account Info Box -->
                <div class="mt-4 col-12">
                    <hr class="my-3" style="border-color: #444;">
                    <div class="p-3 rounded" style="background: linear-gradient(135deg, #1a1a2e 0%, #16213e 100%); border: 1px solid #0f3460;">
                        <div class="d-flex align-items-center mb-2">
                            <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" fill="#635bff" viewBox="0 0 24 24" class="me-2">
                                <path d="M13.976 9.15c-2.172-.806-3.356-1.426-3.356-2.409 0-.831.683-1.305 1.901-1.305 2.227 0 4.515.858 6.09 1.631l.89-5.494C18.252.975 15.697 0 12.165 0 9.667 0 7.589.654 6.104 1.872 4.56 3.147 3.757 4.992 3.757 7.218c0 4.039 2.467 5.76 6.476 7.219 2.585.92 3.445 1.574 3.445 2.583 0 .98-.84 1.545-2.354 1.545-1.875 0-4.965-.921-6.99-2.109l-.9 5.555C5.175 22.99 8.385 24 11.714 24c2.641 0 4.843-.624 6.328-1.813 1.664-1.305 2.525-3.236 2.525-5.732 0-4.128-2.524-5.851-6.591-7.305z"/>
                            </svg>
                            <h6 class="text-white mb-0" style="font-weight: 600;">Secure Bank Account Setup</h6>
                        </div>
                        <p class="text-muted small mb-0">
                            <i class="bi bi-shield-check me-1"></i>
                            After registration, you'll be redirected to Stripe's secure platform to add your bank account details. 
                            This ensures your financial information is protected with bank-level security and enables verified payouts.
                        </p>
                    </div>
                </div>

                <div style="width:max-content" class="mt-4 mx-auto  col-md-12 col">
                    <button type="submit" id="action-button" style="border-radius:12px;border:none;font-weight:500" class="w-100 btn-yellow btn-contact text-uppercase px-md-4 btn btn-primary btn-md">Register Now

                    </button>
                </div>

                <!-- Leave this blank — we'll inject OTP input here -->
                <div id="otp-dynamic-container" class="mt-3 text-center"></div>


            </div>
        </form>
    </div>


    <div class="d-none d-md-block" style="height: 50px;"></div>
</main>