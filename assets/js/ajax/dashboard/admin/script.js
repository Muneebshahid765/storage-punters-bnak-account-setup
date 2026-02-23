$(document).ready(function () {


    $(".profile-btn").click(function (event) {
        // If the click is inside the menu, let the individual handlers work and allow bubbling
        if ($(event.target).closest('.profile_menu').length > 0) {
            return;
        }

        event.stopPropagation(); // Prevent event bubbling only when toggling the menu

        // Toggle menu
        if ($(".profile_menu").length === 0) {
            $(this).append(`
                <div class="mt-5 profile_menu d-flex flex-column justify-content-start gap-2 p-lg-2"
                    style="position: absolute; right: 20px; background: white; box-shadow: 0px 5px 10px rgba(0,0,0,0.2);
                    border-radius: 5px; padding: 10px; z-index: 1000;">
                    <li class="list-unstyled cursor-pointer active__dropdown btn__dashboard px-1 px-md-2">Dashboard</li>
                    <li class="list-unstyled cursor-pointer active__dropdown btn__account px-1 px-md-2">Edit Account</li>

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
                window.location.href = SITE_URL + "my-dashboard/dashboard";
            });

            $(".btn__account").click(function () {
                window.location.href = SITE_URL + "my-dashboard/edit-account";
            });



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
        window.location.href = SITE_URL + "my-dashboard/dashboard";
    });

    $(".btn__edit__account").click(function () {
        window.location.href = SITE_URL + "my-dashboard/edit-account";
    });

    // Sidebar Toggle for Mobile
    $('#sidebarToggle').on('click', function () {
        $('.sidebar-modern').addClass('mobile-open');
        $('#sidebarOverlay').addClass('active');
    });

    $('#sidebarOverlay').on('click', function () {
        $('.sidebar-modern').removeClass('mobile-open');
        $(this).removeClass('active');
    });
});