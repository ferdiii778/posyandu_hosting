<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Api extends CI_Controller {

    public function login()
{
    $data = json_decode($this->input->raw_input_stream, true);

    $username = $data['username'] ?? '';
    $password = $data['password'] ?? '';

    if (empty($username) || empty($password)) {
        $response = [
            'status' => false,
            'message' => 'Username dan password wajib diisi'
        ];
        return $this->output
            ->set_content_type('application/json')
            ->set_output(json_encode($response));
    }

    // Hash password input agar sesuai dengan yang ada di database
    $hashedPassword = md5($password);

    $user = $this->db->get_where('user', [
        'username' => $username,
        'password' => $hashedPassword,
        'aktif'    => 1
    ])->row_array();

    if ($user) {
        $response = [
            'status' => true,
            'message' => 'Login berhasil',
            'data' => [
                'id_user'  => $user['id_user'],
                'nama'     => $user['nama'],
                'username' => $user['username'],
                'role'     => $user['role']
            ]
        ];
    } else {
        $response = [
            'status' => false,
            'message' => 'Username atau password salah'
        ];
    }

    return $this->output
        ->set_content_type('application/json')
        ->set_output(json_encode($response));
}

}
