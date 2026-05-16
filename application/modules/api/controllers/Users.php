<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/api/controllers/Api_base.php';

class Users extends Api_base {

    public function index() {
        $this->require_auth();
        $this->require_role('superadmin');

        if ($_SERVER['REQUEST_METHOD'] === 'GET') {
            $users = $this->db->select('id, username, nama, role, active, last_login, created_at')
                              ->order_by('id', 'ASC')
                              ->get('admin_users')->result();
            $this->json_response(['success' => true, 'data' => $users, 'message' => 'OK']);

        } elseif ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $input    = $this->get_json_input();
            $username = trim($input['username'] ?? '');
            $password = $input['password'] ?? '';
            $nama     = trim($input['nama'] ?? '');
            $role     = $input['role'] ?? 'operator';

            if (empty($username) || empty($password) || empty($nama)) {
                $this->json_response(['success' => false, 'message' => 'Username, password, dan nama wajib diisi'], 400);
            }
            if (strlen($password) < 8 || !preg_match('/[A-Za-z]/', $password) || !preg_match('/[0-9]/', $password)) {
                $this->json_response(['success' => false, 'message' => 'Password minimal 8 karakter, harus mengandung huruf dan angka'], 400);
            }

            $exists = $this->db->get_where('admin_users', ['username' => $username])->row();
            if ($exists) {
                $this->json_response(['success' => false, 'message' => 'Username sudah digunakan'], 409);
            }

            $this->db->insert('admin_users', [
                'username'      => $username,
                'password_hash' => password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]),
                'nama'          => $nama,
                'role'          => in_array($role, ['superadmin', 'admin', 'operator']) ? $role : 'operator',
                'active'        => 1,
            ]);

            $this->audit('create', 'admin_user', $this->db->insert_id(), ['username' => $username, 'role' => $role]);

            $this->json_response(['success' => true, 'data' => null, 'message' => 'User berhasil dibuat'], 201);
        }
    }

    public function detail($id) {
        $this->require_auth();
        $this->require_role('superadmin');

        if ($_SERVER['REQUEST_METHOD'] === 'PUT') {
            $input  = $this->get_json_input();
            $update = [];

            if (isset($input['nama']))   $update['nama']   = trim($input['nama']);
            if (isset($input['role']))   $update['role']    = in_array($input['role'], ['superadmin', 'admin', 'operator']) ? $input['role'] : 'operator';
            if (isset($input['active'])) $update['active']  = $input['active'] ? 1 : 0;

            if (isset($input['password']) && !empty($input['password'])) {
                $pw = $input['password'];
                if (strlen($pw) < 8 || !preg_match('/[A-Za-z]/', $pw) || !preg_match('/[0-9]/', $pw)) {
                    $this->json_response(['success' => false, 'message' => 'Password minimal 8 karakter, harus mengandung huruf dan angka'], 400);
                }
                $update['password_hash'] = password_hash($pw, PASSWORD_BCRYPT, ['cost' => 12]);
            }

            if (empty($update)) {
                $this->json_response(['success' => false, 'message' => 'Tidak ada data untuk diupdate'], 400);
            }

            $this->db->where('id', $id)->update('admin_users', $update);
            $this->audit('update', 'admin_user', $id, array_diff_key($update, ['password_hash' => '']));

            $this->json_response(['success' => true, 'data' => null, 'message' => 'User berhasil diupdate']);

        } elseif ($_SERVER['REQUEST_METHOD'] === 'DELETE') {
            // Prevent deleting self
            if (isset($this->current_user->id) && (int)$this->current_user->id === (int)$id) {
                $this->json_response(['success' => false, 'message' => 'Tidak bisa menghapus akun sendiri'], 400);
            }
            $this->db->where('id', $id)->delete('admin_users');
            $this->audit('delete', 'admin_user', $id);
            $this->json_response(['success' => true, 'data' => null, 'message' => 'User berhasil dihapus']);
        }
    }

    /** POST /api/users/change-password — change own password */
    public function change_password() {
        $this->require_auth();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }

        $input       = $this->get_json_input();
        $old_pass    = $input['old_password'] ?? '';
        $new_pass    = $input['new_password'] ?? '';
        $username    = $this->current_user->username;

        if (empty($old_pass) || empty($new_pass)) {
            $this->json_response(['success' => false, 'message' => 'Password lama dan baru wajib diisi'], 400);
        }
        if (strlen($new_pass) < 8 || !preg_match('/[A-Za-z]/', $new_pass) || !preg_match('/[0-9]/', $new_pass)) {
            $this->json_response(['success' => false, 'message' => 'Password baru minimal 8 karakter, harus mengandung huruf dan angka'], 400);
        }

        $user = $this->db->get_where('admin_users', ['username' => $username])->row();
        if (!$user || !password_verify($old_pass, $user->password_hash)) {
            $this->json_response(['success' => false, 'message' => 'Password lama salah'], 401);
        }

        $this->db->where('id', $user->id)->update('admin_users', [
            'password_hash' => password_hash($new_pass, PASSWORD_BCRYPT, ['cost' => 12]),
        ]);

        $this->audit('change_password', 'admin_user', $user->id);
        $this->json_response(['success' => true, 'data' => null, 'message' => 'Password berhasil diubah']);
    }
}
