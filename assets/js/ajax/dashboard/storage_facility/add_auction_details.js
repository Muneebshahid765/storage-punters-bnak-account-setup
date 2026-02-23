$(document).ready(function () {
    loadAllAuctionDataSequentially().then(function () {
        $("#auctionTypeDropdown").val(1);
        fetchGlobalRules();
    });
    // Apply to both fields
    restrictToDecimal('#auction_outstanding_dept');
    restrictToDecimal('#auction_start_bid_amount');

    loadMasterProfiles();

    $('#masterProfileDropdown').change(function () {
        if (this.value) applyMasterProfile(this.value);
    });

    $('#ai-analyze-btn').click(function () {
        analyzeAuctionContents();
    });
});

// Inject this upload area into your HTML form
$("#auction-upload-section").append(`
    <div class="col-12 mt-4">
        <label class="input-label-secondary">Upload Images</label>
        <div id="image-upload-area" class="drop-area">
            <p>Drag & drop images here or <button type="button" id="browse-btn" type="button">Browse</button></p>
            <input type="file" id="image-input" multiple accept="image/jpeg,image/png" style="display:none;">
            <div id="image-preview-container" class="preview-container mt-3"></div>
        </div>
    </div>
`);

// CSS (you can place this in a separate stylesheet or inline)
const style = document.createElement("style");
style.textContent = `
    .drop-area {
        border: 2px dashed #ccc;
        padding: 20px;
        border-radius: 10px;
        text-align: center;
        background: #fafafa;
        position: relative;
    }
    .drop-area p { margin-bottom: 0; }
    .preview-container { display: flex; flex-wrap: wrap; gap: 10px; }
    .image-thumb { position: relative; width: 120px; }
    .image-thumb img { width: 100%; border-radius: 6px; }
    .progress { height: 4px; background: #eee; border-radius: 4px; margin-top: 5px; }
    .progress-bar { height: 4px; background: #28a745; width: 0%; transition: width 0.4s; }
    .remove-btn {
        position: absolute;
        top: -8px;
        right: -8px;
        background: red;
        color: #fff;
        border: none;
        border-radius: 50%;
        font-size: 14px;
        width: 20px;
        height: 20px;
        cursor: pointer;
    }
`;
document.head.appendChild(style);

// JavaScript logic
let selectedImages = [];

$(document).on('click', '#browse-btn', () => $('#image-input').click());

$(document).on('change', '#image-input', function () {
    handleFiles(this.files);
});

$(document).on('dragover', '#image-upload-area', function (e) {
    e.preventDefault();
    $(this).css('border-color', '#007bff');
});

$(document).on('dragleave', '#image-upload-area', function (e) {
    $(this).css('border-color', '#ccc');
});

$(document).on('drop', '#image-upload-area', function (e) {
    e.preventDefault();
    $(this).css('border-color', '#ccc');
    handleFiles(e.originalEvent.dataTransfer.files);
});

function handleFiles(files) {
    for (let file of files) {
        if (!['image/jpeg', 'image/png'].includes(file.type)) {
            alert("Only JPG and PNG files are allowed.");
            continue;
        }
        if (file.size > 10 * 1024 * 1024) {
            alert("Image must be less than 10MB.");
            continue;
        }


        selectedImages.push(file);

        const thumb = $(`<div class="image-thumb"></div>`);
        const img = $(`<img src="${URL.createObjectURL(file)}" alt="">`);
        const progress = $('<div class="progress"><div class="progress-bar"></div></div>');
        const removeBtn = $('<button class="remove-btn">&times;</button>');

        // Store a reference to the file in the DOM element
        thumb.data("file", file);

        removeBtn.on('click', () => {
            const fileToRemove = thumb.data("file");
            selectedImages = selectedImages.filter(f => f !== fileToRemove);
            thumb.remove();
        });

        thumb.append(img, progress, removeBtn);
        $('#image-preview-container').append(thumb);

        // Simulate progress
        let bar = progress.find('.progress-bar');
        let progressValue = 0;
        let interval = setInterval(() => {
            progressValue += 10;
            bar.css('width', progressValue + '%');
            if (progressValue >= 100) clearInterval(interval);
        }, 30);
    }
}



