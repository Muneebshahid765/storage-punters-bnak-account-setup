// Define API endpoints globally (before event handler)
let apiEndpoints = {
    "btn__all_auctions": { url: SITE_URL + "api/auction/fetch_all_auctions_by_storage_facility", title: "All Auctions" },
    "btn__won_auctions": { url: SITE_URL + "api/auction/fetch_won_auctions_by_storage_facility", title: "Auctions Won" },
    "btn__invoices_auctions": { url: SITE_URL + "api/invoice/fetch_invoices_auctions_by_user", title: "Invoices" },
    "btn__invoice_logs": { url: SITE_URL + "api/logs/invoice/payment-history", title: "Invoice Logs" },
    "btn__auction__confirm_logs": { url: SITE_URL + "api/logs/auction/confirm-logs", title: "Auction Winner Confirm Logs" }
};

$(document).ready(function () {


    function generateAllAuctionsTableLayout(title) {
        let tableHTML = `


            <div class="border rounded dashboard ps-2 pe-2 mt-3">
                <div class="my-2 row">
                    <div class="d-flex justify-content-between col-sm-12 col-12">
                        <div class="px-2 w-100 d-flex justify-content-center justify-content-md-between flex-column flex-md-row"
                            style="background: rgba(0, 0, 0, 0.05); padding: 15px;">
                            <div>
                                <p class="mb-1 my-auto">${title}</p>
                            </div>
                            <div>
                                <p class="my-auto">Current Time Zone - Australia/Brisbane</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="text-center dashboard">
                        <div class="table-responsive">
                           <table id="auction-table" class="table table-striped table-bordered table-hover">
                                <thead class="p-2 text-nowrap">
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Auction #</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Status</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Starts</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Ends</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Start Bid</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Highest Bid</th>
                                    <th style="width: 3%; text-align: left;" class="border ps-3">Actions</th>
                                </thead>
                                <tbody class="p-2 text-nowrap"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>`;

        $(".__tab__table_auction__dashboard").html(tableHTML);
    }


    function generateWonAuctionsTableLayout(title) {
        let tableHTML = `


            <div class="border rounded dashboard ps-2 pe-2 mt-3">
                <div class="my-2 row">
                    <div class="d-flex justify-content-between col-sm-12 col-12">
                        <div class="px-2 w-100 d-flex justify-content-center justify-content-md-between flex-column flex-md-row"
                            style="background: rgba(0, 0, 0, 0.05); padding: 15px;">
                            <div>
                                <p class="mb-1 my-auto">${title}</p>
                            </div>
                            <div>
                                <p class="my-auto">Current Time Zone - Australia/Brisbane</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="text-center dashboard">
                        <div class="table-responsive">
                           <table id="auction-table" class="table table-striped table-bordered table-hover">
                                <thead class="p-2 text-nowrap">
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Auction #</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Status</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Starts</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Ends</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Start Bid</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Highest Bid</th>
                                    <th style="width: 3%; text-align: left;" class="border ps-3">Actions</th>
                                </thead>
                                <tbody class="p-2 text-nowrap"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>`;

        $(".__tab__table_auction__dashboard").html(tableHTML);
    }



    function generateInvoicesAuctionsTableLayout(title) {
        let tableHTML = `
            <div class="border rounded dashboard ps-2 pe-2">
                <div class="my-2 row">
                    <div class="d-flex justify-content-between col-sm-12 col-12">
                        <div class="px-2 w-100 d-flex justify-content-center justify-content-md-between flex-column flex-md-row"
                            style="background: rgba(0, 0, 0, 0.05); padding: 15px;">
                            <div>
                                <p class="mb-1 my-auto">${title}</p>
                            </div>
                            <div>
                                <p class="my-auto">Current Time Zone - Australia/Brisbane</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="text-center dashboard">
                        <div class="table-responsive">
                            <table id="invoice-table" class="mt-sm-1 p-0 table table-striped table-bordered table-hover">
                               <thead class="p-2 text-nowrap">
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Invoice #</th>
                                     <th style="width: 8%; text-align: left;" class="border ps-3">Auction #</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Full Name</th>
                                    <th style="width: 12%; text-align: left;" class="border ps-3">Status</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Facility</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Amount</th>
                                    <th style="width: 12%; text-align: left;" class="border ps-3"> Invoice Date</th>
                                    <th style="width: 5%; text-align: left;" class="border ps-3">Deposit Due</th>
                                    <th style="width: 10%; text-align: left;" class="border ps-3">Actions</th>
                               </thead>
                                <tbody class="p-2 text-nowrap"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>`;

        $(".__tab__table_auction__dashboard").html(tableHTML);
    }

    function generateInvoicesLogsTableLayout(title) {
        let tableHTML = `
            <div class="border rounded dashboard ps-2 pe-2">
                <div class="my-2 row">
                    <div class="d-flex justify-content-between col-sm-12 col-12">
                        <div class="px-2 w-100 d-flex justify-content-center justify-content-md-between flex-column flex-md-row"
                            style="background: rgba(0, 0, 0, 0.05); padding: 15px;">
                                   <div class="d-flex align-items-center">
                                        <!-- 🔵 Circle Back Button using Bootstrap -->
                                        <button type="button"
                                            class="btn-yellow text-white rounded-circle d-flex align-items-center justify-content-center me-2 btn_back_to_invoice_logs"
                                            style="width: 40px; height: 40px;">
                                            <i class="bi bi-arrow-left"></i> <!-- Bootstrap Icon -->
                                        </button>
                                        <p class="mb-1 my-auto">${title}</p>
                                    </div>
                            <div>
                                <p class="my-auto">Current Time Zone - Australia/Brisbane</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="text-center dashboard">
                        <div class="table-responsive">
                            <table id="invoice-logs-table" class="mt-sm-1 p-0 table table-striped table-bordered table-hover">
                               <thead class="p-2 text-nowrap">
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Log #</th>
                                    <th style="width: 12%; text-align: left;" class="border ps-3">Action Event</th>
                                    <th style="width: 12%; text-align: left;" class="border ps-3">Amount</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Status</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Created At #</th>
                               </thead>
                                <tbody class="p-2 text-nowrap"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>`;

        $(".__tab__table_auction__dashboard").html(tableHTML);
    }

    function generateAuctionWinnerConfirmLogsTableLayout(title) {
        let tableHTML = `
            <div class="border rounded dashboard ps-2 pe-2">
                <div class="my-2 row">
                    <div class="d-flex justify-content-between col-sm-12 col-12">
                        <div class="px-2 w-100 d-flex justify-content-center justify-content-md-between flex-column flex-md-row"
                            style="background: rgba(0, 0, 0, 0.05); padding: 15px;">
                                   <div class="d-flex align-items-center">
                                        <!-- 🔵 Circle Back Button using Bootstrap -->
                                        <button type="button"
                                            class="btn-yellow text-white rounded-circle d-flex align-items-center justify-content-center me-2 btn_back_to_auction_confirm_logs"
                                            style="width: 40px; height: 40px;">
                                            <i class="bi bi-arrow-left"></i> <!-- Bootstrap Icon -->
                                        </button>
                                        <p class="mb-1 my-auto">${title}</p>
                                    </div>
                            <div>
                                <p class="my-auto">Current Time Zone - Australia/Brisbane</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-12">
                    <div class="text-center dashboard">
                        <div class="table-responsive">
                            <table id="auction-confirm-logs-table" class="mt-sm-1 p-0 table table-striped table-bordered table-hover">
                               <thead class="p-2 text-nowrap">
                                    <th style="width: 12%; text-align: left;" class="border ps-3">Person Name</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Status</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Created At #</th>
                               </thead>
                                <tbody class="p-2 text-nowrap"></tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>`;

        $(".__tab__table_auction__dashboard").html(tableHTML);
    }

    function auctionListTable(apiUrl, actionType, typeId = '', facilityId = '', actionTitle, actionId) {
        // Destroy existing DataTable if it is already initialized
        if ($.fn.DataTable.isDataTable('#auction-table')) {
            $('#auction-table').DataTable().destroy();
        }

        if (actionType == "auctions") {


            // Initialize DataTable
            $('#auction-table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": apiUrl,
                    "type": "POST",
                    "data": function (d) {
                        d.auction_type_id = typeId;
                        d.storage_facility_id = facilityId;
                        return d;
                    },
                    "beforeSend": function () {
                        showUniversalLoaderForTable('auction-table');
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
                    }
                },
                "columns": [
                    { "data": "auction_id" },
                    { "data": "auction_type_name" },
                    {
                        "data": null, // Use "data": null to access the entire data row
                        "render": function (data, type, row) {
                            return row.auction_start_date + ' ' + row.auction_start_time;
                        }
                    },
                    {
                        "data": null, // Use "data": null to access the entire data row
                        "render": function (data, type, row) {
                            return row.auction_end_date + ' ' + row.auction_end_time;
                        }
                    },
                    { "data": "auction_start_bid_amount" },
                    { "data": "highest_bid_amount" },
                    {
                        "data": null,
                        "orderable": false,
                        "render": function (data, type, row, meta) {

                            let actionButtons = "";
                            // Check confirmations count
                            const confirmationCount = row.confirmations ? row.confirmations.length : 0;
                            const confirmationMessage = row.confirmation_message;

                            if (actionTitle === "Auctions Won") {
                                if (confirmationMessage == "Please confirm") {
                                    // Show Confirm dropdown if not fully confirmed
                                    actionButtons += `
                    <div style="position: relative; display: inline-block; margin-left: 10px;">
                        <!-- Three Dots (⋮) Dropdown Menu -->
                        <div class="dropdown d-inline-block">
                            <button style="background: #1C421D !important;" class="border-0 btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                ⋮
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item view-auction-confirm-history" href="#" data-auction-id="${row.auction_id}" data-auction-winner-id="${row.auction_winner_id}">History</a></li>
                                <li><a class="dropdown-item auction-confirm" href="#" data-auction-id="${row.auction_id}" data-auction-winner-id="${row.auction_winner_id}">Confirm</a></li>
                            </ul>
                        </div>
                    </div>
                `;
                                } else {
                                    // Show confirmation status if both parties confirmed
                                    actionButtons += `
                    <div style="display: inline-block; margin-left: 10px; color: green; font-weight: bold;">
                        `+ confirmationMessage + `
                    </div>

                    <div style="position: relative; display: inline-block; margin-left: 10px;">
                        <!-- Three Dots (⋮) Dropdown Menu -->
                        <div class="dropdown d-inline-block">
                            <button style="background: #1C421D !important;" class="border-0 btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                ⋮
                            </button>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item view-auction-confirm-history" href="#" data-auction-id="${row.auction_id}" data-auction-winner-id="${row.auction_winner_id}">History</a></li>
                            </ul>
                        </div>
                    </div>

                `;
                                }
                            } else {
                                if (row.auction_type_id == 11) {

                                    actionButtons = `
                                 <div style="position: relative; display: inline-block;">

                                <!-- Three Dots (⋮) Dropdown Menu -->
                                <div class="dropdown d-inline-block">
                                    <button style="background: #1C421D !important;" class=" border-0 btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        ⋮
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item view-auction-btn" href="#" data-auction-id="${row.auction_id}">
                                                <i class="bi bi-eye me-2"></i> View
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item edit-auction-btn" href="#" data-auction-id="${row.auction_id}">
                                                <i class="bi bi-pencil-square me-2"></i> Edit
                                            </a>
                                        </li>
                                    </ul>

                                </div>
                            </div>
                            `;

                                } else {

                                    actionButtons = `
                                 <div style="position: relative; display: inline-block;">

                                <!-- Three Dots (⋮) Dropdown Menu -->
                                <div class="dropdown d-inline-block">
                                    <button style="background: #1C421D !important;" class=" border-0 btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        ⋮
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item view-auction-btn" href="#" data-auction-id="${row.auction_id}">
                                                <i class="bi bi-eye me-2"></i> View
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item edit-auction-btn" href="#" data-auction-id="${row.auction_id}">
                                                <i class="bi bi-pencil-square me-2"></i> Edit
                                            </a>
                                        </li>
                                        <li>
                                            <a class="dropdown-item delete-auction-btn text-danger" href="#" data-auction-id="${row.auction_id}">
                                                <i class="bi bi-trash me-2"></i> Delete
                                            </a>
                                        </li>
                                    </ul>

                                </div>
                            </div>
                            `;
                                }

                            }

                            return actionButtons;
                        }
                    }

                ],
                "order": [[0, 'desc']], // Sort by Auction ID
                "rowCallback": function (row, data) {
                    $(row).hover(
                        function () { $(this).addClass('table-hover'); },
                        function () { $(this).removeClass('table-hover'); }
                    ).css('cursor', 'pointer');
                }
            });
        } else if (actionType == "invoices") {
            // Initialize DataTable
            $('#invoice-table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": apiUrl,
                    "type": "POST",
                    "beforeSend": function () {
                        showUniversalLoaderForTable('invoice-table');
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
                    }
                },
                "columns": [
                    { "data": "invoice_number" },
                    { "data": "auction_id" },
                    {
                        "data": null,
                        "orderable": false,
                        "render": function (data, type, row) {
                            return row.firstname + ' ' + row.lastname;
                        }
                    },
                    { "data": "invoice_status" },
                    { "data": "facility_name" },
                    { "data": "invoice_total_amount" },
                    { "data": "invoice_date" },
                    { "data": "invoice_due_date" },

                    {
                        "data": null,
                        "orderable": false,
                        "render": function (data, type, row) {
                            return `
                            <div style="position: relative; display: inline-block;">

                                <!-- Three Dots (⋮) Dropdown Menu -->
                                <div class="dropdown d-inline-block">
                                    <button style="background: #1C421D !important;" class=" border-0 btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        ⋮
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item view-invoice-payment-history" href="#" data-invoice-id="${row.invoice_id}">History</a></li>
                                    </ul>
                                </div>
                            </div>
                            `;
                        }
                    }


                ],
                "order": [[0, 'desc']], // Sort by Auction ID
                "rowCallback": function (row, data) {
                    $(row).hover(
                        function () { $(this).addClass('table-hover'); },
                        function () { $(this).removeClass('table-hover'); }
                    ).css('cursor', 'pointer');
                }
            });
        } else if (actionType == "invoice-logs") {
            // Initialize DataTable
            $('#invoice-logs-table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": apiUrl,
                    "type": "POST",
                    "beforeSend": function () {
                        showUniversalLoaderForTable('invoice-logs-table');
                    },
                    "data": function (d) {

                        d.invoice_id = actionId
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

                        showNotification(json.message, "error");
                        return [];
                    },
                    "error": function (xhr, status, error) {
                        console.error("DataTables AJAX error:", status, error);
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.error) {
                                console.error("Server error:", response.message);
                            }
                        } catch (e) {
                            console.error("Error parsing JSON response:", e);
                        }
                    }
                },
                "columns": [
                    { "data": "invoice_log_id" },

                    {
                        "data": "invoice_event_type", "render": function (data) {
                            return data.toUpperCase();
                        }
                    },
                    {
                        "data": null, // use `null` to access the full row
                        "render": function (data, type, row) {
                            // Example: format amount with 2 decimal places and attach currency
                            return parseFloat(row.invoice_amount_received).toFixed(2) + ' ' + row.invoice_currency.toUpperCase();
                        }
                    },
                    {
                        "data": "invoice_payment_status", "render": function (data) {
                            return data.toUpperCase();
                        }
                    },
                    { "data": "invoice_payment_at" },

                ],
                "order": [[0, 'desc']], // Sort by Auction ID
                "rowCallback": function (row, data) {
                    $(row).hover(
                        function () { $(this).addClass('table-hover'); },
                        function () { $(this).removeClass('table-hover'); }
                    ).css('cursor', 'pointer');
                }
            });
        } else if (actionType == "auction-confirm-logs") {
            // Initialize DataTable
            $('#auction-confirm-logs-table').DataTable({
                "processing": true,
                "serverSide": true,
                "ajax": {
                    "url": apiUrl,
                    "type": "POST",
                    "beforeSend": function () {
                        showUniversalLoaderForTable('auction-confirm-logs-table');
                    },
                    "data": function (d) {

                        d.winner_id = actionId
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

                        showNotification(json.message, "error");
                        return [];
                    },
                    "error": function (xhr, status, error) {
                        console.error("DataTables AJAX error:", status, error);
                        try {
                            var response = JSON.parse(xhr.responseText);
                            if (response.error) {
                                console.error("Server error:", response.message);
                            }
                        } catch (e) {
                            console.error("Error parsing JSON response:", e);
                        }
                    }
                },
                "columns": [
                    { "data": "confirm_person_name" },
                    { "data": "confirm_status" },
                    { "data": "confirmed_at" },
                ],
                "order": [[0, 'desc']], // Sort by Auction ID
                "rowCallback": function (row, data) {
                    $(row).hover(
                        function () { $(this).addClass('table-hover'); },
                        function () { $(this).removeClass('table-hover'); }
                    ).css('cursor', 'pointer');
                }
            });
        }
    }


    $(document).on("click", ".auction-confirm", function (event) {
        event.preventDefault();
        let auctionId = $(this).data("auction-id");
        let auctionWinnerId = $(this).data("auction-winner-id");

        // Store the ID in a data attribute on the modal
        $('#auctionAgreementModal').data('auction-id', auctionId);
        $('#auctionAgreementModal').data('winner-id', auctionWinnerId);

        // Open modal
        $('#auctionAgreementModal').modal('show');
    });

    $(document).on("click", "#confirmAuctionAgreementBtn", function () {
        // Get the winner ID from modal's datal
        const auctionId = $('#auctionAgreementModal').data('auction-id');
        const winnerId = $('#auctionAgreementModal').data('winner-id');

        // Then show confirmation popup
        Swal.fire({
            title: "Confirm Agreement",
            text: "Are you sure you agree to proceed with confirm?",
            icon: "question",
            showCancelButton: true,
            confirmButtonText: "Yes, Confirm",
            cancelButtonText: "Cancel"
        }).then((result) => {
            if (result.isConfirmed && winnerId) {

                confirmAuctionAgreement(auctionId, winnerId, 1);

            } else if (result.isDismissed) {


                $('#auctionAgreementModal').modal('hide');
            }
        });
    });

    function confirmAuctionAgreement(auctionId, winnerId, confirmationTypeId) {
        $.ajax({
            url: SITE_URL + "api/auction/confirm_auction",
            type: 'POST',
            contentType: "application/json",
            dataType: 'json',
            data: JSON.stringify({
                auctionId: auctionId,
                auctionWinnerId: winnerId,
                confirmationTypeId: confirmationTypeId
            }),
            success: function (response) {
                if (response.status === 'success') {
                    showNotification(response.message, "success");

                    $('#auctionAgreementModal').modal('hide');

                    loadAuctionData(SITE_URL + "api/auction/fetch_won_auctions_by_storage_facility", "Auctions Won");
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: response.message || 'Failed to confirm. Please try again.'
                    });
                }
            },
            error: function (xhr, status, error) {

                $('#auctionAgreementModal').modal('hide');

                showNotification(xhr.responseJSON.message, "error");
            }
        });
    }

    $(document).on("click", ".view-invoice-payment-history", function (event) {

        event.preventDefault();

        const defaultTypeId = $("#auctionTypeDropdown").val() || '';
        const defaultFacilityId = $("#storageFacilityDropdown").val() || '';
        let invoiceId = $(this).data("invoice-id");

        loadAuctionData(SITE_URL + "api/logs/invoice/payment-history", "Invoice Logs", defaultTypeId, defaultFacilityId, invoiceId);

    });

    $(document).on("click", ".btn_back_to_invoice_logs", function (event) {

        event.preventDefault();


        loadAuctionData(SITE_URL + "api/invoice/fetch_invoices_auctions_by_user", "Invoices");

    });

    $(document).on("click", ".btn_back_to_auction_confirm_logs", function (event) {

        event.preventDefault();


        loadAuctionData(SITE_URL + "api/auction/fetch_won_auctions_by_storage_facility", "Auctions Won");

    });

    $(document).on("click", ".view-auction-confirm-history", function (event) {

        event.preventDefault();
        const defaultTypeId = $("#auctionTypeDropdown").val() || '';
        const defaultFacilityId = $("#storageFacilityDropdown").val() || '';
        let auctionWinnerId = $(this).data("auction-winner-id");

        loadAuctionData(SITE_URL + "api/logs/auction/confirm-logs", "Auction Winner Confirm Logs", defaultTypeId, defaultFacilityId, auctionWinnerId);


    });

    $(document).on("click", ".view-auction-btn", function () {
        let auctionId = $(this).data("auction-id");
        window.open(SITE_URL + "auctions/" + auctionId, "_blank"); // Open in a new tab
    });

    $(document).on("click", ".edit-auction-btn", function () {
        let auctionId = $(this).data("auction-id");
        window.open(SITE_URL + "auctions/edit/" + auctionId, "_blank"); // Open in a new tab
    });

    $(document).on("click", ".add-auction-btn", function () {
        window.open(SITE_URL + "storage-facility/ai/new", "_blank"); // Open in a new tab
    });

    $(document).on("click", ".delete-auction-btn", function () {
        let auctionId = $(this).data("auction-id");


        Swal.fire({
            title: 'Are you sure?',
            text: "You are about to delete this auction.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#007550',
            cancelButtonColor: '#8E4D49',
            confirmButtonText: 'Yes, delete it!',
            cancelButtonText: 'Cancel',
        }).then((result) => {
            if (result.isConfirmed) {

                $.ajax({
                    url: SITE_URL + "api/auction/storage-facility/update-status", // Change this to your backend URL
                    type: "POST",
                    data: JSON.stringify({ auctionId: auctionId, auction_type_id: 11 }), // Sending data as JSON
                    contentType: "application/json",
                    dataType: "json",
                    beforeSend: function () {
                    },
                    success: function (response) {
                        if (response.status === "success") {
                            showNotification(response.message, "success");
                            setTimeout(() => {
                                window.location.reload();
                            }, 300);

                        } else {
                            showNotification(response.message, "error");

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
        });
    });


    function loadAuctionData(apiUrl, title, typeId = '', facilityId = '', id) {


        var actionType = ""

        $(".__tab__table_auction__dashboard").empty();

        if (title == "All Auctions") {

            actionType = "auctions";

            generateAllAuctionsTableLayout(title);
        } else if (title == "Auctions Won") {

            actionType = "auctions";

            generateWonAuctionsTableLayout(title);
        } else if (title == "Invoices") {

            actionType = "invoices";

            generateInvoicesAuctionsTableLayout(title);
        } else if (title == "Invoice Logs") {

            actionType = "invoice-logs";

            generateInvoicesLogsTableLayout(title);
        } else if (title == "Auction Winner Confirm Logs") {

            actionType = "auction-confirm-logs";

            generateAuctionWinnerConfirmLogsTableLayout(title);
        }

        auctionListTable(apiUrl, actionType, typeId, facilityId, title, id);
    }



    // Handle Tab Clicks
    $(document).on("click", ".__tab__auction__dashboard button", function () {
        let buttonClasses = $(this).attr("class").split(" "); // Get all classes
        let clickedClass = Object.keys(apiEndpoints).find(cls => buttonClasses.includes(cls)); // Find the matching class

        // Set active tab
        $(".__tab__auction__dashboard button").removeClass("tab_btn").addClass("false");
        $(this).removeClass("false").addClass("tab_btn");

        // Load Data if button class matches an API endpoint
        if (clickedClass && apiEndpoints[clickedClass]) {
            const typeId = $("#auctionTypeDropdown").val();
            const facilityId = $("#storageFacilityDropdown").val();


            loadAuctionData(apiEndpoints[clickedClass].url, apiEndpoints[clickedClass].title, typeId, facilityId);
        }
    });


    // Load default table on page load
    const defaultTypeId = $("#auctionTypeDropdown").val() || '';
    const defaultFacilityId = $("#storageFacilityDropdown").val() || '';
    loadAuctionData(SITE_URL + "api/auction/fetch_all_auctions_by_storage_facility", "All Auctions", defaultTypeId, defaultFacilityId);

    $(document).on("change", "#auctionTypeDropdown, #storageFacilityDropdown", function () {
        const typeId = $("#auctionTypeDropdown").val();
        const facilityId = $("#storageFacilityDropdown").val();

        loadAuctionData(SITE_URL + "api/auction/fetch_all_auctions_by_storage_facility", "All Auctions", typeId, facilityId);
    });


});
