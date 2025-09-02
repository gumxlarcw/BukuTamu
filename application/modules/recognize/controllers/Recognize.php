<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Recognize extends MX_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('recognize/m_recognize');
    }

    public function index() {
        $this->load->view('recognize/view_recognize');
    }

    public function data() {
        header('Content-Type: application/json');
        $faces = $this->m_recognize->get_known_faces();
        echo json_encode($faces);
    }
}
