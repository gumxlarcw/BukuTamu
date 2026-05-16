<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Selamat_datang extends MX_Controller {

	public function __construct() {
		parent::__construct();
		$this->load->model('m_selamat_datang');
		$this->load->library('session');
	}

	private function data_construct(){
		$data = array();
		$data['layanan'] = $this->session->userdata('jenis_layanan'); // ambil untuk ditampilkan
		return $data;
	}

	public function index() {
		$data = $this->data_construct();

		if (!$data['layanan']) {
			redirect('layanan'); // redirect jika session tidak ada
		}

		$this->load->view('view_selamat_datang.php', $data);
	}

	public function add() {
		// Cek ID terakhir (tamdes_buku.id_user bukan AUTO_INCREMENT)
		$last = $this->db->select_max('id_user')->get('tamdes_buku')->row();
		$new_id_user = ($last && $last->id_user) ? ((int) $last->id_user + 1) : 8200001;

		$tgldatang_raw = $this->input->post('tgldatang', TRUE);
		$tgldatang = $tgldatang_raw ? substr((string) $tgldatang_raw, 0, 10) : null; // kolom DATE

		// Simpan user
		$data = [
			'id_user' => $new_id_user,
			'tgldatang' => $tgldatang,
			'nama' => $this->input->post('nama', TRUE),
			'email' => $this->input->post('email', TRUE),
			'notel' => $this->input->post('notel', TRUE),
			'jeniskelamin' => $this->input->post('jeniskelamin', TRUE),
			'pendidikan' => $this->input->post('pendidikan', TRUE),
			'pekerjaan' => $this->input->post('pekerjaan', TRUE),
			'kategori_instansi' => $this->input->post('kategori_instansi', TRUE),
			'nama_instansi' => $this->input->post('nama_instansi', TRUE),
			'pemanfaatan' => $this->input->post('pemanfaatan', TRUE),
			'sarana' => json_encode(['1 : Pelayanan Statistik Terpadu (PST) datang langsung']),
			'pengaduan' => $this->input->post('pengaduan', TRUE),
		    'biometric_consent' => 1,
    		'consent_timestamp' => date('Y-m-d H:i:s'),
		];

		// Simpan foto
		$foto_base64 = $this->input->post('foto');
		if ($foto_base64 && strpos($foto_base64, 'data:image') === 0) {
			$foto_parts = explode(',', $foto_base64);
			$data['foto'] = base64_decode($foto_parts[1]);
		}

		// Simpan face descriptor
		$descriptor = $this->input->post('face_descriptor');
		if ($descriptor) $data['face_descriptor'] = $descriptor;

		// Insert ke tabel tamdes_buku
		$this->m_selamat_datang->input($data, 'tamdes_buku');

		// Cek jenis layanan untuk nomor antrian
		$jenis_layanan = $this->session->userdata('jenis_layanan');
		$no_antrian = null;
		if ($jenis_layanan !== 'Lainnya' && $jenis_layanan !== 'Keperluan Pimpinan') {
			// Buat nomor antrian berdasarkan urutan hari ini
			$no_antrian = $this->generate_no_antrian();
		}

		// Simpan ke tabel kunjungan langsung tanpa cek duplikasi
		$kunjungan = [
			'id_user' => $new_id_user,
			'date_visit' => date('Y-m-d H:i:s'),
			'jenis_layanan' => $jenis_layanan,
			'status' => 'antri',
			'nomor_antrian' => $no_antrian
		];
		log_message('debug', 'selamat_datang::add - insert kunjungan: ' . json_encode($kunjungan));
		$this->db->insert('tamdes_kunjungan', $kunjungan);
		if ($this->db->affected_rows() <= 0) {
			$dberr = $this->db->error();
			log_message('error', 'selamat_datang::add - gagal insert kunjungan: ' . print_r($dberr, true));
		}

		// Redirect setelah semua selesai
		$data = [
			'success' => 'Data berhasil disimpan.',
			'nomor_antrian' => $no_antrian,
			'active_tab' => 'photo' // 👈 tambahkan ini
		];

		// If the request is AJAX (fetch from the kiosk), return JSON so the client
		// can show the modal without navigating to /selamat_datang/add.
		if ($this->input->is_ajax_request()) {
			header('Content-Type: application/json');
			echo json_encode([
				'status' => 'success',
				'message' => $data['success'],
				'nomor_antrian' => $data['nomor_antrian']
			]);
			return;
		}

		$this->load->view('view_selamat_datang.php', $data);
	}


	private function send_to_printer($no_antrian) {
		$url = 'http://localhost:5000/print';
		$payload = json_encode(['no' => $no_antrian]);

		$ch = curl_init($url);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_POST, true);
		curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
		curl_setopt($ch, CURLOPT_TIMEOUT, 3);

		$response = curl_exec($ch);
		$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
		curl_close($ch);

		if ($httpCode == 200) {
			log_message('info', '✅ Cetak antrian sukses: ' . $no_antrian);
		} else {
			log_message('error', '❌ Gagal cetak antrian ' . $no_antrian . ': ' . $response);
		}
	}



	public function recognize() {
		if (!trim((string) ($this->session->userdata('jenis_layanan') ?? ''))) {
			return redirect('layanan');
		}
		// Harus lewat langkah pilih status dan memilih "Sudah Pernah Daftar"
		if ($this->session->userdata('status_pilihan') !== 'existing') {
			return redirect('selamat_datang/check');
		}
		$this->load->view('view_recognize');
	}

	public function pilih_status($status = null)
	{
		if (!trim((string) ($this->session->userdata('jenis_layanan') ?? ''))) {
			return redirect('layanan');
		}

		$status = strtolower(trim((string) $status));
		if (!in_array($status, ['existing', 'new'], true)) {
			return redirect('selamat_datang/check');
		}

		$this->session->set_userdata('status_pilihan', $status);
		if ($status === 'existing') {
			return redirect('selamat_datang/recognize');
		}

		return redirect('layanan/auto');
	}

	public function check() {
		if (!trim((string) ($this->session->userdata('jenis_layanan') ?? ''))) {
			return redirect('layanan');
		}
		$this->load->view('view_pilih_status');
	}
	
	private function generate_no_antrian()
	{
		$tanggal = date('Y-m-d');

		// Ambil nomor antrian terakhir hari ini
		$last = $this->db
			->select('nomor_antrian')
			->where('DATE(date_visit)', $tanggal)
			->like('nomor_antrian', 'A')
			->order_by('date_visit', 'DESC')
			->limit(1)
			->get('tamdes_kunjungan')
			->row();

		if ($last && preg_match('/A(\d+)/', $last->nomor_antrian, $match)) {
			$next = (int)$match[1] + 1;
		} else {
			$next = 1;
		}

		return 'A' . str_pad($next, 3, '0', STR_PAD_LEFT); // Contoh: A001
	}

	
	public function get_all_tamu()
	{
		$tamu = $this->db->select('id_user, nama')
						->order_by('nama', 'ASC')
						->get('tamdes_buku')
						->result();

		header('Content-Type: application/json');
		echo json_encode($tamu);
	}


	public function get_face_data()
	{
		$this->load->model('M_user');
		$users = $this->M_user->get_all_with_descriptor();

		$result = [];
		foreach ($users as $user) {
			$result[] = [
				'id_user' => $user->id_user,
				'nama' => $user->nama,
				'nama_instansi' => $user->nama_instansi,
				'face_descriptor' => $user->face_descriptor
			];
		}

		header('Content-Type: application/json');
		echo json_encode($result);
	}
	
	public function print_antrian($no_antrian)
	{
		$this->send_to_printer($no_antrian);

		// Hindari cache di iframe
		$this->output
			->set_content_type('text/plain')
			->set_header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0')
			->set_header('Pragma: no-cache');

		echo "ok";
	}

	public function masuk() {
		$json = json_decode(file_get_contents('php://input'), true);
		$id_user = isset($json['id_user']) ? (int) $json['id_user'] : null;
		$jenis_layanan = trim((string) ($this->session->userdata('jenis_layanan') ?? ''));

		if ($jenis_layanan === '') {
			echo json_encode([
				'status' => 'error',
				'message' => 'Jenis layanan belum dipilih. Silakan kembali ke halaman layanan.'
			]);
			return;
		}

		if ($id_user) {
			// Cek apakah user sudah melakukan kunjungan hari ini
			$existing = $this->db
				->where('id_user', $id_user)
				->where('DATE(date_visit)', date('Y-m-d'))
				->get('tamdes_kunjungan')
				->row();

			// Cek jenis layanan untuk nomor antrian
			$no_antrian = null;
			if ($jenis_layanan !== 'Lainnya' && $jenis_layanan !== 'Keperluan Pimpinan') {
				if ($existing) {
					// Gunakan nomor antrian yang sudah ada
					$no_antrian = $existing->nomor_antrian;
				} else {
					// Buat nomor antrian baru
					$no_antrian = $this->generate_no_antrian();
				}
			}

			if (!$existing) {
				$kunjungan = [
					'id_user' => $id_user,
					'date_visit' => date('Y-m-d H:i:s'),
					'jenis_layanan' => $jenis_layanan,
					'nomor_antrian' => $no_antrian
				];
				log_message('debug', 'selamat_datang::masuk - insert kunjungan: ' . json_encode($kunjungan));
				$this->db->insert('tamdes_kunjungan', $kunjungan);
				if ($this->db->affected_rows() <= 0) {
					$dberr = $this->db->error();
					log_message('error', 'selamat_datang::masuk - gagal insert kunjungan: ' . print_r($dberr, true));
				}
			}

			// Kirim ke printer thermal hanya jika ada nomor antrian
			if ($no_antrian) {
				$this->send_to_printer($no_antrian);
				usleep(1);
			}

			echo json_encode([
				'status' => 'success',
				'nomor_antrian' => $no_antrian,
				'print_url' => $no_antrian ? base_url('antrian/cetak/' . $no_antrian) : null
			]);
		} else {
			echo json_encode([
				'status' => 'error',
				'message' => 'Wajah tidak dikenali.'
			]);
		}
	}

}
