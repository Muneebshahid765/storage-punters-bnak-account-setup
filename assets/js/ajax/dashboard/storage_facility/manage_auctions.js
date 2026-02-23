$(document).ready(function () {
    let currentPage = 0;
    const entriesPerPage = 10;
    let totalRecords = 0;

    // Initial Load
    loadFacilities();
    fetchAuctions();

    // Event Listeners
    $('#btn-apply-filters').on('click', function () {
        currentPage = 0;
        fetchAuctions();
    });

    $('#btn-reset-filters').on('click', function () {
        $('#filter-status').val('');
        $('#filter-location').val('');
        $('#filter-search').val('');
        currentPage = 0;
        fetchAuctions();
    });

    $('#filter-search').on('keypress', function (e) {
        if (e.which == 13) {
            currentPage = 0;
            fetchAuctions();
        }
    });

    function loadFacilities() {
        $.ajax({
            url: SITE_URL + 'api/user/fetch_storage_facilities_by_user',
            type: 'POST',
            success: function (response) {
                if (response.status === 'success') {
                    let opts = '<option value="">All Locations</option>';
                    response.data.forEach(fac => {
                        opts += `<option value="${fac.storage_facility_id}">${fac.storage_facility_name}</option>`;
                    });
                    $('#filter-location').html(opts);
                }
            }
        });
    }

    function fetchAuctions() {
        const postData = {
            draw: 1,
            start: currentPage * entriesPerPage,
            length: entriesPerPage,
            search: { value: $('#filter-search').val() },
            order: [{ column: 0, dir: 'desc' }],
            auction_type_id: $('#filter-status').val(),
            storage_facility_id: $('#filter-location').val()
        };

        const auctionsList = $('#auctions-list');
        auctionsList.html('<tr><td colspan="6" class="text-center py-5"><div class="spinner-border text-emerald" role="status"></div></td></tr>');

        $.ajax({
            url: SITE_URL + 'api/auction/fetch_all_auctions_by_storage_facility',
            type: 'POST',
            data: postData,
            success: function (response) {
                if (response.status === 'success' || response.result) {
                    const data = response.result ? response.result.data : (response.data || []);
                    totalRecords = response.result ? response.result.recordsFiltered : (response.total || 0);
                    renderAuctions(data);
                    renderPagination();
                    updateShowingEntries(data.length);
                } else {
                    auctionsList.html('<tr><td colspan="6" class="text-center py-5 text-muted">No auctions found matching your criteria.</td></tr>');
                    $('#showing-entries').text('Showing 0 entries');
                    $('#pagination-container').empty();
                }
            },
            error: function () {
                auctionsList.html('<tr><td colspan="6" class="text-center py-5 text-danger">Error loading auctions. Please try again.</td></tr>');
            }
        });
    }

    function renderAuctions(auctions) {
        const auctionsList = $('#auctions-list');
        auctionsList.empty();

        if (auctions.length === 0) {
            auctionsList.html('<tr><td colspan="6" class="text-center py-5 text-muted">No auctions found.</td></tr>');
            return;
        }

        auctions.forEach(auction => {
            const statusBadge = getStatusBadge(auction.auction_type_id, auction.auction_type_name);
            const dates = auction.start_date && auction.end_date
                ? `${auction.start_date} to ${auction.end_date}`
                : 'Awaiting Publish';

            const valueDisplay = auction.auction_type_id == 4 // sold
                ? `<strong>$${parseFloat(auction.current_bid).toFixed(2)}</strong>`
                : (auction.current_bid > 0 ? `Current Bid: $${parseFloat(auction.current_bid).toFixed(2)}` : 'N/A');

            const row = `
                <tr>
                    <td class="ps-4 fw-bold">#${auction.id}</td>
                    <td>
                        <div class="fw-bold text-dark">${auction.unit_number ? 'Unit ' + auction.unit_number : 'Untitled'}</div>
                        <div class="small text-muted">${auction.storage_facility_name || 'N/A'}</div>
                    </td>
                    <td>${statusBadge}</td>
                    <td><div class="small">${dates}</div></td>
                    <td><div class="small">${valueDisplay}</div></td>
                    <td class="pe-4 text-end">
                        <div class="dropdown">
                            <button class="btn btn-sm btn-light border dropdown-toggle" type="button" data-bs-toggle="dropdown">
                                Actions
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end shadow-sm">
                                <li><a class="dropdown-item py-2" href="${SITE_URL}auctions/${auction.id}" target="_blank"><i class="bi bi-eye me-2"></i> View Public</a></li>
                                <li><a class="dropdown-item py-2" href="${SITE_URL}auctions/edit/${auction.id}"><i class="bi bi-pencil me-2"></i> Edit Details</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><button class="dropdown-item py-2 text-danger" onclick="confirmDelete(${auction.id})"><i class="bi bi-trash me-2"></i> Delete</button></li>
                            </ul>
                        </div>
                    </td>
                </tr>
            `;
            auctionsList.append(row);
        });
    }

    function getStatusBadge(typeId, typeName) {
        let classes = 'badge rounded-pill px-3 py-2 ';
        switch (parseInt(typeId)) {
            case 1: classes += 'bg-success bg-opacity-10 text-success'; break; // Live
            case 2: classes += 'bg-secondary bg-opacity-10 text-secondary'; break; // Inactive
            case 3: classes += 'bg-warning bg-opacity-10 text-warning'; break; // Pending
            case 4: classes += 'bg-primary bg-opacity-10 text-primary'; break; // Sold
            case 6: classes += 'bg-info bg-opacity-10 text-info'; break; // Draft
            default: classes += 'bg-light text-dark'; break;
        }
        return `<span class="${classes}">${typeName || 'Unknown'}</span>`;
    }

    function renderPagination() {
        const container = $('#pagination-container');
        container.empty();

        if (totalRecords <= entriesPerPage) return;

        const totalPages = Math.ceil(totalRecords / entriesPerPage);
        let html = '<ul class="pagination pagination-sm mb-0">';

        // Prev
        html += `<li class="page-item ${currentPage === 0 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage - 1})">Previous</a>
                 </li>`;

        // Page Numbers
        for (let i = 0; i < totalPages; i++) {
            if (i < 3 || i > totalPages - 4 || (i >= currentPage - 1 && i <= currentPage + 1)) {
                html += `<li class="page-item ${currentPage === i ? 'active' : ''}">
                            <a class="page-link ${currentPage === i ? 'bg-emerald border-emerald' : ''}" href="#" onclick="changePage(${i})">${i + 1}</a>
                         </li>`;
            } else if (i === 3 || i === totalPages - 4) {
                html += '<li class="page-item disabled"><span class="page-link">...</span></li>';
            }
        }

        // Next
        html += `<li class="page-item ${currentPage === totalPages - 1 ? 'disabled' : ''}">
                    <a class="page-link" href="#" onclick="changePage(${currentPage + 1})">Next</a>
                 </li>`;

        html += '</ul>';
        container.html(html);
    }

    window.changePage = function (page) {
        event.preventDefault();
        currentPage = page;
        fetchAuctions();
    };

    function updateShowingEntries(currentCount) {
        const start = currentPage * entriesPerPage + 1;
        const end = Math.min((currentPage + 1) * entriesPerPage, totalRecords);
        if (totalRecords === 0) {
            $('#showing-entries').text('Showing 0 entries');
        } else {
            $('#showing-entries').text(`Showing ${start} to ${end} of ${totalRecords} entries`);
        }
    }

    window.confirmDelete = function (id) {
        Swal.fire({
            title: 'Delete Auction?',
            text: "Are you sure you want to delete this auction? This action cannot be undone.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#6c757d',
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
                    url: SITE_URL + 'api/auction/storage-facility/delete',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({
                        auctionId: id
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
                            fetchAuctions(); // Reload list
                        } else {
                            Swal.fire({
                                icon: 'error',
                                title: 'Error',
                                text: response.message
                            });
                        }
                    },
                    error: function (jqXHR) {
                        let errorMessage = "Failed to delete auction. Please try again.";
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
    };
});
