<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Posyandu extends CI_Controller {
    public function __construct() {
        parent::__construct();
        $this->load->database();
        header('Content-Type: application/json');
    }

    // GET semua posyandu
    public function index() {
        $data = $this->db->get('posyandu')->result_array();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET posyandu by ID
    public function detail($id) {
        $data = $this->db->get_where('posyandu', ['id_posyandu' => $id])->row_array();

        if ($data) {
            echo json_encode(['status' => true, 'data' => $data]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Data posyandu tidak ditemukan']);
        }
    }
}
