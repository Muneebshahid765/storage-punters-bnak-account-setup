$(document).ready(function () {
    // Hamburger menu toggle
    $(document).on('click', '.hamburger-menu-btn', function (e) {
        e.stopPropagation();
        $('.sidebar-modern').addClass('mobile-open');
        $('.sidebar-overlay').addClass('active');
        $('body').css('overflow', 'hidden'); // Prevent background scrolling

        // Ensure overlay exists if not present
        if ($('.sidebar-overlay').length === 0) {
            $('<div class="sidebar-overlay"></div>').insertAfter('.sidebar-modern');
            setTimeout(function () {
                $('.sidebar-overlay').addClass('active');
            }, 10);
        }
    });

    // Close button & Overlay click
    $(document).on('click', '.close-sidebar-btn, .sidebar-overlay', function () {
        $('.sidebar-modern').removeClass('mobile-open');
        $('.sidebar-overlay').removeClass('active');
        $('body').css('overflow', '');
    });

    // Handle resize
    $(window).resize(function () {
        if ($(window).width() > 1024) {
            $('.sidebar-modern').removeClass('mobile-open');
            $('.sidebar-overlay').removeClass('active');
            $('body').css('overflow', '');
        }
    });
});
