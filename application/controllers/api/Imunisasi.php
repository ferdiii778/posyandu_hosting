<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Imunisasi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mimunisasi');
        header('Content-Type: application/json');
    }

    // GET semua jenis imunisasi
    public function index() {
        $data = $this->Mimunisasi->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET jenis imunisasi by ID
    public function detail($id) {
        $data = $this->Mimunisasi->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data jenis imunisasi tidak ditemukan'
            ]);
        }
    }
}
