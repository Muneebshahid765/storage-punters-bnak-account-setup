<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
| -------------------------------------------------------------------------
| URI ROUTING
| -------------------------------------------------------------------------
| This file lets you re-map URI requests to specific controller functions.
|
| Typically there is a one-to-one relationship between a URL string
| and its corresponding controller class/method. The segments in a
| URL normally follow this pattern:
|
|	example.com/class/method/id/
|
| In some instances, however, you may want to remap this relationship
| so that a different class/function is called than the one
| corresponding to the URL.
|
| Please see the user guide for complete details:
|
|	https://codeigniter.com/userguide3/general/routing.html
|
| -------------------------------------------------------------------------
| RESERVED ROUTES
| -------------------------------------------------------------------------
|
| There are three reserved routes:
|
|	$route['default_controller'] = 'welcome';
|
| This route indicates which controller class should be loaded if the
| URI contains no data. In the above example, the "welcome" class
| would be loaded.
|
|	$route['404_override'] = 'errors/page_missing';
|
| This route will tell the Router which controller/method to use if those
| provided in the URL cannot be matched to a valid route.
|
|	$route['translate_uri_dashes'] = FALSE;
|
| This is not exactly a route, but allows you to automatically route
| controller and method names that contain dashes. '-' isn't a valid
| class or method name character, so it requires translation.
| When you set this option to TRUE, it will replace ALL dashes in the
| controller and method URI segments.
|
| Examples:	my-controller/index	-> my_controller/index
|		my-controller/my-method	-> my_controller/my_method
*/
$route['default_controller'] = 'Home';
$route['404_override'] = '';
$route['translate_uri_dashes'] = FALSE;


$route['login'] = 'Auth/signIn';
$route['api/user/checkUserLogin'] = 'Auth/checkUserLogin';
$route['auth/login'] = 'Auth/process_login';
$route['auth/logout'] = 'Auth/process_logout';

$route['api/user/authentication/details'] = 'Auth/get_user_authentication_details';

$route['register/standard-bidder'] = 'Auth/registerBidder';
$route['register/standard-seller'] = 'Auth/registerStorageFacility';

// Auctions Controller //
$route['auctions'] = 'Auctions/index';
$route['auctions/(:any)'] = 'Auctions/actionDetails/$1';
$route['api/auctions/getTopThreeAuctions'] = 'Auctions/getTopThreeAuctions';
$route['api/auctions/getAllAuctions'] = 'Auctions/getAllAuctions';
$route['api/auctions/getServerTime'] = 'Auctions/getServerTime';
$route['api/auctions/getAuctionById'] = 'Auctions/getAuctionById';
$route['api/auctions/add_bid_to_auction'] = 'Auctions/addBidToAuction';
$route['api/auctions/add_auction_to_watchlist'] = 'Auctions/addAuctionToWatchlist';
$route['api/auction/fetch_current_active_auctions_by_user'] = 'Auctions/getCurrentActiveAuctionsByBidderUser';
$route['api/auction/fetch_all_auctions_by_user'] = 'Auctions/getAllAuctionsByBidderUser';
$route['api/auction/fetch_watched_auctions_by_user'] = 'Auctions/getWatchedAuctionsByBidderUser';
$route['api/auction/fetch_won_auctions_by_user'] = 'Auctions/getWonAuctionsByBidderUser';
$route['api/invoice/fetch_invoices_auctions_by_user'] = 'Invoices/getInvoicesAuctionsByBidderUser';
$route['api/auction/fetch_won_auctions_by_storage_facility'] = 'Auctions/getWonAuctionsByStorageFacilityUser';

// Pages Controller //
$route['about-us'] = 'Pages/aboutUs';
$route['contact-us'] = 'Pages/contactUs';
$route['faqs'] = 'Pages/faqs';
$route['terms-and-conditions'] = 'Pages/termsAndConditions';
$route['privacy-policy'] = 'Pages/privacyPolicy';
$route['refund-policy'] = 'Pages/refundPolicy';


// AdminDashboard Controller //

