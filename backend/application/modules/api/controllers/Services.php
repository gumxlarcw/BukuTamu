<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/api/controllers/Api_base.php';

class Services extends Api_base {

    public function index() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        // Urutan ini menentukan layout grid di kiosk (4 kolom):
        // Baris 1: 4 layanan inti SKD. Baris 2: DTSEN, Keperluan Pimpinan, Lainnya.
        $services = [
            ['id' => 'perpustakaan', 'name' => 'Perpustakaan', 'icon' => 'book', 'description' => 'Layanan perpustakaan dan referensi'],
            ['id' => 'konsultasi', 'name' => 'Konsultasi Statistik', 'icon' => 'chart', 'description' => 'Konsultasi data dan statistik'],
            ['id' => 'rekomendasi', 'name' => 'Rekomendasi Kegiatan Statistik', 'icon' => 'clipboard', 'description' => 'Rekomendasi kegiatan statistik'],
            ['id' => 'penjualan', 'name' => 'Penjualan Produk Statistik', 'icon' => 'shopping-cart', 'description' => 'Pembelian publikasi dan data'],
            ['id' => 'konsultasi_dtsen', 'name' => 'Konsultasi DTSEN', 'icon' => 'database', 'description' => 'Konsultasi Data Terpadu Sosial Ekonomi Nasional (di luar kuesioner SKD)'],
            ['id' => 'pimpinan', 'name' => 'Keperluan Pimpinan', 'icon' => 'user', 'description' => 'Keperluan bertemu pimpinan'],
            ['id' => 'lainnya', 'name' => 'Lainnya', 'icon' => 'more', 'description' => 'Keperluan lainnya'],
        ];
        $this->json_response(['success' => true, 'data' => $services, 'message' => 'OK']);
    }
}
