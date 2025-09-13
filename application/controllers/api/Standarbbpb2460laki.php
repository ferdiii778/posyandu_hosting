<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Standarbbpb2460laki extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mstandarbbpb2460laki');
        header('Content-Type: application/json');
    }

    // GET semua data standar BB/PB Laki 24-60 bulan
    public function index() {
        $data = $this->Mstandarbbpb2460laki->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET data standar BB/PB Laki 24-60 bulan by ID
    public function detail($id) {
        $data = $this->Mstandarbbpb2460laki->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data standar BB/PB Laki 24-60 bulan tidak ditemukan'
            ]);
        }
    }
}
