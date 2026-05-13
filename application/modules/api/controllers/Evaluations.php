<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/api/controllers/Api_base.php';

class Evaluations extends Api_base {

    public function pending() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        // Hanya 4 layanan PST yang perlu evaluasi tablet.
        // Resepsionis (Lainnya, Keperluan Pimpinan) skip evaluasi — defense in depth
        // jika ada visit yang lolos ke menunggu_evaluasi tapi bukan PST.
        $candidates = $this->db
            ->order_by('id_kunjungan', 'ASC')
            ->get_where('tamdes_kunjungan', ['status' => 'menunggu_evaluasi'])
            ->result();

        $pst_services = [
            'Perpustakaan',
            'Konsultasi Statistik',
            'Rekomendasi Kegiatan Statistik',
            'Penjualan Produk Statistik',
        ];

        foreach ($candidates as $candidate) {
            $layanan_list = [];
            if (!empty($candidate->jenis_layanan)) {
                $decoded = json_decode($candidate->jenis_layanan, true);
                $layanan_list = is_array($decoded) ? $decoded : [$candidate->jenis_layanan];
            }
            foreach ($layanan_list as $layanan) {
                if (in_array($layanan, $pst_services, true)) {
                    $this->json_response(['success' => true, 'data' => $candidate, 'message' => 'OK']);
                    return;
                }
            }
        }

