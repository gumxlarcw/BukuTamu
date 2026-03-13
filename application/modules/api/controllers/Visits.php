<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/api/controllers/Api_base.php';

class Visits extends Api_base {

    public function index() {
        $this->require_auth();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $q       = $this->input->get('q');
            $layanan = $this->input->get('layanan');
            $tahun   = $this->input->get('tahun');
            $bulan   = $this->input->get('bulan');
            $page    = (int) ($this->input->get('page') ?: 1);
            $limit   = (int) ($this->input->get('limit') ?: 10);
            $offset  = ($page - 1) * $limit;

            $this->db->select('k.*, b.nama, b.nama_instansi')
                     ->from('tamdes_kunjungan k')
                     ->join('tamdes_buku b', 'k.id_user = b.id_user', 'left');

            if ($q) {
                $this->db->group_start();
                $this->db->like('b.nama', $q);
                $this->db->or_like('b.nama_instansi', $q);
                $this->db->or_like('k.jenis_layanan', $q);
                $this->db->or_like('k.status', $q);
                $this->db->group_end();
            }
            if ($layanan) {
                $this->db->where('k.jenis_layanan', $layanan);
            }
            if ($tahun) {
                $this->db->where('YEAR(k.date_visit)', $tahun);
            }
            if ($bulan) {
                $this->db->where('MONTH(k.date_visit)', $bulan);
            }

            $this->db->order_by('k.date_visit', 'DESC');
            $total = $this->db->count_all_results('', false);
            $visits = $this->db->limit($limit, $offset)->get()->result();

            $this->json_response([
                'success' => true,
                'data' => $visits,
                'message' => 'OK',
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'totalPages' => max(1, ceil($total / $limit)),
                ],
            ]);

        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input        = $this->get_json_input();
            $id_user      = $input['id_user'] ?? null;
            $jenis_layanan = $input['jenis_layanan'] ?? '';

            if (!$id_user || !$jenis_layanan) {
                $this->json_response(['success' => false, 'message' => 'id_user dan jenis_layanan diperlukan'], 400);
            }

            $nomor_antrian = $this->generate_queue_number($jenis_layanan);

            $data = [
                'id_user'       => $id_user,
                'jenis_layanan' => $jenis_layanan,
                'date_visit'    => date('Y-m-d H:i:s'),
                'status'        => 'antri',
                'nomor_antrian' => $nomor_antrian,
            ];

            $this->db->insert('tamdes_kunjungan', $data);
            $new_id = $this->db->insert_id();
            $visit  = $this->db->get_where('tamdes_kunjungan', ['id_kunjungan' => $new_id])->row();

            $this->json_response(['success' => true, 'data' => $visit, 'message' => 'Kunjungan berhasil dibuat'], 201);
        } else {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }

    public function detail($id) {
        $this->require_auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $visit = $this->db->select('k.*, b.nama, b.nama_instansi, b.email, b.notel, b.jeniskelamin, b.pendidikan, b.pekerjaan, b.kategori_instansi, b.pemanfaatan, b.pengaduan')
                          ->from('tamdes_kunjungan k')
                          ->join('tamdes_buku b', 'k.id_user = b.id_user', 'left')
                          ->where('k.id_kunjungan', $id)
                          ->get()->row();

        if (!$visit) {
            $this->json_response(['success' => false, 'message' => 'Kunjungan tidak ditemukan'], 404);
        }

        $consultation = $this->db->get_where('konsultasi_pengunjung', ['id_kunjungan' => $id])->result();
        $evaluation   = $this->db->get_where('tamdes_evaluasi_detail', ['id_kunjungan' => $id])->result();

        $this->json_response([
            'success' => true,
            'data' => [
                'visit'        => $visit,
                'consultation' => $consultation,
                'evaluation'   => $evaluation,
            ],
            'message' => 'OK',
        ]);
    }

    public function status($id) {
        $this->require_auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $input  = $this->get_json_input();
        $status = $input['status'] ?? null;

        if (!$status) {
            $this->json_response(['success' => false, 'message' => 'status diperlukan'], 400);
        }

        $visit = $this->db->get_where('tamdes_kunjungan', ['id_kunjungan' => $id])->row();
        if (!$visit) {
            $this->json_response(['success' => false, 'message' => 'Kunjungan tidak ditemukan'], 404);
        }

        $update = ['status' => $status];

        if ($status === 'selesai') {
            $selesai_timestamp    = date('Y-m-d H:i:s');
            $update['selesai_timestamp'] = $selesai_timestamp;
            if ($visit->date_visit) {
                $durasi = strtotime($selesai_timestamp) - strtotime($visit->date_visit);
                $update['durasi_detik'] = max(0, $durasi);
            }
        }

        $this->db->where('id_kunjungan', $id)->update('tamdes_kunjungan', $update);
        $updated = $this->db->get_where('tamdes_kunjungan', ['id_kunjungan' => $id])->row();

        $this->json_response(['success' => true, 'data' => $updated, 'message' => 'Status berhasil diupdate']);
    }

    public function service($id) {
        $this->require_auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $input         = $this->get_json_input();
        $jenis_layanan = $input['jenis_layanan'] ?? null;

        if (!$jenis_layanan) {
            $this->json_response(['success' => false, 'message' => 'jenis_layanan diperlukan'], 400);
        }

        $this->db->where('id_kunjungan', $id)->update('tamdes_kunjungan', ['jenis_layanan' => $jenis_layanan]);
        $updated = $this->db->get_where('tamdes_kunjungan', ['id_kunjungan' => $id])->row();

        $this->json_response(['success' => true, 'data' => $updated, 'message' => 'Layanan berhasil diupdate']);
    }

    public function summary($id) {
        $this->require_auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'PUT') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $input           = $this->get_json_input();
        $hasil_konsultasi = $input['ringkasan'] ?? $input['hasil_konsultasi'] ?? '';

        $existing = $this->db->get_where('konsultasi_pengunjung', ['id_kunjungan' => $id])->row();

        if ($existing) {
            $this->db->where('id_kunjungan', $id)->update('konsultasi_pengunjung', ['hasil_konsultasi' => $hasil_konsultasi]);
        } else {
            $this->db->insert('konsultasi_pengunjung', [
                'id_kunjungan'    => $id,
                'hasil_konsultasi' => $hasil_konsultasi,
                'tanggal_input'   => date('Y-m-d H:i:s'),
            ]);
        }

        $updated = $this->db->get_where('konsultasi_pengunjung', ['id_kunjungan' => $id])->row();

        $this->json_response(['success' => true, 'data' => $updated, 'message' => 'Ringkasan berhasil disimpan']);
    }

}
