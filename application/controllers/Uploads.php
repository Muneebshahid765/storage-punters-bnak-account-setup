<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Uploads extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Australia/Brisbane');
    }

    public function bulkUploadImages()
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

        // Ensure it's an AJAX request
        if (!$this->input->is_ajax_request()) {
            echo json_encode([
                "status" => "error",
                "message" => "Unauthorized request. This endpoint accepts only AJAX requests."
            ], JSON_PRETTY_PRINT);
            http_response_code(403);
            exit;
        }

        // Check if user is logged in
        if (!$this->session->userdata('auction_user_session_data')) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "User is not logged in. Please log in to access your profile."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        // Get user session
        $userSessionData = $this->session->userdata('auction_user_session_data');
        $userId = $userSessionData['user_id'];
        $userRoleId = $userSessionData['role_id'];

        if (!in_array($userRoleId, [1, 3])) {
            http_response_code(401);
            echo json_encode([
                "status" => "error",
                "message" => "You do not have the required permissions to access this request."
            ], JSON_PRETTY_PRINT);
            exit;
        }

        if (empty($_FILES['images'])) {
            http_response_code(400);
            echo json_encode(['status' => 'error', 'message' => 'No files uploaded']);
            return;
        }

        $this->load->model('UploadModel');
        $uploadIds = [];
        $uploadedPaths = [];

        foreach ($_FILES['images']['name'] as $i => $name) {
            $tmpName = $_FILES['images']['tmp_name'][$i];
            $type = $_FILES['images']['type'][$i];
            $size = $_FILES['images']['size'][$i];
            $error = $_FILES['images']['error'][$i];

            if ($error !== UPLOAD_ERR_OK) continue;
            if (!in_array($type, ['image/jpeg', 'image/png'])) continue;
            if ($size > 1024 * 1024) continue;

            $ext = pathinfo($name, PATHINFO_EXTENSION);
            $newName = uniqid("img_") . "." . $ext;
            $uploadDir = "assets/uploads/auctions/";
            $uploadPath = $uploadDir . $newName;

            if (move_uploaded_file($tmpName, FCPATH . $uploadPath)) {
                $publicPath = base_url("media/uploads/auctions/" . $newName); // This will be accessible publicly

                $uploadData = [
                    'name' => $name,
                    'path' => $publicPath, // Save public URL to DB
                    'type' => $type,
                    'size' => $size,
                    'created_at' => date("Y-m-d H:i:s"),
                    'updated_at' => date("Y-m-d H:i:s")
                ];

                $result = $this->UploadModel->insertUploadData($uploadData);
                if (!empty($result['upload_id'])) {
                    $uploadIds[] = $result['upload_id'];
                    $uploadedPaths[] = $publicPath;
                }
            }
        }

        http_response_code(200);
        echo json_encode([
            'status' => 'success',
            'upload_ids' => $uploadIds
        ]);
    }

    public function getUploadImage($filename = null)
    {
        $filePath = FCPATH . 'assets/uploads/auctions/' . $filename;

        if ($filename && file_exists($filePath)) {
            // Get the MIME type
            $mimeType = mime_content_type($filePath);

            // Serve the file
            header('Content-Type: ' . $mimeType);
            header('Content-Length: ' . filesize($filePath));
            readfile($filePath);
            exit;
        } else {
            // Show 404 if not found
            show_404();
        }
    }
}
