<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Jadwal extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mjadwal');
        header('Content-Type: application/json');
    }

    // GET semua jadwal
    public function index() {
        $data = $this->Mjadwal->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET jadwal by ID
    public function detail($id) {
        $data = $this->Mjadwal->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data jadwal pemeriksaan tidak ditemukan'
            ]);
        }
    }
}
