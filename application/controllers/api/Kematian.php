<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class Kematian extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('modelsapi/Mkematian');
        header('Content-Type: application/json');
    }

    // GET semua data kematian
    public function index() {
        $data = $this->Mkematian->getAll();
        echo json_encode([
            'status' => true,
            'data'   => $data
        ]);
    }

    // GET data kematian by ID
    public function detail($id) {
        $data = $this->Mkematian->getById($id);

        if ($data) {
            echo json_encode([
                'status' => true,
                'data'   => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data kematian tidak ditemukan'
            ]);
        }
    }

    // POST tambah data kematian
    public function store() {
        $input = json_decode(file_get_contents("php://input"), true);
    
        if (isset($input['nib']) && isset($input['tgl_kematian']) && isset($input['keterangan'])) {
            // Cek dulu apakah NIB valid
            $check = $this->db->get_where('balita', ['nib' => $input['nib']])->row();
            if (!$check) {
                echo json_encode([
                    'status' => false,
                    'message' => 'Balita dengan NIB tersebut tidak ditemukan'
                ]);
                return;
            }
    
            // Simpan data kematian ke tabel kematian
            $insert = $this->Mkematian->insert([
                'nib' => $input['nib'],
                'tgl_kematian' => $input['tgl_kematian'],
                'keterangan' => $input['keterangan']
            ]);
    
            if ($insert) {
                // Update status balita menjadi meninggal
                $this->db->where('nib', $input['nib']);
                $this->db->update('balita', ['is_meninggal' => 1]);
    
                echo json_encode([
                    'status' => true,
                    'message' => 'Data kematian berhasil ditambahkan dan status balita diperbarui'
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Gagal menambahkan data kematian'
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Field nib, tgl_kematian, dan keterangan wajib diisi'
            ]);
        }
    }


    // PUT update data kematian
    public function update($id) {
        $input = json_decode(file_get_contents("php://input"), true);
    
        if (isset($input['nib']) && isset($input['tgl_kematian']) && isset($input['keterangan'])) {
    
            // Ambil data lama kematian sebelum diupdate
            $old = $this->db->get_where('kematian', ['id_kematian' => $id])->row();
    
            $updated = $this->Mkematian->update($id, [
                'nib' => $input['nib'],
                'tgl_kematian' => $input['tgl_kematian'],
                'keterangan' => $input['keterangan']
            ]);
    
            if ($updated) {
                // 1️⃣ Jika balita sebelumnya berbeda, ubah status lamanya jadi hidup kembali
                if ($old && $old->nib != $input['nib']) {
                    $this->db->where('nib', $old->nib);
                    $this->db->update('balita', ['is_meninggal' => 0]);
                }
    
                // 2️⃣ Update balita baru yang dipilih jadi meninggal
                $this->db->where('nib', $input['nib']);
                $this->db->update('balita', ['is_meninggal' => 1]);
    
                echo json_encode([
                    'status' => true,
                    'message' => 'Data kematian berhasil diperbarui dan status balita disinkronkan'
                ]);
            } else {
                echo json_encode([
                    'status' => false,
                    'message' => 'Gagal memperbarui data kematian'
                ]);
            }
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Field nib, tgl_kematian, dan keterangan wajib diisi'
            ]);
        }
    }
    

    // DELETE hapus data kematian
    public function delete($id) {
        // Ambil dulu data kematian
        $data = $this->db->get_where('kematian', ['id_kematian' => $id])->row();
    
        $deleted = $this->Mkematian->delete($id);
    
        if ($deleted) {
            // Jika data kematian ada, ubah status balita kembali jadi hidup
            if ($data && isset($data->nib)) {
                $this->db->where('nib', $data->nib);
                $this->db->update('balita', ['is_meninggal' => 0]);
            }
    
            echo json_encode([
                'status' => true,
                'message' => 'Data kematian berhasil dihapus dan status balita diperbarui'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Data kematian gagal dihapus'
            ]);
        }
    }
    
}
