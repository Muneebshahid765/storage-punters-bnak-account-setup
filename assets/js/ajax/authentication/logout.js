$(document).ready(function () {
    $(document).on('click', '.btn__logout', function (e) {
        e.preventDefault();

        Swal.fire({
            title: 'Are you sure?',
            text: "You will be logged out of your session.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#198754',
            cancelButtonColor: '#d33',
            confirmButtonText: 'Yes, logout!'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: base_url + 'auth/logout',
                    type: 'POST',
                    contentType: 'application/json',
                    dataType: 'json',
                    success: function (response) {
                        window.location.href = base_url;
                    },
                    error: function (xhr) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Server Error',
                            text: xhr.responseJSON?.message || 'An unexpected error occurred. Please try again.',
                        });
                    }
                });
            }
        });
    });
});
