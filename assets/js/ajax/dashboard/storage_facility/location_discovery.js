$(document).ready(function () {
    let discoveredLocations = [];
    let currentCompanyId = null;

    // Trigger discovery from button click
    window.triggerLocationDiscovery = function (companyId) {
        currentCompanyId = companyId;
        $('#locationDiscoveryModal').modal('show');
        resetModalState();
    };

    function resetModalState() {
        $('#paste-input-container').removeClass('d-none');
        $('#discovery-loading, #discovery-results, #discovery-error').addClass('d-none');
        $('#paste-input').val('');
        $('#btn-parse-paste').prop('disabled', true);
        $('#btn-onboard-all').prop('disabled', true);
        discoveredLocations = [];
    }

    // Handle Paste Input changes - handle multiple events to be sure
    $('#paste-input').on('input keyup change paste', function () {
        setTimeout(() => {
            const val = $(this).val().trim();
            $('#btn-parse-paste').prop('disabled', val.length < 5);
        }, 100);
    });

    // Handle Parse Paste button
    $('#btn-parse-paste').on('click', function () {
        const rawText = $('#paste-input').val().trim();
        console.log("Parsing text:", rawText);
        const parsedLocations = parseGMBText(rawText);

        console.log("Parsed locations:", parsedLocations);

        if (parsedLocations.length === 0) {
            Swal.fire({
                title: 'No Locations Found',
                html: 'We couldn\'t find any business names and addresses in the text you pasted.<br><br><strong>Tip:</strong> Please copy the <u>full list</u> from your Google Business Profile page.',
                icon: 'warning'
            });
            return;
        }

        discoverLocations(currentCompanyId, parsedLocations, rawText);
    });

    function parseGMBText(text) {
        if (!text) return [];

        const lines = text.split('\n').map(l => l.trim()).filter(l => l.length > 0);
        const locations = [];

        console.log("Analyzing lines:", lines);

        for (let i = 0; i < lines.length; i++) {
            const line = lines[i];

            // Address pattern: usually contains numbers and some common address words or state abbreviations
            const isAddress = line.match(/\d+/) &&
                (line.includes(',') ||
                    line.match(/[A-Z]{2,3}\s\d{4,5}/) ||
                    line.match(/Rd|St|Ave|Blvd|Hwy|Pwy|Drive|Lane|Street|Road|Boulevard|Highway|Queensland|Victoria|NSW|WA|SA|TAS|ACT|NT/i));

            if (isAddress) {
                let name = "Unknown";
                let address = line;

                if (i > 0) {
                    const prevLine = lines[i - 1];
                    const statusWords = ['Verified', 'Unverified', 'Process', 'Pending', 'Suspended'];
                    if (!statusWords.some(word => prevLine.includes(word)) && prevLine.length > 2) {
                        name = prevLine;
                    }
                } else if (line.includes(',')) {
                    // Try to extract name from same line if it's the first line
                    const parts = line.split(',');
                    if (parts.length > 0) {
                        // Use parts[0] as name if it's not the full address
                        name = parts[0].trim();
                    }
                }

                if (!locations.some(loc => loc.address === address)) {
                    locations.push({
                        name: name,
                        address: address
                    });
                }
            }
        }

        // Final attempt: If still nothing found, but there's a line with a comma and numbers
        if (locations.length === 0 && lines.length > 0) {
            lines.forEach(line => {
                if (line.includes(',') && line.match(/\d+/)) {
                    const parts = line.split(',');
                    locations.push({
                        name: parts[0].trim(),
                        address: line
                    });
                }
            });
        }

        // Final fallback: If even that fails, but there is ANY text, just wrap it
        if (locations.length === 0 && lines.length > 0) {
            locations.push({
                name: "Location from Text",
                address: lines[0]
            });
        }

        return locations;
    }

    function discoverLocations(companyId, parsedList, rawText) {
        // Show loading state
        $('#paste-input-container').addClass('d-none');
        $('#discovery-loading').removeClass('d-none');
        $('#loading-text').text('AI is verifying locations and fetching details...');

        $.ajax({
            url: base_url + 'api/companies/discover-locations',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                company_id: companyId,
                manual_list: parsedList,
                raw_text: rawText // Send raw text for better context
            }),
            success: function (response) {
                if (response.status === 'success') {
                    discoveredLocations = response.data;
                    renderLocationTable(response.data);
                    $('#discovery-loading').addClass('d-none');
                    $('#discovery-results').removeClass('d-none');
                } else {
                    showError(response.message, response.raw);
                }
            },
            error: function (xhr) {
                let errorMsg = 'Failed to discover locations';
                let raw = null;
                if (xhr.responseJSON) {
                    errorMsg = xhr.responseJSON.message || errorMsg;
                    raw = xhr.responseJSON.raw || null;
                }
                showError(errorMsg, raw);
            }
        });
    }

    function renderLocationTable(locations) {
        const tbody = $('#locations-table-body');
        tbody.empty();

        // Hide loading and show results
        $('#discovery-loading').addClass('d-none');
        $('#discovery-results').removeClass('d-none');

        if (locations.length === 0) {
            tbody.append(`
                <tr>
                    <td colspan="5" class="text-center py-4 text-muted">
                        <i class="bi bi-search text-warning d-block mb-2" style="font-size: 2rem;"></i>
                        No locations found or verified. Try manual entry.
                    </td>
                </tr>
            `);
            $('#btn-onboard-all').prop('disabled', true);
            return;
        }

        locations.forEach((location, index) => {
            const hoursDisplay = formatHours(location.hours);
            tbody.append(`
                <tr data-index="${index}">
                    <td>
                        <input type="text" class="form-control form-control-sm border-0 bg-light" 
                               value="${escapeHtml(location.name)}" 
                               data-field="name">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm border-0 bg-light" 
                               value="${escapeHtml(location.address)}" 
                               data-field="address">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm border-0 bg-light" 
                               value="${escapeHtml(location.phone || '')}" 
                               data-field="phone">
                    </td>
                    <td>
                        <input type="text" class="form-control form-control-sm border-0 bg-light" 
                               value="${escapeHtml(location.website || '')}" 
                               data-field="website">
                    </td>
                    <td>
                        <div class="small text-muted text-truncate" style="max-width: 150px;" title="${escapeHtml(hoursDisplay)}">
                            ${hoursDisplay}
                        </div>
                    </td>
                </tr>
            `);
        });

        $('#btn-onboard-all').prop('disabled', false);

        // Handle inline editing
        tbody.find('input').on('change', function () {
            const index = $(this).closest('tr').data('index');
            const field = $(this).data('field');
            discoveredLocations[index][field] = $(this).val();
        });
    }

    function formatHours(hours) {
        if (!hours || typeof hours !== 'object') return 'Not available';
        const days = ['mon', 'tue', 'wed', 'thu', 'fri', 'sat', 'sun'];
        return days.map(day => {
            const time = hours[day] || 'closed';
            return `${day.toUpperCase()}: ${time}`;
        }).join(', ');
    }

    function showError(message, rawResponse = null) {
        $('#discoveryTabsContent .tab-pane').addClass('d-none');
        $('#discovery-loading').addClass('d-none');
        $('#discovery-error').removeClass('d-none');

        // Enhanced error message with helpful context
        let displayMessage = message;
        if (message.includes('temporarily unavailable') || message.includes('quota') || message.includes('high demand')) {
            displayMessage = message + '<br><br><small class="text-muted">You can add locations manually from the Storage Facilities page.</small>';
        }

        if (rawResponse) {
            displayMessage += `<div class="mt-4 text-start"><details><summary class="text-muted small cursor-pointer">View Technical Details</summary><pre class="bg-light p-2 border rounded mt-2 small text-wrap" style="max-height: 150px; overflow-y: auto; font-size: 11px;">${escapeHtml(rawResponse)}</pre></details></div>`;
        }

        $('#error-message').html(displayMessage);
    }

    // Onboard all locations
    $('#btn-onboard-all').on('click', function () {
        if (discoveredLocations.length === 0) {
            Swal.fire('Error', 'No locations to onboard', 'error');
            return;
        }

        const btn = $(this);
        const originalText = btn.html();
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span>Onboarding...');

        $.ajax({
            url: base_url + 'api/companies/onboard-locations',
            type: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({
                company_id: currentCompanyId,
                locations: discoveredLocations
            }),
            success: function (response) {
                if (response.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: 'Success!',
                        text: response.message,
                        showConfirmButton: true
                    }).then(() => {
                        $('#locationDiscoveryModal').modal('hide');
                        // Reload page or refresh location list
                        window.location.reload();
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                    btn.prop('disabled', false).html(originalText);
                }
            },
            error: function (xhr) {
                let errorMsg = 'Failed to onboard locations';
                if (xhr.responseJSON && xhr.responseJSON.message) {
                    errorMsg = xhr.responseJSON.message;
                }
                Swal.fire('Error', errorMsg, 'error');
                btn.prop('disabled', false).html(originalText);
            }
        });
    });

    // Retry discovery
    $('#btn-retry-discovery').on('click', function () {
        resetModalState();
    });

    function escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text ? text.replace(/[&<>"']/g, m => map[m]) : '';
    }
});
