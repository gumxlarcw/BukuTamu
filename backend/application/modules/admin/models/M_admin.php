<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class M_admin extends CI_Model
{
    private function normalize_tgldatang($value)
    {
        if ($value === null) {
            return null;
        }
        $value = (string) $value;
        // accept 'YYYY-MM-DD' or 'YYYY-MM-DDTHH:MM'
        return ($value !== '') ? substr($value, 0, 10) : null;
    }

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
		$last = $this->db->select_max('id_user')->get('tamdes_buku')->row();
		$new_id_user = ($last && $last->id_user) ? ((int) $last->id_user + 1) : 8200001;
		$tgldatang = $this->normalize_tgldatang($this->input->post('tgldatang', TRUE));

        $data = [
            'id_user' => $new_id_user,
			'tgldatang' => $tgldatang,
            'nama' => $this->input->post('nama', TRUE),
            'email' => $this->input->post('email', TRUE),
            'notel' => $this->input->post('notel', TRUE),
            'jeniskelamin' => $this->input->post('jeniskelamin', TRUE),
            'pendidikan' => $this->input->post('pendidikan', TRUE),
            'pekerjaan' => $this->input->post('pekerjaan', TRUE),
            'kategori_instansi' => $this->input->post('kategori_instansi', TRUE),
            'nama_instansi' => $this->input->post('nama_instansi', TRUE),
            'pemanfaatan' => $this->input->post('pemanfaatan', TRUE),
            'sarana' => json_encode(['1 : Pelayanan Statistik Terpadu (PST) datang langsung']),
            'pengaduan' => $this->input->post('pengaduan', TRUE)
        ];

        // simpan ke tabel tamdes_buku
        $this->db->insert('tamdes_buku', $data);

        // buat kunjungan baru (AUTO_INCREMENT)
        $this->db->insert('tamdes_kunjungan', [
            'id_user'        => $new_id_user,
            'date_visit'     => date('Y-m-d H:i:s'),
            'jenis_layanan'  => null,
            'status'         => 'antri'
        ]);
        $new_id_kunjungan = (int) $this->db->insert_id();

        // simpan id ke session (supaya controller Layanan bisa update)
        $this->session->set_userdata('id_user', $new_id_user);
        $this->session->set_userdata('id_kunjungan', $new_id_kunjungan);
    }


    public function update_tamu($id_user)
    {
		$tgldatang = $this->normalize_tgldatang($this->input->post('tgldatang', TRUE));
        $data = [
			'tgldatang' => $tgldatang,
            'nama' => $this->input->post('nama', TRUE),
            'email' => $this->input->post('email', TRUE),
            'notel' => $this->input->post('notel', TRUE),
            'jeniskelamin' => $this->input->post('jeniskelamin', TRUE),
            'pendidikan' => $this->input->post('pendidikan', TRUE),
            'pekerjaan' => $this->input->post('pekerjaan', TRUE),
            'kategori_instansi' => $this->input->post('kategori_instansi', TRUE),
            'nama_instansi' => $this->input->post('nama_instansi', TRUE),
            'pemanfaatan' => $this->input->post('pemanfaatan', TRUE),
            'sarana' => json_encode(['1 : Pelayanan Statistik Terpadu (PST) datang langsung']),
            'pengaduan' => $this->input->post('pengaduan', TRUE)
        ];

        $this->db->where('id_user', $id_user);
        $this->db->update('tamdes_buku', $data);
    }

    public function count_kunjungan_today() { 
        $today = date('Y-m-d'); 
        $this->db->where('DATE(date_visit)', $today); 
        return $this->db->count_all_results('tamdes_kunjungan'); }
    
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
