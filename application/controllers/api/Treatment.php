<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Treatment extends CI_Controller
{
    function __construct()
    {
        parent::__construct();
        $this->load->database();
        $this->load->model('TreatmentModel');
        $this->load->library('form_validation');
        header('Content-Type: application/json');
    }
    
    // ==========================================================
    //  HELPER FUNCTIONS
    // ==========================================================
    
    private function val($arr, $key, $default = '')
    {
        return isset($arr[$key]) ? $arr[$key] : $default;
    }
    
    private function parse_date($str, $default = '0000-00-00')
    {
        if (!$str) return $default;
        $t = strtotime($str);
        return $t ? date('Y-m-d', $t) : $default;
    }
    
    private function res($status, $msg, $data = null, $code = 200)
    {
        http_response_code($code);
        echo json_encode([
            'status' => $status,
            'message' => $msg,
            'data' => $data
        ]);
        exit;
    }
    
    private function auth() {
        $token = $this->input->get_request_header('X-Token');
        $expired = $this->input->get_request_header('X-Expired');
        $userId = $this->input->get_request_header('X-User-Id');
        
        if (!$token || !$userId) {
            $this->res(false, 'Token dan User ID diperlukan', null, 401);
        }
        
        if ($expired && time() > $expired) {
            $this->res(false, 'Token telah kedaluwarsa', null, 401);
        }
        
        // Validasi user exists
        $user = $this->db->get_where('user', ['id_user' => $userId])->row();
        if (!$user) {
            $this->res(false, 'User tidak ditemukan', null, 401);
        }
        
        return $user;
    }
    
    private function getUserFromToken() {
        $userId = $this->input->get_request_header('X-User-Id');
        if (!$userId) return null;
        
        $user = $this->db->get_where('user', ['id_user' => $userId])->row_array();
        return $user;
    }
    
    // ==========================================================
    //  ENDPOINTS - PERBAIKAN SQL YANG SANGAT SEDERHANA
    // ==========================================================
    

    public function index()
    {
        try {
            // Auth check
            $user = $this->getUserFromToken();
            if (!$user) {
                return $this->res(false, 'User tidak ditemukan', null, 401);
            }
            
            $id_user = $user['id_user'];
            $role = $user['role'];
            
            // PERBAIKAN: Query yang sangat sederhana dan pasti berhasil
            $sql = "
                SELECT 
                    t.*,
                    p.*,
                    b.*,
                    ts.*
                FROM treatment t
                LEFT JOIN pemeriksaan p ON p.kode_pemeriksaan = t.kode_pemeriksaan
                LEFT JOIN balita b ON b.nib = p.nib
                LEFT JOIN timsus ts ON ts.timsus_id = t.timsus_id
            ";
            
            $params = [];
            
            // Filter berdasarkan role
            if ($role == 'timsus') {
                $user_timsus = $this->db->get_where('user', ['id_user' => $id_user])->row();
                if ($user_timsus && isset($user_timsus->timsus_id) && $user_timsus->timsus_id) {
                    $sql .= " WHERE t.timsus_id = ?";
                    $params[] = $user_timsus->timsus_id;
                } else {
                    return $this->res(true, 'Data treatment', []);
                }
            }
            
            $sql .= " ORDER BY t.treatment_tgl DESC";
            
            // Jalankan query manual untuk menghindari error query builder
            $query = $this->db->query($sql, $params);
            $data = $query->result_array();
            
            // Debug: Tampilkan query yang dijalankan
            error_log("SQL Query: " . $sql);
            error_log("Params: " . print_r($params, true));
            error_log("Data count: " . count($data));
            
            // Format data agar lebih terstruktur
            $formattedData = [];
            foreach ($data as $item) {
                // Cek field nama balita yang ada
                $balita_nama = '';
                if (isset($item['nama_balita']) && !empty($item['nama_balita'])) {
                    $balita_nama = $item['nama_balita'];
                } elseif (isset($item['balita_nama']) && !empty($item['balita_nama'])) {
                    $balita_nama = $item['balita_nama'];
                }
                
                $formattedData[] = [
                    'treatment_id' => isset($item['treatment_id']) ? $item['treatment_id'] : null,
                    'treatment_tgl' => isset($item['treatment_tgl']) ? $item['treatment_tgl'] : null,
                    'treatment_keterangan' => isset($item['treatment_keterangan']) ? $item['treatment_keterangan'] : null,
                    'treatment_status' => isset($item['treatment_status']) ? $item['treatment_status'] : null,
                    'treatment_TTD' => isset($item['treatment_TTD']) ? $item['treatment_TTD'] : null,
                    'treatment_ANC' => isset($item['treatment_ANC']) ? $item['treatment_ANC'] : null,
                    'treatment_PMT' => isset($item['treatment_PMT']) ? $item['treatment_PMT'] : null,
                    'treatment_imunisasi' => isset($item['treatment_imunisasi']) ? $item['treatment_imunisasi'] : null,
                    'treatment_suplemen' => isset($item['treatment_suplemen']) ? $item['treatment_suplemen'] : null,
                    'treatment_edukasi_mpasi' => isset($item['treatment_edukasi_mpasi']) ? $item['treatment_edukasi_mpasi'] : null,
                    'treatment_balita_stunting' => isset($item['treatment_balita_stunting']) ? $item['treatment_balita_stunting'] : null,
                    'treatment_sanitasi' => isset($item['treatment_sanitasi']) ? $item['treatment_sanitasi'] : null,
                    'treatment_pola_asuh' => isset($item['treatment_pola_asuh']) ? $item['treatment_pola_asuh'] : null,
                    'treatment_kb' => isset($item['treatment_kb']) ? $item['treatment_kb'] : null,
                    'kode_pemeriksaan' => isset($item['kode_pemeriksaan']) ? $item['kode_pemeriksaan'] : null,
                    'timsus_id' => isset($item['timsus_id']) ? $item['timsus_id'] : null,
                    'balita' => [
                        'nib' => isset($item['nib']) ? $item['nib'] : null,
                        'balita_nama' => $balita_nama,
                        'nama_balita' => $balita_nama,
                        'balita_jenis_kelamin' => isset($item['balita_jenis_kelamin']) ? $item['balita_jenis_kelamin'] : null,
                        'balita_tgl_lahir' => isset($item['balita_tgl_lahir']) ? $item['balita_tgl_lahir'] : null,
                        'balita_bb_lahir' => isset($item['balita_bb_lahir']) ? $item['balita_bb_lahir'] : null,
                        'balita_tb_lahir' => isset($item['balita_tb_lahir']) ? $item['balita_tb_lahir'] : null,
                    ],
                    'timsus' => [
                        'timsus_id' => isset($item['timsus_id']) ? $item['timsus_id'] : null,
                        'timsus_nama' => isset($item['timsus_nama']) ? $item['timsus_nama'] : null,
                    ],
                    'pemeriksaan' => [
                        'kode_pemeriksaan' => isset($item['kode_pemeriksaan']) ? $item['kode_pemeriksaan'] : null,
                        'tgl_pemeriksaan' => isset($item['tgl_pemeriksaan']) ? $item['tgl_pemeriksaan'] : null,
                        'berat' => isset($item['berat']) ? $item['berat'] : null,
                        'tinggi' => isset($item['tinggi']) ? $item['tinggi'] : null,
                        'lingkar_kepala' => isset($item['lingkar_kepala']) ? $item['lingkar_kepala'] : null,
                        'lingkar_lengan' => isset($item['lingkar_lengan']) ? $item['lingkar_lengan'] : null,
                        'status_gizi' => isset($item['status_gizi']) ? $item['status_gizi'] : null,
                        'status_pemeriksaan' => isset($item['status_pemeriksaan']) ? $item['status_pemeriksaan'] : null,
                        'keterangan' => isset($item['keterangan']) ? $item['keterangan'] : null,
                    ]
                ];
            }
            
            $this->res(true, 'Data treatment berhasil dimuat', $formattedData);
            
        } catch (Exception $e) {
            error_log("Error in Treatment index: " . $e->getMessage());
            $this->res(false, 'Error: ' . $e->getMessage(), null, 500);
        }
    }
    public function detail($treatment_id)
    {
        try {
            // Auth check
            $user = $this->getUserFromToken();
            if (!$user) {
                return $this->res(false, 'User tidak ditemukan', null, 401);
            }
            
            // Query detail dengan SQL manual
            $sql = "
                SELECT 
                    t.*,
                    p.*,
                    b.*,
                    ts.*
                FROM treatment t
                LEFT JOIN pemeriksaan p ON p.kode_pemeriksaan = t.kode_pemeriksaan
                LEFT JOIN balita b ON b.nib = p.nib
                LEFT JOIN timsus ts ON ts.timsus_id = t.timsus_id
                WHERE t.treatment_id = ?
            ";
            
            $query = $this->db->query($sql, [$treatment_id]);
            
            if ($query->num_rows() > 0) {
                $item = $query->row_array();
                
                // Cek field nama balita yang ada
                $balita_nama = '';
                if (isset($item['nama_balita']) && !empty($item['nama_balita'])) {
                    $balita_nama = $item['nama_balita'];
                } elseif (isset($item['balita_nama']) && !empty($item['balita_nama'])) {
                    $balita_nama = $item['balita_nama'];
                }
                
                $formattedData = [
                    'treatment_id' => isset($item['treatment_id']) ? $item['treatment_id'] : null,
                    'treatment_tgl' => isset($item['treatment_tgl']) ? $item['treatment_tgl'] : null,
                    'treatment_keterangan' => isset($item['treatment_keterangan']) ? $item['treatment_keterangan'] : null,
                    'treatment_status' => isset($item['treatment_status']) ? $item['treatment_status'] : null,
                    'treatment_TTD' => isset($item['treatment_TTD']) ? $item['treatment_TTD'] : null,
                    'treatment_ANC' => isset($item['treatment_ANC']) ? $item['treatment_ANC'] : null,
                    'treatment_PMT' => isset($item['treatment_PMT']) ? $item['treatment_PMT'] : null,
                    'treatment_imunisasi' => isset($item['treatment_imunisasi']) ? $item['treatment_imunisasi'] : null,
                    'treatment_suplemen' => isset($item['treatment_suplemen']) ? $item['treatment_suplemen'] : null,
                    'treatment_edukasi_mpasi' => isset($item['treatment_edukasi_mpasi']) ? $item['treatment_edukasi_mpasi'] : null,
                    'treatment_balita_stunting' => isset($item['treatment_balita_stunting']) ? $item['treatment_balita_stunting'] : null,
                    'treatment_sanitasi' => isset($item['treatment_sanitasi']) ? $item['treatment_sanitasi'] : null,
                    'treatment_pola_asuh' => isset($item['treatment_pola_asuh']) ? $item['treatment_pola_asuh'] : null,
                    'treatment_kb' => isset($item['treatment_kb']) ? $item['treatment_kb'] : null,
                    'kode_pemeriksaan' => isset($item['kode_pemeriksaan']) ? $item['kode_pemeriksaan'] : null,
                    'timsus_id' => isset($item['timsus_id']) ? $item['timsus_id'] : null,
                    'balita' => [
                        'nib' => isset($item['nib']) ? $item['nib'] : null,
                        'balita_nama' => $balita_nama,
                        'nama_balita' => $balita_nama,
                        'balita_jenis_kelamin' => isset($item['balita_jenis_kelamin']) ? $item['balita_jenis_kelamin'] : null,
                        'balita_tgl_lahir' => isset($item['balita_tgl_lahir']) ? $item['balita_tgl_lahir'] : null,
                        'balita_bb_lahir' => isset($item['balita_bb_lahir']) ? $item['balita_bb_lahir'] : null,
                        'balita_tb_lahir' => isset($item['balita_tb_lahir']) ? $item['balita_tb_lahir'] : null,
                        'balita_alamat' => isset($item['balita_alamat']) ? $item['balita_alamat'] : null,
                        'balita_nik' => isset($item['balita_nik']) ? $item['balita_nik'] : null,
                        'balita_anak_ke' => isset($item['balita_anak_ke']) ? $item['balita_anak_ke'] : null,
                    ],
                    'timsus' => [
                        'timsus_id' => isset($item['timsus_id']) ? $item['timsus_id'] : null,
                        'timsus_nama' => isset($item['timsus_nama']) ? $item['timsus_nama'] : null,
                        'timsus_alamat' => isset($item['timsus_alamat']) ? $item['timsus_alamat'] : null,
                        'timsus_telp' => isset($item['timsus_telp']) ? $item['timsus_telp'] : null,
                    ],
                    'pemeriksaan' => [
                        'kode_pemeriksaan' => isset($item['kode_pemeriksaan']) ? $item['kode_pemeriksaan'] : null,
                        'tgl_pemeriksaan' => isset($item['tgl_pemeriksaan']) ? $item['tgl_pemeriksaan'] : null,
                        'berat' => isset($item['berat']) ? $item['berat'] : null,
                        'tinggi' => isset($item['tinggi']) ? $item['tinggi'] : null,
                        'lingkar_kepala' => isset($item['lingkar_kepala']) ? $item['lingkar_kepala'] : null,
                        'lingkar_lengan' => isset($item['lingkar_lengan']) ? $item['lingkar_lengan'] : null,
                        'status_gizi' => isset($item['status_gizi']) ? $item['status_gizi'] : null,
                        'status_pemeriksaan' => isset($item['status_pemeriksaan']) ? $item['status_pemeriksaan'] : null,
                        'keterangan' => isset($item['keterangan']) ? $item['keterangan'] : null,
                        'is_meninggal' => isset($item['is_meninggal']) ? $item['is_meninggal'] : null,
                        'created_at' => isset($item['created_at']) ? $item['created_at'] : null,
                    ]
                ];
                
                $this->res(true, 'Detail treatment', $formattedData);
            } else {
                $this->res(false, 'Data treatment tidak ditemukan', null, 404);
            }
            
        } catch (Exception $e) {
            error_log("Error in Treatment detail: " . $e->getMessage());
            $this->res(false, 'Error: ' . $e->getMessage(), null, 500);
        }
    }
    
    public function create()
    {
        try {
            // Auth check
            $user = $this->getUserFromToken();
            if (!$user) {
                return $this->res(false, 'User tidak ditemukan', null, 401);
            }
            
            // Permission check
            $role = $user['role'];
            if (!in_array($role, ['admin', 'kader'])) {
                return $this->res(false, 'Anda tidak memiliki izin untuk menambah data treatment', null, 403);
            }
            
            // Get input
            $input = json_decode($this->input->raw_input_stream, true);
            if (empty($input)) {
                return $this->res(false, 'Input tidak boleh kosong atau bukan JSON', null, 400);
            }
            
            // Validasi
            $this->form_validation->set_data($input);
            $this->form_validation->set_rules('treatment_tgl', 'Tanggal Treatment', 'required');
            $this->form_validation->set_rules('kode_pemeriksaan', 'Kode Pemeriksaan', 'required');
            
            if ($this->form_validation->run() == FALSE) {
                return $this->res(false, validation_errors(), null, 400);
            }
            
            $data = [
                'treatment_tgl' => $this->parse_date($this->val($input, 'treatment_tgl')),
                'kode_pemeriksaan' => $this->val($input, 'kode_pemeriksaan'),
                'timsus_id' => $this->val($input, 'timsus_id') ? $this->val($input, 'timsus_id') : null,
                'treatment_keterangan' => $this->val($input, 'treatment_keterangan', ''),
                'treatment_TTD' => $this->val($input, 'treatment_TTD', 'Tidak'),
                'treatment_ANC' => $this->val($input, 'treatment_ANC', 'Tidak'),
                'treatment_PMT' => $this->val($input, 'treatment_PMT', 'Tidak'),
                'treatment_imunisasi' => $this->val($input, 'treatment_imunisasi', 'Tidak'),
                'treatment_suplemen' => $this->val($input, 'treatment_suplemen', 'Tidak'),
                'treatment_status' => 'Sudah',
                'treatment_edukasi_mpasi' => $this->val($input, 'treatment_edukasi_mpasi', 'Tidak'),
                'treatment_balita_stunting' => $this->val($input, 'treatment_balita_stunting', 'Tidak'),
                'treatment_sanitasi' => $this->val($input, 'treatment_sanitasi', 'Tidak'),
                'treatment_pola_asuh' => $this->val($input, 'treatment_pola_asuh', 'Tidak'),
                'treatment_kb' => $this->val($input, 'treatment_kb', 'Tidak'),
                'created_at' => date('Y-m-d H:i:s'),
                'created_by' => $user['id_user'],
            ];
            
            // Cek apakah pemeriksaan ada
            $pemeriksaan = $this->db->get_where('pemeriksaan', [
                'kode_pemeriksaan' => $data['kode_pemeriksaan']
            ])->row();
            
            if (!$pemeriksaan) {
                return $this->res(false, 'Data pemeriksaan tidak ditemukan', null, 400);
            }
            
            // Insert data
            $this->db->insert('treatment', $data);
            $treatment_id = $this->db->insert_id();
            
            if ($treatment_id) {
                $this->res(true, 'Berhasil tambah data Treatment.', [
                    'treatment_id' => $treatment_id,
                ]);
            } else {
                $this->res(false, 'Gagal menambahkan treatment', null, 400);
            }
            
        } catch (Exception $e) {
            error_log("Error in Treatment create: " . $e->getMessage());
            $this->res(false, 'Error: ' . $e->getMessage(), null, 500);
        }
    }
    
    public function update($treatment_id)
    {
        try {
            // Auth check
            $user = $this->getUserFromToken();
            if (!$user) {
                return $this->res(false, 'User tidak ditemukan', null, 401);
            }
            
            // Permission check
            $role = $user['role'];
            if (!in_array($role, ['admin', 'kader'])) {
                return $this->res(false, 'Anda tidak memiliki izin untuk mengedit data treatment', null, 403);
            }
            
            // Cek apakah treatment ada
            $existing = $this->db->get_where('treatment', ['treatment_id' => $treatment_id])->row();
            if (!$existing) {
                return $this->res(false, 'Data treatment tidak ditemukan', null, 404);
            }
            
            // Get input
            $input = json_decode($this->input->raw_input_stream, true);
            if (empty($input)) {
                return $this->res(false, 'Input tidak boleh kosong atau bukan JSON', null, 400);
            }
            
            $data = [
                'treatment_tgl' => $this->parse_date($this->val($input, 'treatment_tgl', $existing->treatment_tgl)),
                'kode_pemeriksaan' => $this->val($input, 'kode_pemeriksaan', $existing->kode_pemeriksaan),
                'timsus_id' => $this->val($input, 'timsus_id') ? $this->val($input, 'timsus_id') : $existing->timsus_id,
                'treatment_keterangan' => $this->val($input, 'treatment_keterangan', $existing->treatment_keterangan),
                'treatment_TTD' => $this->val($input, 'treatment_TTD', $existing->treatment_TTD),
                'treatment_ANC' => $this->val($input, 'treatment_ANC', $existing->treatment_ANC),
                'treatment_PMT' => $this->val($input, 'treatment_PMT', $existing->treatment_PMT),
                'treatment_imunisasi' => $this->val($input, 'treatment_imunisasi', $existing->treatment_imunisasi),
                'treatment_suplemen' => $this->val($input, 'treatment_suplemen', $existing->treatment_suplemen),
                'treatment_edukasi_mpasi' => $this->val($input, 'treatment_edukasi_mpasi', $existing->treatment_edukasi_mpasi),
                'treatment_balita_stunting' => $this->val($input, 'treatment_balita_stunting', $existing->treatment_balita_stunting),
                'treatment_sanitasi' => $this->val($input, 'treatment_sanitasi', $existing->treatment_sanitasi),
                'treatment_pola_asuh' => $this->val($input, 'treatment_pola_asuh', $existing->treatment_pola_asuh),
                'treatment_kb' => $this->val($input, 'treatment_kb', $existing->treatment_kb),
                'updated_at' => date('Y-m-d H:i:s'),
                'updated_by' => $user['id_user'],
            ];
            
            $this->db->where('treatment_id', $treatment_id);
            $result = $this->db->update('treatment', $data);
            
            if ($result) {
                $this->res(true, 'Berhasil edit data Treatment.');
            } else {
                $this->res(false, 'Gagal update data treatment', null, 400);
            }
            
        } catch (Exception $e) {
            error_log("Error in Treatment update: " . $e->getMessage());
            $this->res(false, 'Error: ' . $e->getMessage(), null, 500);
        }
    }
    public function delete($treatment_id)
    {
        try {
            // Auth check
            $user = $this->getUserFromToken();
            if (!$user) {
                return $this->res(false, 'User tidak ditemukan', null, 401);
            }
            
            // Permission check - hanya admin
            if ($user['role'] != 'admin') {
                return $this->res(false, 'Anda tidak memiliki izin untuk menghapus data treatment', null, 403);
            }
            
            // Cek apakah data ada
            $existing = $this->db->get_where('treatment', ['treatment_id' => $treatment_id])->row();
            if (!$existing) {
                return $this->res(false, 'Data treatment tidak ditemukan', null, 404);
            }
            
            $result = $this->db->delete('treatment', ['treatment_id' => $treatment_id]);
            
            if ($result) {
                $this->res(true, 'Berhasil hapus data treatment');
            } else {
                $this->res(false, 'Gagal menghapus data treatment', null, 400);
            }
            
        } catch (Exception $e) {
            error_log("Error in Treatment delete: " . $e->getMessage());
            $this->res(false, 'Error: ' . $e->getMessage(), null, 500);
        }
    }
    
    // GET: /api/treatment/pemeriksaan
    public function pemeriksaan()
    {
        try {
            // Auth check
            $user = $this->getUserFromToken();
            if (!$user) {
                return $this->res(false, 'User tidak ditemukan', null, 401);
            }
            
            // Query yang benar berdasarkan struktur database
            $sql = "
                SELECT 
                    p.*,
                    b.nib,
                    b.nama_balita
                FROM pemeriksaan p
                LEFT JOIN balita b ON b.nib = p.nib
                WHERE p.status_pemeriksaan = 'Stunting'
                GROUP BY p.nib
                ORDER BY p.kode_pemeriksaan DESC
            ";
            
            $query = $this->db->query($sql);
            $result = $query->result_array();
            
            // Format data
            $data = [];
            foreach ($result as $item) {
                $data[] = [
                    'kode_pemeriksaan' => isset($item['kode_pemeriksaan']) ? $item['kode_pemeriksaan'] : null,
                    'nib' => isset($item['nib']) ? $item['nib'] : null,
                    'tgl_pemeriksaan' => isset($item['tgl_pemeriksaan']) ? $item['tgl_pemeriksaan'] : null,
                    'berat' => isset($item['berat']) ? $item['berat'] : null,
                    'tinggi' => isset($item['tinggi']) ? $item['tinggi'] : null,
                    'lingkar_kepala' => isset($item['lingkar_kepala']) ? $item['lingkar_kepala'] : null,
                    'lingkar_lengan' => isset($item['lingkar_lengan']) ? $item['lingkar_lengan'] : null,
                    'status_gizi' => isset($item['status_gizi']) ? $item['status_gizi'] : null,
                    'status_pemeriksaan' => isset($item['status_pemeriksaan']) ? $item['status_pemeriksaan'] : null,
                    'keterangan' => isset($item['keterangan']) ? $item['keterangan'] : null,
                    'balita' => [
                        'nib' => isset($item['nib']) ? $item['nib'] : null,
                        'balita_nama' => isset($item['nama_balita']) ? $item['nama_balita'] : null,
                        'nama_balita' => isset($item['nama_balita']) ? $item['nama_balita'] : null,
                        'balita_jenis_kelamin' => isset($item['balita_jenis_kelamin']) ? $item['balita_jenis_kelamin'] : null,
                        'balita_tgl_lahir' => isset($item['balita_tgl_lahir']) ? $item['balita_tgl_lahir'] : null,
                        'balita_bb_lahir' => isset($item['balita_bb_lahir']) ? $item['balita_bb_lahir'] : null,
                        'balita_tb_lahir' => isset($item['balita_tb_lahir']) ? $item['balita_tb_lahir'] : null,
                    ]
                ];
            }
            
            $this->res(true, 'Data pemeriksaan', $data);
            
        } catch (Exception $e) {
            error_log("Error in Treatment pemeriksaan: " . $e->getMessage());
            $this->res(false, 'Error: ' . $e->getMessage(), null, 500);
        }
    }
    
    // GET: /api/treatment/timsus
    public function timsus()
    {
        try {
            // Auth check
            $user = $this->getUserFromToken();
            if (!$user) {
                return $this->res(false, 'User tidak ditemukan', null, 401);
            }
            
            $query = $this->db->query("SELECT * FROM timsus ORDER BY timsus_nama ASC");
            $data = $query->result_array();
            
            $this->res(true, 'Data timsus', $data);
            
        } catch (Exception $e) {
            error_log("Error in Treatment timsus: " . $e->getMessage());
            $this->res(false, 'Error: ' . $e->getMessage(), null, 500);
        }
    }
}
