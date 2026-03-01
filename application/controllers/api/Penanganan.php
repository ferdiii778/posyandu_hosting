<?php
defined('BASEPATH') or exit('No direct script access allowed');

class penanganan extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();

        // CORS
        header('Access-Control-Allow-Origin: *');
        header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
        header("Access-Control-Allow-Headers: Content-Type, Authorization, X-Requested-With");

        // Preflight
        if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
            exit;
        }

        $this->load->database();

        // ✅ Load model pakai alias biar pasti ada $this->Mpenanganan
        $this->load->model('modelsapi/Mpenanganan', 'Mpenanganan');
        $this->load->model('modelsapi/Mbalita', 'Mbalita');
    }

    /**
     * Helper response JSON
     */
    private function _response($data, $status = 200)
    {
        if (ob_get_length()) ob_clean();

        $this->output
            ->set_status_header($status)
            ->set_content_type('application/json', 'utf-8')
            ->set_output(json_encode($data))
            ->_display();
        exit;
    }

    /**
     * 1) GET DATA DROPDOWN
     */
    public function get_form_resources()
    {
        $role = $this->input->get('role');
        $timsus_id = $this->input->get('timsus_id');

        $this->db->select('pemeriksaan.kode_pemeriksaan, balita.nama_balita, balita.posyandu_id');
        $this->db->from('pemeriksaan');
        $this->db->join('balita', 'balita.nib = pemeriksaan.nib', 'left');
        $this->db->where('is_meninggal', '0');
        $this->db->where('status_pemeriksaan', 'Stunting');

        if ($role == 'timsus') {
            $this->db->join('detail_timsus', 'detail_timsus.posyandu_id = balita.posyandu_id');
            $this->db->where('detail_timsus.timsus_id', $timsus_id);
        }

        $this->db->group_by('pemeriksaan.nib');
        $this->db->order_by('kode_pemeriksaan', 'desc');

        $data_balita = $this->db->get()->result_array();

        $data_timsus = $this->db->get('timsus')->result_array();

        $this->_response([
            'status' => true,
            'data' => [
                'balita_stunting' => !empty($data_balita) ? $data_balita : [],
                'timsus' => !empty($data_timsus) ? $data_timsus : []
            ]
        ]);
    }

    /**
     * 2) READ
     * - /api/penanganan?id=1 -> detail
     * - /api/penanganan?role=timsus&timsus_id=2 -> list terfilter
     */
    public function index()
    {
        $id = $this->input->get('id');
        $role = $this->input->get('role');
        $timsus_id = $this->input->get('timsus_id');

        if ($id) {
            $data = $this->Mpenanganan->get_by_id($id);
            $this->_response(['status' => true, 'data' => $data]);
            return;
        }

        // ✅ LIST dengan JOIN (karena model kamu belum support preload join)
        $this->db->from('treatment');
        $this->db->join('pemeriksaan', 'pemeriksaan.kode_pemeriksaan = treatment.kode_pemeriksaan', 'left');
        $this->db->join('balita', 'balita.nib = pemeriksaan.nib', 'left');
        $this->db->join('timsus', 'timsus.timsus_id = treatment.timsus_id', 'left');

        // pilih kolom yang kamu butuhkan (bisa tambah/kurang)
        $this->db->select('
            treatment.*,
            pemeriksaan.kode_pemeriksaan,
            balita.nama_balita,
            balita.posyandu_id,
            timsus.timsus_nama
        ');

        if ($role == 'timsus' && !empty($timsus_id)) {
            $this->db->where('treatment.timsus_id', $timsus_id);
        }

        $this->db->order_by('treatment.treatment_id', 'desc');

        $list = $this->db->get()->result_array();
        $this->_response(['status' => true, 'data' => $list]);
    }

    /**
     * 3) CREATE
     */
    public function store()
    {
        $json = file_get_contents('php://input');
        $raw_input = json_decode($json, true);
        $input = (json_last_error() === JSON_ERROR_NONE) ? $raw_input : $this->input->post();

        if (empty($input['kode_pemeriksaan'])) {
            $this->_response(['status' => false, 'message' => 'Pilih Balita terlebih dahulu'], 400);
        }

        $data = $this->_map_input($input);
        $data['treatment_status'] = 'Sudah';

        $insert_id = $this->Mpenanganan->insert($data);

        if ($insert_id) {
            $this->_response(['status' => true, 'message' => 'Data Treatment berhasil disimpan', 'insert_id' => $insert_id]);
        } else {
            $this->_response(['status' => false, 'message' => 'Gagal menyimpan data ke database'], 500);
        }
    }

    /**
     * 4) UPDATE
     */
    public function update($id = null)
    {
        if (!$id) $this->_response(['status' => false, 'message' => 'ID tidak valid'], 400);

        $json = file_get_contents('php://input');
        $raw_input = json_decode($json, true);
        $input = (json_last_error() === JSON_ERROR_NONE) ? $raw_input : $this->input->post();

        $update_data = $this->_map_input($input);
        $update_data['treatment_status'] = 'Sudah';

        // ✅ pakai method yang ada di model
        $update = $this->Mpenanganan->update($id, $update_data);

        if ($update) {
            $this->_response(['status' => true, 'message' => 'Data berhasil diperbarui']);
        } else {
            $this->_response(['status' => false, 'message' => 'Tidak ada perubahan atau gagal'], 500);
        }
    }

    /**
     * 5) DELETE
     */
    public function delete($id = null)
    {
        if (!$id) $this->_response(['status' => false, 'message' => 'ID tidak ditemukan'], 400);

        $check = $this->Mpenanganan->get_by_id($id);
        if ($check) {
            $this->Mpenanganan->delete($id);
            $this->_response(['status' => true, 'message' => 'Data berhasil dihapus']);
        } else {
            $this->_response(['status' => false, 'message' => 'Data tidak ditemukan'], 404);
        }
    }

    /**
     * Mapping Input
     */
    private function _map_input($input)
    {
        return [
            'treatment_tgl'             => isset($input['treatment_tgl']) ? date('Y-m-d', strtotime($input['treatment_tgl'])) : date('Y-m-d'),
            'kode_pemeriksaan'          => isset($input['kode_pemeriksaan']) ? $input['kode_pemeriksaan'] : '',
            'timsus_id'                 => isset($input['timsus_id']) ? (int)$input['timsus_id'] : 0,
            'treatment_status'          => 'Sudah',
            'treatment_detail'          => isset($input['treatment_detail']) ? $input['treatment_detail'] : '',
            'treatment_keterangan'      => isset($input['treatment_keterangan']) ? $input['treatment_keterangan'] : '',

            'treatment_TTD'             => (isset($input['treatment_TTD']) && $input['treatment_TTD'] == true) ? 1 : 0,
            'treatment_ANC'             => (isset($input['treatment_ANC']) && $input['treatment_ANC'] == true) ? 1 : 0,
            'treatment_PMT'             => (isset($input['treatment_PMT']) && $input['treatment_PMT'] == true) ? 1 : 0,
            'treatment_imunisasi'       => (isset($input['treatment_imunisasi']) && $input['treatment_imunisasi'] == true) ? 1 : 0,
            'treatment_suplemen'        => (isset($input['treatment_suplemen']) && $input['treatment_suplemen'] == true) ? 1 : 0,
            'treatment_edukasi_mpasi'   => (isset($input['treatment_edukasi_mpasi']) && $input['treatment_edukasi_mpasi'] == true) ? 1 : 0,
            'treatment_balita_stunting' => (isset($input['treatment_balita_stunting']) && $input['treatment_balita_stunting'] == true) ? 1 : 0,
            'treatment_sanitasi'        => (isset($input['treatment_sanitasi']) && $input['treatment_sanitasi'] == true) ? 1 : 0,
            'treatment_pola_asuh'       => (isset($input['treatment_pola_asuh']) && $input['treatment_pola_asuh'] == true) ? 1 : 0,
            'treatment_kb'              => (isset($input['treatment_kb']) && $input['treatment_kb'] == true) ? 1 : 0,
        ];
    }
}
