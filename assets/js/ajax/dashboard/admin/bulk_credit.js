$(document).ready(function () {
    let loadedBidders = [];

    // Load Bidders based on filters
    $('#load-bidders-btn').on('click', function () {
        const location = $('#location-filter').val();
        const bidFilter = $('#bid-filter').val();
        const winFilter = $('#win-filter').val();

        $.ajax({
            url: base_url + 'api/admin/bulk-credit/bidders',
            type: 'POST',
            data: {
                location: location,
                bid_filter: bidFilter,
                win_filter: winFilter
            },
            dataType: 'json',
            success: function (result) {
                if (result.success) {
                    loadedBidders = result.bidders;
                    renderBidders(result.bidders);
                    $('#bidder-count').text(result.bidders.length);
                } else {
                    alert('Error loading bidders: ' + (result.message || 'Unknown error'));
                }
            },
            error: function (xhr) {
                console.error('Error loading bidders:', xhr.responseText);
            }
        });
    });

    // Render bidders in table
    function renderBidders(bidders) {
        let html = '';

        if (bidders.length === 0) {
            html = `<tr><td colspan="8" class="text-center text-muted py-4">No bidders match the selected filters</td></tr>`;
        } else {
            bidders.forEach(function (bidder) {
                html += `
                    <tr>
                        <td>
                            <input type="checkbox" class="form-check-input bidder-checkbox" 
                                data-id="${bidder.id}" value="${bidder.id}">
                        </td>
                        <td>${bidder.id}</td>
                        <td>${bidder.first_name} ${bidder.last_name}</td>
                        <td>${bidder.email}</td>
                        <td>${bidder.city || 'N/A'}, ${bidder.state || 'N/A'}</td>
                        <td>${bidder.total_bids}</td>
                        <td>${bidder.total_wins}</td>
                        <td>$${parseFloat(bidder.wallet_balance).toFixed(2)}</td>
                    </tr>
                `;
            });
        }

        $('#bidders-list').html(html);
        updateSelectedCount();
    }

    // Select All
    $('#select-all-btn, #select-all-checkbox').on('click', function () {
        $('.bidder-checkbox').prop('checked', true);
        updateSelectedCount();
    });

    // Deselect All
    $('#deselect-all-btn').on('click', function () {
        $('.bidder-checkbox').prop('checked', false);
        $('#select-all-checkbox').prop('checked', false);
        updateSelectedCount();
    });

    // Update selected count
    $(document).on('change', '.bidder-checkbox', function () {
        updateSelectedCount();
    });

    function updateSelectedCount() {
        const count = $('.bidder-checkbox:checked').length;
        $('#selected-count').text(count);
    }

    // Apply Bulk Credit
    $('#bulk-credit-form').on('submit', function (e) {
        e.preventDefault();

        const selectedIds = [];
        $('.bidder-checkbox:checked').each(function () {
            selectedIds.push($(this).val());
        });

        if (selectedIds.length === 0) {
            alert('Please select at least one bidder');
            return;
        }

        const creditAmount = $('#bulk-credit-amount').val();
        const expirationDays = $('#expiration-days').val();
        const note = $('#customer-note').val();

        if (!creditAmount || !expirationDays) {
            alert('Please fill in all required fields');
            return;
        }

        if (confirm(`Apply $${creditAmount} credit to ${selectedIds.length} bidder(s)?`)) {
            $.ajax({
                url: base_url + 'api/admin/bulk-credit/apply',
                type: 'POST',
                data: {
                    bidder_ids: selectedIds,
                    credit_amount: creditAmount,
                    expiration_days: expirationDays,
                    note: note
                },
                dataType: 'json',
                success: function (result) {
                    if (result.success) {
                        alert(result.message);
                        // Reset form
                        $('#bulk-credit-form')[0].reset();
                        // Deselect all
                        $('.bidder-checkbox').prop('checked', false);
                        $('#select-all-checkbox').prop('checked', false);
                        updateSelectedCount();
                        // Reload bidders to show updated balances
                        $('#load-bidders-btn').click();
                    } else {
                        alert('Error: ' + result.message);
                    }
                },
                error: function (xhr) {
                    console.error('Error applying bulk credit:', xhr.responseText);
                    alert('An error occurred while applying credit.');
                }
            });
        }
    });
});
