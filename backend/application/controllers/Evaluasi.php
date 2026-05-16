<?php
defined('BASEPATH') OR exit('No direct script access allowed');
/**
 * @property CI_Loader $load
 * @property CI_DB_query_builder $db
 * @property CI_Input $input
 */

class Evaluasi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->database();
    }

    // Halaman tablet standby
    public function tunggu() {
        $this->load->view('evaluasi/tunggu_evaluasi');
    }

    // Endpoint polling untuk evaluasi
    public function kunjungan_terbaru() {
        $row = $this->db
            ->order_by('id_kunjungan', 'desc')
            ->get_where('tamdes_kunjungan', ['status' => 'menunggu_evaluasi'])
            ->row();

        if ($row) {
            echo json_encode(['status' => 'ada', 'id_kunjungan' => $row->id_kunjungan]);
        } else {
            echo json_encode(['status' => 'kosong']);
        }
    }

    // Tampilkan form evaluasi
    public function isi($id_kunjungan) {
        $id_kunjungan = (int) $id_kunjungan;
        $kunjungan = $this->db->get_where('tamdes_kunjungan', ['id_kunjungan' => $id_kunjungan])->row();

        if (!$kunjungan) {
            show_404();
        }

        // Cegah evaluasi ganda
        if ($kunjungan->status === 'selesai') {
            show_error('Evaluasi sudah selesai.');
        }

        $this->load->view('evaluasi/form_evaluasi', [
            'id_kunjungan' => $id_kunjungan,
            'indikator' => $this->indikator_list()
        ]);
    }

    // Submit evaluasi dari pengunjung
    public function submit() {
        $id_kunjungan = $this->input->post('id_kunjungan', TRUE);
        $skor = $this->input->post('skor_keseluruhan', TRUE);
        $kepentingan = $this->input->post('kepentingan');
        $kepuasan = $this->input->post('kepuasan');

        if (
            !$id_kunjungan || !$skor || !is_numeric($skor) || $skor < 1 || $skor > 10 ||
            !is_array($kepentingan) || !is_array($kepuasan)
        ) {
            show_error('Data evaluasi tidak valid.');
        }

        $kunjungan = $this->db->get_where('tamdes_kunjungan', ['id_kunjungan' => $id_kunjungan])->row();
        if (!$kunjungan || $kunjungan->status === 'selesai') {
            show_error('Evaluasi tidak dapat diproses.');
        }

        // Simpan detail evaluasi per indikator
        foreach ($kepentingan as $no => $val_kepentingan) {
            $val_kepuasan = isset($kepuasan[$no]) ? $kepuasan[$no] : null;
            if (!$val_kepuasan || $val_kepentingan < 1 || $val_kepuasan < 1) continue;

            $this->db->insert('tamdes_evaluasi_detail', [
                'id_kunjungan' => $id_kunjungan,
                'indikator_id' => $no,
                'kepentingan' => $val_kepentingan,
                'kepuasan' => $val_kepuasan
            ]);
        }

        // Update hasil evaluasi ringkasan
        $this->db->where('id_kunjungan', $id_kunjungan)->update('tamdes_kunjungan', [
            'rating_pengunjung' => $skor,
            'status' => 'selesai'
        ]);

        $this->load->view('evaluasi/selesai');
    }

    // Daftar indikator evaluasi
    private function indikator_list() {
        return [
            1 => 'Informasi pelayanan tersedia secara elektronik maupun non-elektronik.',
            2 => 'Persyaratan pelayanan mudah dipenuhi.',
            3 => 'Prosedur pelayanan mudah diikuti.',
            4 => 'Waktu pelayanan sesuai dengan yang ditetapkan.',
            5 => 'Biaya pelayanan sesuai ketentuan.',
            6 => 'Produk pelayanan sesuai yang dijanjikan.',
            7 => 'Sarana prasarana nyaman.',
            8 => 'Data mudah diakses.',
            9 => 'Petugas merespon dengan baik.',
            10 => 'Petugas memberi informasi jelas.',
            11 => 'Fasilitas pengaduan mudah diketahui.',
            12 => 'Proses pengaduan jelas dan tidak rumit.',
            13 => 'Tidak ada diskriminasi pelayanan.',
            14 => 'Tidak ada kecurangan/pelayanan di luar prosedur.',
            15 => 'Tidak ada penerimaan gratifikasi.',
            16 => 'Tidak ada pungutan liar.',
            17 => 'Tidak ada praktik percaloan.'
        ];
    }
}
