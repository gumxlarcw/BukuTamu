<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Loader $load
 * @property CI_Input $input
 * @property CI_Session $session
 */

 class Layanan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session'); // pastikan session diload
    }

    public function index() {
        $this->load->view('form/layanan');
    }

    public function go_if_not_set()
    {
        if (!$this->session->userdata('layanan_terpilih')) {
            $this->session->set_userdata('layanan_terpilih', 1);
            $this->session->set_userdata('jenis_layanan', 'Perpustakaan');
        }
        redirect('selamat_datang');
    }

    public function go() {
        $layanan = $this->input->post('layanan');
        $this->session->set_userdata('jenis_layanan', $layanan);
        redirect(base_url("selamat_datang/check"));
    }
}
