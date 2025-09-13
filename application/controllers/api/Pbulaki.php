<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pbulaki extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mpbulaki');
        header('Content-Type: application/json');
    }

    // GET semua data referensi PB/U Laki
    public function index() {
        $data = $this->Mpbulaki->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET data referensi PB/U Laki by ID
    public function detail($id) {
        $data = $this->Mpbulaki->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data referensi PB/U Laki tidak ditemukan'
            ]);
        }
    }
}
