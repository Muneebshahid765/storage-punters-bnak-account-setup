$(document).ready(function () {
    const $form = $('#notification-preferences-form');
    const $saveBtn = $('#save-notifications-btn');

    // Load initial settings
    loadSettings();

    function loadSettings() {
        $.ajax({
            url: base_url + 'storage-facility/notifications/get-preferences', // Will update route
            // Or use the controller method path if not remapped yet, but let's assume I'll map it
            // Actually, let's use the explicit path for now and update routes later
            url: base_url + 'api/companies/storage-facility/get_notification_preferences',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success' && response.data) {
                    const data = response.data;

                    // Audio Alert
                    $('#audio_alert_enabled').val(data.audio_alert_enabled);

                    // Winning Auction Email
                    $(`input[name="winning_auction_email"][value="${data.winning_auction_email}"]`).prop('checked', true);
                    $(`input[name="winning_auction_sms"][value="${data.winning_auction_sms}"]`).prop('checked', true);

                    // Outbid Auction
                    $(`input[name="outbid_auction_email"][value="${data.outbid_auction_email}"]`).prop('checked', true);
                    $(`input[name="outbid_auction_sms"][value="${data.outbid_auction_sms}"]`).prop('checked', true);

                    // Auction Closed
                    $(`input[name="auction_closed_email"][value="${data.auction_closed_email}"]`).prop('checked', true);
                    $(`input[name="auction_closed_sms"][value="${data.auction_closed_sms}"]`).prop('checked', true);
                }
            },
            error: function (xhr, status, error) {
                console.error("Error loading notification settings:", error);
            }
        });
    }

    $form.on('submit', function (e) {
        e.preventDefault();

        // Disable button and show loading state
        const originalBtnText = $saveBtn.text();
        $saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Saving...');

        // Collect data
        const formData = {
            audio_alert_enabled: $('#audio_alert_enabled').val() == '1',
            winning_auction_email: $('input[name="winning_auction_email"]:checked').val() == '1',
            winning_auction_sms: $('input[name="winning_auction_sms"]:checked').val() == '1',
            outbid_auction_email: $('input[name="outbid_auction_email"]:checked').val() == '1',
            outbid_auction_sms: $('input[name="outbid_auction_sms"]:checked').val() == '1',
            auction_closed_email: $('input[name="auction_closed_email"]:checked').val() == '1',
            auction_closed_sms: $('input[name="auction_closed_sms"]:checked').val() == '1'
        };

        $.ajax({
            url: base_url + 'api/companies/storage-facility/save_notification_preferences',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            success: function (response) {
                if (response.status === 'success') {
                    // Show success message (using SweetAlert or simple toast if available, falling back to alert for now or simple UI feedback)
                    // Assuming Swal is available as in other parts of the app
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'success',
                            title: 'Saved!',
                            text: 'Notification preferences have been updated.',
                            timer: 2000,
                            showConfirmButton: false
                        });
                    } else {
                        alert('Settings saved successfully!');
                    }
                } else {
                    if (typeof Swal !== 'undefined') {
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: response.message || 'Failed to save settings.'
                        });
                    } else {
                        alert('Error: ' + response.message);
                    }
                }
            },
            error: function (xhr, status, error) {
                if (typeof Swal !== 'undefined') {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'An unexpected error occurred.'
                    });
                } else {
                    alert('An unexpected error occurred.');
                }
            },
            complete: function () {
                $saveBtn.prop('disabled', false).text(originalBtnText);
            }
        });
    });
});
