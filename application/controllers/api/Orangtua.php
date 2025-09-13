<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Orangtua extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Morangtua');
        header('Content-Type: application/json');
    }

    // GET semua data orang tua
    public function index() {
        $data = $this->Morangtua->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET data orang tua by ID
    public function detail($id) {
        $data = $this->Morangtua->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data orang tua tidak ditemukan'
            ]);
        }
    }
}
