<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/api/controllers/Api_base.php';

class Audit extends Api_base {

    public function index() {
        $this->require_auth();
        $this->require_role('admin');

        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $page   = (int) ($this->input->get('page') ?: 1);
        $limit  = (int) ($this->input->get('limit') ?: 20);
        $offset = ($page - 1) * $limit;

        $total = $this->db->count_all('tamdes_audit_log');

        $data = $this->db->order_by('created_at', 'DESC')
                         ->limit($limit, $offset)
                         ->get('tamdes_audit_log')->result();

        $this->json_response([
            'success'    => true,
            'data'       => $data,
            'pagination' => [
                'page'       => $page,
                'limit'      => $limit,
                'total'      => $total,
                'totalPages' => max(1, ceil($total / $limit)),
            ],
            'message'    => 'OK',
        ]);
    }
}
