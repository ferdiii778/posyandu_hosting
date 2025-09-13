<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kematian extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mkematian');
        header('Content-Type: application/json');
    }

    // GET semua data kematian
    public function index() {
        $data = $this->Mkematian->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET data kematian by ID
    public function detail($id) {
        $data = $this->Mkematian->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data kematian tidak ditemukan'
            ]);
        }
    }
}
