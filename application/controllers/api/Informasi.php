<?php
defined('BASEPATH') OR exit('No direct script access allowed');

header('Content-Type: application/json');

class Informasi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('Minformasi');
    }

    // GET semua data
    public function index() {
        $data = $this->Minformasi->getAll();
        echo json_encode([
            "status" => true,
            "message" => "Data informasi berhasil diambil",
            "data" => $data
        ]);
    }

    // GET berdasarkan ID
    public function detail($id) {
        $data = $this->Minformasi->getById($id);
        if ($data) {
            echo json_encode([
                "status" => true,
                "message" => "Detail informasi",
                "data" => $data
            ]);
        } else {
            echo json_encode([
                "status" => false,
                "message" => "Data tidak ditemukan"
            ]);
        }
    }
}
