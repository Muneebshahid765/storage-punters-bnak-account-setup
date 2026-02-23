# Bank Account to Location Linking - Complete Implementation Guide

## Overview
Yeh implementation bank accounts ko specific storage locations se link karne ki facility provide karta hai, taky har location ke auctions ke payouts sahi bank account mein jayen.

## Problem Statement (Jo Fix Kiya Gaya)

### Issues That Were Fixed:
1. **Bank accounts location se link nahi ho rahe thay** - "Not linked to any locations" show ho raha tha
2. **Stripe account flagged ho gaya tha** - Connected accounts create karne ki galat koshish ki waja se
3. **Payout routing location-specific nahi tha** - Sab payouts ek hi account mein ja rahe thay
4. **Frontend aur backend APIs mismatch thay** - Different endpoints use ho rahe thay

## Solution Implemented

### 1. Database Structure (Already Existed)

**Table: `tbl_storage_facility_bank_relationship`**
```sql
CREATE TABLE IF NOT EXISTS `tbl_storage_facility_bank_relationship` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `bank_id` int(11) NOT NULL,
  `storage_facility_id` int(11) NOT NULL,
  `created_at` datetime NOT NULL DEFAULT current_timestamp(),
  `updated_at` datetime NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
```

**Table: `tbl_bank`**
- Stores bank account details
- Includes Stripe account ID (`stripe_bank_account_id`)
- Has metadata fields: `account_name`, `bsb`, `account_number`, `bank_institution`

### 2. Frontend Changes

#### File: `assets/js/ajax/dashboard/storage_facility/bank/bank_account.js`

**Key Features Added:**
1. **Location Selection Modal** - Bank account create/edit karte waqt locations select kar sakte hain
2. **Edit Locations Button** - Existing bank accounts ke locations update kar sakte hain
3. **Location Checkboxes** - Multi-select functionality with visual feedback
4. **Automatic Metadata Updates** - Stripe metadata automatically update hota hai

**New Functions:**
- `loadModalLocations(bankId)` - Loads all locations and marks linked ones as checked
- `updateBankLocations(bankId)` - Updates location links via API
- `resetBankForm()` - Properly resets form state

**API Calls:**
```javascript
// Get all user locations
POST /api/user/fetch_storage_facilities_by_user

// Get linked locations for a bank
POST /api/users/storage-facility/bank-account/facilities
Body: { bankId: 123 }

// Update location links
POST /api/users/storage-facility/bank-account/update-facilities
Body: {
  bank_id: 123,
  added: [{ facility_id: 1 }, { facility_id: 2 }],
  deleted: [{ facility_id: 3 }]
}

// Add new bank account with locations
POST /api/bank/add-account
Body: {
  account_name: "John Doe",
  bsb: "062000",
  account_number: "12345678",
  bank_institution: "Commonwealth Bank",
  location_ids: [1, 2, 3]
}
```

#### File: `application/views/dashboard/storage_facility/bank/add_bank.php`

**UI Changes:**
- Added location selection section in modal
- Checkboxes for each storage location
- Visual indicators for linked/unlinked status
- Responsive design for mobile

### 3. Backend Changes

#### File: `application/controllers/Bank.php`

**Method: `addAccountForFacility()`**
```php
// Now accepts location_ids in request
$locationIds = $json_input['location_ids'] ?? [];

// Links locations to bank account
if (!empty($locationIds) && is_array($locationIds)) {
    foreach ($locationIds as $facilityId) {
        $this->BankModel->linkBankToFacility($bankAccountId, (int)$facilityId);
    }
    
    // Updates Stripe metadata
    $metadata = [
        'facility_status'   => 'assigned',
        'facility_ids'      => implode(',', $facilityIds),
        'facility_names'    => implode(', ', $facilityNames),
        'facility_count'    => count($facilityIds),
        'facility_updated'  => date('Y-m-d H:i:s'),
    ];
    \Stripe\Account::update($account->id, ['metadata' => $metadata]);
}
```

**Method: `updateBankAccountFacilitiesById()`**
- Already properly implemented
- Updates Stripe metadata when locations are linked/unlinked
- Handles both "assigned" and "unassigned" states

#### File: `application/models/BankModel.php`

**Existing Methods Used:**
- `linkBankToFacility($bankId, $facilityId)` - Links bank to location
- `unlinkBankFromFacility($bankId, $facilityId)` - Unlinks bank from location
- `getLinkedLocationNames($bankId)` - Gets comma-separated location names

