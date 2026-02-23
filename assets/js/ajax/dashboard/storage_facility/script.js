$(document).ready(function () {


    $(".profile-btn").click(function (event) {
        // If the click is inside the menu, let the individual handlers work and allow bubbling
        if ($(event.target).closest('.profile_menu').length > 0) {
            return;
        }

        event.stopPropagation(); // Prevent event bubbling only when toggling the menu

        // Toggle menu
        if ($(this).find(".profile_menu").length === 0) {
            // Remove existing menus from other buttons if any
            $(".profile_menu").remove();

            $(this).css("position", "relative");
            $(this).append(`
                <div class="mt-5 profile_menu d-flex flex-column justify-content-start gap-2 p-lg-2"
                    style="position: absolute; right: 0; top: 100%; background: white; box-shadow: 0px 5px 10px rgba(0,0,0,0.2);
                    border-radius: 5px; padding: 10px; z-index: 1000; min-width: 180px;">

                    <li class="list-unstyled cursor-pointer active__dropdown btn__dashboard px-1 px-md-2">Dashboard</li>
                    <li class="list-unstyled cursor-pointer active__dropdown btn__account px-1 px-md-2">Edit Account</li>


                     <li class="list-unstyled cursor-pointer active__dropdown btn__list__new__auction px-1 fw-bold d-block d-lg-none d-flex align-items-center gap-2">
                        <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-plus-circle" viewBox="0 0 16 16">
                            <path d="M8 15A7 7 0 1 0 8 1a7 7 0 0 0 0 14zM0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8z"/>
                            <path d="M8 4a.5.5 0 0 1 .5.5v3h3a.5.5 0 0 1 0 1h-3v3a.5.5 0 0 1-1 0v-3h-3a.5.5 0 0 1 0-1h3v-3A.5.5 0 0 1 8 4z"/>
                        </svg>
                        List a New Auction
                    </li>


                    <hr class="my-2">

                   <li class="list-unstyled cursor-pointer active__dropdown btn__logout px-1 px-md-2 fw-bold d-flex align-items-center gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" viewBox="0 0 16 16">
                        <path fill-rule="evenodd" d="M6 13a.5.5 0 0 0 0 1h5.5A1.5 1.5 0 0 0 13 12.5v-9A1.5 1.5 0 0 0 11.5 2H6a.5.5 0 0 0 0 1h5.5a.5.5 0 0 1 .5.5v9a.5.5 0 0 1-.5.5H6z"/>
                        <path fill-rule="evenodd" d="M.146 8.354a.5.5 0 0 1 0-.708L3.5 4.293a.5.5 0 1 1 .708.708L1.707 8l2.5 2.5a.5.5 0 0 1-.708.708L.146 8.354z"/>
                        <path fill-rule="evenodd" d="M3 8a.5.5 0 0 1 .5-.5h7a.5.5 0 0 1 0 1h-7A.5.5 0 0 1 3 8z"/>
                    </svg>
                    <span>Log Out</span>
                </li>


                </div>
            `);


            // Change icon to UP when menu is opened
            $(".dropdown-icon").html(`
                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                    <path d="M256 217.9L383 345c9.4 9.4 24.6 9.4 33.9 0 9.4-9.4 9.3-24.6 0-34L273 167c-9.1-9.1-23.7-9.3-33.1-.7L95 310.9c-4.7 4.7-7 10.9-7 17s2.3 12.3 7 17c9.4 9.4 24.6 9.4 33.9 0l127.1-127z"></path>
                </svg>
            `);

            $(".btn__dashboard").click(function () {
                window.location.href = SITE_URL + "storage-facility/dashboard";
            });

            $(".btn__account").click(function () {
                window.location.href = SITE_URL + "storage-facility/edit-account";
            });

            $(".btn__list__new__auction").click(function () {
                window.location.href = SITE_URL + "storage-facility/ai/new";
            });

            $(".btn__setup__company").click(function () {
                window.location.href = SITE_URL + "company";
            });

            $(".btn__setup__bank__account").click(function () {
                window.location.href = SITE_URL + "bank";
            });

            $(".btn__manage__staff").click(function () {
                window.location.href = SITE_URL + "storage-facility/staff/manage";
            });

            $(".btn__manage__facility_storage").click(function () {
                window.location.href = SITE_URL + "storage-facility/manage";
            });



            /*
            $(document).on("click", ".edit-storage-facility-btn", function () {
                let storageFacilityId = $(this).data("storage-facility-id");
                window.open(SITE_URL + "storage-facility/edit/" + storageFacilityId, "_blank"); // Open in a new tab
            });
            */



        } else {
            $(".profile_menu").remove();

            // Change icon to DOWN when menu is closed
            $(".dropdown-icon").html(`
                <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 512 512" height="1em" width="1em" xmlns="http://www.w3.org/2000/svg">
                    <path d="M256 294.1L383 167c9.4-9.4 24.6-9.4 33.9 0s9.3 24.6 0 34L273 345c-9.1 9.1-23.7 9.3-33.1.7L95 201.1c-4.7-4.7-7-10.9-7-17s2.3-12.3 7-17c9.4-9.4 24.6-9.4 33.9 0l127.1 127z"></path>
                </svg>
            `);
        }
    });

    $(".btn__dashboard").click(function () {
        window.location.href = SITE_URL + "storage-facility/dashboard";
    });

    $(".btn__edit__account").click(function () {
        window.location.href = SITE_URL + "storage-facility/edit-account";
    });

    $(".btn__list__new__auction").click(function () {
        window.location.href = SITE_URL + "storage-facility/ai/new";
    });

    $(".btn__setup__company").click(function () {
        window.location.href = SITE_URL + "company";
    });

    $(".btn__setup__bank__account").click(function () {
        window.location.href = SITE_URL + "bank";
    });

    $(".btn__manage__staff").click(function () {
        window.location.href = SITE_URL + "storage-facility/staff/manage";
    });

    $(".btn__manage__facility_storage").click(function () {
        window.location.href = SITE_URL + "storage-facility/manage";
    });




});