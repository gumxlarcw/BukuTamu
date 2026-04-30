<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_base extends CI_Controller {

    protected $current_user = null;

    public function __construct() {
        parent::__construct();
        $this->load->library('JWT_Helper');

        // CORS headers
        $allowed_origins = ['http://localhost:5173'];
        $prod_origin = $this->_env('FRONTEND_URL');
        if ($prod_origin) $allowed_origins[] = $prod_origin;

        $origin = isset($_SERVER['HTTP_ORIGIN']) ? $_SERVER['HTTP_ORIGIN'] : '';
        if (in_array($origin, $allowed_origins)) {
            header('Access-Control-Allow-Origin: ' . $origin);
        }

        header('Content-Type: application/json');
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    protected function require_auth() {
        $token = isset($_COOKIE['jwt_token']) ? $_COOKIE['jwt_token'] : null;
        if (!$token) {
            $this->json_response(['success' => false, 'message' => 'Unauthorized'], 401);
            exit;
        }
        $decoded = $this->jwt_helper->decode($token);
        if (!$decoded) {
            $this->json_response(['success' => false, 'message' => 'Invalid token'], 401);
            exit;
        }
        $this->current_user = $decoded;
    }

    protected function json_response($data, $status = 200) {
        http_response_code($status);
        echo json_encode($data);
        exit;
    }

    protected function get_json_input() {
        return json_decode(file_get_contents('php://input'), true) ?? [];
    }

    private static $_dotenv_cache = null;

    protected function _env($key, $default = '') {
        // Try getenv first (works if putenv succeeded)
        $val = getenv($key);
        if ($val !== false && $val !== '') return $val;

        // Try $_SERVER / $_ENV
        if (isset($_SERVER[$key]) && $_SERVER[$key] !== '') return $_SERVER[$key];
        if (isset($_ENV[$key]) && $_ENV[$key] !== '') return $_ENV[$key];

        // Fallback: parse .env file directly
        if (self::$_dotenv_cache === null) {
            self::$_dotenv_cache = [];
            $envFile = FCPATH . '.env';
            if (is_readable($envFile)) {
                $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
                foreach ($lines as $line) {
                    $line = trim($line);
                    if ($line === '' || $line[0] === '#' || $line[0] === ';') continue;
                    $pos = strpos($line, '=');
                    if ($pos === false) continue;
                    $k = trim(substr($line, 0, $pos));
                    $v = trim(substr($line, $pos + 1));
                    $len = strlen($v);
                    if ($len >= 2) {
                        $f = $v[0]; $l = $v[$len - 1];
                        if (($f === '"' && $l === '"') || ($f === "'" && $l === "'")) {
                            $v = substr($v, 1, -1);
                        }
                    }
                    self::$_dotenv_cache[$k] = $v;
                }
            }
        }

        return isset(self::$_dotenv_cache[$key]) ? self::$_dotenv_cache[$key] : $default;
    }

    protected function require_role($min_role) {
        // resepsionis & petugas_pst share level 1 with legacy operator (different scopes, same tier)
        $role_level = [
            'operator'    => 1,
            'resepsionis' => 1,
            'petugas_pst' => 1,
            'admin'       => 2,
            'superadmin'  => 3,
        ];
        $user_role = isset($this->current_user->role) ? $this->current_user->role : 'operator';
        $user_lvl = isset($role_level[$user_role]) ? $role_level[$user_role] : 1;
        $min_lvl = isset($role_level[$min_role]) ? $role_level[$min_role] : 1;
        if ($user_lvl < $min_lvl) {
            $this->json_response(['success' => false, 'message' => 'Akses ditolak. Role tidak mencukupi.'], 403);
        }
    }

    /**
     * Layanan-based authorization. Pastikan role user sesuai dengan jenis layanan visit.
     * - petugas_pst: 4 layanan PST (Perpustakaan, Konsultasi Statistik, Rekomendasi, Penjualan)
     * - resepsionis: layanan front-office (Lainnya, Keperluan Pimpinan)
     * - admin/superadmin: bypass (full access)
     * - operator (legacy): bypass (untuk backward compat)
     *
     * $jenis_layanan_raw bisa string atau JSON-encoded array dari kolom DB.
     */
    protected function require_layanan_role($jenis_layanan_raw) {
        $role = isset($this->current_user->role) ? $this->current_user->role : 'operator';

        // Bypass roles: full access untuk superadmin, admin, dan operator legacy.
        if (in_array($role, ['admin', 'superadmin', 'operator'], true)) {
            return;
        }

        // Decode jenis_layanan: bisa array, JSON string, atau plain string.
        $list = [];
        if (is_array($jenis_layanan_raw)) {
            $list = $jenis_layanan_raw;
        } elseif (is_string($jenis_layanan_raw) && $jenis_layanan_raw !== '') {
            $decoded = json_decode($jenis_layanan_raw, true);
            $list = is_array($decoded) ? $decoded : [$jenis_layanan_raw];
        }

        $pst_services = [
            'Perpustakaan',
            'Konsultasi Statistik',
            'Rekomendasi Kegiatan Statistik',
            'Penjualan Produk Statistik',
        ];
        $resepsionis_services = ['Lainnya', 'Keperluan Pimpinan'];

        foreach ($list as $layanan) {
            $is_pst   = in_array($layanan, $pst_services, true);
            $is_resep = in_array($layanan, $resepsionis_services, true);

            if ($is_pst && $role !== 'petugas_pst') {
                $this->json_response([
                    'success' => false,
                    'message' => "Layanan \"{$layanan}\" hanya bisa diselesaikan oleh Petugas PST.",
                ], 403);
            }
            if ($is_resep && $role !== 'resepsionis') {
                $this->json_response([
                    'success' => false,
                    'message' => "Layanan \"{$layanan}\" hanya bisa diselesaikan oleh Resepsionis.",
                ], 403);
            }
        }
    }

    /**
     * Tentukan status finalisasi berdasarkan jenis layanan.
     * - PST (Perpustakaan, Konsultasi, Rekomendasi, Penjualan) → 'menunggu_evaluasi' (perlu evaluasi tablet)
     * - Resepsionis (Lainnya, Keperluan Pimpinan) → 'selesai' langsung (tidak perlu evaluasi)
     * - Multi-layanan: jika ada minimal satu PST → tetap 'menunggu_evaluasi'
     */
    protected function next_status_after_completion($jenis_layanan_raw) {
        $list = [];
        if (is_array($jenis_layanan_raw)) {
            $list = $jenis_layanan_raw;
        } elseif (is_string($jenis_layanan_raw) && $jenis_layanan_raw !== '') {
            $decoded = json_decode($jenis_layanan_raw, true);
            $list = is_array($decoded) ? $decoded : [$jenis_layanan_raw];
        }
        $pst_services = [
            'Perpustakaan',
            'Konsultasi Statistik',
            'Rekomendasi Kegiatan Statistik',
            'Penjualan Produk Statistik',
        ];
        foreach ($list as $layanan) {
            if (in_array($layanan, $pst_services, true)) {
                return 'menunggu_evaluasi';
            }
        }
        return 'selesai';
    }

    protected function audit($action, $target_type, $target_id = null, $detail = null) {
        $user = $this->current_user->username ?? 'system';
        $this->db->insert('tamdes_audit_log', [
            'admin_user'  => $user,
            'action'      => $action,
            'target_type' => $target_type,
            'target_id'   => $target_id,
            'detail'      => $detail ? json_encode($detail) : null,
            'ip_address'  => $this->input->ip_address(),
        ]);
    }

    /**
     * Compare old row (assoc array) with new data, return only changed fields.
     * Format: { field: { from: old_val, to: new_val } }
     */
    protected function diff_changes($old, $new) {
        $changes = [];
        foreach ($new as $key => $new_val) {
            $old_val = isset($old[$key]) ? $old[$key] : null;
            // Normalize for comparison
            if (is_numeric($old_val) && is_numeric($new_val)) {
                if ((float)$old_val === (float)$new_val) continue;
            } elseif ((string)$old_val === (string)$new_val) {
                continue;
            }
            $changes[$key] = ['from' => $old_val, 'to' => $new_val];
        }
        return $changes;
    }

    protected function generate_queue_number($jenis_layanan) {
        if (in_array(strtolower($jenis_layanan), ['lainnya', 'keperluan pimpinan'])) {
            return null;
        }

        $prefix = strtoupper(substr($jenis_layanan, 0, 1));
        $today  = date('Y-m-d');

        $count = $this->db->where('DATE(date_visit)', $today)
                          ->where('jenis_layanan', $jenis_layanan)
                          ->count_all_results('tamdes_kunjungan');

        $number = $count + 1;

        return $prefix . str_pad($number, 3, '0', STR_PAD_LEFT);
    }
}
