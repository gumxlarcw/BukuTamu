<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_user extends CI_Model {

    public function get_all_with_descriptor() {
        return $this->db
            ->select('id_user, nama, nama_instansi, face_descriptor')
            ->where('face_descriptor IS NOT NULL', null, false)
            ->order_by('nama', 'ASC')  // ⬅️ Urut abjad dari SQL
            ->get('tamdes_buku')
            ->result();
    }
}
