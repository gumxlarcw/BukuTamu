<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/api/controllers/Api_base.php';

class Consultations extends Api_base {

    public function index() {
        $this->require_auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $today = date('Y-m-d');

        $consultations = $this->db
            ->select('k.*, b.nama, b.nama_instansi, b.email, b.notel, b.jeniskelamin, b.pendidikan, b.pekerjaan, b.kategori_instansi')
            ->from('tamdes_kunjungan k')
            ->join('tamdes_buku b', 'k.id_user = b.id_user', 'left')
            ->where("DATE(k.date_visit)", $today)
            ->where_in('k.jenis_layanan', [
                'Perpustakaan',
                'Konsultasi Statistik',
                'Rekomendasi Kegiatan Statistik',
                'Penjualan Produk Statistik',
            ])
            ->order_by('k.date_visit', 'DESC')
            ->get()->result();

        $this->json_response(['success' => true, 'data' => $consultations, 'message' => 'OK']);
    }

    public function detail($id) {
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
            $selesai_timestamp           = date('Y-m-d H:i:s');
            $update['selesai_timestamp'] = $selesai_timestamp;
            if ($visit->date_visit) {
                $durasi                  = strtotime($selesai_timestamp) - strtotime($visit->date_visit);
                $update['durasi_detik']  = max(0, $durasi);
            }
        }

        $this->db->where('id_kunjungan', $id)->update('tamdes_kunjungan', $update);
        $updated = $this->db->get_where('tamdes_kunjungan', ['id_kunjungan' => $id])->row();

        $this->json_response(['success' => true, 'data' => $updated, 'message' => 'Status berhasil diupdate']);
    }

    public function call($id) {
        $this->require_auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $visit = $this->db->select('nomor_antrian')
                          ->get_where('tamdes_kunjungan', ['id_kunjungan' => $id])
                          ->row();

        if (!$visit) {
            $this->json_response(['success' => false, 'message' => 'Kunjungan tidak ditemukan'], 404);
        }

        $nomor  = $visit->nomor_antrian;
        $result = $this->proxy_antrian($nomor);

        $this->json_response($result);
    }

    public function test_sound($id) {
        $this->require_auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $result = $this->proxy_antrian('TES');

        $this->json_response($result);
    }

    public function data($id) {
        $this->require_auth();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $rows = $this->db->get_where('konsultasi_pengunjung', ['id_kunjungan' => $id])->result();
            $this->json_response(['success' => true, 'data' => $rows, 'message' => 'OK']);

        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input            = $this->get_json_input();
            $hasil_konsultasi = $input['hasil_konsultasi'] ?? '';
            $kebutuhan_data   = $input['kebutuhan_data'] ?? [];

            // Delete existing rows for this visit
            $this->db->where('id_kunjungan', $id)->delete('konsultasi_pengunjung');

            // Insert new rows
            $now = date('Y-m-d H:i:s');
            foreach ($kebutuhan_data as $item) {
                $row = [
                    'id_kunjungan'       => $id,
                    'hasil_konsultasi'   => $hasil_konsultasi,
                    'rincian_data'       => $item['rincian_data'] ?? null,
                    'wilayah_data'       => $item['wilayah_data'] ?? null,
                    'tahun_awal'         => $item['tahun_awal'] ?? null,
                    'tahun_akhir'        => $item['tahun_akhir'] ?? null,
                    'level_data'         => $item['level_data'] ?? null,
                    'periode_data'       => $item['periode_data'] ?? null,
                    'status_data'        => $item['status_data'] ?? null,
                    'jenis_publikasi'    => $item['jenis_publikasi'] ?? null,
                    'judul_publikasi'    => $item['judul_publikasi'] ?? null,
                    'tahun_publikasi'    => $item['tahun_publikasi'] ?? null,
                    'digunakan_nasional' => $item['digunakan_nasional'] ?? null,
                    'kualitas'           => $item['kualitas'] ?? null,
                    'tanggal_input'      => $now,
                ];
                $this->db->insert('konsultasi_pengunjung', $row);
            }

            $saved = $this->db->get_where('konsultasi_pengunjung', ['id_kunjungan' => $id])->result();
            $this->json_response(['success' => true, 'data' => $saved, 'message' => 'Data konsultasi berhasil disimpan']);
        } else {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }

    private function proxy_antrian($nomor) {
        $url     = 'https://dashboard-pst.bpsmalut.com/update-antrian';
        $payload = json_encode(['nomor' => $nomor]);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $payload);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

        $response  = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error     = curl_error($ch);
        curl_close($ch);

        if ($error) {
            return ['success' => false, 'message' => 'cURL error: ' . $error];
        }

        if ($http_code >= 200 && $http_code < 300) {
            return ['success' => true, 'message' => 'Antrian berhasil dipanggil', 'nomor' => $nomor];
        }

        return ['success' => false, 'message' => 'Gagal memanggil antrian', 'http_code' => $http_code];
    }
}
