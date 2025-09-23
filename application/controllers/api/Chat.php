<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mchat');
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
            'isi'           => $input['isi']
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

    // UPDATE chat
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

    // DELETE chat
    public function delete($id) {
        $delete = $this->Mchat->delete($id);

        if ($delete) {
            echo json_encode([
                'status' => true,
                'message' => 'Chat berhasil dihapus'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal menghapus chat'
            ]);
        }
    }

    // Tambahan: GET chat berdasarkan id_konsultasi
    public function byKonsultasi($id_konsultasi) {
        $data = $this->Mchat->getByKonsultasi($id_konsultasi);

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }
}
