<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Standarimtu024laki extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('modelsapi/Mstandarimtu024laki');
        header('Content-Type: application/json');
    }

    // GET semua data standar IMT/U Laki 0-24 bulan
    public function index() {
        $data = $this->Mstandarimtu024laki->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET data standar IMT/U Laki 0-24 bulan by ID
    public function detail($id) {
        $data = $this->Mstandarimtu024laki->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data standar IMT/U Laki 0-24 bulan tidak ditemukan'
            ]);
        }
    }
}
