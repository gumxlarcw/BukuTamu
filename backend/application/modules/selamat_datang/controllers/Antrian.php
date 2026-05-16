<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Antrian extends MX_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper('url');
    }

    public function cetak($no_antrian = null) {
        if (!$no_antrian) {
            show_404();
        }

        $data['no_antrian'] = $no_antrian;

        // Panggil view dari modul selamat_datang
        $this->load->view('selamat_datang/cetak_antrian', $data);
    }
}
