<?php
defined('BASEPATH') or exit('No direct script access allowed');

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentMethod;
use Stripe\Exception\ApiErrorException;

class FacilityPayment extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        
        if (!$this->session->userdata('auction_user_session_data')) {
            header('Content-Type: application/json');
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized'], JSON_PRETTY_PRINT);
            exit;
        }

        $secretKey = stripe_secret_key();
        if ($secretKey) {
            Stripe::setApiKey($secretKey);
        } else {
            log_message('error', 'Stripe secret key missing; please set STRIPE_SECRET_KEY in .env');
        }

        date_default_timezone_set('Australia/Brisbane');
        $this->load->model('UserModel');
    }

    private function jsonResponse($data, $code = 200)
    {
        http_response_code($code);
        header('Content-Type: application/json');
        echo json_encode($data, JSON_PRETTY_PRINT);
        exit;
    }

    public function addPaymentMethod()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Invalid request method'], 405);
        }

        if (!$this->input->is_ajax_request()) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Access denied'], 403);
        }

        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Invalid JSON payload'], 400);
        }

        $paymentMethodId = $json_input['payment_method_id'] ?? null;
        if (!$paymentMethodId) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Payment method ID is required'], 400);
        }

        try {
            // Get or create Stripe customer
            $stripeData = $this->UserModel->getUserStripeIdById($userId);
            $customerId = $stripeData['stripe_customer_id'] ?? null;

            if (!$customerId) {
                $customer = Customer::create([
                    'email' => $userSessionData['email'],
                    'name' => $userSessionData['first_name'] . ' ' . ($userSessionData['last_name'] ?? ''),
                    'metadata' => ['user_id' => $userId]
                ]);
                $customerId = $customer->id;
                
                $this->db->insert('tbl_user_stripe_relationship', [
                    'user_id' => $userId,
                    'stripe_customer_id' => $customerId,
                    'created_at' => date('Y-m-d H:i:s')
                ]);
            }

            // Attach payment method to customer
            $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
            $paymentMethod->attach(['customer' => $customerId]);

            // Set as default payment method
            Customer::update($customerId, [
                'invoice_settings' => ['default_payment_method' => $paymentMethodId]
            ]);

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Payment method added successfully',
                'customer_id' => $customerId
            ]);

        } catch (ApiErrorException $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function getPaymentMethodsForFacility()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Invalid request method'], 405);
        }

        if (!$this->input->is_ajax_request()) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Access denied'], 403);
        }

        try {
            $stripeData = $this->UserModel->getUserStripeIdById($userId);
            $customerId = $stripeData['stripe_customer_id'] ?? null;

            if (!$customerId) {
                return $this->jsonResponse(['status' => 'success', 'data' => []]);
            }

            $paymentMethods = PaymentMethod::all([
                'customer' => $customerId,
                'type' => 'card',
            ]);

            $customer = Customer::retrieve($customerId);
            $defaultPaymentMethodId = $customer->invoice_settings->default_payment_method ?? null;

            $cards = [];
            $nowYear = (int)date('Y');
            $nowMonth = (int)date('m');

            foreach ($paymentMethods->data as $pm) {
                $expYear = (int)$pm->card->exp_year;
                $expMonth = (int)$pm->card->exp_month;
                $isExpired = ($expYear < $nowYear) || ($expYear === $nowYear && $expMonth < $nowMonth);

                $cards[] = [
                    'id' => $pm->id,
                    'brand' => $pm->card->brand,
                    'last4' => $pm->card->last4,
                    'exp_month' => $expMonth,
                    'exp_year' => $expYear,
                    'is_default' => ($pm->id === $defaultPaymentMethodId),
                    'is_expired' => $isExpired,
                ];
            }

            $activeCards = array_values(array_filter($cards, function ($card) {
                return !$card['is_expired'];
            }));

            if (empty($activeCards)) {
                return $this->jsonResponse([
                    'status' => 'error',
                    'code' => 'no_active_cards',
                    'message' => 'Your saved card is expired or inactive. Please add a new payment method.',
                    'data' => $cards,
                ], 200);
            }

            return $this->jsonResponse(['status' => 'success', 'data' => $activeCards]);

        } catch (ApiErrorException $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }

    public function detachPaymentMethod()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Invalid request method'], 405);
        }

        if (!$this->input->is_ajax_request()) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Unauthorized'], 403);
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Access denied'], 403);
        }

        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Invalid JSON payload'], 400);
        }

        $paymentMethodId = $json_input['payment_method_id'] ?? null;
        if (!$paymentMethodId) {
            return $this->jsonResponse(['status' => 'error', 'message' => 'Payment method ID is required'], 400);
        }

        try {
            $stripeData = $this->UserModel->getUserStripeIdById($userId);
            $customerId = $stripeData['stripe_customer_id'] ?? null;

            if (!$customerId) {
                return $this->jsonResponse(['status' => 'error', 'message' => 'No customer found'], 404);
            }

            // Detach payment method from Stripe
            $paymentMethod = PaymentMethod::retrieve($paymentMethodId);
            
            // Verify this payment method belongs to this customer
            if ($paymentMethod->customer !== $customerId) {
                return $this->jsonResponse(['status' => 'error', 'message' => 'Payment method does not belong to this customer'], 403);
            }

            $paymentMethod->detach();

            return $this->jsonResponse([
                'status' => 'success',
                'message' => 'Payment method removed successfully'
            ]);

        } catch (ApiErrorException $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        } catch (\Exception $e) {
            return $this->jsonResponse(['status' => 'error', 'message' => $e->getMessage()], 500);
        }
    }
}
