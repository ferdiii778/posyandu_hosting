<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Tahun extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('modelsapi/Mtahun');

        // Header API
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');

        // Preflight
        if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
            http_response_code(200);
            exit;
        }
    }

    /**
     * GET /api/Tahun
     * Sama dengan: ControllerTahun::index()
     */
    public function index()
    {
        $data = $this->Mtahun->get_all();

        echo json_encode([
            'status' => true,
            'message' => 'Data tahun berhasil diambil',
            'data' => $data
        ]);
    }

    /**
     * POST /api/Tahun/create
     * Sama dengan: insert_action()
     */
    public function create()
    {
        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['tahun_nama'])) {
            http_response_code(400);
            echo json_encode([
                'status' => false,
                'message' => 'Tahun tidak boleh kosong'
            ]);
            return;
        }

        $this->Mtahun->insert([
            'tahun_nama' => $input['tahun_nama']
        ]);

        http_response_code(201);
        echo json_encode([
            'status' => true,
            'message' => 'Berhasil tambah data tahun'
        ]);
    }

    /**
     * PUT /api/Tahun/update/{id}
     * Sama dengan: edit_action()
     */
    public function update($id)
    {
        $cek = $this->Mtahun->get_by_id($id);
        if (!$cek) {
            http_response_code(404);
            echo json_encode([
                'status' => false,
                'message' => 'Data tahun tidak ditemukan'
            ]);
            return;
        }

        $input = json_decode(file_get_contents('php://input'), true);

        if (empty($input['tahun_nama'])) {
            http_response_code(400);
            echo json_encode([
                'status' => false,
                'message' => 'Tahun tidak boleh kosong'
            ]);
            return;
        }

        $this->Mtahun->update($id, [
            'tahun_nama' => $input['tahun_nama']
        ]);

        echo json_encode([
            'status' => true,
            'message' => 'Berhasil edit data tahun'
        ]);
    }

    /**
     * DELETE /api/Tahun/delete/{id}
     * Sama dengan: delete()
     */
    public function delete($id)
    {
        $cek = $this->Mtahun->get_by_id($id);

        if (!$cek) {
            http_response_code(404);
            echo json_encode([
                'status' => false,
                'message' => 'Data tahun tidak ditemukan'
            ]);
            return;
        }

        $this->Mtahun->delete($id);

        echo json_encode([
            'status' => true,
            'message' => 'Berhasil hapus data tahun'
        ]);
    }
}
