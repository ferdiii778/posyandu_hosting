<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pbuperempuan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mpbuperempuan');
        header('Content-Type: application/json');
    }

    // GET semua data referensi PB/U Perempuan
    public function index() {
        $data = $this->Mpbuperempuan->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET data referensi PB/U Perempuan by ID
    public function detail($id) {
        $data = $this->Mpbuperempuan->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data referensi PB/U Perempuan tidak ditemukan'
            ]);
        }
    }
}
