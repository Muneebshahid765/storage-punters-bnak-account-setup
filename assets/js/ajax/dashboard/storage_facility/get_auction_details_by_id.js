// Global variables
let selectedImages = []; // Files for upload
let originalUploadIds = []; // Existing image IDs loaded from DB
let removedUploadIds = []; // Removed image IDs
let auctionId = window.location.pathname.split("/").pop(); // Extract Auction ID from URL

$(document).ready(function () {
    // Ensure dashboard loads all dropdowns first, then run login + fetch
    loadAllAuctionDataSequentially().then(() => {
        checkUserLogin();
    });

    // Apply to both fields
    restrictToDecimal('#auction_outstanding_dept');
    restrictToDecimal('#auction_start_bid_amount');
});


// Upload UI
$("#auction-upload-section").append(`
    <div class="col-12 mt-4">
        <label class="input-label-secondary">Upload Images</label>
        <div id="image-upload-area" class="drop-area">
            <p>Drag & drop images here or <button type="button" id="browse-btn">Browse</button></p>
            <input type="file" id="image-input" multiple accept="image/jpeg,image/png" style="display:none;">
            <div id="image-preview-container" class="preview-container mt-3"></div>
        </div>
    </div>
`);

const style = document.createElement("style");
style.textContent = `
    .drop-area { border: 2px dashed #ccc; padding: 20px; border-radius: 10px; text-align: center; background: #fafafa; }
    .preview-container { display: flex; flex-wrap: wrap; gap: 10px; }
    .image-thumb { position: relative; width: 120px; }
    .image-thumb img { width: 100%; border-radius: 6px; }
    .progress { height: 4px; background: #eee; border-radius: 4px; margin-top: 5px; }
    .progress-bar { height: 4px; background: #28a745; width: 0%; transition: width 0.4s; }
    .remove-btn { position: absolute; top: -8px; right: -8px; background: red; color: #fff; border: none; border-radius: 50%; font-size: 14px; width: 20px; height: 20px; cursor: pointer; }
`;
document.head.appendChild(style);

$(document).on('click', '#browse-btn', () => $('#image-input').click());

$(document).on('change', '#image-input', function () {
    handleFiles(this.files);
});

$(document).on('dragover', '#image-upload-area', function (e) {
    e.preventDefault();
    $(this).css('border-color', '#007bff');
});

$(document).on('dragleave', '#image-upload-area', function () {
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
        if (file.size > 1024 * 1024) {
            alert("Image must be less than 1MB.");
            continue;
        }

        selectedImages.push(file);

        const thumb = $('<div class="image-thumb"></div>');
        const img = $(`<img src="${URL.createObjectURL(file)}" alt="">`);
        const progress = $('<div class="progress"><div class="progress-bar"></div></div>');
        const removeBtn = $('<button class="remove-btn">&times;</button>');

        thumb.data("file", file);

        removeBtn.on('click', () => {
            const fileToRemove = thumb.data("file");
            selectedImages = selectedImages.filter(f => f !== fileToRemove);
            thumb.remove();
        });

        thumb.append(img, progress, removeBtn);
        $('#image-preview-container').append(thumb);

        let bar = progress.find('.progress-bar');
        let progressValue = 0;
        let interval = setInterval(() => {
            progressValue += 10;
            bar.css('width', progressValue + '%');
            if (progressValue >= 100) clearInterval(interval);
        }, 30);
    }
}

// Function to check if the user is logged in
function checkUserLogin() {
    $.ajax({
        url: SITE_URL + "api/user/checkUserLogin", // Adjust API endpoint
        type: "GET",
        dataType: "json",
        success: function (response) {
            if (response.status === true && response.user_data.isLoggedIn) {
                isUserLoggedIn = true; // User is logged in
                hasRoleId = response.user_data.roleId;
            } else {
                isUserLoggedIn = false; // User is not logged in
            }

            fetchAuctionDetails(auctionId); // Proceed to fetch auction details after login check
        },
        error: function () {
            isUserLoggedIn = false; // Assume not logged in on error
            fetchAuctionDetails(auctionId); // Proceed anyway
        }
    });
}

