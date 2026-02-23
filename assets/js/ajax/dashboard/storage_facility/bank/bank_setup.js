$(document).ready(function () {
    const bankTable = $('#bank-account-table').DataTable({
        "processing": true,
        "serverSide": true,
        "ajax": {
            "url": SITE_URL + "api/users/storage-facility/bank-account/get",
            "type": "POST",
            "dataSrc": function (json) {
                if (json.status === "success" && json.data) {
                    return json.data;
                }
                return [];
            }
        },
        "columns": [
            {
                "data": "account_name",
                "render": function (data, type, row) {
                    return `<strong>${data || 'N/A'}</strong>`;
                }
            },
            {
                "data": "bank_details",
                "render": function (data, type, row) {
                    return data || '000-000 / ****0000';
                }
            },
            {
                "data": "linked_locations",
                "render": function (data, type, row) {
                    return data || '<span class="text-muted small">No locations linked</span>';
                }
            },
            {
                "data": null,
                "className": "text-end",
                "render": function (data, type, row) {
                    return `<button class="btn btn-link text-primary p-0 __btn__edit__links" data-bank-id="${row.bank_id}">Edit Links</button>`;
                }
            }
        ],
        "dom": 'rtp', // Hide search and length for cleaner look as per reference
        "language": {
            "emptyTable": "No payout accounts found. Add one to start receiving payouts."
        },
        "createdRow": function (row, data, dataIndex) {
            $(row).find('td').css('vertical-align', 'middle').css('padding', '15px 10px');
        }
    });

    // Handle "Add New Bank Account" button
    $(document).on("click", ".__btn__add__new__bank__account", function () {
        $("#addBankModalLabel").text("Add New Bank Account (Stripe Connect)");
        $("#bank-setup-form")[0].reset();
        $("#addBankModal").modal("show");
        loadModalLocations();
    });

    // Handle "Edit Links" button
    $(document).on("click", ".__btn__edit__links", function () {
        const bankId = $(this).data("bank-id");
        $("#addBankModalLabel").text("Edit Links for Payout Account");
        $("#addBankModal").modal("show");
        loadModalLocations(bankId);
        // Fetch bank details to populate other fields if needed, or just focus on links
    });

    function loadModalLocations(bankId = null) {
        $("#modal-locations-list").html('<div class="col-12 text-center py-3"><span class="spinner-border spinner-border-sm text-secondary"></span></div>');

        $.when(
            $.ajax({ url: SITE_URL + "api/user/fetch_storage_facilities_by_user", type: "POST" }),
            bankId ? $.ajax({
                url: SITE_URL + "api/users/storage-facility/bank-account/facilities",
                type: "POST",
                contentType: "application/json",
                data: JSON.stringify({ bankId: bankId })
            }) : $.Deferred().resolve([{ data: [] }])
        ).done(function (allRes, linkedRes) {
            const all = allRes[0].data || [];
            const linked = linkedRes[0].data || [];
            const linkedIds = linked.map(f => parseInt(f.storage_facility_id));

            let html = '';
            if (all.length === 0) {
                html = '<div class="col-12 text-muted small">No locations found. Please add a location first.</div>';
            } else {
                all.forEach(facility => {
                    const isChecked = linkedIds.includes(parseInt(facility.storage_facility_id));
                    html += `
                        <div class="col-md-6">
                            <div class="form-check">
                                <input class="form-check-input location-checkbox" type="checkbox" value="${facility.storage_facility_id}" id="loc_${facility.storage_facility_id}" ${isChecked ? 'checked' : ''}>
                                <label class="form-check-label small" for="loc_${facility.storage_facility_id}">
                                    ${facility.storage_facility_name}
                                </label>
                            </div>
                        </div>
                    `;
                });
            }
            $("#modal-locations-list").html(html);
        }).fail(function () {
            $("#modal-locations-list").html('<div class="col-12 text-danger small">Failed to load locations.</div>');
        });
    }

    $("#bank-setup-form").on("submit", function (e) {
        e.preventDefault();

        const saveBtn = $("#save-bank-btn");
        const bankId = $(this).data("bank-id"); // We'll set this when editing
        saveBtn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm"></span> Saving...');

        const formData = {
            bank_id: bankId,
            account_name: $("#bank_account_name").val(),
            institution: $("#bank_institution").val(),
            bsb: $("#bank_bsb").val(),
            account_number: $("#bank_account_number").val(),
            location_ids: $(".location-checkbox:checked").map(function () { return this.value; }).get()
        };

        const isEdit = bankId ? true : false;
        const apiUrl = isEdit ?
            SITE_URL + "api/users/storage-facility/bank-account/update-facilities" :
            SITE_URL + "api/stripe/connected-account/attach";

        $.ajax({
            url: apiUrl,
            type: "POST",
            contentType: "application/json",
            data: JSON.stringify(formData),
            success: function (resp) {
                if (resp.status === "success" || resp.success) {
                    if (resp.onboarding_url) {
                        window.location.href = resp.onboarding_url;
                    } else {
                        Swal.fire("Success", resp.message || "Saved successfully", "success");
                        $("#addBankModal").modal("hide");
                        bankTable.ajax.reload();
                    }
                } else {
                    Swal.fire("Error", resp.message || "Failed to save bank details", "error");
                }
            },
            error: function () {
                Swal.fire("Error", "Server error occurred", "error");
            },
            complete: function () {
                saveBtn.prop('disabled', false).text('Save Bank Details');
            }
        });
    });

    // Need to set data-bank-id on form when editing
    $(document).on("click", ".__btn__add__new__bank__account", function () {
        $("#bank-setup-form").removeData("bank-id").removeAttr("data-bank-id");
        $(".bank-details-fields").show();
        $(".bank-details-fields input, .bank-details-fields select").prop('required', true);
        $("#modal-locations-list-container").show();
    });

    $(document).on("click", ".__btn__edit__links", function () {
        const bankId = $(this).data("bank-id");
        $("#bank-setup-form").data("bank-id", bankId).attr("data-bank-id", bankId);
        $(".bank-details-fields").hide();
        $(".bank-details-fields input, .bank-details-fields select").prop('required', false);
        $("#modal-locations-list-container").show();
    });
});