$route['my-dashboard/dashboard'] = 'AdminDashboard/adminDashboard';
$route['my-dashboard/edit-account'] = 'AdminDashboard/adminAccount';
$route['my-dashboard/platform-settings'] = 'AdminDashboard/platformSettings';
$route['my-dashboard/bidder-fee-subscriptions'] = 'AdminDashboard/bidderFeeSubscriptions';
$route['my-dashboard/facility-plan-definitions'] = 'AdminDashboard/facilityPlanDefinitions';
$route['my-dashboard/location-analytics'] = 'AdminDashboard/locationAnalytics';
$route['my-dashboard/signup-bonus'] = 'AdminDashboard/signupBonus';
$route['my-dashboard/manage-bidders'] = 'AdminDashboard/manageBidders';
$route['my-dashboard/bidder-subscriptions'] = 'AdminDashboard/bidderSubscriptions';
$route['my-dashboard/bulk-credit'] = 'AdminDashboard/bulkCredit';
$route['my-dashboard/manage-facilities'] = 'AdminDashboard/manageFacilities';
$route['my-dashboard/manage-staff'] = 'AdminDashboard/manageStaff';
$route['my-dashboard/auctions'] = 'AdminDashboard/manageAuctions';
$route['my-dashboard/prize-drawer'] = 'AdminDashboard/prizeDrawer';
$route['my-dashboard/videos'] = 'AdminDashboard/manageVideos';

$route['api/users/admin/profile/get'] = 'Users/getAdminProfileById';
$route['api/users/admin/profile/update'] = 'Users/updateAdminProfileById';

$route['api/users/admin/setting/credit/get'] = 'AdminSettings/getAdminCreditSettingsById';
$route['api/users/admin/setting/credit/update'] = 'AdminSettings/updateAdminCreditSettingsById';

$route['api/users/admin/setting/commission/get'] = 'AdminSettings/getAdminCommissionSettingsById';
$route['api/users/admin/setting/commission/update'] = 'AdminSettings/updateAdminCommissionSettingsById';

// Subscription Plan Management APIs
$route['api/admin/bidder-plans/list'] = 'AdminDashboard/getBidderPlansList';
$route['api/admin/bidder-plans/create'] = 'AdminDashboard/createBidderPlan';
$route['api/admin/bidder-plans/update'] = 'AdminDashboard/updateBidderPlan';
$route['api/admin/bidder-plans/delete'] = 'AdminDashboard/deleteBidderPlan';
$route['api/admin/bidder-plans/update-base-fee'] = 'AdminDashboard/updateBaseFee';

$route['api/admin/facility-plans/list'] = 'AdminDashboard/getFacilityPlansList';
$route['api/admin/facility-plans/create'] = 'AdminDashboard/createFacilityPlan';
$route['api/admin/facility-plans/update'] = 'AdminDashboard/updateFacilityPlan';
$route['api/admin/facility-plans/delete'] = 'AdminDashboard/deleteFacilityPlan';

$route['api/admin/location-analytics/data'] = 'AdminDashboard/getLocationAnalytics';
$route['api/admin/signup-bonus/get'] = 'AdminDashboard/getSignupBonus';
$route['api/admin/signup-bonus/update'] = 'AdminDashboard/updateSignupBonus';
$route['api/admin/dashboard/stats'] = 'AdminDashboard/getDashboardStats';

// Admin Platform Management APIs
$route['api/admin/bidders/list'] = 'AdminDashboard/getBiddersList';
$route['api/admin/bidders/update'] = 'AdminDashboard/updateBidder';
$route['api/admin/bidders/add-credit'] = 'AdminDashboard/addBidderCredit';
$route['api/admin/bidders/toggle-status'] = 'AdminDashboard/toggleBidderStatus';

// Bidder Subscriptions APIs
$route['api/admin/subscriptions/list'] = 'AdminDashboard/getSubscriptionsList';
$route['api/admin/subscriptions/change-plan'] = 'AdminDashboard/changeSubscriptionPlan';
$route['api/admin/subscriptions/cancel'] = 'AdminDashboard/cancelSubscription';

