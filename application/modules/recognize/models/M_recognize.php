<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_recognize extends CI_Model {

    public function get_known_faces() {
        $this->db->where('face_descriptor IS NOT NULL', null, false);
        $query = $this->db->get('tamdes_buku');

        $data = [];
        foreach ($query->result() as $row) {
            $data[] = [
                'name' => $row->nama,
                'descriptor' => json_decode($row->face_descriptor)
            ];
        }

        return $data;
    }
}
