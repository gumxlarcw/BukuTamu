<?php

if (!function_exists('get_level_label')) {
    function get_level_label($val) {
        return [
            1 => 'Nasional',
            2 => 'Provinsi',
            3 => 'Kabupaten/Kota',
            4 => 'Kecamatan',
            5 => 'Desa/Kelurahan',
            6 => 'Individu',
            7 => 'Lainnya'
        ][$val] ?? '-';
    }
}

if (!function_exists('get_periode_label')) {
    function get_periode_label($val) {
        return [
            1 => 'Sepuluh Tahunan',
            2 => 'Lima Tahunan',
            3 => 'Tiga Tahunan',
            4 => 'Tahunan',
            5 => 'Semesteran',
            6 => 'Triwulanan',
            7 => 'Bulanan',
            8 => 'Mingguan',
            9 => 'Harian',
            10 => 'Lainnya'
        ][$val] ?? '-';
    }
}

if (!function_exists('get_status_label')) {
    function get_status_label($val) {
        return [
            1 => 'Ya, sesuai',
            2 => 'Ya, tidak sesuai',
            3 => 'Tidak diperoleh',
            4 => 'Belum Diperoleh'
        ][$val] ?? '-';
    }
}
