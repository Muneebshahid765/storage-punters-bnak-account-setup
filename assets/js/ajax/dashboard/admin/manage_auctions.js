$(document).ready(function () {
    // Initialize DataTable
    const auctionsTable = $('#auctions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: base_url + 'api/admin/auctions/list',
            type: 'POST',
            data: function (d) {
                d.search = $('#auction-search').val();
                d.status_filter = $('#status-filter').val();
            }
        },
        columns: [
            { data: 'auction_id' },
            { data: 'unit_number' },
            { data: 'facility_name' },
            {
                data: null,
                render: function (data) {
                    return (data.city || '') + ', ' + (data.state || '');
                }
            },
            {
                data: 'outstanding_dept',
                render: function (data) {
                    return '$' + parseFloat(data).toFixed(2);
                }
            },
            {
                data: 'starting_price',
                render: function (data) {
                    return '$' + parseFloat(data).toFixed(2);
                }
            },
            {
                data: 'current_price',
                render: function (data) {
                    return '$' + parseFloat(data).toFixed(2);
                }
            },
            { data: 'start_date' },
            { data: 'end_date' },
            {
                data: 'status_name',
                render: function (data, type, row) {
                    let badgeClass = 'bg-secondary';
                    if (row.status_id == 1) badgeClass = 'bg-success';
                    else if (row.status_id == 2) badgeClass = 'bg-primary';
                    else if (row.status_id == 10) badgeClass = 'bg-danger';

                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            {
                data: 'is_staged',
                render: function (data) {
                    return data == 1 ? '<span class="badge bg-warning text-dark">Staged</span>' : '<span class="badge bg-info">Live</span>';
                }
            },
            {
                data: null,
                orderable: false,
                render: function (data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-primary edit-auction" 
                                data-id="${row.auction_id}"
                                data-debt="${row.outstanding_dept}"
                                data-price="${row.starting_price}"
                                data-staged="${row.is_staged}"
                                data-status="${row.status_id}">Edit</button>
                            <button class="btn btn-danger cancel-auction" data-id="${row.auction_id}">Cancel</button>
                        </div>
                    `;
                }
            }
        ],
        pageLength: 10,
        order: [[0, 'desc']]
    });

    // Search functionality
    $('#auction-search').on('keyup', function () {
        auctionsTable.ajax.reload();
    });

    // Status filter
    $('#status-filter').on('change', function () {
        auctionsTable.ajax.reload();
    });

    // Edit Auction
    $(document).on('click', '.edit-auction', function () {
        const id = $(this).data('id');
        const debt = $(this).data('debt');
        const price = $(this).data('price');
        const status = $(this).data('status');

        $('#edit-auction-id').val(id);
        $('#edit-outstanding-debt').val(debt);
        $('#edit-starting-price').val(price);
        $('#edit-is-staged').prop('checked', $(this).data('staged') == 1);
        $('#edit-status-id').val(status);

        $('#editAuctionModal').modal('show');
    });

    // Save Auction
    $('#save-auction-btn').on('click', function () {
        const formData = $('#edit-auction-form').serialize();

        $.ajax({
            url: base_url + 'api/admin/auctions/update',
            type: 'POST',
            data: formData,
            success: function (response) {
                const result = JSON.parse(response);
                if (result.success) {
                    alert('Auction updated successfully');
                    $('#editAuctionModal').modal('hide');
                    auctionsTable.ajax.reload();
                } else {
                    alert('Error updating auction');
                }
            }
        });
    });

    // Cancel Auction
    $(document).on('click', '.cancel-auction', function () {
        const auctionId = $(this).data('id');

        if (confirm('Are you sure you want to cancel this auction? This action cannot be undone.')) {
            $.ajax({
                url: base_url + 'api/admin/auctions/cancel',
                type: 'POST',
                data: { auction_id: auctionId },
                success: function (response) {
                    const result = JSON.parse(response);
                    if (result.success) {
                        alert('Auction cancelled successfully');
                        auctionsTable.ajax.reload();
                    } else {
                        alert('Error cancelling auction');
                    }
                }
            });
        }
    });
});
