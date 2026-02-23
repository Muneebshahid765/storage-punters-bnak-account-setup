$(document).ready(function () {
    // Initialize DataTable
    const subscriptionsTable = $('#subscriptions-table').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: base_url + 'api/admin/subscriptions/list',
            type: 'POST',
            data: function (d) {
                d.search = $('#subscription-search').val();
            }
        },
        columns: [
            { data: 'user_id' },
            {
                data: null,
                render: function (data) {
                    return data.first_name + ' ' + data.last_name;
                }
            },
            { data: 'email' },
            {
                data: null,
                render: function (data) {
                    return `${data.plan_name || 'N/A'} / ${data.billing_cycle || 'N/A'}`;
                }
            },
            {
                data: 'amount',
                render: function (data) {
                    return data ? '$' + parseFloat(data).toFixed(2) : 'N/A';
                }
            },
            {
                data: 'payment_status',
                render: function (data) {
                    let badgeClass = 'bg-secondary';
                    if (data == 'Paid') badgeClass = 'bg-success';
                    else if (data == 'Due') badgeClass = 'bg-warning';

                    return `<span class="badge ${badgeClass}">${data || 'N/A'}</span>`;
                }
            },
            { data: 'next_due_date' },
            {
                data: null,
                orderable: false,
                render: function (data, type, row) {
                    return `
                        <div class="btn-group btn-group-sm">
                            <button class="btn btn-primary change-plan" 
                                data-id="${row.user_id}"
                                data-plan="${row.plan_name}"
                                data-cycle="${row.billing_cycle}">Change Plan</button>
                            <button class="btn btn-danger cancel-subscription" data-id="${row.user_id}">Cancel</button>
                        </div>
                    `;
                }
            }
        ],
        pageLength: 10,
        order: [[0, 'desc']]
    });

    // Search functionality
    $('#subscription-search').on('keyup', function () {
        subscriptionsTable.ajax.reload();
    });

    // Change Plan
    $(document).on('click', '.change-plan', function () {
        const userId = $(this).data('id');
        const planName = $(this).data('plan');
        const billingCycle = $(this).data('cycle');

        $('#plan-user-id').val(userId);
        $('#plan-name').val(planName);
        $('#billing-cycle').val(billingCycle);

        $('#changePlanModal').modal('show');
    });

    // Save Plan
    $('#save-plan-btn').on('click', function () {
        const formData = $('#change-plan-form').serialize();

        $.ajax({
            url: base_url + 'api/admin/subscriptions/change-plan',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (result) {
                if (result.success) {
                    alert('Subscription plan updated successfully');
                    $('#changePlanModal').modal('hide');
                    subscriptionsTable.ajax.reload();
                } else {
                    alert('Error updating subscription plan: ' + (result.message || 'Unknown error'));
                }
            },
            error: function (xhr) {
                console.error('Error updating subscription plan:', xhr.responseText);
            }
        });
    });

    // Cancel Subscription
    $(document).on('click', '.cancel-subscription', function () {
        const userId = $(this).data('id');

        if (confirm('Are you sure you want to cancel this subscription? This action cannot be undone.')) {
            $.ajax({
                url: base_url + 'api/admin/subscriptions/cancel',
                type: 'POST',
                data: { user_id: userId },
                dataType: 'json',
                success: function (result) {
                    if (result.success) {
                        alert('Subscription cancelled successfully');
                        subscriptionsTable.ajax.reload();
                    } else {
                        alert('Error cancelling subscription: ' + (result.message || 'Unknown error'));
                    }
                },
                error: function (xhr) {
                    console.error('Error cancelling subscription:', xhr.responseText);
                }
            });
        }
    });
});
