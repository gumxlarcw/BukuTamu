<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/api/controllers/Api_base.php';

class Evaluations extends Api_base {

    public function pending() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $visit = $this->db
            ->order_by('id_kunjungan', 'ASC')
            ->get_where('tamdes_kunjungan', ['status' => 'menunggu_evaluasi'])
            ->row();

        $this->json_response(['success' => true, 'data' => $visit, 'message' => 'OK']);
    }

    public function detail($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $indikator = $this->indikator_list();
            $evaluation = $this->db->get_where('tamdes_evaluasi_detail', ['id_kunjungan' => $id])->result();

            $this->json_response([
                'success' => true,
                'data' => [
                    'indikator'  => $indikator,
                    'evaluation' => $evaluation,
                ],
                'message' => 'OK',
            ]);

        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input            = $this->get_json_input();
            $skor_keseluruhan = $input['skor_keseluruhan'] ?? null;
            $kepentingan      = $input['kepentingan'] ?? [];
            $kepuasan         = $input['kepuasan'] ?? [];

            if (!$skor_keseluruhan || !is_numeric($skor_keseluruhan) || $skor_keseluruhan < 1 || $skor_keseluruhan > 10) {
                $this->json_response(['success' => false, 'message' => 'skor_keseluruhan harus antara 1-10'], 400);
            }

            if (!is_array($kepentingan) || !is_array($kepuasan)) {
                $this->json_response(['success' => false, 'message' => 'kepentingan dan kepuasan harus berupa array'], 400);
            }

            $visit = $this->db->get_where('tamdes_kunjungan', ['id_kunjungan' => $id])->row();
            if (!$visit) {
                $this->json_response(['success' => false, 'message' => 'Kunjungan tidak ditemukan'], 404);
            }

            // Delete existing evaluation rows to prevent duplicates
            $this->db->where('id_kunjungan', $id)->delete('tamdes_evaluasi_detail');

            // Insert evaluation rows per indicator
            foreach ($kepentingan as $indikator_id => $val_kepentingan) {
                $val_kepuasan = $kepuasan[$indikator_id] ?? null;
                if (!$val_kepuasan || $val_kepentingan < 1 || $val_kepuasan < 1) continue;

                $this->db->insert('tamdes_evaluasi_detail', [
                    'id_kunjungan' => $id,
                    'indikator_id' => $indikator_id,
                    'kepentingan'  => $val_kepentingan,
                    'kepuasan'     => $val_kepuasan,
                ]);
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

    private function indikator_list() {
        return [
            1  => 'Informasi pelayanan tersedia secara elektronik maupun non-elektronik.',
            2  => 'Persyaratan pelayanan mudah dipenuhi.',
            3  => 'Prosedur pelayanan mudah diikuti.',
            4  => 'Waktu pelayanan sesuai dengan yang ditetapkan.',
            5  => 'Biaya pelayanan sesuai ketentuan.',
            6  => 'Produk pelayanan sesuai yang dijanjikan.',
            7  => 'Sarana prasarana nyaman.',
            8  => 'Data mudah diakses.',
            9  => 'Petugas merespon dengan baik.',
            10 => 'Petugas memberi informasi jelas.',
            11 => 'Fasilitas pengaduan mudah diketahui.',
            12 => 'Proses pengaduan jelas dan tidak rumit.',
            13 => 'Tidak ada diskriminasi pelayanan.',
            14 => 'Tidak ada kecurangan/pelayanan di luar prosedur.',
            15 => 'Tidak ada penerimaan gratifikasi.',
            16 => 'Tidak ada pungutan liar.',
            17 => 'Tidak ada praktik percaloan.',
        ];
    }
}
