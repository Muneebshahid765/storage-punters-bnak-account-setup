const STORAGE_STRIPE_KEY = window.APP_CONFIG?.STRIPE_PUBLISHABLE_KEY || '';
if (!STORAGE_STRIPE_KEY) {
    console.error('Stripe publishable key missing from APP_CONFIG');
}
const stripe = Stripe(STORAGE_STRIPE_KEY);

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

async function attach_bank_account(customer_id, payment_method_id) {
    try {
        const response = await fetch(SITE_URL + "api/stripe/bank-account/attach", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                customer_id: customer_id,
                payment_method_id: payment_method_id
            })
        });

        const data = await response.json();

        if (!response.ok || data.status !== "success") {
            throw new Error(data.message || "Failed to attach bank account.");
        }

        return {
            success: true,
            message: data.message,
            data: data.data || null
        };

    } catch (error) {
        return {
            success: false,
            message: error.message || "Unknown error occurred while attaching bank account"
        };
    }
}


async function attach_connected_account(customer_id) {
    try {
        const response = await fetch(SITE_URL + "api/stripe/connected-account/attach", {
            method: "POST",
            headers: {
                "Content-Type": "application/json",
                "X-Requested-With": "XMLHttpRequest"
            },
            body: JSON.stringify({
                customer_id: customer_id
            })
        });

        const data = await response.json();

        if (!response.ok || data.status !== "success") {
            throw new Error(data.message || "Failed to attach connected account.");
        }

        return {
            success: true,
            message: data.message,
            data: data.data || null
        };

    } catch (error) {
        return {
            success: false,
            message: error.message || "Unknown error occurred while attaching bank account"
        };
    }
}

