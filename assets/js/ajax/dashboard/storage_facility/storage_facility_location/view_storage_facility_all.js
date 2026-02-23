$(document).ready(function () {


    function storageFacilityListTable() {


        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('#storage-facility-table')) {
            // DataTable is already initialized, destroy the existing instance
            $('#storage-facility-table').DataTable().destroy();
        }

        // Initialize DataTable
        $('#storage-facility-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": SITE_URL + "api/storage/storage-facility/get", // Change this to your backend URL
                "type": "POST",
                "beforeSend": function () {

                },
                "dataSrc": function (json) {


                    if (json.status === "success" && json.result) {
                        showNotification(json.message, "success");

                        json.draw = json.result.draw;
                        json.recordsTotal = json.result.recordsTotal;
                        json.recordsFiltered = json.result.recordsFiltered;
                        return json.result.data;
                    }

                    showNotification(json.message, "error");
                    return [];
                },
                "error": function (xhr, status, error) {
                    console.error("DataTables AJAX error:", status, error);
                    try {
                        var response = JSON.parse(xhr.responseText);
                        if (response.error) {
                            console.error("Server error:", response.message);
                        }
                    } catch (e) {
                        console.error("Error parsing JSON response:", e);
                    }
                },
            },
            "columns": [
                { "data": "storage_facility_id" },
                { "data": "storage_facility_name" },
                { "data": "storage_facility_company_name" },
                { "data": "storage_facility_timezone" },
                { "data": "storage_facility_updated_at" },
                {
                    "data": "storage_facility_status",
                    "orderable": false,
                    "render": function (data, type, row) {
                        var isChecked = (data.toLowerCase() === 'active');
                        var switchHtml = '<input type="checkbox" data-toggle="toggle" data-type="account" data-action="verification" data-on="Active" data-off="Inactive" ' +
                            'data-onstyle="success" data-offstyle="danger" ' +
                            'data-storage-facility-system-id="' + row.storage_facility_id + '" ' +
                            (isChecked ? 'checked' : '') + '>';
                        return switchHtml;
                    }
                },
                {
                    "data": null,
                    "orderable": false,
                    "render": function (data, type, row) {
                        return `
                        <div style="position: relative; display: inline-block;">
                            <div class="dropdown d-inline-block">
                                <button style="background: #1C421D !important;" class=" border-0 btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                    ⋮
                                </button>
                                <ul class="dropdown-menu">
                                    <li>
                                        <a class="dropdown-item edit-storage-facility-btn" href="javascript:void(0);" data-storage-facility-id="${row.storage_facility_id}">
                                            <i class="bi bi-pencil-square me-2"></i> Edit
                                        </a>
                                    </li>
                                    <li><hr class="dropdown-divider"></li>
                                    <li>
                                        <a class="dropdown-item text-danger delete-storage-facility-btn" href="#" data-storage-facility-id="${row.storage_facility_id}" data-storage-facility-name="${row.storage_facility_name}">
                                            <i class="bi bi-trash me-2"></i> Delete
                                        </a>
                                    </li>
                                </ul>
                            </div>
                        </div>
                        `;
                    }
                }

            ],
            "order": [[1, 'desc']], // Set the combined name column as default sort
            "initComplete": function (settings, json) {

            },
            "drawCallback": function (settings) {
                // Initialize Bootstrap Switch for new elements
                $('[data-toggle="toggle"]').bootstrapSwitch();

                // Handle switch change event for elements with data-type='card'
                $('[data-toggle="toggle"][data-action="verification"]').on('switchChange.bootstrapSwitch', function (event, state) {
                    var storageFacilityId = $(this).data('storage-facility-system-id');
                    var status = state ? 'Active' : 'Inactive';

                    let actionMessage = status.toLowerCase() === "active"
                        ? "You are about to <strong>activate</strong> this card. Do you want to proceed?"
                        : "You are about to <strong>deactivate</strong> this card. Do you want to continue?";

                    Swal.fire({
                        title: 'Are you sure?',
                        html: actionMessage, // ✅ Clear message for activation & deactivation
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'Yes, change it!'
                    }).then((result) => {
                        if (result.isConfirmed) {

                            $.ajax({
                                url: SITE_URL + 'api/storage/storage-facility/update-status',
                                type: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    storage_facility_id: storageFacilityId,
                                    storage_facility_status_name: status
                                }),
                                beforeSend: function () {

                                },
                                success: function (response) {

                                    if (response.status === "success") {

                                        showNotification(response.message, "success");
                                        storageFacilityListTable();

                                    } else {
                                        showNotification(response.message, "error");
                                    }
                                    // Additional success handling
                                },
                                error: function (jqXHR, textStatus, errorThrown) {
                                    let errorMessage = "Something went wrong. Try again.";

                                    if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                                        errorMessage = jqXHR.responseJSON.message;
                                    }
                                    $('[data-payment-method-system-id="' + storageFacilityId + '"]').bootstrapSwitch('toggleState', true);

                                    showNotification(errorMessage, "error");
                                }
                            });

                        } else {
                            // User clicked No or closed the dialog, revert the switch state

                            $('[data-storage-facility-system-id="' + storageFacilityId + '"]').bootstrapSwitch('toggleState', true);
                        }
                    });
                });


            },
            "createdRow": function (row, data, dataIndex) {
                // Columns: ID, Name, Company, Timezone, Updated At, Status, Actions
                $('td:eq(0)', row).attr('data-label', 'ID');
                $('td:eq(1)', row).attr('data-label', 'Name');
                $('td:eq(2)', row).attr('data-label', 'Company');
                $('td:eq(3)', row).attr('data-label', 'Timezone');
                $('td:eq(4)', row).attr('data-label', 'Last Updated');
                $('td:eq(5)', row).attr('data-label', 'Status');
                $('td:eq(6)', row).attr('data-label', 'Actions');
            },
            "rowCallback": function (row, data, index) {
                // Add click event to the entire row except for the buttons
                $(row).on('click', function (event) {
                    // Check if the clicked element is not a button or a link
                    if (!$(event.target).is('button') && !$(event.target).is('a')) {


                    }
                });

                // Add hover effect
                $(row).hover(
                    function () {
                        $(this).addClass('table-hover');
                    },
                    function () {
                        $(this).removeClass('table-hover');
                    }
                ).css('cursor', 'pointer');
            },
        });




    }

    storageFacilityListTable();

    // Handle delete button click
    $(document).on('click', '.delete-storage-facility-btn', function (e) {
        e.preventDefault();

        const facilityId = $(this).data('storage-facility-id');
        const facilityName = $(this).data('storage-facility-name');

        Swal.fire({
            title: 'Are you sure?',
            html: `You are about to <strong>permanently delete</strong> the location:<br><br><strong>${facilityName}</strong><br><br>This action cannot be undone.`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                // Show loading
                Swal.fire({
                    title: 'Deleting...',
                    text: 'Please wait',
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });

                $.ajax({
                    url: SITE_URL + 'api/storage/storage-facility/delete',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        storage_facility_id: facilityId
                    }),
                    success: function (response) {
                        if (response.status === "success") {
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted!',
                                text: response.message,
                                timer: 2000,
                                showConfirmButton: false
                            });
                            storageFacilityListTable(); // Reload table
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function (jqXHR) {
                        let errorMessage = "Failed to delete location. Please try again.";
                        if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                            errorMessage = jqXHR.responseJSON.message;
                        }
                        Swal.fire({
                            icon: 'error',
                            title: 'Error',
                            text: errorMessage
                        });
                    }
                });
            }
        });
    });

    // Handle Edit button click
    $(document).on('click', '.edit-storage-facility-btn', function (e) {
        e.preventDefault();
        e.stopPropagation();

        console.log('Edit button clicked'); // Debug log

        const facilityId = $(this).data('storage-facility-id');

        // Show loading state or directly fetch data
        $.ajax({
            url: SITE_URL + "api/storage/storage-facility/get_by_storage_id",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({ storageFacilityId: facilityId }),
            success: function (response) {
                if (response.status === "success" && response.data) {
                    let facility = response.data[0];

                    // Populate form fields
                    $("#storage_facility_id").val(facility.storage_facility_id); // Set hidden ID
                    $("#companiesDropdown").val(facility.storage_facility_company_id);
                    $("#storage_facility_name").val(facility.storage_facility_name);
                    $("#storage_facility_website").val(facility.storage_facility_website);
                    $("#storage_facility_description").val(facility.storage_facility_description);
                    $("#storage_facility_contact_email").val(facility.storage_facility_contact_email?.replace(/[\r\n]+/g, '').trim());
                    $("#storage_facility_additional_email").val(facility.storage_facility_additional_email?.replace(/[\r\n]+/g, '').trim());
                    $("#cell_phone").val(facility.storage_facility_cell_phone);
                    $("#storage_facility_office_hours").val(facility.storage_facility_office_hours);
                    $("#storage_facility_payout_details").val(facility.storage_facility_payout_details);
                    $("#storage_facility_youtube_video_url").val(facility.storage_facility_youtube_video_url);
                    $("#storage_facility_branding_logo").val(facility.storage_facility_branding_logo);
                    $("#storage_facility_notice_to_buyers").val(facility.storage_facility_notice_to_buyers);

                    // Address info
                    $("#address").val(facility.storage_facility_street_address);
                    $("#apt_suite").val(facility.storage_facility_apt_suite);
                    $("#city").val(facility.storage_facility_city);
                    $("#zip").val(facility.storage_facility_zip);
                    $("#country").val(facility.storage_facility_country);
                    $("#state").val(facility.storage_facility_state);

                    // Handle Timezone
                    const timezoneSelect = $("#storage_facility_timezone");
                    const setTimezone = () => {
                        if (facility.storage_facility_timezone) {
                            timezoneSelect.val(facility.storage_facility_timezone);
                        }
                    };
                    if (timezoneSelect.find("option").length > 1) {
                        setTimezone();
                    } else {
                        setTimeout(setTimezone, 500); // fallback
                    }

                    // Handle Images
                    $('#image-preview-container').empty();
                    selectedImages = []; // Reset selected images
                    if (Array.isArray(facility.storage_facility_cover_images)) {
                        facility.storage_facility_cover_images.forEach(img => {
                            const thumb = $(`
                                <div class="image-thumb existing" data-upload-id="${img.id}">
                                    <img src="${img.path}" alt="Image">
                                    <button class="remove-btn" type="button">&times;</button>
                                </div>
                            `);
                            // Note: Logic to handle removal of *existing* images (mark for deletion) 
                            // is not fully implemented in add_storage_facility.js update logic yet.
                            // For this pass, we visualize them. Implementing full delete sync 
                            // requires modifying add_storage_facility.js to accept remove_upload_ids.

                            thumb.find('.remove-btn').on('click', () => {
                                thumb.remove();
                                // TODO: Add to removedUploadIds array if backend supports it
                            });
                            $('#image-preview-container').append(thumb);
                        });
                    }

                    // Update Modal UI
                    $('#addStorageFacilityModalLabel').text('Edit Facility Location');
                    $('#save-storage-facility-add-btn').text('Save Changes');

                    // Show Modal
                    $('#addStorageFacilityModal').modal('show');

                } else {
                    showNotification("Failed to fetch facility details.", "error");
                }
            },
            error: function (xhr, status, error) {
                console.error("Fetch Error:", status, error);
                console.log("Response:", xhr.responseText);
                showNotification("Error fetching details: " + (xhr.responseJSON?.message || error || "Unknown error"), "error");
            }
        });
    });

});
