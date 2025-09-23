<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jadwal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mjadwal');
        header('Content-Type: application/json');
    }

    // GET semua jadwal
    public function index() {
        $data = $this->Mjadwal->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET jadwal by ID
    public function detail($id) {
        $data = $this->Mjadwal->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data jadwal pemeriksaan tidak ditemukan'
            ]);
        }
    }

    // CREATE jadwal baru
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
            'tgl_jadwal' => $input['tgl_jadwal'],
            'jam_mulai'  => $input['jam_mulai'],
            'jam_selese' => $input['jam_selese']
        ];

        $insert = $this->Mjadwal->insert($data);

        if ($insert) {
            echo json_encode([
                'status' => true,
                'message' => 'Data jadwal berhasil ditambahkan'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal menambahkan data jadwal'
            ]);
        }
    }

    // UPDATE jadwal
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
            'tgl_jadwal' => $input['tgl_jadwal'],
            'jam_mulai'  => $input['jam_mulai'],
            'jam_selese' => $input['jam_selese']
        ];

        $update = $this->Mjadwal->update($id, $data);

        if ($update) {
            echo json_encode([
                'status' => true,
                'message' => 'Data jadwal berhasil diperbarui'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal memperbarui data jadwal'
            ]);
        }
    }

    // DELETE jadwal
    public function delete($id) {
        $delete = $this->Mjadwal->delete($id);

        if ($delete) {
            echo json_encode([
                'status' => true,
                'message' => 'Data jadwal berhasil dihapus'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal menghapus data jadwal'
            ]);
        }
    }
}
