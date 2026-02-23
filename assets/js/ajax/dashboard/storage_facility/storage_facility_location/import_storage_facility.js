$(document).ready(function () {
    const fileInput = document.getElementById('csv_file_input');
    const $fileNameDisplay = $('#file-name-display');
    const $dropZone = $('#csv-drop-zone');

    const updateSelectedFile = (file) => {
        if (!fileInput) return;
        const dataTransfer = new DataTransfer();
        dataTransfer.items.add(file);
        fileInput.files = dataTransfer.files;
        $fileNameDisplay.text(file.name);
    };

    // Handle File Input Change
    $('#csv_file_input').on('change', function () {
        const file = this.files[0];
        if (file) {
            $fileNameDisplay.text(file.name);
        } else {
            $fileNameDisplay.text('');
        }
    });

    if ($dropZone.length) {
        const highlight = () => $dropZone.addClass('dragover-active');
        const unhighlight = () => $dropZone.removeClass('dragover-active');
        const preventDefaults = (e) => {
            e.preventDefault();
            e.stopPropagation();
        };

        ['dragenter', 'dragover', 'dragleave', 'drop'].forEach(eventName => {
            $dropZone.on(eventName, preventDefaults);
        });

        ['dragenter', 'dragover'].forEach(eventName => {
            $dropZone.on(eventName, highlight);
        });

        ['dragleave', 'dragend', 'drop'].forEach(eventName => {
            $dropZone.on(eventName, unhighlight);
        });

        $dropZone.on('drop', function (e) {
            const dt = e.originalEvent.dataTransfer;
            if (!dt || !dt.files || dt.files.length === 0) return;
            const file = dt.files[0];
            if (file && file.name.toLowerCase().endsWith('.csv')) {
                updateSelectedFile(file);
            } else {
                Swal.fire({
                    icon: 'warning',
                    title: 'Invalid File',
                    text: 'Please drop a CSV file only.'
                });
            }
        });
    }

    // Handle Import Button Click
    $('#btn-import-csv').on('click', function () {
        const file = fileInput.files[0];

        if (!file) {
            Swal.fire({
                icon: 'warning',
                title: 'No File Selected',
                text: 'Please select a CSV file to import.',
            });
            return;
        }

        const formData = new FormData();
        formData.append('csv_file', file);

        // Show loading state
        Swal.fire({
            title: 'Importing...',
            text: 'Please wait while we process your file.',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        // Send AJAX request
        $.ajax({
            url: SITE_URL + 'api/storage/storage-facility/import',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Import Successful',
                        text: response.message,
                        timer: 2000,
                        showConfirmButton: false
                    }).then(() => {
                        // Close modal and refresh table
                        $('#importStorageFacilityModal').modal('hide');
                        $('#storage-facility-table').DataTable().ajax.reload();
                        // Reset file input
                        fileInput.value = '';
                        $('#file-name-display').text('');
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Import Failed',
                        text: response.message || 'Unknown error occurred.',
                    });
                }
            },
            error: function (xhr, status, error) {
                let errorMessage = 'An error occurred during import.';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMessage = xhr.responseJSON.message;
                }
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: errorMessage,
                });
            }
        });
    });
});
