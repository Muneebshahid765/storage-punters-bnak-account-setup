$(document).ready(function () {

    var interval = "yearly"; // default
    var membershipData = [];
    var userSub = null; // active subscription (if any)
    var currentCurrency = "AUD"; // Default to AUD

    // Utility: calculate savings
    function calculateSavings(yearPlan, monthPlan) {
        if (!yearPlan || !monthPlan) return null;

        let yearlyPerMonth = yearPlan.price / 12;
        let savings = ((monthPlan.price * 12) - yearPlan.price).toFixed(2);

        return { yearlyPerMonth, savings };
    }

    // Render list of memberships (MODAL)
    function renderMembershipList(memberships, target, interval, activeProductId = null, activePriceId = null) {
        memberships.sort((a, b) => a.membership.product_order - b.membership.product_order);

        let html = `<div class="row g-4 justify-content-center">`;

        memberships.forEach(m => {
            let yearPlan = m.plans.find(p => p.interval === "yearly");
            let monthPlan = m.plans.find(p => p.interval === "monthly");
            let plan = (interval === "yearly") ? yearPlan : monthPlan;
            if (!plan) return;

            // Determine if this is the "Most Popular" plan (Professional Punter)
            // Adjust the product_id check if necessary based on real data
            let isMostPopular = m.membership.name.toLowerCase().includes("professional");

            let isActivePlan = (activeProductId === m.membership.product_id && activePriceId === plan.price_id);
            // let isActiveProduct = (activeProductId === m.membership.product_id); // Not strictly needed for UI style but good for logic

            // Savings calculation
            let savingsText = "";
            if (interval === "yearly" && yearPlan && monthPlan) {
                let savings = calculateSavings(yearPlan, monthPlan);
                if (savings) {
                    savingsText = `
                        <div class="mt-2 w-100 py-2 rounded text-center small fw-bold text-white fs-12" style="background-color: #0b2e23;">
                             ${savings.yearlyPerMonth.toFixed(2)} ${plan.currency}/month<br>
                             You save ${savings.savings} ${plan.currency} per year!
                        </div>
                    `;
                }
            }


            // Button Configuration
            let buttonHtml = "";
            let buttonClass = isMostPopular ? "btn-success" : "btn-dark"; // Green for Prof, Dark for Occasional (from image)
            // If strictly following the image:
            // "Occasional Punter" -> Dark Button "Change Plan"
            // "Professional Punter" -> Green Button "Switch Plan"
            // But if it's the active plan, maybe show "Current Plan" or disable it?
            // The image shows "Change Plan" for Occasional and "Switch Plan" for Professional.

            let btnLabel = isActivePlan ? "Current Plan" : (isMostPopular ? "Switch Plan" : "Change Plan");
            let btnStyle = isMostPopular ? "background-color: #009865; border-color: #009865;" : "background-color: #1a3c2f; border-color: #1a3c2f;"; // Custom colors from image approximation

            if (isActivePlan) {
                buttonHtml = `<button class="btn w-100 fw-bold border-0 text-white mt-auto py-2" style="background-color: #6c757d; cursor: default;" disabled>${btnLabel}</button>`;
            } else {
                buttonHtml = `<button class="btn w-100 fw-bold border-0 text-white mt-auto py-2 selectable-plan" 
                                style="${btnStyle}"
                                data-price="${plan.price_id}"
                                data-name="${m.membership.name}"
                                data-product="${m.membership.product_id}"
                                data-interval="${plan.interval}">
                                ${btnLabel}
                            </button>`;
            }

            // Badge (Most Popular)
            let badgeHtml = isMostPopular ?
                `<span class="position-absolute top-0 end-0 translate-middle badge rounded-pill fw-normal px-3 py-2" style="background-color: #009865; font-size: 12px; transform: translate(10px, -50%) !important;">Most Popular</span>` :
                "";

            // Border color: Green for Most Popular, none/light for others
            let borderStyle = isMostPopular ? "border: 2px solid #009865;" : "border: 1px solid #dee2e6;";


            html += `
                <div class="col-md-6 d-flex align-items-stretch">
                    <div class="card w-100 shadow-sm position-relative p-4 d-flex flex-column h-100" style="${borderStyle} border-radius: 12px;">
                        ${badgeHtml}
                        
                        <h5 class="fw-bold mb-3 d-flex align-items-center">
                            <i class="bi bi-star${isMostPopular ? '-fill text-warning' : ''} me-2"></i> ${m.membership.name}
                        </h5>

                        <ul class="list-unstyled mb-4 flex-grow-1 small text-muted">
                            ${m.membership.description
                    .split('.')
                    .map(s => s.trim() ? `<li class="mb-2 d-flex align-items-start"><i class="bi bi-dot me-1 fs-5 lh-1"></i> ${s}</li>` : '')
                    .join('')
                }
                             ${plan.price_id === "price_1S6DpJ07pHCLCmTy0n3OX1Xn" &&
                    m.membership.limited_offer_text &&
                    m.membership.limited_offer_text !== "-"
                    ? `<li class="mt-2"><span class="fw-bold text-dark">${m.membership.limited_offer_text}</span></li>`
                    : ""
                }
                        </ul>

                         <div class="mb-3">
                            <h5 class="fw-bold mb-0">${plan.price} ${plan.currency} / ${interval}</h5>
                        </div>

                        ${buttonHtml}

                        ${savingsText}
                    </div>
                </div>
            `;
        });

        html += `</div>`;

        // Fee Notice Logic
        let defaultFee = 17.5;
        let currentFee = defaultFee;
        if (activeProductId) {
            let active = memberships.find(m => m.membership.product_id === activeProductId);
            if (active) {
                let fee = parseFloat(active.membership.commission_rate);
                if (!isNaN(fee)) currentFee = fee;
            }
        }

        html += `
          <div class="mt-4 py-3 px-3 rounded text-center" style="background: #f1f3f5;">
              <p class="mb-0 fw-bold text-dark fs-6">
                Your current Auction Win Fees are +${currentFee}%
                <br><span class="fw-normal text-muted small">Upgrade to reduce or eliminate fees.</span>
              </p>
          </div>
        `;

        $(target).html(html);
    }

    // Skeleton Loader
    function renderMembershipListSkeleton(target = ".membership-container") {
        $(target).html(`
           <div class="card border-0 shadow-sm rounded-4 p-4">
                <div class="skeleton skeleton-title mb-3" style="width: 40%;"></div>
                 <div class="skeleton skeleton-text mb-2" style="width: 30%;"></div>
                 <div class="skeleton skeleton-text mb-4" style="width: 20%;"></div>
                  <div class="skeleton skeleton-btn" style="width: 15%;"></div>
           </div>
        `);
    }

    // No Membership State
    function renderNoMembership(memberships, profile) {
        $(".membership-container").html(`
            <div class="alert alert-light border shadow-sm rounded-3 p-4 text-center">
                 <div class="mb-3">
                    <i class="bi bi-stars text-warning fs-1"></i>
                 </div>
                 <h4 class="fw-bold">Upgrade Your Experience</h4>
                 <p class="text-muted mb-4">You are currently on the free tier. Subscribe to save on fees and unlock exclusive perks.</p>
                 <button id="btn-buy-membership" class="btn btn-success fw-bold px-4 py-2" style="background-color: #0d3b2e; border: none;">
                    View Plans
                 </button>
            </div>
        `);

        $(document).off("click", "#btn-buy-membership").on("click", "#btn-buy-membership", function () {
            renderMembershipList(membershipData, "#membership-modal-body", interval);
            $("#membershipModal").modal("show");
        });
    }


    // Render Active Subscription (Image 1 Design)
    function renderActiveMembership(data) {
        let userSub = data.subscriptions[0];
        let activeProductId = userSub.plan.product_id;
        let currentMember = membershipData.find(m => m.membership.product_id === activeProductId);
        let currentName = currentMember?.membership?.name || userSub.plan.name || "Active Plan";
        let isCancelled = userSub.membership_status === "inactive"; // Pending cancellation

        let statusBadge = isCancelled
            ? `<div class="alert alert-warning p-2 small mb-3"><i class="bi bi-exclamation-triangle-fill me-1"></i> Cancellation Requested. Ends on ${userSub.current_period_end}</div>`
            : "";

        $(".membership-container").html(`
             <div class="card bg-white border shadow-sm rounded-3 p-0 overflow-hidden">
                <div class="card-header bg-white border-bottom p-3 d-flex justify-content-between align-items-center">
                     <h5 class="mb-0 fw-bold text-dark">Manage Subscription (${isCancelled ? 'Cancel' : 'Active'})</h5>
                </div>
                <div class="card-body p-4 row">
                     <div class="col-md-7 mb-4 mb-md-0">
                        <p class="text-muted mb-0">
                            Choose a membership plan that suits your bidding style. Enjoy lower fees, exclusive perks, and more opportunities to win auctions with confidence.
                        </p>
                     </div>
                     <div class="col-md-5">
                          <div class="border rounded-3 p-3 position-relative">
                               <h6 class="fw-bold text-success mb-2 d-flex align-items-center">
                                    <i class="bi bi-arrow-right-circle-fill me-2"></i> ${currentName}
                               </h6>
                               <p class="mb-1 text-muted small">${userSub.plan.amount} ${userSub.plan.currency} / ${userSub.plan.interval.replace('ly', '')}</p>
                               <p class="mb-3 text-muted small">Expires: ${userSub.current_period_end}</p>
                               
                               ${statusBadge}

                               <button id="btn-change-membership" class="btn btn-success w-100 fw-bold" style="background-color: #007550; border: none;">
                                    Change Membership
                               </button>

                               ${!isCancelled ? `<button id="btn-cancel-membership" class="btn btn-outline-danger btn-sm w-100 mt-2 border-0">Cancel Subscription</button>` : ''}

                          </div>
                     </div>
                </div>
             </div>
        `);

        $(document).off("click", "#btn-change-membership").on("click", "#btn-change-membership", function () {
            renderMembershipList(membershipData, "#membership-modal-body", interval, activeProductId, userSub.plan.price_id);
            $("#membershipModal").modal("show");
        });

        // --- VAULT: Retention Intercept ---
        $(document).off("click", "#btn-cancel-membership").on("click", "#btn-cancel-membership", function () {
            // Reset button state
            $("#btn-claim-retention").prop('disabled', false).html('<i class="bi bi-check-circle-fill me-2"></i> Claim 5 Free Entries');
            $("#retentionModal").modal("show");
        });
    }


    // Fetch plans from backend
    function fetchMembershipProduct() {
        $.ajax({
            url: SITE_URL + "api/membership/plan/get",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({ interval: interval }),
            beforeSend: function () {
                renderMembershipListSkeleton();
            },
            success: function (response) {
                if (response.status === "success") {
                    let data = response.data;
                    membershipData = data.membership;
                    userSub = data.subscriptions ? data.subscriptions[0] : null;

                    if (!data.user || !data.subscriptions || data.subscriptions.length === 0) {
                        renderNoMembership(membershipData, data.profile);
                    } else {
                        renderActiveMembership(data);
                    }
                } else {
                    $(".membership-container").html(`<p class="text-danger">${response.message}</p>`);
                }
            },
            error: function () {
                $(".membership-container").html(`<p class="text-danger">Something went wrong. Try again.</p>`);
            }
        });
    }

    // Toggle Year/Month inside modal
    $(document).on("click", "#intervalToggle", function () {
        $(this).toggleClass("active");
        interval = $(this).hasClass("active") ? "yearly" : "monthly";
        $("#intervalLabel").text(interval === "yearly" ? "Yearly" : "Monthly"); // Update text label if existent

        let activeProductId = userSub ? userSub.plan.product_id : null;
        let activePriceId = userSub ? userSub.plan.price_id : null;

        renderMembershipList(membershipData, "#membership-modal-body", interval, activeProductId, activePriceId);
    });

    // Select membership (from modal)
    $(document).on("click", ".selectable-plan", function () {
        let priceId = $(this).data("price");
        let planName = $(this).data("name");
        let planInterval = $(this).data("interval");
        let planIntervalCap = planInterval.charAt(0).toUpperCase() + planInterval.slice(1);

        Swal.fire({
            title: "Confirm Your Membership",
            html: `
                <div class="text-start">
                    <p class="mb-2">You are about to subscribe to:</p>
                    <div class="p-3 rounded" style="background:#f8f9fa; border:1px solid #ddd;">
                        <h5 class="fw-bold mb-1">${planName}</h5>
                        <p class="mb-1 text-muted">
                            <i class="bi bi-clock me-1"></i>
                            Billing Interval: <strong>${planIntervalCap}</strong>
                        </p>
                    </div>
                </div>
            `,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: 'Yes, Confirm',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-success fw-bold',
                cancelButton: 'btn btn-secondary ms-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                createMembershipSubscription(priceId, planName, planInterval);
            }
        });
    });

    // Create subscription AJAX call
    function createMembershipSubscription(priceId, planName, planInterval) {
        $.ajax({
            url: SITE_URL + "api/users/bidder/membership/subscribe",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({
                price_id: priceId,
                plan_name: planName,
                interval: planInterval
            }),
            beforeSend: function () {
                Swal.fire({
                    title: "Processing...",
                    text: "Activating membership...",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            },
            success: function (response) {
                Swal.close();
                if (response.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "Activated!",
                        text: response.message,
                        confirmButtonText: "OK",
                        customClass: { confirmButton: 'btn btn-success' },
                        buttonsStyling: false
                    }).then(() => {
                        fetchMembershipProduct();
                        $("#membershipModal").modal("hide");
                    });
                } else {
                    Swal.fire("Error", response.message, "error");
                }
            },
            error: function (jqXHR) {
                Swal.close();
                let errorMessage = "Something went wrong.";
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                }
                Swal.fire("Error", errorMessage, "error");
            }
        });
    }

    fetchMembershipProduct();

    // --- VAULT: Retention Modal Injection (Copied from original) ---
    if ($("#retentionModal").length === 0) {
        $("body").append(`
        <div class="modal fade" id="retentionModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-warning shadow-lg" style="border-radius: 15px; overflow: hidden;">
                    <div class="modal-header bg-warning text-dark border-0">
                        <h5 class="modal-title fw-bold"><i class="bi bi-gift-fill me-2"></i> Wait! Before you go...</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body text-center p-4">
                        <div class="mb-3">
                            <i class="bi bi-safe2-fill text-warning" style="font-size: 3rem;"></i>
                        </div>
                        <h4 class="fw-bold mb-2">The Next $20,000 Draw is Coming!</h4>
                        <p class="text-muted mb-4">You are currently entered to win big. If you cancel now, you might miss out on the ultimate prize.</p>
                        
                        <div class="alert alert-light border border-warning">
                            <h5 class="text-success fw-bold mb-1">🎁 SPECIAL OFFER</h5>
                            <p class="mb-0">Stay with us and get <strong class="text-dark">5 EXTRA ENTRIES</strong> for the next draw absolutely free!</p>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-center border-0 pb-4">
                        <button id="btn-claim-retention" class="btn btn-success btn-lg fw-bold px-4 rounded-pill shadow-sm">
                            <i class="bi bi-check-circle-fill me-2"></i> Claim 5 Free Entries
                        </button>
                        <button id="btn-proceed-cancel" class="btn btn-outline-secondary btn-sm mt-3 w-100 border-0">
                            No thanks, I still want to cancel
                        </button>
                    </div>
                </div>
            </div>
        </div>
        `);
    }

    // Handle "Claim Bonus"
    $(document).on("click", "#btn-claim-retention", function () {
        let btn = $(this);
        btn.prop('disabled', true).html('<span class="spinner-border spinner-border-sm me-2"></span> Claiming...');

        $.ajax({
            url: SITE_URL + "api/users/bidder/membership/claim_retention",
            type: "POST",
            dataType: "json",
            success: function (response) {
                if (response.status === "success") {
                    Swal.fire("Success!", response.message, "success");
                    $("#retentionModal").modal("hide");
                } else {
                    Swal.fire("Oops", response.message, "error");
                    btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-2"></i> Claim 5 Free Entries');
                }
            },
            error: function () {
                Swal.fire("Error", "Something went wrong. Please try again.", "error");
                btn.prop('disabled', false).html('<i class="bi bi-check-circle-fill me-2"></i> Claim 5 Free Entries');
            }
        });
    });

    // Handle "Proceed to Cancel"
    $(document).on("click", "#btn-proceed-cancel", function () {
        $("#retentionModal").modal("hide");
        $("#cancelMembershipModal").modal("show");
    });

    // Final Cancellation Logic
    $(document).off("click", "#confirm-cancel-membership").on("click", "#confirm-cancel-membership", function () {
        let subscriptionId = userSub?.subscription_id;
        if (!subscriptionId) return;

        $.ajax({
            url: SITE_URL + "api/users/bidder/membership/cancel",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({ subscription_id: subscriptionId }),
            beforeSend: function () {
                Swal.fire({
                    title: "Cancelling...",
                    text: "Processing your request.",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            },
            success: function (response) {
                Swal.close();
                if (response.status === "success") {
                    Swal.fire('Cancelled', response.message, 'success').then(() => {
                        fetchMembershipProduct();
                        $("#cancelMembershipModal").modal("hide");
                    });
                } else {
                    Swal.fire('Error', response.message, 'error');
                }
            },
            error: function (jqXHR) {
                Swal.close();
                let msg = jqXHR.responseJSON?.message || "Cancellation failed.";
                Swal.fire('Error', msg, 'error');
            }
        });
    });

});