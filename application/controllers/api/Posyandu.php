<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Posyandu extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->database();

        // ✅ load master model sebagai "mm" (ini yang hilang)
        $this->load->model('modelsapi/Mmaster', 'mm');

        // ✅ alias biar konsisten dipanggil $this->Mposyandu
        $this->load->model('modelsapi/Mposyandu', 'Mposyandu');

        header('Content-Type: application/json');
        header('Access-Control-Allow-Origin: *');
        header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS');
        header('Access-Control-Allow-Headers: Content-Type, Authorization');
    }

    // Mendapatkan semua data Posyandu (GET)
    public function index()
    {
        $preload_posyandu = array(
            'join' => array(
                array('ref_desa', 'ref_desa.desa_id = ref_posyandu.desa_id', 'left'),
                array('ref_kecamatan', 'ref_kecamatan.kecamatan_id = ref_desa.kecamatan_id', 'left'),
            ),
        );

        // ✅ sekarang $this->mm sudah ada
        $data = $this->mm->get('ref_posyandu', $preload_posyandu);

        echo json_encode([
            'status' => true,
            'message' => 'Data Posyandu berhasil diambil',
            'data' => $data
        ]);
    }

    // Dropdown desa (GET)
    public function get_desa()
    {
        $desa = $this->db->get('ref_desa')->result_array();

        echo json_encode([
            'status' => true,
            'data' => $desa
        ]);
    }

    // Tambah posyandu (POST)
    public function insert_action()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) $input = $this->input->post();

        $data = [
            'posyandu_nama'           => $input['posyandu_nama'] ?? null,
            'desa_id'                 => $input['desa_id'] ?? null,
            'posyandu_ketua'          => $input['posyandu_ketua'] ?? null,
            'posyandu_telp'           => $input['posyandu_telp'] ?? null,
            'posyandu_admin_apk'      => $input['posyandu_admin_apk'] ?? null,
            'posyandu_telp_admin_apk' => $input['posyandu_telp_admin_apk'] ?? null,
            'posyandu_alamat'         => $input['posyandu_alamat'] ?? null,
        ];

        $this->Mposyandu->insert($data);

        echo json_encode([
            'status' => true,
            'message' => 'Berhasil tambah data posyandu.'
        ]);
    }

    // Detail posyandu (GET)
    public function detail($id)
    {
        $data = $this->Mposyandu->get_by_id($id);
        if ($data) {
            echo json_encode(['status' => true, 'data' => $data]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan']);
        }
    }

    // Edit posyandu (POST/PUT)
    public function edit_action()
    {
        $input = json_decode(file_get_contents('php://input'), true);
        if (empty($input)) $input = $this->input->post();

        $posyandu_id = $input['posyandu_id'] ?? null;
        if (!$posyandu_id) {
            echo json_encode(['status' => false, 'message' => 'posyandu_id wajib diisi']);
            return;
        }

        $data = [
            'posyandu_nama'           => $input['posyandu_nama'] ?? null,
            'posyandu_telp'           => $input['posyandu_telp'] ?? null,
            'posyandu_alamat'         => $input['posyandu_alamat'] ?? null,
            'posyandu_ketua'          => $input['posyandu_ketua'] ?? null,
            'posyandu_admin_apk'      => $input['posyandu_admin_apk'] ?? null,
            'posyandu_telp_admin_apk' => $input['posyandu_telp_admin_apk'] ?? null,
            'desa_id'                 => $input['desa_id'] ?? null,
        ];

        $this->Mposyandu->update($posyandu_id, $data);

        echo json_encode(['status' => true, 'message' => 'Berhasil edit data posyandu.']);
    }

    // Hapus posyandu (DELETE/GET)
    public function delete($id)
    {
        $data = $this->Mposyandu->get_by_id($id);
        if ($data) {
            $this->Mposyandu->delete($id);
            echo json_encode(['status' => true, 'message' => 'Berhasil hapus data.']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan.']);
        }
    }
}
