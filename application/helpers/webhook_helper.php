<?php
defined('BASEPATH') or exit('No direct script access allowed');

/**
 * Webhook Helper — with response and full payload return
 */
if (!function_exists('send_webhook_event')) {

    /**
     * Send a webhook event and return full info including payload.
     *
     * @param string $event_name
     * @param string $object_type
     * @param array  $data
     * @return array
     */
    function send_webhook_event($event_name, $object_type, array $data)
    {
        date_default_timezone_set('Australia/Brisbane');
        $webhook_url = 'https://hook.eu1.make.com/gj62sb6xf56py0jgn0tq7g0t16nu7q9y';

        // ✅ Construct full payload
        $payload = [
            'id'         => 'evt_' . date('Ymd_His') . '_' . bin2hex(random_bytes(4)),
            'event'      => $event_name,
            'timestamp'  => date('Y-m-d H:i A'),
            'object'     => $object_type,
            'data'       => $data
        ];

        $result = [
            'success'  => false,
            'status'   => 0,
            'error'    => '',
            'response' => '',
            'payload'  => $payload // ✅ Return what was sent
        ];

        try {
            $ch = curl_init($webhook_url);
            curl_setopt_array($ch, [
                CURLOPT_POST           => true,
                CURLOPT_HTTPHEADER     => ['Content-Type: application/json'],
                CURLOPT_POSTFIELDS     => json_encode($payload),
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HEADER         => false,
                CURLOPT_TIMEOUT        => 5,
                CURLOPT_CONNECTTIMEOUT => 2,
                CURLOPT_SSL_VERIFYPEER => true,
            ]);

            $response = curl_exec($ch);
            $error    = curl_error($ch);
            $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            $result['success']  = ($status >= 200 && $status < 300 && !$error);
            $result['status']   = $status;
            $result['error']    = $error;
            $result['response'] = $response;

            if ($result['success']) {
                log_message('info', "[WEBHOOK] Sent {$event_name} | Status {$status}");
            } else {
                log_message('error', "[WEBHOOK] Failed {$event_name} | {$error} | Status {$status}");
            }
        } catch (Exception $e) {
            $result['error'] = $e->getMessage();
            log_message('error', "[WEBHOOK] Exception: " . $e->getMessage());
        }

        return $result;
    }
}
