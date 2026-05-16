<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Recognize extends MX_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('recognize/m_recognize');
        $this->load->database();
        $this->load->library('session');
    }

    /**
     * Halaman utama Face Recognition
     */
    public function index() {
        // Hindari duplikasi UI: alur utama kiosk menggunakan selamat_datang/recognize.
        if (!trim((string) ($this->session->userdata('jenis_layanan') ?? ''))) {
            return redirect('layanan');
        }
        return redirect('selamat_datang/recognize');
    }

    private function generate_no_antrian()
    {
        $tanggal = date('Y-m-d');

        $last = $this->db
            ->select('nomor_antrian')
            ->where('DATE(date_visit)', $tanggal)
            ->like('nomor_antrian', 'A')
            ->order_by('date_visit', 'DESC')
            ->limit(1)
            ->get('tamdes_kunjungan')
            ->row();

        if ($last && isset($last->nomor_antrian) && preg_match('/A(\d+)/', (string) $last->nomor_antrian, $match)) {
            $next = (int) $match[1] + 1;
        } else {
            $next = 1;
        }

        return 'A' . str_pad($next, 3, '0', STR_PAD_LEFT);
    }

    /**
     * Mengambil data wajah yang sudah tersimpan (nama + descriptor)
     * Dipakai oleh face-api.js untuk membuat LabeledFaceDescriptors
     */
    public function data() {
        header('Content-Type: application/json');

        $faces = $this->m_recognize->get_known_faces();
        echo json_encode($faces);
    }

    /**
     * Menerima hasil konfirmasi wajah dan menyimpannya ke tabel tamdes_kunjungan
     * Dipanggil via fetch() dari view_recognize
     */
    public function save() {
        header('Content-Type: application/json');

        $input = json_decode(file_get_contents('php://input'), true);

        $name = trim($input['name'] ?? '');
        $manualId = trim($input['manualId'] ?? '');

        // Pastikan data valid
        if (empty($name) && empty($manualId)) {
            echo json_encode(['status' => 'error', 'message' => '❌ Data tidak valid.']);
            return;
        }

        // Jika user memilih tamu manual dari dropdown
        if (!empty($manualId)) {
            $id_user = $manualId;
        } else {
            // Cari berdasarkan nama hasil deteksi wajah
            $user = $this->db->get_where('tamdes_buku', ['nama' => $name])->row();
            $id_user = $user->id_user ?? null;
        }

        if (!$id_user) {
            echo json_encode(['status' => 'error', 'message' => '❌ Tamu tidak ditemukan dalam database.']);
            return;
        }

        $jenis_layanan = trim((string) ($this->session->userdata('jenis_layanan') ?? ''));
        if ($jenis_layanan === '') {
            // Fallback optional bila endpoint ini dipakai dari context non-session.
            $jenis_layanan = trim((string) ($input['jenis_layanan'] ?? ''));
        }

        if ($jenis_layanan === '') {
            echo json_encode([
                'status' => 'error',
                'message' => '❌ Jenis layanan belum dipilih. Silakan pilih layanan terlebih dahulu.'
            ]);
            return;
        }

        // Cek apakah sudah ada kunjungan hari ini untuk user ini
        $existing = $this->db
            ->where('id_user', (int) $id_user)
            ->where('DATE(date_visit)', date('Y-m-d'))
            ->order_by('date_visit', 'DESC')
            ->limit(1)
            ->get('tamdes_kunjungan')
            ->row();

        // Cek jenis layanan untuk nomor antrian
        $no_antrian = null;
        $skip_nomor = ($jenis_layanan === 'Lainnya' || $jenis_layanan === 'Keperluan Pimpinan');

        if ($existing) {
            $no_antrian = trim((string) ($existing->nomor_antrian ?? ''));
            if (!$skip_nomor && $no_antrian === '') {
                $no_antrian = $this->generate_no_antrian();
                $this->db
                    ->where('id_kunjungan', (int) $existing->id_kunjungan)
                    ->update('tamdes_kunjungan', [
                        'nomor_antrian' => $no_antrian,
                        'jenis_layanan' => $jenis_layanan
                    ]);

                $dberr = $this->db->error();
                if (!empty($dberr['message'])) {
                    log_message('error', 'recognize::save - gagal update kunjungan: ' . print_r($dberr, true));
                    echo json_encode([
                        'status' => 'error',
                        'message' => '⚠️ Gagal menyimpan update kunjungan.'
                    ]);
                    return;
                }
            }

            echo json_encode([
                'status' => 'success',
                'message' => '✅ Kunjungan sudah tercatat hari ini.',
                'nomor_antrian' => $no_antrian,
                'print_url' => $no_antrian ? base_url('antrian/cetak/' . $no_antrian) : null
            ]);
            return;
        }

        // Siapkan data untuk disimpan
        if (!$skip_nomor) {
            $no_antrian = $this->generate_no_antrian();
        }
        $data = [
            'id_user' => $id_user,
            'jenis_layanan' => $jenis_layanan,
            'date_visit' => date('Y-m-d H:i:s'),
            'status' => 'antri',
            'nomor_antrian' => $no_antrian
        ];

        // Simpan ke tabel tamdes_kunjungan
        log_message('debug', 'recognize::save - insert payload: ' . json_encode($data));
        $this->db->insert('tamdes_kunjungan', $data);

        // Cek hasil insert
        if ($this->db->affected_rows() > 0) {
            echo json_encode([
                'status' => 'success',
                'message' => '✅ Kunjungan berhasil disimpan.',
                'nomor_antrian' => $no_antrian,
                'print_url' => $no_antrian ? base_url('antrian/cetak/' . $no_antrian) : null
            ]);
        } else {
            $dberror = $this->db->error();
            log_message('error', 'recognize::save - gagal insert kunjungan: ' . print_r($dberror, true));
            echo json_encode(['status' => 'error', 'message' => '⚠️ Gagal menyimpan data kunjungan.']);
        }
    }
}
