<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Loader $load
 * @property CI_Input $input
 * @property CI_Session $session
 * @property CI_DB_query_builder $db
 */
class Layanan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->library('session');
        $this->load->database(); // <--- tambahkan
    }

    public function index() {
        $this->load->view('form/layanan');
    }

    public function go_if_not_set()
    {
        if (!$this->session->userdata('layanan_terpilih')) {
            $this->session->set_userdata('layanan_terpilih', ' ');
            $this->session->set_userdata('jenis_layanan', ' ');
        }
        redirect('selamat_datang');
    }

    public function go() {
        $layanan = trim($this->input->post('layanan'));

        if ($layanan === '' || $layanan === ' ') {
            $this->session->set_flashdata('error', 'Harap pilih layanan terlebih dahulu.');
            redirect('layanan');
            return;
        }

        $this->session->set_userdata('jenis_layanan', $layanan);

        $id_kunjungan = $this->session->userdata('id_kunjungan');
        if ($id_kunjungan) {
            $this->db->where('id_kunjungan', $id_kunjungan)
                     ->update('tamdes_kunjungan', ['jenis_layanan' => $layanan]);
        }

        redirect(base_url("selamat_datang/check"));
    }
}
