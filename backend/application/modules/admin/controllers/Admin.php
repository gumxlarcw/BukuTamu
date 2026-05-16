<?php
defined('BASEPATH') OR exit('No direct script access allowed');

/**
 * @property CI_Loader $load
 * @property CI_Input $input
 * @property CI_Session $session
 * @property M_admin $admin
 * @property CI_DB_query_builder $db
 */
class Admin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->helper(['url']);
        $this->load->library('session');
        $this->load->model('admin/M_admin', 'admin');
    }

    private function check_login() {
        if (!$this->session->userdata('logged_in')) {
            redirect('admin');
            exit;
        }
    }

    public function index() {
        if ($this->session->userdata('logged_in')) {
            redirect('admin/dashboard');
        }
        $this->load->view('admin/login');
    }

    public function login() {
        $username = $this->input->post('username', TRUE);
        $password = $this->input->post('password', TRUE);

        $envUsername = getenv('ADMIN_USERNAME') ?: '';
        $envPasswordHash = getenv('ADMIN_PASSWORD_HASH') ?: '';

        // Production: wajib pakai ENV (hindari kredensial hardcoded di repo)
        if (ENVIRONMENT === 'production') {
            if ($envUsername === '' || $envPasswordHash === '') {
                $this->session->set_flashdata('error', 'Konfigurasi admin belum diset. Hubungi administrator.');
                return redirect('admin');
            }

            $isValid = hash_equals($envUsername, (string) $username) && password_verify((string) $password, $envPasswordHash);
            if ($isValid) {
                $this->session->set_userdata([
                    'logged_in' => TRUE,
                    'admin_username' => $envUsername,
                ]);
                return redirect('admin/dashboard');
            }

            $this->session->set_flashdata('error', 'Username atau password salah.');
            return redirect('admin');
        }

        // Non-production: fallback untuk kemudahan dev jika ENV belum diset
        $fallbackUsername = ($envUsername !== '') ? $envUsername : 'admin';
        $isValid = FALSE;
        if ($envPasswordHash !== '') {
            $isValid = hash_equals($fallbackUsername, (string) $username) && password_verify((string) $password, $envPasswordHash);
        } else {
            $isValid = ($username === $fallbackUsername && $password === 'admin123');
        }

        if ($isValid) {
            $this->session->set_userdata([
                'logged_in' => TRUE,
                'admin_username' => $fallbackUsername,
            ]);
            return redirect('admin/dashboard');
        }

        $this->session->set_flashdata('error', 'Username atau password salah.');
        return redirect('admin');
    }

    public function tambah_kunjungan_manual()
    {
        $this->check_login();

        $today = date('Y-m-d');

        // daftar tamu hari ini (tidak wajib diubah)
        $this->db->select('tamdes_buku.id_user, tamdes_buku.nama, tamdes_buku.nama_instansi');
        $this->db->join('tamdes_kunjungan', 'tamdes_buku.id_user = tamdes_kunjungan.id_user', 'left');
        $this->db->where('DATE(tamdes_kunjungan.date_visit)', $today);
        $this->db->group_by('tamdes_buku.id_user');
        $this->db->order_by('tamdes_buku.nama', 'ASC'); // ✅ urutkan abjad
        $data['tamu_hari_ini'] = $this->db->get('tamdes_buku')->result();

        // semua tamu untuk dropdown
        $this->db->order_by('nama', 'ASC'); // ✅ urutkan abjad juga di sini
        $data['semua_tamu'] = $this->admin->get_all_tamu(); // pastikan model-nya juga support sorting
        $this->load->view('admin/tambah_kunjungan_manual', $data);
    }


    public function simpan_kunjungan_manual()
    {
        $this->check_login();

        $id_user = $this->input->post('id_user', TRUE);
        $jenis_layanan = $this->input->post('jenis_layanan', TRUE);

        if (!$id_user || !$jenis_layanan) {
            $this->session->set_flashdata('error', 'Semua field wajib diisi.');
            redirect('admin/tambah_kunjungan_manual');
        }

        $data = [
            'id_user' => $id_user,
            'jenis_layanan' => $jenis_layanan,
            'date_visit' => date('Y-m-d H:i:s'),
            'status' => 'antri',
            'nomor_antrian' => $this->generate_nomor_antrian($jenis_layanan)
        ];

        $this->db->insert('tamdes_kunjungan', $data);

        $this->session->set_flashdata('success', 'Kunjungan berhasil ditambahkan secara manual.');
        redirect('admin/daftar_kunjungan');
    }

    // Optional helper untuk membuat nomor antrian
    private function generate_nomor_antrian($jenis_layanan)
    {
        // Tidak generate nomor antrian untuk Lainnya & Keperluan Pimpinan
        if (in_array(strtolower(trim($jenis_layanan)), ['lainnya', 'keperluan pimpinan'])) {
            return null;
        }
        $prefix = strtoupper(substr($jenis_layanan, 0, 1)); // contoh: "P" untuk Perpustakaan
        $today = date('Y-m-d');

        $this->db->where('DATE(date_visit)', $today);
        $this->db->where('jenis_layanan', $jenis_layanan);
        $count = $this->db->count_all_results('tamdes_kunjungan');

        return $prefix . str_pad($count + 1, 3, '0', STR_PAD_LEFT); // contoh: P001
    }


    public function logout() {
        $this->session->sess_destroy();
        redirect('admin');
    }

    public function dashboard() {
    $this->check_login();

    // Warna berdasarkan jenis layanan
    $warna_layanan = [
        'Perpustakaan' => '#4e73df',                     // biru tua
        'Konsultasi Statistik' => '#1cc88a',             // hijau
        'Rekomendasi Kegiatan Statistik' => '#36b9cc',   // cyan
        'Penjualan Produk Statistik' => '#f6c23e',       // kuning
        'Keperluan Pimpinan' => '#e74a3b',               // merah
        'Lainnya' => '#6f42c1',                          // ungu
    ];

    // Statistik kunjungan
    $data['total_today'] = $this->admin->count_kunjungan_today();
    $data['total_month'] = $this->admin->count_kunjungan_bulan_ini();
    $data['total_all'] = $this->admin->count_kunjungan_all();
    $data['total_unique'] = $this->admin->count_tamu_unik();

    // Ambil data kunjungan + atribut untuk statistik dashboard
    $this->db->select('tamdes_kunjungan.date_visit, tamdes_kunjungan.jenis_layanan, tamdes_kunjungan.status, tamdes_kunjungan.durasi_detik, tamdes_kunjungan.id_user, tamdes_buku.nama, tamdes_buku.nama_instansi');
    $this->db->from('tamdes_kunjungan');
    $this->db->join('tamdes_buku', 'tamdes_kunjungan.id_user = tamdes_buku.id_user', 'left');
    $kunjungan = $this->db->get()->result();

    // Buat data untuk kalender
    $events = [];
    foreach ($kunjungan as $row) {
        $layanan = trim($row->jenis_layanan ?? '');
        $color = array_key_exists($layanan, $warna_layanan) ? $warna_layanan[$layanan] : '#6c757d'; // abu-abu jika tidak cocok

        $events[] = [
            'title' => $row->nama,
            'start' => date('Y-m-d', strtotime($row->date_visit)),
            'color' => $color,
            'extendedProps' => [
                'id_user' => $row->id_user,
                'jenis_layanan' => $layanan,
                'status' => $row->status,
                'durasi_detik' => $row->durasi_detik,
                'nama_instansi' => $row->nama_instansi,
            ],
        ];
    }

    $data['calendar_events'] = $events;

    $this->load->view('admin/dashboard', $data);
}



    public function update_jenis_layanan()
    {
        $this->check_login();
        $id_kunjungan = $this->input->post('id_kunjungan', TRUE);
        $jenis_layanan = $this->input->post('jenis_layanan', TRUE);

        $this->db->where('id_kunjungan', $id_kunjungan);
        $this->db->update('tamdes_kunjungan', ['jenis_layanan' => $jenis_layanan]);

        $this->session->set_flashdata('success', 'Jenis layanan berhasil diperbarui.');
        redirect('admin/daftar_kunjungan');
    }

    public function update_ringkasan()
    {
        $this->check_login();
        $id_kunjungan = $this->input->post('id_kunjungan', TRUE);
        $ringkasan = $this->input->post('ringkasan', TRUE);

        // Jika sudah ada, update
        $existing = $this->db->get_where('konsultasi_pengunjung', ['id_kunjungan' => $id_kunjungan])->row();

        if ($existing) {
            $this->db->where('id_kunjungan', $id_kunjungan);
            $this->db->update('konsultasi_pengunjung', ['hasil_konsultasi' => $ringkasan]);
        } else {
            $this->db->insert('konsultasi_pengunjung', [
                'id_kunjungan' => $id_kunjungan,
                'hasil_konsultasi' => $ringkasan,
                'tanggal_input' => date('Y-m-d H:i:s')
            ]);
        }

        $this->session->set_flashdata('success', 'Ringkasan berhasil disimpan.');
        redirect('admin/daftar_kunjungan');
    }

    public function update_status_kunjungan()
    {
        $this->check_login();
        $id_kunjungan = $this->input->post('id_kunjungan', TRUE);
        $status = $this->input->post('status', TRUE);

        // Ambil data waktu datang
        $kunjungan = $this->db->get_where('tamdes_kunjungan', ['id_kunjungan' => $id_kunjungan])->row();

        $data = ['status' => $status];

        // ✅ Jika status diset ke selesai, simpan waktu selesai + hitung durasi
        if ($status === 'selesai') {
            $selesai = date('Y-m-d H:i:s');
            $data['selesai_timestamp'] = $selesai;

            // hitung durasi detik
            $awal  = strtotime($kunjungan->date_visit);
            $akhir = strtotime($selesai);
            $durasi = $akhir - $awal;
            $data['durasi_detik'] = $durasi;
        }

        $this->db->where('id_kunjungan', $id_kunjungan);
        $this->db->update('tamdes_kunjungan', $data);

        $this->session->set_flashdata('success', 'Status kunjungan berhasil diperbarui.');
        redirect('admin/daftar_kunjungan');
    }

    public function delete_kunjungan($id_kunjungan)
    {
        $this->check_login();

        // Ambil data kunjungan untuk mengetahui id_user
        $kunjungan = $this->db->get_where('tamdes_kunjungan', ['id_kunjungan' => $id_kunjungan])->row();
        if (!$kunjungan) {
            show_404();
        }

        // Hapus ringkasan/hasil konsultasi jika ada
        $this->db->delete('konsultasi_pengunjung', ['id_kunjungan' => $id_kunjungan]);

        // Hapus evaluasi jika ada
        $this->db->delete('tamdes_evaluasi_detail', ['id_kunjungan' => $id_kunjungan]);

        // Hapus kunjungan
        $this->db->delete('tamdes_kunjungan', ['id_kunjungan' => $id_kunjungan]);

        $this->session->set_flashdata('success', 'Data kunjungan berhasil dihapus.');
        redirect('admin/daftar_kunjungan');
    }



    public function daftar_tamu() {
        $this->check_login();
        $data['tamu'] = $this->admin->get_all_tamu();
        $this->load->view('admin/daftar_tamu', $data);
    }

    public function tambah() {
        $this->check_login();
        $this->session->set_flashdata('error', 'Pendaftaran tamu wajib melalui scan wajah. Fitur tambah manual dinonaktifkan di Admin Panel.');
        redirect('admin/daftar_tamu');
    }

    public function insert() {
        $this->check_login();
        $this->session->set_flashdata('error', 'Pendaftaran tamu wajib melalui scan wajah. Fitur tambah manual dinonaktifkan di Admin Panel.');
        redirect('admin/daftar_tamu');
    }

    public function edit($id_user) {
        $this->check_login();
        $data['tamu'] = $this->admin->get_tamu_by_id($id_user);
        if (!$data['tamu']) {
            show_404();
        }
        $this->load->view('admin/edit', $data);
    }

    public function update($id_user = null) {
        $this->check_login();
        if (!$id_user || !$this->admin->get_tamu_by_id($id_user)) {
            show_404();
        }
        $this->admin->update_tamu($id_user);
        $this->session->set_flashdata('success', 'Data tamu berhasil diperbarui.');
        redirect('admin/daftar_tamu');
    }

    public function delete($id_user) {
        $this->check_login();
        if (!$this->admin->get_tamu_by_id($id_user)) {
            show_404();
        }
        $this->admin->delete_tamu($id_user);
        $this->session->set_flashdata('success', 'Data tamu berhasil dihapus.');
        redirect('admin/daftar_tamu');
    }

    public function antrian_konsultasi() {
        $this->check_login();
    
        $today = date('Y-m-d');
    
        $this->db->select('tamdes_kunjungan.id_kunjungan, tamdes_kunjungan.nomor_antrian, tamdes_kunjungan.jenis_layanan, tamdes_kunjungan.date_visit, tamdes_kunjungan.status, tamdes_buku.nama, tamdes_buku.nama_instansi');
        $this->db->from('tamdes_kunjungan');
        $this->db->join('tamdes_buku', 'tamdes_kunjungan.id_user = tamdes_buku.id_user');
        $this->db->where('DATE(tamdes_kunjungan.date_visit)', $today);
            $this->db->where_in('tamdes_kunjungan.jenis_layanan', [
                'Perpustakaan',
                'Konsultasi Statistik',
                'Rekomendasi Kegiatan Statistik',
                'Penjualan Produk Statistik'
        ]);
        
        $this->db->order_by('tamdes_kunjungan.date_visit', 'DESC');
    
        $data['kunjungan'] = $this->db->get()->result();
    
        $this->load->view('admin/antrian_konsultasi', $data);
    }
    
    
    

    public function form_konsultasi($id_kunjungan) {
        $this->check_login();
    
        // Ambil data kunjungan
        $kunjungan = $this->db->get_where('tamdes_kunjungan', ['id_kunjungan' => $id_kunjungan])->row();
        if (!$kunjungan) {
            show_404();
        }
    
        // Ambil data pengunjung
        $pengunjung = $this->db->get_where('tamdes_buku', ['id_user' => $kunjungan->id_user])->row();
    
        // List pertanyaan kuesioner (bisa dari DB jika pakai ref_indikator)
        $indikator = [
            1 => 'Informasi pelayanan pada unit layanan ini tersedia melalui media elektronik maupun non elektronik.',
            2 => 'Persyaratan pelayanan yang ditetapkan mudah dipenuhi/disiapkan oleh konsumen.',
            3 => 'Prosedur/alur pelayanan yang ditetapkan mudah diikuti/dilakukan.',
            4 => 'Jangka waktu penyelesaian pelayanan yang diterima sesuai dengan yang ditetapkan.',
            5 => 'Biaya pelayanan yang dibayarkan sesuai dengan biaya yang ditetapkan.',
            6 => 'Produk pelayanan yang diterima sesuai dengan yang dijanjikan.',
            7 => 'Sarana dan prasarana pendukung pelayanan memberikan kenyamanan.',
            8 => 'Data BPS mudah diakses melalui fasilitas utama yang digunakan.',
            9 => 'Petugas pelayanan dan/atau aplikasi pelayanan online merespon dengan baik.',
            10 => 'Petugas pelayanan dan/atau aplikasi pelayanan online mampu memberikan informasi yang jelas.',
            11 => 'Keberadaan fasilitas pengaduan PST mudah diketahui.',
            12 => 'Proses penanganan pengaduan PST mudah diketahui, jelas, dan tidak berbelit-belit.',
            13 => 'Tidak ada diskriminasi dalam pelayanan.',
            14 => 'Tidak ada pelayanan di luar prosedur/kecurangan pelayanan.',
            15 => 'Tidak ada penerimaan gratifikasi.',
            16 => 'Tidak ada pungutan liar (pungli) dalam pelayanan.',
            17 => 'Tidak ada praktik percaloan dalam pelayanan.'
        ];
    
        $data = [
            'kunjungan' => $kunjungan,
            'pengunjung' => $pengunjung,
            'indikator' => $indikator
        ];
    
        $this->load->view('admin/form_konsultasi', $data);
    }

    public function aktifkan_evaluasi($id_kunjungan) {
        $this->check_login();
        $this->db->where('id_kunjungan', $id_kunjungan);
        $this->db->update('tamdes_kunjungan', ['status' => 'menunggu_evaluasi']);
    
        echo "Evaluasi diaktifkan untuk ID $id_kunjungan.";
    }

    public function simpan_konsultasi($id_kunjungan)
    {
        $this->check_login();
        $this->load->database();

        $hasil_konsultasi = $this->input->post('hasil_konsultasi', TRUE);
        $kebutuhan_data = $this->input->post('kebutuhan_data', TRUE);

        // Simpan hasil konsultasi ke tabel konsultasi_pengunjung
        if (!$kebutuhan_data || !is_array($kebutuhan_data) || count($kebutuhan_data) === 0) {
            $this->db->insert('konsultasi_pengunjung', [
                'id_kunjungan' => $id_kunjungan,
                'hasil_konsultasi' => $hasil_konsultasi,
                'rincian_data' => '-',
                'wilayah_data' => '-',
                'tahun_awal' => null,
                'tahun_akhir' => null,
                'level_data' => null,
                'periode_data' => null,
                'status_data' => null,
                'jenis_publikasi' => null,
                'judul_publikasi' => null,
                'tahun_publikasi' => null,
                'digunakan_nasional' => null,
                'kualitas' => null,
                'tanggal_input' => date('Y-m-d H:i:s')
            ]);
        } else {
            foreach ($kebutuhan_data as $item_json) {
                $item = json_decode($item_json, true);
                $this->db->insert('konsultasi_pengunjung', [
                    'id_kunjungan' => $id_kunjungan,
                    'hasil_konsultasi' => $hasil_konsultasi,
                    'rincian_data' => $item['rincian_data'] ?? '-',
                    'wilayah_data' => $item['wilayah_data'] ?? '-',
                    'tahun_awal' => $item['tahun_awal'] ?? null,
                    'tahun_akhir' => $item['tahun_akhir'] ?? null,
                    'level_data' => $item['level_data'] ?? null,
                    'periode_data' => $item['periode_data'] ?? null,
                    'status_data' => $item['status_data'] ?? null,
                    'jenis_publikasi' => $item['jenis_publikasi'] ?? null,
                    'judul_publikasi' => $item['judul_publikasi'] ?? null,
                    'tahun_publikasi' => $item['tahun_publikasi'] ?? null,
                    'digunakan_nasional' => $item['digunakan_nasional'] ?? null,
                    'kualitas' => $item['kualitas'] ?? null,
                    'tanggal_input' => date('Y-m-d H:i:s')
                ]);
            }
        }

        // ✅ Update status kunjungan ke 'menunggu_evaluasi'
        $this->db->where('id_kunjungan', $id_kunjungan)
                ->update('tamdes_kunjungan', ['status' => 'menunggu_evaluasi']);

        // ✅ Arahkan ke layar tablet untuk evaluasi
        redirect('admin/antrian_konsultasi');
    }

    public function daftar_kunjungan()
    {
        $this->check_login();
        $this->load->database();

        // Ambil input filter
        $q       = $this->input->get('q');
        $layanan = $this->input->get('layanan');
        $tahun   = $this->input->get('tahun');
        $bulan   = $this->input->get('bulan');

        $this->db->select('tamdes_kunjungan.*, tamdes_buku.nama, tamdes_buku.nama_instansi, konsultasi_pengunjung.hasil_konsultasi');
        $this->db->from('tamdes_kunjungan');
        $this->db->join('tamdes_buku', 'tamdes_kunjungan.id_user = tamdes_buku.id_user', 'left');
        $this->db->join('konsultasi_pengunjung', 'tamdes_kunjungan.id_kunjungan = konsultasi_pengunjung.id_kunjungan', 'left');

        // 🔍 Pencarian global
        if ($q) {
            $this->db->group_start();
            $this->db->like('tamdes_buku.nama', $q);
            $this->db->or_like('tamdes_buku.nama_instansi', $q);
            $this->db->or_like('tamdes_kunjungan.jenis_layanan', $q);
            $this->db->or_like('tamdes_kunjungan.status', $q);
            $this->db->group_end();
        }

        // 🎯 Filter
        if ($layanan) {
            $this->db->where('tamdes_kunjungan.jenis_layanan', $layanan);
        }
        if ($tahun) {
            $this->db->where('YEAR(tamdes_kunjungan.date_visit)', $tahun);
        }
        if ($bulan) {
            $this->db->where('MONTH(tamdes_kunjungan.date_visit)', $bulan);
        }

        // ⬇️ Urut berdasarkan waktu
        $this->db->order_by('tamdes_kunjungan.date_visit', 'desc');

        // Eksekusi dan kirim ke view
        $data['kunjungan'] = $this->db->get()->result();

        $this->load->view('admin/daftar_kunjungan', $data);
    }

    public function detail_kunjungan($id_kunjungan)
    {
        $this->check_login();

        // Ambil data kunjungan
        $kunjungan = $this->db->get_where('tamdes_kunjungan', ['id_kunjungan' => $id_kunjungan])->row();
        if (!$kunjungan) {
            show_404();
        }

        // Ambil data pengunjung
        $pengunjung = $this->db->get_where('tamdes_buku', ['id_user' => $kunjungan->id_user])->row();

        // Ambil hasil konsultasi
        $konsultasi = $this->db->get_where('konsultasi_pengunjung', ['id_kunjungan' => $id_kunjungan])->result();

        // Ambil evaluasi
        $evaluasi = $this->db->get_where('tamdes_evaluasi_detail', ['id_kunjungan' => $id_kunjungan])->result();


        $data = [
            'kunjungan' => $kunjungan,
            'pengunjung' => $pengunjung,
            'konsultasi' => $konsultasi,
            'evaluasi' => $evaluasi
        ];

        $this->load->view('admin/detail_kunjungan', $data); // pastikan file ini ada
    }

}