// Bulk Credit APIs
$route['api/admin/bulk-credit/bidders'] = 'AdminDashboard/getBulkCreditBidders';
$route['api/admin/bulk-credit/apply'] = 'AdminDashboard/applyBulkCredit';

$route['api/admin/facilities/list'] = 'AdminDashboard/getFacilitiesList';
$route['api/admin/facilities/update'] = 'AdminDashboard/updateFacility';
$route['api/admin/facilities/toggle-status'] = 'AdminDashboard/toggleFacilityStatus';
$route['api/admin/facilities/locations'] = 'AdminDashboard/getFacilityLocations';

$route['api/admin/auctions/list'] = 'AdminDashboard/getAuctionsList';
$route['api/admin/auctions/update'] = 'AdminDashboard/updateAuction';
$route['api/admin/auctions/cancel'] = 'AdminDashboard/cancelAuction';

// Admin Staff Management APIs
$route['api/admin/staff/list'] = 'AdminDashboard/getStaffList';
$route['api/admin/staff/get/(:any)'] = 'AdminDashboard/getStaffDetails/$1';
$route['api/admin/staff/add'] = 'AdminDashboard/addStaff';
$route['api/admin/staff/update'] = 'AdminDashboard/updateStaff';
$route['api/admin/staff/toggle-status'] = 'AdminDashboard/toggleStaffStatus';

// Admin Vault Routes
$route['api/admin/vault/draws'] = 'AdminVault/manage_draws';
$route['api/admin/vault/get-draw'] = 'AdminVault/get_draw';
$route['api/admin/vault/activate-draw'] = 'AdminVault/activate_draw';
$route['api/admin/vault/create-draw'] = 'AdminVault/create_draw';
$route['api/admin/vault/update-draw'] = 'AdminVault/update_draw';
$route['api/admin/vault/delete-draw'] = 'AdminVault/delete_draw';
$route['api/admin/vault/delete-prize'] = 'AdminVault/delete_prize';
$route['api/admin/vault/add-prize'] = 'AdminVault/add_prize';
$route['api/admin/vault/trigger-draw'] = 'AdminVault/trigger_draw';
$route['api/admin/vault/stats'] = 'AdminVault/get_stats';

// Admin Videos Routes
$route['api/admin/videos/seasons'] = 'AdminVideos/list_seasons';
$route['api/admin/videos/create-season'] = 'AdminVideos/create_season';
$route['api/admin/videos/add-episode'] = 'AdminVideos/add_episode';
$route['api/admin/videos/update-season'] = 'AdminVideos/update_season';
$route['api/admin/videos/delete-season'] = 'AdminVideos/delete_season';
$route['api/admin/videos/update-episode'] = 'AdminVideos/update_episode';
$route['api/admin/videos/delete-episode'] = 'AdminVideos/delete_episode';

// BidderDashboard Controller //
$route['show-dashboard/dashboard'] = 'BidderDashboard/bidderDashboard';
$route['show-dashboard/edit-account'] = 'BidderDashboard/bidderAccount';
$route['show-dashboard/payment-method'] = 'BidderDashboard/bidderPaymentMethod';
$route['show-dashboard/notifications'] = 'BidderDashboard/bidderNotification';
$route['show-dashboard/membership'] = 'BidderDashboard/bidderMembership';
$route['show-dashboard/invoices'] = 'BidderDashboard/bidderInvoices';
$route['show-dashboard/coin-shop'] = 'BidderDashboard/bidderCoinShop';

// Invoice APIs
$route['api/invoices/get'] = 'Invoices/getInvoicesAuctionsByBidderUser';
$route['invoices/download/(:any)'] = 'Invoices/downloadInvoice/$1';

$route['show-dashboard/auctions/owner-contact'] = 'BidderDashboard/showOwnerContactAfterPayment';


// Users Controller //
$route['api/users/standard-bidder/registration'] = 'Users/registerNewBidder';
$route['api/users/standard-seller/registration'] = 'Users/registerNewStoragePunter';

