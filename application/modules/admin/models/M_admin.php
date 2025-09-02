<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin extends CI_Model
{
    public function get_all_tamu()
    {
        return $this->db->get('tamdes_buku')->result();
    }

    public function get_tamu_by_id($id_user)
    {
        return $this->db->get_where('tamdes_buku', ['id_user' => $id_user])->row();
    }

    public function insert_tamu()
    {
        $new_id_user = uniqid('usr_');

        $data = [
            'id_user' => $new_id_user,
            'tgldatang' => $this->input->post('tgldatang'),
            'nama' => $this->input->post('nama'),
            'email' => $this->input->post('email'),
            'notel' => $this->input->post('notel'),
            'jeniskelamin' => $this->input->post('jeniskelamin'),
            'pendidikan' => $this->input->post('pendidikan'),
            'pekerjaan' => $this->input->post('pekerjaan'),
            'kategori_instansi' => $this->input->post('kategori_instansi'),
            'nama_instansi' => $this->input->post('nama_instansi'),
            'pemanfaatan' => $this->input->post('pemanfaatan'),
            'sarana' => json_encode(['1 : Pelayanan Statistik Terpadu (PST) datang langsung']),
            'pengaduan' => $this->input->post('pengaduan')
        ];

        $this->db->insert('tamdes_buku', $data);
    }

    public function update_tamu($id_user)
    {
        $data = [
            'tgldatang' => $this->input->post('tgldatang'),
            'nama' => $this->input->post('nama'),
            'email' => $this->input->post('email'),
            'notel' => $this->input->post('notel'),
            'jeniskelamin' => $this->input->post('jeniskelamin'),
            'pendidikan' => $this->input->post('pendidikan'),
            'pekerjaan' => $this->input->post('pekerjaan'),
            'kategori_instansi' => $this->input->post('kategori_instansi'),
            'nama_instansi' => $this->input->post('nama_instansi'),
            'pemanfaatan' => $this->input->post('pemanfaatan'),
            'sarana' => json_encode(['1 : Pelayanan Statistik Terpadu (PST) datang langsung']),
            'pengaduan' => $this->input->post('pengaduan')
        ];

        $this->db->where('id_user', $id_user);
        $this->db->update('tamdes_buku', $data);
    }

    public function count_kunjungan_today() {
        $today = date('Y-m-d');
        $this->db->where('DATE(date_visit)', $today);
        return $this->db->count_all_results('tamdes_kunjungan');
    }
    
    
    public function count_kunjungan_bulan_ini() {
        $this->db->where('MONTH(date_visit)', date('m'));
        $this->db->where('YEAR(date_visit)', date('Y'));
        return $this->db->count_all_results('tamdes_kunjungan');
    }
    
    public function count_kunjungan_all() {
        return $this->db->count_all('tamdes_kunjungan');
    }

    public function delete_tamu($id_user)
    {
        $this->db->where('id_user', $id_user);
        $this->db->delete('tamdes_buku');
    }

    public function count_tamu_unik() {
        return $this->db
            ->select('COUNT(DISTINCT id_user) AS total', false)
            ->get('tamdes_buku')
            ->row()
            ->total;
    }

    public function get_evaluasi_by_kunjungan($id_kunjungan)
    {
        return $this->db->get_where('evaluasi_pengunjung', ['id_kunjungan' => $id_kunjungan])->row();
    }
}
