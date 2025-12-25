<?php
defined('BASEPATH') OR exit('No direct script access allowed');

class user extends CI_Controller {

    public function __construct() {
        parent::__construct();
        $this->load->model('modelsapi/Muser');
        $this->load->helper('token'); // ← PENTING
        header('Content-Type: application/json');
    }

    // ✅ GET semua user
    public function index() {
        $data = $this->Muser->getAll();
        echo json_encode([
            'status' => true,
            'message' => 'Data user berhasil dimuat',
            'data' => $data
        ]);
    }

    // ✅ GET data untuk dropdown (Posyandu & Tim Khusus)
    public function get_dropdown_data() {
        // Ambil data dari tabel sesuai kode mentor
        $posyandu = $this->db->get('ref_posyandu')->result_array();
        $timsus = $this->db->get('timsus')->result_array();
    
        echo json_encode([
            'status' => true,
            'message' => 'Data dropdown berhasil dimuat',
            'data' => [
                'posyandu' => $posyandu,
                'timsus' => $timsus,
                'levels' => ['admin', 'kader', 'member'] // Level bisa statis atau ambil dari DB jika ada tabelnya
            ]
        ]);
    }

    // ✅ GET data untuk dropdown form tambah user
    public function get_form_data() {
        // Ambil daftar posyandu (seperti logic mentor)
        $posyandu = $this->db->get('ref_posyandu')->result_array();
        
        // Ambil daftar tim khusus
        $timsus = $this->db->get('timsus')->result_array();
    
        // Data Role diambil dari struktur ENUM di database kamu
        $roles = ['admin', 'kader', 'member', 'timsus'];
    
        // Data Status (0 = Tidak Aktif, 1 = Aktif)
        $status = [
            ['id' => 1, 'label' => 'Aktif'],
            ['id' => 0, 'label' => 'Tidak Aktif']
        ];
    
        echo json_encode([
            'status' => true,
            'message' => 'Data form berhasil dimuat',
            'data' => [
                'posyandu' => $posyandu,
                'timsus'   => $timsus,
                'roles'    => $roles,
                'status'   => $status
            ]
        ]);
    }

    // ✅ GET detail user by ID
    public function detail($id) {
        $data = $this->Muser->getById($id);
        if ($data) {
            echo json_encode([
                'status' => true,
                'message' => 'Detail user ditemukan',
                'data' => $data
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'User tidak ditemukan'
            ]);
        }
    }

    // ✅ POST tambah user baru (Sinkron dengan logic Mentor)
    public function create() {
        $input = json_decode($this->input->raw_input_stream, true);

        // Validasi input wajib sesuai form mentor
        if (empty($input['nama']) || empty($input['username']) || empty($input['password'])) {
            echo json_encode([
                'status' => false,
                'message' => 'Nama, username, dan password wajib diisi'
            ]);
            return;
        }

        // Data disusun berdasarkan struktur tabel mentor
        $data = [
            'nama'        => $input['nama'],
            'username'    => $input['username'],
            'password'    => md5($input['password']), // Menggunakan md5 sesuai mentor
            'role'        => isset($input['role']) ? $input['role'] : 'member',
            'aktif'       => isset($input['aktif']) ? $input['aktif'] : 'Aktif',
            'posyandu_id' => isset($input['posyandu_id']) ? $input['posyandu_id'] : null,
            'timsus_id'   => isset($input['timsus_id']) ? $input['timsus_id'] : null,
            'bumil_id'    => isset($input['bumil_id']) ? $input['bumil_id'] : 0, // Tetap dijaga jika kolomnya ada
        ];

        if ($this->Muser->insert($data)) {
            echo json_encode([
                'status' => true,
                'message' => 'User berhasil ditambahkan'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal menambahkan user'
            ]);
        }
    }

