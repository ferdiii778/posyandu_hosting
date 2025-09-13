<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Ortubayi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mortubayi');
        header('Content-Type: application/json');
    }

    // GET semua data ortu_bayi
    public function index() {
        $data = $this->Mortubayi->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET data ortu_bayi by ID
    public function detail($id) {
        $data = $this->Mortubayi->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data ortu_bayi tidak ditemukan'
            ]);
        }
    }
}
