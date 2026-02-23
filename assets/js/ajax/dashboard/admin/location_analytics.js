$(document).ready(function () {
    loadAnalytics();

    $('#analytics-filters').on('submit', function (e) {
        e.preventDefault();
        loadAnalytics();
    });

    function loadAnalytics() {
        const formData = $('#analytics-filters').serialize();
        $.ajax({
            url: base_url + 'api/admin/location-analytics/data',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    updateKPIs(res.summary);
                    renderAnalyticsTable(res.analytics);
                } else {
                    console.error('API Error:', res.message);
                }
            },
            error: function (xhr) {
                console.error('Error loading analytics:', xhr.responseText);
            }
        });
    }

    function updateKPIs(summary) {
        $('#kpi-auctions').text(summary.total_auctions.toLocaleString());
        $('#kpi-avg-bid').text('$' + parseFloat(summary.avg_winning_bid).toFixed(2));
        $('#kpi-signups').text(summary.total_signups.toLocaleString());
        $('#kpi-concentration').html(`${summary.highest_concentration.state} <span class="fs-6 d-block text-muted">(${summary.highest_concentration.percentage}%)</span>`);
    }

    function renderAnalyticsTable(data) {
        let html = '';
        if (data.length === 0) {
            html = '<tr><td colspan="5" class="text-center text-muted py-4">No data found for the selected filters</td></tr>';
        } else {
            data.forEach(row => {
                html += `
                    <tr>
                        <td class="fw-bold">${row.city}</td>
                        <td>${row.state}</td>
                        <td>${row.total_auctions}</td>
                        <td>${parseFloat(row.total_signups / 1000).toFixed(1)}K</td>
                        <td class="fw-bold">$${Math.round(row.avg_winning_bid)}</td>
                    </tr>
                `;
            });
        }
        $('#city-performance-list').html(html);
    }
});
