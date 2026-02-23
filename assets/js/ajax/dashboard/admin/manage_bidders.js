$(document).ready(function () {
    // Initialize DataTable
    const biddersTable = $('#bidders-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: base_url + 'api/admin/bidders/list',
            type: 'POST',
            data: function (d) {
                d.search = $('#bidder-search').val();
            }
        },
        columns: [
            { data: 'id' },
            {
                data: null,
                render: function (data) {
                    return data.first_name + ' ' + data.last_name;
                }
            },
            { data: 'email' },
            { data: 'phone' },
            { data: 'total_bids' },
            { data: 'total_wins' },
            {
                data: 'avg_win_price',
                render: function (data) {
                    return '$' + parseFloat(data).toFixed(2);
                }
            },
            {
                data: 'wallet_balance',
                render: function (data) {
                    return '$' + parseFloat(data).toFixed(2);
                }
            },
            {
                data: 'status_name',
                render: function (data, type, row) {
                    const badgeClass = row.status_id == 1 ? 'bg-success' : 'bg-danger';
                    return `<span class="badge ${badgeClass}">${data}</span>`;
                }
            },
            {
                data: null,
                orderable: false,
                render: function (data, type, row) {
                    const statusAction = row.status_id == 1 ?
                        `<button class="btn btn-sm btn-warning toggle-status" data-id="${row.id}" data-status="2">Block</button>` :
                        `<button class="btn btn-sm btn-success toggle-status" data-id="${row.id}" data-status="1">Activate</button>`;

                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-primary edit-bidder" data-id="${row.id}" 
                                data-firstname="${row.first_name}" data-lastname="${row.last_name}"
                                data-email="${row.email}" data-phone="${row.phone}">Edit</button>
                            <button class="btn btn-success add-credit" data-id="${row.id}">Add Credit</button>
                            ${statusAction}
                        </div>
                    `;
                }
            }
        ],
        pageLength: 10,
        order: [[0, 'desc']]
    });

    // Search functionality
    $('#bidder-search').on('keyup', function () {
        biddersTable.ajax.reload();
    });

    // Edit Bidder
    $(document).on('click', '.edit-bidder', function () {
        const id = $(this).data('id');
        const firstName = $(this).data('firstname');
        const lastName = $(this).data('lastname');
        const email = $(this).data('email');
        const phone = $(this).data('phone');

        $('#edit-user-id').val(id);
        $('#edit-first-name').val(firstName);
        $('#edit-last-name').val(lastName);
        $('#edit-email').val(email);
        $('#edit-phone').val(phone);

        $('#editBidderModal').modal('show');
    });

    // Save Bidder
    $('#save-bidder-btn').on('click', function () {
        const formData = $('#edit-bidder-form').serialize();

        $.ajax({
            url: base_url + 'api/admin/bidders/update',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (result) {
                if (result.success) {
                    alert('Bidder updated successfully');
                    $('#editBidderModal').modal('hide');
                    biddersTable.ajax.reload();
                } else {
                    alert('Error updating bidder: ' + (result.message || 'Unknown error'));
                }
            },
            error: function (xhr) {
                console.error('Error updating bidder:', xhr.responseText);
            }
        });
    });

    // Add Credit
    $(document).on('click', '.add-credit', function () {
        const id = $(this).data('id');
        $('#credit-user-id').val(id);
        $('#credit-amount').val('');
        $('#credit-note').val('');
        $('#addCreditModal').modal('show');
    });

    // Save Credit
    $('#add-credit-btn').on('click', function () {
        const formData = $('#add-credit-form').serialize();

        $.ajax({
            url: base_url + 'api/admin/bidders/add-credit',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (result) {
                if (result.success) {
                    alert('Credit added successfully');
                    $('#addCreditModal').modal('hide');
                    biddersTable.ajax.reload();
                } else {
                    alert('Error adding credit: ' + (result.message || 'Unknown error'));
                }
            },
            error: function (xhr) {
                console.error('Error adding credit:', xhr.responseText);
            }
        });
    });

    // Toggle Status
    $(document).on('click', '.toggle-status', function () {
        const userId = $(this).data('id');
        const statusId = $(this).data('status');
        const action = statusId == 1 ? 'activate' : 'block';

        if (confirm(`Are you sure you want to ${action} this bidder?`)) {
            $.ajax({
                url: base_url + 'api/admin/bidders/toggle-status',
                type: 'POST',
                data: { user_id: userId, status_id: statusId },
                dataType: 'json',
                success: function (result) {
                    if (result.success) {
                        alert('Status updated successfully');
                        biddersTable.ajax.reload();
                    } else {
                        alert('Error updating status: ' + (result.message || 'Unknown error'));
                    }
                },
                error: function (xhr) {
                    console.error('Error toggling status:', xhr.responseText);
                }
            });
        }
    });
});
