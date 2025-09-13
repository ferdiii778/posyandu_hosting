<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Balita extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mbalita');
        header('Content-Type: application/json');
    }

    // GET semua data balita
    public function index() {
        $data = $this->Mbalita->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET balita by NIB
    public function detail($nib) {
        $data = $this->Mbalita->getById($nib);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data balita tidak ditemukan'
            ]);
        }
    }
}
