<main style="flex-grow:1">
    <div style="height:350px" class="w-100 p-0 login-banner border ">
        <section class="banner__info">
            <h1 style="font-size:56px;font-weight:bold;text-align:center;max-width:100%" class="font-weight-bold px-2">Welcome to your new experience</h1>
        </section>
    </div>
    <div class=" login__page d-flex flex-column container-md justify-content-center align-items-md-center mt-4 mb-4 py-4 container-fluid">
        <div class="login__container  d-flex flex-column justify-content-md-center px-3 px-md-0"><svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 24 24" font-size="32" class="mb-4 d-none cursor-pointer" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                <path fill="none" d="M0 0h24v24H0V0z"></path>
                <path d="M21 11H6.83l3.58-3.59L9 6l-6 6 6 6 1.41-1.41L6.83 13H21v-2z"></path>
            </svg>
            <form id="login_form2" class="px-4 mt-3 px-md-0">
                <div class="row">
                    <p class="fw-bold text-center mb-5" style="font-size:26px;color:var(--text-color-grey)">Log in using your same credentials</p>
                    <div class="col-12"><label class="input-label-secondary">Username<!-- --> <svg xmlns="http://www.w3.org/2000/svg" width="19.006px" height="19.006px" viewBox="0 0 19.006 20.734" class="ms-2">
                                <path id="Icon_awesome-asterisk" data-name="Icon awesome-asterisk" d="M20,13.53l-5.759-3.163L20,7.2a.972.972,0,0,0,.374-1.338L19.587,4.5a.972.972,0,0,0-1.346-.345L12.623,7.561,12.764.993A.972.972,0,0,0,11.792,0H10.215a.972.972,0,0,0-.972.993l.141,6.569L3.765,4.155A.972.972,0,0,0,2.42,4.5L1.631,5.867A.972.972,0,0,0,2,7.2l5.759,3.162L2,13.53a.972.972,0,0,0-.374,1.338l.789,1.366a.972.972,0,0,0,1.346.345l5.618-3.406-.141,6.569a.972.972,0,0,0,.972.993h1.578a.972.972,0,0,0,.972-.993l-.141-6.569,5.618,3.406a.972.972,0,0,0,1.346-.345l.789-1.366A.972.972,0,0,0,20,13.53Z" transform="translate(-1.5)" fill="var(--btn-bg-yellow)"></path>
                            </svg></label>
                        <div class="input-field-container"><input name="email2" id="email2" placeholder="Username" value="" class=" input-secondary "></div>
                    </div>
                    <div class="mt-2 mt-md-4 col-12"><label class="input-label-secondary">Password<!-- --> <svg xmlns="http://www.w3.org/2000/svg" width="19.006px" height="19.006px" viewBox="0 0 19.006 20.734" class="ms-2">
                                <path id="Icon_awesome-asterisk" data-name="Icon awesome-asterisk" d="M20,13.53l-5.759-3.163L20,7.2a.972.972,0,0,0,.374-1.338L19.587,4.5a.972.972,0,0,0-1.346-.345L12.623,7.561,12.764.993A.972.972,0,0,0,11.792,0H10.215a.972.972,0,0,0-.972.993l.141,6.569L3.765,4.155A.972.972,0,0,0,2.42,4.5L1.631,5.867A.972.972,0,0,0,2,7.2l5.759,3.162L2,13.53a.972.972,0,0,0-.374,1.338l.789,1.366a.972.972,0,0,0,1.346.345l5.618-3.406-.141,6.569a.972.972,0,0,0,.972.993h1.578a.972.972,0,0,0,.972-.993l-.141-6.569,5.618,3.406a.972.972,0,0,0,1.346-.345l.789-1.366A.972.972,0,0,0,20,13.53Z" transform="translate(-1.5)" fill="var(--btn-bg-yellow)"></path>
                            </svg></label>
                        <div class="input-field-container"><input type="password" name="password2" id="password2" placeholder="Password" value="" class=" input-secondary " >
                            <div class="input-svg toggle-password"><svg xmlns="http://www.w3.org/2000/svg" width="23.401" height="auto" viewBox="0 0 23.401 15.601" class="">
                                    <path id="Icon_awesome-eye" data-name="Icon awesome-eye" d="M23.26,11.707A13.03,13.03,0,0,0,11.7,4.5,13.032,13.032,0,0,0,.141,11.708a1.314,1.314,0,0,0,0,1.186A13.03,13.03,0,0,0,11.7,20.1,13.032,13.032,0,0,0,23.26,12.893,1.314,1.314,0,0,0,23.26,11.707ZM11.7,18.151a5.85,5.85,0,1,1,5.85-5.85A5.85,5.85,0,0,1,11.7,18.151Zm0-9.75a3.872,3.872,0,0,0-1.028.154,1.944,1.944,0,0,1-2.718,2.718A3.891,3.891,0,1,0,11.7,8.4Z" transform="translate(0 -4.5)" fill="var(--btn-bg-yellow)"></path>
                                </svg></div>
                        </div>
                        <p class="text-end mt-2 text-yellow __system__user__forgot__password__btn" style="cursor:pointer">Forgot Password?</p>
                    </div>
                    <div class="mx-auto col-10"><button type="submit" style="background:var(--icon-yellow);font-weight:bold;border:0" class="w-100 btn-login btn btn-primary">Sign In</button></div>
                    <div class="text-center mt-2 mt-md-3 col-12">
                        <p>Don't have an Account?<!-- --> <span class="text-yellow" data-bs-toggle="modal" data-bs-target="#unifiedAuthModal" onclick="showBidderRegister()" style="cursor:pointer">Sign Up Now</span></p>
                    </div>
                    <div style="position:fixed;z-index:9999;top:16px;left:16px;right:16px;bottom:16px;pointer-events:none"></div>
                </div>
            </form>
        </div>
    </div>
</main>