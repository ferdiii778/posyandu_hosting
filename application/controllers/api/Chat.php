<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Chat extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Mchat');
        header('Content-Type: application/json');
    }

    // GET semua chat
    public function index() {
        $data = $this->Mchat->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET chat by ID
    public function detail($id) {
        $data = $this->Mchat->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data tidak ditemukan'
            ]);
        }
    }
}
