(function ($) {
    var days = 24 * 60 * 60, hours = 60 * 60, minutes = 60;

    $.fn.countdown = function (prop) {
        var options = $.extend({ callback: function () { }, timestamp: 0 }, prop);
        var left, d, h, m, s, positions;
        init(this, options);
        positions = this.find('.position');

        (function tick() {
            // 🔹 Get "now" in Brisbane timezone
            var now = new Date(
                new Date().toLocaleString("en-US", { timeZone: "Australia/Brisbane" })
            );

            // Time left in seconds
            left = Math.floor((options.timestamp - now) / 1000);
            if (left < 0) { left = 0; }

            d = Math.floor(left / days); updateDuo(0, 1, d); left -= d * days;
            h = Math.floor(left / hours); updateDuo(2, 3, h); left -= h * hours;
            m = Math.floor(left / minutes); updateDuo(4, 5, m); left -= m * minutes;
            s = left; updateDuo(6, 7, s);

            options.callback(d, h, m, s);

            setTimeout(tick, 1000);
        })();

        function updateDuo(minor, major, value) {
            switchDigit(positions.eq(minor), Math.floor(value / 10) % 10);
            switchDigit(positions.eq(major), value % 10);
        }

        return this;
    };

    function init(elem, options) {
        elem.addClass('countdownHolder');
        $.each(['Days', 'Hours', 'Minutes', 'Seconds'], function (i) {
            $('<span class="count' + this + '">').html(
                '<span class="position"><span class="digit static">0</span></span>' +
                '<span class="position"><span class="digit static">0</span></span>'
            ).appendTo(elem);
            if (this != "Seconds") {
                elem.append('<span class="countDiv countDiv' + i + '"></span>');
            }
        });
    }

    function switchDigit(position, number) {
        var digit = position.find('.digit');
        if (digit.is(':animated')) return false;
        if (position.data('digit') == number) return false;

        position.data('digit', number);

        var replacement = $('<span>', {
            'class': 'digit',
            css: { top: '-2.1em', opacity: 0 },
            html: number
        });

        digit.before(replacement)
            .removeClass('static')
            .animate({ top: '2.5em', opacity: 0 }, 'fast', function () { digit.remove(); });

        replacement.delay(100)
            .animate({ top: 0, opacity: 1 }, 'fast', function () { replacement.addClass('static'); });
    }
})(jQuery);

$(document).ready(function () {
    var currentUrl = window.location.href;

    // ✅ Show only if exact URL matches
    if (currentUrl === "https://storagepunters.com/") {
        $("#title").css("display", "block");
    } else {
        $("#title").css("display", "none");
    }
});

// Initialization
$(function () {


    var note = $('#note');

    // Set launch date/time in Brisbane (ISO 8601 with timezone offset)
    var targetDateStr = "2025-09-25T08:00:00"; // 25 Sept 2025, 08:00 Brisbane
    var ts = new Date(targetDateStr);

    var newYear = true;

    // Current Brisbane time
    var now = new Date(
        new Date().toLocaleString("en-US", { timeZone: "Australia/Brisbane" })
    );

    if (now > ts) {
        ts = new Date(now.getTime() + 10 * 24 * 60 * 60 * 1000);
        newYear = false;
    }

    $('#countdown').countdown({
        timestamp: ts,
        callback: function (days, hours, minutes, seconds) {
            var message = days + " day" + (days == 1 ? '' : 's') + ", ";
            message += hours + " hour" + (hours == 1 ? '' : 's') + ", ";
            message += minutes + " minute" + (minutes == 1 ? '' : 's') + " and ";
            message += seconds + " second" + (seconds == 1 ? '' : 's') + "<br />";
            if (newYear) {
                message += "left until launch!";
            } else {
                message += "left to 10 days from now!";
            }
            note.html(message);
        }
    });

    // Scroll behavior for fixed header
    $(window).on("scroll", function () {
        if ($(this).scrollTop() > 50) {
            $("#title").addClass("fixed");
        } else {
            $("#title").removeClass("fixed");
        }
    });


});
