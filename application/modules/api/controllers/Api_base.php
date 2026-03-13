<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api_base extends CI_Controller {

    protected $current_user = null;

    public function __construct() {
        parent::__construct();
        $this->load->library('JWT_Helper');

        // CORS headers
        $allowed_origins = ['http://localhost:5173'];
        $prod_origin = getenv('FRONTEND_URL');
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
