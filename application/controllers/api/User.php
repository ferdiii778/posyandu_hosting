<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class User extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Muser');
        header('Content-Type: application/json');
    }

    // GET semua user
    public function index() {
        $data = $this->Muser->getAll();
        echo json_encode(['status' => true, 'data' => $data]);
    }

    // GET user by ID
    public function detail($id) {
        $data = $this->Muser->getById($id);

        if ($data) {
            echo json_encode(['status' => true, 'data' => $data]);
        } else {
            echo json_encode(['status' => false, 'message' => 'User tidak ditemukan']);
        }
    }

    // POST tambah user
    public function create() {
        $data = json_decode($this->input->raw_input_stream, true);
        $data['password'] = md5($data['password']); // hash password

        if ($this->Muser->insert($data)) {
            echo json_encode(['status' => true, 'message' => 'User berhasil ditambahkan']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal menambahkan user']);
        }
    }

    // PUT update user
    public function update($id) {
        $data = json_decode($this->input->raw_input_stream, true);

        if (!empty($data['password'])) {
            $data['password'] = md5($data['password']);
        }

        if ($this->Muser->updateData($id, $data)) {
            echo json_encode(['status' => true, 'message' => 'User berhasil diperbarui']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal memperbarui user']);
        }
    }

    // DELETE user
    public function delete($id) {
        if ($this->Muser->deleteData($id)) {
            echo json_encode(['status' => true, 'message' => 'User berhasil dihapus']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal menghapus user']);
        }
    }
}
