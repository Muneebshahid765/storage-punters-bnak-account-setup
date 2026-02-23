$(document).ready(function () {
    let selectedFiles = [];

    // Load Facilities and Global Rules
    loadFacilities();
    fetchGlobalRules();

    // Drag & Drop Handlers
    const dropZone = $('#drop-zone');
    const fileInput = $('#file-input');
    const previewContainer = $('#preview-container');

    dropZone.on('dragover', function (e) {
        e.preventDefault();
        $(this).addClass('border-emerald bg-emerald-light');
    });

    dropZone.on('dragleave', function (e) {
        e.preventDefault();
        $(this).removeClass('border-emerald bg-emerald-light');
    });

    dropZone.on('drop', function (e) {
        e.preventDefault();
        $(this).removeClass('border-emerald bg-emerald-light');
        const files = e.originalEvent.dataTransfer.files;
        handleFiles(files);
    });

    fileInput.on('change', function () {
        handleFiles(this.files);
    });

    function handleFiles(files) {
        if (files.length > 0) {
            Array.from(files).forEach(file => {
                selectedFiles.push(file);
                const reader = new FileReader();
                reader.onload = function (e) {
                    const img = `<div class="position-relative" style="width: 100px; height: 100px;">
                                    <img src="${e.target.result}" class="w-100 h-100 object-fit-cover rounded-3 shadow-sm border">
                                    <button type="button" class="btn btn-danger btn-sm p-0 position-absolute top-0 end-0 rounded-circle d-flex align-items-center justify-content-center" 
                                            style="width: 20px; height: 20px; transform: translate(30%, -30%);"
                                            onclick="removeFile(this, '${file.name}')">&times;</button>
                                </div>`;
                    previewContainer.append(img);
                }
                reader.readAsDataURL(file);
            });
            updateAnalyzeButton();
        }
    }

    window.removeFile = function (btn, fileName) {
        $(btn).parent().remove();
        selectedFiles = selectedFiles.filter(f => f.name !== fileName);
        updateAnalyzeButton();
    };

    function updateAnalyzeButton() {
        if (selectedFiles.length > 0 && $('#storageFacilityDropdown').val()) {
            $('#btn-analyze').prop('disabled', false);
        } else {
            $('#btn-analyze').prop('disabled', true);
        }
    }

    $('#storageFacilityDropdown').on('change', updateAnalyzeButton);

    let currentUploadIds = [];
    let currentAuctionId = null;

    // AI Analyze Action
    $('#btn-analyze').on('click', function () {
        const formData = new FormData();
        selectedFiles.forEach(file => formData.append('images[]', file));
        formData.append('storage_facility_id', $('#storageFacilityDropdown').val());
        formData.append('unit_number', $('#unit_number').val() || 'TBD');

        // Show Loading
        $('#ai-step-upload').addClass('d-none');
        $('#ai-loading').removeClass('d-none');

        $.ajax({
            url: SITE_URL + 'api/storage/ai/generate',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status === 'success') {
                    currentUploadIds = response.upload_ids;
                    currentAuctionId = response.auction_id;
                    populateDraft(response.data);
                    $('#ai-loading').addClass('d-none');
                    $('#ai-step-review').removeClass('d-none');
                } else {
                    Swal.fire('Error', response.message, 'error');
                    $('#ai-loading').addClass('d-none');
                    $('#ai-step-upload').removeClass('d-none');
                }
            },
            error: function (xhr) {
                console.error(xhr);
                Swal.fire('Error', 'AI Analysis failed: ' + (xhr.responseText || 'Server error'), 'error');
                $('#ai-loading').addClass('d-none');
                $('#ai-step-upload').removeClass('d-none');
            }
        });
    });

    function populateDraft(data) {
        $('#auction_title').val(data.title);

        // Format items as a bulleted list
        let itemsList = '';
        if (Array.isArray(data.items)) {
            itemsList = 'Description\n' + data.items.join('\n');
        }
        $('#auction_items').val(itemsList);

        if (Array.isArray(data.tags)) {
            $('#auction_tags').val(data.tags.join(', '));
        }
        $('#ai-confidence').text((data.confidence * 100).toFixed(0) + '%');

        // Set debt to estimated value if available
        if (data.estimated_value_min) {
            $('#debt_amount').val(data.estimated_value_min);
        }

        // Set unit size if AI estimated it
        if (data.unit_size && data.unit_size !== 'unknown') {
            // Try to match with existing options
            let matched = false;
            $('#ai_unit_size option').each(function () {
                if ($(this).val().toLowerCase().includes(data.unit_size.toLowerCase()) ||
                    data.unit_size.toLowerCase().includes($(this).val().toLowerCase())) {
                    if ($(this).val() !== "") {
                        $('#ai_unit_size').val($(this).val());
                        matched = true;
                        return false;
                    }
                }
            });
            if (!matched) {
                // If no match but we have a value, we can't easily force it into the restricted select
                // But we can at least log it or try to find the closest
                console.log('AI estimated unit size: ' + data.unit_size + ' but no exact match in dropdown.');
            }
        }
    }

    // Publish Action
    $('#btn-publish').on('click', function () {
        const unit_number = $('#unit_number').val().trim();
        const auction_title = $('#auction_title').val().trim();
        const unit_size_raw = $('#ai_unit_size').val() || $('select[name="unit_size"]').val();
        const unit_size = (unit_size_raw && unit_size_raw !== "Select Size...") ? unit_size_raw : "";

        if (!unit_number) {
            Swal.fire('Error', 'Unit Number is required.', 'error');
            return;
        }

        if (!auction_title) {
            Swal.fire('Error', 'Auction Title is required.', 'error');
            return;
        }

        if (!unit_size || unit_size === "") {
            Swal.fire('Error', 'Unit Size is required. Please select a size from the dropdown.', 'error');
            console.error('Unit Size selection failed. Elements:', $('#ai_unit_size').length, $('select[name="unit_size"]').length);
            return;
        }

        const postData = {
            storage_facility_id: $('#storageFacilityDropdown').val(),
            unit_number: unit_number,
            auction_title: auction_title,
            auction_description: $('#auction_items').val(), // Send items as description
            auction_tags: $('#auction_tags').val(),
            auction_terms: $('#auction_terms').val(),
            debt_amount: $('#debt_amount').val() || 0,
            cleaning_deposit: $('#cleaning_deposit').val() || 0,
            duration: $('#duration').val(),
            display_debt: $('#display_debt').is(':checked') ? 1 : 0,
            start_bid: parseFloat($('#debt_amount').val() || 0) * (parseFloat($('#start_bid_percent').val() || 50) / 100),
            unit_size: unit_size,

            // Payment Methods
            cleaning_deposit_payment_type: $('input[name="ai_payment_method"]:checked').val() || "Cash",
            accept_cash: ($('input[name="ai_payment_method"]:checked').val() || "Cash") === 'Cash' ? 1 : 0,
            accept_card: ($('input[name="ai_payment_method"]:checked').val() || "Cash") === 'Card' ? 1 : 0,

            upload_ids: currentUploadIds,
            auction_id: currentAuctionId
        };

        console.log('AI Listing Save Payload:', postData);

        $.ajax({
            url: SITE_URL + 'api/storage/ai/save',
            type: 'POST',
            data: JSON.stringify(postData),
            contentType: 'application/json',
            beforeSend: function () {
                $('#btn-publish').prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Publishing...');
            },
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Published!',
                        text: 'Auction is now live.',
                        showConfirmButton: false,
                        timer: 2000
                    }).then(() => {
                        window.location.href = SITE_URL + 'storage-facility/dashboard';
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                    $('#btn-publish').prop('disabled', false).html('<i class="bi bi-rocket-takeoff me-2"></i> PUBLISH AUCTION');
                }
            }
        });
    });

    function loadFacilities() {
        $.ajax({
            url: SITE_URL + 'api/user/fetch_storage_facilities_by_user',
            type: 'POST',
            success: function (response) {
                if (response.status === 'success') {
                    let opts = '<option value="">Select Facility...</option>';
                    response.data.forEach(fac => {
                        opts += `<option value="${fac.storage_facility_id}">${fac.storage_facility_name}</option>`;
                    });
                    $('#storageFacilityDropdown').html(opts);
                }
            }
        });
    }

    function fetchGlobalRules() {
        $.ajax({
            url: SITE_URL + 'api/companies/storage-facility/get_global_auction_rules',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                console.log("Global Rules fetched:", response);
                if (response.status === 'success' && response.data) {
                    const rules = response.data;

                    // Apply Global Rules to Form
                    if (rules.default_auction_duration) {
                        console.log("Setting duration to:", rules.default_auction_duration);
                        $('#duration').val(rules.default_auction_duration);
                    }

                    if (rules.display_debt_value !== undefined) {
                        $('#display_debt').prop('checked', rules.display_debt_value == 1);
                    }

                    if (rules.cleaning_deposit_amount) {
                        // If 'has_no_deposit' is checked in rules, we might want to handle it, 
                        // but for now let's just set the amount.
                        $('#cleaning_deposit').val(rules.cleaning_deposit_amount);
                    }

                    if (rules.starting_price_percentage !== undefined) {
                        $('#start_bid_percent').val(rules.starting_price_percentage);
                    }

                    // Payment Methods (Single Select)
                    if (rules.accept_card == 1) {
                        $('#ai_payment_card').prop('checked', true);
                    } else {
                        $('#ai_payment_cash').prop('checked', true);
                    }

                    if (rules.default_terms_conditions) {
                        $('#auction_terms').val(rules.default_terms_conditions);
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("Error fetching rules:", textStatus, errorThrown);
            }
        });
    }
});
