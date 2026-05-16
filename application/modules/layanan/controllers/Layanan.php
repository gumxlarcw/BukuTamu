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

    // Layanan::go_if_not_set() — HAPUS pemakaian ' '
    public function go_if_not_set()
    {
        // Jika belum pilih layanan → balik ke halaman layanan
        if (!$this->session->userdata('jenis_layanan')) {
            return redirect('layanan');
        }

        // Kalau sudah pilih → langsung masuk form pertama
        return redirect('selamat_datang'); 
    }


    public function go() {
        $layanan = trim($this->input->post('layanan') ?? '');

        if ($layanan === '') {
            $this->session->set_flashdata('error', 'Harap pilih layanan terlebih dahulu.');
            return redirect('layanan');
        }

        // set session yang dipakai seluruh alur
        $this->session->set_userdata('jenis_layanan', $layanan);

        // Reset pilihan status (existing/new) setiap kali layanan diganti
        $this->session->unset_userdata('status_pilihan');

        // Jika ada konteks kunjungan dalam session, batasi update supaya tidak mengubah data historis.
        $id_kunjungan = (int) ($this->session->userdata('id_kunjungan') ?? 0);
        $id_user = (int) ($this->session->userdata('id_user') ?? 0);
        if ($id_kunjungan > 0 && $id_user > 0) {
            $this->db
                ->where('id_kunjungan', $id_kunjungan)
                ->where('id_user', $id_user)
                ->where('DATE(date_visit)', date('Y-m-d'))
                ->update('tamdes_kunjungan', ['jenis_layanan' => $layanan]);
        }

        return redirect('selamat_datang/check');
    }

}
