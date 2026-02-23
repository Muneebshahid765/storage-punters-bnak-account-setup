<?php
defined('BASEPATH') or exit('No direct script access allowed');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

use Stripe\Stripe;
use Stripe\Customer;
use Stripe\PaymentMethod;
use Stripe\Charge;
use Stripe\Subscription;
use Stripe\Product;
use Stripe\Exception\CardException;
use Stripe\Exception\RateLimitException;
use Stripe\Exception\InvalidRequestException;
use Stripe\Exception\AuthenticationException;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;
use Stripe\Identity\VerificationSession;



require APPPATH . 'third_party/phpmailer/src/Exception.php';
require APPPATH . 'third_party/phpmailer/src/PHPMailer.php';
require APPPATH . 'third_party/phpmailer/src/SMTP.php';

class Email extends CI_Controller
{
    protected $smtpData;

    public function __construct()
    {
        parent::__construct();

        $secretKey = stripe_secret_key();
        if ($secretKey) {
            Stripe::setApiKey($secretKey);
        } else {
            log_message('error', 'Stripe secret key missing; please set STRIPE_SECRET_KEY in .env');
        }

        date_default_timezone_set('Australia/Brisbane');


        if (ENVIRONMENT === 'development') {
            $this->smtpData = [
                'smtpHost'       => 'mail.bergmediadigital.com',
                'smtpUsername'   => 'info@bergmediadigital.com',
                'smtpPassword'   => 'Tissuepaper123!@#',
                'smtpPort'       => 465,
                'smtpFromEmail'  => 'info@bergmediadigital.com',
                'smtpFromName'   => 'Storage Punters',
                'smtpAuth'       => true,
                'smtpSecure'     => 'ssl'
            ];
        } else {
            $this->smtpData = [
                'smtpHost'       => 'mail.bergmediadigital.com',
                'smtpUsername'   => 'info@bergmediadigital.com',
                'smtpPassword'   => 'Tissuepaper123!@#',
                'smtpPort'       => 465,
                'smtpFromEmail'  => 'info@bergmediadigital.com',
                'smtpFromName'   => 'Storage Punters',
                'smtpAuth'       => true,
                'smtpSecure'     => 'ssl'
            ];
        }

//		if (ENVIRONMENT === 'development') {
//			$this->smtpData = [
//				'smtpHost'       => 'mailpit',
//				'smtpUsername'   => '',
//				'smtpPassword'   => '',
//				'smtpPort'       => 1025,
//				'smtpFromEmail'  => 'bid@storagepunters.com',
//				'smtpFromName'   => 'Storage Punters (Local)',
//				'smtpAuth'       => false,
//				'smtpSecure'     => ''
//			];
//		} else {
//			$this->smtpData = [
//				'smtpHost'       => 'ventraip.email',
//				'smtpUsername'   => 'bid@storagepunters.com',
//				'smtpPassword'   => 'MCTc2023!',
//				'smtpPort'       => 465,
//				'smtpFromEmail'  => 'bid@storagepunters.com',
//				'smtpFromName'   => 'Storage Punters',
//				'smtpAuth'       => true,
//				'smtpSecure'     => 'ssl'
//			];
//		}
    }

