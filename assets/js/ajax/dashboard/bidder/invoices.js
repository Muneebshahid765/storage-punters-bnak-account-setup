$(document).ready(function () {
    const table = $('#table-invoices').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: SITE_URL + "api/invoices/get",
            type: "POST",
            dataSrc: (json) => {
                if (json.status === "success") {
                    return json.result.data;
                }
                return [];
            },
            data: (d) => {
                // Add any extra filters if needed
            },
            error: (xhr) => {
                if (xhr.status === 401) {
                    window.location.href = SITE_URL + 'login';
                }
            }
        },
        columns: [
            { data: 'invoice_number', className: 'fw-bold' },
            {
                data: 'invoice_date',
                render: (data) => data ? moment(data).format('DD/MM/YYYY') : 'N/A'
            },
            { data: 'facility_name' },
            {
                data: null,
                render: (data) => `<b>${data.invoice_currency} ${parseFloat(data.invoice_total_amount).toFixed(2)}</b>`
            },
            {
                data: 'payment_status',
                render: (data) => {
                    const statusClass = data === 'paid' ? 'bg-success' : (data === 'pending' ? 'bg-warning' : 'bg-secondary');
                    return `<span class="badge ${statusClass} text-capitalize">${data}</span>`;
                }
            },
            {
                data: 'invoice_id',
                render: (data) => `
                    <a href="${SITE_URL}invoices/download/${data}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                        <i class="bi bi-download me-1"></i> Download PDF
                    </a>
                `
            }
        ],
        order: [[1, 'desc']], // Sort by date by default
        pageLength: 10,
        language: {
            emptyTable: "No invoices found for your won auctions.",
            processing: '<div class="spinner-border text-primary spinner-border-sm" role="status"></div> Loading invoices...'
        }
    });
});
