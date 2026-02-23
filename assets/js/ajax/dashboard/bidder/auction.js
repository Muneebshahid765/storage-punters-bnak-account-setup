// Define API endpoints globally (before event handler)
let apiEndpoints = {
    "btn__current__active_auctions": { url: SITE_URL + "api/auction/fetch_current_active_auctions_by_user", title: "Active Auctions Bid On" },
    "btn__all_auctions": { url: SITE_URL + "api/auction/fetch_all_auctions_by_user", title: "All Auctions Bid On" },
    "btn__watched_auctions": { url: SITE_URL + "api/auction/fetch_watched_auctions_by_user", title: "Watched Auctions" },
    "btn__won_auctions": { url: SITE_URL + "api/auction/fetch_won_auctions_by_user", title: "Auctions Won" },
    "btn__invoices_auctions": { url: SITE_URL + "api/invoice/fetch_invoices_auctions_by_user", title: "Invoices" },
    "btn__invoice_logs": { url: SITE_URL + "api/logs/invoice/payment-history", title: "Invoice Logs" },
    "btn__auction__confirm_logs": { url: SITE_URL + "api/logs/auction/confirm-logs", title: "Auction Winner Confirm Logs" }
};

$(document).ready(function () {
    function generateCurrentActiveAuctionsTableLayout(title) {
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
                        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                            <table id="auction-table" class="table table-striped table-bordered table-hover" style="min-width: 800px;">
                                <thead class="p-2 text-nowrap">
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Auction #</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Status</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Starts</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Ends</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">High Bid</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">My Max Bid</th>
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

    function generateAllAuctionsTableLayout(title) {
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
                        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                           <table id="auction-table" class="table table-striped table-bordered table-hover" style="min-width: 800px;">
                                <thead class="p-2 text-nowrap">
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Auction #</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Status</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Starts</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Ends</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">High Bid</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">My Max Bid</th>
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

    function generateWatchedAuctionsTableLayout(title) {
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
                        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                           <table id="auction-table" class="table table-striped table-bordered table-hover" style="min-width: 800px;">
                                <thead class="p-2 text-nowrap">
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Auction #</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Status</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Starts</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Ends</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">High Bid</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">My Max Bid</th>
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
                        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                            <table id="auction-table" class="table table-striped table-bordered table-hover" style="min-width: 800px;">
                                <thead class="p-2 text-nowrap">
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Auction #</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Status</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Starts</th>
                                    <th style="width: 15%; text-align: left;" class="border ps-3">Ends</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">High Bid</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">My Max Bid</th>
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
                        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                            <table id="invoice-table" class="mt-sm-1 p-0 table table-striped table-bordered table-hover" style="min-width: 800px;">
                               <thead class="p-2 text-nowrap">
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Invoice #</th>
                                    <th style="width: 8%; text-align: left;" class="border ps-3">Auction #</th>
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
                        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                            <table id="invoice-logs-table" class="mt-sm-1 p-0 table table-striped table-bordered table-hover" style="min-width: 800px;">
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
                        <div class="table-responsive" style="overflow-x: auto; -webkit-overflow-scrolling: touch;">
                            <table id="auction-confirm-logs-table" class="mt-sm-1 p-0 table table-striped table-bordered table-hover" style="min-width: 800px;">
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


    function auctionListTable(apiUrl, actionType, actionTitle, actionId) {
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
                    { "data": "highest_bid_amount" },
                    { "data": "my_max_bid" },
                    {
                        "data": null,
                        "orderable": false,
                        "render": function (data, type, row, meta) {
                            let actionButtons = `
                                <div style="position: relative; display: inline-block;">
                                    <!-- Eye Icon Button -->
                                    <button type="button" class="icon-button border-0 btn btn-primary view-auction-btn" data-auction-id="${row.auction_id}" style="display: flex; align-items: center; justify-content: center; width: 40px; height: 40px; padding: 0; border-radius: 50%;">
                                        <svg stroke="currentColor" fill="currentColor" stroke-width="0" viewBox="0 0 1024 1024" height="20" width="20" xmlns="http://www.w3.org/2000/svg">
                                            <path d="M396 512a116 116 0 1 0 232 0 116 116 0 1 0-232 0zm512-64c-77.6-128-205.8-208-342-208s-264.4 80-342 208c-11.6 19.2-11.6 44.8 0 64 77.6 128 205.8 208 342 208s264.4-80 342-208c11.6-19.2 11.6-44.8 0-64zM512 648c-75.6 0-136-60.4-136-136s60.4-136 136-136 136 60.4 136 136-60.4 136-136 136z"></path>
                                        </svg>
                                    </button>
                                </div>
                            `;

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
                    { "data": "invoice_status" },
                    { 
                        "data": "facility_name",
                        "render": function(data, type, row) {
                            return (row.auction_id == 0 || row.auction_id == null) ? '<span class="badge bg-warning text-dark">The Vault</span>' : (data || "N/A");
                        }
                    },
                    { "data": "invoice_total_amount" },
                    { "data": "invoice_date" },
                    { "data": "invoice_due_date" },

                    {
                        "data": null,
                        "orderable": false,
                        "render": function (data, type, row) {

                            if (row.payment_status != "Paid") {
                                return `
                            <div style="position: relative; display: inline-block;">

                                <!-- Three Dots (⋮) Dropdown Menu -->
                                <div class="dropdown d-inline-block">
                                    <button style="background: #1C421D !important;" class=" border-0 btn btn-primary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                                        ⋮
                                    </button>
                                    <ul class="dropdown-menu">
                                        <li>
                                            <a class="dropdown-item pay-now"
                                                href="#"
                                                data-invoice-id="${row.invoice_id}"
                                                data-amount="${row.invoice_total_amount}"
                                                data-tax="${row.invoice_tax_rate ?? 10}"
                                                data-auction-id="${row.auction_id}">
                                                Pay Now
                                            </a>
                                        </li>
                                        <li><a class="dropdown-item view-invoice-payment-history" href="#" data-invoice-id="${row.invoice_id}">History</a></li>
                                    </ul>
                                </div>
                            </div>
                            `;
                            } else {
                                let buttons = `
                                <div class="d-flex align-items-center gap-3">

                                    <!-- Invoice Paid Pill -->
                                    <div class="d-inline-flex align-items-center bg-success bg-opacity-10 text-success px-3 py-1 rounded-pill fw-semibold shadow-sm">
                                        <i class="bi bi-check-circle-fill me-1"></i> Invoice Paid
                                    </div>`;

                                if (row.auction_id != 0 && row.auction_id != null) {
                                    buttons += `
                                    <!-- Contact Owner Small Button -->
                                    <button
                                        class="btn btn-sm btn-success btn-view-owner-contact d-inline-flex align-items-center border-0 shadow-sm"
                                        data-auction-id="${row.auction_id}"
                                        data-invoice-id="${row.invoice_id}"
                                        style="background: #1C421D; color: #fff; font-size: 0.8rem; font-weight: 500; border-radius: 8px; padding: 4px 10px;">
                                        <i class="bi bi-person-lines-fill me-1"></i>
                                        Owner Info
                                    </button>`;
                                }

                                buttons += `
                                </div>`;
                                return buttons;
                            }
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

    $(document).on('click', '.btn-view-owner-contact', function (e) {
        e.preventDefault();

        const auctionId = $(this).data('auction-id');
        const invoiceId = $(this).data('invoice-id');

        $('#ownerContactModal').modal('show');
        $('#ownerContactModalBody').html(`
        <div class="text-center py-5 text-muted">
            <div class="spinner-border text-primary mb-3" role="status"></div><br>
            Fetching contact details...
        </div>
    `);

        $.ajax({
            url: SITE_URL + 'show-dashboard/auctions/owner-contact',
            method: 'POST',
            contentType: 'application/json',
            dataType: 'json',
            data: JSON.stringify({ auctionId, invoiceId }),
            success: function (res) {
                if (res.status === 'success') {
                    const d = res.data;
                    $('#ownerContactModalBody').html(`
                    <div class="container-fluid">
                        <div class="row g-4">

                            <div class="col-md-6">
                                <div class="card shadow-sm border-0 rounded-4 h-100" style="background: white;">
                                    <div class="card-body p-4">
                                        <h5 class="fw-bold text-success mb-3">
                                            <i class="bi bi-geo-alt-fill me-2"></i> Facility Information
                                        </h5>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2"><i class="bi bi-building me-2 text-secondary"></i><strong>Company:</strong> ${d.company_name}</li>
                                            <li class="mb-2"><i class="bi bi-door-open me-2 text-secondary"></i><strong>Facility:</strong> ${d.facility_name}</li>
                                            <li class="mb-2"><i class="bi bi-globe2 me-2 text-secondary"></i><strong>Website:</strong> <a href="${d.facility_website}" target="_blank" class="text-decoration-none text-success">${d.facility_website}</a></li>
                                            <li class="mb-2"><i class="bi bi-clock-history me-2 text-secondary"></i><strong>Office Hours:</strong> ${d.facility_office_hours}</li>
                                            <li class="mb-2"><i class="bi bi-envelope-fill me-2 text-secondary"></i><strong>Email:</strong> ${d.facility_contact_email}</li>
                                            <li><i class="bi bi-envelope-paper-fill me-2 text-secondary"></i><strong>Alt Email:</strong> ${d.facility_additional_email}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <div class="card shadow-sm border-0 rounded-4 h-100" style="background: white;">
                                    <div class="card-body p-4">
                                        <h5 class="fw-bold text-success mb-3">
                                            <i class="bi bi-person-badge-fill me-2"></i> Owner Details
                                        </h5>
                                        <ul class="list-unstyled mb-0">
                                            <li class="mb-2"><i class="bi bi-person-fill me-2 text-secondary"></i><strong>Name:</strong> ${d.owner_name}</li>
                                            <li class="mb-2"><i class="bi bi-envelope-at-fill me-2 text-secondary"></i><strong>Email:</strong> ${d.owner_email}</li>
                                            <li><i class="bi bi-telephone-fill me-2 text-secondary"></i><strong>Phone:</strong> ${d.owner_phone}</li>
                                        </ul>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                `);
                } else {
                    $('#ownerContactModalBody').html(`
                    <div class="alert alert-warning text-center mt-3">
                        ${res.message}
                    </div>
                `);
                }
            },
            error: function (xhr) {
                let msg = "Unable to fetch contact details. Please try again later.";
                if (xhr.responseJSON && xhr.responseJSON.message) msg = xhr.responseJSON.message;
                $('#ownerContactModalBody').html(`
                <div class="alert alert-danger text-center mt-3">${msg}</div>
            `);
            }
        });
    });




    $(document).on("click", ".view-auction-btn", function () {
        let auctionId = $(this).data("auction-id");
        window.open(SITE_URL + "auctions/" + auctionId, "_blank"); // Open in a new tab
    });

    $(document).on("click", ".pay-now", function (event) {
        event.preventDefault();

        const invoiceId = $(this).data("invoice-id");
        const invoiceAmount = parseFloat($(this).data("amount")) || 0;
        const taxRate = parseFloat($(this).data("tax")) || 10;

        // 🔹 Step 1: Check membership before showing payment confirmation
        $.ajax({
            url: SITE_URL + "api/users/bidder/membership/get",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            beforeSend: function () {
                Swal.fire({
                    title: "Checking Membership",
                    text: "Please wait while we verify your membership status...",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            },
            success: function (response) {
                Swal.close();

                if (response.status === "success") {
                    let data = response.data;
                    let userSub = data.subscriptions ? data.subscriptions[0] : null;
                    let commissionRate = data.commission_rate ?? 17.5;

                    const commissionAmount = (invoiceAmount * commissionRate) / 100;
                    const taxAmount = invoiceAmount - (invoiceAmount / (1 + (taxRate / 100)));
                    const totalPayable = invoiceAmount + commissionAmount;

                    if (!data.user || !userSub) {
                        // ❌ No active membership
                        Swal.fire({
                            icon: "question",
                            title: "No Active Membership",
                            html: `
                            <p>You currently don't have an active membership.</p>
                            <hr>
                            <table class="table text-start">
                                <tr><td><b>Base Amount:</b></td><td>$${invoiceAmount.toFixed(2)}</td></tr>
                                <tr><td><b>Commission (${commissionRate}%):</b></td><td>$${commissionAmount.toFixed(2)}</td></tr>
                                <tr><td><b>Tax on Invoice (${taxRate}%):</b></td><td>$${taxAmount.toFixed(2)}</td></tr>
                                <tr><td><b>Total Payable:</b></td><td><b>$${totalPayable.toFixed(2)}</b></td></tr>
                            </table>
                            <div class="text-white text-center py-2 shadow" style="background: var(--text-yello-light);">
                                <i class="bi bi-cash-coin me-1"></i>
                                A standard commission rate of <span class="fw-bold">+${commissionRate}% applies without membership.</span>
                            </div>
                        `,
                            showCancelButton: true,
                            confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Proceed to Pay',
                            cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancel',
                            focusConfirm: true,
                            customClass: {
                                popup: 'swal2-custom-width',
                                confirmButton: 'btn btn-yellow text-white fw-bold',
                                cancelButton: 'btn btn-secondary ms-2'
                            },
                            buttonsStyling: false
                        }).then(result => {
                            if (result.isConfirmed) {
                                payInvoice(invoiceId, {
                                    hasMembership: false,
                                    commissionRate,
                                    invoiceAmount,
                                    totalPayable,
                                    taxRate
                                });
                            }
                        });
                    } else {
                        // ✅ Active membership found
                        let planName = userSub.plan.name;
                        Swal.fire({
                            icon: "question",
                            title: "Your Current Membership",
                            html: `
                            <p>You are on the <b>${planName}</b> plan.</p>
                            <hr>
                            <table class="table text-start">
                                <tr><td><b>Base Amount:</b></td><td>$${invoiceAmount.toFixed(2)}</td></tr>
                                <tr><td><b>Commission (${commissionRate}%):</b></td><td>$${commissionAmount.toFixed(2)}</td></tr>
                                <tr><td><b>Tax on Commission (${taxRate}%):</b></td><td>$${taxAmount.toFixed(2)}</td></tr>
                                <tr><td><b>Total Payable:</b></td><td><b>$${totalPayable.toFixed(2)}</b></td></tr>
                            </table>

                            <div class="text-white text-center py-2 shadow" style="background: var(--text-yello-light);">
                                <i class="bi bi-cash-coin me-1"></i>
                               Membership benefits applied automatically</span>
                            </div>

                        `,

                            showCancelButton: true,
                            confirmButtonText: '<i class="bi bi-check-circle me-1"></i> Yes, Pay Now',
                            cancelButtonText: '<i class="bi bi-x-circle me-1"></i> Cancel',
                            focusConfirm: true,
                            customClass: {
                                popup: 'swal2-custom-width',
                                confirmButton: 'btn btn-yellow text-white fw-bold',
                                cancelButton: 'btn btn-secondary ms-2'
                            },
                            buttonsStyling: false
                        }).then(result => {
                            if (result.isConfirmed) {
                                payInvoice(invoiceId, {
                                    hasMembership: true,
                                    planName,
                                    commissionRate,
                                    invoiceAmount,
                                    totalPayable,
                                    taxRate
                                });
                            }
                        });
                    }
                } else {
                    Swal.fire({
                        title: "Error",
                        text: response.message || "Unable to check membership status.",
                        icon: "error"
                    });
                }
            },
            error: function () {
                Swal.close();
                Swal.fire({
                    title: "Error",
                    text: "Something went wrong while checking membership. Please try again.",
                    icon: "error"
                });
            }
        });
    });



    $(document).on("click", ".view-invoice-payment-history", function (event) {

        event.preventDefault();
        let invoiceId = $(this).data("invoice-id");

        loadAuctionData(SITE_URL + "api/logs/invoice/payment-history", "Invoice Logs", invoiceId);

    });

    $(document).on("click", ".btn_back_to_invoice_logs", function (event) {

        event.preventDefault();


        loadAuctionData(SITE_URL + "api/invoice/fetch_invoices_auctions_by_user", "Invoices");

    });

    $(document).on("click", ".btn_back_to_auction_confirm_logs", function (event) {

        event.preventDefault();


        loadAuctionData(SITE_URL + "api/auction/fetch_won_auctions_by_user", "Auctions Won");

    });


    function payInvoice(invoiceId, membershipInfo = {}) {
        $.ajax({
            url: SITE_URL + "api/stripe/invoice/payment",
            type: 'POST',
            contentType: "application/json",
            dataType: 'json',
            data: JSON.stringify({
                invoice_id: invoiceId,
                membership: membershipInfo
            }),
            beforeSend: function () {
                showUniversalLoaderForTable('invoice-table');
            },
            success: function (response) {


                if (response.status === 'success') {
                    showNotification(response.message, "success");

                    // ⏳ Wait for 5 seconds (5000 milliseconds) before loading data
                    setTimeout(function () {
                        hideUniversalLoader();
                        loadAuctionData(SITE_URL + "api/invoice/fetch_invoices_auctions_by_user", "Invoices");
                    }, 10000);
                } else {
                    hideUniversalLoader();
                    showNotification(response.message, "error");
                }
            },
            error: function (xhr, status, error) {
                hideUniversalLoader();
                showNotification(xhr.responseJSON.message, "error");
            }
        });
    }
    $(document).on("click", ".view-auction-confirm-history", function (event) {

        event.preventDefault();
        let auctionWinnerId = $(this).data("auction-winner-id");

        loadAuctionData(SITE_URL + "api/logs/auction/confirm-logs", "Auction Winner Confirm Logs", auctionWinnerId);

    });


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

                    loadAuctionData(SITE_URL + "api/auction/fetch_won_auctions_by_user", "Auctions Won");
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


    function loadAuctionData(apiUrl, title, id) {

        var actionType = ""

        $(".__tab__table_auction__dashboard").empty();

        if (title == "Active Auctions Bid On") {

            actionType = "auctions";

            generateCurrentActiveAuctionsTableLayout(title);
        } else if (title == "All Auctions Bid On") {

            actionType = "auctions";

            generateAllAuctionsTableLayout(title);
        } else if (title == "Watched Auctions") {

            actionType = "auctions";

            generateWatchedAuctionsTableLayout(title);
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

        auctionListTable(apiUrl, actionType, title, id);
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
            loadAuctionData(apiEndpoints[clickedClass].url, apiEndpoints[clickedClass].title);
        }
    });


    // Load default table on page load
    loadAuctionData(SITE_URL + "api/auction/fetch_current_active_auctions_by_user", "Active Auctions Bid On");
});