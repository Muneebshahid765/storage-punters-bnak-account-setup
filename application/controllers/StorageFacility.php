<?php
defined('BASEPATH') or exit('No direct script access allowed');

class StorageFacility extends CI_Controller
{

    public function getAllFacilitiesByUser()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
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
        // Load Auction Model
        $this->load->model('UserModel');

        // Fetch bidder profile details from the database
        $facilityData = $this->UserModel->getAllFacilitiesByUser($userId);

        // If user profile not found, return error response
        if (!$facilityData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No facility data is found."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Return success response with user profile data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Facility data has been retrieved successfully.",
            "data" => $facilityData
        ], JSON_PRETTY_PRINT);
    }

    public function addStorageFacilityByStorageFacilityUser()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
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
            http_response_code(400); // Bad Request
            exit;
        }

        // Get  storage company ID from JSON input
        $companyId = !empty($json_input['companyId']) ? (int)$json_input['companyId'] : 0;

        // Validate company ID
        if ($companyId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing company ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // ✅ Required fields list
        $required_fields = [
            'storage_facility_name',
            'storage_facility_website',
            'storage_facility_timezone',
            'storage_facility_contact_email',
            'storage_facility_office_hours',
            'storage_facility_notice_to_buyers'
        ];

        $missing_fields = [];

        // ✅ Validate required fields
        foreach ($required_fields as $field) {
            if (!isset($json_input[$field]) || trim((string)$json_input[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        // ✅ Return error if any required field is missing
        if (!empty($missing_fields)) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields: " . implode(", ", $missing_fields)
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        // Extract only required fields from JSON input
        $storageFacilityData = [
            'name' => isset($json_input['storage_facility_name']) ? trim($json_input['storage_facility_name']) : null,
            'website' => isset($json_input['storage_facility_website']) ? trim($json_input['storage_facility_website']) : null,
            'description' => isset($json_input['storage_facility_description']) ? trim($json_input['storage_facility_description']) : null,
            'timezone' => isset($json_input['storage_facility_timezone']) ? trim($json_input['storage_facility_timezone']) : null,
            'contact_email' => isset($json_input['storage_facility_contact_email']) ? trim($json_input['storage_facility_contact_email']) : null,
            'additional_email' => isset($json_input['storage_facility_additional_email']) ? trim($json_input['storage_facility_additional_email']) : null,
            'office_hours' => isset($json_input['storage_facility_office_hours']) ? trim($json_input['storage_facility_office_hours']) : null,
            'payout_details' => isset($json_input['storage_facility_payout_details']) ? trim($json_input['storage_facility_payout_details']) : null,
            'youtube_video_url' => isset($json_input['storage_facility_youtube_video_url']) ? trim($json_input['storage_facility_youtube_video_url']) : null,
            'branding_logo' => isset($json_input['storage_facility_branding_logo']) ? trim($json_input['storage_facility_branding_logo']) : null,
            'notice_to_buyers' => isset($json_input['storage_facility_notice_to_buyers']) ? trim($json_input['storage_facility_notice_to_buyers']) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];


        // Load storage facility Model
        $this->load->model('StorageFacilityModel');


        $storageFacilityDataInserted = $this->StorageFacilityModel->insertStorageFacilityData($storageFacilityData);

        // Get storage facility ID from JSON input
        $storageFacilityId = $storageFacilityDataInserted['storage_facility_id'];

        // Validate storage facility ID
        if ($storageFacilityId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing storage facility ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $storageFacilityLocationData = [
            'street_address' => isset($json_input['storage_facility_street_address']) ? trim($json_input['storage_facility_street_address']) : null,
            'apt_suite' => isset($json_input['storage_facility_apt_suite']) ? trim($json_input['storage_facility_apt_suite']) : null,
            'city' => isset($json_input['storage_facility_city']) ? trim($json_input['storage_facility_city']) : null,
            'postal_code' => isset($json_input['storage_facility_zip']) ? trim($json_input['storage_facility_zip']) : null,
            'state' => isset($json_input['storage_facility_state']) ? trim($json_input['storage_facility_state']) : null,
            'country' => isset($json_input['storage_facility_country']) ? trim($json_input['storage_facility_country']) : null,
            'phone' => isset($json_input['storage_facility_cell_phone']) ? trim($json_input['storage_facility_cell_phone']) : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];


        $storageFacilityLocationDataInserted = $this->StorageFacilityModel->insertStorageFacilityLocationData($storageFacilityLocationData);

        // Get storage facility location ID from JSON input
        $storageFacilityLocationId = $storageFacilityLocationDataInserted['storage_facility_location_id'];

        // Validate company ID
        if ($storageFacilityLocationId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing storage facility location ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        $storageFacilityLocationRelationshipData = [
            'storage_facility_id' => $storageFacilityId,
            'location_id' => $storageFacilityLocationId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $storageFacilityStatusData = [
            'storage_facility_id' => $storageFacilityId,
            'status_type_id' => 1,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $storageFacilityCompanyRelationshipData = [
            'storage_facility_id' => $storageFacilityId,
            'company_id' => $companyId,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ];


        $storageFacilityLocationRelationshipDataInserted = $this->StorageFacilityModel->insertStorageFacilityLocationRelationshipData($storageFacilityLocationRelationshipData);
        $storageFacilityStatusDataInserted = $this->StorageFacilityModel->insertStorageFacilityStatusData($storageFacilityStatusData);
        $storageFacilityCompanyRelationshipDataInserted = $this->StorageFacilityModel->insertStorageFacilityCompanyRelationshipData($storageFacilityCompanyRelationshipData);


        // Check if at least one update was successful
        if ($storageFacilityDataInserted || $storageFacilityLocationDataInserted || $storageFacilityLocationRelationshipDataInserted || $storageFacilityStatusDataInserted || $storageFacilityCompanyRelationshipDataInserted) {

            // Insert upload_id and auction_id into tbl_auction_upload_relationship
            if (!empty($json_input['upload_ids']) && is_array($json_input['upload_ids'])) {
                $uploadRelationshipData = [];
                foreach ($json_input['upload_ids'] as $uploadId) {
                    $uploadRelationshipData[] = [
                        'storage_facility_id' => $storageFacilityId,
                        'upload_id' => $uploadId,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s")
                    ];
                }

                // Insert batch upload relationships
                $this->StorageFacilityModel->insertStorageFacilityUploadData($uploadRelationshipData);
            }

            $webhookData = [
                'storage_facility'       => [
                    'id' => $storageFacilityId,
                    'company_id' => $companyId,
                    'name' => isset($json_input['storage_facility_name']) ? trim($json_input['storage_facility_name']) : null,
                            'website' => isset($json_input['storage_facility_website']) ? trim($json_input['storage_facility_website']) : null,
                    'description' => isset($json_input['storage_facility_description']) ? trim($json_input['storage_facility_description']) : null,
                    'timezone' => isset($json_input['storage_facility_timezone']) ? trim($json_input['storage_facility_timezone']) : null,
                    'contact_email' => isset($json_input['storage_facility_contact_email']) ? trim($json_input['storage_facility_contact_email']) : null,
                    'additional_email' => isset($json_input['storage_facility_additional_email']) ? trim($json_input['storage_facility_additional_email']) : null,
                    'office_hours' => isset($json_input['storage_facility_office_hours']) ? trim($json_input['storage_facility_office_hours']) : null,
                    'payout_details' => isset($json_input['storage_facility_payout_details']) ? trim($json_input['storage_facility_payout_details']) : null,
                    'youtube_video_url' => isset($json_input['storage_facility_youtube_video_url']) ? trim($json_input['storage_facility_youtube_video_url']) : null,
                    'branding_logo' => isset($json_input['storage_facility_branding_logo']) ? trim($json_input['storage_facility_branding_logo']) : null,
                    'notice_to_buyers' => isset($json_input['storage_facility_notice_to_buyers']) ? trim($json_input['storage_facility_notice_to_buyers']) : null,
                    'street_address' => isset($json_input['storage_facility_street_address']) ? trim($json_input['storage_facility_street_address']) : null,
                    'apt_suite' => isset($json_input['storage_facility_apt_suite']) ? trim($json_input['storage_facility_apt_suite']) : null,
                    'city' => isset($json_input['storage_facility_city']) ? trim($json_input['storage_facility_city']) : null,
                    'postal_code' => isset($json_input['storage_facility_zip']) ? trim($json_input['storage_facility_zip']) : null,
                    'state' => isset($json_input['storage_facility_state']) ? trim($json_input['storage_facility_state']) : null,
                    'country' => isset($json_input['storage_facility_country']) ? trim($json_input['storage_facility_country']) : null
                ]
            ];

            // ✅ Send webhook and get result including payload
            $webhookResponse = send_webhook_event('storage_facility.created', 'storage_facility', $webhookData);

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Storage facility informations has been added successfully.",
                "data" => [
                    "storage_facility_id" => $storageFacilityId,
                    "user_id" => $userId,
                    "user_role_id" => $userId
                ]
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to add storage facility details."
            ], JSON_PRETTY_PRINT);
        }
        exit;
    }



    public function updateStorageFacilityByStorageFacilityUser()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
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
            http_response_code(400); // Bad Request
            exit;
        }


        // Get  storage facility ID from JSON input
        $storageFacilityId = isset($json_input['storageFacilityId']) ? (int)$json_input['storageFacilityId'] : 0;

        // Validate  storage facility ID
        if ($storageFacilityId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing  storage facility ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        // Load Company Model
        $this->load->model('StorageFacilityModel');

        // Check if company exists for this user
        $storageFacilityLocationData = $this->StorageFacilityModel->getStorageFacilityLocationIdByStorageFacilityId($storageFacilityId); // you must implement this method


        // Get storage facility location ID from JSON input
        $storageFacilityLocationId = $storageFacilityLocationData['location_id'];

        // Validate company ID
        if ($storageFacilityLocationId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing storage facility location ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // ✅ Required fields list
        $required_fields = [
            'storage_facility_name',
            'storage_facility_website',
            'storage_facility_timezone',
            'storage_facility_contact_email',
            'storage_facility_office_hours',
            'storage_facility_notice_to_buyers'
        ];

        $missing_fields = [];

        // ✅ Validate required fields
        foreach ($required_fields as $field) {
            if (!isset($json_input[$field]) || trim((string)$json_input[$field]) === '') {
                $missing_fields[] = $field;
            }
        }

        // ✅ Return error if any required field is missing
        if (!empty($missing_fields)) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing required fields: " . implode(", ", $missing_fields)
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        // Extract only required fields from JSON input
        $storageFacilityData = [
            'name' => isset($json_input['storage_facility_name']) ? trim($json_input['storage_facility_name']) : null,
            'website' => isset($json_input['storage_facility_website']) ? trim($json_input['storage_facility_website']) : null,
            'description' => isset($json_input['storage_facility_description']) ? trim($json_input['storage_facility_description']) : null,
            'timezone' => isset($json_input['storage_facility_timezone']) ? trim($json_input['storage_facility_timezone']) : null,
            'contact_email' => isset($json_input['storage_facility_contact_email']) ? trim($json_input['storage_facility_contact_email']) : null,
            'additional_email' => isset($json_input['storage_facility_additional_email']) ? trim($json_input['storage_facility_additional_email']) : null,
            'office_hours' => isset($json_input['storage_facility_office_hours']) ? trim($json_input['storage_facility_office_hours']) : null,
            'payout_details' => isset($json_input['storage_facility_payout_details']) ? trim($json_input['storage_facility_payout_details']) : null,
            'youtube_video_url' => isset($json_input['storage_facility_youtube_video_url']) ? trim($json_input['storage_facility_youtube_video_url']) : null,
            'branding_logo' => isset($json_input['storage_facility_branding_logo']) ? trim($json_input['storage_facility_branding_logo']) : null,
            'notice_to_buyers' => isset($json_input['storage_facility_notice_to_buyers']) ? trim($json_input['storage_facility_notice_to_buyers']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];


        $storageFacilityLocationData = [
            'street_address' => isset($json_input['storage_facility_street_address']) ? trim($json_input['storage_facility_street_address']) : null,
            'apt_suite' => isset($json_input['storage_facility_apt_suite']) ? trim($json_input['storage_facility_apt_suite']) : null,
            'city' => isset($json_input['storage_facility_city']) ? trim($json_input['storage_facility_city']) : null,
            'postal_code' => isset($json_input['storage_facility_zip']) ? trim($json_input['storage_facility_zip']) : null,
            'state' => isset($json_input['storage_facility_state']) ? trim($json_input['storage_facility_state']) : null,
            'country' => isset($json_input['storage_facility_country']) ? trim($json_input['storage_facility_country']) : null,
            'phone' => isset($json_input['storage_facility_cell_phone']) ? trim($json_input['storage_facility_cell_phone']) : null,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $storageFacilityDataUpdated = $this->StorageFacilityModel->updateStorageFacilityData($storageFacilityId, $storageFacilityData);
        $storageFacilityLocationDataUpdated = $this->StorageFacilityModel->updateStorageFacilityLocationData($storageFacilityLocationId, $storageFacilityLocationData);

        // Check if at least one update was successful
        if ($storageFacilityDataUpdated || $storageFacilityLocationDataUpdated) {

            // Insert upload_id and auction_id into tbl_storage_facility_upload_relationship
            if (!empty($json_input['upload_ids']) && is_array($json_input['upload_ids'])) {
                $uploadRelationshipData = [];
                foreach ($json_input['upload_ids'] as $uploadId) {
                    $uploadRelationshipData[] = [
                        'storage_facility_id' => $storageFacilityId,
                        'upload_id' => $uploadId,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s")
                    ];
                }

                // Insert batch upload relationships
                $this->StorageFacilityModel->insertStorageFacilityUploadData($uploadRelationshipData);
            }

            // ✅ Remove upload_id and auction_id from tbl_storage_facility_upload_relationship
            if (!empty($json_input['remove_upload_ids']) && is_array($json_input['remove_upload_ids'])) {
                foreach ($json_input['remove_upload_ids'] as $uploadIdToRemove) {
                    $this->StorageFacilityModel->deleteStorageFacilityUploadData($storageFacilityId, $uploadIdToRemove);
                }
            }

            $webhookData = [
                'storage_facility'       => [
                    'id' => $storageFacilityId,
                    'name' => isset($json_input['storage_facility_name']) ? trim($json_input['storage_facility_name']) : null,
                            'website' => isset($json_input['storage_facility_website']) ? trim($json_input['storage_facility_website']) : null,
                    'description' => isset($json_input['storage_facility_description']) ? trim($json_input['storage_facility_description']) : null,
                    'timezone' => isset($json_input['storage_facility_timezone']) ? trim($json_input['storage_facility_timezone']) : null,
                    'contact_email' => isset($json_input['storage_facility_contact_email']) ? trim($json_input['storage_facility_contact_email']) : null,
                    'additional_email' => isset($json_input['storage_facility_additional_email']) ? trim($json_input['storage_facility_additional_email']) : null,
                    'office_hours' => isset($json_input['storage_facility_office_hours']) ? trim($json_input['storage_facility_office_hours']) : null,
                    'payout_details' => isset($json_input['storage_facility_payout_details']) ? trim($json_input['storage_facility_payout_details']) : null,
                    'youtube_video_url' => isset($json_input['storage_facility_youtube_video_url']) ? trim($json_input['storage_facility_youtube_video_url']) : null,
                    'branding_logo' => isset($json_input['storage_facility_branding_logo']) ? trim($json_input['storage_facility_branding_logo']) : null,
                    'notice_to_buyers' => isset($json_input['storage_facility_notice_to_buyers']) ? trim($json_input['storage_facility_notice_to_buyers']) : null,
                    'street_address' => isset($json_input['storage_facility_street_address']) ? trim($json_input['storage_facility_street_address']) : null,
                    'apt_suite' => isset($json_input['storage_facility_apt_suite']) ? trim($json_input['storage_facility_apt_suite']) : null,
                    'city' => isset($json_input['storage_facility_city']) ? trim($json_input['storage_facility_city']) : null,
                    'postal_code' => isset($json_input['storage_facility_zip']) ? trim($json_input['storage_facility_zip']) : null,
                    'state' => isset($json_input['storage_facility_state']) ? trim($json_input['storage_facility_state']) : null,
                    'country' => isset($json_input['storage_facility_country']) ? trim($json_input['storage_facility_country']) : null
                ]
            ];

            // ✅ Send webhook and get result including payload
            $webhookResponse = send_webhook_event('storage_facility.updated', 'storage_facility', $webhookData);

            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Storage facility informations has been updated successfully."
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update storage facility details."
            ], JSON_PRETTY_PRINT);
        }
        exit;
    }




    public function updateStorageFacilityStatusByIdByStorageFacilityUser()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
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
            http_response_code(400); // Bad Request
            exit;
        }


        // Get storage facility ID from JSON input
        $storageFacilityId = isset($json_input['storage_facility_id']) ? (int)$json_input['storage_facility_id'] : 0;

        // Validate  storage facility ID
        if ($storageFacilityId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing storage facility ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        // ✅ Check if `storage_facility_status_name` is passed (for update scenarios)
        $storage_facility_status_name = isset($json_input['storage_facility_status_name']) ? (string)$json_input['storage_facility_status_name'] : null;


        // ✅ Handle case when `storage_facility_status_name` is NOT passed
        if ($storage_facility_status_name === null) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: storage_facility_status_name is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        if ($storage_facility_status_name == "Active") {
            $storage_facility_status_id = 1;
        } else if ($storage_facility_status_name == "Inactive") {
            $storage_facility_status_id = 2;
        } else {
            $storage_facility_status_id = 0;
        }

        // ✅ Handle case when `storage_facility_status_id` is NOT passed
        if ($storage_facility_status_id == 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Missing input parameters: storage_facility_status_id is required."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }


        // Load User Model
        $this->load->model('StorageFacilityModel');

        $storageFacilityStatusData = [
            'status_type_id' => $storage_facility_status_id,
            'updated_at' => date('Y-m-d H:i:s')
        ];

        $storageFacilityStatusDataUpdated = $this->StorageFacilityModel->updateStorageFacilityStatusData($storageFacilityId, $storageFacilityStatusData);

        // Check if at least one update was successful
        if ($storageFacilityStatusDataUpdated) {
            http_response_code(200);
            echo json_encode([
                "status" => "success",
                "message" => "Storage facility status has been updated successfully."
            ], JSON_PRETTY_PRINT);
        } else {
            http_response_code(500);
            echo json_encode([
                "status" => "error",
                "message" => "Failed to update storage facility status."
            ], JSON_PRETTY_PRINT);
        }
        exit;
    }

    public function deleteStorageFacility()
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Invalid request method"]);
            exit;
        }

        if (!$this->input->is_ajax_request()) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Unauthorized request"]);
            exit;
        }

        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "User not logged in"]);
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "Insufficient permissions"]);
            exit;
        }

        $json_input = json_decode(file_get_contents('php://input'), true);
        $storageFacilityId = isset($json_input['storage_facility_id']) ? (int)$json_input['storage_facility_id'] : 0;

        if ($storageFacilityId <= 0) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid storage facility ID"]);
            exit;
        }

        $this->load->model('StorageFacilityModel');
        $this->load->model('CompanyModel');

        $facilityCompanies = $this->StorageFacilityModel->getCompanyIdsByStorageFacilityId($storageFacilityId);
        
        if (empty($facilityCompanies)) {
            http_response_code(404);
            echo json_encode(["status" => "error", "message" => "Storage facility not found"]);
            exit;
        }

        $hasAccess = false;
        foreach ($facilityCompanies as $facilityCompany) {
             $currentCompanyId = $facilityCompany['company_id'];
             $companyUserData = $this->CompanyModel->getCompanyByIdWithUserId($currentCompanyId, $userId);
             if ($companyUserData) {
                 $hasAccess = true;
                 break;
             }
        }
        
        if (!$hasAccess) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Access denied"]);
            exit;
        }

        $deleteData = ['status_type_id' => 3, 'updated_at' => date('Y-m-d H:i:s')];
        $deleted = $this->StorageFacilityModel->updateStorageFacilityStatusData($storageFacilityId, $deleteData);

        if ($deleted) {
            echo json_encode(["status" => "success", "message" => "Storage facility deleted successfully"]);
        } else {
            http_response_code(500);
            echo json_encode(["status" => "error", "message" => "Failed to delete storage facility"]);
        }
        exit;
    }



    public function getAllStorageFacility()
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
        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(401); // Unauthorized
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
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
        $this->load->model('StorageFacilityModel');

        $totalRecords = $this->StorageFacilityModel->countAllStorageFacility();
        $filteredRecords = $this->StorageFacilityModel->countFilteredStorageFacility($search, $userId);
        $result = $this->StorageFacilityModel->getFilteredStorageFacilityById($start, $length, $search, $orderColumn, $orderDir, $userId);

        // Prepare the response data
        $data = [
            'draw' => $draw,
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $result,
        ];

        // Return success response with storage facilities data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Storage facilities retrieved successfully.",
            "result" => $data
        ], JSON_PRETTY_PRINT);
    }


    public function generate_ai_listing()
    {
        header('Content-Type: application/json');

        // 1. Validate Uploads & Input
        $storageFacilityId = $this->input->post('storage_facility_id');
        $unitNumber = $this->input->post('unit_number');

        if (empty($_FILES['images']['name'][0]) || empty($storageFacilityId) || empty($unitNumber)) {
            echo json_encode(['status' => 'error', 'message' => 'Missing images, facility, or unit number.']);
            return;
        }

        // 2. Handle Image Uploads first (to get IDs)
        $this->load->model('UploadModel');
        $uploadIds = [];
        $uploadedPaths = [];

        foreach ($_FILES['images']['name'] as $i => $name) {
            $tmpName = $_FILES['images']['tmp_name'][$i];
            $type = $_FILES['images']['type'][$i];
            $size = $_FILES['images']['size'][$i];
            $error = $_FILES['images']['error'][$i];

            if ($error !== UPLOAD_ERR_OK) continue;
            // Basic validation (can be stricter)
            if (!in_array($type, ['image/jpeg', 'image/png', 'image/webp'])) continue;

            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $newName = uniqid("ai_") . "." . $ext;
            $uploadDir = "assets/uploads/auctions/";
            $uploadPath = $uploadDir . $newName;

            if (move_uploaded_file($tmpName, FCPATH . $uploadPath)) {
                $publicPath = base_url("media/uploads/auctions/" . $newName);
                $uploadData = [
                    'name' => $name,
                    'path' => $publicPath,
                    'type' => $type,
                    'size' => $size,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ];

                $result = $this->UploadModel->insertUploadData($uploadData);
                if (!empty($result['upload_id'])) {
                    $uploadIds[] = $result['upload_id'];
                    $uploadedPaths[] = $publicPath;
                    $localPaths[] = FCPATH . $uploadPath; // Keep local path for AI
                }
            }
        }

        if (empty($uploadIds)) {
            echo json_encode(['status' => 'error', 'message' => 'Failed to process uploaded images. Check directory permissions.']);
            return;
        }

        // 3. Prepare for Gemini (Use the first successful upload)
        $api_key = 'AIzaSyBwvFjOw6fnI2evvJt3tKrQLdKqMNccUEM'; 
        $api_url = "https://generativelanguage.googleapis.com/v1beta/models/gemini-2.0-flash:generateContent?key={$api_key}";

        $firstImagePath = $localPaths[0]; // Use local path directly
        if (!file_exists($firstImagePath)) {
             echo json_encode(['status' => 'error', 'message' => 'Image file not found on server after upload.']);
             return;
        }
        $imageData = base64_encode(file_get_contents($firstImagePath));
        $mimeType = mime_content_type($firstImagePath);

        // 4. Construct Prompt
        $prompt = "Analyze this storage unit image. Identify visible items and count them where possible.
                   Do not use fancy creative words for the title, just describe exactly what it is on a broader term (e.g. 'Camping items', 'Household furniture', 'Office equipment').
                   Generate a list of items with their quantities in the format '1 x Item Name'.
                   Estimate a minimum value (lowball integer).
                   Estimate the storage unit size if possible (e.g. '3m x 3m', '1.5m x 1.5m', 'Locker').
                   Return strictly JSON: { \"title\": \"...\", \"items\": [\"1 x Item A\", \"1 x Item B\"], \"tags\": [\"tag1\", \"tag2\"], \"estimated_value_min\": 50, \"unit_size\": \"...\", \"confidence\": 0.95 }";

        $payload = [
            "contents" => [
                [
                    "parts" => [
                        ["text" => $prompt],
                        ["inline_data" => [
                            "mime_type" => $mimeType,
                            "data" => $imageData
                        ]]
                    ]
                ]
            ]
        ];

        // 5. Call API
        $ch = curl_init($api_url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload));
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        
        if (curl_errno($ch)) {
             log_message('error', 'Gemini API Curl Error: ' . curl_error($ch));
             echo json_encode(['status' => 'error', 'message' => 'AI Service Unavailable: ' . curl_error($ch)]);
             curl_close($ch);
             return;
        }
        curl_close($ch);

        // 6. Parse Response
        $result = json_decode($response, true);
        if (isset($result['error'])) {
             log_message('error', 'Gemini API Error Response: ' . print_r($result['error'], true));
             echo json_encode(['status' => 'error', 'message' => 'Gemini API returned an error: ' . ($result['error']['message'] ?? 'Unknown Error'), 'response' => $response]);
             return;
        }

        $raw_text = $result['candidates'][0]['content']['parts'][0]['text'] ?? '';
        $raw_text = preg_replace('/```json|```/', '', $raw_text);
        $raw_text = trim($raw_text);
        $ai_data = json_decode($raw_text, true);

        // Fallback if AI fails
        if (!$ai_data || !isset($ai_data['title'])) {
            log_message('error', 'AI Parsing Failed. Raw: ' . $raw_text);
            $ai_data = [
                'title' => 'Storage Unit Contents - Unit ' . ($unitNumber ?? 'TBD'),
                'items' => ['1 x miscellaneous items'],
                'tags' => ['miscellaneous'],
                'estimated_value_min' => 50,
                'unit_size' => 'Select Size...',
                'confidence' => 0.5
            ];
        }

        if ($ai_data) {
             // Fetch Global Rules for defaults
             $this->load->model('CompanyModel');
             $this->load->model('AuctionModel');
             $companyData = $this->CompanyModel->getCompanyByUserId($this->session->userdata('auction_user_session_data')['user_id']);
             $globalRules = $companyData ? $this->CompanyModel->getGlobalAuctionRules($companyData['id']) : null;
             
             // Defaults
             $defaultDuration = $globalRules['default_auction_duration'] ?? 7;
             $defaultCleaningDeposit = $globalRules['cleaning_deposit_amount'] ?? 150;

             // Prepare Data
             $aiTitle = $ai_data['title'] ?? '';
             $items = $ai_data['items'] ?? [];
             $itemsStr = "Description\n" . (is_array($items) ? implode("\n", $items) : $items);
             $dbDesc = $aiTitle ? "{$aiTitle}\n\n{$itemsStr}" : $itemsStr;

             // 1. Insert Auction (Draft)
             $auctionData = [
                  'description' => $dbDesc,
                  'unit_number' => $unitNumber,
                  'disclose_unit_number_on_website' => 1,
                  'outstanding_dept' => 0,
                  'starting_price' => $ai_data['estimated_value_min'] ?? 10,
                  'start_date' => date('Y-m-d'),
                  'start_time' => date('h:i A'),
                  'end_date' => date('Y-m-d', strtotime("+{$defaultDuration} days")), // Use Global Rule
                  'end_time' => date('h:i A'),
                  'cleaning_deposit_price' => $defaultCleaningDeposit, // Use Global Rule
                  'cleaning_deposit_payment_type' => 'Cash',
                   'unit_size' => !empty($ai_data['unit_size']) ? $ai_data['unit_size'] : 'Select Size...',
                  'youtube_video_url' => '',
                  'tags' => is_array($ai_data['tags']) ? implode(',', $ai_data['tags']) : ($ai_data['tags'] ?? ''),
                  'additional_features' => '', 
                  'created_at' => date('Y-m-d H:i:s'),
                  'updated_at' => date('Y-m-d H:i:s')
             ];
             
             $auctionInserted = $this->AuctionModel->insertAuctionData($auctionData);
             $newAuctionId = $auctionInserted['auction_id'];

             // 2. Link Facility
             $this->AuctionModel->insertAuctionStorageFacilityData([
                 'storage_facility_id' => $storageFacilityId,
                 'auction_id' => $newAuctionId,
                 'created_at' => date('Y-m-d H:i:s'),
                 'updated_at' => date('Y-m-d H:i:s')
             ]);

             // 3. Link Status (6 = Draft)
             $this->AuctionModel->insertAuctionStatusTypeData([
                 'auction_type_id' => 6,
                 'auction_id' => $newAuctionId,
                 'created_at' => date('Y-m-d H:i:s'),
                 'updated_at' => date('Y-m-d H:i:s')
             ]);

             // 4. Link Category (1 = Default)
             $this->AuctionModel->insertAuctionCategoryData([
                 'auction_category_id' => 1, 
                 'auction_id' => $newAuctionId,
                 'created_at' => date('Y-m-d H:i:s'),
                 'updated_at' => date('Y-m-d H:i:s')
             ]);

             // 5. Link Images
             if (!empty($uploadIds)) {
                 $auctionUploadData = [];
                 foreach ($uploadIds as $uId) {
                     $auctionUploadData[] = [
                         'auction_id' => $newAuctionId,
                         'upload_id' => $uId,
                         'created_at' => date('Y-m-d H:i:s'),
                         'updated_at' => date('Y-m-d H:i:s')
                     ];
                 }
                 $this->AuctionModel->insertAuctionUploadData($auctionUploadData);
             }

             echo json_encode(['status' => 'success', 'data' => $ai_data, 'upload_ids' => $uploadIds, 'auction_id' => $newAuctionId]);
        } else {
             log_message('error', 'AI Parsing Failed. Raw: ' . $response);
             echo json_encode(['status' => 'error', 'message' => 'AI failed to interpret the image.', 'raw_response' => $response]);
        }
    }

    public function save_ai_auction()
    {
        header('Content-Type: application/json');

        // Check Session
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
            return;
        }
        
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];

        $raw_input = file_get_contents('php://input');
        log_message('error', 'SAVE_AI_AUCTION RAW INPUT: ' . $raw_input);

        $json_input = json_decode($raw_input, true);
        
        if (!$json_input) {
            log_message('error', 'SAVE_AI_AUCTION FAILED TO DECODE JSON.');
            echo json_encode(['status' => 'error', 'message' => 'Invalid data provided.']);
            return;
        }

        // Validate Unit Size is present and not 'Unknown' or 'Select Size...'
        if (empty($json_input['unit_size']) || strtolower($json_input['unit_size']) === 'unknown' || $json_input['unit_size'] === 'Select Size...') {
             log_message('error', 'SAVE_AI_AUCTION BLOCKED: Unit Size missing or invalid. Value: ' . ($json_input['unit_size'] ?? 'NULL') . ' Payload: ' . $raw_input);
             echo json_encode(['status' => 'error', 'message' => 'Unit Size is required. Please select a valid size from the dropdown.']);
             return;
        }

        log_message('error', 'SAVE_AI_AUCTION JSON INPUT: ' . print_r($json_input, true));

        $this->load->model('AuctionModel');
        $this->load->model('StorageFacilityModel');

        // 1. Prepare Data
        $auctionId = $json_input['auction_id'] ?? null;
        $title = $json_input['auction_title'] ?? '';
        $desc = $json_input['auction_description'] ?? '';
        $dbDesc = $title ? "{$title}\n\n{$desc}" : $desc;

        $auctionData = [
             'description' => $dbDesc,
             'unit_number' => $json_input['unit_number'] ?? '',
             'disclose_unit_number_on_website' => 1,
             'outstanding_dept' => !empty($json_input['debt_amount']) ? (float)$json_input['debt_amount'] : 0,
             'starting_price' => !empty($json_input['start_bid']) ? (float)$json_input['start_bid'] : 0,
             'start_date' => date('Y-m-d'),
             'start_time' => date('H:i:s'),
             'end_date' => date('Y-m-d', strtotime("+" . ($json_input['duration'] ?? 7) . " days")),
             'end_time' => date('H:i:s'),
             'cleaning_deposit_price' => !empty($json_input['cleaning_deposit']) ? (float)$json_input['cleaning_deposit'] : 0,
             'cleaning_deposit_payment_type' => !empty($json_input['cleaning_deposit_payment_type']) ? trim($json_input['cleaning_deposit_payment_type']) : 'Cash',
             'accept_cash' => !empty($json_input['accept_cash']) ? (int)$json_input['accept_cash'] : 0,
             'accept_card' => !empty($json_input['accept_card']) ? (int)$json_input['accept_card'] : 0,
             'unit_size' => trim($json_input['unit_size']),
             'youtube_video_url' => '',
             'tags' => $json_input['auction_tags'] ?? '',
             'additional_features' => '',
             'updated_at' => date('Y-m-d H:i:s')
        ];

        // UPDATE EXISTING DRAFT
        if ($auctionId) {
             $this->AuctionModel->updateAuctionData($auctionId, $auctionData);
             $this->AuctionModel->updateAuctionStatus($auctionId, 1); // Set to Active

             echo json_encode(['status' => 'success', 'message' => 'Auction published successfully.', 'auction_id' => $auctionId]);
             return;
        }

        // INSERT NEW (One-step publish fallback)
        $auctionData['created_at'] = date('Y-m-d H:i:s');
        $auctionDataInserted = $this->AuctionModel->insertAuctionData($auctionData); 
        
        if ($auctionDataInserted) {
            $auctionId = $auctionDataInserted['auction_id'];

            // Link Facility
            $this->AuctionModel->insertAuctionStorageFacilityData([
                'storage_facility_id' => $json_input['storage_facility_id'],
                'auction_id' => $auctionId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Link Status (1 = Active for immediate publish)
            $this->AuctionModel->insertAuctionStatusTypeData([
                'auction_type_id' => 1,
                'auction_id' => $auctionId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Link Category
            $this->AuctionModel->insertAuctionCategoryData([
                'auction_category_id' => 1, 
                'auction_id' => $auctionId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            // Link Images
            if (!empty($json_input['upload_ids']) && is_array($json_input['upload_ids'])) {
                $uploadRelationshipData = [];
                foreach ($json_input['upload_ids'] as $uploadId) {
                    $uploadRelationshipData[] = [
                        'auction_id' => $auctionId,
                        'upload_id' => $uploadId,
                        'created_at' => date("Y-m-d H:i:s"),
                        'updated_at' => date("Y-m-d H:i:s")
                    ];
                }
                $this->AuctionModel->insertAuctionUploadData($uploadRelationshipData);
            }
            
            echo json_encode(['status' => 'success', 'message' => 'Auction published successfully.', 'auction_id' => $auctionId]);
        } else {
             echo json_encode(['status' => 'error', 'message' => 'Database Insert Failed']);
        }


    }


    public function getAllStorageFacilityById()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid request method. Only POST requests are allowed."
            ], JSON_PRETTY_PRINT);
            http_response_code(405); // Method Not Allowed
            exit;
        }

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403); // Forbidden
            exit;
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
            http_response_code(400); // Bad Request
            exit;
        }

        // Get storage facility ID from JSON input
        $storageFacilityId = isset($json_input['storageFacilityId']) ? (int)$json_input['storageFacilityId'] : 0;

        // Validate auction ID
        if ($storageFacilityId <= 0) {
            echo json_encode([
                "status" => "error",
                "message" => "Invalid or missing storage facility ID."
            ], JSON_PRETTY_PRINT);
            http_response_code(400); // Bad Request
            exit;
        }

        // Load Auction Model
        $this->load->model('StorageFacilityModel');

        // Fetch bidder profile details from the database
        $storageDacilityData = $this->StorageFacilityModel->getStorageFacilityInformationById($storageFacilityId);

        // If user profile not found, return error response
        if (!$storageDacilityData) {
            http_response_code(404); // Not Found
            echo json_encode([
                "status" => "error",
                "message" => "No storage facility is found."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Return success response with user profile data
        http_response_code(200); // Success
        echo json_encode([
            "status" => "success",
            "message" => "Your storage facility data is retrieved successfully.",
            "data" => $storageDacilityData
        ], JSON_PRETTY_PRINT);
    }

    public function importStorageFacilities()
    {
        header('Content-Type: application/json');

        // Ensure it's a POST request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo json_encode(["status" => "error", "message" => "Method not allowed"]);
            exit;
        }

        // Check login
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode(["status" => "error", "message" => "User not logged in"]);
            exit;
        }

        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        // Check permissions (Super Admin or Facility Owner)
        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(403);
            echo json_encode(["status" => "error", "message" => "Insufficient permissions"]);
            exit;
        }

        // Check if file is uploaded
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "No valid file uploaded"]);
            exit;
        }

        $fileTmpPath = $_FILES['csv_file']['tmp_name'];
        $fileName = $_FILES['csv_file']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

        if ($fileExtension !== 'csv') {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Invalid file type. Please upload a CSV file."]);
            exit;
        }

        $this->load->model('CompanyModel');
        $this->load->model('StorageFacilityModel');

        // Get User's Company
        $companyData = $this->CompanyModel->getCompanyByUserId($userId);
        if (!$companyData) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "No company associated with this user."]);
            exit;
        }
        $companyId = $companyData['company_id'] ?? $companyData['id'];

        // Parse CSV (robustly to support quoted commas/newlines)
        $csvData = [];
        $header = [];
        $handle = fopen($fileTmpPath, 'r');
        if ($handle === false) {
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "Unable to read uploaded CSV file."]);
            exit;
        }

        $header = fgetcsv($handle);
        if (empty($header) || !is_array($header)) {
            fclose($handle);
            http_response_code(400);
            echo json_encode(["status" => "error", "message" => "CSV header row is missing or invalid."]);
            exit;
        }

        $headerCount = count($header);
        while (($row = fgetcsv($handle)) !== false) {
            if (!is_array($row)) {
                continue;
            }
            if (count($row) < $headerCount) {
                $row = array_pad($row, $headerCount, '');
            }
            $csvData[] = $row;
        }
        fclose($handle);

        // Normalize header columns to indices for robustness
        $normalizedHeaderMap = [];
        foreach ((array)$header as $idx => $colName) {
            $key = strtolower(trim((string)$colName));
            if ($key !== '') {
                $normalizedHeaderMap[$key] = $idx;
            }
        }
        
        // Required columns check
        $requiredCols = ['business name'];
        foreach ($requiredCols as $col) {
            if (!isset($normalizedHeaderMap[$col])) {
                 http_response_code(400);
                 echo json_encode(["status" => "error", "message" => "Missing required column: $col"]);
                 exit;
            }
        }

        $importedCount = 0;
        $errors = [];

        foreach ($csvData as $rowIndex => $row) {
            // Skip empty rows
            if (empty($row)) {
                continue;
            }

            // Helper to get data safely (case-insensitive header names)
            $getVal = function($colName) use ($row, $normalizedHeaderMap) {
                $key = strtolower(trim((string)$colName));
                return isset($normalizedHeaderMap[$key]) && isset($row[$normalizedHeaderMap[$key]]) ? trim((string)$row[$normalizedHeaderMap[$key]]) : '';
            };

            $name = $getVal('Business name');
            if (empty($name)) {
                continue; // Skip if no business name
            }

            // Prepare Data
            $address = $getVal('Address line 1');
            $city = $getVal('Locality');
            $stateRaw = $getVal('Administrative area');
            $countryCode = $getVal('Country/Region');
            $country = ($countryCode === 'AU') ? 'Australia' : $countryCode;
            $zip = $getVal('Postcode');
            $phone = $getVal('Primary phone');
            $website = $getVal('Website');
            $description = $getVal('From the business');

            // Normalize state: template often has AU abbreviations (QLD, NSW, etc)
            $state = $stateRaw;
            $stateUpper = strtoupper(trim((string)$stateRaw));
            $stateMap = [
                'NSW' => 'New South Wales',
                'VIC' => 'Victoria',
                'QLD' => 'Queensland',
                'WA' => 'Western Australia',
                'SA' => 'South Australia',
                'TAS' => 'Tasmania',
                'ACT' => 'Australian Capital Territory',
                'NT' => 'Northern Territory'
            ];
            if (isset($stateMap[$stateUpper])) {
                $state = $stateMap[$stateUpper];
            }
            
            // Hours Construction
            $hours = [];
            $days = ['Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday', 'Sunday'];
            foreach ($days as $day) {
                $val = $getVal("$day hours");
                if ($val) $hours[] = "$day: $val";
            }
            $officeHours = !empty($hours) ? implode(", ", $hours) : 'Mon-Fri 9am-5pm';

            // Insert Storage Facility
            $storageFacilityData = [
                'name' => $name,
                'website' => $website,
                'description' => $description,
                'timezone' => 'Australia/Brisbane', // Default
                'contact_email' => '', // Not in CSV
                'additional_email' => '',
                'office_hours' => $officeHours,
                'payout_details' => '',
                'youtube_video_url' => '',
                'branding_logo' => '',
                'notice_to_buyers' => '',
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $insertedFacility = $this->StorageFacilityModel->insertStorageFacilityData($storageFacilityData);
            $facilityId = $insertedFacility['storage_facility_id'];

            // Insert Location
            $locationData = [
                'street_address' => $address,
                'apt_suite' => '',
                'city' => $city,
                'postal_code' => $zip,
                'state' => $state,
                'country' => $country,
                'phone' => $phone,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ];
            $insertedLocation = $this->StorageFacilityModel->insertStorageFacilityLocationData($locationData);
            $locationId = $insertedLocation['storage_facility_location_id'];

            // Insert Relationships
            $this->StorageFacilityModel->insertStorageFacilityLocationRelationshipData([
                'storage_facility_id' => $facilityId,
                'location_id' => $locationId
            ]);

            $this->db->insert('tbl_storage_facility_company_relationship', [
                'storage_facility_id' => $facilityId,
                'company_id' => $companyId,
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

             $this->StorageFacilityModel->insertStorageFacilityStatusData([
                'storage_facility_id' => $facilityId,
                'status_type_id' => 1, // Active
                'created_at' => date('Y-m-d H:i:s'),
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            $importedCount++;
        }

        echo json_encode([
            "status" => "success",
            "message" => "Successfully imported $importedCount locations."
        ]);
        exit;
    }
}
