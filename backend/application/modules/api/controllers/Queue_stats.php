<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/api/controllers/Api_base.php';

class Queue_stats extends Api_base {

    public function index() {
        $this->require_auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $tahun = $this->input->get('tahun') ?: date('Y');

        // Avg wait time (durasi_detik for completed visits)
        $avg_wait = $this->db->select('AVG(durasi_detik) as avg_durasi, MIN(durasi_detik) as min_durasi, MAX(durasi_detik) as max_durasi, COUNT(*) as total_selesai')
                             ->where('status', 'selesai')
                             ->where('durasi_detik IS NOT NULL')
                             ->where('YEAR(date_visit)', $tahun)
                             ->get('tamdes_kunjungan')->row();

        // Visits per hour distribution
        $hourly = $this->db->select('HOUR(date_visit) as jam, COUNT(*) as jumlah')
                           ->where('YEAR(date_visit)', $tahun)
                           ->group_by('HOUR(date_visit)')
                           ->order_by('jam', 'ASC')
                           ->get('tamdes_kunjungan')->result();

        // Visits per day of week
        $daily = $this->db->select('DAYNAME(date_visit) as hari, DAYOFWEEK(date_visit) as dow, COUNT(*) as jumlah')
                          ->where('YEAR(date_visit)', $tahun)
                          ->group_by('DAYOFWEEK(date_visit)')
                          ->order_by('dow', 'ASC')
                          ->get('tamdes_kunjungan')->result();

        // Visits per month
        $monthly = $this->db->select('MONTH(date_visit) as bulan, COUNT(*) as jumlah')
                            ->where('YEAR(date_visit)', $tahun)
                            ->group_by('MONTH(date_visit)')
                            ->order_by('bulan', 'ASC')
                            ->get('tamdes_kunjungan')->result();

        // Service distribution
        $services = $this->db->select('jenis_layanan, COUNT(*) as jumlah')
                             ->where('YEAR(date_visit)', $tahun)
                             ->group_by('jenis_layanan')
                             ->order_by('jumlah', 'DESC')
                             ->get('tamdes_kunjungan')->result();

        // Status distribution
        $statuses = $this->db->select('status, COUNT(*) as jumlah')
                             ->where('YEAR(date_visit)', $tahun)
                             ->group_by('status')
                             ->get('tamdes_kunjungan')->result();

        $this->json_response([
            'success' => true,
            'data' => [
                'avg_wait' => $avg_wait,
                'hourly'   => $hourly,
                'daily'    => $daily,
                'monthly'  => $monthly,
                'services' => $services,
                'statuses' => $statuses,
            ],
            'message' => 'OK',
        ]);
    }
}
