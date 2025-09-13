<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Vitamin extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mvitamin');
        header('Content-Type: application/json');
    }

    // GET semua jenis vitamin
    public function index() {
        $data = $this->Mvitamin->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET jenis vitamin by ID
    public function detail($id) {
        $data = $this->Mvitamin->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data jenis vitamin tidak ditemukan'
            ]);
        }
    }
}
