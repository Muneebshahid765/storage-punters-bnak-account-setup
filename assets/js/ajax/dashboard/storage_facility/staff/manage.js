$(document).ready(function () {


    const $spinner = $('#spinner-overlay');

    function showSpinner() { $spinner.show(); }
    function hideSpinner() { $spinner.hide(); }

    function staffMemberListTable() {


        // Check if DataTable is already initialized
        if ($.fn.DataTable.isDataTable('#staff-member-table')) {
            // DataTable is already initialized, destroy the existing instance
            $('#staff-member-table').DataTable().destroy();
        }

        // Initialize DataTable
        $('#staff-member-table').DataTable({
            "processing": true,
            "serverSide": true,
            "ajax": {
                "url": SITE_URL + "api/staff/storage-facility/get", // Change this to your backend URL
                "type": "POST",
                "beforeSend": function () {
                    showUniversalLoaderForTable('staff-member-table');
                },
                "dataSrc": function (json) {

                    hideUniversalLoader();

                    if (json.status === "success" && json.result) {
                        showNotification(json.message, "success");

                        json.draw = json.result.draw;
                        json.recordsTotal = json.result.recordsTotal;
                        json.recordsFiltered = json.result.recordsFiltered;
                        return json.result.data;
                    }

                    // Manually override DataTables draw & record counts
                    json.draw = json.result?.draw ?? 0;
                    json.recordsTotal = json.result?.recordsTotal ?? 0;
                    json.recordsFiltered = json.result?.recordsFiltered ?? 0;

                    showNotification(json.message, "error");
                    return [];
                },
                "error": function (xhr, status, error) {

                    hideUniversalLoader();

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
                { "data": "staff_member_id", "className": "fw-bold text-muted" },
                { "data": "staff_member_first_name" },
                { "data": "staff_member_last_name" },
                {
                    "data": "staff_member_email",
                    "render": function (data) {
                        return `<span class="text-emerald-primary fw-500">${data}</span>`;
                    }
                },
                {
                    "data": "staff_member_status",
                    "orderable": false,
                    "render": function (data, type, row) {
                        const isActive = data.toLowerCase() === 'active';

                        if (isActive) {
                            return `
                                <div class="d-flex align-items-center">
                                    <input type="checkbox"
                                        data-toggle="toggle"
                                        data-type="account"
                                        data-action="activation"
                                        data-on="Active"
                                        data-off="Inactive"
                                        data-onstyle="success"
                                        data-offstyle="danger"
                                        data-size="small"
                                        data-staff-member-system-id="${row.staff_member_id}"
                                        checked>
                                </div>
                            `;
                        }

                        return `
                            <div class="d-flex align-items-center gap-2">
                                <span class="badge bg-soft-danger text-danger border border-danger-subtle px-3 py-2 rounded-pill fw-bold">
                                    <i class="bi bi-clock-history me-1"></i> Pending
                                </span>
                                <button type="button"
                                    class="btn btn-sm btn-yellow text-white fw-semibold rounded-pill px-3 __btn__resend__invitation"
                                    title="Resend Invitation"
                                    data-staff-member-id="${row.staff_member_id}"
                                    data-staff-member-email="${row.staff_member_email}">
                                    <i class="bi bi-arrow-repeat me-1"></i> Resend
                                </button>
                            </div>
                        `;
                    }
                },
                {
                    "data": null,
                    "orderable": false,
                    "className": "text-end",
                    "render": function (data, type, row) {
                        return `
                            <div class="d-flex justify-content-end gap-2">
                                <button type="button" 
                                    class="btn btn-sm btn-outline-primary border-2 rounded-3 px-3 __btn__edit__staff"
                                    title="Edit Staff"
                                    data-staff-member-id="${row.staff_member_id}"
                                    data-staff-member-first-name="${row.staff_member_first_name}"
                                    data-staff-member-last-name="${row.staff_member_last_name}"
                                    data-staff-member-email="${row.staff_member_email}"
                                    data-manage-all="${row.manage_all_locations || 0}">
                                    <i class="bi bi-pencil-square"></i> Edit
                                </button>
                                <button type="button" 
                                    class="btn btn-sm btn-outline-danger border-2 rounded-3 px-3 __btn__delete__staff"
                                    title="Delete Staff"
                                    data-staff-member-id="${row.staff_member_id}"
                                    data-staff-member-name="${row.staff_member_first_name} ${row.staff_member_last_name}">
                                    <i class="bi bi-trash3-fill"></i> Delete
                                </button>
                            </div>`;
                    }
                }
            ],
            "order": [[1, 'desc']], // Set the combined name column as default sort
            "initComplete": function (settings, json) {

            },
            "createdRow": function (row, data, dataIndex) {
                // Columns: ID, First Name, Last Name, Email, Status
                $('td:eq(0)', row).attr('data-label', 'Staff #');
                $('td:eq(1)', row).attr('data-label', 'First Name');
                $('td:eq(2)', row).attr('data-label', 'Last Name');
                $('td:eq(3)', row).attr('data-label', 'Email');
                $('td:eq(4)', row).attr('data-label', 'Access');
                $('td:eq(5)', row).attr('data-label', 'Action');
            },
            "drawCallback": function (settings) {
                // Initialize Bootstrap Switch for new elements
                $('[data-toggle="toggle"]').bootstrapSwitch();


                // Handle switch change event for elements with data-type='staff'
                $('[data-toggle="toggle"][data-action="activation"]').on('switchChange.bootstrapSwitch', function (event, state) {
                    var staffMemberId = $(this).data('staff-member-system-id');
                    var status = state ? 'Active' : 'Inactive';

                    let actionMessage = status.toLowerCase() === "active"
                        ? "You are about to <strong>active</strong> this staff. Do you want to proceed?"
                        : "You are about to <strong>deactive</strong> this staff. Do you want to continue?";

                    Swal.fire({
                        title: 'Are you sure?',
                        html: actionMessage, // ✅ Clear message for activation & deactivation
                        icon: 'warning',
                        showCancelButton: true,
                        customClass: {
                            popup: 'swal2-custom-width',
                            confirmButton: 'btn btn-yellow text-white fw-bold',
                            cancelButton: 'btn btn-secondary ms-2'
                        },
                        confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Yes, change it!',
                        cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancel',
                    }).then((result) => {
                        if (result.isConfirmed) {

                            $.ajax({
                                url: SITE_URL + 'api/staff/storage-facility/update-status',
                                type: 'POST',
                                contentType: 'application/json',
                                data: JSON.stringify({
                                    staff_member_id: staffMemberId,
                                    staff_member_status_name: status
                                }),
                                beforeSend: function () {

                                },
                                success: function (response) {

                                    if (response.status === "success") {

                                        showNotification(response.message, "success");
                                        staffMemberListTable();

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
                                    $('[data-toggle="toggle"][data-action="activation"][data-staff-member-system-id="' + staffMemberId + '"]').bootstrapSwitch('toggleState', true);


                                    showNotification(errorMessage, "error");
                                }
                            });

                        } else {
                            // User clicked No or closed the dialog, revert the switch state

                            $('[data-toggle="toggle"][data-action="activation"][data-staff-member-system-id="' + staffMemberId + '"]').bootstrapSwitch('toggleState', true);

                        }
                    });
                });


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

        /*$(document).on("click", ".__btn__view__staff_member", function () {

            let formContainer = $(".__table__staff__member");

            // Extract values from `data-` attributes
            let staffMemberId = $(this).data("staff-member-id");
            let firstName = $(this).data("staff-member-first-name");
            let lastName = $(this).data("staff-member-last-name");
            let email = $(this).data("staff-member-email");


            // Toggle form: If exists → Remove; Else → Append
            if ($(".__update__staff_member__form").length > 0 || $(".__new__staff_member__add__form").length > 0) {
                $(".__update__staff_member__form").slideUp(300, function () {
                    $(this).remove();
                    // Find the button where `data-staff-member-id` matches the selected ID
                    $(`.__btn__update__staff__member__text`).text("Edit Staff");
                    $(".__btn__add__new__staff__member__text").text("Add New");
                });
                $(".__new__staff_member__add__form").slideUp(300, function () {
                    $(this).remove();
                    $(".__btn__add__new__staff__member__text").text("Add New");
                    // Find the button where `data-staff-member-id` matches the selected ID
                    $(`.__btn__update__staff__member__text`).text("Edit Staff");
                });
            } else {
                let formHTML = `
                <div class="mt-5 mb-5 __update__staff_member__form">
                    <form id="staffMemberUpdateForm">

                     <input type="hidden" id="staff_member_id" value="${staffMemberId}" class="input-secondary text-300">

                    <div class="row">
                        <div class="mt-4 col-xl-6 col-lg-3 col-md-6">
                            <label for="firstname" class="input-label-secondary">First Name *</label>
                            <div class="input-field-container">
                                <input id="firstname" type="text" placeholder="First name" name="firstname"
                                    class="input-secondary text-300" value="${firstName}">
                            </div>
                        </div>

                        <div class="mt-4 col-xl-6 col-lg-6 col-md-6">
                            <label for="lastname" class="input-label-secondary">Last Name *</label>
                            <div class="input-field-container">
                                <input id="lastname" type="text" placeholder="Last name" name="lastname"
                                    class="input-secondary text-300" value="${lastName}">
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="mt-4 col-lg-12 col-md-12">
                            <label for="email_address" class="input-label-secondary">Email *</label>
                            <div class="input-field-container">
                                <input id="email_address" type="email" placeholder="Email Address" name="email_address"
                                    class="input-secondary text-300" value="${email}" readonly>
                            </div>
                        </div>
                    </div>


                         <div style="width: max-content;" class="mt-4 mx-auto col">
                            <button type="submit" class="w-100 btn-yellow btn-update-staff-member btn-contact text-uppercase px-md-4 btn btn-primary btn-md">
                                Update Staff Information
                            </button>
                        </div>
                    </form>
                </div>`;

                formContainer.append(formHTML);


                $(".__update__staff_member__form").hide().slideDown(300);
                $(`.__btn__update__staff__member__text`).text("Edit Staff");
                $(`button[data-staff-member-id="${staffMemberId}"] .__btn__update__staff__member__text`).text("Hide");
            }

        });*/


    }

    staffMemberListTable();


    // Reset form when modal is closed
    $('#addStaffModal').on('hidden.bs.modal', function () {
        $('#staffMemberAddForm')[0].reset();
        $('.error-message').remove();
        $("#location-checkboxes-container").removeClass("opacity-50").find("input").prop("disabled", false);
    });

    async function loadCompanyLocations() {
        $.ajax({
            url: SITE_URL + "api/user/fetch_storage_facilities_by_user",
            type: "POST",
            success: function (response) {
                if (response.status === "success" && response.data) {
                    let html = '';
                    response.data.forEach(loc => {
                        html += `
                            <div class="form-check mb-1">
                                <input class="form-check-input location-checkbox" type="checkbox" value="${loc.storage_facility_id}" id="loc_${loc.storage_facility_id}">
                                <label class="form-check-label small" for="loc_${loc.storage_facility_id}">
                                    ${loc.storage_facility_name}
                                </label>
                            </div>
                        `;
                    });
                    $("#location-list").html(html || '<p class="text-muted small mb-0">No locations found.</p>');
                }
            }
        });
    }

    $('#addStaffModal').on('show.bs.modal', function () {
        loadCompanyLocations();
    });

    $(document).on("change", "#manage_all", function () {
        if ($(this).is(":checked")) {
            $("#location-checkboxes-container").addClass("opacity-50").find("input").prop("disabled", true).prop("checked", false);
        } else {
            $("#location-checkboxes-container").removeClass("opacity-50").find("input").prop("disabled", false);
        }
    });


    $(document).on("submit", "#staffMemberAddForm", async function (e) {
        e.preventDefault();

        $(".error-message").remove(); // Clear previous error messages

        let isValid = true;
        let firstInvalid = null;

        function showError(id, message) {
            const $el = $(`#${id}`);
            $el.after(`<p class="error-message text-yellow text-error">${message}</p>`);
            if (!firstInvalid) firstInvalid = $el;
            isValid = false;
        }

        // Collect input values
        let firstname = $("#firstname").val().trim();
        let lastname = $("#lastname").val().trim();
        let email_address = $("#email_address2").val().trim();

        // Validate first name
        if (!firstname) showError("firstname", "First name is required.");

        // Validate last name
        if (!lastname) showError("lastname", "Last name is required.");

        // Validate email
        if (!email_address) {
            showError("email_address2", "Email address is required.");
        } else {
            const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
            if (!emailPattern.test(email_address)) {
                showError("email_address2", "Please enter a valid email address.");
            }
        }

        if (!isValid) {
            firstInvalid?.focus();
            return;
        }

        let location_ids = [];
        $(".location-checkbox:checked").each(function () {
            location_ids.push($(this).val());
        });

        let formData = {
            firstname: firstname,
            lastname: lastname,
            email: email_address,
            manage_all: $("#manage_all").is(":checked") ? 1 : 0,
            location_ids: location_ids,
            can_manage_auctions: $("#manage_auctions").is(":checked") ? 1 : 0,
            can_view_reports: $("#view_reports").is(":checked") ? 1 : 0,
            can_handle_disputes: $("#handle_disputes").is(":checked") ? 1 : 0,
            can_manage_users: $("#manage_users").is(":checked") ? 1 : 0
        };

        showSpinner(); // Show global spinner

        $.ajax({
            url: SITE_URL + "api/staff/storage-facility/add",
            type: 'POST',
            contentType: "application/json",
            dataType: 'json',
            data: JSON.stringify(formData),
            beforeSend: function () {
                // showUniversalLoaderForTable('staff-member-table');
            },
            success: function (response) {

                hideUniversalLoader();

                if (response.status === 'success') {


                    if (response.data && response.data.email && response.data.company_id) {
                        $.ajax({
                            url: SITE_URL + "email/process-staff-invitation",
                            type: "POST",
                            contentType: "application/json",
                            data: JSON.stringify({
                                email: response.data.email,
                                companyId: response.data.company_id,
                            }),
                            success: function (emailResponse) {

                                hideSpinner();

                                if (emailResponse.success) {
                                    showNotification(emailResponse.message, "success");
                                    staffMemberListTable();

                                    staffMemberListTable();

                                    // Close modal and reset form
                                    $('#addStaffModal').modal('hide');
                                    $('#staffMemberAddForm')[0].reset();


                                } else {
                                    showNotification(emailResponse.message, "error");
                                }
                            },
                            error: function () {
                                console.error("Email sending failed.");

                            }
                        });
                    } else {
                        showNotification("We couldn't send the invitation email. Please try again later.", "error");
                        hideSpinner();
                    }
                } else {

                    showNotification(response.message, "error");
                    hideSpinner();
                }
            },
            error: function (xhr, status, error) {
                hideUniversalLoader();
                showNotification(xhr.responseJSON.message, "error");
                hideSpinner();
            }
        });
    });

    $(document).on("click", ".__btn__resend__invitation", function () {
        const email = $(this).data("staff-member-email");
        const staffId = $(this).data("staff-member-id");

        Swal.fire({
            title: 'Resend Invitation?',
            html: `
            You are about to <strong>resend</strong> an invitation to:<br>
            <span class="text-success fw-semibold">${email}</span><br><br>
            Do you want to continue?
        `,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Yes,  resend it!',
            cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancel',
            focusConfirm: true,
            customClass: {
                popup: 'swal2-custom-width',
                confirmButton: 'btn btn-yellow text-white fw-bold',
                cancelButton: 'btn btn-secondary ms-2'
            }
        }).then((result) => {
            if (result.isConfirmed) {
                showSpinner(); // Show global spinner

                $.ajax({
                    url: SITE_URL + "email/process-staff-invitation",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({ email: email }),
                    success: function (emailResponse) {
                        hideSpinner();

                        if (emailResponse.success) {
                            Swal.fire({
                                icon: 'success',
                                title: 'Invitation Resent!',
                                text: emailResponse.message || 'The invitation email has been resent successfully.',
                                confirmButtonColor: '#198754'
                            });
                            staffMemberListTable();
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: emailResponse.message || 'Unable to resend the invitation. Try again later.',
                                confirmButtonColor: '#d33'
                            });
                        }
                    },
                    error: function () {
                        hideSpinner();
                        Swal.fire({
                            icon: 'error',
                            title: 'Email Failed',
                            text: 'Something went wrong while sending the invitation email. Please try again.',
                            confirmButtonColor: '#d33'
                        });
                    }
                });
            }
        });
    });



    /* $(document).on("submit", "#staffMemberUpdateForm", async function (e) {
         e.preventDefault();

         // Validate input fields
         let staff_member_id = $("#staff_member_id").val().trim();
         let firstname = $("#firstname").val().trim();
         let lastname = $("#lastname").val().trim();

         // Validate required fields
         if (!firstname || !lastname) {
             showNotification("All fields are required.", "error");
             return;
         }

         let formData = {
             staff_member_id: staff_member_id,
             firstname: firstname,
             lastname: lastname,
         };


         $.ajax({
             url: SITE_URL + "api/staff/storage-facility/update",
             type: 'POST',
             contentType: "application/json",
             dataType: 'json',
             data: JSON.stringify(formData),
             beforeSend: function () {
                 showUniversalLoaderForTable('staff-member-table');
             },
             success: function (response) {
                 $(".btn-update-staff-member").text("Update Staff Informations").prop("disabled", false);

                 if (response.status === "success") {

                     showNotification(response.message, "success");


                     $(".__update__staff_member__form").slideUp(300, function () {
                         $(this).remove();
                         $(".__btn__add__new__staff_member__text").text("Add New");
                         $(".__btn__update__staff__member__text").text("Edit Staff");
                     });

                     staffMemberListTable();
                 } else {

                     showNotification(response.message, "error");
                 }
             },
             error: function (jqXHR, textStatus, errorThrown) {

                 $(".btn-update-staff-member").text("Update Staff Information").prop("disabled", false);

                 let errorMessage = "Something went wrong. Try again.";

                 if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                     errorMessage = jqXHR.responseJSON.message;
                 }

                 showNotification(errorMessage, "error");
             }
         });
     });*/

    async function loadCompanyLocationsForEdit(assignedLocationIds = []) {
        $.ajax({
            url: SITE_URL + "api/user/fetch_storage_facilities_by_user",
            type: "POST",
            success: function (response) {
                if (response.status === "success" && response.data) {
                    let html = '';
                    response.data.forEach(loc => {
                        const isChecked = assignedLocationIds.includes(loc.storage_facility_id.toString()) || assignedLocationIds.includes(parseInt(loc.storage_facility_id));
                        html += `
                            <div class="form-check mb-1">
                                <input class="form-check-input edit-location-checkbox" type="checkbox" value="${loc.storage_facility_id}" id="edit_loc_${loc.storage_facility_id}" ${isChecked ? 'checked' : ''}>
                                <label class="form-check-label small" for="edit_loc_${loc.storage_facility_id}">
                                    ${loc.storage_facility_name}
                                </label>
                            </div>
                        `;
                    });
                    $("#edit-location-list").html(html || '<p class="text-muted small mb-0">No locations found.</p>');
                }
            }
        });
    }

    $(document).on("click", ".__btn__edit__staff", function () {
        const staffId = $(this).data("staff-member-id");
        const firstName = $(this).data("staff-member-first-name");
        const lastName = $(this).data("staff-member-last-name");
        const email = $(this).data("staff-member-email");
        const manageAll = $(this).data("manage-all");

        $("#edit_staff_member_id").val(staffId);
        $("#edit_firstname").val(firstName);
        $("#edit_lastname").val(lastName);
        $("#edit_email").val(email);
        $("#edit_manage_all").prop("checked", parseInt(manageAll) === 1).trigger('change');

        // Fetch assigned locations and permissions
        $.ajax({
            url: SITE_URL + "api/staff/storage-facility/get_assigned_locations",
            type: "GET",
            data: { staff_member_id: staffId },
            success: function (response) {
                const assignedIds = (response.status === "success") ? response.data : [];
                loadCompanyLocationsForEdit(assignedIds);

                // Fetch permissions
                $.ajax({
                    url: SITE_URL + "api/staff/storage-facility/get_permissions",
                    type: "GET",
                    data: { staff_member_id: staffId },
                    success: function (permResponse) {
                        if (permResponse.status === "success" && permResponse.data) {
                            const p = permResponse.data;
                            $("#edit_manage_auctions").prop("checked", parseInt(p.can_manage_auctions) === 1);
                            $("#edit_view_reports").prop("checked", parseInt(p.can_view_reports) === 1);
                            $("#edit_handle_disputes").prop("checked", parseInt(p.can_handle_disputes) === 1);
                            $("#edit_manage_users").prop("checked", parseInt(p.can_manage_users) === 1);
                        } else {
                            $("#edit_manage_auctions, #edit_view_reports, #edit_handle_disputes, #edit_manage_users").prop("checked", false);
                        }
                        $("#editStaffModal").modal("show");
                    },
                    error: function () {
                        $("#edit_manage_auctions, #edit_view_reports, #edit_handle_disputes, #edit_manage_users").prop("checked", false);
                        $("#editStaffModal").modal("show");
                    }
                });
            },
            error: function () {
                loadCompanyLocationsForEdit([]);
                $("#editStaffModal").modal("show");
            }
        });
    });

    $(document).on("change", "#edit_manage_all", function () {
        if ($(this).is(":checked")) {
            $("#edit-location-checkboxes-container").addClass("opacity-50").find("input").prop("disabled", true).prop("checked", false);
        } else {
            $("#edit-location-checkboxes-container").removeClass("opacity-50").find("input").prop("disabled", false);
        }
    });

    $(document).on("submit", "#staffMemberUpdateForm", function (e) {
        e.preventDefault();

        const staffId = $("#edit_staff_member_id").val();
        const firstName = $("#edit_firstname").val().trim();
        const lastName = $("#edit_lastname").val().trim();
        const manageAll = $("#edit_manage_all").is(":checked") ? 1 : 0;

        let locationIds = [];
        if (!manageAll) {
            $(".edit-location-checkbox:checked").each(function () {
                locationIds.push($(this).val());
            });
        }

        if (!firstName || !lastName) {
            showNotification("First and last names are required.", "error");
            return;
        }

        const formData = {
            staff_member_id: staffId,
            firstname: firstName,
            lastname: lastName,
            manage_all: manageAll,
            location_ids: locationIds,
            can_manage_auctions: $("#edit_manage_auctions").is(":checked") ? 1 : 0,
            can_view_reports: $("#edit_view_reports").is(":checked") ? 1 : 0,
            can_handle_disputes: $("#edit_handle_disputes").is(":checked") ? 1 : 0,
            can_manage_users: $("#edit_manage_users").is(":checked") ? 1 : 0
        };

        $.ajax({
            url: SITE_URL + "api/staff/storage-facility/update",
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(formData),
            success: function (response) {
                if (response.status === "success") {
                    showNotification(response.message, "success");
                    $("#editStaffModal").modal("hide");
                    staffMemberListTable();
                } else {
                    showNotification(response.message, "error");
                }
            },
            error: function (xhr) {
                const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to update staff member.";
                showNotification(msg, "error");
            }
        });
    });

    $(document).on("click", ".__btn__delete__staff", function () {
        const staffId = $(this).data("staff-member-id");
        const staffName = $(this).data("staff-member-name");

        Swal.fire({
            title: 'Are you sure?',
            text: `You are about to delete staff member: ${staffName}. This action cannot be undone!`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: '<i class="bi bi-trash3-fill me-1"></i> Yes, delete them!',
            cancelButtonText: 'Cancel'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: SITE_URL + "api/staff/storage-facility/delete",
                    type: "POST",
                    contentType: "application/json",
                    data: JSON.stringify({ staff_member_id: staffId }),
                    success: function (response) {
                        if (response.status === "success") {
                            Swal.fire('Deleted!', response.message, 'success');
                            staffMemberListTable();
                        } else {
                            Swal.fire('Error!', response.message, 'error');
                        }
                    },
                    error: function (xhr) {
                        const msg = (xhr.responseJSON && xhr.responseJSON.message) ? xhr.responseJSON.message : "Failed to delete staff member.";
                        Swal.fire('Error!', msg, 'error');
                    }
                });
            }
        });
    });
});
