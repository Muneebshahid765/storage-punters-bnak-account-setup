const BIDDER_STRIPE_KEY = window.APP_CONFIG?.STRIPE_PUBLISHABLE_KEY || '';
if (!BIDDER_STRIPE_KEY) {
    console.error('Stripe publishable key missing from APP_CONFIG');
}
const stripe = Stripe(BIDDER_STRIPE_KEY);
var elements = stripe.elements();
var card; // ✅ declare card globally
var style = '';

style = {
    base: {
        color: '#32325d',
        fontFamily: '"Helvetica Neue", Helvetica, sans-serif',
        fontSmoothing: 'antialiased',
        fontSize: '16px',
        '::placeholder': {
            color: '#aab7c4'
        }
    },
    invalid: {
        color: '#fa755a',
        iconColor: '#fa755a'
    }
};

var cardNumberValidate = 0;
var cardExpireDateValidate = 0;
var cardCVCValidate = 0;

var cardNumber = 0;
var cardExpiry = 0;
var cardCvc = 0;

function mountStripeElements() {
    if (cardNumber) cardNumber.unmount();
    if (cardExpiry) cardExpiry.unmount();
    if (cardCvc) cardCvc.unmount();

    elements = stripe.elements();
    cardNumber = elements.create('cardNumber', { style });
    cardExpiry = elements.create('cardExpiry', { style });
    cardCvc = elements.create('cardCvc', { style });

    cardNumber.mount('#card_number');
    cardExpiry.mount('#card_expiry');
    cardCvc.mount('#card_security_code');

    cardNumber.on('change', handleCardValidationChange);
    cardExpiry.on('change', handleCardValidationChange);
    cardCvc.on('change', handleCardValidationChange);
}

function unmountStripeElements() {
    if (cardNumber) cardNumber.unmount();
    if (cardExpiry) cardExpiry.unmount();
    if (cardCvc) cardCvc.unmount();
}


async function search_customer_by_email() {
    try {
        const response = await fetch(SITE_URL + "api/stripe/customer/search_by_email", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest" // ✅ This line fixes the issue
            },
            body: JSON.stringify({})
        });

        const data = await response.json();

        if (!response.ok || !data) {
            throw new Error(data.message || "Failed to check customer.");
        }

        if (data.success && data.customer) {
            return { exists: true, customer: data.customer };
        } else {
            return { exists: false, customer: null };
        }

    } catch (error) {

        return { exists: false, customer: null };
    }
}


async function create_stripe_customer() {
    try {
        const response = await fetch(SITE_URL + "api/stripe/customer/add", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest" // ✅ This line fixes the issue
            },
            body: JSON.stringify({})
        });

        const data = await response.json();

        if (!response.ok || !data.success || !data.customer) {
            throw new Error(data.message || "Customer creation failed.");
        }

        return {
            success: true,
            customer: data.customer
        };
    } catch (error) {

        return {
            success: false,
            message: error.message || "Unknown error occurred"
        };
    }
}


async function create_setup_intent(customer_id) {
    try {
        const response = await fetch(SITE_URL + "api/stripe/setup-intent", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest" // ✅ This line fixes the issue
            },
            body: JSON.stringify({ customer_id })
        });

        const data = await response.json();

        if (!response.ok || !data.client_secret) {
            throw new Error(data.message || "Failed to create SetupIntent.");
        }

        return {
            success: true,
            client_secret: data.client_secret
        };
    } catch (error) {
        return {
            success: false,
            message: error.message || "SetupIntent error"
        };
    }
}

async function attach_payment_method(customer_id, payment_method_id) {
    try {
        const response = await fetch(SITE_URL + "api/stripe/payment-method/attach", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest" // ✅ This line fixes the issue
            },
            body: JSON.stringify({
                customer_id: customer_id,
                payment_method_id: payment_method_id
            })
        });

        const data = await response.json();

        if (!response.ok || data.status !== "success") {
            throw new Error(data.message || "Failed to attach payment method.");
        }

        return {
            success: true,
            message: data.message,
            data: data.data || null // in case you return extra info like internal IDs
        };
    } catch (error) {

        return {
            success: false,
            message: error.message || "Unknown error occurred"
        };
    }
}