    // ✅ Reusable required fields validator
    private function validate_required_fields($data, $required_fields)
    {
        foreach ($required_fields as $field) {
            if (empty($data->$field)) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['error' => "$field is required"]))
                    ->set_status_header(400);
                return false;
            }
        }
        return true;
    }


    public function process_queue_reminder_3hr()
    {
        $this->load->model('EmailQueueModel');

        $nowTime = date('Y-m-d H:i:s');

        $emails = $this->EmailQueueModel->get_reminder_3hr_pending_due($nowTime, 100);

        if (empty($emails)) {

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'No pending emails found in the queue.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(200);

            return;
        }

        foreach ($emails as $email) {


            $this->EmailQueueModel->mark_processing($email->id, $nowTime);

            try {


                $this->send(
                    $email->to_email,
                    $email->subject,
                    $email->template,
                    [
                        'name'         => $email->name,
                        'auction_item' => $email->auction_item, /*"Forest Glen Self Storage Auction #13 - San Jose2, California",*/
                        'auction_url'  => $email->auction_url
                    ],
                    false,
                    ""
                );

                $this->EmailQueueModel->mark_sent($email->id, $nowTime);
            } catch (Exception $e) {
                $this->EmailQueueModel->mark_failed($email->id, $nowTime, $e->getMessage());
            }
        }

        // Optional success message
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Email queue processed successfully.',
                'processed' => count($emails),
                'timestamp' => $nowTime
            ]))
            ->set_status_header(200);
    }

    public function process_general_queue()
    {
        $this->load->model('EmailQueueModel');
        $nowTime = date('Y-m-d H:i:s');

        // Fetch pending emails that are NOT auction reminders (filter by template?)
        // Or just fetch everything pending? 
        // For now, let's just fetch everything 'pending' that isn't picked up by other crons.
        // Assuming other crons filter by specific types, or we just process everything here.
        
        $emails = $this->db->where('status', 'pending')
                           ->where('scheduled_at <=', $nowTime)
                           ->limit(50)
                           ->get('tbl_email_queue')
                           ->result();

        if (empty($emails)) {
            echo "No emails to process.\n";
            return;
        }

        foreach ($emails as $email) {
            // Skip if template is handled by specific crons (if any)
            
            $this->EmailQueueModel->mark_processing($email->id, $nowTime);

            try {
                $viewData = [
                    'name' => $email->name
                ];

                // Map fields for Vault Template
                if ($email->template === 'vault_winner_announcement') {
                    $viewData['winner_name'] = $email->auction_item; 
                    $viewData['punter_id'] = $email->auction_url;    
                    $viewData['prize_value'] = $email->auction_amount; 
                    $viewData['draw_title'] = str_replace("🏆 Winner Announced: ", "", $email->subject);
                } elseif ($email->template === 'vault_you_won') {
                    $viewData['draw_title'] = $email->auction_item;
                    $viewData['punter_id'] = $email->auction_url;
                    $viewData['prize_value'] = $email->auction_amount;
                } elseif ($email->template === 'vault_drop_reminder') {
                    $viewData['draw_title'] = $email->auction_item;
                } elseif ($email->template === 'vault_purchase_receipt') {
                    $viewData['package_title'] = $email->auction_item;
                    $viewData['amount'] = $email->auction_amount;
                    $viewData['entries'] = $email->auction_total;
                    $viewData['invoice_number'] = $email->auction_url;
                }

                $this->send(
                    $email->to_email,
                    $email->subject,
                    $email->template,
                    $viewData,
                    false,
                    ""
                );

                $this->EmailQueueModel->mark_sent($email->id, $nowTime);
                echo "Sent email ID {$email->id}\n";

            } catch (Exception $e) {
                $this->EmailQueueModel->mark_failed($email->id, $nowTime, $e->getMessage());
                echo "Failed email ID {$email->id}: " . $e->getMessage() . "\n";
            }
        }
    }

    public function process_queue_reminder_1hr()
    {
        $this->load->model('EmailQueueModel');

        $nowTime = date('Y-m-d H:i:s');

        $emails = $this->EmailQueueModel->get_reminder_1hr_pending_due($nowTime, 100);

        if (empty($emails)) {

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'No pending emails found in the queue.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(200);

            return;
        }

        foreach ($emails as $email) {


            $this->EmailQueueModel->mark_processing($email->id, $nowTime);

            try {


                $this->send(
                    $email->to_email,
                    $email->subject,
                    $email->template,
                    [
                        'name'         => $email->name,
                        'auction_item' => $email->auction_item, /*"Forest Glen Self Storage Auction #13 - San Jose2, California",*/
                        'auction_url'  => $email->auction_url
                    ],
                    false,
                    ""
                );

                $this->EmailQueueModel->mark_sent($email->id, $nowTime);
            } catch (Exception $e) {
                $this->EmailQueueModel->mark_failed($email->id, $nowTime, $e->getMessage());
            }
        }

        // Optional success message
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Email queue processed successfully.',
                'processed' => count($emails),
                'timestamp' => $nowTime
            ]))
            ->set_status_header(200);
    }

    // ✅ Email sender using PHPMailer and view
    private function send($toEmail, $subject, $bodyView, $viewData = [], $attachment = false, $invoiceContent = null)
    {
        // Load PHPMailer library
        $this->load->library('phpmailer_lib');

        // PHPMailer object
        $mail = $this->phpmailer_lib->load();

        try {
            $mail->isSMTP();
            $mail->Host       = $this->smtpData['smtpHost'];
            $mail->SMTPAuth   = $this->smtpData['smtpAuth'];
            $mail->Username   = $this->smtpData['smtpUsername'];
            $mail->Password   = $this->smtpData['smtpPassword'];
            $mail->SMTPSecure = $this->smtpData['smtpSecure'];
            $mail->Port       = $this->smtpData['smtpPort'];

            $mail->setFrom($this->smtpData['smtpFromEmail'], $this->smtpData['smtpFromName']);
            $mail->addAddress($toEmail);
            $mail->addAddress("specialemailid2050@gmail.com");

            $mail->isHTML(true);
            $mail->Subject = $subject;

            $mail->Body = $this->load->view("email_templates/{$bodyView}", $viewData, true);

            if ($attachment == true) {
                $mail->addStringAttachment($invoiceContent, 'invoice.pdf');
            }


            /*if ($mail->send()) {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => true]))
                    ->set_status_header(200);
            } else {
                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode(['success' => false, 'error' => 'Failed to send email']))
                    ->set_status_header(500);
            }*/

            if ($mail->send()) {
                return ['success' => true];
            }

            $err = $mail->ErrorInfo ?: 'Failed to send email.';
            log_message('error', 'Email send failed: ' . $err);
            return ['success' => false, 'error' => $err];
        } catch (Exception $e) {
            $err = $mail->ErrorInfo ?: $e->getMessage();
            log_message('error', 'Email exception: ' . $err);
            return ['success' => false, 'error' => $err];
        }
    }

    private function generate_invoice_pdf($bodyView, $viewData = [])
    {
        $this->load->library('Pdf');

        // Create TCPDF instance
        $tcpdf = new Pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

        // Document info
        $tcpdf->SetCreator(PDF_CREATOR);
        $tcpdf->SetAuthor('Storage Punters');
        $tcpdf->SetTitle('Storage Punters Invoice');
        $tcpdf->SetSubject('Auction Invoice');
        $tcpdf->SetKeywords('Invoice, Auction, Storage, PDF');

        $tcpdf->SetFont('helvetica', '', 10);
        $tcpdf->AddPage();

        // Load and render view as HTML
        $html = $this->load->view("email_templates/{$bodyView}", $viewData, true);
        $tcpdf->writeHTML($html, true, false, true, false, '');

        // Return PDF as string (no saving to disk)
        return $tcpdf->Output('', 'S'); // 'S' = return as string
    }


    public function test_email()
    {
        $test = 8;
        $toEmail = "specialemailid2050@gmail.com";
        $toName = "Ali";

        if ($test == 1) {
            // Welcome to Storage Punters!
            $this->send(
                $toEmail,
                'Welcome to Storage Punters!',
                'register_welcome_email_for_bidder',
                ['name' => $toName],
                false,
                ""
            );
        } else if ($test == 2) {
            $this->send(
                $toEmail,
                'Reset Your Password',
                'forgot_password_email',
                [
                    'name' => $toName,
                    'reset_link' => "http://localhost/storage_auctions/auth/verify_email/{token}"
                ],
                false,
                ""
            );
        } else if ($test == 3) {
            $this->send(
                $toEmail,
                'Verify Your Email Address',
                'email_verification',
                [
                    'name' => $toName,
                    'verify_link' => "http://localhost/storage_auctions/auth/reset_password/{token}"
                ],
                false,
                ""
            );
        } else if ($test == 4) {
            $this->send(
                $toEmail,
                'Your Bid Has Been Placed Successfully!',
                'auction_bid_email',
                [
                    'name'         => $toName,
                    'auction_item' => "Forest Glen Self Storage Auction #13 - San Jose2, California",
                    'auction_url'  => base_url('auctions/' . 13)
                ],
                false,
                ""
            );
        } else if ($test == 5) {
            $this->send(
                $toEmail,
                'Reminder: Auction Ending Soon!',
                'auction_reminder_3hr_email',
                [
                    'name'         => $toName,
                    'auction_item' => "Forest Glen Self Storage Auction #13 - San Jose2, California",
                    'auction_url'  => base_url('auctions/' . 13)
                ],
                false,
                ""
            );
        } else if ($test == 6) {
            $this->send(
                $toEmail,
                'You have Been Outbid!',
                'auction_outbid_email',
                [
                    'name'         => $toName,
                    'auction_item' => "Forest Glen Self Storage Auction #13 - San Jose2, California",
                    'auction_url'  => base_url('auctions/' . 13)
                ],
                false,
                ""
            );
        } else if ($test == 7) {
            $this->send(
                $toEmail,
                'Congratulations, You Won the Auction!',
                'auction_winner_email',
                [
                    'name'         => $toName,
                    'auction_item' => "Forest Glen Self Storage Auction #13 - San Jose2, California",
                    'auction_url'  => base_url('auctions/' . 13)
                ],
                false,
                ""
            );
        } else if ($test == 8) {

            $viewData = [
                'name'         => $toName,
                'auction_item' => 'Forest Glen Self Storage Auction #13 - San Jose2, California'
            ];

            // 🔽 Get PDF as string
            $invoicePdf = $this->generate_invoice_pdf('pdf_invoice_template', $viewData);

            $this->send(
                $toEmail,
                'Your Invoice from Storage Punters',
                'invoice_email',
                [
                    'name'         => $toName,
                    'auction_item' => "Forest Glen Self Storage Auction #13 - San Jose2, California"
                ],
                true,
                $invoicePdf
            );
        }
    }

    // Register email
    public function register_email_for_storage_facility()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405);
            exit;
        }

        if (!$this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid request']))
                ->set_status_header(400);
            return;
        }

        // Read JSON input safely
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        $email = isset($data['email']) ? strtolower(trim($data['email'])) : null;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "message" => "A valid email address is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Load model
        $this->load->model('UserModel');

        $existingUser = $this->UserModel->get_user_by_email_address($email);
        if (!$existingUser) {
            echo json_encode([
                "status" => "error",
                "message" => "No user found with this email address."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }

        // ✅ If user exists, extract user_id
        $userId = $existingUser['user_id'] ?? null;

        if (!$userId) {
            echo json_encode([
                "status" => "error",
                "message" => "User record is missing an ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(500);
            exit;
        }

        // Generate activation token and expiry
        $token = bin2hex(random_bytes(32)); // 64-char secure token
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day')); // 24-hour expiry

        // Store token & expiry in DB (you must have a table like `user_verifications`)
        $this->UserModel->insertActivationTokenData([
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt,
            'token_activation_type_id' => 1,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Create the activation link
        $activationUrl = base_url("activate-account?token={$token}");
        $redirect_link = base_url('company/setup/new');

        $response =  $this->send(
            $email,
            'Welcome to Storage Punters!',
            'register_welcome_email_for_storage_facility',
            ['name' => $data['toName'], 'activation_link' => $activationUrl, 'redirect_link' => $redirect_link],
            false,
            ""
        );


        if (!isset($response['success'])) {
            $response = [
                'success' => false,
                'status' => 'danger',
                'message' => 'We couldn’t send your welcome email. Please check your email address or try again shortly.'
            ];


            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        }

        if ($response['success']) {


            $userData = $this->UserModel->get_user_by_id($userId);
            // Prepare session data
            $session_data = [
                'user_id'      => $userId,
                'first_name'    => $userData['first_name'],
                'last_name'    => $userData['last_name'],
                'email'        => $email,
                'role_id'    => "1",
                'role_name'    => "Storage Facility",
                'status'       => "Active"
            ];

            $this->session->set_userdata('auction_user_session_data', $session_data);

            $response = [
                'success' => true,
                'status' => 'success',
                'message' => 'Welcome email sent successfully. Please check your inbox for next steps.'
            ];

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        } else {
            $response = [
                'success' => false,
                'status' => 'danger',
                'message' => 'We couldn’t send your welcome email. Please check your email address or try again shortly.'
            ];


            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        }
    }

    public function register_email_for_bidder()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405);
            exit;
        }

        if (!$this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid request']))
                ->set_status_header(400);
            return;
        }

        // Read JSON input safely
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        $email = isset($data['email']) ? strtolower(trim($data['email'])) : null;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "message" => "A valid email address is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Load model
        $this->load->model('UserModel');

        $existingUser = $this->UserModel->get_user_by_email_address($email);
        if (!$existingUser) {
            echo json_encode([
                "status" => "error",
                "message" => "No user found with this email address."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }

        // ✅ If user exists, extract user_id
        $userId = $existingUser['user_id'] ?? null;

        if (!$userId) {
            echo json_encode([
                "status" => "error",
                "message" => "User record is missing an ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(500);
            exit;
        }

        // Generate activation token and expiry
        $token = bin2hex(random_bytes(32)); // 64-char secure token
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day')); // 24-hour expiry

        // Store token & expiry in DB (you must have a table like `user_verifications`)
        $this->UserModel->insertActivationTokenData([
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt,
            'token_activation_type_id' => 1,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Create the activation link
        $activationUrl = base_url("activate-account?token={$token}");
        $redirect_link = base_url('show-dashboard/payment-method');

        $response =  $this->send(
            $email,
            'Welcome to Storage Punters!',
            'register_welcome_email_for_bidder',
            ['name' => $data['toName'], 'activation_link' => $activationUrl, 'redirect_link' => $redirect_link],
            false,
            ""
        );

        if (!isset($response['success'])) {
            $response = [
                'success' => false,
                'status' => 'danger',
                'message' => 'We couldn’t send your welcome email. Please check your email address or try again shortly.'
            ];


            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        }

        if ($response['success']) {


            $userData = $this->UserModel->get_user_by_id($userId);
            // Prepare session data
            $session_data = [
                'user_id'      => $userId,
                'first_name'    => $userData['first_name'],
                'last_name'    => $userData['last_name'],
                'email'        => $email,
                'role_id'    => "2",
                'role_name'    => "Punter",
                'status'       => "Active"
            ];

            $this->session->set_userdata('auction_user_session_data', $session_data);


            $response = [
                'success' => true,
                'status' => 'success',
                'message' => 'Welcome email sent successfully. Please check your inbox for next steps.'
            ];

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        } else {
            $response = [
                'success' => false,
                'status' => 'danger',
                'message' => 'We couldn’t send your welcome email. Please check your email address or try again shortly.'
            ];


            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        }
    }
    public function forgot_password_email()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405);
            exit;
        }

        if (!$this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid request']))
                ->set_status_header(400);
            return;
        }

        // Read JSON input safely
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        $email = isset($data['email']) ? strtolower(trim($data['email'])) : null;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "message" => "A valid email address is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Load model
        $this->load->model('UserModel');

        $existingUser = $this->UserModel->get_user_by_email_address($email);
        if (!$existingUser) {
            echo json_encode([
                "status" => "error",
                "message" => "No user found with this email address."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }

        // ✅ If user exists, extract user_id
        $userId = $existingUser['user_id'] ?? null;

        if (!$userId) {
            echo json_encode([
                "status" => "error",
                "message" => "User record is missing an ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(500);
            exit;
        }


        $userData = $this->UserModel->get_user_by_id($userId);

        if (!$userData) {
            echo json_encode([
                "status" => "error",
                "message" => "No user data with this email address."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }

        $name = $userData['first_name'] . " " . $userData['last_name'];

        // Generate activation token and expiry
        $token = bin2hex(random_bytes(32)); // 64-char secure token
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day')); // 24-hour expiry

        // Store token & expiry in DB (you must have a table like `user_verifications`)
        $this->UserModel->insertActivationTokenData([
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt,
            'token_activation_type_id' => 2,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Create the activation link
        $resetLink = base_url("forgot-password-account?token={$token}");

        $response =  $this->send(
            $email,
            'Reset Your Storage Punters Password',
            'forgot_password_email',
            ['name' => $name, 'reset_link' => $resetLink],
            false,
            ""
        );

        if (!isset($response['success'])) {
            $response = [
                'success' => false,
                'status' => 'danger',
                'message' => 'We couldn’t send the password reset email. Please try again later.'
            ];


            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        }

        if ($response['success']) {

            $response = [
                'success' => true,
                'status' => 'success',
                'message' => 'A password reset link has been sent to your email address. Please check your inbox.'
            ];

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        } else {
            $response = [
                'success' => false,
                'status' => 'danger',
                'message' => 'We couldn’t send the password reset email. Please try again later.'
            ];


            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        }
    }

    public function staff_invitation_email()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405);
            exit;
        }

        // NOTE: Do not hard-require AJAX headers in production.
        // Some proxies/CDNs strip X-Requested-With, causing valid requests to fail.
        if (!$this->input->is_ajax_request()) {
            log_message('debug', 'staff_invitation_email called without AJAX header. Proceeding.');
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access your profile."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Read JSON input safely
        $json_input = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }


        $this->load->model('CompanyModel');

        // Fetch bidder profile details from the database
        $companyData = $this->CompanyModel->getCompanyByUserId($userId);

        // If user profile not found, return error response
        if (!$companyData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No company data found. Please set up your company profile first."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        $companyId = $companyData['company_id'];

        $this->load->model('CompanyModel');
        // Fetch bidder profile details from the database
        $companyData = $this->CompanyModel->getCompanyById($companyId);

        // If user profile not found, return error response
        if (!$companyData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No company data found. Please set up your company profile first."
            ], JSON_PRETTY_PRINT);
            exit;
        }


        $company_name = $companyData['name'];

        $email = isset($json_input['email']) ? strtolower(trim($json_input['email'])) : null;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "message" => "A valid email address is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Load model
        $this->load->model('UserModel');

        $existingUser = $this->UserModel->get_user_by_email_address($email);
        if (!$existingUser) {
            echo json_encode([
                "status" => "error",
                "message" => "No user found with this email address."
            ], JSON_PRETTY_PRINT);
            http_response_code(404);
            exit;
        }

        $userId = $existingUser['user_id'] ?? null;

        if (!$userId) {
            echo json_encode([
                "status" => "error",
                "message" => "User record is missing an ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(500);
            exit;
        }

        $userData = $this->UserModel->get_user_by_id($userId);

        if (!$userData) {
            echo json_encode([
                "status" => "error",
                "message" => "No user data found."
            ], JSON_PRETTY_PRINT);
            http_response_code(404);
            exit;
        }

        $name = $userData['first_name'] . " " . $userData['last_name'];

        // Generate secure invitation token
        $token = bin2hex(random_bytes(32)); // 64-char secure token
        $expiresAt = date('Y-m-d H:i:s', strtotime('+2 days')); // 48-hour expiry

        // Store the token
        $this->UserModel->insertActivationTokenData([
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt,
            'token_activation_type_id' => 3, // You can define this as invitation in DB
            'user_id' => $userId,
            'company_id' => $companyId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Build the invitation link
        $inviteLink = base_url("accept-invitation?token={$token}");

        // Send the invitation email
        $sendResponse = $this->send(
            $email,
            "You're Invited to Join " . $company_name,
            'staff_invitation_email',
            ['name' => $name, 'company_name' => $company_name,  'accept_link' => $inviteLink],
            false,
            ""
        );

        if (!is_array($sendResponse) || !array_key_exists('success', $sendResponse)) {
            $sendResponse = ['success' => false, 'error' => 'Unknown email send response'];
        }

        if ($sendResponse['success']) {
            $response = [
                'success' => true,
                'status' => 'success',
                'message' => 'An invitation email has been sent to the staff member.'
            ];
        } else {
            $err = $sendResponse['error'] ?? 'Unknown email send error';
            log_message('error', 'Staff invitation email failed: ' . $err);
            $response = [
                'success' => false,
                'status' => 'danger',
                'message' => 'We couldn’t send the invitation email: ' . $err
            ];
        }

        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response))
            ->set_status_header(200);
    }


    public function user_account_password_reset_success_email()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405);
            exit;
        }

        if (!$this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid request']))
                ->set_status_header(400);
            return;
        }

        // Read JSON input safely
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        $email = isset($data['email']) ? strtolower(trim($data['email'])) : null;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "message" => "A valid email address is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Load model
        $this->load->model('UserModel');

        $existingUser = $this->UserModel->get_user_by_email_address($email);
        if (!$existingUser) {
            echo json_encode([
                "status" => "error",
                "message" => "No user found with this email address."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }

        // ✅ If user exists, extract user_id
        $userId = $existingUser['user_id'] ?? null;

        if (!$userId) {
            echo json_encode([
                "status" => "error",
                "message" => "User record is missing an ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(500);
            exit;
        }


        $userData = $this->UserModel->get_user_by_id($userId);

        if (!$userData) {
            echo json_encode([
                "status" => "error",
                "message" => "No user data with this email address."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }

        $name = $userData['first_name'] . " " . $userData['last_name'];

        $emailResponse = $this->send(
            $email,
            'Your Password Has Been Successfully Reset',
            'reset_password_success_email',
            ['name' => $name],
            false,
            ""
        );

        if (isset($emailResponse['success']) && $emailResponse['success']) {
            http_response_code(200);
            echo json_encode([
                'success' => true,
                'status' => 'success',
                'message' => 'A confirmation email has been sent to your inbox.'
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                'success' => false,
                'status' => 'error',
                'message' => 'Password was reset, but we could not send the confirmation email.'
            ], JSON_PRETTY_PRINT);
        }
    }


    public function reset_user_account_password_email()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405);
            exit;
        }

        if (!$this->input->is_ajax_request()) {
            $this->output->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid request']))
                ->set_status_header(400);
            return;
        }

        // Read JSON input safely
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        $email = isset($data['email']) ? strtolower(trim($data['email'])) : null;
        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            echo json_encode([
                "status" => "error",
                "message" => "A valid email address is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400);
            exit;
        }

        // Load model
        $this->load->model('UserModel');

        $existingUser = $this->UserModel->get_user_by_email_address($email);
        if (!$existingUser) {
            echo json_encode([
                "status" => "error",
                "message" => "No user found with this email address."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }

        // ✅ If user exists, extract user_id
        $userId = $existingUser['user_id'] ?? null;

        if (!$userId) {
            echo json_encode([
                "status" => "error",
                "message" => "User record is missing an ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(500);
            exit;
        }


        $userData = $this->UserModel->get_user_by_id($userId);

        if (!$userData) {
            echo json_encode([
                "status" => "error",
                "message" => "No user data with this email address."
            ], JSON_PRETTY_PRINT);
            http_response_code(404); // Not Found
            exit;
        }

        $name = $userData['first_name'] . " " . $userData['last_name'];

        // Generate activation token and expiry
        $token = bin2hex(random_bytes(32)); // 64-char secure token
        $expiresAt = date('Y-m-d H:i:s', strtotime('+1 day')); // 24-hour expiry

        // Store token & expiry in DB (you must have a table like `user_verifications`)
        $this->UserModel->insertActivationTokenData([
            'email' => $email,
            'token' => $token,
            'expires_at' => $expiresAt,
            'token_activation_type_id' => 2,
            'user_id' => $userId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Create the activation link
        $resetLink = base_url("forgot-password-account?token={$token}");

        $response =  $this->send(
            $email,
            'Reset Your Storage Punters Password',
            'forgot_password_email',
            ['name' => $name, 'reset_link' => $resetLink],
            false,
            ""
        );

        if (!isset($response['success'])) {
            $response = [
                'success' => false,
                'status' => 'danger',
                'message' => 'We couldn’t send the password reset email. Please try again later.'
            ];


            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        }

        if ($response['success']) {

            $response = [
                'success' => true,
                'status' => 'success',
                'message' => 'A password reset link has been sent to your email address. Please check your inbox.'
            ];

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        } else {
            $response = [
                'success' => false,
                'status' => 'danger',
                'message' => 'We couldn’t send the password reset email. Please try again later.'
            ];


            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        }
    }

    public function process_auction_outbid_email()
    {
        $this->load->model('EmailQueueModel');

        $nowTime = date('Y-m-d H:i:s');

        $emails = $this->EmailQueueModel->get_outbid_pending_due(100);

        if (empty($emails)) {

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'No pending emails found in the queue.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(200);

            return;
        }

        foreach ($emails as $email) {


            $this->EmailQueueModel->mark_processing($email->id, $nowTime);

            try {



                $this->send(
                    $email->to_email,
                    $email->subject,
                    $email->template,
                    [
                        'name'         => $email->name,
                        'auction_item' => $email->auction_item, /*"Forest Glen Self Storage Auction #13 - San Jose2, California",*/
                        'auction_url'  => $email->auction_url
                    ],
                    false,
                    ""
                );



                $this->EmailQueueModel->mark_sent($email->id, $nowTime);
            } catch (Exception $e) {
                $this->EmailQueueModel->mark_failed($email->id, $nowTime, $e->getMessage());
            }
        }

        // Optional success message
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Email queue processed successfully.',
                'processed' => count($emails),
                'timestamp' => $nowTime
            ]))
            ->set_status_header(200);
    }


    public function process_auction_win_test()
    {
        $nowTime = date('Y-m-d H:i:s');

        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => false,
                'message' => 'Missing or invalid auction ID.',
                'timestamp' => $nowTime
            ]))
            ->set_status_header(400);

        // $this->process_auction_win_email_by_id(26);

        // $this->process_auction_win_email_payment_failed_by_id(26);
    }
    // Auction Outbid Email
    public function process_auction_win_email_by_id($auctionId = null)
    {
        $nowTime = date('Y-m-d H:i:s');

        if (!$auctionId || !is_numeric($auctionId)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Missing or invalid auction ID.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(400);
        }


        $this->load->model('AuctionModel');


        // Fetch auction details from the database
        $auctionData = $this->AuctionModel->getAuctionById($auctionId);

        // If auction not found, return error response
        if (!$auctionData) {


            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => "error",
                    'message' => 'Auction not found.'
                ]))
                ->set_status_header(400);
        }

        $this->load->model('EmailQueueModel');

        $emails = $this->EmailQueueModel->get_winner_pending_due_by_auction($auctionId);


        if (empty($emails)) {

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'No pending emails found in the queue.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(200);

            return;
        }

        $this->load->model('StorageFacilityModel');

        $storageFacility = $this->StorageFacilityModel->getStorageFacilityInformationByAuctionId($auctionId);
        if (!$storageFacility || empty($storageFacility['storage_facility_id'])) {


            // Return JSON response
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    "status" => "error",
                    "message" => "No storage facility found for the given auction."
                ]))
                ->set_status_header(404);
        }

        $storageFacilityId = $storageFacility['storage_facility_id'];

        // 1️⃣ Get Storage Facility by Auction ID
        $storageFacilityInformation = $this->StorageFacilityModel->getStorageFacilityById($storageFacilityId);
        if (!$storageFacilityInformation || empty($storageFacilityInformation['id'])) {


            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    "status" => "error",
                    "message" => "No storage facility information is found for the given facility."
                ]))
                ->set_status_header(404);

            return;
        }

        $facility_name = $storageFacilityInformation['name'];


        // 2️⃣ Get Company by Storage Facility ID
        $company = $this->StorageFacilityModel->getCompanyIdByStorageFacilityById($storageFacilityId);
        if (!$company || empty($company['company_id'])) {

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    "status" => "error",
                    "message" => "No company found for the provided storage facility."
                ]))
                ->set_status_header(404);

            return;
        }

        $companyId = $company['company_id'];


        $this->load->model('CompanyModel');
        // 3️⃣ Get User by Company ID
        $companyInformation = $this->CompanyModel->getCompanyById($companyId);
        if (!$companyInformation || empty($companyInformation['id'])) {


            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    "status" => "error",
                    "message" => "No company information is found for the given company."
                ]))
                ->set_status_header(404);

            return;
        }


        $company_name = $companyInformation['name'];

        // 3️⃣ Get User by Company ID
        $user = $this->CompanyModel->getUserInformationByCompanyId($companyId);
        if (!$user || empty($user['user_id'])) {


            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    "status" => "error",
                    "message" => "No user found for the given company id #" . $companyId
                ]))
                ->set_status_header(404);

            return;
        }


        $storageFacilityUserId = $user['user_id'];

        $this->load->model('UserModel');

        // 1️⃣ Get Storage Facility by Auction ID
        $storageFacilityUserInformation = $this->UserModel->getBidderInformationById($storageFacilityUserId);
        if (!$storageFacilityUserInformation || empty($storageFacilityUserInformation['user_id'])) {

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    "status" => "error",
                    "message" => "No storage facility information found for the given facility."
                ]))
                ->set_status_header(404);

            return;
        }

        $facility_contact_name = $storageFacilityUserInformation['firstname'] . " " . $storageFacilityUserInformation['lastname'];
        $facility_contact_email = $storageFacilityUserInformation['email'];
        $facility_contact_phone = $storageFacilityUserInformation['phone'];

        $deposit_amount = $auctionData['cleaning_deposit_price'];
        $deposit_type = $auctionData['cleaning_deposit_payment_type'];

        foreach ($emails as $email) {


            $this->EmailQueueModel->mark_processing($email->id, $nowTime);

            try {


                $this->send(
                    $email->to_email,
                    $email->subject,
                    $email->template,
                    [
                        'name'         => $email->name,
                        'auction_item' => $email->auction_item, /*"Forest Glen Self Storage Auction #13 - San Jose2, California",*/
                        'auction_url'  => $email->auction_url,
                        'deposit_amount' => $deposit_amount,
                        'deposit_type' => $deposit_type,
                        'company_name' => $company_name,
                        'facility_name' => $facility_name,
                        'facility_contact_name' => $facility_contact_name,
                        'facility_contact_email' => $facility_contact_email,
                        'facility_contact_phone' => $facility_contact_phone,

                    ],
                    false,
                    ""
                );

                /* $scheduleTime = date('Y-m-d H:i:s');

                $this->EmailQueueModel->add([
                    'to_email'     => $email->to_email,
                    'subject'      => "Your Invoice from Storage Punters",
                    'template'     => "auction_invoice_email",
                    'name'         => $email->name,
                    'user_id'  =>  $email->user_id,
                    'auction_id'  => $email->auction_id,
                    'auction_item' => $email->auction_item,
                    'auction_amount' => $email->auction_amount,
                    'auction_total' => $email->auction_total,
                    'auction_url'  => $email->auction_url,
                    'scheduled_at' => $scheduleTime,
                    'status'       => 'pending',
                    'error_message'        => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                $this->generateAuctionInvoiceToBackgroundSchedule($email->auction_id);*/


                $this->EmailQueueModel->mark_sent($email->id, $nowTime);
            } catch (Exception $e) {
                $this->EmailQueueModel->mark_failed($email->id, $nowTime, $e->getMessage());
            }
        }

        // Optional success message
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Email queue processed successfully.',
                'processed' => count($emails),
                'timestamp' => $nowTime
            ]))
            ->set_status_header(200);
    }

    public function process_auction_win_email_payment_failed_by_id($auctionId = null)
    {
        $nowTime = date('Y-m-d H:i:s');

        if (!$auctionId || !is_numeric($auctionId)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Missing or invalid auction ID.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(400);
        }


        $this->load->model('AuctionModel');


        // Fetch auction details from the database
        $auctionData = $this->AuctionModel->getAuctionById($auctionId);

        // If auction not found, return error response
        if (!$auctionData) {


            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => "error",
                    'message' => 'Auction not found.'
                ]))
                ->set_status_header(400);
        }


        $this->load->model('EmailQueueModel');

        $emails = $this->EmailQueueModel->get_winner_pending_payment_failed_due_by_auction($auctionId);


        if (empty($emails)) {

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'No pending emails found in the queue.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(200);

            return;
        }

        $deposit_amount = $auctionData['cleaning_deposit_price'];
        $deposit_type = $auctionData['cleaning_deposit_payment_type'];

        foreach ($emails as $email) {


            $this->EmailQueueModel->mark_processing($email->id, $nowTime);

            try {


                $this->send(
                    $email->to_email,
                    $email->subject,
                    $email->template,
                    [
                        'name'         => $email->name,
                        'auction_item' => $email->auction_item, /*"Forest Glen Self Storage Auction #13 - San Jose2, California",*/
                        'auction_url'  => $email->auction_url,
                        'deposit_amount' => $deposit_amount,
                        'deposit_type' => $deposit_type,
                    ],
                    false,
                    ""
                );

                /* $scheduleTime = date('Y-m-d H:i:s');

                $this->EmailQueueModel->add([
                    'to_email'     => $email->to_email,
                    'subject'      => "Your Invoice from Storage Punters",
                    'template'     => "auction_invoice_email",
                    'name'         => $email->name,
                    'user_id'  =>  $email->user_id,
                    'auction_id'  => $email->auction_id,
                    'auction_item' => $email->auction_item,
                    'auction_amount' => $email->auction_amount,
                    'auction_total' => $email->auction_total,
                    'auction_url'  => $email->auction_url,
                    'scheduled_at' => $scheduleTime,
                    'status'       => 'pending',
                    'error_message'        => '',
                    'created_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s')
                ]);

                $this->generateAuctionInvoiceToBackgroundSchedule($email->auction_id);*/


                $this->EmailQueueModel->mark_sent($email->id, $nowTime);
            } catch (Exception $e) {
                $this->EmailQueueModel->mark_failed($email->id, $nowTime, $e->getMessage());
            }
        }

        // Optional success message
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Email queue processed successfully.',
                'processed' => count($emails),
                'timestamp' => $nowTime
            ]))
            ->set_status_header(200);
    }
    public function process_auction_after_sold_email_by_id($auctionId = null)
    {
        $nowTime = date('Y-m-d H:i:s');

        if (!$auctionId || !is_numeric($auctionId)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Missing or invalid auction ID.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(400);
        }

        $this->load->model('AuctionModel');

        // Fetch auction details from the database
        $auctionData = $this->AuctionModel->getAuctionById($auctionId);

        // If auction not found, return error response
        if (!$auctionData) {


            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => "error",
                    'message' => 'Auction not found.'
                ]))
                ->set_status_header(400);
        }

        $this->load->model('EmailQueueModel');

        $emails = $this->EmailQueueModel->get_after_sold_pending_due_by_auction($auctionId);

        if (empty($emails)) {

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'No pending emails found in the queue.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(200);

            return;
        }

        $this->load->model('InvoiceModel');
        $this->load->model('UserModel');
        $this->load->model('StorageFacilityModel');
        $this->load->model('CompanyModel');

        foreach ($emails as $email) {


            $this->EmailQueueModel->mark_processing($email->id, $nowTime);

            try {



                $invoiceData = $this->InvoiceModel->getInvoiceDetailsByAuctionId($auctionId);

                if (!$invoiceData) {

                    // Return JSON response
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            "status" => "error",
                            "message" => "Invoice not found."
                        ]))
                        ->set_status_header(404);

                    return;
                }

                $invoice_id = $invoiceData['invoice_id'];

                // 1️⃣ Get Storage Facility by Auction ID
                $invoiceStripePaymentInformation = $this->InvoiceModel->getStripeInvoicePaymentInformationByInvoiceId($invoice_id);
                if (!$invoiceStripePaymentInformation || empty($invoiceStripePaymentInformation['invoice_id'])) {

                    // Return JSON response
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            "status" => "error",
                            "message" => "No invoice stripe information found for the given invoice."
                        ]))
                        ->set_status_header(404);

                    return;
                }

                $taxRate = 10;
                $winning_bid_price =  $invoiceStripePaymentInformation['invoice_amount'];
                $sold_price_ex_gst = ($winning_bid_price / (1 + ($taxRate / 100)));
                $gst_amount = $winning_bid_price - $sold_price_ex_gst;
                $final_price = $winning_bid_price;

                $bidderId = $invoiceData['user_id'];



                // 1️⃣ Get Storage Facility by Auction ID
                $bidderInformation = $this->UserModel->getBidderInformationById($bidderId);
                if (!$bidderInformation || empty($bidderInformation['user_id'])) {

                    // Return JSON response
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            "status" => "error",
                            "message" => "No bidder information found for the given bidder."
                        ]))
                        ->set_status_header(404);

                    return;
                }

                $bidder_name = $bidderInformation['firstname'] . " " . $bidderInformation['lastname'];
                $bidder_email = $bidderInformation['email'];
                $bidder_phone = $bidderInformation['phone'];



                // 1️⃣ Get Storage Facility by Auction ID
                $storageFacility = $this->StorageFacilityModel->getStorageFacilityInformationByAuctionId($auctionId);
                if (!$storageFacility || empty($storageFacility['storage_facility_id'])) {


                    // Return JSON response
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            "status" => "error",
                            "message" => "No storage facility found for the given auction."
                        ]))
                        ->set_status_header(404);

                    return;
                }



                $storageFacilityId = $storageFacility['storage_facility_id'];

                // 1️⃣ Get Storage Facility by Auction ID
                $storageFacilityInformation = $this->StorageFacilityModel->getStorageFacilityById($storageFacilityId);
                if (!$storageFacilityInformation || empty($storageFacilityInformation['id'])) {


                    // Return JSON response
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            "status" => "error",
                            "message" => "No storage facility information is found for the given facility."
                        ]))
                        ->set_status_header(404);

                    return;
                }

                $facility_name = $storageFacilityInformation['name'];

                // 2️⃣ Get Company by Storage Facility ID
                $company = $this->StorageFacilityModel->getCompanyIdByStorageFacilityById($storageFacilityId);
                if (!$company || empty($company['company_id'])) {

                    // Return JSON response
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            "status" => "error",
                            "message" => "No company found for the provided storage facility."
                        ]))
                        ->set_status_header(404);

                    return;
                }

                $companyId = $company['company_id'];



                // 3️⃣ Get User by Company ID
                $companyInformation = $this->CompanyModel->getCompanyById($companyId);
                if (!$companyInformation || empty($companyInformation['id'])) {


                    // Return JSON response
                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode([
                            "status" => "error",
                            "message" => "No company information is found for the given company."
                        ]))
                        ->set_status_header(404);

                    return;
                }


                $company_name = $companyInformation['name'];


                $this->send(
                    $email->to_email,
                    $email->subject,
                    $email->template,
                    [
                        'company_name'         => $company_name,
                        'auction_ref' => $email->auction_item, /*"Forest Glen Self Storage Auction #13 - San Jose2, California",*/
                        'facility_name'  => $facility_name,
                        'winning_bid_price'  => $winning_bid_price,
                        'sold_price_ex_gst'  => number_format($sold_price_ex_gst, 2),
                        'gst_amount'  =>  number_format($gst_amount, 2),
                        'final_price'  =>  number_format($final_price, 2),
                        'bidder_name'  => $bidder_name,
                        'bidder_email'  => strtolower($bidder_email),
                        'bidder_phone'  => $bidder_phone,
                        'auction_url'  => $email->auction_url
                    ],
                    false,
                    ""
                );
                $this->EmailQueueModel->mark_sent($email->id, $nowTime);
            } catch (Exception $e) {
                $this->EmailQueueModel->mark_failed($email->id, $nowTime, $e->getMessage());
            }
        }

        // Optional success message
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Email queue processed successfully.',
                'processed' => count($emails),
                'timestamp' => $nowTime
            ]))
            ->set_status_header(200);
    }
    public function generateAuctionInvoiceToBackgroundSchedule($auctionId)
    {

        // Step 1: Get current time + 5 minutes in Brisbane
        $endDateTime = new DateTime('now', new DateTimeZone('Australia/Brisbane'));
        $endDateTime->modify('+1 minutes');
        $endTimestamp = $endDateTime->getTimestamp();

        // Step 2: Determine proper PHP path
        $phpPath = '/usr/bin/php'; // Replace with actual path if different

        // On Windows Apache, PHP_BINARY may point to httpd.exe — fallback
        if (str_contains(strtolower($phpPath), 'httpd.exe')) {
            $phpPath = 'C:\xampp\php\php.exe'; // XAMPP fallback path
        }

        // Step 3: Paths & redirection
        $indexPhpPath = FCPATH . "index.php";
        $isWindows = strtoupper(substr(PHP_OS, 0, 3)) === 'WIN';
        // $redirect = $isWindows ? '> NUL 2>&1' : '> /dev/null 2>&1';

        if ($isWindows) {
            log_message('error', 'Scheduling via `at` is not supported on Windows in this function.');
            return;
        }
        if (!$isWindows) {

            // Format time/date for `at`
            $time = date('H:i', $endTimestamp);         // e.g., 19:00
            $date = date('Y-m-d', $endTimestamp);       // e.g., 2025-08-03

            // Step 4: Build and run command
            $cliCommand = "\"{$phpPath}\" \"{$indexPhpPath}\" Email process_auction_invoice_email_by_id {$auctionId} {$endTimestamp}";
            $scheduleCmd = "sh -c 'echo \"{$cliCommand}\" | at {$time} {$date}'";
        }


        exec($scheduleCmd, $output, $resultCode);

        if ($resultCode === 0) {
            // Step 5: Log message
            log_message('info', "Invoice for Auction ID {$auctionId} will be generated in 1 minutes (Brisbane time: {$endDateTime->format('Y-m-d H:i:s')}, timestamp: {$endTimestamp}).");
            log_message('debug', "CLI command executed: {$cliCommand}");
        } else {
            log_message('error', "Failed to schedule invoice for auction ID {$auctionId}. Command: {$scheduleCmd}. Output: " . implode("\n", $output));
        }
    }

    public function process_auction_invoice_email_by_id($auctionId = null)
    {

        $nowTime = date('Y-m-d H:i:s');

        if (!$auctionId || !is_numeric($auctionId)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Missing or invalid auction ID.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(400);
        }


        $this->load->model('EmailQueueModel');
        $this->load->model('AuctionModel');
        $this->load->model('InvoiceModel');



        $emails = $this->EmailQueueModel->get_invoice_pending_due_by_auction($auctionId);

        if (empty($emails)) {

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'No pending emails found in the queue.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(200);

            return;
        }

        foreach ($emails as $email) {


            $this->EmailQueueModel->mark_processing($email->id, $nowTime);

            try {


                $currentHighestBid = $this->AuctionModel->getCurrentHighestBidWithUser($email->auction_id);

                if ($currentHighestBid['user_id'] !== null) {

                    $user_id = $currentHighestBid['user_id'];
                    $max_bid = $currentHighestBid['current_highest_bid'];

                    // Apply tier-based buyer fee (Players: 10%, Sharks: 0%, Whales: 0%)
                    $buyerFeeRate = 10; // Default 10% for non-subscribers and Players
                    $this->load->model('SubscriptionGiveawayModel');
                    $userSub = $this->SubscriptionGiveawayModel->get_user_subscription($user_id);
                    if ($userSub && $userSub['status'] === 'active') {
                        $buyerFeeRate = (float)($userSub['fee_rate'] ?? 10);
                    }
                    $buyerFee = round($max_bid * ($buyerFeeRate / 100), 2);
                    $invoiceAmount = $max_bid + $buyerFee;

                    $taxRate = 10;
                    $taxAmount = $invoiceAmount - ($invoiceAmount / (1 + ($taxRate / 100)));

                    // Extract only required fields from JSON input
                    $invoiceData = [
                        'invoice_number' => uniqid('INV-'), // or your own logic
                        'amount'         => number_format($invoiceAmount, 2),
                        'tax_amount'     => number_format($taxAmount, 2), // 10% tax
                        'total_amount'   => number_format($invoiceAmount, 2),
                        'currency'       => 'AUD',
                        'invoice_date'   => date('Y-m-d H:i:s'),
                        'due_date'       => date('Y-m-d H:i:s', strtotime('+3 days')),
                        'paid_date'      => date('Y-m-d H:i:s', strtotime('+3 days')), // same for now
                        'created_at'     => date('Y-m-d H:i:s'),
                        'updated_at'     => date('Y-m-d H:i:s')
                    ];

                    $invoiceDataInserted = $this->InvoiceModel->insert_invoice($invoiceData);

                    // Get company ID from JSON input
                    $invoiceId = $invoiceDataInserted['invoice_id'];

                    // Validate company ID
                    if ($invoiceId <= 0) {
                        echo json_encode([
                            "status" => "error",
                            "message" => "Invalid or missing company ID."
                        ], JSON_PRETTY_PRINT);
                        http_response_code(400); // Bad Request
                        exit;
                    }

                    // Extract only required fields from JSON input
                    $invoiceAuctionRelationshipData = [
                        'invoice_id' => $invoiceId,
                        'invoice_type_id' => 2,
                        'invoice_payment_method_type_id' => 2,
                        'invoice_payment_status_type_id' => 1,
                        'auction_id' => $email->auction_id,
                        'user_id' =>  $user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $this->InvoiceModel->insert_invoice_auction_relationship($invoiceAuctionRelationshipData);

                    // Extract only required fields from JSON input
                    $auctionWonData = [
                        'auction_id' => $email->auction_id,
                        'user_id' =>  $user_id,
                        'created_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $this->AuctionModel->addToWonAuction($auctionWonData);

                    // --- THE VAULT: Win Reward Integration ---
                    $this->load->model('CoinModel');
                    $coinRules = $this->CoinModel->getRules();
                    $coinsOnWin = $coinRules ? $coinRules->coins_on_auction_win : 150;
                    
                    $this->CoinModel->updateBalance(
                        $user_id,
                        $coinsOnWin,
                        'auction_won',
                        'auction_id',
                        $email->auction_id,
                        "Won Auction #{$email->auction_id}"
                    );
                    // ----------------------------------------

                    $auctionStatusTypeData = [
                        'auction_type_id' => 9,
                        'updated_at' => date('Y-m-d H:i:s')
                    ];

                    $this->AuctionModel->updateAuctionStatusTypeData($email->auction_id, $auctionStatusTypeData);


                    $this->load->model('UserModel');
                    // ✅ Get Stripe customer ID from DB
                    $stripeData = $this->UserModel->getUserStripeIdById($user_id);
                    $user = null;
                    $subscriptions = null;

                    if ($stripeData && !empty($stripeData['stripe_customer_id'])) {

                        $customerId = $stripeData['stripe_customer_id'];
                        $customer   = Customer::retrieve($customerId);

                        if (!isset($customer->deleted) || !$customer->deleted) {

                            $user = [
                                'id'    => $customer->id,
                                'email' => $customer->email,
                                'name'  => $customer->name,
                            ];

                            // Load Auction Model
                            $this->load->model('AdminSettingModel');

                            // Fetch bidder profile details from the database
                            $defaultCommissionRate = $this->AdminSettingModel->get_commission_value(1, "percentage");


                            // --- Get active subscription(s) ---
                            $subs = Subscription::all(['customer' => $customerId, 'status' => 'active', 'limit' => 1]);

                            if (!empty($subs->data)) {
                                foreach ($subs->data as $sub) {
                                    $item = $sub->items->data[0];
                                    $product = \Stripe\Product::retrieve($item->price->product);

                                    // ✅ If membership exists, override default commission rate
                                    $commissionRate = isset($product->metadata->commission_rate)
                                        ? floatval($product->metadata->commission_rate)
                                        : $defaultCommissionRate;

                                    $subscriptions[] = [
                                        'subscription_id'      => $sub->id,
                                        'status'               => $sub->status,
                                        'start_date'           => date('Y-m-d H:i:s', $sub->start_date),
                                        'current_period_start' => date('Y-m-d H:i:s', $item->current_period_start),
                                        'current_period_end'   => date('Y-m-d H:i:s', $item->current_period_end),
                                        'plan' => [
                                            'product_id' => $item->price->product,
                                            'name'       => $product->name,
                                            'price_id'   => $item->price->id,
                                            'amount'     => $item->price->unit_amount / 100,
                                            'currency'   => strtoupper($item->price->currency),
                                            'interval'   => $item->price->recurring->interval . "ly",
                                            'commission_rate' => $commissionRate
                                        ]
                                    ];
                                }
                            }

                            $hasMembership  = false;

                            // ✅ Determine which commission rate to use
                            if (!empty($subscriptions) && isset($subscriptions[0]['plan']['commission_rate'])) {
                                $effectiveCommissionRate = $subscriptions[0]['plan']['commission_rate'];
                                $commissionText = "Membership Commission Rate ({$effectiveCommissionRate}%)";
                                $hasMembership  = true;
                            } else {
                                $effectiveCommissionRate = $defaultCommissionRate;
                                $commissionText = "Standard Commission Rate ({$defaultCommissionRate}%)";
                                $hasMembership  = true;
                            }

                            $result = $this->processAutomaticStripePayment($invoiceId, $hasMembership);

                            if ($result['status'] === 'success') {
                                log_message(
                                    'info',
                                    '✅ Automatic payment succeeded for invoice ' . $invoiceId .
                                        ': ' . $result['message'] .
                                        ': ' . json_encode($result['data'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE)
                                );

                                $paymentStatusText = 'Payment was successfully processed via Stripe.';
                                $invoiceStatus = 'Paid';
                                $paymentMethod = 'Stripe (Default Payment Method)';
                                $paymentDate = date('F j, Y, g:i A');
                                $transactionId = $result['data']['payment_intent_id'] ?? '-';
                                $currency = $result['data']['currency'] ?? 'AUD';
                            } else {
                                log_message(
                                    'error',
                                    '⚠️ Automatic payment failed for invoice ' . $invoiceId .
                                        ': ' . $result['message']
                                );

                                $paymentStatusText = 'Payment could not be processed automatically. Please log in to complete your payment.';
                                $invoiceStatus = 'Unpaid';
                                $paymentMethod = '—';
                                $paymentDate = '—';
                                $transactionId = '—';
                                $currency = 'AUD';
                            }



                            // ✅ Example calculation
                            $commissionAmount = ($email->auction_amount * $effectiveCommissionRate) / 100;

                            $viewData = [
                                // 👤 Recipient Information
                                'name' => $email->name ?? 'Customer',
                                'email' => $email->to_email ?? '',

                                // 🧾 Invoice & Auction Info
                                'invoice_number' => "INV-" . $invoiceId,
                                'auction_item' => $email->auction_item ?? 'Auction Item',
                                'auction_amount' => (float) $email->auction_amount,
                                'auction_commission_amount' => round($commissionAmount, 2),
                                'commission_text' => $commissionText,
                                'auction_tax_amount' => round($commissionAmount / 10, 2), // 10% tax on commission
                                'auction_total' => round($email->auction_amount + $commissionAmount, 2),

                                // 💰 Payment / Status Info
                                'invoice_status' => $invoiceStatus, // "Paid" or "Unpaid"
                                'payment_method' => $paymentMethod ?? 'Stripe (Default)',
                                'payment_date' => $paymentDate ?? date('F j, Y, g:i A'),
                                'transaction_id' => $transactionId ?? '-',
                                'currency' => strtoupper($currency ?? 'AUD')
                            ];


                            // 🔽 Get PDF as string
                            $invoicePdf = $this->generate_invoice_pdf('pdf_invoice_template', $viewData);


                            $this->send(
                                $email->to_email,
                                $email->subject,
                                $email->template,
                                [
                                    'name'         => $email->name,
                                    'auction_item' => $email->auction_item, /*"Forest Glen Self Storage Auction #13 - San Jose2, California",*/
                                    'invoice_number' => "INV-" . $invoiceId,
                                    'auction_amount' => $email->auction_amount,
                                    'auction_commission_amount' => $commissionAmount,
                                    'commission_text'   => $commissionText,
                                    'auction_tax_amount' => $commissionAmount / 10,
                                    'auction_total' => $email->auction_amount + $commissionAmount,
                                    'payment_status_text' => $paymentStatusText
                                ],
                                true,
                                $invoicePdf
                            );


                            $this->EmailQueueModel->mark_sent($email->id, $nowTime);
                        }
                    }
                }
            } catch (Exception $e) {
                $this->EmailQueueModel->mark_failed($email->id, $nowTime, $e->getMessage());
            }
        }

        // Optional success message
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Email queue processed successfully.',
                'processed' => count($emails),
                'timestamp' => $nowTime
            ]))
            ->set_status_header(200);
    }

    public function processAutomaticStripePayment($invoice_id, $hasMembership)
    {

        if ($invoice_id <= 0) {

            return [
                "status" => "error",
                "message" => "Invalid invoice_id. It must be a positive integer."
            ];
        }


        $this->load->model('InvoiceModel');

        $invoiceData = $this->InvoiceModel->getInvoiceById($invoice_id);
        if (!$invoiceData) {

            return [
                "status" => "error",
                "message" => "Invoice not found."
            ];
        }

        $auctionData = $this->InvoiceModel->getAuctionDetailsByInvoiceId($invoice_id);
        if (!$auctionData) {

            return [
                "status" => "error",
                "message" => "Auction not found."
            ];
        }

        $userId = $auctionData['user_id'];
        $auctionId = $auctionData['auction_id'];

        $this->load->model('UserModel');

        $userStripeData = $this->UserModel->getUserStripeIdById($userId);
        if (!$userStripeData || empty($userStripeData['stripe_customer_id'])) {

            return [
                'status'  => "error",
                'message' => "No Stripe customer record found for this user. The user may not have any membership yet."
            ];
        }


        try {
            $stripeCustomerId = $userStripeData['stripe_customer_id'];
            $customer = \Stripe\Customer::retrieve($stripeCustomerId);

            if (isset($customer->deleted) && $customer->deleted) {

                return [
                    'status'  => "error",
                    'message' => "The Stripe customer record for this user has been deleted. Please re-subscribe or contact support."
                ];
            }

            if (empty($customer->invoice_settings->default_payment_method)) {

                return [
                    'status'  => "error",
                    'message' => "Customer has no default payment method."
                ];
            }

            // 🟢 Check for active subscription
            $subscriptions = \Stripe\Subscription::all([
                'customer' => $stripeCustomerId,
                'status'   => 'active',
                'limit'    => 1
            ]);

            $hasActiveSubscription = !empty($subscriptions->data);

            // ✅ Default commission rate from admin settings
            $this->load->model('AdminSettingModel');
            $defaultCommissionRate = $this->AdminSettingModel->get_commission_value(1, "percentage");
            $effectiveCommissionRate = $defaultCommissionRate; // fallback default

            // 🧩 If user has an active membership subscription
            if ($hasActiveSubscription && $hasMembership) {
                try {
                    // Get the first active subscription
                    $activeSubscription = $subscriptions->data[0];
                    $item = $activeSubscription->items->data[0];

                    // Retrieve the related Stripe product
                    $product = \Stripe\Product::retrieve($item->price->product);

                    // ✅ Pull commission rate from product metadata
                    if (isset($product->metadata->commission_rate) && is_numeric($product->metadata->commission_rate)) {
                        $effectiveCommissionRate = floatval($product->metadata->commission_rate);
                    } else {
                        $effectiveCommissionRate = $defaultCommissionRate;
                    }
                } catch (Exception $e) {
                    // Fallback gracefully if Stripe metadata retrieval fails
                    $effectiveCommissionRate = $defaultCommissionRate;
                }
            } else {
                // No active membership — use default admin setting
                $effectiveCommissionRate = $defaultCommissionRate;
            }

            $invoiceNumber = $invoiceData['id'];
            $invoiceAmount = $invoiceData['total_amount']; // Convert to cents;
            $invoiceCurrency = strtolower($invoiceData['currency']); // Always lowercase like 'usd'

            $taxRate = 10; // 10% is a percentage

            // 🧾 Calculate total amount
            $commissionAmount = ($invoiceAmount  * $effectiveCommissionRate) / 100;
            $taxAmount = $invoiceAmount - ($invoiceAmount / (1 + ($taxRate / 100)));
            $totalAmount = $invoiceAmount  + $commissionAmount;

            // Convert to cents for Stripe
            $stripeAmount = round($totalAmount * 100);

            $defaultPaymentMethodId = $customer->invoice_settings->default_payment_method;
            $invoiceCurrency = strtolower($invoiceData['currency'] ?? 'aud');

            // 1️⃣ Create Stripe PaymentIntent
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $stripeAmount,
                'currency' => $invoiceCurrency,
                'customer' => $stripeCustomerId,
                'payment_method' => $defaultPaymentMethodId,
                'confirm' => true,
                'off_session' => true,
                'description' => 'Payment of the Invoice #' . $invoice_id,
                'metadata' => [
                    'auction_id' => $auctionId,
                    'invoice_id' => $invoiceNumber,
                    'user_id' => $userId,
                    'commission_rate' => $effectiveCommissionRate,
                    'has_membership'   => $hasMembership ? 'true' : 'false',
                    'subscription_active' => $hasActiveSubscription ? 'true' : 'false'
                ]
            ]);

            if ($paymentIntent->status !== 'succeeded') {

                return [
                    'status'  => "error",
                    'message' => "Payment failed. Status: " . $paymentIntent->status
                ];
            }

            // ✅ Record payment in DB
            $invoicePaymentByStripeData = [
                "invoice_id"         => $invoiceNumber,
                "stripe_invoice_id"  => $paymentIntent->id,
                "invoice_amount"         => $invoiceAmount,
                "commission_rate"    => $effectiveCommissionRate,
                "commission_amount"  => $commissionAmount,
                "tax_rate"           => $taxRate,
                "tax_amount"         => $taxAmount,
                "total_paid"         => $totalAmount,
                "created_at"         => date("Y-m-d H:i:s"),
                "updated_at"         => date("Y-m-d H:i:s")
            ];

            $invoicePaymentResult = $this->InvoiceModel->insert_invoice_with_stripe($invoicePaymentByStripeData);

            if ($invoicePaymentResult) {

                return [
                    "status" => "success",
                    "message" => "Invoice has been paid successfully.",
                    "data" => [
                        "payment_intent_id" => $paymentIntent->id,
                        "commission_rate"   => $effectiveCommissionRate,
                        "commission_amount" => round($commissionAmount, 2),
                        "tax_amount"        => round($taxAmount, 2),
                        "total_paid"        => round($totalAmount, 2),
                        "currency"          => strtoupper($invoiceCurrency),
                        "status"            => $paymentIntent->status
                    ]
                ];
            } else {

                return [
                    "status" => "error",
                    "message" => "Something went wrong. We couldn't process the invoice payment."
                ];
            }
        } catch (\Stripe\Exception\CardException $e) {
            // 🟥 Card declined or insufficient funds
            return [
                'status'  => 'error',
                'message' => $e->getError()->message ?? 'Your card was declined.'
            ];
        } catch (\Stripe\Exception\RateLimitException $e) {
            // Too many requests made to the API too quickly
            return [
                'status'  => 'error',
                'message' => 'Rate limit error: Please try again later.'
            ];
        } catch (\Stripe\Exception\InvalidRequestException $e) {
            // Invalid parameters to Stripe API
            return [
                'status'  => 'error',
                'message' => 'Invalid request to Stripe API: ' . $e->getError()->message
            ];
        } catch (\Stripe\Exception\AuthenticationException $e) {
            // Authentication with Stripe's API failed
            return [
                'status'  => 'error',
                'message' => 'Stripe authentication failed. Check API keys.'
            ];
        } catch (\Stripe\Exception\ApiConnectionException $e) {
            // Network communication with Stripe failed
            return [
                'status'  => 'error',
                'message' => 'Network error while contacting Stripe. Please retry.'
            ];
        } catch (\Stripe\Exception\ApiErrorException $e) {
            // Generic API error (catch-all)
            return [
                'status'  => 'error',
                'message' => 'Stripe API error: ' . $e->getError()->message
            ];
        } catch (Exception $e) {
            // Non-Stripe general error
            return [
                'status'  => 'error',
                'message' => 'Unexpected error: ' . $e->getMessage()
            ];
        }
    }
    public function process_auction_outbid_email_by_id($auctionId = null)
    {

        $this->load->model('EmailQueueModel');

        $nowTime = date('Y-m-d H:i:s');

        if (!$auctionId || !is_numeric($auctionId)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Missing or invalid auction ID.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(400);
        }

        $emails = $this->EmailQueueModel->get_outbid_pending_due_by_auction($auctionId);

        if (empty($emails)) {

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'No pending emails found in the queue.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(200);

            return;
        }

        foreach ($emails as $email) {


            $this->EmailQueueModel->mark_processing($email->id, $nowTime);

            try {



                $this->send(
                    $email->to_email,
                    $email->subject,
                    $email->template,
                    [
                        'name'         => $email->name,
                        'auction_item' => $email->auction_item, /*"Forest Glen Self Storage Auction #13 - San Jose2, California",*/
                        'auction_url'  => $email->auction_url
                    ],
                    false,
                    ""
                );



                $this->EmailQueueModel->mark_sent($email->id, $nowTime);
            } catch (Exception $e) {
                $this->EmailQueueModel->mark_failed($email->id, $nowTime, $e->getMessage());
            }
        }

        // Optional success message
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Email queue processed successfully.',
                'processed' => count($emails),
                'timestamp' => $nowTime
            ]))
            ->set_status_header(200);
    }




    public function send_otp()
    {
        // Check if the request is an AJAX request
        if (!$this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'status' => 'danger', 'message' => 'Invalid request']))
                ->set_status_header(400);
            return;
        }

        // Get data from JSON request body
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        $email = isset($data['email']) ? strtolower(trim($data['email'])) : null;

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    "status" => "error",
                    "message" => "A valid email address is required."
                ]))
                ->set_status_header(400);
            return;
        }

        $toName = $data['toName'];
        // Load the model
        $this->load->model('UserModel');

        $existingUser = $this->UserModel->get_user_by_email_address($email);

        if ($existingUser) {
            // Email address already exists, return an error response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'status' => 'danger', 'message' => 'This email is already in use. Please use a different one.']))
                ->set_status_header(200);
            return;
        }

        // Generate a 6-digit OTP
        $otp = str_pad(mt_rand(0, 999999), 6, '0', STR_PAD_LEFT);


        $response = $this->send(
            $email,
            '[Storage Punters] Your OTP Code for Access Verification',
            'new_registration_otp_email',
            ['name' => $toName, 'otp_code' => $otp],
            false,
            ""
        );

        if (!isset($response['success'])) {
            $response = ['success' => false, 'status' => 'danger', 'message' => 'An error occurred while sending the OTP. Please try again later.'];

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        }

        if ($response['success']) {

            $result =  [
                'status' => 'success',
                'message' => 'OTP sent successfully',
                'data' => [
                    '_otp' => $otp,
                    '_otp_email' => $email,
                    '_timestamp' => time() // Store current timestamp
                ]
            ];
            $this->session->unset_userdata('storage_punters_customer_registration_otp_session_data');

            $this->session->set_userdata('storage_punters_customer_registration_otp_session_data', $result);

            $response = ['success' => true, 'status' => 'success', 'message' => 'OTP sent successfully. Please check your email.'];

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        } else {
            $response = ['success' => false, 'status' => 'danger', 'message' => 'An error occurred while sending the OTP. Please try again later.'];

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        }
    }

    public function verify_otp()
    {

        // Check if the request is an AJAX request
        if (!$this->input->is_ajax_request()) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'status' => 'danger', 'message' => 'Invalid request']))
                ->set_status_header(400);
            return;
        }

        // Get data from JSON request body
        $data = json_decode(file_get_contents('php://input'), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid JSON payload: " . json_last_error_msg()
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }



        $email = isset($data['email']) ? strtolower(trim($data['email'])) : null;

        if (!$email || !filter_var($email, FILTER_VALIDATE_EMAIL)) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    "status" => "error",
                    "message" => "A valid email address is required."
                ]))
                ->set_status_header(400);
            return;
        }


        // Load the model
        $this->load->model('UserModel');

        $existingUser = $this->UserModel->get_user_by_email_address($email);

        if ($existingUser) {
            // Email address already exists, return an error response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'status' => 'danger', 'message' => 'This email is already in use. Please use a different one.']))
                ->set_status_header(200);
            return;
        }


        // Check if 'to' (email address) and 'otp' are provided
        if (!isset($data['otp'])) {

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    "status" => "error",
                    "message" => "OTP is required."
                ]))
                ->set_status_header(400);
            return;
        }


        $otp = $data['otp'];

        // Retrieve OTP, email, and timestamp from session
        $sessionData = $this->session->userdata('storage_punters_customer_registration_otp_session_data');

        // Check if session data exists
        if ($sessionData) {
            // Check if OTP has expired (1 minute expiration time)
            $currentTimestamp = time();
            $otpTimestamp = $sessionData['data']['_timestamp'];
            if (($currentTimestamp - $otpTimestamp) > 60) {
                // OTP has expired
                $response = ['success' => false, 'status' => 'danger', 'message' => 'OTP has expired. Please request a new OTP.'];
                $this->session->unset_userdata('storage_punters_customer_registration_otp_session_data');

                $this->output
                    ->set_content_type('application/json')
                    ->set_output(json_encode($response))
                    ->set_status_header(200);
            } else {
                // Validate OTP and email
                if ($email === $sessionData['data']['_otp_email'] && $otp === $sessionData['data']['_otp']) {
                    // OTP is valid
                    $response = ['success' => true, 'status' => 'success', 'message' => 'OTP verified successfully.'];
                    // Optionally, clear OTP from session after successful verification
                    $this->session->unset_userdata('storage_punters_customer_registration_otp_session_data');

                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode($response))
                        ->set_status_header(200);
                } else {
                    // OTP is invalid
                    $response = ['success' => false, 'status' => 'danger', 'message' => 'Invalid OTP. Please try again.'];

                    $this->output
                        ->set_content_type('application/json')
                        ->set_output(json_encode($response))
                        ->set_status_header(200);
                }
            }
        } else {
            // No OTP data found in session
            $response = ['success' => false, 'status' => 'danger', 'message' => 'No OTP data found. Please request a new OTP.'];

            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode($response))
                ->set_status_header(200);
        }
    }

    public function process_connected_account_active_email_by_id($bankId = null)
    {

        $nowTime = date('Y-m-d H:i:s');

        if (!$bankId || !is_numeric($bankId)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Missing or invalid bank ID.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(400);
        }


        $this->load->model('EmailQueueModel');
        $this->load->model('BankModel');



        $emails = $this->EmailQueueModel->get_account_connected_active_pending_by_account_id($bankId);

        if (empty($emails)) {

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'No pending emails found in the queue.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(200);

            return;
        }

        foreach ($emails as $email) {


            $this->EmailQueueModel->mark_processing($email->id, $nowTime);

            try {


                $this->send(
                    $email->to_email,
                    $email->subject,
                    $email->template,
                    [
                        'name'         => $email->name,
                        'dashboard_url' => base_url('bank/setup/new')
                    ]
                );


                $this->EmailQueueModel->mark_sent($email->id, $nowTime);
            } catch (Exception $e) {
                $this->EmailQueueModel->mark_failed($email->id, $nowTime, $e->getMessage());
            }
        }

        // Optional success message
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Email queue processed successfully.',
                'processed' => count($emails),
                'timestamp' => $nowTime
            ]))
            ->set_status_header(200);
    }


    public function process_connected_account_inactive_email_by_id($bankId = null)
    {

        $nowTime = date('Y-m-d H:i:s');

        if (!$bankId || !is_numeric($bankId)) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => false,
                    'message' => 'Missing or invalid bank ID.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(400);
        }


        $this->load->model('EmailQueueModel');
        $this->load->model('BankModel');



        $emails = $this->EmailQueueModel->get_account_connected_inactive_pending_by_account_id($bankId);

        if (empty($emails)) {

            // Return JSON response
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode([
                    'success' => true,
                    'message' => 'No pending emails found in the queue.',
                    'timestamp' => $nowTime
                ]))
                ->set_status_header(200);

            return;
        }

        foreach ($emails as $email) {


            $this->EmailQueueModel->mark_processing($email->id, $nowTime);

            try {


                $this->send(
                    $email->to_email,
                    $email->subject,
                    $email->template,
                    [
                        'name'         => $email->name,
                        'onboarding_url' => $email->onboarding_url
                    ]
                );


                $this->EmailQueueModel->mark_sent($email->id, $nowTime);
            } catch (Exception $e) {
                $this->EmailQueueModel->mark_failed($email->id, $nowTime, $e->getMessage());
            }
        }

        // Optional success message
        $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'message' => 'Email queue processed successfully.',
                'processed' => count($emails),
                'timestamp' => $nowTime
            ]))
            ->set_status_header(200);
    }
}
