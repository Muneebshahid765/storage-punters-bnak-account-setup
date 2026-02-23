(function initFacilitySubscriptionScript(window) {
    const MAX_ATTEMPTS = 50;
    let attempts = 0;

    function waitForjQuery() {
        if (window.jQuery) {
            initialize(window.jQuery);
        } else if (attempts < MAX_ATTEMPTS) {
            attempts++;
            setTimeout(waitForjQuery, 100);
        } else {
            console.error('subscription.js could not find jQuery. Ensure jQuery is loaded before this script.');
        }
    }

    function initialize($) {
        $(document).ready(function () {

    var interval = "yearly"; // default
    var membershipData = [];
    var userSub = null; // active subscription (if any)
    var currentCurrency = "AUD"; // Default to AUD
    var activePaymentMethod = null;

    // Utility: calculate savings
    function calculateSavings(yearPlan, monthPlan) {
        if (!yearPlan || !monthPlan) return null;

        let yearlyPerMonth = yearPlan.price / 12;
        let savings = ((monthPlan.price * 12) - yearPlan.price).toFixed(2);

        return { yearlyPerMonth, savings };
    }

    // Render list of memberships in modal
    function renderMembershipList(memberships, target, interval, activeProductId = null, activePriceId = null) {
        console.log("renderMembershipList called");

        if (!memberships || memberships.length === 0) {
            $(target).html(`
                <div class="alert alert-warning text-center">
                    <i class="bi bi-info-circle me-2"></i>
                    No membership plans available at this time.
                </div>
            `);
            return;
        }

        memberships.sort((a, b) => a.membership.product_order - b.membership.product_order);

        let html = `<div class="row">`;

        memberships.forEach(m => {
            let yearPlan = m.plans.find(p => p.interval === "yearly");
            let monthPlan = m.plans.find(p => p.interval === "monthly");
            let plan = (interval === "yearly") ? yearPlan : monthPlan;
            if (!plan) return;

            let isActivePlan = (activeProductId === m.membership.product_id && activePriceId === plan.price_id);
            let isActiveProduct = (activeProductId === m.membership.product_id);

            // Calculate yearly savings
            let savingsHtml = "";
            if (interval === "yearly" && yearPlan && monthPlan) {
                let savings = calculateSavings(yearPlan, monthPlan);
                if (savings) {
                    savingsHtml = `
            <div class="alert p-2 mt-3 mb-0 small
                ${isActivePlan && plan.interval === "yearly" ? "text-dark border" : "text-white"}"
                style="${isActivePlan && plan.interval === "yearly"
                            ? "background: white;"
                            : "background: var(--text-yello-light);"}">
                <i class="bi bi-piggy-bank-fill me-1"></i>
                ${savings.yearlyPerMonth.toFixed(2)} ${plan.currency}/month
                <br><strong>You save ${savings.savings} ${plan.currency} per year!</strong>
            </div>`;
                }
            }

            // Decide badge
            let badgeHtml = "";
            if (!activeProductId) {
                badgeHtml = `<span class="badge text-white" style="background: var(--text-yello-light);">
                           <i class="bi bi-cart-check me-1" ></i> Buy Plan
                         </span>`;
            } else if (isActivePlan) {
                badgeHtml = `<span class="badge text-dark" style="background: white;">
                           <i class="bi bi-person-badge me-1"></i> Current Plan
                         </span>`;
            } else if (isActiveProduct) {
                badgeHtml = `<span class="badge text-white" style="background: var(--text-yello-light);">
                           <i class="bi bi-arrow-left-right me-1"></i> Switch Plan
                         </span>`;
            } else {
                badgeHtml = `<span class="badge text-white" style="background: var(--text-yello-light);">
                           <i class="bi bi-arrow-repeat me-1"></i> Change Plan
                         </span>`;
            }

            html += `
                <div class="col-md-6 mb-3">
                    <div class="membership-card p-3 rounded shadow-sm
                        ${isActivePlan ? "active-sub" : "selectable"}"
                        data-price="${plan.price_id}"
                        data-name="${m.membership.name}"
                        data-product="${m.membership.product_id}"
                        data-interval="${plan.interval}">

                        <h5>
                            <i class="bi ${isActivePlan ? "bi-check-circle-fill text-white" : "bi-star"} me-2"></i>
                            <i class="fw-bold ">${m.membership.name}</i>
                        </h5>

                        <ul class="mb-2">
                             ${m.membership.description.split('.').map(s => s.trim() ? `<li>${s}</li>` : '').join('')}
                        </ul>

                        <p class="fw-bold mb-2">${plan.price} ${plan.currency} / ${plan.interval}</p>

                        ${badgeHtml}
                        ${savingsHtml}
                    </div>
                </div>
            `;
        });

        html += `</div>`;
        $(target).html(html);
    }

    // No membership UI
    function renderNoMembership(memberships, profile) {
        $(".membership-container").html(`
            <div class="no-subscription-card">
                <i class="bi bi-exclamation-circle" style="font-size: 48px; color: #6c757d;"></i>
                <h3>No Active Subscription</h3>
                <p>You don't have an active subscription plan. Subscribe today to unlock exclusive benefits and reduce your fees.</p>
                <button id="btn-buy-membership" type="button" class="btn-change-plan">
                    <i class="bi bi-cart-plus me-2"></i> Browse Plans
                </button>
            </div>
        `);
    }

    // Active membership UI
    function renderActiveMembership(user, profile, subscriptions, memberships) {
        userSub = subscriptions[0];
        let activeProductId = userSub.plan.product_id;
        let activePriceId = userSub.plan.price_id;

        let currentPlan = memberships.find(m => m.membership.product_id === activeProductId);
        let currentName = currentPlan?.membership?.name || "Active Plan";
        let currentInterval = userSub.plan.interval || "monthly";
        let currentAmount = userSub.plan.amount || "0";
        let currentCurrency = userSub.plan.currency || "USD";
        let planDescription = currentPlan?.membership?.description || "Enhanced AI Item Specificity & Financial Reporting Access active.";
        let nextBillingDateRaw = userSub.current_period_end || "N/A";
        let nextBillingDate = "N/A";
        
        // Format the date properly
        if (nextBillingDateRaw !== "N/A") {
            try {
                let dateObj = new Date(nextBillingDateRaw);
                if (!isNaN(dateObj.getTime())) {
                    nextBillingDate = dateObj.toLocaleDateString('en-US', { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric' 
                    });
                }
            } catch (e) {
                console.error('Date parsing error:', e);
            }
        }

        let savingsText = "";
        if (currentInterval === "yearly" && currentPlan) {
            let yearPlan = currentPlan.plans.find(p => p.interval === "yearly");
            let monthPlan = currentPlan.plans.find(p => p.interval === "monthly");
            if (yearPlan && monthPlan) {
                let savings = calculateSavings(yearPlan, monthPlan);
                if (savings) {
                    savingsText = ` (Save ${Math.round((savings.savings / (monthPlan.price * 12)) * 100)}%)`;
                }
            }
        }

        let isActive = userSub.status === "paid" || userSub.membership_status === "active";
        let statusNoticeHtml = "";
        if (!isActive) {
            const noticeText = `<i class=\"bi bi-hourglass-split me-1\"></i><strong>Cancellation Requested</strong> - Membership will end on <strong>${nextBillingDate}</strong>.`;
            statusNoticeHtml = `
                <div class=\"alert alert-warning mt-2 mb-0\">
                    ${noticeText}
                </div>`;
        }

        let monthlyPlan = currentPlan?.plans?.find(p => p.interval === "monthly");
        let yearlyPlan = currentPlan?.plans?.find(p => p.interval === "yearly");
        let monthlyPrice = monthlyPlan ? monthlyPlan.price : "99";
        let yearlyPrice = yearlyPlan ? yearlyPlan.price : "990";

        $(".membership-container").html(`
            <!-- Current Plan Card -->
            <div class="current-plan-card">
                <div class="current-plan-title">Current Plan: ${currentName}</div>
                <div class="current-plan-desc">${planDescription}</div>
                <div class="current-plan-desc mt-1"><strong>Next Billing Date:</strong> ${nextBillingDate}</div>
            </div>

            <!-- Billing Cycle Section -->
            <div class="billing-cycle-section">
                <div class="billing-cycle-title">Billing Cycle</div>
                <div class="billing-cycle-options">
                    <div class="billing-cycle-option">
                        <input type="radio" id="billing-monthly" name="billing-cycle" value="monthly" ${currentInterval === "monthly" ? "checked" : ""} disabled>
                        <label for="billing-monthly">28-Day (Monthly) - $${monthlyPrice}/mo</label>
                    </div>
                    <div class="billing-cycle-option">
                        <input type="radio" id="billing-yearly" name="billing-cycle" value="yearly" ${currentInterval === "yearly" ? "checked" : ""} disabled>
                        <label for="billing-yearly">Annually - $${yearlyPrice}/yr${savingsText}</label>
                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="action-buttons-row">
                <button id="btn-change-membership" type="button" class="btn-change-plan">
                    Change Plan / Upgrade
                </button>
                <button id="btn-manage-payment-methods" type="button" class="btn-change-plan">
                    <i class="bi bi-credit-card me-2"></i>Manage Payment Methods
                </button>
                ${isActive ? `
                <button id="btn-cancel-membership" type="button" class="btn-cancel-sub">
                    Cancel Subscription
                </button>
                ` : statusNoticeHtml}
            </div>
        `);
    }

    // Create subscription AJAX call
    function createMembershipSubscription(priceId, planName, planInterval) {
        $.ajax({
            url: SITE_URL + "api/facility/membership/subscribe",
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
                    text: "Please wait while we activate your membership.",
                    allowOutsideClick: false,
                    didOpen: () => {
                        Swal.showLoading();
                    }
                });
            },
            success: function (response) {
                Swal.close();
                if (response.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "Membership Activated!",
                        text: response.message || "Your subscription is now active.",
                        confirmButtonText: "OK"
                    }).then(() => {
                        fetchMembershipProduct();
                        $("#membershipModal").modal("hide");
                    });
                } else {
                    showNotification(response.message, "error");
                }
            },
            error: function (jqXHR) {
                Swal.close();
                
                // Log full error details for debugging
                console.error('Subscription Error Details:', {
                    status: jqXHR.status,
                    statusText: jqXHR.statusText,
                    responseJSON: jqXHR.responseJSON,
                    responseText: jqXHR.responseText
                });
                
                let errorMessage = jqXHR.responseJSON?.message || "Something went wrong. Try again.";
                let debugInfo = jqXHR.responseJSON?.debug || null;
                
                // Parse HTML error response if JSON is undefined
                if (!jqXHR.responseJSON && jqXHR.responseText) {
                    // Extract error message from HTML
                    let htmlResponse = jqXHR.responseText;
                    let errorMatch = htmlResponse.match(/<p>([^<]*Error[^<]*)<\/p>/i);
                    let lineMatch = htmlResponse.match(/Line Number:\s*(\d+)/i);
                    let fileMatch = htmlResponse.match(/Filename:\s*([^<\n]+)/i);
                    
                    if (errorMatch || lineMatch || fileMatch) {
                        errorMessage = 'PHP Error: ';
                        if (errorMatch) errorMessage += errorMatch[1] + '\n';
                        if (fileMatch) errorMessage += 'File: ' + fileMatch[1] + '\n';
                        if (lineMatch) errorMessage += 'Line: ' + lineMatch[1];
                        
                        console.error('Parsed PHP Error:', {
                            error: errorMatch ? errorMatch[1] : null,
                            file: fileMatch ? fileMatch[1] : null,
                            line: lineMatch ? lineMatch[1] : null
                        });
                    }
                }
                
                if (debugInfo) {
                    console.error('Debug Info:', debugInfo);
                    errorMessage += '\n\nDebug: ' + debugInfo.message + ' at line ' + debugInfo.line;
                }
                
                if (errorMessage.includes("payment method")) {
                    // Store subscription details for retry
                    window.pendingSubscription = {
                        priceId: priceId,
                        planName: planName,
                        planInterval: planInterval
                    };
                    
                    // Show payment method modal
                    $("#membershipModal").modal("hide");
                    setTimeout(function() {
                        $("#paymentMethodModal").modal("show");
                    }, 500);
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Subscription Failed',
                        html: '<pre style="text-align: left; font-size: 12px;">' + errorMessage + '</pre>',
                        confirmButtonText: 'OK'
                    });
                }
            }
        });
    }

    // Fetch plans from backend
    function fetchMembershipProduct() {
        $.ajax({
            url: SITE_URL + "api/facility/membership/plan/get",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({ interval: interval }),
            beforeSend: function () {
                $(".membership-container").html(`
                    <div class="text-center py-5">
                        <div class="spinner-border text-primary" role="status">
                            <span class="visually-hidden">Loading...</span>
                        </div>
                        <p class="mt-3 text-muted">Loading subscription details...</p>
                    </div>
                `);
            },
            success: function (response) {
                if (response.status === "success") {
                    let data = response.data;
                    membershipData = data.membership;
                    userSub = data.subscriptions ? data.subscriptions[0] : null;

                    if (!userSub) {
                        renderNoMembership(membershipData, data.profile);
                    } else {
                        renderActiveMembership(data.user, data.profile, data.subscriptions, membershipData);
                    }
                } else {
                    $(".membership-container").html(`<p class="text-danger">${response.message}</p>`);
                }
            },
            error: function (jqXHR) {
                let errorMessage = "Something went wrong. Try again.";
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                } else if (jqXHR.responseText) {
                    try {
                        const parsed = JSON.parse(jqXHR.responseText);
                        errorMessage = parsed.message || errorMessage;
                    } catch (e) {
                        console.error("Failed to parse error response:", jqXHR.responseText);
                    }
                }
                $(".membership-container").html(`<p class="text-danger">${errorMessage}</p>`);
            }
        });
    }

    // --- Event Handlers ---

    function shouldPromptNewCard(message = "") {
        if (!message) return false;
        const normalized = message.toLowerCase();
        const keywords = [
            "card was declined",
            "card_declined",
            "insufficient",
            "invalid card",
            "no such payment method",
            "authentication required",
            "payment method"
        ];
        return keywords.some((word) => normalized.includes(word));
    }

    $(document).on("click", "#btn-buy-membership, #btn-change-membership", function () {
        let activeProductId = userSub ? userSub.plan.product_id : null;
        let activePriceId = userSub ? userSub.plan.price_id : null;
        renderMembershipList(membershipData, "#membership-modal-body", interval, activeProductId, activePriceId);
        $("#membershipModal").modal("show");
    });

    function fetchActivePaymentMethod(forceRefresh = false) {
        if (!forceRefresh && activePaymentMethod) {
            return Promise.resolve(activePaymentMethod);
        }

        return new Promise((resolve) => {
            $.ajax({
                url: SITE_URL + "api/facility/payment-method/get",
                type: "POST",
                contentType: "application/json",
                dataType: "json",
                data: JSON.stringify({}),
                success: function (response) {
                    if (response.status === "success" && Array.isArray(response.data) && response.data.length > 0) {
                        const defaultCard = response.data.find(card => card.is_default) || response.data[0];
                        activePaymentMethod = defaultCard;
                        resolve(defaultCard);
                    } else {
                        activePaymentMethod = null;
                        resolve(null);
                    }
                },
                error: function () {
                    activePaymentMethod = null;
                    resolve(null);
                }
            });
        });
    }

    function getCurrentSubscriptionId() {
        if (!userSub) return null;
        return userSub.subscription_id || userSub.stripe_subscription_id || userSub.id || null;
    }

    function performMembershipAction(endpoint, successTitle, successText) {
        const subscriptionId = getCurrentSubscriptionId();
        if (!subscriptionId) {
            showNotification("Subscription reference missing. Please refresh and try again.", "error");
            return;
        }

        $.ajax({
            url: SITE_URL + endpoint,
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({ subscription_id: subscriptionId }),
            beforeSend: function () {
                Swal.fire({
                    title: "Processing...",
                    text: "Please wait...",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            },
            success: function (response) {
                Swal.close();
                if (response.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: successTitle,
                        text: successText || response.message,
                        confirmButtonText: "OK"
                    }).then(() => {
                        fetchMembershipProduct();
                    });
                } else if (shouldPromptNewCard(response.message || "")) {
                    promptPaymentMethodSetup(priceId, planName, planInterval);
                } else {
                    Swal.fire("Error", response.message || "Request failed.", "error");
                }
            },
            error: function (jqXHR) {
                Swal.close();
                const message = jqXHR.responseJSON?.message || "Request failed. Please try again.";
                if (shouldPromptNewCard(message)) {
                    promptPaymentMethodSetup(priceId, planName, planInterval);
                    return;
                }
                Swal.fire("Error", message, "error");
            }
        });
    }

    $(document).on("click", "#btn-manage-payment-methods", function () {
        fetchAndShowPaymentMethods();
    });

    function fetchAndShowPaymentMethods() {
        $.ajax({
            url: SITE_URL + "api/facility/payment-method/get",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({}),
            beforeSend: function () {
                Swal.fire({
                    title: "Loading...",
                    text: "Fetching your payment methods...",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            },
            success: function (response) {
                Swal.close();
                if (response.status === "success" && Array.isArray(response.data) && response.data.length > 0) {
                    showPaymentMethodsModal(response.data);
                } else if (response.code === 'no_active_cards') {
                    Swal.fire({
                        icon: "warning",
                        title: "No Active Cards",
                        text: response.message || "You don't have any active payment methods. Please add a new card.",
                        confirmButtonText: "Add Card"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $("#paymentMethodModal").modal("show");
                        }
                    });
                } else {
                    Swal.fire({
                        icon: "info",
                        title: "No Payment Methods",
                        text: "You don't have any saved payment methods. Would you like to add one?",
                        showCancelButton: true,
                        confirmButtonText: "Add Card",
                        cancelButtonText: "Cancel"
                    }).then((result) => {
                        if (result.isConfirmed) {
                            $("#paymentMethodModal").modal("show");
                        }
                    });
                }
            },
            error: function (jqXHR) {
                Swal.close();
                const message = jqXHR.responseJSON?.message || "Failed to fetch payment methods. Please try again.";
                Swal.fire("Error", message, "error");
            }
        });
    }

    function showPaymentMethodsModal(cards) {
        let cardsHtml = cards.map(card => {
            let defaultBadge = card.is_default ? '<span class="badge bg-success ms-2">Default</span>' : '';
            return `
                <div class="card mb-3">
                    <div class="card-body d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="mb-1">
                                <i class="bi bi-credit-card me-2"></i>
                                ${(card.brand || 'Card').toUpperCase()} •••• ${card.last4}
                                ${defaultBadge}
                            </h6>
                            <small class="text-muted">Expires ${String(card.exp_month).padStart(2, '0')}/${card.exp_year}</small>
                        </div>
                        <button class="btn btn-sm btn-danger btn-detach-card" data-card-id="${card.id}" data-card-last4="${card.last4}">
                            <i class="bi bi-trash me-1"></i> Remove
                        </button>
                    </div>
                </div>
            `;
        }).join('');

        Swal.fire({
            title: "Your Payment Methods",
            html: `
                <div class="text-start">
                    ${cardsHtml}
                    <button id="btn-add-new-card-modal" class="btn btn-primary w-100 mt-2">
                        <i class="bi bi-plus-circle me-2"></i> Add New Card
                    </button>
                </div>
            `,
            showConfirmButton: false,
            showCloseButton: true,
            width: '600px',
            didOpen: () => {
                // Attach detach event handlers
                $('.btn-detach-card').on('click', function() {
                    const cardId = $(this).data('card-id');
                    const last4 = $(this).data('card-last4');
                    confirmDetachCard(cardId, last4);
                });

                $('#btn-add-new-card-modal').on('click', function() {
                    Swal.close();
                    $("#paymentMethodModal").modal("show");
                });
            }
        });
    }

    function confirmDetachCard(cardId, last4) {
        Swal.fire({
            title: "Remove Payment Method?",
            html: `Are you sure you want to remove card ending in <strong>${last4}</strong>?<br><br><small class="text-muted">This action cannot be undone.</small>`,
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Remove",
            cancelButtonText: "Cancel",
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary ms-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                detachPaymentMethod(cardId);
            }
        });
    }

    function detachPaymentMethod(cardId) {
        $.ajax({
            url: SITE_URL + "api/facility/payment-method/detach",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({ payment_method_id: cardId }),
            beforeSend: function () {
                Swal.fire({
                    title: "Removing...",
                    text: "Please wait...",
                    allowOutsideClick: false,
                    didOpen: () => Swal.showLoading()
                });
            },
            success: function (response) {
                Swal.close();
                if (response.status === "success") {
                    Swal.fire({
                        icon: "success",
                        title: "Removed",
                        text: response.message || "Payment method removed successfully",
                        confirmButtonText: "OK"
                    }).then(() => {
                        fetchAndShowPaymentMethods();
                    });
                } else {
                    Swal.fire("Error", response.message || "Failed to remove payment method", "error");
                }
            },
            error: function (jqXHR) {
                Swal.close();
                const message = jqXHR.responseJSON?.message || "Failed to remove payment method. Please try again.";
                Swal.fire("Error", message, "error");
            }
        });
    }

    $(document).on("click", "#btn-cancel-membership", function () {
        Swal.fire({
            title: "Cancel Subscription?",
            text: "You will retain access until the current billing period ends.",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Yes, Cancel",
            cancelButtonText: "Keep Subscription",
            customClass: {
                confirmButton: 'btn btn-danger',
                cancelButton: 'btn btn-secondary ms-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                performMembershipAction("api/facility/membership/cancel", "Subscription Cancelled", "Your membership will remain active until the end of the billing cycle.");
            }
        });
    });

    $(document).on("click", "#intervalToggle", function () {
        $(this).toggleClass("active");
        interval = $(this).hasClass("active") ? "yearly" : "monthly";
        let activeProductId = userSub ? userSub.plan.product_id : null;
        let activePriceId = userSub ? userSub.plan.price_id : null;
        renderMembershipList(membershipData, "#membership-modal-body", interval, activeProductId, activePriceId);
    });

    $(document).on("click", ".membership-card.selectable", async function () {
        let priceId = $(this).data("price");
        let planName = $(this).data("name");
        let planInterval = $(this).data("interval");

        const cardDetails = await fetchActivePaymentMethod();

        if (!cardDetails) {
            promptPaymentMethodSetup(priceId, planName, planInterval, $(this));
            return;
        }

        const cardHtml = `
            <div class="border rounded-3 p-3 text-start bg-light mt-3">
                <div class="text-muted small">Billing with</div>
                <div class="fw-bold text-dark">${(cardDetails.brand || 'Card').toUpperCase()} •••• ${cardDetails.last4}</div>
                <div class="text-muted small">Expires ${String(cardDetails.exp_month).padStart(2, '0')}/${cardDetails.exp_year}</div>
            </div>`;

        Swal.fire({
            title: "Confirm Your Membership",
            html: `
                <div class="text-start">
                    <p class="mb-1">Subscribe to <strong>${planName}</strong> (${planInterval})?</p>
                    ${cardHtml}
                </div>
            `,
            icon: "question",
            showCancelButton: true,
            confirmButtonText: 'Yes, Confirm',
            cancelButtonText: 'Cancel',
            customClass: {
                confirmButton: 'btn btn-yellow text-white fw-bold',
                cancelButton: 'btn btn-secondary ms-2'
            },
            buttonsStyling: false
        }).then((result) => {
            if (result.isConfirmed) {
                ensurePaymentMethodBeforeSubscription(priceId, planName, planInterval, $(this), function() {
                    createMembershipSubscription(priceId, planName, planInterval);
                });
            }
        });
    });

    function ensurePaymentMethodBeforeSubscription(priceId, planName, planInterval, buttonRef, onSuccess) {
        $.ajax({
            url: SITE_URL + "api/facility/payment-method/get",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({}),
            success: function (response) {
                if (response.status === "success" && Array.isArray(response.data) && response.data.length > 0) {
                    onSuccess();
                } else {
                    promptPaymentMethodSetup(priceId, planName, planInterval, buttonRef);
                }
            },
            error: function () {
                promptPaymentMethodSetup(priceId, planName, planInterval, buttonRef);
            }
        });
    }

    function promptPaymentMethodSetup(priceId, planName, planInterval, button = null) {
        activePaymentMethod = null;
        window.pendingSubscription = {
            priceId: priceId,
            planName: planName,
            planInterval: planInterval,
            button: button || null
        };

        $("#membershipModal").modal("hide");
        setTimeout(function() {
            $("#paymentMethodModal").modal("show");
        }, 300);

        if (typeof showNotification === 'function') {
            showNotification("No active payment methods found. Please add one to continue.", "error");
        }
    }

    // Store current subscription attempt for retry
    window.currentSubscriptionAttempt = null;
    
    // Retry subscription purchase after adding payment method
    window.retrySubscriptionPurchase = function() {
        if (window.currentSubscriptionAttempt) {
            purchaseSubscription(
                window.currentSubscriptionAttempt.priceId,
                window.currentSubscriptionAttempt.planName,
                window.currentSubscriptionAttempt.button
            );
        }
    };

    // Handle "Buy Plan" button click inside modal
    $(document).on("click", ".btn-buy-plan", function (event) {
        event.preventDefault();
        event.stopPropagation();
        let priceId = $(this).data("price-id");
        let planName = $(this).data("plan-name");
        purchaseSubscription(priceId, planName, $(this));
    });

    function purchaseSubscription(priceId, planName, button) {
        if (!priceId) {
            alert("Invalid plan selected.");
            return;
        }

        // Store for retry
        window.currentSubscriptionAttempt = { priceId, planName, button };

        // Disable button and show loading
        button.prop("disabled", true).text("Processing...");

        $.ajax({
            url: SITE_URL + "api/facility/membership/plan/subscribe",
            type: "POST",
            contentType: "application/json",
            dataType: "json",
            data: JSON.stringify({ price_id: priceId, interval: interval }),
            success: function (response) {
                if (response.status === "success") {
                    $("#membershipModal").modal("hide");
                    alert("Subscription successful! Redirecting...");
                    location.reload();
                } else {
                    // Check if error is about missing payment method
                    if (response.message && response.message.includes("No active payment methods")) {
                        $("#membershipModal").modal("hide");
                        setTimeout(function() {
                            $("#paymentMethodModal").modal("show");
                        }, 500);
                    } else {
                        alert(response.message || "Failed to subscribe.");
                    }
                }
            },
            error: function (jqXHR) {
                let errorMessage = "An error occurred. Please try again.";
                if (jqXHR.responseJSON && jqXHR.responseJSON.message) {
                    errorMessage = jqXHR.responseJSON.message;
                    
                    // Check if error is about missing payment method
                    if (errorMessage.includes("No active payment methods")) {
                        $("#membershipModal").modal("hide");
                        setTimeout(function() {
                            $("#paymentMethodModal").modal("show");
                        }, 500);
                        return;
                    }
                }
                alert(errorMessage);
            },
            complete: function () {
                button.prop("disabled", false).text("Buy Plan");
            }
        });
    }

    // Initial load
    fetchMembershipProduct();
        });
    }

    waitForjQuery();
})(window);
