$(document).ready(function () {
    loadCoinPackages();
    loadCashPackages();

    // Global Rules Update
    $('#form-rules').submit(function (e) {
        e.preventDefault();
        const data = $(this).serialize();
        $.post(SITE_URL + 'admin/economy/update-rules', data, function (res) {
            if (res.success) {
                showNotification(res.message, 'success');
            } else {
                showNotification(res.message, 'error');
            }
        }, 'json');
    });

    // Package Form Submit
    $('#form-package').submit(function (e) {
        e.preventDefault();
        const type = $('#package-type').val();
        const url = type === 'coin' ? 'admin/economy/save-coin-package' : 'admin/economy/save-cash-package';

        // Map common fields to specific package fields
        const id = $('#package-id').val();
        const name = $('#package-name').val();
        const cost = $('#package-cost').val();
        const entries = $('#package-entries').val();
        const active = $('#package-active').is(':checked') ? 1 : 0;

        const data = {
            id: id,
            package_name: name,
            entry_amount: entries,
            is_active: active
        };

        if (type === 'coin') {
            data.coin_cost = cost;
        } else {
            data.price_aud = cost;
        }

        $.post(SITE_URL + url, data, function (res) {
            if (res.success) {
                showNotification(res.message, 'success');
                $('#packageModal').modal('hide');
                if (type === 'coin') loadCoinPackages(); else loadCashPackages();
            } else {
                showNotification(res.message, 'error');
            }
        }, 'json');
    });
});

function loadCoinPackages() {
    $.get(SITE_URL + 'admin/economy/list-coin-packages', function (res) {
        if (res.success) {
            const tbody = $('#table-coin-packages tbody');
            tbody.empty();
            res.data.forEach(p => {
                tbody.append(`
                    <tr>
                        <td>${p.package_name}</td>
                        <td>${p.coin_cost} Coins</td>
                        <td>${p.entry_amount}</td>
                        <td><span class="badge ${p.is_active == 1 ? 'bg-success' : 'bg-secondary'}">${p.is_active == 1 ? 'Active' : 'Inactive'}</span></td>
                        <td>
                            <button class="btn btn-sm btn-light" onclick='editPackage(${JSON.stringify(p)}, "coin")'>Edit</button>
                        </td>
                    </tr>
                `);
            });
        }
    }, 'json');
}

function loadCashPackages() {
    $.get(SITE_URL + 'admin/economy/list-cash-packages', function (res) {
        if (res.success) {
            const tbody = $('#table-cash-packages tbody');
            tbody.empty();
            res.data.forEach(p => {
                tbody.append(`
                    <tr>
                        <td>${p.package_name}</td>
                        <td>$${p.price_aud}</td>
                        <td>${p.entry_amount}</td>
                        <td><span class="badge ${p.is_active == 1 ? 'bg-success' : 'bg-secondary'}">${p.is_active == 1 ? 'Active' : 'Inactive'}</span></td>
                        <td>
                            <button class="btn btn-sm btn-light" onclick='editPackage(${JSON.stringify(p)}, "cash")'>Edit</button>
                        </td>
                    </tr>
                `);
            });
        }
    }, 'json');
}

function openPackageModal(type) {
    $('#form-package')[0].reset();
    $('#package-id').val('');
    $('#package-type').val(type);
    $('#packageModalLabel').text(type === 'coin' ? 'New Coin Package' : 'New Cash Package');
    $('#label-cost').text(type === 'coin' ? 'Coin Cost' : 'Price (AUD)');
    $('#packageModal').modal('show');
}

function editPackage(p, type) {
    $('#package-type').val(type);
    $('#package-id').val(p.id);
    $('#package-name').val(p.package_name);
    $('#package-cost').val(type === 'coin' ? p.coin_cost : p.price_aud);
    $('#package-entries').val(p.entry_amount);
    $('#package-active').prop('checked', p.is_active == 1);

    $('#packageModalLabel').text(type === 'coin' ? 'Edit Coin Package' : 'Edit Cash Package');
    $('#label-cost').text(type === 'coin' ? 'Coin Cost' : 'Price (AUD)');
    $('#packageModal').modal('show');
}
