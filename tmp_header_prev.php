<div class="top-banner w-100 text-center py-2" style="background-color: #d1e7ff; color: #004085; font-size: 14px; font-weight: 500;">
    Welcome to the upgraded website! Share <a href="<?php echo site_url("contact-us"); ?>" style="color: #007bff; text-decoration: underline;">Feedback</a>.
</div>
<header class="nav-scroll">
    <div class="header px-0 px-md-2 bg-transparent container-fluid">
        <div class="d-flex align-items-center h-100 customContainer mx-auto container-fluid">
            <section class="false">
                <a href="<?php echo site_url(); ?>" class="brand-logo">
                    <i class="bi bi-box-seam-fill" style="color: #ffc107;"></i>
                    <span style="text-transform: uppercase;">STORAGE<span class="brand-text-highlight" style="color: #ffc107;">PUNTERS</span></span>
                </a>
            </section>

            <section class="header__links d-none d-lg-block h-100">
                <ul class="d-flex list-unstyled w-100 align-items-center justify-content-end justify-content-xl-center h-100">
                    <li class=" text-white false"><a class="text-200" href="<?php echo site_url(); ?>">Home</a></li>
                    <li class=" text-grey false"><a class="text-200" href='<?php echo site_url("vault"); ?>'>The Vault</a></li>
                    <li class=" text-grey false"><a class="text-200" href='<?php echo site_url("videos"); ?>'>Punters TV</a></li>
                    <li class=" text-grey false"><a class="text-200" href='<?php echo site_url("auctions"); ?>'>Auctions</a></li>
                    <li class=" text-grey false"><a class="text-200" href='<?php echo site_url("faqs"); ?>'>FAQs</a></li>
                    <li class=" text-grey false"><a class="text-200" href='<?php echo site_url("contact-us"); ?>'>Contact</a></li>
                </ul>
            </section>
            <section class="position-relative  d-flex h-100  d-md-flex align-items-center ms-auto  ">

                <!-- THE VAULT BUTTON (Desktop) -->
                <button type="button" class="btn btn-warning rounded-pill px-3 py-2 font-weight-bold d-none d-sm-flex align-items-center me-3" style="font-weight: 800; letter-spacing: 1px; color: #000; border: 2px solid #b8860b; background: linear-gradient(45deg, #FFD700, #FDB931);" data-bs-toggle="modal" data-bs-target="#vaultModal">
                    <i class="bi bi-lock-fill me-2"></i> THE VAULT
                </button>

                <?php if (!$this->session->userdata('auction_user_session_data')) { ?>

                    <button type="button" style="height: 44px; min-width: max-content; width: 100%;"
                        class="btn_primary_gradient position-relative px-3 font-semibold d-flex justify-content-center align-items-center __header__btn d-none d-sm-flex me-2 outline-dark-btn btn btn-primary"
                        data-bs-toggle="modal" data-bs-target="#unifiedAuthModal">
                        <div class="me-2" style="color: inherit;">
                            <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" class="me-1 d-flex d-md-none" height="18" width="18" xmlns="http://www.w3.org/2000/svg">
                                <path d="M256 256c52.805 0 96-43.201 96-96s-43.195-96-96-96-96 43.201-96 96 43.195 96 96 96zm0 48c-63.598 0-192 32.402-192 96v48h384v-48c0-63.598-128.402-96-192-96z"></path>
                            </svg>
                        </div>Log In / Register
                    </button>

                <?php } else {

                    $userSessionData = $this->session->userdata('auction_user_session_data');
                    $userId = $userSessionData['user_id'];
                    $userRoleId = $userSessionData['role_id'];

                ?>

                    <div class="profile-btn d-lg-none d-flex ms-auto ">
                        <button type="button" class="bg-transparent text-dark border-0 btn btn-primary">
                            <span class="d-flex align-items-center gap-1">
                                <svg stroke="currentColor" fill="none" stroke-width="0" viewBox="0 0 24 24" height="25" width="25" xmlns="http://www.w3.org/2000/svg">
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M16 9C16 11.2091 14.2091 13 12 13C9.79086 13 8 11.2091 8 9C8 6.79086 9.79086 5 12 5C14.2091 5 16 6.79086 16 9ZM14 9C14 10.1046 13.1046 11 12 11C10.8954 11 10 10.1046 10 9C10 7.89543 10.8954 7 12 7C13.1046 7 14 7.89543 14 9Z" fill="currentColor"></path>
                                    <path fill-rule="evenodd" clip-rule="evenodd" d="M12 1C5.92487 1 1 5.92487 1 12C1 18.0751 5.92487 23 12 23C18.0751 23 23 18.0751 23 12C23 5.92487 18.0751 1 12 1ZM3 12C3 14.0902 3.71255 16.014 4.90798 17.5417C6.55245 15.3889 9.14627 14 12.0645 14C14.9448 14 17.5092 15.3531 19.1565 17.4583C20.313 15.9443 21 14.0524 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12ZM12 21C9.84977 21 7.87565 20.2459 6.32767 18.9878C7.59352 17.1812 9.69106 16 12.0645 16C14.4084 16 16.4833 17.1521 17.7538 18.9209C16.1939 20.2191 14.1881 21 12 21Z" fill="currentColor"></path>
                                </svg>
                                <span class="dropdown-icon">
                                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M256 294.1L383 167c9.4-9.4 24.6-9.4 33.9 0s9.3 24.6 0 34L273 345c-9.1 9.1-23.7 9.3-33.1.7L95 201.1c-4.7-4.7-7-10.9-7-17s2.3-12.3 7-17c9.4-9.4 24.6-9.4 33.9 0l127.1 127z"></path>
                                    </svg>
                                </span>
                            </span>
                        </button>
                    </div>

                    <?php if ($userRoleId == 2) { ?>
                        <li class="list-unstyled btn__wallet__balance">
                            <button type="button" style="border-radius:12px;border:none;font-weight:500;" class="btn btn-yellow btn btn-primary d-none d-lg-flex align-items-center gap-2 fw-bold px-2">
                                <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-credit-card" viewBox="0 0 16 16">
                                    <path d="M0 4a2 2 0 0 1 2-2h12a2 2 0 0 1 2 2v1H0V4z" />
                                    <path d="M0 7v5a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V7H0zm3 2h2a.5.5 0 0 1 0 1H3a.5.5 0 0 1 0-1z" />
                                </svg>
                                <span class="wallet_balance" style="color: #fff !important;">$0.00 AUD</span>
                            </button>

                        </li>

                    <?php } ?>

                    <?php if ($userRoleId == 1 || $userRoleId == 3) { ?>
                        <li class="list-unstyled btn__list__new__auction">
                            <a href="<?php echo site_url('storage-facility/ai/new'); ?>" style="border-radius:12px;border:none;font-weight:600;color:#fff;background-color: #1e392a;" class="btn btn-primary d-none d-lg-flex align-items-center gap-2 px-3 py-2 text-decoration-none">
                                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                                    <path d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zM0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8z" />
                                    <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z" />
                                </svg>
                                <span>List a New Auction</span>
                            </a>
                        </li>

                    <?php } ?>



                    <div class="profile-btn ms-2">
                        <button type="button" class="bg-transparent text-dark border-0 btn btn-primary d-flex align-items-center gap-2">
                            <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" height="24" width="24" xmlns="http://www.w3.org/2000/svg">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z" />
                            </svg>
                            <span class="fw-bold">Profile</span>
                            <span class="dropdown-icon">
                                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" height="16" width="16" xmlns="http://www.w3.org/2000/svg">
                                    <path d="M7 10l5 5 5-5z" />
                                </svg>
                            </span>
                        </button>
                    </div>



                <?php } ?>

            </section>

            <div style="background-color: white; z-index:9999;" class="d-none position-absolute login-popup col-md-4">
                <div class="mx-auto py-2 py-md-4 col-md-10">


                    <!-- Description -->
                    <div class="text-center mb-3">
                        <h3 class="fw-bold text-dark">Welcome Back!</h3>
                        <p class="text-muted">Log in to your account to access exclusive features, track your bids, and manage your dashboard efficiently.</p>
                    </div>

                    <form id="login_form" class="px-4 mt-3 px-md-0">
                        <div class="row">
                            <div class="col-12">
                                <label class="input-label-secondary">Username
                                    <svg xmlns="http://www.w3.org/2000/svg" width="19.006px" height="19.006px" viewBox="0 0 19.006 20.734" class="ms-2">
                                        <path id="Icon_awesome-asterisk" data-name="Icon awesome-asterisk" d="M20,13.53l-5.759-3.163L20,7.2a.972.972,0,0,0,.374-1.338L19.587,4.5a.972.972,0,0,0-1.346-.345L12.623,7.561,12.764.993A.972.972,0,0,0,11.792,0H10.215a.972.972,0,0,0-.972.993l.141,6.569L3.765,4.155A.972.972,0,0,0,2.42,4.5L1.631,5.867A.972.972,0,0,0,2,7.2l5.759,3.162L2,13.53a.972.972,0,0,0-.374,1.338l.789,1.366a.972.972,0,0,0,1.346.345l5.618-3.406-.141,6.569a.972.972,0,0,0,.972.993h1.578a.972.972,0,0,0,.972-.993l-.141-6.569,5.618,3.406a.972.972,0,0,0,1.346-.345l.789-1.366A.972.972,0,0,0,20,13.53Z" transform="translate(-1.5)" fill="var(--btn-bg-yellow)"></path>
                                    </svg>
                                </label>
                                <div class="input-field-container"><input type="text" name="email" id="email" placeholder="Username" class="input-secondary" value=""></div>
                            </div>
                            <div class="mt-2 mt-md-4 col-12">
                                <label class="input-label-secondary">Password
                                    <svg xmlns="http://www.w3.org/2000/svg" width="19.006px" height="19.006px" viewBox="0 0 19.006 20.734" class="ms-2">
                                        <path id="Icon_awesome-asterisk" data-name="Icon awesome-asterisk" d="M20,13.53l-5.759-3.163L20,7.2a.972.972,0,0,0,.374-1.338L19.587,4.5a.972.972,0,0,0-1.346-.345L12.623,7.561,12.764.993A.972.972,0,0,0,11.792,0H10.215a.972.972,0,0,0-.972.993l.141,6.569L3.765,4.155A.972.972,0,0,0,2.42,4.5L1.631,5.867A.972.972,0,0,0,2,7.2l5.759,3.162L2,13.53a.972.972,0,0,0-.374,1.338l.789,1.366a.972.972,0,0,0,1.346.345l5.618-3.406-.141,6.569a.972.972,0,0,0,.972.993h1.578a.972.972,0,0,0,.972-.993l-.141-6.569,5.618,3.406a.972.972,0,0,0,1.346-.345l.789-1.366A.972.972,0,0,0,20,13.53Z" transform="translate(-1.5)" fill="var(--btn-bg-yellow)"></path>
                                    </svg>
                                </label>
                                <div class="input-field-container"><input name="password" id="password" placeholder="Password" type="password" class=" input-secondary" value="">
                                    <div class="input-svg toggle-password">
                                        <svg xmlns="http://www.w3.org/2000/svg" width="23.401" height="auto" viewBox="0 0 23.401 15.601" class="">
                                            <path id="Icon_awesome-eye" data-name="Icon awesome-eye" d="M23.26,11.707A13.03,13.03,0,0,0,11.7,4.5,13.032,13.032,0,0,0,.141,11.708a1.314,1.314,0,0,0,0,1.186A13.03,13.03,0,0,0,11.7,20.1,13.032,13.032,0,0,0,23.26,12.893,1.314,1.314,0,0,0,23.26,11.707ZM11.7,18.151a5.85,5.85,0,1,1,5.85-5.85A5.85,5.85,0,0,1,11.7,18.151Zm0-9.75a3.872,3.872,0,0,0-1.028.154,1.944,1.944,0,0,1-2.718,2.718A3.891,3.891,0,1,0,11.7,8.4Z" transform="translate(0 -4.5)" fill="var(--btn-bg-yellow)"></path>
                                        </svg>
                                    </div>
                                </div>
                                <p class="text-end mt-2 text-yellow __system__user__forgot__password__btn" style="cursor: pointer;">Forgot Password?</p>
                            </div>
                            <div class="mx-auto col-10"><button type="submit" style="background: var(--icon-yellow); font-weight: bold; border: 0px;" class="w-100 btn-login btn btn-primary" data-dashlane-label="true" data-dashlane-rid="311d23e999c375a2" data-dashlane-classification="action,login" data-kwimpalastatus="dead">Sign In</button></div>
                            <div class="text-center mt-2 mt-md-3 col-12">
                                <p>Don't have an Account? <span class="text-yellow" data-bs-toggle="modal" data-bs-target="#unifiedAuthModal" onclick="showBidderRegister()" style="cursor: pointer;">Sign Up Now</span></p>
                            </div>
                            <div style="position: fixed; z-index: 9999; inset: 16px; pointer-events: none;"></div>
                        </div>
                    </form>
                </div>
            </div>


            <div style="background-color: white; z-index:9999;" class="d-none position-absolute forgot-password-popup col-md-4">
                <div class="mx-auto py-2 py-md-4 col-md-10">
                    <form id="forgot_password_form" class="px-4 px-md-0">
                        <div class="row">
                            <div class="mt-2 mt-md-4 col-12">
                                <h2 class="text-center font-bold" style="color: var(--icon-yellow);">Forgot Password</h2>
                            </div>
                            <div class="mt-2 mt-md-4 col-12"><svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" font-size="32" class="mb-4 d-flex cursor-pointer __system__user__forgot__password__back__btn" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="none" d="M0 0h24v24H0V0z"></path>
                                    <path d="M21 11H6.83l3.58-3.59L9 6l-6 6 6 6 1.41-1.41L6.83 13H21v-2z"></path>
                                </svg>
                                <h5 class="text-center font-semibold text-grey">Email Password Reset Link</h5>
                            </div>
                            <div class="mt-4 col-12">
                                <p class="text-center text-grey">Enter the email address of the associated account you would like to recover and a link to reset your password will be sent </p>
                            </div>
                            <div class="mt-2 mt-md-4 col-12">
                                <div class="input-field-container"><input name="email_address" id="email_address" placeholder="Email address associated with your account" type="email" class=" input-secondary "></div>
                            </div>
                            <div class="mt-4 pt-md-4 col-12"><button type="submit" style="background: var(--icon-yellow); font-weight: 600; border: 0px;" class="w-100 btn-forgot-password text-400 btn btn-primary">Reset Password and Send Link</button> </div>
                        </div>
                        <div style="position: fixed; z-index: 9999; inset: 16px; pointer-events: none;"></div>
                    </form>
                </div>
            </div>

            <section class="d-flex align-items-center">
                <div class="d-none d-lg-none d-flex ms-auto ">
                    <button type="button" class="bg-transparent text-dark border-0 btn btn-primary">
                        <span class="d-flex align-items-center gap-1">
                            <svg stroke="currentColor" fill="none" stroke-width="0" viewBox="0 0 24 24" height="25" width="25" xmlns="http://www.w3.org/2000/svg">
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M16 9C16 11.2091 14.2091 13 12 13C9.79086 13 8 11.2091 8 9C8 6.79086 9.79086 5 12 5C14.2091 5 16 6.79086 16 9ZM14 9C14 10.1046 13.1046 11 12 11C10.8954 11 10 10.1046 10 9C10 7.89543 10.8954 7 12 7C13.1046 7 14 7.89543 14 9Z" fill="currentColor"></path>
                                <path fill-rule="evenodd" clip-rule="evenodd" d="M12 1C5.92487 1 1 5.92487 1 12C1 18.0751 5.92487 23 12 23C18.0751 23 23 18.0751 23 12C23 5.92487 18.0751 1 12 1ZM3 12C3 14.0902 3.71255 16.014 4.90798 17.5417C6.55245 15.3889 9.14627 14 12.0645 14C14.9448 14 17.5092 15.3531 19.1565 17.4583C20.313 15.9443 21 14.0524 21 12C21 7.02944 16.9706 3 12 3C7.02944 3 3 7.02944 3 12ZM12 21C9.84977 21 7.87565 20.2459 6.32767 18.9878C7.59352 17.1812 9.69106 16 12.0645 16C14.4084 16 16.4833 17.1521 17.7538 18.9209C16.1939 20.2191 14.1881 21 12 21Z" fill="currentColor"></path>
                            </svg>
                            <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                                <path d="M256 294.1L383 167c9.4-9.4 24.6-9.4 33.9 0s9.3 24.6 0 34L273 345c-9.1 9.1-23.7 9.3-33.1.7L95 201.1c-4.7-4.7-7-10.9-7-17s2.3-12.3 7-17c9.4-9.4 24.6-9.4 33.9 0l127.1 127z"></path>
                            </svg>
                        </span>
                    </button>
                </div>
                <div class="d-lg-none d-flex ms-auto" style="cursor: pointer;">
                    <svg class="header__nav__open_button__dropdown" stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 1024 1024" height="30" width="30" xmlns="http://www.w3.org/2000/svg">
                        <path d="M912 192H328c-4.4 0-8 3.6-8 8v56c0 4.4 3.6 8 8 8h584c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8zm0 284H328c-4.4 0-8 3.6-8 8v56c0 4.4 3.6 8 8 8h584c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8zm0 284H328c-4.4 0-8 3.6-8 8v56c0 4.4 3.6 8 8 8h584c4.4 0 8-3.6 8-8v-56c0-4.4-3.6-8-8-8zM104 228a56 56 0 1 0 112 0 56 56 0 1 0-112 0zm0 284a56 56 0 1 0 112 0 56 56 0 1 0-112 0zm0 284a56 56 0 1 0 112 0 56 56 0 1 0-112 0z"></path>
                    </svg>
                </div>
            </section>
        </div>
        <nav class="">
            <div class="header__nav__dropdown  false">
                <div class="topBar px-0 mx-0 h-maxC container-fluid">
                    <div class="d-flex align-items-center customContainer container-fluid">
                        <section class=" ps-2"></section>

                        <!-- Mobile Vault Button -->
                        <section class="me-2">
                            <button type="button" style="height:44px;min-width:max-content;" 
                                class="btn btn-warning rounded-pill px-3 font-weight-bold d-flex align-items-center d-lg-none" 
                                style="font-weight: 800; color: #000; background: linear-gradient(45deg, #FFD700, #FDB931);"
                                data-bs-toggle="modal" data-bs-target="#vaultModal">
                                <i class="bi bi-lock-fill me-2"></i> VAULT
                            </button>
                        </section>

                        <?php if (!$this->session->userdata('auction_user_session_data')) { ?>
                            <section>
                                <button type="button" style="height:44px;min-width:max-content;width:100%"
                                    class=" btn_primary_gradient position-relative px-3 font-semibold d-flex justify-content-center  align-items-center  __header__btn d-lg-none d-lg-flex me-2 outline-dark-btn btn btn-primary"
                                    data-bs-toggle="modal" data-bs-target="#unifiedAuthModal">
                                    <div class="me-2 " style="color:inherit">
                                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" class="me-1" height="18" width="18" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M256 256c52.805 0 96-43.201 96-96s-43.195-96-96-96-96 43.201-96 96 43.195 96 96 96zm0 48c-63.598 0-192 32.402-192 96v48h384v-48c0-63.598-128.402-96-192-96z"></path>
                                        </svg>
                                    </div>Log In / Register
                                </button>
                            </section>

                        <?php } ?>

                        <section class=" ps-2 d-flex flex-column ms-auto">
                            <div class="mt-auto">
                                <svg class="header__nav__close_button__dropdown" stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" color="#fff" style="color:#fff" height="30" width="30" xmlns="http://www.w3.org/2000/svg">
                                    <path fill="none" d="M0 0h24v24H0z"></path>
                                    <path d="M19 6.41L17.59 5 12 10.59 6.41 5 5 6.41 10.59 12 5 17.59 6.41 19 12 13.41 17.59 19 19 17.59 13.41 12z"></path>
                                </svg>
                            </div>
                        </section>
                    </div>
                </div>

                <ul class="d-flex list-unstyled mb-0 w-100 align-items-start padding-y-13 justify-content-start px-4 pt-2 ">
                    <li class=" header__nav__dropdown_li text-white  mt-0 "><a href="<?php echo site_url(); ?>">Home</a></li>
                    <li class="text-grey false "><a href='<?php echo site_url("vault"); ?>'>The Vault</a></li>
                    <li class="text-grey false "><a href='<?php echo site_url("videos"); ?>'>Punters TV</a></li>
                    <li class="text-grey false "><a href='<?php echo site_url("auctions"); ?>'>Auctions</a></li>
                    <li class="text-grey false "><a href='<?php echo site_url("faqs"); ?>'>FAQs</a></li>
                    <li class="text-grey false "><a href='<?php echo site_url("contact-us"); ?>'>Contact</a></li>
                </ul>
            </div>
        </nav>
    </div>
</header>