$route['api/users/biddent/wallet_balance/get'] = 'Users/getBidderBalanceById';
$route['api/users/bidder/dashboard/stats'] = 'Users/getDashboardStats';
$route['api/users/bidder/profile/get'] = 'Users/getBidderProfileById';
$route['api/users/bidder/profile/update'] = 'Users/updateBidderProfileById';

$route['api/users/storage-facility/profile/get'] = 'Users/getStorageFacilityProfileById';
$route['api/users/storage-facility/profile/update'] = 'Users/updateStorageFacilityProfileById';



$route['api/users/bidder/payment-method/add'] = 'Users/addBidderPaymentMethodById';
$route['api/users/bidder/payment-method/update'] = 'Users/updateBidderPaymentMethodById';
$route['api/users/bidder/payment-method/update-status'] = 'StripeController/attachDetachBidderPaymentMethodCardStatusFromStripeById';
$route['api/users/bidder/payment-method/update-card-status'] = 'StripeController/updateBidderPaymentMethodCardStatusFromStripeById';
$route['api/users/bidder/payment-method/get'] = 'StripeController/getBidderPaymentMethodFromStripeById';
$route['api/users/invitation/accept'] = 'Users/acceptInvitationByStaff';
$route['api/users/invitation/cancel'] = 'Users/cancelInvitationByStaff';

$route['api/users/storage-facility/payment-method/get'] = 'StripeController/getBidderPaymentMethodFromStripeById';
$route['api/users/storage-facility/payment-method/update-status'] = 'StripeController/attachDetachBidderPaymentMethodCardStatusFromStripeById';
$route['api/users/storage-facility/payment-method/update-card-status'] = 'StripeController/updateBidderPaymentMethodCardStatusFromStripeById';

$route['api/users/bidder/notifications/get'] = 'Notifications/getBidderNotificationById';
$route['api/users/bidder/notifications/update'] = 'Notifications/updateBidderNotificationStatusById';

$route['api/users/bidder/membership/subscribe'] = 'Users/subscribeBidderMembership';
$route['api/users/bidder/membership/cancel'] = 'Users/subscribeCancelBidderMembership';
$route['api/users/bidder/membership/claim_retention'] = 'Users/claimRetentionBonus';

// StorageFacilityDashboard Controller //
$route['storage-facility/dashboard'] = 'StorageFacilityDashboard/storageFacilityDashboard';
$route['storage-facility/edit-account'] = 'StorageFacilityDashboard/storageFacilityAccount';
$route['storage-facility/manage'] = 'StorageFacilityDashboard/storageFacilityManage';
$route['storage-facility/add'] = 'StorageFacilityDashboard/storageFacilityAdd';
$route['storage-facility/edit/(:any)'] = 'StorageFacilityDashboard/storageFacilityDetailsEditByStorageFacilityUser/$1';
$route['storage-facility/staff/manage'] = 'StorageFacilityDashboard/storageFacilityStaffManage';
$route['storage-facility/ai/new'] = 'StorageFacilityDashboard/newListingAI';
$route['storage-facility/auctions'] = 'StorageFacilityDashboard/auctionManage';


// Auction APIs
$route['api/auction/statuses'] = 'Auctions/getAllStatuses';
$route['api/auction/types'] = 'Auctions/getAllTypes';
$route['api/auction/categories'] = 'Auctions/getAllCategories';
// Storage Facility APIs
$route['api/user/fetch_storage_facilities_by_user'] = 'StorageFacility/getAllFacilitiesByUser';



// Companies Controller //
$route['api/companies/storage-facility/add'] = 'Companies/addCompanyByStorageFacilityUser';
$route['api/companies/storage-facility/update'] = 'Companies/updateCompanyByStorageFacilityUser';
$route['api/companies/storage-facility/get_by_company_id'] = 'Companies/getCompanyInformationById';
$route['api/user/fetch_companies_by_user'] = 'Companies/getAllCompaniesByUser';

