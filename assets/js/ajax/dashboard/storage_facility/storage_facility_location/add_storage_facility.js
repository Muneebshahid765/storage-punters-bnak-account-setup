function loadAllCompanyDataSequentially() {
    getAllCompanies()
        .then(function (typeResponse) {
            if (typeResponse.status === "success") {
                appendCompanies(typeResponse.data);
            }
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

    loadAllCompanyDataSequentially();
});


// Inject this upload area into your HTML form
$("#storage-facility-upload-section").append(`
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



// Handle "Add New Location" button click to reset form
$(document).on('click', '.__btn__add__new__storage__facility', function () {
    $('#storage-facility-add-form')[0].reset();
    $('#storage_facility_id').val(''); // Clear ID
    $('#addStorageFacilityModalLabel').text('Add New Facility Location');
    $('#save-storage-facility-add-btn').text('Add Facility');
    $('#image-preview-container').empty();
    selectedImages = [];

    // Reset timezone and country to defaults if needed
    $('#storage_facility_timezone').val('');
    $('#country').val('Australia');
    $('#state').val('');
});

$('#storage-facility-add-form').submit(async function (event) {
    event.preventDefault(); // Prevent default form submission
    $('.error-message').remove();

    let isValid = true;
    let firstInvalid = null;

    function showError(inputId, message) {
        const $el = $(`#${inputId}`);
        $el.after(`<p class="error-message text-yellow text-error">${message}</p>`);
        if (!firstInvalid) firstInvalid = $el;
        isValid = false;
    }

    const storageFacilityId = $('#storage_facility_id').val();

    // Collect values
    const formData = {
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
        storage_facility_apt_suite: $("#apt_suite").val().trim(), // optional
        storage_facility_city: $("#city").val().trim(),
        storage_facility_zip: $("#zip").val().trim(),
        storage_facility_country: $("#country").val(),
        storage_facility_state: $("#state").val()
    };

    if (storageFacilityId) {
        formData.storageFacilityId = storageFacilityId;
    }

    // Required field validation
    if (!formData.companyId) showError("companiesDropdown", "Please select a company.");
    if (!formData.storage_facility_name) showError("storage_facility_name", "Facility name is required.");
    /* if (!formData.storage_facility_website || !/^https?:\/\/.+\..+/.test(formData.storage_facility_website)) {
         showError("storage_facility_website", "Copy and paste your website URL so it contains https//.");
     }*/
    // if (!formData.storage_facility_description) showError("storage_facility_description", "Description is required.");
    if (!formData.storage_facility_contact_email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.storage_facility_contact_email)) {
        showError("storage_facility_contact_email", "Enter a valid contact email.");
    }
    /*if (formData.storage_facility_additional_email &&
        !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(formData.storage_facility_additional_email)) {
        showError("storage_facility_additional_email", "Enter a valid additional email.");
    }*/
    if (!formData.storage_facility_timezone) showError("storage_facility_timezone", "Select a timezone.");
    // if (!formData.storage_facility_office_hours) showError("storage_facility_office_hours", "Office hours required.");
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


    const btnElement = $("#save-storage-facility-add-btn");
    const originalBtnText = btnElement.text();
    const processingText = storageFacilityId ? 'Saving changes...' : 'Creating your storage facility...';

    btnElement.html(`<span class="spinner-border spinner-border-sm"></span> ${processingText}`).attr("disabled", true);

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
            btnElement.html(originalBtnText).attr('disabled', false);
            showNotification('Image upload failed. Please try again.', 'error');
            return;
        }
    }

    formData.upload_ids = uploadIds;

    const apiUrl = storageFacilityId
        ? SITE_URL + 'api/storage/storage-facility/update'
        : SITE_URL + 'api/storage/storage-facility/add';

    $.ajax({
        url: apiUrl,
        type: 'POST',
        contentType: 'application/json',
        data: JSON.stringify(formData),
        dataType: 'json',
        beforeSend: function () {
            console.log(storageFacilityId ? 'Updating storage facility details...' : 'Adding storage facility details...');
        },
        success: function (response) {

            btnElement.html(originalBtnText).attr("disabled", false);

            if (response.status === "success") {
                showNotification(response.message, "success");

                // Clear the form
                $('#storage-facility-add-form')[0].reset();
                $('#storage_facility_id').val('');

                // Close the modal
                $('#addStorageFacilityModal').modal('hide');

                // Refresh the DataTable
                if ($.fn.DataTable.isDataTable('#storage-facility-table')) {
                    $('#storage-facility-table').DataTable().ajax.reload(null, false);
                } else {
                    // Fallback if table isn't initialized for some reason (rare)
                    location.reload();
                }

                // Reset upload area
                $('#image-preview-container').empty();
                selectedImages = [];

            } else {
                showNotification(response.message, "error");
            }
        },
        error: function (jqXHR, textStatus, errorThrown) {

            btnElement.html(originalBtnText).attr("disabled", false);
            let errorMessage = "Something went wrong. Try again.";

            if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                errorMessage = jqXHR.responseJSON.message;
            }

            showNotification(errorMessage, "error");
        }
    });
});

