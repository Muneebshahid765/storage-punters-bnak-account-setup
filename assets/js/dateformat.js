// ✅ Auto-format all date strings on page load & DataTables redraw
$(document).ready(function () {

    function formatDateString(dateStr) {
        if (!dateStr) return dateStr;

        // Match formats like "2025-09-26 10:00 PM" or just "2025-09-26"
        const match = dateStr.match(/^(\d{4})-(\d{2})-(\d{2})(?:\s+(\d{1,2}:\d{2}\s*[APMapm]{2}))?$/);
        if (!match) return dateStr; // Not a recognized format

        const [_, year, month, day, time] = match;
        const months = ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"];
        const formattedDate = `${parseInt(day)} ${months[parseInt(month) - 1]} ${year}`;
        return time ? `${formattedDate}, ${time.toUpperCase()}` : formattedDate;
    }

    function reformatDates() {
        $('td, span, div').each(function () {
            const text = $(this).text().trim();
            // Match YYYY-MM-DD or YYYY-MM-DD HH:mm:ss
            if (/^\d{4}-\d{2}-\d{2}(\s\d{2}:\d{2}:\d{2})?$/.test(text)) {
                const m = moment(text);
                if (m.isValid()) {
                    $(this).text(m.format('DD/MM/YYYY'));
                }
            } else if (/^\d{4}-\d{2}-\d{2}\s\d{1,2}:\d{2}\s*[APM]{2}$/i.test(text)) {
                // Match "2025-09-26 10:00 PM"
                const m = moment(text, 'YYYY-MM-DD h:mm A');
                if (m.isValid()) {
                    $(this).text(m.format('DD/MM/YYYY'));
                }
            }
        });
    }

    // Run once on load
    reformatDates();

    // Re-run on DataTables redraw (pagination, sort, search)
    $(document).on('draw.dt', function () {
        reformatDates();
    });

    // Optional: re-run when data changes dynamically
    $(document).on('change', function () {
        reformatDates();
    });
});
