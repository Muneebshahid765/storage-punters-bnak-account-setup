<?php
defined('BASEPATH') or exit('No direct script access allowed');

class AdminDisputes extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('DisputeModel');
        // Add session/auth check here if needed
    }

    public function index()
    {
        $disputes = $this->DisputeModel->get_all_disputes();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => true, 'data' => $disputes]));
    }

    public function review()
    {
        $id = $this->input->post('dispute_id');
        $status = $this->input->post('status') ?: 'Under Review';

        if (!$id) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'Dispute ID is required']));
        }

        $result = $this->DisputeModel->update_dispute($id, ['status' => $status]);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => $result, 'message' => $result ? 'Dispute status updated' : 'Update failed']));
    }

    public function resolve()
    {
        $id = $this->input->post('dispute_id');
        $resolution = $this->input->post('resolution');

        if (!$id || !$resolution) {
            return $this->output
                ->set_content_type('application/json')
                ->set_output(json_encode(['success' => false, 'message' => 'ID and resolution are required']));
        }

        $data = [
            'status' => 'Resolved',
            'resolution' => $resolution
        ];

        $result = $this->DisputeModel->update_dispute($id, $data);
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode(['success' => $result, 'message' => $result ? 'Dispute resolved' : 'Resolution failed']));
    }
}
