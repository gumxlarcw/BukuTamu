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

    public function guest_list() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $guests = $this->db
            ->select('id_user, nama, nama_instansi')
            ->from('tamdes_buku')
            ->order_by('nama', 'ASC')
            ->get()->result();

        $this->json_response(['success' => true, 'data' => $guests, 'message' => 'OK']);
    }

    public function register() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $input = $this->get_json_input();

        // Strategy C: tolak cross layanan
        $this->validate_no_cross_layanan($input['jenis_layanan'] ?? null);
        $this->validate_sarana_for_layanan($input['jenis_layanan'] ?? null, $input['sarana'] ?? []);

        // Prevent double-submit: check if same nama+notel registered in last 30 seconds
        $recent = $this->db->where('nama', $input['nama'] ?? '')
                           ->where('notel', $input['notel'] ?? '')
                           ->where('tgldatang', date('Y-m-d'))
                           ->order_by('id_user', 'DESC')
                           ->get('tamdes_buku')->row();
        if ($recent) {
            // Already registered today — return existing visit
            $existing_visit = $this->db->where('id_user', $recent->id_user)
                                       ->order_by('id_kunjungan', 'DESC')
                                       ->get('tamdes_kunjungan')->row();
            if ($existing_visit) {
                $this->json_response([
                    'success' => true,
                    'data'    => ['id_kunjungan' => $existing_visit->id_kunjungan, 'id_user' => $recent->id_user, 'nomor_antrian' => $existing_visit->nomor_antrian],
                    'message' => 'Pendaftaran berhasil',
                ], 201);
            }
        }

        // Generate id_user with table lock to prevent race condition
        $this->db->query('LOCK TABLES tamdes_buku WRITE, tamdes_kunjungan WRITE, tamdes_responden_tahunan WRITE');
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
            'id_user'              => $new_id,
            'nama'                 => $input['nama'] ?? '',
            'email'                => $input['email'] ?? '',
            'notel'                => $input['notel'] ?? '',
            'jeniskelamin'         => $input['jeniskelamin'] ?? '',
            'umur'                 => !empty($input['umur']) ? (int)$input['umur'] : null,
            'disabilitas'          => !empty($input['disabilitas']) ? (int)$input['disabilitas'] : null,
            'jenis_disabilitas'    => !empty($input['jenis_disabilitas']) ? (int)$input['jenis_disabilitas'] : null,
            'pendidikan'           => $input['pendidikan'] ?? '',
            'pekerjaan'            => $input['pekerjaan'] ?? '',
            'pekerjaan_lainnya'    => $input['pekerjaan_lainnya'] ?? null,
            'kategori_instansi'    => $input['kategori_instansi'] ?? '',
            'kategori_lainnya'     => $input['kategori_lainnya'] ?? null,
            'nama_instansi'        => $input['nama_instansi'] ?? '',
            'pemanfaatan'          => $input['pemanfaatan'] ?? '',
            'pemanfaatan_lainnya'  => $input['pemanfaatan_lainnya'] ?? null,
            'pengaduan'            => $input['pengaduan'] ?? '',
            'foto'                 => $foto,
            'face_descriptor'      => isset($input['face_descriptor']) ? json_encode($input['face_descriptor']) : null,
            'tgldatang'            => date('Y-m-d'),
            'biometric_consent'    => !empty($input['biometric_consent']) ? 1 : 0,
            'consent_timestamp'    => !empty($input['consent_timestamp']) ? date('Y-m-d H:i:s', strtotime($input['consent_timestamp'])) : null,
            'registered_via'       => 'kiosk',
        ];

        $this->db->insert('tamdes_buku', $guest_data);

        // Insert visit — jenis_layanan & sarana stored as JSON arrays
        $jenis_layanan_raw = $input['jenis_layanan'] ?? '';
        $jenis_layanan = is_array($jenis_layanan_raw) ? json_encode($jenis_layanan_raw) : $jenis_layanan_raw;
        $nomor_antrian = $this->generate_queue_number(is_array($jenis_layanan_raw) ? ($jenis_layanan_raw[0] ?? '') : $jenis_layanan_raw);

        $sarana_raw = $input['sarana'] ?? [];
        $sarana = is_array($sarana_raw) ? json_encode($sarana_raw) : $sarana_raw;

        $visit_data = [
            'id_user'            => $new_id,
            'jenis_layanan'      => $jenis_layanan,
            'layanan_lainnya'    => $input['layanan_lainnya'] ?? null,
            'sarana'             => $sarana,
            'sarana_lainnya'     => $input['sarana_lainnya'] ?? null,
            'date_visit'         => date('Y-m-d H:i:s'),
            'status'             => 'antri',
            'nomor_antrian'      => $nomor_antrian,
            'created_by'         => 'kiosk',
        ];

        $this->db->insert('tamdes_kunjungan', $visit_data);
        $id_kunjungan = $this->db->insert_id();

        $this->db->query('UNLOCK TABLES');

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

        $input   = $this->get_json_input();

        // Strategy C: tolak cross layanan
        $this->validate_no_cross_layanan($input['jenis_layanan'] ?? null);
        $this->validate_sarana_for_layanan($input['jenis_layanan'] ?? null, $input['sarana'] ?? []);

        $id_user = $input['id_user'] ?? null;

        $jenis_layanan_raw = $input['jenis_layanan'] ?? '';
        $jenis_layanan = is_array($jenis_layanan_raw) ? json_encode($jenis_layanan_raw) : $jenis_layanan_raw;

        if (!$id_user || !$jenis_layanan_raw) {
            $this->json_response(['success' => false, 'message' => 'id_user dan jenis_layanan diperlukan'], 400);
        }

        // Dedup: accidental double-tap within 60s for SAME guest + SAME service → return existing.
        // Window is short (60s, not all-day like register) because returning guests legitimately
        // come back same day for different services.
        $recent_cutoff = date('Y-m-d H:i:s', time() - 60);
        $recent = $this->db
            ->where('id_user', $id_user)
            ->where('jenis_layanan', $jenis_layanan)
            ->where('date_visit >=', $recent_cutoff)
            ->order_by('id_kunjungan', 'DESC')
            ->limit(1)
            ->get('tamdes_kunjungan')->row();
        if ($recent) {
            $this->json_response([
                'success' => true,
                'data'    => ['id_kunjungan' => $recent->id_kunjungan, 'nomor_antrian' => $recent->nomor_antrian],
                'message' => 'Kunjungan berhasil dibuat',
            ], 201);
        }

        $nomor_antrian = $this->generate_queue_number(is_array($jenis_layanan_raw) ? ($jenis_layanan_raw[0] ?? '') : $jenis_layanan_raw);

        $sarana_raw = $input['sarana'] ?? [];
        $sarana = is_array($sarana_raw) ? json_encode($sarana_raw) : $sarana_raw;

        $visit_data = [
            'id_user'            => $id_user,
            'jenis_layanan'      => $jenis_layanan,
            'layanan_lainnya'    => $input['layanan_lainnya'] ?? null,
            'sarana'             => $sarana,
            'sarana_lainnya'     => $input['sarana_lainnya'] ?? null,
            'date_visit'         => date('Y-m-d H:i:s'),
            'status'             => 'antri',
            'nomor_antrian'      => $nomor_antrian,
            'created_by'         => 'kiosk',
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

    /**
     * GET /api/kiosk/profile-gaps/:id_user
     * Returns list of field names that are NULL/empty for this user.
     */
    public function profile_gaps($id_user) {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $user = $this->db->where('id_user', $id_user)->get('tamdes_buku')->row_array();
        if (!$user) {
            $this->json_response(['success' => false, 'message' => 'User tidak ditemukan'], 404);
        }

        // Fields to check — only ones that could be missing from old data
        $checkable = [
            'umur', 'disabilitas', 'jenis_disabilitas',
            'pendidikan', 'pekerjaan', 'kategori_instansi',
            'nama_instansi', 'pemanfaatan', 'email', 'notel',
        ];

        $gaps = [];
        foreach ($checkable as $field) {
            $val = $user[$field] ?? null;
            if ($val === null || $val === '' || $val === '0' || $val === 0) {
                // jenis_disabilitas is only required if disabilitas = 1
                if ($field === 'jenis_disabilitas') {
                    $dis = $user['disabilitas'] ?? null;
                    if ($dis !== null && (int)$dis === 1) {
                        $gaps[] = $field;
                    }
                } else {
                    $gaps[] = $field;
                }
            }
        }

        $this->json_response(['success' => true, 'data' => ['gaps' => $gaps], 'message' => 'OK']);
    }

    /**
     * POST /api/kiosk/profile-update/:id_user
     * Patch only provided fields into tamdes_buku.
     */
    public function profile_update($id_user) {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $input = $this->get_json_input();

        $allowed = [
            'umur', 'disabilitas', 'jenis_disabilitas',
            'pendidikan', 'pekerjaan', 'pekerjaan_lainnya',
            'kategori_instansi', 'kategori_lainnya',
            'nama_instansi', 'pemanfaatan', 'pemanfaatan_lainnya',
            'email', 'notel',
        ];

        $update = [];
        foreach ($allowed as $field) {
            if (array_key_exists($field, $input)) {
                $update[$field] = $input[$field];
            }
        }

        if (empty($update)) {
            $this->json_response(['success' => false, 'message' => 'Tidak ada data untuk diupdate'], 400);
        }

        $this->db->where('id_user', $id_user)->update('tamdes_buku', $update);

        $this->json_response(['success' => true, 'data' => null, 'message' => 'Profil berhasil dilengkapi']);
    }

}
