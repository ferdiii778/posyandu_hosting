<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Imunisasi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mimunisasi');
        header('Content-Type: application/json');
    }

    // GET semua jenis imunisasi
    public function index() {
        $data = $this->Mimunisasi->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET jenis imunisasi by ID
    public function detail($id) {
        $data = $this->Mimunisasi->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data jenis imunisasi tidak ditemukan'
            ]);
        }
    }

    // POST tambah imunisasi
    public function store() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (isset($input['nama_imunisasi'])) {
            $this->Mimunisasi->insert([
                'nama_imunisasi' => $input['nama_imunisasi']
            ]);

            echo json_encode([
                'status' => true,
                'message' => 'Data imunisasi berhasil ditambahkan'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Nama imunisasi harus diisi'
            ]);
        }
    }

    // PUT update imunisasi
    public function update($id) {
        $input = json_decode(file_get_contents("php://input"), true);

        if (isset($input['nama_imunisasi'])) {
            $updated = $this->Mimunisasi->update($id, [
                'nama_imunisasi' => $input['nama_imunisasi']
            ]);

            if ($updated) {
                echo json_encode([
                    'status' => true,
                    'message' => 'Data imunisasi berhasil diperbarui'
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Gagal memperbarui data imunisasi'
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Nama imunisasi harus diisi'
            ]);
        }
    }

    // DELETE hapus imunisasi
    public function delete($id) {
        $deleted = $this->Mimunisasi->delete($id);

        if ($deleted) {
            echo json_encode([
                'status' => true,
                'message' => 'Data imunisasi berhasil dihapus'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data imunisasi gagal dihapus'
            ]);
        }
    }
}