// Fetch auction details from API
function fetchAuctionDetails(auctionId) {


    $.ajax({
        url: SITE_URL + "api/auctions/getAuctionById",
        type: "POST",
        contentType: "application/json",
        dataType: "json",
        data: JSON.stringify({ auctionId: auctionId, mode: "edit" }),
        success: function (response) {


            if (response.status === "success" && response.data) {
                let auction = response.data[0];
                let userData = response.user_data;

                $("#auction_id").val(auction.auction_id || "");

                // ✅ Dropdowns
                if ($("#auctionTypeDropdown").length && auction.auction_status_id)
                    $("#auctionTypeDropdown").val(auction.auction_status_id);

                if ($("#storageFacilityDropdown").length && auction.auction_storage_facility_id)
                    $("#storageFacilityDropdown").val(auction.auction_storage_facility_id);

                if ($("#auctionCategoryDropdown").length && auction.auction_category_id)
                    $("#auctionCategoryDropdown").val(auction.auction_category_id);

                // ✅ Inputs (if these exist in your form, update accordingly)
                $("#auction_unit_number").val(auction.auction_unit_number || "");
                $("#auction_description").val(auction.auction_description || "");
                $("#auction_tags").val(auction.auction_tags || "");
                $("#auction_disclose_unit_number_on_website").val(auction.auction_disclose_unit_number_on_website || 0);
                $("#auction_outstanding_dept").val(auction.auction_outstanding_dept || "");
                $("#auction_start_bid_amount").val(auction.auction_start_bid_amount || "");

                $("#auction_start_date").val(auction.auction_start_date || "");
                $("#auction_start_time").val(auction.auction_start_time);
                $("#auction_end_date").val(auction.auction_end_date || "");

                $('#auction_end_time').empty().append(
                    $('<option>', {
                        value: auction.auction_end_time,
                        text: auction.auction_end_time,
                        selected: true
                    })
                );

                // Fill start and end values
                $("#auction_start_date").val(auction.auction_start_date || "");
                $("#auction_start_time").val(auction.auction_start_time);
                $("#auction_end_date").val(auction.auction_end_date || "");

                $('#auction_end_time').empty().append(
                    $('<option>', {
                        value: auction.auction_end_time,
                        text: auction.auction_end_time,
                        selected: true
                    })
                );
                $('#auction_cleaning_deposit').val(auction.auction_cleaning_deposit_price || "");
                $('#auction_cleaning_deposit_payment_type').val(auction.auction_cleaning_deposit_payment_type || "");


                // ---- Calculate and preselect auction_set_days ----
                if (auction.auction_start_date && auction.auction_start_time && auction.auction_end_date && auction.auction_end_time) {
                    // Parse start datetime
                    const [sYear, sMonth, sDay] = auction.auction_start_date.split('-').map(Number);
                    let [sTimePart, sAmpm] = auction.auction_start_time.split(' ');
                    let [sHours, sMinutes] = sTimePart.split(':').map(Number);
                    if (sAmpm === 'PM' && sHours < 12) sHours += 12;
                    if (sAmpm === 'AM' && sHours === 12) sHours = 0;
                    const startDateTime = new Date(sYear, sMonth - 1, sDay, sHours, sMinutes, 0);

                    // Parse end datetime
                    const [eYear, eMonth, eDay] = auction.auction_end_date.split('-').map(Number);
                    let [eTimePart, eAmpm] = auction.auction_end_time.split(' ');
                    let [eHours, eMinutes] = eTimePart.split(':').map(Number);
                    if (eAmpm === 'PM' && eHours < 12) eHours += 12;
                    if (eAmpm === 'AM' && eHours === 12) eHours = 0;
                    const endDateTime = new Date(eYear, eMonth - 1, eDay, eHours, eMinutes, 0);

                    // Calculate difference in days
                    const diffMs = endDateTime.getTime() - startDateTime.getTime();
                    const diffDays = Math.round(diffMs / (24 * 60 * 60 * 1000));

                    // Preselect auction_set_days (fallback to 3 if invalid)
                    const setDays = diffDays >= 3 && diffDays <= 14 ? diffDays : 3;
                    $("#auction_set_days").val(setDays);
                }


                $("#auction_youtube_video_url").val(auction.auction_youtube_video_url || "");
                $("#auction_unit_size").val(auction.auction_unit_size || "");
                $("#auction_additional_features").val(auction.auction_additional_features || "");
                $("#auction_status_id").val(auction.auction_status_id || "");

                $('#image-preview-container').empty();
                if (Array.isArray(auction.auction_cover_images)) {
                    auction.auction_cover_images.forEach(img => {
                        originalUploadIds.push(img.id);
                        const thumb = $(`
            <div class="image-thumb existing" data-upload-id="${img.id}">
                <img src="${img.path}" alt="Image">
                <button class="remove-btn">&times;</button>
            </div>
        `);
                        thumb.find('.remove-btn').on('click', () => {
                            if (img.id) {
                                removedUploadIds.push(img.id);
                            }
                            thumb.remove();
                        });
                        $('#image-preview-container').append(thumb);
                    });
                }

                if (auction.auction_status_id == 11) {
                    $('#save-auction-updates-btn').closest('div').remove(); // Removes the parent container
                }

                showNotification(response.message, "success");
            }

        },
        error: function (jqXHR, textStatus, errorThrown) {
            let errorMessage = "Something went wrong. Try again.";

            if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                errorMessage = jqXHR.responseJSON.message;
            }

            showNotification(errorMessage, "error");
        }
    });
}

