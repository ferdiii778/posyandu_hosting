<?php
defined('BASEPATH') or exit('No direct script access allowed');

class layanan extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->model('modelsapi/Mlayanan');
        $this->load->library('upload');
        $this->load->helper(array('url', 'file'));

        // Header wajib agar API bisa diakses oleh Mobile (CORS)
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }

    // --- [READ] Menampilkan semua data ---
    public function index()
    {
        $list = $this->Mlayanan->get_all();
        echo json_encode([
            "status"  => "success",
            "message" => "Data berhasil dimuat",
            "data"    => $list
        ]);
    }

    // --- [CREATE] Menambah data baru ---
    public function create()
    {
        $judul     = $this->input->post('layanan_judul');
        $deskripsi = $this->input->post('layanan_deskripsi');

        $config['upload_path']   = './images/layanan';
        $config['allowed_types'] = 'jpg|png|jpeg';
        $config['file_name']     = 'img_' . time();
        $this->upload->initialize($config);

        if ($this->upload->do_upload('layanan_file')) {
            $fileData = $this->upload->data();
            $data = [
                'layanan_judul'     => $judul,
                'layanan_deskripsi' => $deskripsi,
                'layanan_file'      => $fileData['file_name']
            ];
            $this->Mlayanan->insert($data);
            echo json_encode(["status" => "success", "message" => "Data berhasil disimpan"]);
        } else {
            echo json_encode(["status" => "error", "message" => $this->upload->display_errors()]);
        }
    }

    // --- [UPDATE] Mengubah data ---
    public function update($id)
    {
        // Pastikan ID ada di database
        $oldData = $this->Mlayanan->get_by_id($id);
        if (!$oldData) {
            echo json_encode(["status" => "error", "message" => "Data tidak ditemukan"]);
            return;
        }

        $judul     = $this->input->post('layanan_judul');
        $deskripsi = $this->input->post('layanan_deskripsi');

        $data = [
            'layanan_judul'     => $judul,
            'layanan_deskripsi' => $deskripsi
        ];

        // Cek jika user mengupload file baru
        if (!empty($_FILES['layanan_file']['name'])) {
            $config['upload_path']   = './images/layanan';
            $config['allowed_types'] = 'jpg|png|jpeg';
            $config['file_name']     = 'img_' . time();
            $this->upload->initialize($config);

            if ($this->upload->do_upload('layanan_file')) {
                // Hapus file lama jika ada
                if ($oldData->layanan_file) {
                    unlink('./images/layanan/' . $oldData->layanan_file);
                }
                $fileData = $this->upload->data();
                $data['layanan_file'] = $fileData['file_name'];
            }
        }

        $this->Mlayanan->update($id, $data);
        echo json_encode(["status" => "success", "message" => "Data berhasil diupdate"]);
    }

    // --- [DELETE] Menghapus data ---
    public function delete($id)
    {
        $oldData = $this->Mlayanan->get_by_id($id);
        if ($oldData) {
            // Hapus file fisik di folder
            if ($oldData->layanan_file) {
                unlink('./images/layanan/' . $oldData->layanan_file);
            }
            $this->Mlayanan->delete($id);
            echo json_encode(["status" => "success", "message" => "Data berhasil dihapus"]);
        } else {
            echo json_encode(["status" => "error", "message" => "Data tidak ditemukan"]);
        }
    }
}