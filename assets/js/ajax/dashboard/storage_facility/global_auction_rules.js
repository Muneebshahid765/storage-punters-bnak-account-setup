$(document).ready(function () {
    fetchGlobalAuctionRules();

    // Slider value update
    $('#starting_price_percentage').on('input', function () {
        $('#starting_price_value').text($(this).val() + '%');
    });

    $('#global-auction-rules-form').submit(function (e) {
        e.preventDefault();
        saveGlobalAuctionRules();
    });
});

function fetchGlobalAuctionRules() {
    $.ajax({
        url: SITE_URL + 'api/companies/storage-facility/get_global_auction_rules',
        type: 'GET',
        dataType: 'json',
        success: function (response) {
            if (response.status === 'success' && response.data) {
                const rules = response.data;

                // Set toggles
                $('#estimate_best_photo').prop('checked', rules.estimate_best_photo == 1);
                $('#display_debt_value').prop('checked', rules.display_debt_value == 1);
                $('#start_immediately').prop('checked', rules.start_immediately == 1);
                $('#has_no_deposit').prop('checked', rules.has_no_deposit == 1);

                // Set Payment Method Radios (Single Select)
                if (rules.accept_card == 1) {
                    $('#payment_card').prop('checked', true);
                } else {
                    $('#payment_cash').prop('checked', true); // Default to Cash if card not set or cash set
                }

                // Set Slider
                $('#starting_price_percentage').val(rules.starting_price_percentage);
                $('#starting_price_value').text(rules.starting_price_percentage + '%');

                // Set inputs
                $('#default_auction_duration').val(rules.default_auction_duration);
                $('#cleaning_deposit_amount').val(rules.cleaning_deposit_amount);
                $('#default_terms_conditions').val(rules.default_terms_conditions);
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {
            console.error("Error fetching rules:", textStatus, errorThrown);
        }
    });
}

function saveGlobalAuctionRules() {
    const formData = {
        estimate_best_photo: $('#estimate_best_photo').is(':checked'),
        starting_price_percentage: $('#starting_price_percentage').val(),
        display_debt_value: $('#display_debt_value').is(':checked'),
        default_auction_duration: $('#default_auction_duration').val(),
        start_immediately: $('#start_immediately').is(':checked'),
        cleaning_deposit_amount: $('#cleaning_deposit_amount').val(),
        has_no_deposit: $('#has_no_deposit').is(':checked'),
        accept_cash: $('#payment_cash').is(':checked'),
        accept_card: $('#payment_card').is(':checked'),
        default_terms_conditions: $('#default_terms_conditions').val()
    };

    const $btn = $('#save-global-rules-btn');
    const originalText = $btn.text();
    $btn.prop('disabled', true).text('Saving...');

    $.ajax({
        url: SITE_URL + 'api/companies/storage-facility/save_global_auction_rules',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        success: function (response) {
            $btn.prop('disabled', false).text(originalText);
            if (response.status === 'success') {
                showNotification(response.message, 'success');
            } else {
                showNotification(response.message, 'error');
            }
        },
        error: function (jqXHR) {
            $btn.prop('disabled', false).text(originalText);
            let msg = "Failed to save settings.";
            if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                msg = jqXHR.responseJSON.message;
            }
            showNotification(msg, 'error');
        }
    });
}
