$(document).ready(async function () {
    async function fetchAdminCreditSettings() {
        return $.ajax({
            url: base_url + "api/users/admin/setting/credit/get",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            beforeSend: function () {
                $('#credit-loader').removeClass('d-none');
                $('#credit-update-btn').prop('disabled', true);
            }
        }).then(response => {
            if (response.status === "success") {
                $('#credit_value').val(response.data.credit_value);
            } else {
                showNotification(response.message, "error");
            }
        }).catch(jqXHR => {
            let errorMessage = jqXHR.responseJSON?.message || "Something went wrong. Try again.";
            showNotification(errorMessage, "error");
        }).always(() => {
            $('#credit-loader').addClass('d-none');
            $('#credit-update-btn').prop('disabled', false);
        });
    }

    async function fetchAdminCommissionSettings() {
        return $.ajax({
            url: base_url + "api/users/admin/setting/commission/get",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            beforeSend: function () {
                $('#commission-loader').removeClass('d-none');
                $('#commission-update-btn').prop('disabled', true);
            }
        }).then(response => {
            if (response.status === "success") {
                $('#commission_value').val(response.data.commission_value);
            } else {
                showNotification(response.message, "error");
            }
        }).catch(jqXHR => {
            let errorMessage = jqXHR.responseJSON?.message || "Something went wrong. Try again.";
            showNotification(errorMessage, "error");
        }).always(() => {
            $('#commission-loader').addClass('d-none');
            $('#commission-update-btn').prop('disabled', false);
        });
    }

    async function fetchDashboardStats() {
        return $.ajax({
            url: base_url + "api/admin/dashboard/stats",
            type: "GET",
            dataType: "json"
        }).then(res => {
            if (res.success) {
                $('#stat-facilities').text(res.stats.total_facilities.toLocaleString());
                $('#stat-bidders').text(res.stats.total_bidders.toLocaleString());
                $('#stat-auctions').text(res.stats.active_auctions.toLocaleString());
                $('#stat-revenue').text('$' + parseFloat(res.stats.revenue).toLocaleString(undefined, { minimumFractionDigits: 2, maximumFractionDigits: 2 }));
            }
        }).catch(err => {
            console.error('Error fetching dashboard stats:', err);
        });
    }

    // 🚀 Parallel async calls on load for better performance and resilience
    Promise.allSettled([
        fetchDashboardStats(),
        fetchAdminCreditSettings(),
        fetchAdminCommissionSettings()
    ]).then(results => {
        results.forEach((result, index) => {
            if (result.status === "rejected") {
                console.error(`Fetch call ${index} failed:`, result.reason);
            }
        });
    });

    // ================= Credit Form =================
    $('#credit-setting-form').submit(function (event) {
        event.preventDefault();
        let formData = { credit_value: $('#credit_value').val() };

        $.ajax({
            url: base_url + 'api/users/admin/setting/credit/update',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            dataType: 'json',
            beforeSend: function () {
                $('#credit-loader').removeClass('d-none');
                $('#credit-update-btn').prop('disabled', true);
            },
            success: function (response) {
                if (response.status === "success") {
                    showNotification(response.message, "success");
                } else {
                    showNotification(response.message, "error");
                }
            },
            error: function (jqXHR) {
                let errorMessage = jqXHR.responseJSON?.message || "Something went wrong. Try again.";
                showNotification(errorMessage, "error");
            },
            complete: function () {
                $('#credit-loader').addClass('d-none');
                $('#credit-update-btn').prop('disabled', false);
            }
        });
    });

    // ================= Commission Form =================
    $('#commission-setting-form').submit(function (event) {
        event.preventDefault();
        let formData = { commission_value: $('#commission_value').val() };

        $.ajax({
            url: base_url + 'api/users/admin/setting/commission/update',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify(formData),
            dataType: 'json',
            beforeSend: function () {
                $('#commission-loader').removeClass('d-none');
                $('#commission-update-btn').prop('disabled', true);
            },
            success: function (response) {
                if (response.status === "success") {
                    showNotification(response.message, "success");
                } else {
                    showNotification(response.message, "error");
                }
            },
            error: function (jqXHR) {
                let errorMessage = jqXHR.responseJSON?.message || "Something went wrong. Try again.";
                showNotification(errorMessage, "error");
            },
            complete: function () {
                $('#commission-loader').addClass('d-none');
                $('#commission-update-btn').prop('disabled', false);
            }
        });
    });
});
