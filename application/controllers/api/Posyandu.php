<?php
defined('BASEPATH') or exit('No direct script access allowed');

class posyandu extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('modelsapi/Mposyandu');
        
        // Catatan: Untuk API, sebaiknya tambahkan header CORS agar bisa diakses mobile
        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
    }

    // Mendapatkan semua data Posyandu (Method: GET)
    public function index()
    {
        $preload_posyandu = array(
            'join' => array(
                array('ref_desa', 'ref_desa.desa_id = ref_posyandu.desa_id', 'left'),
                array('ref_kecamatan', 'ref_kecamatan.kecamatan_id = ref_desa.kecamatan_id', 'left'),
            ),
        );
        
        // Menggunakan library mm (Master Model) sesuai kode asli anda
        $data = $this->mm->get('ref_posyandu', $preload_posyandu);

        echo json_encode([
            'status' => 'success',
            'message' => 'Data Posyandu berhasil diambil',
            'data' => $data
        ]);
    }

    // Menampilkan daftar desa untuk dropdown di mobile
    public function get_desa()
    {
        // Mengambil data desa dari tabel ref_desa
        $desa = $this->db->get('ref_desa')->result_array();
        
        echo json_encode([
            'status' => 'success',
            'data' => $desa
        ]);
    }

    // Menambah data Posyandu (Method: POST)
    public function insert_action()
    {
        // Mengambil input JSON atau Post
        $input = json_decode(file_get_contents('php://input'), true);
        if(empty($input)) $input = $this->input->post();

        $data = [
            'posyandu_nama'           => $input['posyandu_nama'],
            'desa_id'                 => $input['desa_id'],
            'posyandu_ketua'          => $input['posyandu_ketua'],
            'posyandu_telp'           => $input['posyandu_telp'],
            'posyandu_admin_apk'      => $input['posyandu_admin_apk'],
            'posyandu_telp_admin_apk' => $input['posyandu_telp_admin_apk'],
            'posyandu_alamat'         => $input['posyandu_alamat'],
        ];

        $insert = $this->Mposyandu->insert($data);
        
        echo json_encode([
            'status' => 'success',
            'message' => 'Berhasil tambah data posyandu.'
        ]);
    }

    // Mengambil satu data spesifik (Method: GET)
    public function detail($id)
    {
        $data = $this->Mposyandu->get_by_id($id);
        if ($data) {
            echo json_encode(['status' => 'success', 'data' => $data]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan'], 404);
        }
    }

    // Mengedit data (Method: POST atau PUT)
    public function edit_action()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if(empty($input)) $input = $this->input->post();

        $posyandu_id = $input['posyandu_id'];
        $data = [
            'posyandu_nama'           => $input['posyandu_nama'],
            'posyandu_telp'           => $input['posyandu_telp'],
            'posyandu_alamat'         => $input['posyandu_alamat'],
            'posyandu_ketua'          => $input['posyandu_ketua'],
            'posyandu_admin_apk'      => $input['posyandu_admin_apk'],
            'posyandu_telp_admin_apk' => $input['posyandu_telp_admin_apk'],
            'desa_id'                 => $input['desa_id'],
        ];

        $this->Mposyandu->update($posyandu_id, $data);
        echo json_encode(['status' => 'success', 'message' => 'Berhasil edit data posyandu.']);
    }

    // Menghapus data (Method: DELETE atau GET)
    public function delete($id)
    {
        $data = $this->Mposyandu->get_by_id($id);
        if ($data) {
            $this->Mposyandu->delete($id);
            echo json_encode(['status' => 'success', 'message' => 'Berhasil hapus data.']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Data tidak ditemukan.']);
        }
    }
}