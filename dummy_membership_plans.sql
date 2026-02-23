-- =====================================================
-- Dummy Membership Plans SQL
-- =====================================================
-- Note: This system fetches plans from Stripe API, not database.
-- You need to create products in Stripe Dashboard or use Stripe API.
-- 
-- However, if you want to test locally, here's a reference structure:
-- =====================================================

-- OPTION 1: Create Products in Stripe Dashboard
-- =====================================================
-- Go to: https://dashboard.stripe.com/test/products
-- 
-- Create 3 Products:
--
-- 1. Basic Plan
--    - Name: Basic
--    - Description: Essential features for small operations
--    - Metadata:
--      * commission_rate: 15
--      * product_order: 1
--      * limited_offer_text: -
--      * limited_offer_value: 0
--    - Prices:
--      * Monthly: $49/month
--      * Yearly: $490/year
--
-- 2. Premium AI (Most Popular)
--    - Name: Premium AI
--    - Description: Enhanced AI Item Specificity & Financial Reporting Access
--    - Metadata:
--      * commission_rate: 12
--      * product_order: 2
--      * limited_offer_text: Limited Time: 50% Off First Month
--      * limited_offer_value: 50
--    - Prices:
--      * Monthly: $99/month
--      * Yearly: $990/year
--
-- 3. Enterprise
--    - Name: Enterprise
--    - Description: Full platform access with dedicated support
--    - Metadata:
--      * commission_rate: 10
--      * product_order: 3
--      * limited_offer_text: -
--      * limited_offer_value: 0
--    - Prices:
--      * Monthly: $199/month
--      * Yearly: $1990/year

-- =====================================================
-- OPTION 2: Use Stripe CLI to create products
-- =====================================================
/*
# Install Stripe CLI: https://stripe.com/docs/stripe-cli

# Login
stripe login

# Create Basic Plan
stripe products create \
  --name="Basic" \
  --description="Essential features for small operations" \
  --metadata[commission_rate]=15 \
  --metadata[product_order]=1 \
  --metadata[limited_offer_text]="-" \
  --metadata[limited_offer_value]=0

# Get the product ID (e.g., prod_xxxxx) and create prices
stripe prices create \
  --product=prod_xxxxx \
  --unit-amount=4900 \
  --currency=usd \
  --recurring[interval]=month

stripe prices create \
  --product=prod_xxxxx \
  --unit-amount=49000 \
  --currency=usd \
  --recurring[interval]=year

# Repeat for Premium AI and Enterprise plans
*/

-- =====================================================
-- OPTION 3: Quick Test - Mock Data (For Development Only)
-- =====================================================
-- If you want to bypass Stripe temporarily for testing,
-- modify StripeMembershipPlans.php line 176-211 to return mock data:

/*
// Replace the Stripe API call with:
$membership = [
    [
        'membership' => [
            'product_id' => 'prod_test_basic',
            'name' => 'Basic',
            'description' => 'Essential features for small operations',
            'commission_rate' => '15',
            'product_order' => '1',
            'limited_offer_text' => '-',
            'limited_offer_value' => '0'
        ],
        'plans' => [
            [
                'price_id' => 'price_test_basic_monthly',
                'price' => 49,
                'currency' => 'USD',
                'interval' => 'monthly'
            ],
            [
                'price_id' => 'price_test_basic_yearly',
                'price' => 490,
                'currency' => 'USD',
                'interval' => 'yearly'
            ]
        ]
    ],
    [
        'membership' => [
            'product_id' => 'prod_T2IPPtrgtlOoqF',
            'name' => 'Premium AI',
            'description' => 'Enhanced AI Item Specificity & Financial Reporting Access',
            'commission_rate' => '12',
            'product_order' => '2',
            'limited_offer_text' => 'Limited Time: 50% Off First Month',
            'limited_offer_value' => '50'
        ],
        'plans' => [
            [
                'price_id' => 'price_test_premium_monthly',
                'price' => 99,
                'currency' => 'USD',
                'interval' => 'monthly'
            ],
            [
                'price_id' => 'price_1S6DpJ07pHCLCmTy0n3OX1Xn',
                'price' => 990,
                'currency' => 'USD',
                'interval' => 'yearly'
            ]
        ]
    ],
    [
        'membership' => [
            'product_id' => 'prod_test_enterprise',
            'name' => 'Enterprise',
            'description' => 'Full platform access with dedicated support',
            'commission_rate' => '10',
            'product_order' => '3',
            'limited_offer_text' => '-',
            'limited_offer_value' => '0'
        ],
        'plans' => [
            [
                'price_id' => 'price_test_enterprise_monthly',
                'price' => 199,
                'currency' => 'USD',
                'interval' => 'monthly'
            ],
            [
                'price_id' => 'price_test_enterprise_yearly',
                'price' => 1990,
                'currency' => 'USD',
                'interval' => 'yearly'
            ]
        ]
    ]
];
*/
