<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/api/controllers/Api_base.php';

class Kiosk extends Api_base {

    public function face_data() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $guests = $this->db
            ->select('id_user, nama, face_descriptor')
            ->from('tamdes_buku')
            ->where('face_descriptor IS NOT NULL', null, false)
            ->where('face_descriptor !=', '')
            ->get()->result();

        foreach ($guests as $guest) {
            if ($guest->face_descriptor) {
                $guest->face_descriptor = json_decode($guest->face_descriptor, true);
            }
        }

        $this->json_response(['success' => true, 'data' => $guests, 'message' => 'OK']);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $input = $this->get_json_input();

        // Generate id_user (not auto-increment, same logic as Guests)
        $max    = $this->db->select_max('id_user')->get('tamdes_buku')->row()->id_user;
        $new_id = $max ? $max + 1 : 8200001;

        // Handle photo: base64 decode if provided
        $foto = null;
        if (!empty($input['foto'])) {
            // Strip data URI prefix if present
            $b64 = preg_replace('/^data:image\/\w+;base64,/', '', $input['foto']);
            $foto = base64_decode($b64);
        }

        $guest_data = [
            'id_user'           => $new_id,
            'nama'              => $input['nama'] ?? '',
            'email'             => $input['email'] ?? '',
            'notel'             => $input['notel'] ?? '',
            'jeniskelamin'      => $input['jeniskelamin'] ?? '',
            'pendidikan'        => $input['pendidikan'] ?? '',
            'pekerjaan'         => $input['pekerjaan'] ?? '',
            'kategori_instansi' => $input['kategori_instansi'] ?? '',
            'nama_instansi'     => $input['nama_instansi'] ?? '',
            'pemanfaatan'       => $input['pemanfaatan'] ?? '',
            'pengaduan'         => $input['pengaduan'] ?? '',
            'foto'              => $foto,
            'face_descriptor'   => isset($input['face_descriptor']) ? json_encode($input['face_descriptor']) : null,
            'tgldatang'         => date('Y-m-d'),
        ];

        $this->db->insert('tamdes_buku', $guest_data);

        // Insert visit
        $jenis_layanan = $input['jenis_layanan'] ?? '';
        $nomor_antrian = $this->generate_queue_number($jenis_layanan);

        $visit_data = [
            'id_user'       => $new_id,
            'jenis_layanan' => $jenis_layanan,
            'date_visit'    => date('Y-m-d H:i:s'),
            'status'        => 'antri',
            'nomor_antrian' => $nomor_antrian,
        ];

        $this->db->insert('tamdes_kunjungan', $visit_data);
        $id_kunjungan = $this->db->insert_id();

        $this->json_response([
            'success'      => true,
            'data'         => ['id_kunjungan' => $id_kunjungan, 'id_user' => $new_id, 'nomor_antrian' => $nomor_antrian],
            'message'      => 'Pendaftaran berhasil',
        ], 201);
    }

    public function visit() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $input         = $this->get_json_input();
        $id_user       = $input['id_user'] ?? null;
        $jenis_layanan = $input['jenis_layanan'] ?? '';

        if (!$id_user || !$jenis_layanan) {
            $this->json_response(['success' => false, 'message' => 'id_user dan jenis_layanan diperlukan'], 400);
        }

        $nomor_antrian = $this->generate_queue_number($jenis_layanan);

        $visit_data = [
            'id_user'       => $id_user,
            'jenis_layanan' => $jenis_layanan,
            'date_visit'    => date('Y-m-d H:i:s'),
            'status'        => 'antri',
            'nomor_antrian' => $nomor_antrian,
        ];

        $this->db->insert('tamdes_kunjungan', $visit_data);
        $id_kunjungan = $this->db->insert_id();

        $this->json_response([
            'success' => true,
            'data'    => ['id_kunjungan' => $id_kunjungan, 'nomor_antrian' => $nomor_antrian],
            'message' => 'Kunjungan berhasil dibuat',
        ], 201);
    }

    public function ticket($id) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $ticket = $this->db
            ->select('k.id_kunjungan, k.nomor_antrian, k.jenis_layanan, k.date_visit, b.nama')
            ->from('tamdes_kunjungan k')
            ->join('tamdes_buku b', 'k.id_user = b.id_user', 'left')
            ->where('k.id_kunjungan', $id)
            ->get()->row();

        if (!$ticket) {
            $this->json_response(['success' => false, 'message' => 'Tiket tidak ditemukan'], 404);
        }

        $this->json_response(['success' => true, 'data' => $ticket, 'message' => 'OK']);
    }

}
