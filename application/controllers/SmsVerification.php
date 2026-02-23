<?php
defined('BASEPATH') or exit('No direct script access allowed');

class SmsVerification extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->config->load('twilio', TRUE);
        $this->load->library('TwilioSMS');
    }

    private function respond($success, $message, $data = [], $code = 200)
    {
        return $this->output
            ->set_status_header($code)
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => $success,
                'message' => $message,
                'data' => $data
            ]));
    }

    /**
     * Send OTP to phone number
     * POST: { phone: "+61412345678" }
     */
    public function send_otp()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $phone = $input['phone'] ?? null;

        if (!$phone) {
            return $this->respond(false, 'Phone number is required.', [], 400);
        }

        // Normalize Australian phone
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '+61' . substr($phone, 1);
        }
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+' . $phone;
        }

        if (!preg_match('/^\+614\d{8}$/', $phone)) {
            return $this->respond(false, 'Please enter a valid Australian mobile number (+614XXXXXXXX).', [], 400);
        }

        // Rate limit: max 3 OTPs per session per 10 minutes
        $otp_count = $this->session->userdata('sms_otp_count') ?: 0;
        $otp_first = $this->session->userdata('sms_otp_first_sent') ?: 0;

        if ($otp_count >= 3 && (time() - $otp_first) < 600) {
            return $this->respond(false, 'Too many verification attempts. Please wait 10 minutes.');
        }

        if ((time() - $otp_first) >= 600) {
            $otp_count = 0;
            $this->session->set_userdata('sms_otp_first_sent', time());
        }

        $otpCode = $this->twiliosms->generate_otp();
        $this->twiliosms->store_otp($phone, $otpCode);

        $sent = $this->twiliosms->send_verification($phone, $otpCode);
        log_message('info', 'SMS OTP send attempt: phone=' . $phone . ' sent=' . (int)$sent . ' twilio_enabled=' . (int)$this->twiliosms->is_enabled());

        $this->session->set_userdata('sms_otp_count', $otp_count + 1);
        if ($otp_count === 0) {
            $this->session->set_userdata('sms_otp_first_sent', time());
        }

        if ($sent) {
            $response_data = [
                'phone_masked' => substr($phone, 0, 4) . '****' . substr($phone, -3),
                'twilio_enabled' => $this->twiliosms->is_enabled(),
                'phone' => $phone
            ];

            if (!$this->twiliosms->is_enabled()) {
                $response_data['dev_code'] = $otpCode;
                $response_data['message'] = 'Twilio SMS integration is not active on this environment. Use the code below for verification.';
            }

            return $this->respond(true, 'Verification code sent to your phone.', $response_data, 200);
        } else {
            $httpCode = $this->twiliosms->is_enabled() ? 500 : 200;
            $message = $this->twiliosms->is_enabled()
                ? 'Failed to send verification code. Please try again.'
                : 'Twilio SMS integration is not active on this environment. Use the code below for verification.';

            $payload = [
                'phone_masked' => substr($phone, 0, 4) . '****' . substr($phone, -3),
                'twilio_enabled' => $this->twiliosms->is_enabled(),
                'phone' => $phone,
                'twilio_error' => $this->twiliosms->get_last_error()
            ];

            if (!$this->twiliosms->is_enabled()) {
                $payload['dev_code'] = $otpCode;
            }

            return $this->respond(false, $message, $payload, $httpCode);
        }
    }

    /**
     * Verify OTP
     * POST: { phone: "+61412345678", code: "123456" }
     */
    public function verify_otp()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        $phone = $input['phone'] ?? null;
        $code = $input['code'] ?? null;

        if (!$phone || !$code) {
            return $this->respond(false, 'Phone and code are required.', [], 400);
        }

        // Normalize
        $phone = preg_replace('/[^0-9+]/', '', $phone);
        if (substr($phone, 0, 1) === '0') {
            $phone = '+61' . substr($phone, 1);
        }
        if (substr($phone, 0, 1) !== '+') {
            $phone = '+' . $phone;
        }

        $result = $this->twiliosms->verify_otp($phone, $code);

        if ($result['valid']) {
            // Mark phone as verified in session
            $this->session->set_userdata('phone_verified', $phone);
            return $this->respond(true, $result['message']);
        } else {
            return $this->respond(false, $result['message']);
        }
    }
}
