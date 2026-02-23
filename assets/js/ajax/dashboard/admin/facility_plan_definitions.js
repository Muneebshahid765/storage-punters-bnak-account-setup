$(document).ready(function () {
    loadFacilityPlans();

    function loadFacilityPlans() {
        $.ajax({
            url: base_url + 'api/admin/facility-plans/list',
            type: 'GET',
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    renderFacilityPlans(res.plans);
                }
            },
            error: function (xhr) {
                console.error('Error loading facility plans:', xhr.responseText);
            }
        });
    }

    function renderFacilityPlans(plans) {
        let html = '';
        plans.forEach(plan => {
            let featuresHtml = '';
            const allFeatures = [
                'Basic AI Auction creation process',
                'Advanced AI Auction creation process',
                'AI Estimate value (based on images)',
                'Run local ads for the duration of my auction',
                'Advanced reporting',
                'API Access'
            ];

            allFeatures.forEach(feature => {
                const included = plan.features.includes(feature);
                featuresHtml += `
                    <div class="d-flex align-items-center mb-2">
                        <i class="bi bi-check-square-fill me-2 ${included ? 'text-success' : 'text-muted opacity-25'}"></i>
                        <span class="small ${included ? '' : 'text-muted'}">${feature}</span>
                    </div>
                `;
            });

            html += `
                <div class="col-md-6 mb-4">
                    <div class="settings-card-modern h-100 d-flex flex-column">
                        <h3 class="fw-bold mb-3">${plan.plan_name}</h3>
                        <div class="mb-4">
                            <h2 class="display-6 fw-bold mb-0 text-primary">$${parseFloat(plan.monthly_price).toFixed(2)} <span class="fs-6 text-muted">/monthly</span></h2>
                            <div class="mt-2">
                                <label class="small text-muted fw-bold">Annual Price</label>
                                <div class="fw-bold">$${parseFloat(plan.annual_price).toFixed(2)}</div>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label class="small text-muted fw-bold mb-2 d-block">Feature Inclusions</label>
                            ${featuresHtml}
                        </div>

                        <div class="d-flex gap-2 justify-content-end mt-auto pt-3 border-top">
                            <button class="btn btn-outline-secondary btn-sm edit-f-plan" data-id="${plan.id}" data-name="${plan.plan_name}" data-monthly="${plan.monthly_price}" data-annual="${plan.annual_price}" data-features='${JSON.stringify(plan.features)}'>Update Plan</button>
                            <button class="btn btn-danger btn-sm delete-f-plan" data-id="${plan.id}">Delete</button>
                        </div>
                    </div>
                </div>
            `;
        });
        $('#facility-plans-list').html(html);
    }

    // Create Facility Plan
    $('#create-facility-plan-form').on('submit', function (e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.ajax({
            url: base_url + 'api/admin/facility-plans/create',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    alert(res.message);
                    $('#create-facility-plan-form')[0].reset();
                    $('#createFacilityPlanModal').modal('hide');
                    loadFacilityPlans();
                } else {
                    alert('Error: ' + res.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
            }
        });
    });

    // Edit Trigger
    $(document).on('click', '.edit-f-plan', function () {
        const data = $(this).data();
        $('#edit-f-plan-id').val(data.id);
        $('#edit-f-name').val(data.name);
        $('#edit-f-monthly').val(data.monthly);
        $('#edit-f-annual').val(data.annual);

        const allFeatures = [
            'Basic AI Auction creation process',
            'Advanced AI Auction creation process',
            'AI Estimate value (based on images)',
            'Run local ads for the duration of my auction',
            'Advanced reporting',
            'API Access'
        ];

        let featuresHtml = '';
        allFeatures.forEach(feature => {
            const checked = data.features.includes(feature) ? 'checked' : '';
            featuresHtml += `
                <div class="form-check mb-2">
                    <input class="form-check-input" type="checkbox" name="features[]" value="${feature}" id="edit-f-${feature.replace(/\s+/g, '-')}" ${checked}>
                    <label class="form-check-label small" for="edit-f-${feature.replace(/\s+/g, '-')}">${feature}</label>
                </div>
            `;
        });
        $('#edit-f-features-container').html(featuresHtml);
        $('#editFacilityPlanModal').modal('show');
    });

    // Save Edit
    $('#save-edit-f-plan').on('click', function () {
        const formData = $('#edit-facility-plan-form').serialize();
        $.ajax({
            url: base_url + 'api/admin/facility-plans/update',
            type: 'POST',
            data: formData,
            dataType: 'json',
            success: function (res) {
                if (res.success) {
                    alert(res.message);
                    $('#editFacilityPlanModal').modal('hide');
                    loadFacilityPlans();
                } else {
                    alert('Error: ' + res.message);
                }
            },
            error: function (xhr) {
                console.error(xhr.responseText);
            }
        });
    });

    // Delete
    $(document).on('click', '.delete-f-plan', function () {
        if (confirm('Are you sure you want to delete this facility plan?')) {
            const planId = $(this).data('id');
            $.ajax({
                url: base_url + 'api/admin/facility-plans/delete',
                type: 'POST',
                data: { plan_id: planId },
                dataType: 'json',
                success: function (res) {
                    if (res.success) {
                        alert(res.message);
                        loadFacilityPlans();
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
