<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Home extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
    }

    public function index()
    {
        $scripts = [
            base_url('assets/js/script.js'),
            base_url('assets/js/ajax/auctions/get_auctions.js'),
            base_url('assets/js/ajax/auctions/get_top_3_auctions.js')
        ];

        if ($this->session->userdata('auction_user_session_data')) {

            $userSessionData = $this->session->userdata('auction_user_session_data');
            $userRoleId = $userSessionData['role_id'];

            if ($userRoleId == 2) {
                $scripts[] = base_url('assets/js/ajax/dashboard/bidder/initial.js');
            }
        }


        $data['scripts'] = $scripts;

        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('home/index');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts', $data);
    }
}
