<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Pemeriksaan extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mpemeriksaan');
        header('Content-Type: application/json');
    }

    // GET semua data pemeriksaan
    public function index() {
        $data = $this->Mpemeriksaan->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET data pemeriksaan by kode
    public function detail($kode) {
        $data = $this->Mpemeriksaan->getById($kode);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data pemeriksaan tidak ditemukan'
            ]);
        }
    }
}
