<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('modelsapi/Mchat');
        header('Content-Type: application/json');
    }

    // GET semua chat
    public function index() {
        $data = $this->Mchat->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET chat by ID
    public function detail($id) {
        $data = $this->Mchat->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data chat tidak ditemukan'
            ]);
        }
    }

    // CREATE chat baru
    public function create() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input) {
            echo json_encode([
                'status' => false,
                'message' => 'Input tidak boleh kosong'
            ]);
            return;
        }

        $data = [
            'id_konsultasi' => $input['id_konsultasi'],
            'dari'          => $input['dari'],
            'untuk'         => $input['untuk'],
            'isi'           => $input['isi'],
            'is_deleted'    => 0,
            'is_edited'     => 0,
        ];

        $insert = $this->Mchat->insert($data);
        
        if ($insert) {
            echo json_encode([
                'status' => true,
                'message' => 'Chat berhasil ditambahkan'
            ]);
        } else {
            // 🔴 Debug error dari database
            $error = $this->db->error();
            echo json_encode([
                'status' => false,
                'message' => 'Gagal menambahkan chat',
                'db_error' => $error
            ]);
        }
    }

    // UPDATE chat (hanya isi, tidak tandai edited)
    public function update($id) {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input) {
            echo json_encode([
                'status' => false,
                'message' => 'Input tidak boleh kosong'
            ]);
            return;
        }

        $data = [
            'isi' => $input['isi']
        ];

        $update = $this->Mchat->update($id, $data);

        if ($update) {
            echo json_encode([
                'status' => true,
                'message' => 'Chat berhasil diperbarui'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal memperbarui chat'
            ]);
        }
    }

    // Soft Delete chat (tandai dihapus)
    public function softDelete($id) {
        $delete = $this->Mchat->softDelete($id);

        if ($delete) {
            echo json_encode([
                'status' => true,
                'message' => 'Pesan berhasil dihapus'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal menghapus pesan'
            ]);
        }
    }

    // Edit chat (update isi + tandai edited)
    public function edit($id) {
        $input = json_decode(file_get_contents("php://input"), true);

        if (!$input || !isset($input['isi'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Input isi tidak boleh kosong'
            ]);
            return;
        }

        $update = $this->Mchat->updateMessage($id, $input['isi']);

        if ($update) {
            echo json_encode([
                'status' => true,
                'message' => 'Pesan berhasil diedit'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal mengedit pesan'
            ]);
        }
    }

    // DELETE chat (hard delete, jarang dipakai kalau sudah ada soft delete)
    public function delete($id) {
        $delete = $this->Mchat->delete($id);

        if ($delete) {
            echo json_encode([
                'status' => true,
                'message' => 'Chat berhasil dihapus permanen'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal menghapus chat'
            ]);
        }
    }

    // GET chat berdasarkan id_konsultasi
    public function byKonsultasi($id_konsultasi) {
        $data = $this->Mchat->getByKonsultasi($id_konsultasi);

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }
}
