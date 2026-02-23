/**
 * Manage Staff Page Scripts
 */

$(document).ready(function () {
    // Initialize DataTable
    var table = $('#staff-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": base_url + "api/admin/staff/list",
            "type": "POST"
        },
        "columns": [
            {
                "data": "id",
                "render": function (data, type, row) {
                    return '<span class="text-white-50">#P' + String(data).padStart(3, '0') + '</span>';
                }
            },
            {
                "data": null,
                "render": function (data, type, row) {
                    return '<div class="d-flex align-items-center">' +
                        '<div>' +
                        '<h6 class="mb-0 text-white">' + row.first_name + ' ' + row.last_name + '</h6>' +
                        '<small class="text-white-50">' + row.email + '</small>' +
                        '</div>' +
                        '</div>';
                }
            },
            {
                "data": "role_name",
                "className": "text-info"
            },
            {
                "data": "status_name",
                "render": function (data, type, row) {
                    var badgeClass = data === 'Active' ? 'bg-success bg-opacity-10 text-success' : 'bg-danger bg-opacity-10 text-danger';
                    return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                }
            },
            {
                "data": null,
                "render": function (data, type, row) {
                    var statusAction = row.status_name === 'Active' ? 'Deactivate' : 'Activate';
                    var statusClass = row.status_name === 'Active' ? 'text-danger' : 'text-success';

                    return '<a href="#" class="btn btn-sm btn-link text-primary p-0 me-2 edit-staff-btn" data-id="' + row.id + '">Edit Permissions</a>' +
                        '<a href="#" class="btn btn-sm btn-link ' + statusClass + ' p-0 toggle-status-btn" data-id="' + row.id + '" data-status="' + row.status_name + '">' + statusAction + '</a>';
                }
            }
        ],
        "dom": 'rt<"bottom"p><"clear">', // Custom DOM similar to other tables if needed
        "pageLength": 10,
        "ordering": false // Simplified for now
    });


    // Search functionality
    $('#search-staff').on('keyup', function () {
        table.search(this.value).draw();
    });

    // Handle Edit Button Click
    $('#staff-table').on('click', '.edit-staff-btn', function (e) {
        e.preventDefault();
        var id = $(this).data('id');

        // Fetch details
        $.ajax({
            url: base_url + 'api/admin/staff/get/' + id,
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    var data = response.data;
                    $('#staff_user_id').val(data.id);
                    $('input[name="first_name"]').val(data.first_name);
                    $('input[name="last_name"]').val(data.last_name);
                    $('input[name="email"]').val(data.email);
                    $('input[name="phone"]').val(data.phone);
                    $('select[name="role_id"]').val(data.role_id);
                    $('input[name="password"]').val(''); // Clear password, don't show hash

                    // Update Modal UI
                    $('#addStaffModalLabel').text('Edit Staff');
                    $('#save-staff-btn').text('Update Staff');

                    $('#addStaffModal').modal('show');
                } else {
                    alert('Error fetching details: ' + response.message);
                }
            }
        });
    });

    // Reset Modal on Close
    $('#addStaffModal').on('hidden.bs.modal', function () {
        $('#add-staff-form')[0].reset();
        $('#staff_user_id').val('');
        $('#addStaffModalLabel').text('Add New Staff');
        $('#save-staff-btn').text('Create Staff Account');
        $('#add-staff-form input, #add-staff-form select').removeClass('is-invalid');
    });

    // Handle Deactivate/Activate Button Click
    $('#staff-table').on('click', '.toggle-status-btn', function (e) {
        e.preventDefault();
        var id = $(this).data('id');
        var currentStatus = $(this).data('status'); // Optional: for confirmation message

        if (!confirm('Are you sure you want to change the status of this user?')) return;

        $.ajax({
            url: base_url + 'api/admin/staff/toggle-status',
            type: 'POST',
            data: { user_id: id },
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    // alert(response.message);
                    $('#staff-table').DataTable().ajax.reload(null, false); // Reload without resetting paging
                } else {
                    alert('Error: ' + response.message);
                }
            }
        });
    });

    // Handle Add/Update Staff Form Submission
    $('#save-staff-btn').click(function () {
        var form = $('#add-staff-form');
        var formData = new FormData(form[0]);
        var userId = $('#staff_user_id').val();

        // Determine URL based on whether it's Add or Update
        var url = userId ? (base_url + 'api/admin/staff/update') : (base_url + 'api/admin/staff/add');

        // Basic validation
        var isValid = true;
        form.find('input, select').each(function () {
            // Password is not required when Updating
            if ($(this).attr('name') === 'password' && userId) return;

            if ($(this).prop('required') && !$(this).val()) {
                isValid = false;
                $(this).addClass('is-invalid');
            } else {
                $(this).removeClass('is-invalid');
            }
        });

        if (!isValid) {
            alert('Please fill in all required fields.');
            return;
        }

        // Show loading state
        var btn = $(this);
        var originalText = btn.text();
        btn.prop('disabled', true).text(userId ? 'Updating...' : 'Creating...');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function (response) {
                if (response.success) {
                    alert(response.message);
                    $('#addStaffModal').modal('hide');
                    // Reset handled by modal hidden event
                    // Reload table
                    $('#staff-table').DataTable().ajax.reload(null, false);
                } else {
                    alert(response.message || 'An error occurred');
                }
            },
            error: function () {
                alert('An error occurred while communicating with the server.');
            },
            complete: function () {
                btn.prop('disabled', false).text(originalText);
            }
        });
    });

    // Remove invalid class on input
    $('#add-staff-form input, #add-staff-form select').on('input change', function () {
        $(this).removeClass('is-invalid');
    });
});
