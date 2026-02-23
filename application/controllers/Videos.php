<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Videos extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('VideosModel');
    }

    public function index()
    {
        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('videos/index');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts');
    }

    public function api_list()
    {
        $data = $this->VideosModel->get_all_grouped_by_season();
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode([
                'success' => true,
                'data' => $data
            ]));
    }
}