// Storage Controller //
$route['api/storage/storage-facility/add'] = 'StorageFacility/addStorageFacilityByStorageFacilityUser';
$route['api/storage/storage-facility/update'] = 'StorageFacility/updateStorageFacilityByStorageFacilityUser';
$route['api/storage/storage-facility/update-status'] = 'StorageFacility/updateStorageFacilityStatusByIdByStorageFacilityUser';
$route['api/storage/storage-facility/delete'] = 'StorageFacility/deleteStorageFacility';
$route['api/storage/storage-facility/import'] = 'StorageFacility/importStorageFacilities';
$route['api/storage/storage-facility/get'] = 'StorageFacility/getAllStorageFacility';
$route['api/storage/storage-facility/get_by_storage_id'] = 'StorageFacility/getAllStorageFacilityById';
$route['api/storage/ai/generate'] = 'StorageFacility/generate_ai_listing';
$route['api/storage/ai/save'] = 'StorageFacility/save_ai_auction';

// Staff Controller //
$route['api/staff/storage-facility/add'] = 'Staff/addStaffMemberByStorageFacilityUser';
$route['api/staff/storage-facility/update'] = 'Staff/updateStaffMemberByStorageFacilityUser';
$route['api/staff/storage-facility/update-status'] = 'Staff/updateStaffMemberStatusByIdByStorageFacilityUser';
$route['api/staff/storage-facility/get'] = 'Staff/getAllStaffMember';
$route['api/staff/storage-facility/get_assigned_locations'] = 'Staff/getStaffAssignedLocationsByStaffId';
$route['api/staff/storage-facility/get_permissions'] = 'Staff/getStaffPermissionsByStaffId';
$route['api/staff/run-migration-fix'] = 'Staff/run_migration_fix';
$route['api/staff/storage-facility/delete'] = 'Staff/deleteStaffMemberByStorageFacilityUser';

// Auctions Controller //
$route['api/auction/confirm_auction'] = 'Auctions/addAuctionToConfirm';
$route['api/auction/fetch_all_auctions_by_storage_facility'] = 'Auctions/getAllAuctionsByStorageFacilityUser';
$route['api/auction/storage-facility/add'] = 'Auctions/addAuctionByStorageFacilityUser';
$route['api/auction/storage-facility/update'] = 'Auctions/updateAuctionByAuctionIdForStorageFacilityUser';
$route['api/auction/storage-facility/update-status'] = 'Auctions/updateAuctionStatusByAuctionIdForStorageFacilityUser';
$route['api/auction/storage-facility/delete'] = 'Auctions/deleteAuctionByAuctionIdForStorageFacilityUser';
$route['api/auction/storage-facility/get'] = 'Auctions/getAuctionsStatusByStorageFacilityUser';
$route['auctions/edit/(:any)'] = 'Auctions/auctionDetailsEditByStorageFacilityUser/$1';
$route['auctions/create/new'] = 'Auctions/auctionDetailsAddByStorageFacilityUser';
$route['api/auction/remove_views'] = 'Auctions/auctionRemoveViewDataByBidderUser';



// Company Controller //
$route['company/edit/(:any)'] = 'Companies/companyDetailsViewByStorageFacilityUser/$1';
$route['company/setup/new'] = 'Companies/companyDetailsSetupAddByStorageFacilityUser';
$route['company'] = 'Companies';
$route['companies/get_by_company_id'] = 'Companies/getCompanyDetailsById';

$route['company/global-auction-rules'] = 'StorageFacilityDashboard/globalAuctionRules';
$route['api/companies/storage-facility/get_global_auction_rules'] = 'StorageFacilityDashboard/getGlobalAuctionRules';
$route['api/companies/storage-facility/save_global_auction_rules'] = 'StorageFacilityDashboard/saveGlobalAuctionRules';

$route['storage-facility/notifications'] = 'StorageFacilityDashboard/notifications';
$route['api/companies/storage-facility/get_notification_preferences'] = 'StorageFacilityDashboard/getNotificationPreferences';
$route['api/companies/storage-facility/save_notification_preferences'] = 'StorageFacilityDashboard/saveNotificationPreferences';

// AI Location Discovery Routes
$route['api/companies/discover-locations'] = 'Companies/discoverLocations';
$route['api/companies/onboard-locations'] = 'Companies/onboardLocations';

