<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Standarbbpbperempuan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('modelsapi/Mstandarbbpbperempuan');
        header('Content-Type: application/json');
    }

    // GET semua data standar BB/PB Perempuan 0-24 bulan
    public function index() {
        $data = $this->Mstandarbbpbperempuan->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET data standar BB/PB Perempuan by ID
    public function detail($id) {
        $data = $this->Mstandarbbpbperempuan->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data standar BB/PB Perempuan 0-24 bulan tidak ditemukan'
            ]);
        }
    }
}
