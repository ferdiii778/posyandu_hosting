<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vitamin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mvitamin');
        header('Content-Type: application/json');
    }

    // GET semua jenis vitamin
    public function index() {
        $data = $this->Mvitamin->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET jenis vitamin by ID
    public function detail($id) {
        $data = $this->Mvitamin->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data jenis vitamin tidak ditemukan'
            ]);
        }
    }

    // POST tambah vitamin
    public function store() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (isset($input['nama_vitamin'])) {
            $this->Mvitamin->insert([
                'nama_vitamin' => $input['nama_vitamin']
            ]);

            echo json_encode([
                'status' => true,
                'message' => 'Data vitamin berhasil ditambahkan'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Nama vitamin harus diisi'
            ]);
        }
    }

    // PUT update vitamin
    public function update($id) {
        $input = json_decode(file_get_contents("php://input"), true);

        if (isset($input['nama_vitamin'])) {
            $updated = $this->Mvitamin->update($id, [
                'nama_vitamin' => $input['nama_vitamin']
            ]);

            if ($updated) {
                echo json_encode([
                    'status' => true,
                    'message' => 'Data vitamin berhasil diperbarui'
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Gagal memperbarui data vitamin'
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Nama vitamin harus diisi'
            ]);
        }
    }

    // DELETE hapus vitamin
    public function delete($id) {
        $deleted = $this->Mvitamin->delete($id);

        if ($deleted) {
            echo json_encode([
                'status' => true,
                'message' => 'Data vitamin berhasil dihapus'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data vitamin gagal dihapus'
            ]);
        }
    }
}