// Bank Controller //
$route['bank/edit/(:any)'] = 'Bank/bankDetailsViewByStorageFacilityUser/$1';
$route['bank/setup/new'] = 'Bank/bankDetailsSetupAddByStorageFacilityUser';
$route['bank'] = 'Bank';
$route['api/bank/get-accounts'] = 'Bank/getAccountsForFacility';
$route['api/bank/add-account'] = 'Bank/addAccountForFacility';
$route['api/bank/set-default'] = 'Bank/setDefaultAccountForFacility';
$route['api/bank/detach-account'] = 'Bank/detachAccountForFacility';

$route['api/facility/payment-method/add'] = 'FacilityPayment/addPaymentMethod';
$route['api/facility/payment-method/get'] = 'FacilityPayment/getPaymentMethodsForFacility';
$route['api/facility/payment-method/detach'] = 'FacilityPayment/detachPaymentMethod';

$route['api/users/storage-facility/bank-account/get'] = 'StripeController/getStorageFacilityAccountConnectLinkedFromStripeById';
$route['api/users/storage-facility/bank-account/update-status'] = 'StripeController/attachDetachStorageFacilityBankAccountStatusFromStripeById';
$route['api/users/storage-facility/bank-account/update-bank-status'] = 'StripeController/updateStorageFacilityBankAccountStatusFromStripeById';
$route['api/users/storage-facility/bank-account/facilities'] = 'Bank/getBankAccountStorageFacilityById';
$route['api/users/storage-facility/bank-account/update-facilities'] = 'Bank/updateBankAccountFacilitiesById';

$route['api/email/test'] = 'Email/test_email';



$route['api/stripe/customer/search_by_email'] = 'StripeController/searchStripeCustomer';
$route['api/stripe/customer/add'] = 'StripeController/addStripeCustomer';
$route['api/stripe/setup-intent'] = 'StripeController/createStripeSetupIntent';
$route['api/stripe/invoice/payment'] = 'StripeController/invoicePaymentBidderFromStripeById';
$route['api/stripe/create-onboarding-link'] = 'StripeController/createConnectOnboardingLink';
$route['stripe/connect/return'] = 'StripeController/connectReturn';
$route['stripe/connect/refresh'] = 'StripeController/connectRefresh';

$route['api/stripe/prepare-registration'] = 'StripeController/prepareRegistration';
$route['api/stripe/prepare-facility-registration'] = 'StripeController/prepareFacilityRegistration';
$route['api/stripe/payment-method/attach'] = 'StripeController/attachStripePaymentMethod';
$route['api/stripe/customer/payment-method/update'] = 'StripeController/addStripePaymentMethod';

$route['api/stripe/bank-account/attach'] = 'StripeController/addBankByStorageFacilityUserFromStripeById';
$route['api/stripe/connected-account/attach'] = 'StripeController/addConnectedAccountByStorageFacilityUserFromStripeById';
$route['stripe/connected-account/success/(:any)'] = 'StripeController/successConnectedAccountByStorageFacility/$1';
$route['stripe/connected-account/reauth/(:any)'] = 'StripeController/reauthConnectedAccountByStorageFacility/$1';

$route['webhook/stripe'] = 'webhook/stripe_webhook';
$route['webhook/stripe/subscription'] = 'webhook/stripe_webhook';
$route['webhook/stripe/invoice/payment-status'] = 'webhook/stripe_webhook';
$route['webhook/stripe/account-connected-link/update-status'] = 'webhook/stripeAccountConnectLinkedUpdateStatus';
$route['stripe/checkout/entries/(:num)'] = 'StripeController/checkout_entries/$1';

// Vault Routes
$route['vault'] = 'Vault/index';
$route['api/vault/current'] = 'Vault/current';
$route['api/vault/winners'] = 'Vault/winners';
$route['api/vault/packages'] = 'Vault/get_packages';
$route['api/vault/buy-entries'] = 'Vault/buy_entries_with_coins';

// Subscription Routes
$route['api/subscription/status'] = 'Subscription/status';
$route['api/subscription/cancel-info'] = 'Subscription/cancel_start';
$route['api/subscription/claim-bonus'] = 'Subscription/claim_retention_bonus';
$route['api/subscription/cancel-confirm'] = 'Subscription/cancel_confirm';

