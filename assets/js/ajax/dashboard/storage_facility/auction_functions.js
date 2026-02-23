function loadAllAuctionDataSequentially() {
    return getAllAuctionTypes("specific")
        .then(function (typeResponse) {
            if (typeResponse.status === "success") {
                appendAuctionTypes(typeResponse.data);
            }
            return getAllAuctionStorageFacilities();
        })
        .then(function (facilitiesResponse) {
            if (facilitiesResponse.status === "success") {
                appendAuctionFacilities(facilitiesResponse.data);
            }

            if ($("#auctionCategoryDropdown").length) {
                return getAllAuctionCategory().then(function (categoryResponse) {
                    if (categoryResponse.status === "success") {
                        appendAuctionCategories(categoryResponse.data);
                    }
                });
            }

            // 🔁 Return a resolved promise if no category dropdown
            return Promise.resolve();
        })
        .catch(function (error) {
            console.error("Error loading auction data:", error);
            showNotification("Failed to load auction data", "error");
        });
}


$('#auction_start_date').on('change', function () {
    filterFutureTimes();
    calculateEndDateTime(); // Recalculate as well
});

// Recalculate on date or time change
$('#auction_start_time').on('change', calculateEndDateTime);
$('#auction_set_days').on('change', calculateEndDateTime);

function filterFutureTimes2() {
    const startDate = $('#auction_start_date').val();
    const timeSelect = $('#auction_start_time');
    const endDate = $('#auction_end_date');
    const timeEndSelect = $('#auction_end_time');

    if (endDate.val()) {
        endDate.val('');
    }

    timeSelect.empty().append('<option value="">Select time</option>');
    timeEndSelect.empty().append('<option value="">Select time</option>');

    if (!startDate) return;

    // Brisbane time (UTC+10, no DST)
    const now = new Date();
    const brisbaneOffset = 10 * 60; // in minutes
    const utc = now.getTime() + now.getTimezoneOffset() * 60000;
    const brisbaneNow = new Date(utc + brisbaneOffset * 60000);

    const todayStr = `${brisbaneNow.getFullYear()}-${(brisbaneNow.getMonth() + 1).toString().padStart(2, '0')}-${brisbaneNow.getDate().toString().padStart(2, '0')}`;

    const currentHour = brisbaneNow.getHours(); // 0 - 23

    const timeOptions = [
        "12:00 AM", "01:00 AM", "02:00 AM", "03:00 AM", "04:00 AM", "05:00 AM",
        "06:00 AM", "07:00 AM", "08:00 AM", "09:00 AM", "10:00 AM", "11:00 AM",
        "12:00 PM", "01:00 PM", "02:00 PM", "03:00 PM", "04:00 PM", "05:00 PM",
        "06:00 PM", "07:00 PM", "08:00 PM", "09:00 PM", "10:00 PM", "11:00 PM"
    ];

    timeOptions.forEach(timeStr => {
        const [timePart, meridian] = timeStr.split(' ');
        let [hour] = timePart.split(':').map(Number);
        if (meridian === 'PM' && hour < 12) hour += 12;
        if (meridian === 'AM' && hour === 12) hour = 0;

        // Only add if future time
        if (startDate !== todayStr || hour > currentHour) {
            timeSelect.append($('<option>', {
                value: timeStr,
                text: timeStr
            }));
        }
    });
}

function filterFutureTimes() {
    const startDate = $('#auction_start_date').val();
    const timeSelect = $('#auction_start_time');
    const endDate = $('#auction_end_date');
    const timeEndSelect = $('#auction_end_time');

    if (endDate.val()) {
        endDate.val('');
    }

    timeSelect.empty().append('<option value="">Select time</option>');
    timeEndSelect.empty().append('<option value="">Select time</option>');

    if (!startDate) return;

    // Brisbane time (UTC+10, no DST)
    const now = new Date();
    const brisbaneOffset = 10 * 60; // in minutes
    const utc = now.getTime() + now.getTimezoneOffset() * 60000;
    const brisbaneNow = new Date(utc + brisbaneOffset * 60000);

    const todayStr = `${brisbaneNow.getFullYear()}-${(brisbaneNow.getMonth() + 1)
        .toString().padStart(2, '0')}-${brisbaneNow.getDate().toString().padStart(2, '0')}`;

    const currentHour = brisbaneNow.getHours();
    const currentMinute = brisbaneNow.getMinutes();
    const currentTotalMinutes = currentHour * 60 + currentMinute;

    // Generate 15-min interval time slots for 24 hours
    const timeOptions = [];
    for (let h = 0; h < 24; h++) {
        for (let m = 0; m < 60; m += 5) {
            const totalMinutes = h * 60 + m;
            let hour12 = h % 12 || 12;
            const meridian = h < 12 ? 'AM' : 'PM';
            const timeStr = `${hour12.toString().padStart(2, '0')}:${m.toString().padStart(2, '0')} ${meridian}`;
            timeOptions.push({ totalMinutes, label: timeStr });
        }
    }

    timeOptions.forEach(({ totalMinutes, label }) => {
        // If same day, only show times *after* the current minute
        if (startDate !== todayStr || totalMinutes > currentTotalMinutes) {
            timeSelect.append($('<option>', {
                value: label,
                text: label
            }));
        }
    });
}