    // ✅ PUT update user
    public function update($id) {
        $input = json_decode($this->input->raw_input_stream, true);
        if (empty($input)) {
            echo json_encode(['status' => false, 'message' => 'Data tidak ditemukan']);
            return;
        }

        $updateData = [];
        if(isset($input['nama']))        $updateData['nama'] = $input['nama'];
        if(isset($input['username']))    $updateData['username'] = $input['username'];
        if(isset($input['role']))        $updateData['role'] = $input['role'];
        if(isset($input['aktif']))       $updateData['aktif'] = $input['aktif'];
        if(isset($input['posyandu_id'])) $updateData['posyandu_id'] = $input['posyandu_id'];
        if(isset($input['timsus_id']))   $updateData['timsus_id'] = $input['timsus_id'];

        // Logic password: Jika diisi maka di-hash, jika kosong tidak di-update (menghindari overwrite)
        if (!empty($input['password'])) {
            $updateData['password'] = md5($input['password']);
        }

        if ($this->Muser->updateData($id, $updateData)) {
            echo json_encode([
                'status' => true,
                'message' => 'User berhasil diperbarui'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal memperbarui user'
            ]);
        }
    }

    // ✅ DELETE user
    public function delete($id) {
        if ($this->Muser->deleteData($id)) {
            echo json_encode([
                'status' => true,
                'message' => 'User berhasil dihapus'
            ]);
        } else {
            echo json_encode([
                'status' => false,
                'message' => 'Gagal menghapus user'
            ]);
        }
    }

    // ✅ LOGIN user
    public function login() {
        $data = json_decode($this->input->raw_input_stream, true);
        $username = isset($data['username']) ? trim($data['username']) : '';
        $password = isset($data['password']) ? md5(trim($data['password'])) : '';
    
        if (empty($username) || empty($password)) {
            echo json_encode(['status' => false, 'message' => 'Username dan password wajib diisi']);
            return;
        }
    
        $user = $this->Muser->getByUsernameAndPassword($username, $password);
    
        if (!$user) {
            echo json_encode(['status' => false, 'message' => 'Username atau password salah']);
            return;
        }
    
        unset($user['password']); // demi keamanan
    
        // 🔹 buat token acak
        $token = generate_token();
        
        // 🔹 waktu expired 60 menit
        $exp = generate_expired();
    
        echo json_encode([
            'status' => true,
            'message' => 'Login berhasil',
            'token' => $token,
            'expired_at' => $exp,
            'data' => [
                'id_user'     => $user['id_user'],
                'username'    => $user['username'],
                'role'        => $user['role'],
                'posyandu_id' => $user['posyandu_id'],
                'timsus_id'   => $user['timsus_id'],
                'bumil_id'    => $user['bumil_id']
            ]
        ]);
    }

    // ✅ LOGOUT user
    public function logout() {
        $data = json_decode($this->input->raw_input_stream, true);
        $user_id = isset($data['user_id']) ? $data['user_id'] : null;

        if (!$user_id) {
            echo json_encode(['status' => false, 'message' => 'User ID wajib dikirim']);
            return;
        }

        if ($this->Muser->updateData($user_id, ['token' => null])) {
            echo json_encode(['status' => true, 'message' => 'Logout berhasil']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal logout']);
        }
    }

    // ✅ Ganti password
    public function changePassword($id) {
        $data = json_decode($this->input->raw_input_stream, true);

        $oldPassword = isset($data['old_password']) ? md5($data['old_password']) : '';
        $newPassword = isset($data['new_password']) ? md5($data['new_password']) : '';

        $user = $this->Muser->getById($id);

        if (!$user) {
            echo json_encode(['status' => false, 'message' => 'User tidak ditemukan']);
            return;
        }

        if ($user['password'] !== $oldPassword) {
            echo json_encode(['status' => false, 'message' => 'Password lama salah']);
            return;
        }

        if ($this->Muser->updateData($id, ['password' => $newPassword])) {
            echo json_encode(['status' => true, 'message' => 'Password berhasil diubah']);
        } else {
            echo json_encode(['status' => false, 'message' => 'Gagal mengubah password']);
        }
    }
}
