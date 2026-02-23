// Global variables
let selectedImages = []; // Files for upload
let originalUploadIds = []; // Existing image IDs loaded from DB
let removedUploadIds = []; // Removed image IDs
let storageFacilityId = window.location.pathname.split("/").pop(); // Extract Auction ID from URL


function loadAllCompanyDataSequentially() {
    getAllCompanies()
        .then(function (typeResponse) {
            if (typeResponse.status === "success") {
                appendCompanies(typeResponse.data);
            }

            return fetchStorageFacilityDetails(storageFacilityId);
        })
        .catch(function (error) {
            console.error("Error loading auction data:", error);
            showNotification("Failed to load auction data", "error");
        });
}

$(document).ready(function () {

    /*
    const timezones = moment.tz.names();
    const guessedZone = moment.tz.guess();

    $.each(timezones, function (index, timezone) {
        $('#storage_facility_timezone').append(
            $('<option>', {
                value: timezone,
                text: timezone
            })
        );
    });

    // Optionally preselect user's timezone
    $('#storage_facility_timezone').val(guessedZone);
    */

    loadAllCompanyDataSequentially()


});


// Upload UI
$("#storage-facility-upload-section").append(`
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


// Fetch auction details from API
function fetchStorageFacilityDetails(storageFacilityId) {


    $.ajax({
        url: SITE_URL + "api/storage/storage-facility/get_by_storage_id",
        type: "POST",
        contentType: "application/json",
        dataType: "json",
        data: JSON.stringify({ storageFacilityId: storageFacilityId }),
        success: function (response) {


            if (response.status === "success" && response.data) {
                let facility = response.data[0];


                // Basic fields
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

                // Set timezone if it's available after timezones are loaded
                const timezoneSelect = $("#storage_facility_timezone");
                const setTimezone = () => {
                    if (facility.storage_facility_timezone) {
                        timezoneSelect.val(facility.storage_facility_timezone);
                    }
                };

                // Wait for timezone options to be populated if using moment.tz.names()
                if (timezoneSelect.find("option").length > 1) {
                    setTimezone();
                } else {
                    setTimeout(setTimezone, 500); // fallback delay to ensure dropdown is ready
                }


                $('#image-preview-container').empty();
                if (Array.isArray(facility.storage_facility_cover_images)) {
                    facility.storage_facility_cover_images.forEach(img => {
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



$('#storage-facility-update-form').submit(async function (event) {
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


    let formData = {
        storageFacilityId: storageFacilityId, // Assuming you have this defined elsewhere
        companyId: $("#companiesDropdown").val(),
        storage_facility_name: $("#storage_facility_name").val().trim(),
        storage_facility_website: $("#storage_facility_website").val().trim(),
        storage_facility_description: $("#storage_facility_description").val().trim(),
        storage_facility_contact_email: $("#storage_facility_contact_email").val().trim(),
        storage_facility_additional_email: $("#storage_facility_additional_email").val().trim(),
        storage_facility_timezone: $("#storage_facility_timezone").val(),
        storage_facility_office_hours: $("#storage_facility_office_hours").val().trim(),
        storage_facility_payout_details: $("#storage_facility_payout_details").val().trim(),
        storage_facility_youtube_video_url: $("#storage_facility_youtube_video_url").val().trim(),
        storage_facility_branding_logo: $("#storage_facility_branding_logo").val().trim(),
        storage_facility_notice_to_buyers: $("#storage_facility_notice_to_buyers").val().trim(),
        storage_facility_cell_phone: $("#cell_phone").val().trim(),

        storage_facility_street_address: $("#address").val().trim(),
        storage_facility_apt_suite: $("#apt_suite").val().trim(),
        storage_facility_city: $("#city").val().trim(),
        storage_facility_zip: $("#zip").val().trim(),
        storage_facility_country: $("#country").val(),
        storage_facility_state: $("#state").val()
    };

    // Validations
    if (!formData.companyId) showError("companiesDropdown", "Please select a company.");
    if (!formData.storage_facility_name) showError("storage_facility_name", "Facility name is required.");
    if (!formData.storage_facility_website || !/^https?:\/\/.+\..+/.test(formData.storage_facility_website)) {
        showError("storage_facility_website", "Copy and paste your website URL so it contains https//.");
    }
    // if (!formData.storage_facility_description) showError("storage_facility_description", "Description is required.");
    if (!formData.storage_facility_contact_email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.storage_facility_contact_email)) {
        showError("storage_facility_contact_email", "Enter a valid contact email.");
    }
    /*if (formData.storage_facility_additional_email &&
        !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.storage_facility_additional_email)) {
        showError("storage_facility_additional_email", "Enter a valid additional email.");
    }*/
    if (!formData.storage_facility_timezone) showError("storage_facility_timezone", "Select a timezone.");
    if (!formData.storage_facility_office_hours) showError("storage_facility_office_hours", "Office hours required.");
    // if (!formData.storage_facility_payout_details) showError("storage_facility_payout_details", "Payout details required.");
    if (formData.storage_facility_youtube_video_url &&
        !/^https?:\/\/(www\.)?youtube\.com\/.+$/.test(formData.storage_facility_youtube_video_url)) {
        showError("storage_facility_youtube_video_url", "Copy and paste your website URL so it contains https//.");
    }

    if (!formData.storage_facility_notice_to_buyers) showError("storage_facility_notice_to_buyers", "Notice to Buyers required.");

    if (!formData.storage_facility_cell_phone || !/^[\d\s()+-]{6,}$/.test(formData.storage_facility_cell_phone)) {
        showError("cell_phone", "Enter a valid phone number.");
    }
    if (!formData.storage_facility_street_address) showError("address", "Street address is required.");
    if (!formData.storage_facility_city) showError("city", "City is required.");
    if (!formData.storage_facility_zip || !/^\d{4,10}$/.test(formData.storage_facility_zip)) {
        showError("zip", "Enter a valid ZIP/postal code.");
    }
    if (!formData.storage_facility_country) showError("country", "Country is required.");
    if (!formData.storage_facility_state) showError("state", "State is required.");

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
    formData.upload_ids = uploadIds;
    formData.remove_upload_ids = removedUploadIds;

    $("#save-storage-facility-updates-btn").html('<span class="spinner-border spinner-border-sm"></span> Saving changes...').attr("disabled", true);

    $.ajax({
        url: SITE_URL + 'api/storage/storage-facility/update', // Update with actual API endpoint
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        beforeSend: function () {
            console.log('Updating storage facility details...');
        },
        success: function (response) {

            $("#save-storage-facility-updates-btn").html("Save Updates").attr("disabled", false);

            if (response.status === "success") {

                showNotification(response.message, "success");

                removedUploadIds = [];
                selectedImages = [];
                uploadIds = [];


                fetchStorageFacilityDetails(storageFacilityId);

            } else {
                showNotification(response.message, "error");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {

            $("#save-storage-facility-updates-btn").html("Save Updates").attr("disabled", false);

            let errorMessage = "Something went wrong. Try again.";

            if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                errorMessage = jqXHR.responseJSON.message;
            }

            showNotification(errorMessage, "error");
        }
    });
});

