$(document).ready(function () {
    loadSignupBonus();

    function loadSignupBonus() {
        $.ajax({
            url: base_url + 'api/admin/signup-bonus/get',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success && res.settings) {
                    $('#bonus-toggle').prop('checked', res.settings.enabled == 1);
                    $('#bonus-amount').val(res.settings.amount);
                    $('#signup-coins').val(res.settings.signup_coins || 100);
                    $('#bonus-expiration').val(res.settings.expiration_days);
                }
            },
            error: function (xhr) {
                console.error('Error loading bonus settings:', xhr.responseText);
            }
        });
    }

    $('#signup-bonus-form').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        // Handle checkbox since serialize doesn't include unchecked
        let dataStr = formData;
        if (!$(this).find('input[name="enabled"]').is(':checked')) {
            dataStr += '&enabled=0';
        }

        $.ajax({
            url: base_url + 'api/admin/signup-bonus/update',
            type: 'POST',
            data: dataStr,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    alert(res.message);
                    loadSignupBonus();
                } else {
                    alert('Error: ' + res.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
            }
        });
    });
});
