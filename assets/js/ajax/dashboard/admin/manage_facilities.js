$(document).ready(function () {
    // Initialize DataTable
    const facilitiesTable = $('#facilities-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: base_url + 'api/admin/facilities/list',
            type: 'POST',
            data: function (d) {
                d.search = $('#facility-search').val();
            }
        },
        columns: [
            { data: 'company_id' },
            { data: 'company_name' },
            {
                data: null,
                render: function (data) {
                    return data.first_name + ' ' + data.last_name;
                }
            },
            { data: 'email' },
            { data: 'total_locations' },
            {
                data: 'total_debt',
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
                        `<button class="btn btn-sm btn-warning toggle-facility-status" data-id="${row.company_id}" data-status="2">Block</button>` :
                        `<button class="btn btn-sm btn-success toggle-facility-status" data-id="${row.company_id}" data-status="1">Activate</button>`;

                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-primary edit-facility" data-id="${row.company_id}" 
                                data-name="${row.company_name}" data-email="${row.email}">Edit</button>
                            <button class="btn btn-info view-locations" data-id="${row.company_id}">Locations</button>
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
    $('#facility-search').on('keyup', function () {
        facilitiesTable.ajax.reload();
    });

    // Edit Facility
    $(document).on('click', '.edit-facility', function () {
        const id = $(this).data('id');
        const name = $(this).data('name');
        const email = $(this).data('email');

        $('#edit-company-id').val(id);
        $('#edit-company-name').val(name);
        $('#edit-facility-email').val(email);

        $('#editFacilityModal').modal('show');
    });

    // Save Facility
    $('#save-facility-btn').on('click', function () {
        const formData = $('#edit-facility-form').serialize();

        $.ajax({
            url: base_url + 'api/admin/facilities/update',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (result) {
                if (result.success) {
                    alert('Facility updated successfully');
                    $('#editFacilityModal').modal('hide');
                    facilitiesTable.ajax.reload();
                } else {
                    alert('Error updating facility: ' + (result.message || 'Unknown error'));
                }
            },
            error: function (xhr) {
                console.error('Error updating facility:', xhr.responseText);
            }
        });
    });

    // View Locations
    $(document).on('click', '.view-locations', function () {
        const companyId = $(this).data('id');

        $.ajax({
            url: base_url + 'api/admin/facilities/locations',
            type: 'POST',
            data: { company_id: companyId },
            dataType: 'json',
            success: function (result) {
                if (result.success) {
                    let html = '';
                    result.locations.forEach(function (location) {
                        html += `
                            <tr>
                                <td>${location.id}</td>
                                <td>${location.name}</td>
                                <td>${location.street_address || 'N/A'}</td>
                                <td>${location.city || 'N/A'}</td>
                                <td>${location.state || 'N/A'}</td>
                            </tr>
                        `;
                    });
                    $('#locations-list').html(html);
                    $('#locationsModal').modal('show');
                } else {
                    alert('Error loading locations: ' + (result.message || 'Unknown error'));
                }
            },
            error: function (xhr) {
                console.error('Error loading locations:', xhr.responseText);
            }
        });
    });

    // Toggle Status
    $(document).on('click', '.toggle-facility-status', function () {
        const companyId = $(this).data('id');
        const statusId = $(this).data('status');
        const action = statusId == 1 ? 'activate' : 'block';

        if (confirm(`Are you sure you want to ${action} this facility?`)) {
            $.ajax({
                url: base_url + 'api/admin/facilities/toggle-status',
                type: 'POST',
                data: { company_id: companyId, status_id: statusId },
                dataType: 'json',
                success: function (result) {
                    if (result.success) {
                        alert('Status updated successfully');
                        facilitiesTable.ajax.reload();
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
