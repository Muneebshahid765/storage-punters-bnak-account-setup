$(document).ready(function () {
    loadBidderPlans();

    function loadBidderPlans() {
        $.ajax({
            url: base_url + 'api/admin/bidder-plans/list',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    $('#base-fee-display').text(parseFloat(res.base_fee).toFixed(1));
                    $('#base-fee-input').val(res.base_fee);
                    renderPlans(res.plans, res.base_fee);
                }
            },
            error: function (xhr) {
                console.error('Error loading plans:', xhr.responseText);
            }
        });
    }

    function renderPlans(plans, baseFee) {
        let html = '';
        plans.forEach(plan => {
            const reduction = (baseFee - plan.fee_reduction).toFixed(1);
            html += `
                <div class="col-md-6 mb-4">
                    <div class="settings-card-modern h-100">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <div>
                                <h3 class="fw-bold mb-0">${plan.plan_name}</h3>
                                <h2 class="text-primary fw-bold display-5 mb-0">${parseFloat(plan.fee_reduction).toFixed(1)}%</h2>
                                <p class="text-muted small">Fee Reduction: Reduces fee from ${baseFee}% down to ${plan.fee_reduction}%.</p>
                            </div>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label small fw-bold text-muted">Price (28 Days)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control" value="${plan.price_28_days}" readonly>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label small fw-bold text-muted">Price (Year)</label>
                            <div class="input-group">
                                <span class="input-group-text">$</span>
                                <input type="text" class="form-control" value="${plan.price_year}" readonly>
                            </div>
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-auto">
                            <button class="btn btn-outline-secondary edit-plan" data-id="${plan.id}" data-name="${plan.plan_name}" data-reduction="${plan.fee_reduction}" data-price28="${plan.price_28_days}" data-priceyear="${plan.price_year}">Update</button>
                            <button class="btn btn-danger delete-plan" data-id="${plan.id}">Delete</button>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#plans-container').html(html);
    }

    // Update Base Fee
    $('#save-base-fee-btn').on('click', function () {
        const baseFee = $('#base-fee-input').val();
        $.ajax({
            url: base_url + 'api/admin/bidder-plans/update-base-fee',
            type: 'POST',
            data: { base_fee: baseFee },
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    alert(res.message);
                    $('#editBaseFeeModal').modal('hide');
                    loadBidderPlans();
                } else {
                    alert('Error: ' + res.message);
                }
            },
            error: function (xhr) {
                alert('An error occurred. Please check console for details.');
                console.error(xhr.responseText);
            }
        });
    });

    // Create New Plan
    $('#create-plan-btn').on('click', function () {
        const formData = $('#create-plan-form').serialize();
        $.ajax({
            url: base_url + 'api/admin/bidder-plans/create',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    alert(res.message);
                    $('#createPlanModal').modal('hide');
                    $('#create-plan-form')[0].reset();
                    loadBidderPlans();
                } else {
                    alert('Error: ' + res.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
            }
        });
    });

    // Edit Plan Trigger
    $(document).on('click', '.edit-plan', function () {
        const data = $(this).data();
        $('#edit-plan-id').val(data.id);
        $('#edit-fee-reduction').val(data.reduction);
        $('#edit-price-28').val(data.price28);
        $('#edit-price-year').val(data.priceyear);
        $('#editPlanModal').modal('show');
    });

    // Update Plan
    $('#update-plan-btn').on('click', function () {
        const formData = $('#edit-plan-form').serialize();
        $.ajax({
            url: base_url + 'api/admin/bidder-plans/update',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    alert(res.message);
                    $('#editPlanModal').modal('hide');
                    loadBidderPlans();
                } else {
                    alert('Error: ' + res.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
            }
        });
    });

    // Delete Plan
    $(document).on('click', '.delete-plan', function () {
        if (confirm('Are you sure you want to delete this plan?')) {
            const planId = $(this).data('id');
            $.ajax({
                url: base_url + 'api/admin/bidder-plans/delete',
                type: 'POST',
                data: { plan_id: planId },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        alert(res.message);
                        loadBidderPlans();
                    } else {
                        alert('Error: ' + res.message);
                    }
                },
                error: function (xhr) {
                    console.error(xhr.responseText);
                }
            });
        }
    });
});
