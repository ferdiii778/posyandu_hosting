<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bbuperempuan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('modelsapi/Mbbuperempuan');
        header('Content-Type: application/json');
    }

    // GET semua data referensi BB/U Perempuan
    public function index() {
        $data = $this->Mbbuperempuan->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET data referensi BB/U Perempuan by ID
    public function detail($id) {
        $data = $this->Mbbuperempuan->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data referensi BB/U Perempuan tidak ditemukan'
            ]);
        }
    }
}
