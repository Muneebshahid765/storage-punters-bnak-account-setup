<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Pages extends CI_Controller
{

    public function __construct()
    {
        parent::__construct();
        date_default_timezone_set('Australia/Brisbane');
    }

    public function contactUs()
    {
        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('pages/contact_us');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts');
    }

    public function aboutUs()
    {
        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('pages/about_us');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts');
    }

    public function faqs()
    {
        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('pages/faqs');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts');
    }

    public function termsAndConditions()
    {
        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('pages/terms_and_conditions');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts');
    }


    public function privacyPolicy()
    {
        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('pages/privacy_policy');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts');
    }


    public function refundPolicy()
    {
        $this->load->view('layout/head');
        $this->load->view('layout/header');
        $this->load->view('pages/refund_policy');
        $this->load->view('layout/footer');
        $this->load->view('layout/scripts');
    }
}