### 4. Payout Routing (Already Working)

#### File: `application/controllers/Webhook.php`

**Method: `createPaymentReleaseLog()`**
```php
// Gets bank account by storage facility ID
$bank = $this->StorageFacilityModel->getBankIdByStorageFacilityById($storageFacilityId);

// Uses this bank_id for payout
$paymentReleaseData = [
    "invoice_id" => $invoice_id,
    "invoice_amount" => $total_amount,
    "bank_id" => $bankId,  // ✅ Location-specific bank account
    "user_id" => $storageFacilityUserId,
    // ...
];
```

## How It Works - Complete Flow

### 1. Adding New Bank Account with Locations

```
User Action:
1. Click "Add New Bank Account"
2. Enter bank details (name, BSB, account number)
3. Select storage locations from checkboxes
4. Click "Save Bank Account"

Backend Process:
1. Creates Stripe Express Account
2. Saves bank details to tbl_bank
3. Creates user-bank relationship
4. Links selected locations to bank account
5. Updates Stripe metadata with facility info
6. Returns success response

Result:
✅ Bank account created
✅ Locations linked
✅ Stripe metadata updated
✅ Ready for payouts
```

### 2. Editing Location Links

```
User Action:
1. Click "Edit Locations" button on bank account row
2. Check/uncheck locations
3. Click "Save"

Backend Process:
1. Calculates added and deleted locations
2. Updates tbl_storage_facility_bank_relationship
3. Updates Stripe metadata
4. Returns updated location list

Result:
✅ Location links updated
✅ Stripe metadata synced
✅ UI refreshed with new links
```

### 3. Auction Payout Flow

```
Auction Completed:
1. Winner pays invoice
2. Webhook receives payment confirmation
3. Gets storage_facility_id from auction
4. Queries bank_id using facility_id
5. Schedules payout to correct bank account

Payout Process:
1. Background job processes payout
2. Transfers to Stripe account linked to location
3. Funds route to correct bank account
4. Updates payment status

Result:
✅ Payout goes to correct location's bank account
✅ No manual intervention needed
✅ Automatic reconciliation
```

## Testing Guide

### Test Case 1: Create Bank Account with Locations

**Steps:**
1. Login as storage facility owner
2. Go to Bank Setup page
3. Click "Add New Bank Account"
4. Fill in details:
   - Account Name: "Test Account"
   - BSB: "062-000"
   - Account Number: "12345678"
   - Bank: "Commonwealth Bank"
5. Select 2-3 locations from checkboxes
6. Click "Save Bank Account"

**Expected Result:**
- ✅ Success message appears
- ✅ Bank account appears in table
- ✅ Linked locations show in "Linked Locations" column
- ✅ Stripe metadata updated (check logs)

**Verification:**
```sql
-- Check bank account created
SELECT * FROM tbl_bank WHERE account_name = 'Test Account';

-- Check location links
SELECT * FROM tbl_storage_facility_bank_relationship WHERE bank_id = [bank_id];
```

### Test Case 2: Edit Location Links

**Steps:**
1. Click "Edit Locations" on existing bank account
2. Uncheck one location, check another
3. Click "Save"

**Expected Result:**
- ✅ Modal closes
- ✅ Location list updates in table
- ✅ Stripe metadata reflects changes

**Verification:**
```sql
-- Check updated links
SELECT sf.name, sfbr.* 
FROM tbl_storage_facility_bank_relationship sfbr
JOIN tbl_storage_facilities sf ON sf.id = sfbr.storage_facility_id
WHERE sfbr.bank_id = [bank_id];
```

### Test Case 3: Payout Routing

**Steps:**
1. Create auction for Location A (linked to Bank Account 1)
2. Complete auction and process payment
3. Check payment release log

**Expected Result:**
- ✅ Payment scheduled to Bank Account 1
- ✅ Correct bank_id in tbl_stripe_payment_release
- ✅ Payout processes successfully

**Verification:**
```sql
-- Check payment routing
SELECT spr.*, tb.account_name, tb.stripe_bank_account_id
FROM tbl_stripe_payment_release spr
JOIN tbl_bank tb ON tb.id = spr.bank_id
WHERE spr.invoice_id = [invoice_id];
```

### Test Case 4: Multiple Locations, One Bank

**Steps:**
1. Create one bank account
2. Link 3 different locations to it
3. Create auctions at all 3 locations
4. Process payments

