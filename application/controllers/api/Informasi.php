<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Informasi extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('modelsapi/Minformasi');
        $this->load->helper('url');
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
        header('Access-Control-Allow-Headers: Content-Type');
    }

    // GET semua informasi
    public function index() {
        $data = $this->Minformasi->getAll();

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET informasi by ID
    public function detail($id) {
        $data = $this->Minformasi->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data informasi tidak ditemukan'
            ]);
        }
    }

    // GET informasi by Posyandu ID
    public function by_posyandu($posyandu_id) {
        $data = $this->Minformasi->getByPosyandu($posyandu_id);

        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // CREATE informasi baru
    public function create() {
        // Validasi input
        $judul = $this->input->post('judul');
        $isi = $this->input->post('isi');
        $posyandu_id = $this->input->post('posyandu_id');

        if (empty($judul) || empty($isi) || empty($posyandu_id)) {
            echo json_encode([
                'status' => false,
                'message' => 'Judul, isi, dan posyandu_id wajib diisi'
            ]);
            return;
        }

        // Prepare data
        $data = [
            'judul' => $judul,
            'isi' => $isi,
            'tgl_post' => date('Y-m-d H:i:s'),
            'posyandu_id' => $posyandu_id,
            'foto' => ''
        ];

        // Handle file upload
        if (!empty($_FILES['foto']['name'])) {
            $upload_result = $this->upload_foto();
            if ($upload_result['status']) {
                $data['foto'] = $upload_result['filename'];
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => $upload_result['message']
                ]);
                return;
            }
        }

        // Insert data
        $result = $this->Minformasi->insert($data);

        if ($result) {
            echo json_encode([
                'status' => true,
                'message' => 'Data informasi berhasil ditambahkan',
                'data' => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal menambahkan data informasi'
            ]);
        }
    }

    // UPDATE informasi
    public function update($id) {
        // Cek apakah data ada
        $existing = $this->Minformasi->getById($id);
        if (!$existing) {
            echo json_encode([
                'status' => false,
                'message' => 'Data informasi tidak ditemukan'
            ]);
            return;
        }

        // Prepare data
        $data = [];
        
        if ($this->input->post('judul')) {
            $data['judul'] = $this->input->post('judul');
        }
        
        if ($this->input->post('isi')) {
            $data['isi'] = $this->input->post('isi');
        }
        
        if ($this->input->post('posyandu_id')) {
            $data['posyandu_id'] = $this->input->post('posyandu_id');
        }

        // Handle file upload
        if (!empty($_FILES['foto']['name'])) {
            // Hapus foto lama jika ada
            if (!empty($existing->foto) && file_exists('./uploads/informasi/' . $existing->foto)) {
                unlink('./uploads/informasi/' . $existing->foto);
            }

            $upload_result = $this->upload_foto();
            if ($upload_result['status']) {
                $data['foto'] = $upload_result['filename'];
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => $upload_result['message']
                ]);
                return;
            }
        }

        // Update data
        if (empty($data)) {
            echo json_encode([
                'status' => false,
                'message' => 'Tidak ada data yang diupdate'
            ]);
            return;
        }

        $result = $this->Minformasi->update($id, $data);

        if ($result) {
            echo json_encode([
                'status' => true,
                'message' => 'Data informasi berhasil diupdate',
                'data' => $this->Minformasi->getById($id)
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal mengupdate data informasi'
            ]);
        }
    }

    // DELETE informasi
    public function delete($id) {
        // Cek apakah data ada
        $existing = $this->Minformasi->getById($id);
        if (!$existing) {
            echo json_encode([
                'status' => false,
                'message' => 'Data informasi tidak ditemukan'
            ]);
            return;
        }

        // Hapus foto jika ada
        if (!empty($existing->foto) && file_exists('./uploads/informasi/' . $existing->foto)) {
            unlink('./uploads/informasi/' . $existing->foto);
        }

        // Delete data
        $result = $this->Minformasi->delete($id);

        if ($result) {
            echo json_encode([
                'status' => true,
                'message' => 'Data informasi berhasil dihapus'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal menghapus data informasi'
            ]);
        }
    }

    // Helper function untuk upload foto
    private function upload_foto() {
        $config['upload_path']   = './uploads/informasi/';
        $config['allowed_types'] = 'jpg|jpeg|png|gif';
        $config['max_size']      = 2048; // 2MB
        $config['file_name']     = str_replace(' ', '_', $this->input->post('judul'));

        // Buat folder jika belum ada
        if (!is_dir($config['upload_path'])) {
            mkdir($config['upload_path'], 0777, true);
        }

        $this->load->library('upload', $config);

        if ($this->upload->do_upload('foto')) {
            $upload_data = $this->upload->data();
            return [
                'status' => true,
                'filename' => $upload_data['file_name']
            ];
        } else {
            return [
                'status' => false,
                'message' => $this->upload->display_errors('', '')
            ];
        }
    }
}