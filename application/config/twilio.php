<?php
defined('BASEPATH') or exit('No direct script access allowed');

/*
|--------------------------------------------------------------------------
| Twilio SMS Configuration
|--------------------------------------------------------------------------
| Set these values in your .env file or directly here for testing.
| For production, use environment variables.
|
| TWILIO_SID=your_account_sid
| TWILIO_TOKEN=your_auth_token
| TWILIO_FROM=+1234567890
|
*/

$config['twilio_sid']   = getenv('TWILIO_SID') ?: '';
$config['twilio_token'] = getenv('TWILIO_TOKEN') ?: '';
$config['twilio_from']  = getenv('TWILIO_FROM') ?: '';
