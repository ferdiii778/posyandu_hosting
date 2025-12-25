<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Standarimtu518laki extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('modelsapi/Mstandarimtu518laki');
        header('Content-Type: application/json');
    }

    // GET semua data standar IMT/U Laki 5-18 tahun
    public function index() {
        $data = $this->Mstandarimtu518laki->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET data standar IMT/U Laki 5-18 tahun by ID
    public function detail($id) {
        $data = $this->Mstandarimtu518laki->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data standar IMT/U Laki 5-18 tahun tidak ditemukan'
            ]);
        }
    }
}