**Expected Result:**
- ✅ All payouts route to same bank account
- ✅ Stripe metadata shows all 3 locations
- ✅ No errors in payout processing

### Test Case 5: Stripe Metadata Verification

**Steps:**
1. Add bank account with locations
2. Check Stripe dashboard
3. View account metadata

**Expected Result:**
```json
{
  "facility_status": "assigned",
  "facility_ids": "1,2,3",
  "facility_names": "Location A, Location B, Location C",
  "facility_count": "3",
  "facility_updated": "2026-02-22 05:56:00"
}
```

## Stripe Account Security

### Why Account Was Flagged

**Problem:**
- Multiple connected accounts created without proper verification
- Accounts created with minimal information
- No proper business profile setup

**Solution Implemented:**
```php
// Proper account creation with full details
$account = \Stripe\Account::create([
    'type' => 'express',
    'country' => 'AU',
    'email' => $email,
    'business_profile' => [
        'name' => $accountName,
        'product_description' => "Payouts for auction sales",
        'support_email' => $email,
        'support_url' => base_url(),
        'url' => base_url(),
        'mcc' => '7399',
    ],
    'capabilities' => [
        'transfers' => ['requested' => true],
    ],
    'metadata' => [
        'user_id' => $userId,
        'facility_status' => 'assigned',
        // ... location info
    ],
]);
```

### Best Practices for Production

1. **Rotate Stripe API Keys**
   - Delete old keys created before Feb 21, 2026
   - Create new restricted keys
   - Update .env file

2. **Complete Stripe Onboarding**
   - Verify business details
   - Add business documents
   - Complete KYC verification

3. **Monitor Account Status**
   - Check Stripe dashboard regularly
   - Set up webhook alerts
   - Monitor payout failures

4. **Test in Stripe Test Mode First**
   - Use test API keys
   - Create test accounts
   - Verify full flow works

## API Endpoints Summary

### Bank Account Management
```
GET  /bank/setup/new                                    - Bank setup page
POST /api/bank/get-accounts                             - Get all bank accounts
POST /api/bank/add-account                              - Add new bank account
POST /api/bank/set-default                              - Set default bank
POST /api/bank/detach-account                           - Detach bank account
```

### Location Linking
```
POST /api/users/storage-facility/bank-account/facilities        - Get linked locations
POST /api/users/storage-facility/bank-account/update-facilities - Update location links
```

### Storage Facilities
```
POST /api/user/fetch_storage_facilities_by_user        - Get all user locations
```

## Database Queries for Troubleshooting

### Check Bank-Location Links
```sql
SELECT 
    tb.id as bank_id,
    tb.account_name,
    tb.stripe_bank_account_id,
    sf.id as facility_id,
    sf.name as facility_name,
    sfbr.created_at as linked_at
FROM tbl_bank tb
LEFT JOIN tbl_storage_facility_bank_relationship sfbr ON tb.id = sfbr.bank_id
LEFT JOIN tbl_storage_facilities sf ON sf.id = sfbr.storage_facility_id
WHERE tb.user_id = [user_id]
ORDER BY tb.id, sf.name;
```

### Check Payout Routing
```sql
SELECT 
    i.id as invoice_id,
    a.title as auction_title,
    sf.name as location_name,
    tb.account_name as bank_account,
    spr.invoice_amount,
    spr.schedule_status
FROM tbl_invoices i
JOIN tbl_auctions a ON a.id = i.auction_id
JOIN tbl_auction_storage_facility_relationship asfr ON asfr.auction_id = a.id
JOIN tbl_storage_facilities sf ON sf.id = asfr.storage_facility_id
JOIN tbl_storage_facility_bank_relationship sfbr ON sfbr.storage_facility_id = sf.id
JOIN tbl_bank tb ON tb.id = sfbr.bank_id
LEFT JOIN tbl_stripe_payment_release spr ON spr.invoice_id = i.id
WHERE i.id = [invoice_id];
```

### Find Unlinked Bank Accounts
```sql
SELECT 
    tb.id,
    tb.account_name,
    tb.stripe_bank_account_id,
    COUNT(sfbr.id) as linked_locations
FROM tbl_bank tb
LEFT JOIN tbl_storage_facility_bank_relationship sfbr ON tb.id = sfbr.bank_id
WHERE tb.status_type_id = 1
GROUP BY tb.id
HAVING linked_locations = 0;
```

## Troubleshooting Common Issues

### Issue 1: "Not linked to any locations" showing

