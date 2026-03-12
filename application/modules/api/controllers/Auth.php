<?php
defined('BASEPATH') OR exit('No direct script access allowed');

require_once APPPATH . 'modules/api/controllers/Api_base.php';

class Auth extends Api_base {

    public function check() {
        if ($_SERVER['REQUEST_METHOD'] !== 'GET') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $this->require_auth();
        $this->json_response([
            'success' => true,
            'data' => [
                'id' => $this->current_user->id ?? 0,
                'username' => $this->current_user->username,
                'nama' => $this->current_user->nama ?? $this->current_user->username,
            ],
            'message' => 'Authenticated',
        ]);
    }

    public function login() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        $input = $this->get_json_input();
        $username = isset($input['username']) ? $input['username'] : '';
        $password = isset($input['password']) ? $input['password'] : '';

        if (empty($username) || empty($password)) {
            $this->json_response(['success' => false, 'message' => 'Username dan password wajib diisi'], 400);
        }

        // Auth uses env vars, same as existing Admin controller
        $valid_username = getenv('ADMIN_USERNAME') ?: 'admin';
        $password_hash = getenv('ADMIN_PASSWORD_HASH') ?: '';

        if ($username !== $valid_username || !password_verify($password, $password_hash)) {
            $this->json_response(['success' => false, 'message' => 'Username atau password salah'], 401);
        }

        $token = $this->jwt_helper->encode([
            'id' => 1,
            'username' => $username,
            'nama' => $username,
        ]);

        setcookie('jwt_token', $token, [
            'expires' => time() + 86400,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
            'secure' => isset($_SERVER['HTTPS']),
        ]);

        $this->json_response([
            'success' => true,
            'data' => ['id' => 1, 'username' => $username, 'nama' => $username],
            'message' => 'Login berhasil',
        ]);
    }

    public function logout() {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json_response(['success' => false, 'message' => 'Method not allowed'], 405);
        }
        setcookie('jwt_token', '', [
            'expires' => time() - 3600,
            'path' => '/',
            'httponly' => true,
            'samesite' => 'Strict',
        ]);
        $this->json_response(['success' => true, 'data' => null, 'message' => 'Logout berhasil']);
    }
}
