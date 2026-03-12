<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/api/controllers/Api_base.php';

class Guests extends Api_base {

    public function index() {
        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $this->require_auth();
            $search = $this->input->get('search');
            $page = (int) ($this->input->get('page') ?: 1);
            $limit = (int) ($this->input->get('limit') ?: 10);
            $offset = ($page - 1) * $limit;

            $this->db->from('tamdes_buku');
            if ($search) {
                $this->db->group_start();
                $this->db->like('nama', $search);
                $this->db->or_like('email', $search);
                $this->db->or_like('nama_instansi', $search);
                $this->db->group_end();
            }
            $total = $this->db->count_all_results('', false);
            $guests = $this->db->limit($limit, $offset)->get()->result();

            $this->json_response([
                'success' => true,
                'data' => $guests,
                'message' => 'OK',
                'pagination' => [
                    'page' => $page,
                    'limit' => $limit,
                    'total' => $total,
                    'totalPages' => max(1, ceil($total / $limit)),
                ],
            ]);
        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $this->require_auth();
            $input = $this->get_json_input();

            // Generate id_user (not auto-increment)
            $max = $this->db->select_max('id_user')->get('tamdes_buku')->row()->id_user;
            $new_id = $max ? $max + 1 : 8200001;

            $data = [
                'id_user' => $new_id,
                'nama' => $input['nama'] ?? '',
                'email' => $input['email'] ?? '',
                'notel' => $input['notel'] ?? '',
                'jeniskelamin' => $input['jeniskelamin'] ?? '',
                'pendidikan' => $input['pendidikan'] ?? '',
                'pekerjaan' => $input['pekerjaan'] ?? '',
                'kategori_instansi' => $input['kategori_instansi'] ?? '',
                'nama_instansi' => $input['nama_instansi'] ?? '',
                'pemanfaatan' => $input['pemanfaatan'] ?? '',
                'pengaduan' => $input['pengaduan'] ?? '',
                'tgldatang' => date('Y-m-d'),
                'face_descriptor' => isset($input['face_descriptor']) ? json_encode($input['face_descriptor']) : null,
            ];
            $this->db->insert('tamdes_buku', $data);
            $guest = $this->db->get_where('tamdes_buku', ['id_user' => $new_id])->row();
            $this->json_response(['success' => true, 'data' => $guest, 'message' => 'Tamu berhasil ditambahkan'], 201);
        }
    }

    public function detail($id) {
        $this->require_auth();

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $guest = $this->db->get_where('tamdes_buku', ['id_user' => $id])->row();
            if (!$guest) {
                $this->json_response(['success' => false, 'message' => 'Tamu tidak ditemukan'], 404);
            }
            $this->json_response(['success' => true, 'data' => $guest, 'message' => 'OK']);

        } elseif ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $input = $this->get_json_input();
            // Remove id_user from update data to prevent PK modification
            unset($input['id_user']);
            $this->db->where('id_user', $id)->update('tamdes_buku', $input);
            $guest = $this->db->get_where('tamdes_buku', ['id_user' => $id])->row();
            $this->json_response(['success' => true, 'data' => $guest, 'message' => 'Tamu berhasil diupdate']);

        } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            $this->db->where('id_user', $id)->delete('tamdes_buku');
            $this->json_response(['success' => true, 'data' => null, 'message' => 'Tamu berhasil dihapus']);
        }
    }
}