// Videos Routes
$route['videos'] = 'Videos/index';
$route['api/videos/list'] = 'Videos/api_list';

$route['api/logs/invoice/payment-history'] = 'Logs/invoicePaymentHistoryBidderFromStripeById';
$route['api/logs/auction/confirm-logs'] = 'Logs/auctionConfirmWinnerLogsById';


$route['cron/send-auction-to-queue'] = 'Queue/sendAuctionToQueue';

$route['email/process-auction-win'] = 'Email/process_auction_win_email';
$route['email/process-auction-outbid'] = 'Email/process_auction_outbid_email';
$route['email/process-auction-outbid-by-id/(:num)'] = 'Email/process_auction_outbid_email_by_id/$1';
$route['email/process-queue-1h'] = 'Email/process_queue_reminder_1hr';
$route['email/process-queue-3h'] = 'Email/process_queue_reminder_3hr';
$route['email/process-auction-invoice'] = 'Email/process_auction_invoice_email';
$route['email/process-forgot-password'] = 'Email/forgot_password_email';
$route['email/process-password-reset-success'] = 'Email/user_account_password_reset_success_email';
$route['email/process-staff-invitation'] = 'Email/staff_invitation_email';
$route['email/process-resend-staff-invitation'] = 'Email/resend_staff_invitation_email';
$route['email/process-general-queue'] = 'Email/process_general_queue';

$route['test/process-auction-expiration-auto'] = 'Auctions/testAuctionToBackgroundSchedule';

$route['api/users/send-welcome-email-to-bidder'] = 'Email/register_email_for_bidder';
$route['api/users/send-welcome-email-to-storage-punter'] = 'Email/register_email_for_storage_facility';
$route['api/users/send-otp'] = 'Email/send_otp';
$route['api/users/verify-otp'] = 'Email/verify_otp';

$route['api/membership/plan/get'] = 'StripeMembershipPlans/getStripeMembershipProduct';
$route['api/users/bidder/membership/get'] = 'StripeMembershipPlans/getBidderMembershipByUserId';
$route['api/facility/membership/plan/get'] = 'FacilityMembership/getFacilityMembershipProduct';
$route['api/facility/membership/subscribe'] = 'FacilityMembership/subscribeFacilityMembership';
$route['api/facility/membership/cancel'] = 'FacilityMembership/cancelFacilityMembership';
$route['storage-facility/subscription'] = 'StorageFacilityDashboard/subscription';


$route['activate-account'] = 'Users/activate_account';
$route['forgot-password-account'] = 'Users/forgot_password_account';
$route['accept-invitation'] = 'Users/staff_invitation_account';

$route['api/users/account/process-reset-password'] = 'Users/reset_user_account_password';


$route['registration-success'] = 'Users/registrationSuccessPage';
$route['api/uploads/bulk-upload-images'] = 'Uploads/bulkUploadImages';
$route['media/uploads/auctions/(:any)'] = 'Uploads/getUploadImage/$1';

// The Vault & Coin Economy
$route['vault/status'] = 'Vault/status';
$route['coin-shop'] = 'CoinShop/index';
$route['coin-shop/buy_with_coins'] = 'CoinShop/buy_with_coins';
$route['coin-shop/buy_with_cash'] = 'CoinShop/buy_with_cash';

// Admin Vault
$route['admin/vault/pick_winner/(:num)'] = 'AdminVault/pick_winner/$1';

// Vault Settings
$route['api/vault/notifications/get'] = 'Vault/get_notifications';
$route['api/vault/notifications/update'] = 'Vault/update_notifications';

// $route['test/process_auction_win_test'] = 'Webhook/process_auction_win_test';

$route['my-dashboard/economy'] = 'AdminDashboard/manageEconomy';
$route['my-dashboard/giveaway-draws'] = 'AdminDashboard/manageGiveawayDraws';
$route['my-dashboard/manage-subscriptions'] = 'AdminDashboard/manageSubscriptions';

// Admin Economy API
$route['admin/economy/update-rules'] = 'AdminEconomy/update_rules';

