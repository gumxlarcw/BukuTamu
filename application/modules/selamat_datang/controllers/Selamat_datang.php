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
		// Cek ID terakhir
		$last = $this->db->select('id_user')->order_by('id_user', 'DESC')->get('tamdes_buku')->row();
		$new_id_user = $last ? $last->id_user + 1 : 8200001;

		// Simpan user
		$data = [
			'id_user' => $new_id_user,
			'tgldatang' => $this->input->post('tgldatang'),
			'nama' => $this->input->post('nama'),
			'email' => $this->input->post('email'),
			'notel' => $this->input->post('notel'),
			'jeniskelamin' => $this->input->post('jeniskelamin'),
			'pendidikan' => $this->input->post('pendidikan'),
			'pekerjaan' => $this->input->post('pekerjaan'),
			'kategori_instansi' => $this->input->post('kategori_instansi'),
			'nama_instansi' => $this->input->post('nama_instansi'),
			'pemanfaatan' => $this->input->post('pemanfaatan'),
			'sarana' => json_encode(['1 : Pelayanan Statistik Terpadu (PST) datang langsung']),
			'pengaduan' => $this->input->post('pengaduan')
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

		// Buat nomor antrian berdasarkan urutan hari ini
		$no_antrian = $this->generate_no_antrian();

		// Simpan ke tabel kunjungan langsung tanpa cek duplikasi
		$this->db->insert('tamdes_kunjungan', [
			'id_user' => $new_id_user,
			'date_visit' => date('Y-m-d H:i:s'),
			'jenis_layanan' => $this->session->userdata('jenis_layanan'),
			'nomor_antrian' => $no_antrian
		]);

		// Redirect setelah semua selesai
		$data = [
			'success' => 'Data berhasil disimpan.',
			'nomor_antrian' => $no_antrian,
			'active_tab' => 'photo' // 👈 tambahkan ini
			
		];
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
		$this->load->view('view_recognize');
	}

	public function check() {
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

	

	public function get_face_data()
	{
		$this->load->model('M_user');
		$users = $this->M_user->get_all_with_descriptor();

		$result = [];
		foreach ($users as $user) {
			$result[] = [
				'id_user' => $user->id_user,
				'nama' => $user->nama,
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
		$id_user = $json['id_user'] ?? null;

		if ($id_user) {
			// Cek apakah user sudah melakukan kunjungan hari ini
			$existing = $this->db->get_where('tamdes_kunjungan', [
				'id_user' => $id_user,
				'DATE(date_visit)' => date('Y-m-d')
			])->row();

			if ($existing) {
				// Gunakan nomor antrian yang sudah ada
				$no_antrian = $existing->nomor_antrian;
			} else {
				// Buat nomor antrian baru
				$no_antrian = $this->generate_no_antrian();

				$this->db->insert('tamdes_kunjungan', [
					'id_user' => $id_user,
					'date_visit' => date('Y-m-d H:i:s'),
					'jenis_layanan' => $this->session->userdata('jenis_layanan'),
					'nomor_antrian' => $no_antrian
				]);
			}

			// Kirim ke printer thermal
			$this->send_to_printer($no_antrian);
			usleep(1);

			echo json_encode([
				'status' => 'success',
				'nomor_antrian' => $no_antrian,
				'print_url' => base_url('antrian/cetak/' . $no_antrian)
			]);
		} else {
			echo json_encode([
				'status' => 'error',
				'message' => 'Wajah tidak dikenali.'
			]);
		}
	}

}
