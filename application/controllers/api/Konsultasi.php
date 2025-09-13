<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Konsultasi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mkonsultasi');
        header('Content-Type: application/json');
    }

    // GET semua data konsultasi
    public function index() {
        $data = $this->Mkonsultasi->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET data konsultasi by ID
    public function detail($id) {
        $data = $this->Mkonsultasi->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data konsultasi tidak ditemukan'
            ]);
        }
    }
}