// Admin Subscription Management API
$route['api/admin/subscriptions/stats'] = 'AdminSubscriptions/getStats';
$route['api/admin/subscriptions/tiers'] = 'AdminSubscriptions/getTiers';
$route['api/admin/subscriptions/tier/(:num)'] = 'AdminSubscriptions/getTier/$1';
$route['api/admin/subscriptions/tier/update/(:num)'] = 'AdminSubscriptions/updateTier/$1';
$route['api/admin/subscriptions/list'] = 'AdminSubscriptions/getSubscriptions';
$route['api/admin/subscriptions/cancel/(:num)'] = 'AdminSubscriptions/cancelSubscription/$1';
$route['api/admin/subscriptions/grant-entries'] = 'AdminSubscriptions/grantEntries';
$route['admin/economy/list-coin-packages'] = 'AdminEconomy/list_coin_packages';
$route['admin/economy/list-cash-packages'] = 'AdminEconomy/list_cash_packages';
$route['admin/economy/save-coin-package'] = 'AdminEconomy/save_coin_package';
$route['admin/economy/save-cash-package'] = 'AdminEconomy/save_cash_package';

// ============================================================
// Subscription Giveaway Routes
// ============================================================

// Public Pages
$route['join'] = 'SubscriptionGiveaway/plans';
$route['join/checkout/(:any)'] = 'SubscriptionGiveaway/checkout/$1';
$route['subscription/success'] = 'SubscriptionGiveaway/success';

// Public API
$route['api/subscription/tiers'] = 'SubscriptionGiveaway/api_tiers';
$route['api/subscription/countdown'] = 'SubscriptionGiveaway/api_countdown';
$route['api/subscription/states'] = 'SubscriptionGiveaway/api_states';
$route['api/subscription/create-checkout'] = 'SubscriptionGiveaway/create_checkout_session';

// Authenticated User API
$route['api/subscription/membership-status'] = 'SubscriptionGiveaway/api_membership_status';
$route['api/subscription/cancel'] = 'SubscriptionGiveaway/api_cancel_subscription';
$route['api/subscription/undo-cancel'] = 'SubscriptionGiveaway/api_undo_cancellation';
$route['api/subscription/fix-billing-dates'] = 'SubscriptionGiveaway/fix_billing_dates';

// Admin API
$route['api/admin/giveaway/dashboard'] = 'SubscriptionGiveaway/admin_draw_dashboard';
$route['api/admin/giveaway/lock-entries'] = 'SubscriptionGiveaway/admin_lock_entries';
$route['api/admin/giveaway/export-csv'] = 'SubscriptionGiveaway/admin_export_csv';
$route['api/admin/giveaway/upload-results'] = 'SubscriptionGiveaway/admin_upload_results';
$route['api/admin/giveaway/adjust-entries'] = 'SubscriptionGiveaway/admin_adjust_entries';
$route['api/admin/giveaway/adjust-coins'] = 'SubscriptionGiveaway/admin_adjust_coins';
$route['api/admin/giveaway/user-details'] = 'SubscriptionGiveaway/admin_get_user_details';
$route['api/admin/giveaway/draw-winners'] = 'SubscriptionGiveaway/admin_get_draw_winners';
$route['api/admin/giveaway/search-subscribers'] = 'SubscriptionGiveaway/admin_search_subscribers';
$route['api/admin/giveaway/search-users'] = 'SubscriptionGiveaway/admin_search_users';
$route['api/admin/giveaway/update-settings'] = 'SubscriptionGiveaway/admin_update_settings';
$route['api/admin/giveaway/create-draw'] = 'SubscriptionGiveaway/admin_create_draw';

// SMS Verification API
$route['api/sms/send-otp'] = 'SmsVerification/send_otp';
$route['api/sms/verify-otp'] = 'SmsVerification/verify_otp';
$route['api/users/update-phone-verification'] = 'Users/updatePhoneVerification';

// Stripe Webhook for Subscriptions
$route['webhook/stripe/subscription'] = 'SubscriptionGiveaway/stripe_webhook';