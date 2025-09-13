<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Bbulaki extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mbbulaki');
        header('Content-Type: application/json');
    }

    // GET semua data referensi BB/U Laki
    public function index() {
        $data = $this->Mbbulaki->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET data referensi BB/U Laki by ID
    public function detail($id) {
        $data = $this->Mbbulaki->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data referensi BB/U Laki tidak ditemukan'
            ]);
        }
    }
}