        $this->json_response(['success' => true, 'data' => null, 'message' => 'OK']);
    }

    public function detail($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $indikator = $this->indikator_list();
            $evaluation = $this->db->get_where('tamdes_evaluasi_detail', ['id_kunjungan' => $id])->result();

            // Konsultasi data dengan status 1 (Ya sesuai) atau 2 (Ya tidak sesuai) —
            // tamu perlu beri rating kualitas tiap data yang diperoleh.
            $konsultasi_kualitas = $this->db
                ->select('id, rincian_data, status_data, kualitas')
                ->where('id_kunjungan', $id)
                ->where_in('status_data', [1, 2])
                ->get('konsultasi_pengunjung')->result();

            $this->json_response([
                'success' => true,
                'data' => [
                    'indikator'           => $indikator,
                    'evaluation'          => $evaluation,
                    'konsultasi_kualitas' => $konsultasi_kualitas,
                ],
                'message' => 'OK',
            ]);

        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input            = $this->get_json_input();
            $skor_keseluruhan = $input['skor_keseluruhan'] ?? null;
            $kepuasan         = $input['kepuasan'] ?? [];
            $kualitas_per_konsultasi = $input['kualitas_per_konsultasi'] ?? [];

            if (!$skor_keseluruhan || !is_numeric($skor_keseluruhan) || $skor_keseluruhan < 1 || $skor_keseluruhan > 10) {
                $this->json_response(['success' => false, 'message' => 'skor_keseluruhan harus antara 1-10'], 400);
            }

            if (!is_array($kepuasan)) {
                $this->json_response(['success' => false, 'message' => 'kepuasan harus berupa array'], 400);
            }

            $visit = $this->db->get_where('tamdes_kunjungan', ['id_kunjungan' => $id])->row();
            if (!$visit) {
                $this->json_response(['success' => false, 'message' => 'Kunjungan tidak ditemukan'], 404);
            }

            // Gate: hanya visit yang sudah menunggu evaluasi (atau sudah selesai, untuk re-submit
            // koreksi) yang boleh menerima POST. Mencegah attacker post fake eval ke visit
            // yang masih antri/diproses. Endpoint sengaja no-auth (tablet kiosk), jadi gate ini
            // adalah satu-satunya defense.
            if (!in_array($visit->status, ['menunggu_evaluasi', 'selesai'], true)) {
                $this->json_response([
                    'success' => false,
                    'message' => 'Evaluasi belum tersedia untuk kunjungan ini (status: ' . $visit->status . ').',
                ], 400);
            }

            // Delete existing evaluation rows to prevent duplicates
            $this->db->where('id_kunjungan', $id)->delete('tamdes_evaluasi_detail');

            // Insert evaluation rows: skala Likert 1-10 untuk kepuasan saja (kepentingan deprecated).
            foreach ($kepuasan as $indikator_id => $val_kepuasan) {
                if (!$val_kepuasan || $val_kepuasan < 1 || $val_kepuasan > 10) continue;

                $this->db->insert('tamdes_evaluasi_detail', [
                    'id_kunjungan' => $id,
                    'indikator_id' => (int) $indikator_id,
                    'kepentingan'  => null,
                    'kepuasan'     => (int) $val_kepuasan,
                ]);
            }

            // Update kualitas per data konsultasi (status_data 1 atau 2)
            if (is_array($kualitas_per_konsultasi)) {
                foreach ($kualitas_per_konsultasi as $konsultasi_id => $val_kualitas) {
                    if (!is_numeric($val_kualitas) || $val_kualitas < 1 || $val_kualitas > 10) continue;
                    $this->db
                        ->where('id', (int) $konsultasi_id)
                        ->where('id_kunjungan', $id)
                        ->update('konsultasi_pengunjung', ['kualitas' => (int) $val_kualitas]);
                }
            }

            // Update kunjungan: rating, status, selesai_timestamp, durasi_detik
            $selesai_timestamp = date('Y-m-d H:i:s');
            $update = [
                'rating_pengunjung'  => $skor_keseluruhan,
                'status'             => 'selesai',
                'selesai_timestamp'  => $selesai_timestamp,
            ];
            if ($visit->date_visit) {
                $durasi             = strtotime($selesai_timestamp) - strtotime($visit->date_visit);
                $update['durasi_detik'] = max(0, $durasi);
            }
            $this->db->where('id_kunjungan', $id)->update('tamdes_kunjungan', $update);

            $this->json_response(['success' => true, 'data' => null, 'message' => 'Evaluasi berhasil disimpan']);
        } else {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
    }

    public function results($id) {
        $this->require_auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $visit = $this->db->select('rating_pengunjung, status, durasi_detik')
                          ->get_where('tamdes_kunjungan', ['id_kunjungan' => $id])
                          ->row();

        if (!$visit) {
            $this->json_response(['success' => false, 'message' => 'Kunjungan tidak ditemukan'], 404);
        }

        $details = $this->db->get_where('tamdes_evaluasi_detail', ['id_kunjungan' => $id])->result();

        $this->json_response([
            'success' => true,
            'data' => [
                'rating_pengunjung' => $visit->rating_pengunjung,
                'status'            => $visit->status,
                'durasi_detik'      => $visit->durasi_detik,
                'details'           => $details,
                'indikator'         => $this->indikator_list(),
            ],
            'message' => 'OK',
        ]);
    }

    /**
     * GET /api/evaluations/summary — all evaluations with avg scores
     */
    public function summary() {
        $this->require_auth();
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $tahun = $this->input->get('tahun');

        // Per-visit summary
        $this->db->select('d.id_kunjungan, b.nama, k.jenis_layanan, k.date_visit, k.rating_pengunjung,
                           AVG(d.kepentingan) as avg_kepentingan, AVG(d.kepuasan) as avg_kepuasan,
                           COUNT(d.id) as jumlah_indikator')
                 ->from('tamdes_evaluasi_detail d')
                 ->join('tamdes_kunjungan k', 'd.id_kunjungan = k.id_kunjungan')
                 ->join('tamdes_buku b', 'k.id_user = b.id_user', 'left')
                 ->group_by('d.id_kunjungan')
                 ->order_by('k.date_visit', 'DESC');

        if ($tahun) {
            $this->db->where('YEAR(k.date_visit)', $tahun);
        }

        $visits = $this->db->get()->result();

        // Per-indicator average (IKM breakdown)
        $this->db->select('d.indikator_id, AVG(d.kepentingan) as avg_kepentingan, AVG(d.kepuasan) as avg_kepuasan, COUNT(DISTINCT d.id_kunjungan) as responden')
                 ->from('tamdes_evaluasi_detail d')
                 ->join('tamdes_kunjungan k', 'd.id_kunjungan = k.id_kunjungan');

        if ($tahun) {
            $this->db->where('YEAR(k.date_visit)', $tahun);
        }

        $indicators = $this->db->group_by('d.indikator_id')->order_by('d.indikator_id', 'ASC')->get()->result();

        // Overall average
        $this->db->select('AVG(d.kepuasan) as ikm_score, COUNT(DISTINCT d.id_kunjungan) as total_responden')
                 ->from('tamdes_evaluasi_detail d')
                 ->join('tamdes_kunjungan k', 'd.id_kunjungan = k.id_kunjungan');

        if ($tahun) {
            $this->db->where('YEAR(k.date_visit)', $tahun);
        }

        $overall = $this->db->get()->row();

        $this->json_response([
            'success' => true,
            'data'    => [
                'visits'     => $visits,
                'indicators' => $indicators,
                'overall'    => $overall,
                'labels'     => $this->indikator_list(),
            ],
            'message' => 'OK',
        ]);
    }

    /**
     * Blok II. Kepuasan terhadap Pelayanan Data dan Informasi Statistik BPS.
     * 16 indikator. Skala penilaian: Likert 1-10 (1 = sangat tidak puas, 10 = sangat puas).
     * Hanya tingkat kepuasan; tingkat kepentingan tidak dipakai lagi.
     */
    private function indikator_list() {
        return [
            1  => 'Informasi pelayanan pada unit layanan ini tersedia melalui media elektronik maupun non elektronik.',
            2  => 'Persyaratan pelayanan yang ditetapkan mudah dipenuhi/disiapkan oleh konsumen.',
            3  => 'Prosedur/alur pelayanan yang ditetapkan mudah diikuti/dilakukan.',
            4  => 'Jangka waktu penyelesaian pelayanan yang diterima sesuai dengan yang ditetapkan.',
            5  => 'Biaya pelayanan yang dibayarkan sesuai dengan biaya yang ditetapkan.',
            6  => 'Produk pelayanan yang diterima sesuai dengan yang dijanjikan.',
            7  => 'Sarana dan prasarana pendukung pelayanan memberikan kenyamanan.',
            8  => 'Data BPS mudah diakses melalui sarana utama yang digunakan.',
            9  => 'Petugas pelayanan dan/atau aplikasi pelayanan online merespon dengan baik.',
            10 => 'Petugas pelayanan dan/atau aplikasi pelayanan online mampu memberikan informasi yang jelas.',
            11 => 'Fasilitas pengaduan PST mudah diakses (contoh: Kotak saran dan pengaduan, website https://webapps.bps.go.id/pengaduan, e-mail bpshq@bps.go.id).',
            12 => 'Tidak ada diskriminasi dalam pelayanan.',
            13 => 'Tidak ada pelayanan di luar prosedur/kecurangan pelayanan.',
            14 => 'Tidak ada penerimaan gratifikasi.',
            15 => 'Tidak ada pungutan liar (pungli) dalam pelayanan.',
            16 => 'Tidak ada praktik percaloan dalam pelayanan.',
        ];
    }
}