**Cause:** Location links not created during account setup

**Solution:**
1. Click "Edit Locations" button
2. Select locations
3. Save changes

**Verify:**
```sql
SELECT * FROM tbl_storage_facility_bank_relationship WHERE bank_id = [bank_id];
```

### Issue 2: Stripe metadata not updating

**Cause:** Stripe API error or account ID mismatch

**Solution:**
1. Check application logs: `application/logs/log-[date].php`
2. Verify Stripe account ID is correct
3. Check API key permissions

**Manual Update:**
```php
// Run in PHP console or create test script
$stripe = new \Stripe\StripeClient(stripe_secret_key());
$stripe->accounts->update('acct_xxxxx', [
    'metadata' => [
        'facility_status' => 'assigned',
        'facility_ids' => '1,2,3',
        'facility_names' => 'Location A, Location B, Location C',
        'facility_count' => 3,
        'facility_updated' => date('Y-m-d H:i:s'),
    ]
]);
```

### Issue 3: Payouts going to wrong account

**Cause:** Multiple bank accounts linked to same location

**Solution:**
1. Check which accounts are linked:
```sql
SELECT tb.account_name, COUNT(*) as link_count
FROM tbl_storage_facility_bank_relationship sfbr
JOIN tbl_bank tb ON tb.id = sfbr.bank_id
WHERE sfbr.storage_facility_id = [facility_id]
GROUP BY tb.id;
```

2. Remove duplicate links:
```sql
-- Keep only the most recent link
DELETE FROM tbl_storage_facility_bank_relationship
WHERE id NOT IN (
    SELECT MAX(id) 
    FROM tbl_storage_facility_bank_relationship
    WHERE storage_facility_id = [facility_id]
);
```

### Issue 4: Stripe account restricted

**Cause:** Account flagged due to suspicious activity

**Solution:**
1. Contact Stripe support
2. Provide business documentation
3. Explain legitimate use case
4. Wait for manual review (24-48 hours)

**Prevention:**
- Complete business verification upfront
- Use proper account metadata
- Don't create multiple test accounts in production
- Follow Stripe's best practices

## Files Modified

### Frontend Files
1. `assets/js/ajax/dashboard/storage_facility/bank/bank_account.js` - ✅ Complete rewrite
2. `application/views/dashboard/storage_facility/bank/add_bank.php` - ✅ Added location UI

### Backend Files
1. `application/controllers/Bank.php` - ✅ Updated addAccountForFacility()
2. `application/models/BankModel.php` - ✅ No changes (already had required methods)
3. `application/controllers/Webhook.php` - ✅ No changes (already working correctly)

### Database Files
1. `fix_missing_bank_table.sql` - ✅ Already existed (no changes needed)

## Production Deployment Checklist

- [ ] Backup database before deployment
- [ ] Test in staging environment first
- [ ] Verify all bank accounts have locations linked
- [ ] Check Stripe API keys are production keys
- [ ] Rotate old Stripe API keys
- [ ] Complete Stripe business verification
- [ ] Test full payout flow with real transaction
- [ ] Monitor logs for 24 hours after deployment
- [ ] Set up Stripe webhook monitoring
- [ ] Document any custom configurations

## Support & Maintenance

### Log Files to Monitor
```
application/logs/log-[date].php
- Look for: "Stripe metadata updated"
- Look for: "Stripe metadata update failed"
- Look for: "Payment release log created"
```

### Regular Maintenance Tasks
1. **Weekly:** Check for unlinked bank accounts
2. **Monthly:** Verify Stripe metadata accuracy
3. **Quarterly:** Review payout routing logs
4. **Yearly:** Update Stripe business information

### Contact Information
- **Stripe Support:** https://support.stripe.com
- **API Documentation:** https://stripe.com/docs/api
- **Webhook Testing:** https://stripe.com/docs/webhooks/test

## Conclusion

Yeh implementation ab fully functional hai:
- ✅ Bank accounts locations se properly link ho rahe hain
- ✅ Stripe metadata automatically update ho raha hai
- ✅ Payouts location-specific bank accounts mein ja rahe hain
- ✅ UI user-friendly aur responsive hai
- ✅ Complete testing guide available hai

**Production mein deploy karne se pehle:**
1. Stripe API keys rotate karein
2. Business verification complete karein
3. Test mode mein complete flow test karein
4. Backup lein aur staging mein test karein

Agar koi issue aaye to is documentation ko refer karein aur troubleshooting section follow karein.
