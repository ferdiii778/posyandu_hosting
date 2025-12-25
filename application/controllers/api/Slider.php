<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class slider extends CI_Controller {

    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('modelsapi/Mslider');
        $this->load->library('upload');
        header("Content-Type: application/json");
    }

    // GET /api/slider
    public function index()
    {
        $slider = $this->Mslider->get_all();

        echo json_encode([
            'status' => true,
            'data' => $slider
        ]);
    }

    // POST /api/slider
    public function store()
    {
        $judul = $this->input->post('slider_judul');

        $config['upload_path']   = './images/slider/';
        $config['allowed_types'] = 'jpg|jpeg|png';
        $config['file_name']     = time().'_'.$judul;
        $this->upload->initialize($config);

        if (!$this->upload->do_upload('slider_foto')) {
            echo json_encode([
                'status' => false,
                'message' => strip_tags($this->upload->display_errors())
            ]);
            return;
        }

        $foto = $this->upload->data('file_name');

        $this->Mslider->insert([
            'slider_judul'    => $judul,
            'slider_tgl_post' => date('Y-m-d H:i:s'),
            'slider_foto'     => $foto
        ]);

        echo json_encode([
            'status' => true,
            'message' => 'Slider berhasil ditambahkan'
        ]);
    }

    // POST /api/slider/{id}
    public function update($id)
    {
        $data = [
            'slider_judul' => $this->input->post('slider_judul'),
        ];

        if (!empty($_FILES['slider_foto']['name'])) {
            $config['upload_path']   = './images/slider/';
            $config['allowed_types'] = 'jpg|jpeg|png';
            $config['file_name']     = time();
            $this->upload->initialize($config);

            if ($this->upload->do_upload('slider_foto')) {
                $data['slider_foto'] = $this->upload->data('file_name');
            }
        }

        $this->Mslider->update($id, $data);

        echo json_encode([
            'status' => true,
            'message' => 'Slider berhasil diupdate'
        ]);
    }

    // DELETE /api/slider/{id}
    public function delete($id)
    {
        $this->Mslider->delete($id);
    
        echo json_encode([
            'status' => true
        ]);
    }

}
