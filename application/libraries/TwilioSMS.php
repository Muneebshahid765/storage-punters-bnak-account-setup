<?php
defined('BASEPATH') or exit('No direct script access allowed');

require_once FCPATH . 'vendor/twilio/sdk/src/Twilio/autoload.php';

use Twilio\Rest\Client;

class TwilioSMS
{
    private $ci;
    private $client;
    private $from;
    private $enabled;
    private $lastError;

    public function __construct()
    {
        $this->ci =& get_instance();

        // Load Twilio credentials from config or environment
        $sid = $this->getEnvValue('TWILIO_SID') ?: $this->ci->config->item('twilio_sid');
        $token = $this->getEnvValue('TWILIO_TOKEN') ?: $this->ci->config->item('twilio_token');
        $this->from = $this->getEnvValue('TWILIO_FROM') ?: $this->ci->config->item('twilio_from');
        $this->enabled = !empty($sid) && !empty($token) && !empty($this->from);
        log_message('info', 'Twilio config resolved', ['sid' => $sid ? substr($sid, 0, 6) . '***' : null, 'from' => $this->from, 'enabled' => $this->enabled]);

        if ($this->enabled) {
            try {
                $this->client = new Client($sid, $token);
            } catch (\Exception $e) {
                log_message('error', 'Twilio init error: ' . $e->getMessage());
                $this->lastError = [
                    'stage' => 'init',
                    'message' => $e->getMessage(),
                    'code' => $e->getCode()
                ];
                $this->enabled = false;
            }
        }
    }

    public function get_last_error()
    {
        return $this->lastError;
    }

    private function getEnvValue($key)
    {
        $value = getenv($key);
        if ($value === false || $value === null || $value === '') {
            if (isset($_ENV[$key])) {
                $value = $_ENV[$key];
            } elseif (isset($_SERVER[$key])) {
                $value = $_SERVER[$key];
            }
        }

        return $value;
    }

    /**
     * Send SMS verification code to a phone number
     */
    public function send_verification($phone, $code)
    {
        $this->lastError = null;
        if (!$this->enabled) {
            log_message('info', "Twilio disabled - OTP $code would be sent to $phone");
            return true; // Return true in dev mode so registration flow continues
        }

        try {
            $message = $this->client->messages->create($phone, [
                'from' => $this->from,
                'body' => "Your Storage Punters verification code is: $code. This code expires in 10 minutes."
            ]);

            log_message('info', "Twilio SMS sent to $phone - SID: " . $message->sid);
            return true;
        } catch (\Exception $e) {
            $this->lastError = [
                'stage' => 'send',
                'message' => $e->getMessage(),
                'code' => $e->getCode()
            ];
            log_message('error', 'Twilio send error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Generate a 6-digit OTP code
     */
    public function generate_otp()
    {
        return str_pad(random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    }

    /**
     * Store OTP in session with expiry
     */
    public function store_otp($phone, $code)
    {
        $this->ci->session->set_userdata('sms_otp', [
            'phone' => $phone,
            'code' => $code,
            'expires_at' => time() + 600 // 10 minutes
        ]);
    }

    /**
     * Verify OTP from session
     */
    public function verify_otp($phone, $code)
    {
        $otp_data = $this->ci->session->userdata('sms_otp');

        if (!$otp_data) {
            return ['valid' => false, 'message' => 'No verification code found. Please request a new one.'];
        }

        if ($otp_data['expires_at'] < time()) {
            $this->ci->session->unset_userdata('sms_otp');
            return ['valid' => false, 'message' => 'Verification code has expired. Please request a new one.'];
        }

        if ($otp_data['phone'] !== $phone) {
            return ['valid' => false, 'message' => 'Phone number mismatch.'];
        }

        if ($otp_data['code'] !== $code) {
            return ['valid' => false, 'message' => 'Invalid verification code.'];
        }

        // OTP verified - clear it
        $this->ci->session->unset_userdata('sms_otp');
        return ['valid' => true, 'message' => 'Phone verified successfully.'];
    }

    /**
     * Check if Twilio is configured and enabled
     */
    public function is_enabled()
    {
        return $this->enabled;
    }
}