function convertTo24Hr(timeStr) {
    if (!timeStr) return "";
    let [time, modifier] = timeStr.split(" ");
    if (!modifier) return time; // Already in 24hr format

    let [hours, minutes] = time.split(":");
    hours = parseInt(hours);

    if (modifier === "PM" && hours !== 12) {
        hours += 12;
    }
    if (modifier === "AM" && hours === 12) {
        hours = 0;
    }

    return `${hours.toString().padStart(2, "0")}:${minutes}`;
}



$('#auction-update-form').submit(async function (event) {
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


    // Validate dropdowns
    const storageFacilityId = parseInt($('#storageFacilityDropdown').val());
    const auctionCategoryId = parseInt($('#auctionCategoryDropdown').val());
    const auctionTypeId = parseInt($('#auctionTypeDropdown').val());

    if (!storageFacilityId) showError('storageFacilityDropdown', 'Please select a storage facility.');
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


    $('#save-auction-updates-btn').html('<span class="spinner-border spinner-border-sm"></span> Uploading...').attr('disabled', true);

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
            $("#save-auction-updates-btn").html("Save Updates").attr("disabled", false);
            showNotification('Image upload failed. Please try again.', 'error');
            return;
        }
    }

    $('#save-auction-updates-btn').html('<span class="spinner-border spinner-border-sm"></span> Updating auction...');


    let formData = {
        auctionId: auctionId,
        description: $('#auction_description').val(),
        unit_number: $('#auction_unit_number').val(),
        disclose_unit_number_on_website: $('#auction_disclose_unit_number_on_website').val(),
        outstanding_dept: $('#auction_outstanding_dept').val(),
        starting_price: $('#auction_start_bid_amount').val(),
        start_date: $('#auction_start_date').val(),
        start_time: $('#auction_start_time').val(),
        end_date: $('#auction_end_date').val(),
        end_time: $('#auction_end_time').val(),
        youtube_video_url: $('#auction_youtube_video_url').val(),
        unit_size: $('#auction_unit_size').val(),
        tags: $('#auction_tags').val(),
        additional_features: $('#auction_additional_features').val(),
        storage_facility_id: $('#storageFacilityDropdown').val(),
        auction_category_id: $('#auctionCategoryDropdown').val(),
        auction_type_id: $('#auctionTypeDropdown').val(),
        cleaning_deposit_price: $('#auction_cleaning_deposit').val(),
        cleaning_deposit_payment_type: $('#auction_cleaning_deposit_payment_type').val(),
        upload_ids: uploadIds,
        remove_upload_ids: removedUploadIds
    };

    $.ajax({
        url: SITE_URL + 'api/auction/storage-facility/update', // Update with actual API endpoint
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        beforeSend: function () {
            console.log('Updating auction details...');
        },
        success: function (response) {

            $("#save-auction-updates-btn").html("Save Updates").attr("disabled", false);

            if (response.status === "success") {
                showNotification(response.message, "success");

                // ✅ Reset image-related state after successful submission
                removedUploadIds = [];
                selectedImages = [];
                uploadIds = [];

                // Optionally reload auction data to reflect changes
                fetchAuctionDetails(auctionId);

            } else {
                showNotification(response.message, "error");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {

            $("#save-auction-updates-btn").html("Save Updates").attr("disabled", false);
            let errorMessage = "Something went wrong. Try again.";

            if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                errorMessage = jqXHR.responseJSON.message;
            }

            showNotification(errorMessage, "error");
        }
    });
});