$('#auction-add-form').submit(async function (event) {
    event.preventDefault();
    $('.error-message').remove();

    let isValid = true;
    let firstInvalid = null;

    function showError(inputId, message) {
        const $el = $(`#${inputId}`);
        $el.after(`<p class="error-message text-yellow text-error">${message}</p>`);
        if (!firstInvalid) firstInvalid = $el;
        isValid = false;
    }

    // Dropdown validations
    const storageFacilityIds = $('#storageFacilityDropdown').val(); // Array for multi-select
    const auctionCategoryId = parseInt($('#auctionCategoryDropdown').val());
    const auctionTypeId = parseInt($('#auctionTypeDropdown').val());

    if (!storageFacilityIds || storageFacilityIds.length === 0) showError('storageFacilityDropdown', 'Please select at least one storage facility.');
    if (!auctionCategoryId) showError('auctionCategoryDropdown', 'Please select an auction category.');
    if (!auctionTypeId) showError('auctionTypeDropdown', 'Please select an auction type.');

    // Date/time validations
    const startDate = $('#auction_start_date').val();
    const startTime = $('#auction_start_time').val();
    const endDate = $('#auction_end_date').val();
    const endTime = $('#auction_end_time').val();

    if (!startDate) showError('auction_start_date', 'Start date is required.');
    if (!startTime) showError('auction_start_time', 'Start time is required.');
    if (!endDate) showError('auction_end_date', 'End date is required.');
    if (!endTime) showError('auction_end_time', 'End time is required.');


    if (startDate && startTime) {
        // Get Brisbane date/time
        const now = new Date();
        const brisbaneOffsetMinutes = 10 * 60;
        const brisbaneNow = new Date(now.getTime() + (brisbaneOffsetMinutes + now.getTimezoneOffset()) * 60000);

        const [year, month, day] = startDate.split('-').map(Number);
        const [timePart, meridian] = startTime.split(' ');
        let [hour, minute] = timePart.split(':').map(Number);

        if (meridian === 'PM' && hour < 12) hour += 12;
        if (meridian === 'AM' && hour === 12) hour = 0;

        const selectedStart = new Date(year, month - 1, day, hour, minute, 0);

        if (selectedStart < brisbaneNow) {
            showError('auction_start_time', 'Start date/time must be in the future (Brisbane time).');
        }
    }


    // Additional field validations
    const youtubeUrl = $('#auction_youtube_video_url').val().trim();
    if (youtubeUrl && !/^https?:\/\/(www\.)?youtube\.com\/.+$/.test(youtubeUrl)) {
        showError('auction_youtube_video_url', 'Enter a valid YouTube URL.');
    }

    const startingPrice = $('#auction_start_bid_amount').val().trim();
    if (!startingPrice || isNaN(startingPrice) || parseFloat(startingPrice) < 0) {
        showError('auction_start_bid_amount', 'Starting price must be a positive number.');
    }

    const outstandingDept = $('#auction_outstanding_dept').val().trim();
    if (outstandingDept && (isNaN(outstandingDept) || parseFloat(outstandingDept) < 0)) {
        showError('auction_outstanding_dept', 'Outstanding debt must be a valid number.');
    }

    // Text input validations
    const description = $('#auction_description').val().trim();
    const unitNumber = $('#auction_unit_number').val().trim();
    const unitSize = $('#auction_unit_size').val().trim();
    const tags = $('#auction_tags').val().trim();
    const features = $('#auction_additional_features').val().trim();
    const discloseUnit = $('#auction_disclose_unit_number_on_website').val();

    if (!description || description.length < 10) {
        showError('auction_description', 'Description is required and must be at least 10 characters.');
    }
    if (!unitNumber || unitNumber.length < 1) {
        showError('auction_unit_number', 'Unit number is required.');
    }
    if (!unitSize) {
        showError('auction_unit_size', 'Unit size is required.');
    }
    if (!tags) {
        showError('auction_tags', 'At least one tag is required.');
    }
    if (!features) {
        showError('auction_additional_features', 'Please list at least one feature.');
    }
    if (!discloseUnit) {
        showError('auction_disclose_unit_number_on_website', 'Please select an option.');
    }

    // ✅ Cleaning Deposit Validations
    const cleaningDepositPrice = $('#auction_cleaning_deposit').val();
    const cleaningDepositPaymentType = $('#auction_cleaning_deposit_payment_type').val();

    if (!cleaningDepositPrice || isNaN(cleaningDepositPrice) || parseFloat(cleaningDepositPrice) <= 0) {
        showError('auction_cleaning_deposit', 'Please select a valid cleaning deposit amount.');
    }

    if (!cleaningDepositPaymentType) {
        showError('auction_cleaning_deposit_payment_type', 'Please select a cleaning deposit payment type.');
    }

    if (!isValid) {
        firstInvalid?.focus();
        return;
    }

    $('#save-auction-add-btn').html('<span class="spinner-border spinner-border-sm"></span> Uploading...').attr('disabled', true);

    let uploadIds = [];

    if (selectedImages.length) {
        const imageForm = new FormData();
        selectedImages.forEach(img => imageForm.append('images[]', img));

        try {
            const response = await $.ajax({
                url: SITE_URL + 'api/uploads/bulk-upload-images',
                type: 'POST',
                data: imageForm,
                contentType: false,
                processData: false,
                dataType: 'json'
            });
            if (response.status === 'success') {
                uploadIds = response.upload_ids;
            }
        } catch (err) {
            $('#save-auction-add-btn').html('Add Auction').attr('disabled', false);
            showNotification('Image upload failed. Please try again.', 'error');
            return;
        }
    }

    $('#save-auction-add-btn').html('<span class="spinner-border spinner-border-sm"></span> Creating your auction...');

    let formData = {
        description,
        unit_number: unitNumber,
        disclose_unit_number_on_website: discloseUnit,
        outstanding_dept: outstandingDept,
        starting_price: startingPrice,
        start_date: startDate,
        start_time: startTime,
        end_date: endDate,
        end_time: endTime,
        youtube_video_url: youtubeUrl,
        unit_size: unitSize,
        tags,
        additional_features: features,
        storage_facility_id: storageFacilityIds[0], // Keep for backward compat if needed
        location_ids: storageFacilityIds, // ✅ NEW: Support multi-insertion
        auction_category_id: auctionCategoryId,
        auction_type_id: auctionTypeId,
        cleaning_deposit_price: cleaningDepositPrice,
        auction_type_id: auctionTypeId,
        cleaning_deposit_price: cleaningDepositPrice,
        cleaning_deposit_payment_type: $('input[name="auction_payment_method"]:checked').val() || "Cash",
        accept_cash: ($('input[name="auction_payment_method"]:checked').val() || "Cash") === 'Cash' ? 1 : 0,
        accept_card: ($('input[name="auction_payment_method"]:checked').val() || "Cash") === 'Card' ? 1 : 0,
        upload_ids: uploadIds
    };

    $.ajax({
        url: SITE_URL + 'api/auction/storage-facility/add',
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        success: function (response) {
            // $("#save-auction-add-btn").html("Add Auction").attr("disabled", false);
            if (response.status === "success") {
                showNotification(response.message, "success");

                // Delay for user to see notification (optional)
                setTimeout(() => {
                    const auctionId = response.data?.auction_id;
                    if (auctionId) {
                        window.location.href = SITE_URL + 'storage-facility/dashboard';
                    }
                }, 1000); // wait 1s before redirect

            } else {
                showNotification(response.message, "error");
            }
        },
        error: function (jqXHR) {
            $("#save-auction-add-btn").html("Add Auction").attr("disabled", false);
            const errorMessage = jqXHR.responseJSON?.message || "Something went wrong. Try again.";
            showNotification(errorMessage, "error");
        }
    });
    function fetchGlobalRules() {
        $.ajax({
            url: SITE_URL + 'api/companies/storage-facility/get_global_auction_rules',
            type: 'GET',
            dataType: 'json',
            success: function (response) {
                if (response.status === 'success' && response.data) {
                    const rules = response.data;

                    // Apply Global Rules to Form
                    if (rules.default_auction_duration) {
                        // Check if option exists, if not add it
                        if ($("#auction_set_days option[value='" + rules.default_auction_duration + "']").length === 0) {
                            $('#auction_set_days').append(`<option value="${rules.default_auction_duration}" data-type="day">${rules.default_auction_duration} Days</option>`);
                        }
                        $('#auction_set_days').val(rules.default_auction_duration).trigger('change');
                    }

                    if (rules.display_debt_value !== undefined) {
                        $('#auction_disclose_unit_number_on_website').val(rules.display_debt_value == 1 ? '1' : '2');
                    }

                    if (rules.cleaning_deposit_amount) {
                        // Check if option exists, if not add it
                        const depVal = parseFloat(rules.cleaning_deposit_amount).toFixed(2);
                        if ($("#auction_cleaning_deposit option[value='" + depVal + "']").length === 0) {
                            $('#auction_cleaning_deposit').append(`<option value="${depVal}">$${depVal} AUD</option>`);
                        }
                        $('#auction_cleaning_deposit').val(depVal);
                    }

                    if (rules.starting_price_percentage !== undefined) {
                        // In this form it's a dollar amount, but maybe we can't easily set it without debt.
                    }

                    if (rules.default_terms_conditions) {
                        $('#auction_description').val(rules.default_terms_conditions);
                    }

                    // Payment Methods
                    // Payment Methods
                    if (rules.accept_card == 1) {
                        $('#auction_payment_card').prop('checked', true);
                    } else {
                        $('#auction_payment_cash').prop('checked', true);
                    }
                }
            },
            error: function (jqXHR, textStatus, errorThrown) {
                console.error("Error fetching rules:", textStatus, errorThrown);
            }
        });
    }
});