function calculateEndDateTime2() {
    const startDate = $('#auction_start_date').val();      // format: YYYY-MM-DD
    const startTime = $('#auction_start_time').val();      // format: "hh:mm AM/PM"
    const setDays = parseInt($('#auction_set_days').val(), 10) || 3; // default 3 days

    if (!startDate || !startTime) return;

    // Split into time and meridian (AM/PM)
    const [timePart, ampm] = startTime.split(' ');
    let [hours, minutes] = timePart.split(':').map(Number);

    // Convert to 24-hour format
    if (ampm === 'PM' && hours < 12) hours += 12;
    if (ampm === 'AM' && hours === 12) hours = 0;

    // Parse start date
    const [year, month, day] = startDate.split('-').map(Number);

    // Construct date object in local time
    const startDateTime = new Date(year, month - 1, day, hours, minutes, 0); // month is 0-indexed
    if (isNaN(startDateTime.getTime())) return;

    // Add 72 hours
    const endDateTime = new Date(startDateTime.getTime() + setDays * 24 * 60 * 60 * 1000);

    // Format end date: YYYY-MM-DD
    const endYear = endDateTime.getFullYear();
    const endMonth = (endDateTime.getMonth() + 1).toString().padStart(2, '0');
    const endDay = endDateTime.getDate().toString().padStart(2, '0');
    const endDate = `${endYear}-${endMonth}-${endDay}`;

    // Format end time as hh:mm AM/PM
    let endHours = endDateTime.getHours();
    const endMinutes = endDateTime.getMinutes().toString().padStart(2, '0');
    const endAMPM = endHours >= 12 ? 'PM' : 'AM';
    endHours = endHours % 12 || 12;
    const formattedEndTime = `${endHours.toString().padStart(2, '0')}:${endMinutes} ${endAMPM}`;

    // Update the end date and time dropdown
    $('#auction_end_date').val(endDate);
    $('#auction_end_time').empty().append(
        $('<option>', {
            value: formattedEndTime,
            text: formattedEndTime,
            selected: true
        })
    );
}

function calculateEndDateTime() {
    const startDate = $('#auction_start_date').val();      // format: YYYY-MM-DD
    const startTime = $('#auction_start_time').val();      // format: "hh:mm AM/PM"
    const selectedOption = $('#auction_set_days option:selected');
    const value = parseInt(selectedOption.val(), 10);
    const type = selectedOption.data('type') || 'day';

    if (!startDate || !startTime) return;

    // Convert start time to 24-hour
    const [timePart, ampm] = startTime.split(' ');
    let [hours, minutes] = timePart.split(':').map(Number);
    if (ampm === 'PM' && hours < 12) hours += 12;
    if (ampm === 'AM' && hours === 12) hours = 0;

    // Parse date
    const [year, month, day] = startDate.split('-').map(Number);
    const startDateTime = new Date(year, month - 1, day, hours, minutes, 0);
    if (isNaN(startDateTime.getTime())) return;

    // Determine how much time to add
    let addMs = 0;
    if (type === 'minute') {
        addMs = value * 60 * 1000;
    } else if (type === 'hour') {
        addMs = value * 60 * 60 * 1000;
    } else if (type === 'day') {
        addMs = value * 24 * 60 * 60 * 1000;
    }

    const endDateTime = new Date(startDateTime.getTime() + addMs);

    // Format end date (YYYY-MM-DD)
    const endYear = endDateTime.getFullYear();
    const endMonth = (endDateTime.getMonth() + 1).toString().padStart(2, '0');
    const endDay = endDateTime.getDate().toString().padStart(2, '0');
    const endDate = `${endYear}-${endMonth}-${endDay}`;

    // Format end time (hh:mm AM/PM)
    let endHours = endDateTime.getHours();
    const endMinutes = endDateTime.getMinutes().toString().padStart(2, '0');
    const endAMPM = endHours >= 12 ? 'PM' : 'AM';
    endHours = endHours % 12 || 12;
    const formattedEndTime = `${endHours.toString().padStart(2, '0')}:${endMinutes} ${endAMPM}`;

    // Update end date & time fields
    $('#auction_end_date').val(endDate);
    $('#auction_end_time').empty().append(
        $('<option>', {
            value: formattedEndTime,
            text: formattedEndTime,
            selected: true
        })
    );
}


// Set today's date as the minimum selectable date
$(document).ready(function () {
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = (today.getMonth() + 1).toString().padStart(2, '0'); // month is 0-based
    const dd = today.getDate().toString().padStart(2, '0');
    const minDate = `${yyyy}-${mm}-${dd}`;

    $('#auction_start_date').attr('min', minDate);
});


function restrictToDecimal(inputSelector) {
    $(document).on('input', inputSelector, function () {
        let value = $(this).val();

        // Remove all characters except digits and dots
        value = value.replace(/[^0-9.]/g, '');

        // Keep only the first dot
        const parts = value.split('.');
        if (parts.length > 1) {
            value = parts[0] + '.' + parts.slice(1).join('');
        }

        $(this).val(value);
    });

    $(document).on('keydown', inputSelector, function (e) {
        const allowedKeys = [
            'Backspace', 'Tab', 'ArrowLeft', 'ArrowRight', 'Delete', 'Home', 'End'
        ];

        if (
            allowedKeys.includes(e.key) ||
            (e.ctrlKey && ['a', 'c', 'v', 'x'].includes(e.key.toLowerCase()))
        ) {
            return;
        }

        const currentValue = $(this).val();

        // Allow digits
        if (/^\d$/.test(e.key)) return;

        // Allow one dot
        if (e.key === '.' && !currentValue.includes('.')) return;

        // Block anything else
        e.preventDefault();
    });

    // Block paste, drop, right-click
    $(document).on('paste drop contextmenu', inputSelector, function (e) {
        e.preventDefault();
    });
}



