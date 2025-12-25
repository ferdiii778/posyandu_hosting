<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Bumil extends CI_Controller
{
    public function __construct()
    {
        parent::__construct();
        $this->load->model('BumilModel');
        $this->load->database();
        header('Content-Type: application/json');
    }

    // --------------------------
    // ?? Helper functions
    // --------------------------
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
    
    private function res($status, $msg)
    {
        echo json_encode(array('status' => $status, 'message' => $msg));
    }
    
    private function auth() {
        $token = $this->input->get_request_header('X-Token');
        $expired = $this->input->get_request_header('X-Expired');
        
        if (!$token || !$expired) {
            return ['status'=>false, 'message'=>'Token diperlukan'];
        }
    
        if ($expired < time()) {
            return ['status'=>false, 'message'=>'Token expired'];
        }
        
        return ['status'=>true];
    }

    private function getUserFromToken() {
        $user_id = $this->input->get_request_header('X-User-Id');
        if (!$user_id) return null;
        
        return $this->db->get_where('user', ['id_user' => $user_id])->row_array();
    }

    // ?? Get bumil_id from user table
    private function get_bumil_id_from_user($id_user)
    {
        $this->db->select('bumil_id');
        $this->db->where('id_user', $id_user);
        $query = $this->db->get('user');
        
        if ($query->num_rows() > 0) {
            $user_data = $query->row();
            return $user_data->bumil_id;
        }
        return null;
    }

    // ==========================================================
    //  BAGIAN DATA IBU HAMIL - DIMODIFIKASI UNTUK ROLE-BASED
    // ==========================================================

    public function index()
    {
        // --- AUTH CHECK ---
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }
    
        // --- GET USER FROM TOKEN ---
        $user = $this->getUserFromToken();
        if (!$user) {
            return $this->res(false, 'User tidak ditemukan');
        }
    
        // Ambil role & posyandu dari user header-token
        $role = $user['role'];
        $posyandu_id = $user['posyandu_id'];
        $bumil_id = $user['bumil_id']; // untuk member
    
        try {
            $data = [];
    
            switch ($role) {
    
                // ================================
                // ADMIN → Lihat semua data
                // ================================
                case 'admin':
                    $data = $this->BumilModel->get_all();
                    break;
    
                // ================================
                // KADER → Lihat berdasarkan posyandu_id
                // ================================
                case 'kader':
    
                    if (!$posyandu_id) {
                        return $this->res(false, 'Posyandu ID tidak ditemukan untuk kader');
                    }
    
                    $this->db->where('posyandu_id', $posyandu_id);
                    $data = $this->db->get('bumil')->result();
                    break;
    
                // ================================
                // MEMBER → hanya lihat data diri sendiri
                // ================================
                case 'member':
    
                    if ($bumil_id) {
                        $this->db->where('bumil_id', $bumil_id);
                        $data = $this->db->get('bumil')->result();
                    } else {
                        $data = [];
                    }
    
                    break;
    
                default:
                    return $this->res(false, 'Role tidak dikenali');
            }
    
            echo json_encode([
                'status' => true,
                'message' => 'Data ibu hamil berhasil dimuat',
                'role' => $role,
                'data' => $data
            ]);
    
        } catch (Exception $e) {
            return $this->res(false, 'Error: ' . $e->getMessage());
        }
    }


    public function detail($id = null)
    {
        if (!$id) return $this->res(false, 'Parameter ID tidak ditemukan');
    
        // --- AUTH CHECK ---
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }
    
        // --- USER FROM TOKEN ---
        $user = $this->getUserFromToken();
        if (!$user) {
            return $this->res(false, 'User tidak ditemukan');
        }
    
        // role & id dari header token
        $role = $user['role'];
        $posyandu_id = $user['posyandu_id'];
        $user_bumil_id = $user['bumil_id'];
    
        // --- GET DATA ---
        $data = $this->BumilModel->get_by_id($id);
        if (!$data) {
            return $this->res(false, 'Data tidak ditemukan');
        }
    
        // --- ROLE: KADER ---
        // hanya bisa melihat data dengan posyandu_id yang sama
        if ($role == 'kader') {
            if ($posyandu_id != $data->posyandu_id) {
                return $this->res(false, 'Anda tidak memiliki akses ke data ini');
            }
        }
    
        // --- ROLE: MEMBER ---
        // hanya bisa melihat data dirinya sendiri
        if ($role == 'member') {
            if ($user_bumil_id != $id) {
                return $this->res(false, 'Anda hanya bisa melihat data sendiri');
            }
        }
    
        // --- SUCCESS ---
        echo json_encode([
            'status' => true,
            'data' => $data
        ]);
    }

    public function add()
    {
        // --- AUTH CHECK ---
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }
    
        // --- USER FROM TOKEN ---
        $user = $this->getUserFromToken();
        if (!$user) {
            return $this->res(false, 'User tidak ditemukan');
        }
    
        $role = $user['role'];
        $id_user = $user['id_user'];
    
        // --- ROLE VALIDATION ---
        // Saat ini hanya admin yang boleh menambah data bumil
        if ($role != 'admin') {
            return $this->res(false, 'Anda tidak memiliki izin untuk menambah data');
        }
    
        // --- INPUT JSON ---
        $input = json_decode($this->input->raw_input_stream, true);
        if (empty($input)) {
            return $this->res(false, 'Input tidak boleh kosong / bukan JSON');
        }
    
        // --- DATA MAPPING ---
        $data = array(
            'posyandu_id' => $this->val($input, 'posyandu_id'),
            'bumil_nama' => $this->val($input, 'bumil_nama'),
            'bumil_ttl' => $this->parse_date($this->val($input, 'bumil_ttl')),
            'bumil_nik' => $this->val($input, 'bumil_nik'),
            'bumil_no_jkn' => $this->val($input, 'bumil_no_jkn'),
            'bumil_goldar' => $this->val($input, 'bumil_goldar'),
            'bumil_faskes1' => $this->val($input, 'bumil_faskes1'),
            'bumil_faskes_rujukan' => $this->val($input, 'bumil_faskes_rujukan'),
            'bumil_pendidikan' => $this->val($input, 'bumil_pendidikan'),
            'bumil_pekerjaan' => $this->val($input, 'bumil_pekerjaan'),
            'bumil_telp' => $this->val($input, 'bumil_telp'),
            'bumil_alamat' => $this->val($input, 'bumil_alamat'),
            'bumil_asuransi_lain' => $this->val($input, 'bumil_asuransi_lain'),
            'bumil_asuransi_lain_no' => $this->val($input, 'bumil_asuransi_lain_no'),
            'bumil_asuransi_lain_tgl_aktif' => $this->parse_date($this->val($input, 'bumil_asuransi_lain_tgl_aktif')),
            'bumil_anak_ke' => $this->val($input, 'bumil_anak_ke', 0),
            'bumil_tgl_input' => date('Y-m-d')
        );
    
        // --- VALIDATION ---
        if (empty($data['bumil_nama']) || empty($data['posyandu_id'])) {
            return $this->res(false, 'Nama dan Posyandu wajib diisi!');
        }
    
        // --- INSERT ---
        $ok = $this->BumilModel->insert($data);
    
        if ($ok) {
    
            // Get ID baru
            $new_bumil_id = $this->db->insert_id();
    
            // Update tabel user -> set bumil_id
            $this->db->where('id_user', $id_user);
            $this->db->update('user', ['bumil_id' => $new_bumil_id]);
    
            echo json_encode([
                'status' => true,
                'message' => 'Data ibu hamil berhasil disimpan',
                'data' => $data,
                'bumil_id' => $new_bumil_id
            ]);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal menyimpan data']);
        }
    }
    
    public function update($id = null)
    {
        if (!$id) return $this->res(false, 'ID tidak boleh kosong');
    
        // --- AUTH ---
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }
    
        // --- USER FROM TOKEN ---
        $user = $this->getUserFromToken();
        if (!$user) return $this->res(false, 'User tidak ditemukan');
    
        $role = $user['role'];
        $posyandu_user = $user['posyandu_id'];
        $user_bumil_id = $user['bumil_id'];
    
        // --- EXISTING DATA ---
        $existing = $this->BumilModel->get_by_id($id);
        if (!$existing) return $this->res(false, 'Data tidak ditemukan');
    
        // --- PERMISSION ---
        if ($role == 'kader' && $existing->posyandu_id != $posyandu_user) {
            return $this->res(false, 'Anda tidak memiliki izin mengedit data posyandu lain');
        }
    
        if ($role == 'member' && $user_bumil_id != $id) {
            return $this->res(false, 'Anda hanya bisa mengedit data milik Anda sendiri');
        }
    
        // --- INPUT JSON ---
        $input = json_decode($this->input->raw_input_stream, true);
        if (empty($input)) return $this->res(false, 'Input tidak boleh kosong / bukan JSON');
    
        // --- UPDATE DATA LENGKAP ---
        $data = [
            'posyandu_id'               => $this->val($input, 'posyandu_id', $existing->posyandu_id),
            'bumil_nama'                => $this->val($input, 'bumil_nama', $existing->bumil_nama),
            'bumil_ttl'                 => $this->parse_date($this->val($input, 'bumil_ttl', $existing->bumil_ttl)),
            'bumil_nik'                 => $this->val($input, 'bumil_nik', $existing->bumil_nik),
            'bumil_no_jkn'              => $this->val($input, 'bumil_no_jkn', $existing->bumil_no_jkn),
            'bumil_goldar'              => $this->val($input, 'bumil_goldar', $existing->bumil_goldar),
            'bumil_faskes1'             => $this->val($input, 'bumil_faskes1', $existing->bumil_faskes1),
            'bumil_faskes_rujukan'      => $this->val($input, 'bumil_faskes_rujukan', $existing->bumil_faskes_rujukan),
            'bumil_pendidikan'          => $this->val($input, 'bumil_pendidikan', $existing->bumil_pendidikan),
            'bumil_pekerjaan'           => $this->val($input, 'bumil_pekerjaan', $existing->bumil_pekerjaan),
            'bumil_telp'                => $this->val($input, 'bumil_telp', $existing->bumil_telp),
            'bumil_alamat'              => $this->val($input, 'bumil_alamat', $existing->bumil_alamat),
            'bumil_anak_ke'             => $this->val($input, 'bumil_anak_ke', $existing->bumil_anak_ke),
    
            // --- FIELD TAMBAHAN ---
            'bumil_asuransi_lain'           => $this->val($input, 'bumil_asuransi_lain', $existing->bumil_asuransi_lain),
            'bumil_asuransi_lain_no'        => $this->val($input, 'bumil_asuransi_lain_no', $existing->bumil_asuransi_lain_no),
            'bumil_asuransi_lain_tgl_aktif' => $this->parse_date($this->val($input, 'bumil_asuransi_lain_tgl_aktif', $existing->bumil_asuransi_lain_tgl_aktif)),
    
            // --- FIELD NOT NULL ---
            'bumil_puskesmas_domisili'  => $this->val($input, 'bumil_puskesmas_domisili', $existing->bumil_puskesmas_domisili),
            'bumil_no_kohort1_ibu'      => $this->val($input, 'bumil_no_kohort1_ibu', $existing->bumil_no_kohort1_ibu),
            'bumil_no_kohort1_bayi'     => $this->val($input, 'bumil_no_kohort1_bayi', $existing->bumil_no_kohort1_bayi),
            'bumil_no_kohort1_balita'   => $this->val($input, 'bumil_no_kohort1_balita', $existing->bumil_no_kohort1_balita),
            'bumil_no_catatan_medik'    => $this->val($input, 'bumil_no_catatan_medik', $existing->bumil_no_catatan_medik),
    
            // TGL Input jangan berubah
            'bumil_tgl_input' => $existing->bumil_tgl_input,
        ];
    
        // --- EXECUTE UPDATE (AMANKAN) ---
        $this->db->where('bumil_id', $id);
        $ok = $this->db->update('bumil', $data);
    
        echo json_encode([
            'status' => ($ok !== false),
            'message' => $ok !== false ? 'Data ibu hamil berhasil diperbarui' : 'Gagal update data'
        ]);
    }


    public function delete($id = null)
    {
        if (!$id) return $this->res(false, 'ID tidak ditemukan');
    
        // --- AUTH TOKEN ---
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }
    
        // --- USER FROM TOKEN ---
        $user = $this->getUserFromToken();
        if (!$user) {
            return $this->res(false, 'User tidak ditemukan');
        }
    
        $role = $user['role'];
        $posyandu_user = $user['posyandu_id'];
        $user_bumil_id = $user['bumil_id'];
    
        // --- CEK DATA YANG AKAN DIHAPUS ---
        $target = $this->BumilModel->get_by_id($id);
        if (!$target) {
            return $this->res(false, 'Data ibu hamil tidak ditemukan');
        }
    
        // ============================
        //  ROLE VALIDATION
        // ============================
    
        // Admin → boleh hapus semua
        if ($role == 'kader') {
            // Kader hanya boleh hapus yang posyandu_id == posyandu kader
            if ($target->posyandu_id != $posyandu_user) {
                return $this->res(false, 'Anda tidak memiliki izin untuk menghapus data dari posyandu lain');
            }
        }
    
        if ($role == 'member') {
            // Member hanya boleh hapus data dirinya sendiri
            if ($user_bumil_id != $id) {
                return $this->res(false, 'Anda hanya bisa menghapus data milik Anda sendiri');
            }
        }
    
        // ============================
        //  EXECUTE DELETE
        // ============================
    
        $ok = $this->BumilModel->delete($id);
    
        if ($ok) {
            // Kosongkan bumil_id user yang terkait
            $this->db->where('bumil_id', $id);
            $this->db->update('user', ['bumil_id' => null]);
    
            echo json_encode([
                'status' => true,
                'message' => 'Data ibu hamil berhasil dihapus'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal menghapus data'
            ]);
        }
    }


    // ==========================================================
    //  BAGIAN PEMERIKSAAN KEHAMILAN - DIMODIFIKASI UNTUK ROLE-BASED
    // ==========================================================
    
    public function pemeriksaan($bumil_id = null)
    {
        if (!$bumil_id) {
            http_response_code(400);
            return $this->res(false, 'Parameter bumil_id wajib ada!');
        }
    
        // --- AUTH TOKEN ---
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }
    
        // --- USER FROM TOKEN ---
        $user = $this->getUserFromToken();
        if (!$user) {
            return $this->res(false, 'User tidak ditemukan');
        }
    
        $role = $user['role'];
        $posyandu_user = $user['posyandu_id'];
        $user_bumil_id = $user['bumil_id'];
    
        // --- GET TARGET BUMIL ---
        $bumil_data = $this->BumilModel->get_by_id($bumil_id);
        if (!$bumil_data) {
            return $this->res(false, 'Data ibu hamil tidak ditemukan');
        }
    
        // ============================
        //  ROLE VALIDATION
        // ============================
    
        // --- KADER ---
        if ($role == 'kader') {
            if ($bumil_data->posyandu_id != $posyandu_user) {
                return $this->res(false, 'Anda tidak memiliki akses ke data ini');
            }
        }
    
        // --- MEMBER ---
        if ($role == 'member') {
            if ($user_bumil_id != $bumil_id) {
                return $this->res(false, 'Anda hanya bisa melihat data pemeriksaan milik Anda');
            }
        }
    
        // ============================
        //  GET PEMERIKSAAN
        // ============================
    
        $query = $this->db->get_where('pemeriksaan_kehamilan', ['bumil_id' => $bumil_id]);
        $data = $query->result();
    
        echo json_encode([
            'status' => true,
            'message' => 'Data pemeriksaan dimuat',
            'data' => $data
        ]);
    }

    public function pemeriksaan_add()
    {
        // --- TOKEN AUTH ---
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }
    
        // --- USER FROM TOKEN ---
        $user = $this->getUserFromToken();
        if (!$user) {
            return $this->res(false, 'User tidak ditemukan');
        }
    
        $role = $user['role'];
        $posyandu_user = $user['posyandu_id'];
    
        // --- INPUT ---
        $input = json_decode($this->input->raw_input_stream, true);
        if (empty($input)) return $this->res(false, 'Input tidak boleh kosong / bukan JSON');
    
        $bumil_id = $this->val($input, 'bumil_id');
    
        if (!$bumil_id) {
            return $this->res(false, 'bumil_id wajib dikirim');
        }
    
        // --- CEK BUMIL ---
        $bumil_data = $this->BumilModel->get_by_id($bumil_id);
        if (!$bumil_data) {
            return $this->res(false, 'Data ibu hamil tidak ditemukan');
        }
    
        // ===========================
        // ROLE CHECK
        // ===========================
    
        // Admin boleh input pemeriksaan siapapun
        if ($role == 'admin') {
            // aman
        }
    
        // Kader hanya boleh input pada bumil dari posyandu yang sama
        elseif ($role == 'kader') {
            if ($bumil_data->posyandu_id != $posyandu_user) {
                return $this->res(false, 'Anda tidak memiliki akses untuk menambah pemeriksaan pada bumil ini');
            }
        }
    
        // Member tidak boleh input pemeriksaan kehamilan
        else {
            return $this->res(false, 'Role Anda tidak diperbolehkan menambah pemeriksaan');
        }
    
        // ===========================
        // PROSES INSERT PEMERIKSAAN
        // ===========================
        
        $data = array(
            'bumil_id' => $bumil_id,
            'pemeriksaan_kehamilan_tgl' => $this->parse_date($this->val($input, 'pemeriksaan_kehamilan_tgl')),
            'pemeriksaan_kehamilan_minggu' => $this->val($input, 'pemeriksaan_kehamilan_minggu'),
            'pemeriksaan_kehamilan_rutin' => $this->val($input, 'pemeriksaan_kehamilan_rutin', 0),
            'pemeriksaan_kehamilan_kelas' => $this->val($input, 'pemeriksaan_kehamilan_kelas', 0),
            'pemeriksaan_kehamilan_demam' => $this->val($input, 'pemeriksaan_kehamilan_demam', 0),
            'pemeriksaan_kehamilan_pusing' => $this->val($input, 'pemeriksaan_kehamilan_pusing', 0),
            'pemeriksaan_kehamilan_cemas' => $this->val($input, 'pemeriksaan_kehamilan_cemas', 0),
            'pemeriksaan_kehamilan_tb' => $this->val($input, 'pemeriksaan_kehamilan_tb', 0),
            'pemeriksaan_kehamilan_gerakan_bayi' => $this->val($input, 'pemeriksaan_kehamilan_gerakan_bayi', 0),
            'pemeriksaan_kehamilan_nyeri_perut' => $this->val($input, 'pemeriksaan_kehamilan_nyeri_perut', 0),
            'pemeriksaan_kehamilan_keluar_cairan' => $this->val($input, 'pemeriksaan_kehamilan_keluar_cairan', 0),
            'pemeriksaan_kehamilan_sakit_saat_kencing' => $this->val($input, 'pemeriksaan_kehamilan_sakit_saat_kencing', 0),
            'pemeriksaan_kehamilan_diare' => $this->val($input, 'pemeriksaan_kehamilan_diare', 0),
            'pemeriksaan_kehamilan_kader' => $role == 'kader' ? $user['username'] : 'admin',
            'pemeriksaan_kehamilan_trimester' => $this->val($input, 'pemeriksaan_kehamilan_trimester'),
            'pemeriksaan_kehamilan_berat' => $this->val($input, 'pemeriksaan_kehamilan_berat'),
            'pemeriksaan_kehamilan_tinggi' => $this->val($input, 'pemeriksaan_kehamilan_tinggi'),
            'pemeriksaan_kehamilan_lila' => $this->val($input, 'pemeriksaan_kehamilan_lila'),
            'pemeriksaan_kehamilan_tekanan_darah' => $this->val($input, 'pemeriksaan_kehamilan_tekanan_darah'),
            'pemeriksaan_kehamilan_tinggi_rahim' => $this->val($input, 'pemeriksaan_kehamilan_tinggi_rahim'),
            'pemeriksaan_kehamilan_jantung_bayi' => $this->val($input, 'pemeriksaan_kehamilan_jantung_bayi'),
            'pemeriksaan_kehamilan_imunisasi_tetanus' => $this->val($input, 'pemeriksaan_kehamilan_imunisasi_tetanus'),
            'pemeriksaan_kehamilan_konseling' => $this->val($input, 'pemeriksaan_kehamilan_konseling'),
            'pemeriksaan_kehamilan_skrining_dokter' => $this->val($input, 'pemeriksaan_kehamilan_skrining_dokter', 0),
            'pemeriksaan_kehamilan_tambah_darah' => $this->val($input, 'pemeriksaan_kehamilan_tambah_darah', 0),
            'pemeriksaan_kehamilan_tes_hb' => $this->val($input, 'pemeriksaan_kehamilan_tes_hb'),
            'pemeriksaan_kehamilan_tes_goldar' => $this->val($input, 'pemeriksaan_kehamilan_tes_goldar'),
            'pemeriksaan_kehamilan_tes_protein_urine' => $this->val($input, 'pemeriksaan_kehamilan_tes_protein_urine'),
            'pemeriksaan_kehamilan_tes_guladarah' => $this->val($input, 'pemeriksaan_kehamilan_tes_guladarah'),
            'pemeriksaan_kehamilan_usg' => $this->val($input, 'pemeriksaan_kehamilan_usg', 0),
            'pemeriksaan_kehamilan_triple_eliminasi_h' => $this->val($input, 'pemeriksaan_kehamilan_triple_eliminasi_h', 0),
            'pemeriksaan_kehamilan_triple_eliminasi_s' => $this->val($input, 'pemeriksaan_kehamilan_triple_eliminasi_s', 0),
            'pemeriksaan_kehamilan_triple_eliminasi_hepB' => $this->val($input, 'pemeriksaan_kehamilan_triple_eliminasi_hepB', 0),
            'pemeriksaan_kehamilan_tata_laksana_kasus' => $this->val($input, 'pemeriksaan_kehamilan_tata_laksana_kasus')
        );
    
        $ok = $this->db->insert('pemeriksaan_kehamilan', $data);
    
        echo json_encode($ok
            ? array('status' => true, 'message' => 'Pemeriksaan berhasil ditambahkan', 'data' => $data)
            : array('status' => false, 'message' => 'Gagal menambah pemeriksaan')
        );
    }
    
    public function pemeriksaan_edit($id = null)
    {
        // =============================
        // VALIDASI ID
        // =============================
        if (!$id) return $this->res(false, 'ID pemeriksaan wajib diisi!');
    
        // =============================
        // TOKEN AUTH
        // =============================
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }
    
        // =============================
        // USER FROM TOKEN
        // =============================
        $user = $this->getUserFromToken();
        if (!$user) {
            return $this->res(false, 'User tidak ditemukan');
        }
    
        $role = $user['role'];
        $posyandu_user = $user['posyandu_id'];
    
        // =============================
        // CEK APAKAH PEMERIKSAAN ADA?
        // =============================
        $pemeriksaan = $this->db
            ->get_where('pemeriksaan_kehamilan', ['pemeriksaan_kehamilan_id' => $id])
            ->row();
    
        if (!$pemeriksaan) {
            return $this->res(false, 'Data pemeriksaan tidak ditemukan');
        }
    
        // =============================
        // CEK DATA BUMIL UNTUK POSYANDU
        // =============================
        $bumil = $this->BumilModel->get_by_id($pemeriksaan->bumil_id);
        if (!$bumil) {
            return $this->res(false, 'Data bumil tidak ditemukan');
        }
    
        // =============================
        // ROLE VALIDATION
        // =============================
    
        // Admin → bebas edit
        if ($role == 'admin') {
            // aman
        }
        // Kader → hanya boleh edit pemeriksaan bumil dari posyandunya
        elseif ($role == 'kader') {
            if ($bumil->posyandu_id != $posyandu_user) {
                return $this->res(false, 'Anda tidak memiliki akses mengedit pemeriksaan ini');
            }
        }
        // Member → tidak boleh edit pemeriksaan
        else {
            return $this->res(false, 'Anda tidak memiliki izin untuk mengedit data pemeriksaan');
        }
    
        // =============================
        // INPUT
        // =============================
        $input = json_decode($this->input->raw_input_stream, true);
        if (empty($input)) return $this->res(false, 'Input tidak boleh kosong / bukan JSON');
    
        // =============================
        // UPDATE FIELDS
        // =============================
        $data = array(
            'pemeriksaan_kehamilan_tgl' => $this->parse_date($this->val($input, 'pemeriksaan_kehamilan_tgl')),
            'pemeriksaan_kehamilan_minggu' => $this->val($input, 'pemeriksaan_kehamilan_minggu', 0),
            'pemeriksaan_kehamilan_rutin' => $this->val($input, 'pemeriksaan_kehamilan_rutin', 0),
            'pemeriksaan_kehamilan_kelas' => $this->val($input, 'pemeriksaan_kehamilan_kelas', 0),
            'pemeriksaan_kehamilan_demam' => $this->val($input, 'pemeriksaan_kehamilan_demam', 0),
            'pemeriksaan_kehamilan_pusing' => $this->val($input, 'pemeriksaan_kehamilan_pusing', 0),
            'pemeriksaan_kehamilan_cemas' => $this->val($input, 'pemeriksaan_kehamilan_cemas', 0),
            'pemeriksaan_kehamilan_tb' => $this->val($input, 'pemeriksaan_kehamilan_tb', 0),
            'pemeriksaan_kehamilan_gerakan_bayi' => $this->val($input, 'pemeriksaan_kehamilan_gerakan_bayi', 0),
            'pemeriksaan_kehamilan_nyeri_perut' => $this->val($input, 'pemeriksaan_kehamilan_nyeri_perut', 0),
            'pemeriksaan_kehamilan_keluar_cairan' => $this->val($input, 'pemeriksaan_kehamilan_keluar_cairan', 0),
            'pemeriksaan_kehamilan_sakit_saat_kencing' => $this->val($input, 'pemeriksaan_kehamilan_sakit_saat_kencing', 0),
            'pemeriksaan_kehamilan_diare' => $this->val($input, 'pemeriksaan_kehamilan_diare', 0),
            'pemeriksaan_kehamilan_kader' => $role == 'kader' ? $user['username'] : 'admin',
            'pemeriksaan_kehamilan_berat' => $this->val($input, 'pemeriksaan_kehamilan_berat'),
            'pemeriksaan_kehamilan_tinggi' => $this->val($input, 'pemeriksaan_kehamilan_tinggi'),
            'pemeriksaan_kehamilan_lila' => $this->val($input, 'pemeriksaan_kehamilan_lila'),
            'pemeriksaan_kehamilan_tekanan_darah' => $this->val($input, 'pemeriksaan_kehamilan_tekanan_darah'),
            'pemeriksaan_kehamilan_tinggi_rahim' => $this->val($input, 'pemeriksaan_kehamilan_tinggi_rahim'),
            'pemeriksaan_kehamilan_jantung_bayi' => $this->val($input, 'pemeriksaan_kehamilan_jantung_bayi'),
            'pemeriksaan_kehamilan_imunisasi_tetanus' => $this->val($input, 'pemeriksaan_kehamilan_imunisasi_tetanus'),
            'pemeriksaan_kehamilan_konseling' => $this->val($input, 'pemeriksaan_kehamilan_konseling'),
            'pemeriksaan_kehamilan_skrining_dokter' => $this->val($input, 'pemeriksaan_kehamilan_skrining_dokter', 0),
            'pemeriksaan_kehamilan_tambah_darah' => $this->val($input, 'pemeriksaan_kehamilan_tambah_darah', 0),
            'pemeriksaan_kehamilan_tes_hb' => $this->val($input, 'pemeriksaan_kehamilan_tes_hb'),
            'pemeriksaan_kehamilan_tes_goldar' => $this->val($input, 'pemeriksaan_kehamilan_tes_goldar'),
            'pemeriksaan_kehamilan_tes_protein_urine' => $this->val($input, 'pemeriksaan_kehamilan_tes_protein_urine'),
            'pemeriksaan_kehamilan_tes_guladarah' => $this->val($input, 'pemeriksaan_kehamilan_tes_guladarah'),
            'pemeriksaan_kehamilan_usg' => $this->val($input, 'pemeriksaan_kehamilan_usg', 0),
            'pemeriksaan_kehamilan_triple_eliminasi_h' => $this->val($input, 'pemeriksaan_kehamilan_triple_eliminasi_h', 0),
            'pemeriksaan_kehamilan_triple_eliminasi_s' => $this->val($input, 'pemeriksaan_kehamilan_triple_eliminasi_s', 0),
            'pemeriksaan_kehamilan_triple_eliminasi_hepB' => $this->val($input, 'pemeriksaan_kehamilan_triple_eliminasi_hepB', 0),
            'pemeriksaan_kehamilan_tata_laksana_kasus' => $this->val($input, 'pemeriksaan_kehamilan_tata_laksana_kasus')
        );
    
        // =============================
        // UPDATE KE DATABASE
        // =============================
        $this->db->where('pemeriksaan_kehamilan_id', $id);
        $ok = $this->db->update('pemeriksaan_kehamilan', $data);
    
        echo json_encode($ok
            ? array('status' => true, 'message' => 'Data pemeriksaan berhasil diubah')
            : array('status' => false, 'message' => 'Gagal mengubah data')
        );
    }

    public function pemeriksaan_delete($id = null)
    {
        // ===============================
        // 1. VALIDASI ID
        // ===============================
        if (!$id) return $this->res(false, 'ID pemeriksaan tidak ditemukan!');
    
        // ===============================
        // 2. AUTH TOKEN
        // ===============================
        $auth = $this->auth();
        if (!$auth['status']) {
            echo json_encode($auth);
            return;
        }
    
        // ===============================
        // 3. USER DARI TOKEN
        // ===============================
        $user = $this->getUserFromToken();
        if (!$user) {
            return $this->res(false, 'User tidak ditemukan');
        }
    
        $role = $user['role'];
        $posyandu_user = $user['posyandu_id'];
    
        // ===============================
        // 4. CEK PEMERIKSAAN ADA?
        // ===============================
        $pemeriksaan = $this->db
            ->get_where('pemeriksaan_kehamilan', ['pemeriksaan_kehamilan_id' => $id])
            ->row();
    
        if (!$pemeriksaan) {
            return $this->res(false, 'Data pemeriksaan tidak ditemukan');
        }
    
        // ===============================
        // 5. CEK DATA BUMIL UNTUK POSYANDU
        // ===============================
        $bumil = $this->BumilModel->get_by_id($pemeriksaan->bumil_id);
        if (!$bumil) {
            return $this->res(false, 'Data bumil tidak ditemukan');
        }
    
        // ===============================
        // 6. ROLE VALIDATION
        // ===============================
    
        // Admin → boleh hapus semua
        if ($role == 'admin') {
            // OK
        }
        // Kader → hanya boleh hapus jika bumil dari posyandunya
        elseif ($role == 'kader') {
            if ($bumil->posyandu_id != $posyandu_user) {
                return $this->res(false, 'Anda tidak memiliki akses menghapus data pemeriksaan ini');
            }
        }
        // Member → tidak boleh hapus
        else {
            return $this->res(false, 'Anda tidak memiliki izin untuk menghapus data pemeriksaan');
        }
    
        // ===============================
        // 7. EKSEKUSI DELETE
        // ===============================
        $ok = $this->db->delete('pemeriksaan_kehamilan', [
            'pemeriksaan_kehamilan_id' => $id
        ]);
    
        echo json_encode(
            $ok
                ? ['status' => true, 'message' => 'Data pemeriksaan berhasil dihapus']
                : ['status' => false, 'message' => 'Gagal menghapus data pemeriksaan']
        );
    }
}