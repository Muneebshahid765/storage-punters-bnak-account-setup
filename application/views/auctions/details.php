<div class="p-0 d-flex flex-column h-100 container-fluid">

    <main style="flex-grow:1">

        <div class="customContainer px-0 container-fluid">

            <div class="col-12">
                <div class="w-100 mt-md-3 px-md-4 py-md-5 p-3">
                    <h2 class="text-light-black text-sm mb-md-4 section_heading" style="background:#f1f1f1;font-weight:600;width:100%">Unit Details</h2>
                    <div class="auction__details__section w-100"></div>
                </div>
            </div>




            <div class="px-3 each__auction__picture__section__container ms-0 mt-md-4 justify-content-start justify-content-md-start no-gutters me-0 row">
                <div class="px-0  each__auction__picture__section  col-xl-5 col-lg-6 col-md-12">



                    <div class="col-md-12 col-12">
                        <div class="mt-md-3 w-100 d-flex align-items-center flex-column">




                            <h2 class="text-light-black text-sm mb-md-4 section_heading" style="background:#f1f1f1;font-weight:600;width:100%">Storage Unit Preview</h2>

                            <!-- 🖼️ Auction Image Slider START -->
                            <div id="auctionImageCarousel" class="carousel slide mb-4 w-100" data-bs-ride="carousel">
                                <div class="carousel-inner rounded-5" id="auction-image-container">
                                    <!-- Images will be loaded dynamically via JS -->
                                    <div class="carousel-item active">
                                        <img src="<?= base_url('assets/images/no-image.png') ?>" class="d-block w-100 rounded-3" alt="No Image Available" style="height: 350px; object-fit: cover;">
                                    </div>
                                </div>

                                <!-- Controls -->
                                <button class="carousel-control-prev" type="button" data-bs-target="#auctionImageCarousel" data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#auctionImageCarousel" data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                </button>
                            </div>
                            <!-- 🖼️ Auction Image Slider END -->



                        </div>
                    </div>
                </div>
                <div class="ms-0 px-0 px-lg-3 ms-xl-4 d-flex flex-column col-lg-6 col-12">
                    <div class="d-flex flex-column justify-content-start align-items-start mt-md-0" style="flex-grow:1">
                        <div class="d-none d-md-flex w-75 auction__count_down__section_for_large_devices __timing__  mx-auto justify-content-lg-between px-0 text-center false row">
                            <!-- <div class="ps-0  col-3">
                                <div class="__timing__box__ __Days" style="visibility: visible;">00</div>
                                <p class="text-center mt-2 ">Days</p>
                            </div>
                            <div class="col-3">
                                <div class="__timing__box__ __Hours" style="visibility: visible;">02</div>
                                <p class="text-center mt-2 ">Hours</p>
                            </div>
                            <div class="col-3">
                                <div class="__timing__box__ __Minutes " style="visibility: visible;">04</div>
                                <p class="text-center mt-2 ">Minutes</p>
                            </div>
                            <div class="col-3">
                                <div class="__timing__box__ __Seconds" style="visibility: visible;">08</div>
                                <p class="text-center mt-2 ">Seconds</p>
                            </div>-->
                        </div>
                        <p style="visibility: visible;" class=" mt-3 px-4 mt-md-2 font-medium __timezone__">Time zone in Brisbane QLD, Australia</p>
                        <div class="loggin-to-bid mt-3 mb-1"></div>
                        <div class="w-100 p-4 __auction__timing__session__ false">
                            <div class="d-flex align-items-center gap-4 p-2">
                                <span style="box-sizing:border-box;display:inline-block;overflow:hidden;width:initial;height:initial;background:none;opacity:1;border:0;margin:0;padding:0;position:relative;max-width:100%">
                                    <span style="box-sizing:border-box;display:block;width:initial;height:initial;background:none;opacity:1;border:0;margin:0;padding:0;max-width:100%">
                                        <img style="display:block;max-width:100%;width:initial;height:initial;background:none;opacity:1;border:0;margin:0;padding:0" alt="" aria-hidden="true" src="data:image/svg+xml,%3csvg%20xmlns=%27http://www.w3.org/2000/svg%27%20version=%271.1%27%20width=%2720%27%20height=%2720%27/%3e">
                                    </span>
                                    <img alt="calender_img" src="<?= base_url('assets/images/auction_details/calender.svg') ?>" style="position:absolute;top:0;left:0;bottom:0;right:0;box-sizing:border-box;padding:0;border:none;margin:auto;display:block;width:0;height:0;min-width:100%;max-width:100%;min-height:100%;max-height:100%">
                                </span>
                                <p class="font-medium text-200 my-auto"><strong>Starts: </strong> <span class="auction__start_date_time__section"></span></p>
                            </div>
                            <div class="d-flex align-items-center gap-4 p-2">
                                <span style="box-sizing:border-box;display:inline-block;overflow:hidden;width:initial;height:initial;background:none;opacity:1;border:0;margin:0;padding:0;position:relative;max-width:100%">
                                    <span style="box-sizing:border-box;display:block;width:initial;height:initial;background:none;opacity:1;border:0;margin:0;padding:0;max-width:100%">
                                        <img style="display:block;max-width:100%;width:initial;height:initial;background:none;opacity:1;border:0;margin:0;padding:0" alt="" aria-hidden="true" src="data:image/svg+xml,%3csvg%20xmlns=%27http://www.w3.org/2000/svg%27%20version=%271.1%27%20width=%2720%27%20height=%2720%27/%3e">
                                    </span>
                                    <img alt="eye_img" src="<?= base_url('assets/images/auction_details/eye.svg') ?>" style="visibility:hidden;position:absolute;top:0;left:0;bottom:0;right:0;box-sizing:border-box;padding:0;border:none;margin:auto;display:block;width:0;height:0;min-width:100%;max-width:100%;min-height:100%;max-height:100%">
                                </span>
                                <span class="d-flex flex-column flex-xl-row gap-2 dashboard">
                                    <p class="font-medium text-200 my-auto"><strong>Ends: </strong> <span class="auction__end_date_time__section"></span></p>
                                    <p class="font-medium text-100 my-auto"><span class="auction__default__timezone"></span> </p>
                                </span>
                            </div>
                            <div class="d-flex align-items-center gap-4 p-2">
                                <span style="box-sizing:border-box;display:inline-block;overflow:hidden;width:initial;height:initial;background:none;opacity:1;border:0;margin:0;padding:0;position:relative;max-width:100%">
                                    <span style="box-sizing:border-box;display:block;width:initial;height:initial;background:none;opacity:1;border:0;margin:0;padding:0;max-width:100%">
                                        <img style="display:block;max-width:100%;width:initial;height:initial;background:none;opacity:1;border:0;margin:0;padding:0" alt="" aria-hidden="true" src="data:image/svg+xml,%3csvg%20xmlns=%27http://www.w3.org/2000/svg%27%20version=%271.1%27%20width=%2720%27%20height=%2720%27/%3e">
                                    </span>
                                    <img alt="eye_img" src="<?= base_url('assets/images/auction_details/eye.svg') ?>" style="position:absolute;top:0;left:0;bottom:0;right:0;box-sizing:border-box;padding:0;border:none;margin:auto;display:block;width:0;height:0;min-width:100%;max-width:100%;min-height:100%;max-height:100%">
                                </span>
                                <p class="font-medium text-200 my-auto"><strong>Views: </strong><span class="auction__total_views__section"></span></p>
                            </div>
                        </div>
                        <div class="w-100  p-2 p-md-4   __auction__timing__session__ mt-3 d-flex false">
                            <div class=" d-none d-lg-flex justify-content-center"><span style="width:100px;height:100px;object-fit:cover"> <img class="hammer__img" alt="hammer" width="100%" height="100%" src="<?= base_url('assets/images/auction_details/hammer.svg') ?>"></span></div>
                            <div class="mx-auto mx-md-0" style="position:relative;z-index:2">
                                <div class="d-flex gap-2 justify-content-between">
                                    <div class="w-20 ">
                                        <p class="text-100 font-semibold">Current Price</p>
                                        <div class="d-flex gap-1 align-items-center">
                                            <h3 style="color:var(--color-high-bid)" class="font-bold auction__current_price__section">0</h3><span class="text-100 font-semibold">AUD</span>
                                        </div>
                                    </div>
                                    <div class="gap-2 w-60 ps-auto d-flex justify-content-end  d-md-none auction__count_down__section_for_small_devices __timing__  mx-auto justify-content-lg-between px-0 text-center false">
                                        <!--<div class="d-flex px-0 justify-content-center flex-column align-items-center ps-md-0 ">
                                            <div class="__timing__box__ __Days " style="visibility: visible;">00</div>
                                            <p class="text-center mt-2 ">Days</p>
                                        </div>
                                        <div class="d-flex justify-content-center flex-column align-items-center ">
                                            <div class="__timing__box__ __Hours" style="visibility: visible;">02</div>
                                            <p class="text-center mt-2 ">Hours</p>
                                        </div>
                                        <div class="d-flex justify-content-center flex-column align-items-center ">
                                            <div class="__timing__box__ __Minutes " style="visibility: visible;">04</div>
                                            <p class="text-center mt-2 ">Mins</p>
                                        </div>
                                        <div class="d-flex justify-content-center flex-column align-items-center ">
                                            <div class="__timing__box__ __Seconds" style="visibility: visible;">08</div>
                                            <p class="text-center mt-2 ">Secs</p>
                                        </div>-->
                                    </div>
                                </div>
                                <div class="user-dynamic-section mt-3 mb-3"></div>
                            </div>

                        </div>
                        <!-- Universal wrapper for user-related elements -->



                        <!--<section class="position-relative  d-flex h-100  d-md-flex align-items-center ">
                            <div class="me-2 __header__login__btn bg-white d-none d-sm-flex align-items-center justify-content-center" style="width: 44px; height: 44px; flex-shrink: 0; border-radius: 50%; border: 2px solid var(--border-active); cursor: pointer;">
                                <svg stroke="currentColor" fill="none" stroke-width="2" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round" color="black" class=" " style="color: black;" height="16" width="16" xmlns="http://www.w3.org/2000/svg">
                                    <circle cx="11" cy="11" r="8"></circle>
                                    <line x1="21" y1="21" x2="16.65" y2="16.65"></line>
                                </svg>
                            </div>
                            <button type="button" style="height: 44px; min-width: max-content; width: 100%;"
                                class="btn_primary_gradient position-relative px-3 font-semibold d-flex justify-content-center align-items-center __header__btn __header__login__btn d-none d-sm-flex me-2 outline-dark-btn btn btn-primary"
                                data-bs-toggle="modal" data-bs-target="#registerModal">
                                <div class="me-2" style="color: inherit;">
                                    <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" class="me-1 d-flex d-md-none" height="18" width="18" xmlns="http://www.w3.org/2000/svg">
                                        <path d="M256 256c52.805 0 96-43.201 96-96s-43.195-96-96-96-96 43.201-96 96 43.195 96 96 96zm0 48c-63.598 0-192 32.402-192 96v48h384v-48c0-63.598-128.402-96-192-96z"></path>
                                    </svg>
                                </div>Bid Auction
                            </button>
                        </section>-->

                    </div>
                </div>
            </div>
        </div>
        <div style="position:sticky;bottom:0;z-index:99999" class="  d-none   d-md-none  container">
            <div style="background:#f5f5f5" class="pt-2 px-1 dashboard mx-1">
                <div class="d-flex gap-2 justify-content-between mx-1 ">
                    <div class=" w-20">
                        <p class="text-100 font-semibold">Current Price</p>
                        <div class="d-flex gap-1 align-items-center">
                            <h3 style="color:var(--color-high-bid)" class="font-bold auction__current_price__section">0</h3><span class="text-100 font-semibold">USD</span>
                        </div>
                    </div>
                    <div class="gap-2 ps-auto w-60 d-flex justify-content-end  d-md-none auction__count_down__section_for_small_devices __timing__  mx-auto justify-content-lg-between px-0 text-center false">
                        <!--<div class="d-flex px-0 justify-content-center flex-column align-items-center ps-md-0 ">
                            <div class="__timing__box__ __Days " style="visibility: visible;">00</div>
                            <p class="text-center mt-2 ">Days</p>
                        </div>
                        <div class="d-flex justify-content-center flex-column align-items-center">
                            <div class="__timing__box__ __Hours" style="visibility: visible;">02</div>
                            <p class="text-center mt-2 ">Hours</p>
                        </div>
                        <div class="d-flex justify-content-center flex-column align-items-center ">
                            <div class="__timing__box__ __Minutes " style="visibility: visible;">04</div>
                            <p class="text-center mt-2 ">Mins</p>
                        </div>
                        <div class="d-flex justify-content-center flex-column align-items-center ">
                            <div class="__timing__box__ __Seconds" style="visibility: visible;">08</div>
                            <p class="text-center mt-2 ">Secs</p>
                        </div>-->
                    </div>
                </div>
            </div>
        </div>
        <div class="customContainer px-3 container-fluid">
            <div class="row">
                <div class="col-md-6 col-12">
                    <div class="mt-2 d-flex flex-column w-100 px-md-4 py-md-2 p-1">
                        <h2 class="text-light-black text-sm mb-md-4 section_heading" style="background:#f1f1f1;font-weight:600;width:100%">Description <span style="font-size:21px">(appears to contain):</span></h2>
                        <div class="h-100 ms-1 ms-md-5 ">
                            <p class="title__detail__auction auction__descriptions__section"> </p>
                        </div>

                    </div>
                </div>

                <div class="col-md-6 col-12">
                    <div class="mt-2 d-flex flex-column w-100 px-md-4 py-md-2 p-1">

                        <div class="">
                            <h2 class="text-light-black text-sm mb-md-4 section_heading " style="background:#f1f1f1;font-weight:600;width:100%">Tags</h2>
                            <div sx="12" class="mt-2 ms-1 ms-md-5 col ">
                                <div class="d-flex gap-2 flex-wrap justify-content-start auction__tags__section">
                                    
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
        <div class="customContainer px-3 container-fluid">
            <div class="px-2 px-md-4  row">
                <div style="background-color:#ffefd9;border-radius:32px" class="mx-auto mt-md-2 col-md-12 col-12">
                    <h2 class="text-light-black text-sm mb-md-4 px-5 pt-4 " style="font-weight:600;border-radius:30px;width:100%">Notice to Buyers</h2>
                    <div>
                        <div class="px-1 px-md-5 py-2 py-md-4 bidder__notice">
                            <!-- Dynamically loaded via get_auction_by_id.js -->
                        </div>
                    </div>
                </div>

            </div>
        </div>
        <div class="customContainer px-3 mt-4 container-fluid">
            <div class="mt-5">
                <div class="d-none d-md-block">
                    <div class="row"></div>
                </div>
                <div class="d-md-none d-block"></div>
            </div>
        </div>

    </main>
</div>