<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kematian extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mkematian');
        header('Content-Type: application/json');
    }

    // GET semua data kematian
    public function index() {
        $data = $this->Mkematian->getAll();
        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET data kematian by ID
    public function detail($id) {
        $data = $this->Mkematian->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data kematian tidak ditemukan'
            ]);
        }
    }

    // POST tambah data kematian
    public function store() {
        $input = json_decode(file_get_contents("php://input"), true);

        if (isset($input['nib']) && isset($input['tgl_kematian']) && isset($input['keterangan'])) {
            $this->Mkematian->insert([
                'nib' => $input['nib'],
                'tgl_kematian' => $input['tgl_kematian'],
                'keterangan' => $input['keterangan']
            ]);

            echo json_encode([
                'status' => true,
                'message' => 'Data kematian berhasil ditambahkan'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Field nib, tgl_kematian, dan keterangan wajib diisi'
            ]);
        }
    }

    // PUT update data kematian
    public function update($id) {
        $input = json_decode(file_get_contents("php://input"), true);

        if (isset($input['nib']) && isset($input['tgl_kematian']) && isset($input['keterangan'])) {
            $updated = $this->Mkematian->update($id, [
                'nib' => $input['nib'],
                'tgl_kematian' => $input['tgl_kematian'],
                'keterangan' => $input['keterangan']
            ]);

            if ($updated) {
                echo json_encode([
                    'status' => true,
                    'message' => 'Data kematian berhasil diperbarui'
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Gagal memperbarui data kematian'
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Field nib, tgl_kematian, dan keterangan wajib diisi'
            ]);
        }
    }

    // DELETE hapus data kematian
    public function delete($id) {
        $deleted = $this->Mkematian->delete($id);

        if ($deleted) {
            echo json_encode([
                'status' => true,
                'message' => 'Data kematian berhasil dihapus'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data kematian gagal dihapus'
            ]);
        }
    }
}
