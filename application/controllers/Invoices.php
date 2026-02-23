<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Invoices extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Australia/Brisbane');
    }

    public function getInvoicesAuctionsByBidderUser()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            http_response_code(403); // Forbidden
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access payment methods."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Get logged-in user ID
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check if user is logged in
        if (!in_array($userRoleId, [1, 2, 3])) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }



        if (in_array($userRoleId, [1, 3])) {


            $this->load->model('CompanyModel');


            // Fetch bidder profile details from the database
            $companyData = $this->CompanyModel->getAllCompaniesByUser($userId);

            // If user profile not found, return error response
            if (!$companyData) {

                // Prepare the response data
                $data = [
                    "draw" => (int) $this->input->post('draw'),
                    "recordsTotal" => "0",
                    "recordsFiltered" => "0",
                    "data" => [],
                ];

                http_response_code(200); // Not Found
                echo json_encode([
                    "status" => "error",
                    "message" => "No company data is found.",
                    "result" => $data
                ], JSON_PRETTY_PRINT);
                exit;
            }



            // Extract just the company IDs
            $companyIds = array_column($companyData, 'company_id');

            $this->load->model('StorageFacilityModel');

            $storageFacilityIds = $this->StorageFacilityModel->getStorageFacilityIdsByCompanyId($companyIds);

            if (empty($storageFacilityIds)) {

                // Prepare the response data
                $data = [
                    "draw" => (int) $this->input->post('draw'),
                    "recordsTotal" => "0",
                    "recordsFiltered" => "0",
                    "data" => [],
                ];

                http_response_code(200);


                echo json_encode([
                    "status" => "error",
                    "message" => "No storage facilities found for the user's companies.",
                    "result" => $data
                ], JSON_PRETTY_PRINT);
                exit;
            }


            $this->load->model('AuctionModel');

            $auctionIds = $this->AuctionModel->getAuctionIdsByStorageFacilityIds($storageFacilityIds);

            if (empty($auctionIds)) {

                // Prepare the response data
                $data = [
                    "draw" => (int) $this->input->post('draw'),
                    "recordsTotal" => "0",
                    "recordsFiltered" => "0",
                    "data" => [],
                ];


                http_response_code(200);
                echo json_encode([
                    "status" => "error",
                    "message" => "No auctions are found for the user's storage facilities.",
                    "result" => $data
                ], JSON_PRETTY_PRINT);
                exit;
            }
        } else {

            $this->load->model('AuctionModel');

            $auctionIds = $this->AuctionModel->getAuctionIdsByUserId($userId);


            if (empty($auctionIds)) {

                // Prepare the response data
                $data = [
                    "draw" => (int) $this->input->post('draw'),
                    "recordsTotal" => "0",
                    "recordsFiltered" => "0",
                    "data" => [],
                ];


                http_response_code(200);
                echo json_encode([
                    "status" => "error",
                    "message" => "No auctions are found for the user.",
                    "result" => $data
                ], JSON_PRETTY_PRINT);
                exit;
            }
        }
        // Sanitize input data
        $draw = (int)$this->input->post('draw');
        $start = (int)$this->input->post('start');
        $length = (int)$this->input->post('length');
        $search = $this->input->post('search')['value'];
        $orderColumn = $this->input->post('order')[0]['column'];
        $orderDir = $this->input->post('order')[0]['dir'];

        // Validate input data to prevent SQL injection
        if (!is_int($draw) || !is_int($start) || !is_int($length)) {
            $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['error' => 'Invalid input']))
                ->set_status_header(400);
            return;
        }

        // Load User Model
        $this->load->model('InvoiceModel');

        $totalRecords = $this->InvoiceModel->countAllAuctionInvoicesByBidderUser($userId, $userRoleId, $auctionIds);
        $filteredRecords = $this->InvoiceModel->countFilteredAuctionInvoicesByBidderUser($search, $userId, $userRoleId, $auctionIds);
        $result = $this->InvoiceModel->getFilteredAuctionInvoicesByBidderUser($start, $length, $search, $orderColumn, $orderDir, $userId, $userRoleId, $auctionIds);

        // Prepare the response data
        $data = [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $result,
        ];

        // Return success response with payment method data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Invoices are retrieved successfully.",
            "result" => $data
        ], JSON_PRETTY_PRINT);
    }

    public function downloadInvoice($invoiceId)
    {
        // 1. Check Login
        $session = $this->session->userdata('auction_user_session_data');
        if (!$session) {
            redirect('login');
        }

        $userId = $session['user_id'];
        $userRoleId = $session['role_id'];

        // 2. Load Models & Library
        $this->load->model('InvoiceModel');
        $this->load->model('AuctionModel');
        $this->load->model('UserModel');
        $this->load->model('StorageFacilityModel');
        $this->load->model('CompanyModel');
        $this->load->library('Pdf');

        // 3. Fetch Data
        $invoice = $this->InvoiceModel->getInvoiceById($invoiceId);
        if (!$invoice) show_404();

        $auctionRel = $this->InvoiceModel->getAuctionDetailsByInvoiceId($invoiceId);
        if (!$auctionRel) show_404();

        // Security Check: Only the bidder who won or an admin can download
        if ($userRoleId == 2 && $auctionRel['user_id'] != $userId) {
            show_error('Unauthorized access to this invoice.', 403);
        }

        $auction = $this->AuctionModel->getAuctionById($auctionRel['auction_id']);
        $punter = $this->UserModel->getBidderInformationById($auctionRel['user_id']); 
        $facility = $this->StorageFacilityModel->getStorageFacilityInformationByAuctionId($auctionRel['auction_id']);
        $facilityInfo = $this->StorageFacilityModel->getStorageFacilityById($facility['storage_facility_id']);

        // 4. Create PDF
        $pdf = new Pdf(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);
        $pdf->SetCreator(PDF_CREATOR);
        $pdf->SetAuthor('Storage Punters');
        $pdf->SetTitle('Invoice ' . $invoice['invoice_number']);
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(false);
        $pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);
        $pdf->SetMargins(PDF_MARGIN_LEFT, PDF_MARGIN_TOP, PDF_MARGIN_RIGHT);
        $pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
        $pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);
        $pdf->AddPage();
        $pdf->SetFont('helvetica', '', 10);

        // 5. HTML Content
        $html = "
        <table cellpadding='5'>
            <tr>
                <td><h1 style='color: #2D3436;'>TAX INVOICE</h1></td>
                <td align='right'>
                    <b>Invoice:</b> {$invoice['invoice_number']}<br>
                    <b>Date:</b> " . date('d/m/Y', strtotime($invoice['invoice_date'])) . "<br>
                    <b>Status:</b> PAID
                </td>
            </tr>
        </table>
        <hr>
        <table cellpadding='5'>
            <tr>
                <td>
                    <b>BILL FROM:</b><br>
                    {$facilityInfo['name']}<br>
                    {$facilityInfo['address']}<br>
                    Email: {$facilityInfo['contact_email']}
                </td>
                <td>
                    <b>BILL TO:</b><br>
                    {$punter['firstname']} {$punter['lastname']}<br>
                    Email: {$punter['email']}<br>
                    Punter ID: " . ($punter['punter_id'] ?? 'N/A') . "
                </td>
            </tr>
        </table>
        <br><br>
        <table border='1' cellpadding='8'>
            <tr style='background-color: #f2f2f2; font-weight: bold;'>
                <th width='70%'>Description</th>
                <th width='30%' align='right'>Amount</th>
            </tr>
            <tr>
                <td>
                    <b>Auction #{$auction['id']} - Unit " . ($auction['unit_number'] ?? 'N/A') . "</b><br>
                    Winning Bid: " . number_format($invoice['amount'], 2) . " {$invoice['currency']}
                </td>
                <td align='right'>" . number_format($invoice['amount'], 2) . "</td>
            </tr>
            <tr>
                <td align='right'><b>Subtotal</b></td>
                <td align='right'>" . number_format($invoice['amount'], 2) . "</td>
            </tr>
            <tr>
                <td align='right'><b>GST (10%)</b></td>
                <td align='right'>" . number_format($invoice['tax_amount'], 2) . "</td>
            </tr>
            <tr style='background-color: #f9f9f9;'>
                <td align='right'><b>TOTAL ({$invoice['currency']})</b></td>
                <td align='right'><b>" . number_format($invoice['total_amount'], 2) . "</b></td>
            </tr>
        </table>
        <br><br>
        <div style='text-align: center; color: #888; font-size: 9px;'>
            Thank you for your business. For any questions, please contact the storage facility directly.<br>
            <i>Generated by Storage Punters Platform</i>
        </div>";

        $pdf->writeHTML($html, true, false, true, false, '');
        $pdf->Output('Invoice_' . $invoice['invoice_number'] . '.pdf', 'D');
    }
}
