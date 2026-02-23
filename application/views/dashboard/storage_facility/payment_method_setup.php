<!-- Payment Method Setup Modal -->
<div class="modal fade" id="paymentMethodModal" tabindex="-1" aria-labelledby="paymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="paymentMethodModalLabel">Add Payment Method</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted mb-3">Add a credit or debit card to purchase subscriptions.</p>
                <form id="payment-method-form">
                    <div id="card-element" class="form-control" style="height: 40px; padding: 10px;"></div>
                    <div id="card-errors" class="text-danger mt-2" role="alert"></div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-primary" id="save-payment-method">Save Payment Method</button>
            </div>
        </div>
    </div>
</div>

<script>
(function initFacilityPaymentModal() {
    const MAX_ATTEMPTS = 50;
    let attempts = 0;

    function waitForDependencies() {
        if (window.jQuery && typeof Stripe !== 'undefined') {
            setupModal(window.jQuery);
        } else if (attempts < MAX_ATTEMPTS) {
            attempts++;
            setTimeout(waitForDependencies, 100);
        } else {
            console.error('Payment method modal failed to initialize because dependencies were not loaded.');
        }
    }

    function setupModal($) {
        const stripePublishableKey = '<?php echo stripe_publishable_key(); ?>';

        if (!stripePublishableKey) {
            console.error('Stripe publishable key is missing');
            return;
        }

        const stripe = Stripe(stripePublishableKey);
        const elements = stripe.elements();
        const cardElement = elements.create('card');
        let cardMounted = false;

        $('#paymentMethodModal').on('shown.bs.modal', function () {
            if (!cardMounted) {
                try {
                    cardElement.mount('#card-element');
                    cardMounted = true;
                } catch (error) {
                    console.error('Error mounting card element:', error);
                    $('#card-errors').text('Failed to load payment form. Please refresh the page.');
                }
            }
        });

        cardElement.on('change', function(event) {
            const displayError = document.getElementById('card-errors');
            displayError.textContent = event.error ? event.error.message : '';
        });

        $('#save-payment-method').on('click', async function() {
            const btn = $(this);
            btn.prop('disabled', true).text('Processing...');

            try {
                const {paymentMethod, error} = await stripe.createPaymentMethod({
                    type: 'card',
                    card: cardElement,
                });

                if (error) {
                    $('#card-errors').text(error.message);
                    return;
                }

                const response = await $.ajax({
                    url: SITE_URL + 'api/facility/payment-method/add',
                    type: 'POST',
                    contentType: 'application/json',
                    data: JSON.stringify({ payment_method_id: paymentMethod.id })
                });

                if (response.status === 'success') {
                    $('#paymentMethodModal').modal('hide');
                    cardElement.clear();

                    if (typeof showNotification === 'function') {
                        showNotification('Payment method added successfully!', 'success');
                    } else {
                        alert('Payment method added successfully!');
                    }

                    // Retry pending subscription if exists
                    if (window.pendingSubscription) {
                        setTimeout(function() {
                            const pending = window.pendingSubscription;
                            window.pendingSubscription = null;
                            
                            // Call subscription function if available
                            if (typeof createMembershipSubscription === 'function') {
                                createMembershipSubscription(pending.priceId, pending.planName, pending.planInterval);
                            } else {
                                location.reload();
                            }
                        }, 1000);
                    } else {
                        location.reload();
                    }
                } else {
                    $('#card-errors').text(response.message || 'Failed to add payment method');
                }
            } catch (error) {
                console.error('Payment method error:', error);
                $('#card-errors').text(error.responseJSON?.message || 'An error occurred');
            } finally {
                btn.prop('disabled', false).text('Save Payment Method');
            }
        });

        $('#paymentMethodModal').on('hidden.bs.modal', function () {
            cardElement.clear();
            $('#card-errors').text('');
        });
    }

    waitForDependencies();
})();
</script>
